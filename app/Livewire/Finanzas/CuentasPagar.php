<?php

namespace App\Livewire\Finanzas;

use App\Traits\TieneContextoEmpresa;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Cuentas por Pagar
 * Proveedores con saldo pendiente (compras a crédito no saldadas)
 */
class CuentasPagar extends Component
{
    use WithPagination, TieneContextoEmpresa;

    public string $buscar = '';
    public string $filtroEstado = 'pendiente';
    public bool $showModal = false;

    public ?int $compraId = null;
    public float $montoPago = 0;
    public string $fechaPago = '';
    public string $metodoPago = 'transferencia';
    public string $referencia = '';

    public function mount(): void
    {
        $this->inicializarContexto();
        $this->fechaPago = now()->toDateString();
    }

    public function abrirPago(int $compraId, float $saldoPendiente): void
    {
        $this->compraId = $compraId;
        $this->montoPago = $saldoPendiente;
        $this->showModal = true;
    }

    public function registrarPago(): void
    {
        $this->validate([
            'montoPago' => 'required|numeric|min:0.01',
            'fechaPago' => 'required|date',
            'metodoPago' => 'required|string',
        ]);

        $compra = DB::table('compras')->find($this->compraId);
        if (!$compra || $compra->empresa_id != $this->empresaId)
            return;

        $nuevoPagado = $compra->monto_pagado + $this->montoPago;
        $nuevoEstado = $nuevoPagado >= $compra->total ? 'ingresada_almacen' : 'registrada';

        DB::table('compras')->where('id', $this->compraId)->update([
            'monto_pagado' => min($nuevoPagado, $compra->total),
            'estado' => $nuevoEstado,
            'updated_at' => now(),
        ]);

        // ── Automatización Contable: Pago CxP ──
        try {
            app(\App\Services\ContabilidadAutomaticaService::class)
                ->registrarPagoCxP(
                    $this->empresaId,
                    $this->montoPago,
                    "Compra #{$this->compraId} — {$this->metodoPago}" . ($this->referencia ? " Ref: {$this->referencia}" : '')
                );
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning('Contabilidad automática: pago CxP', [
                'compra_id' => $this->compraId,
                'error' => $e->getMessage()
            ]);
        }

        session()->flash('success', "Pago de S/ " . number_format($this->montoPago, 2) . " registrado al proveedor.");
        $this->showModal = false;
        $this->reset(['compraId', 'montoPago', 'referencia']);
        $this->fechaPago = now()->toDateString();
    }

    public function render()
    {
        $query = DB::table('compras')
            ->leftJoin('proveedores', 'compras.proveedor_id', '=', 'proveedores.id')
            ->where('compras.empresa_id', $this->empresaId)
            ->where('compras.forma_pago', 'credito')
            ->whereNotIn('compras.estado', ['anulada', 'borrador']);

        if ($this->buscar) {
            $q = $this->buscar;
            $query->where(fn($b) => $b
                ->where('compras.serie', 'LIKE', "%{$q}%")
                ->orWhere('compras.correlativo', 'LIKE', "%{$q}%")
                ->orWhere('proveedores.razon_social', 'LIKE', "%{$q}%")
                ->orWhere('proveedores.ruc', 'LIKE', "%{$q}%"));
        }

        if ($this->filtroEstado === 'pendiente') {
            $query->whereRaw('compras.monto_pagado < compras.total');
        } elseif ($this->filtroEstado === 'vencido') {
            $query->where('compras.fecha_vencimiento', '<', now()->toDateString())
                ->whereRaw('compras.monto_pagado < compras.total');
        } elseif ($this->filtroEstado === 'pagado') {
            $query->whereRaw('compras.monto_pagado >= compras.total');
        }

        $isSqlite = DB::connection()->getDriverName() === 'sqlite';
        $diasVencidoSql = $isSqlite
            ? 'CAST(julianday(\'now\') - julianday(compras.fecha_vencimiento) AS INTEGER) as dias_vencido'
            : 'DATEDIFF(NOW(), compras.fecha_vencimiento) as dias_vencido';

        $stats = DB::table('compras')
            ->where('empresa_id', $this->empresaId)
            ->where('forma_pago', 'credito')
            ->whereNotIn('estado', ['anulada', 'borrador'])
            ->selectRaw('
                SUM(total - monto_pagado) as total_pendiente,
                COUNT(CASE WHEN fecha_vencimiento < CURRENT_DATE AND monto_pagado < total THEN 1 END) as vencidas,
                COUNT(CASE WHEN monto_pagado < total THEN 1 END) as pendientes,
                SUM(CASE WHEN monto_pagado >= total THEN total ELSE 0 END) as total_pagado
            ')->first();

        return view('livewire.finanzas.cuentas-pagar', [
            'compras' => $query
                ->select(
                    'compras.*',
                    'proveedores.razon_social as proveedor_nombre',
                    'proveedores.ruc',
                    DB::raw('compras.total - compras.monto_pagado as saldo_pendiente'),
                    DB::raw($diasVencidoSql)
                )
                ->orderBy('compras.fecha_vencimiento')
                ->paginate(20),
            'stats' => $stats,
        ]);
    }
}
