<?php

namespace App\Livewire\Finanzas;

use App\Traits\TieneContextoEmpresa;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Cuentas por Cobrar
 * Clientes con saldo pendiente de cobro (ventas a crédito no saldadas)
 */
class CuentasCobrar extends Component
{
    use WithPagination, TieneContextoEmpresa;

    public string $buscar = '';
    public string $filtroEstado = 'pendiente';
    public bool $showModal = false;

    // Registrar pago manual
    public ?int $ventaId = null;
    public float $montoPago = 0;
    public string $fechaPago = '';
    public string $metodoPago = 'efectivo';
    public string $referencia = '';

    public function mount(): void
    {
        $this->inicializarContexto();
        $this->fechaPago = now()->toDateString();
    }

    public function abrirPago(int $ventaId, float $saldoPendiente): void
    {
        $this->ventaId = $ventaId;
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

        $venta = DB::table('ventas')->find($this->ventaId);
        if (!$venta || $venta->empresa_id != $this->empresaId)
            return;

        $nuevoPagado = $venta->monto_pagado + $this->montoPago;
        $nuevoEstado = $nuevoPagado >= $venta->total ? 'pagado' : 'parcial';

        DB::table('ventas')->where('id', $this->ventaId)->update([
            'monto_pagado' => min($nuevoPagado, $venta->total),
            'estado_pago' => $nuevoEstado,
            'updated_at' => now(),
        ]);

        // Registrar en caja_movimientos si hay caja activa
        $cajaAbierta = DB::table('caja_aperturas')
            ->join('cajas', 'caja_aperturas.caja_id', '=', 'cajas.id')
            ->where('cajas.sucursal_id', $this->sucursalId)
            ->where('caja_aperturas.estado', 'abierta')
            ->select('caja_aperturas.id')
            ->first();

        if ($cajaAbierta) {
            DB::table('caja_movimientos')->insert([
                'caja_apertura_id' => $cajaAbierta->id,
                'tipo' => 'ingreso',
                'concepto' => 'ingreso_otro',
                'monto' => $this->montoPago,
                'referencia' => "Cobro CxC Venta #{$this->ventaId}" . ($this->referencia ? " — {$this->referencia}" : ''),
                'movible_type' => 'App\\Models\\Venta',
                'movible_id' => $this->ventaId,
                'descripcion' => "Método: {$this->metodoPago}",
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // ── Automatización Contable: Cobro CxC ──
        try {
            app(\App\Services\ContabilidadAutomaticaService::class)
                ->registrarCobroCxC(
                    $this->empresaId,
                    $this->montoPago,
                    "Venta #{$this->ventaId} — {$this->metodoPago}" . ($this->referencia ? " Ref: {$this->referencia}" : '')
                );
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning('Contabilidad automática: cobro CxC', [
                'venta_id' => $this->ventaId,
                'error' => $e->getMessage()
            ]);
        }

        session()->flash('success', "Pago de S/ " . number_format($this->montoPago, 2) . " registrado correctamente.");
        $this->showModal = false;
        $this->reset(['ventaId', 'montoPago', 'referencia']);
        $this->fechaPago = now()->toDateString();
    }

    public function render()
    {
        $query = DB::table('ventas')
            ->leftJoin('clientes', 'ventas.cliente_id', '=', 'clientes.id')
            ->where('ventas.empresa_id', $this->empresaId)
            ->where('ventas.forma_pago', 'credito')
            ->whereNotIn('ventas.estado', ['anulada']);

        if ($this->buscar) {
            $q = $this->buscar;
            $query->where(fn($b) => $b
                ->where('ventas.serie', 'LIKE', "%{$q}%")
                ->orWhere('ventas.numero', 'LIKE', "%{$q}%")
                ->orWhere('clientes.razon_social', 'LIKE', "%{$q}%")
                ->orWhere('clientes.numero_documento', 'LIKE', "%{$q}%"));
        }

        if ($this->filtroEstado === 'pendiente') {
            $query->where(fn($b) => $b
                ->where('ventas.estado_pago', 'pendiente')
                ->orWhere('ventas.estado_pago', 'parcial'));
        } elseif ($this->filtroEstado === 'vencido') {
            $query->where('ventas.fecha_vencimiento', '<', now()->toDateString())
                ->where('ventas.estado_pago', '!=', 'pagado');
        } elseif ($this->filtroEstado === 'pagado') {
            $query->where('ventas.estado_pago', 'pagado');
        }

        $isSqlite = DB::connection()->getDriverName() === 'sqlite';
        $diasVencidoSql = $isSqlite
            ? 'CAST(julianday(\'now\') - julianday(ventas.fecha_vencimiento) AS INTEGER) as dias_vencido'
            : 'DATEDIFF(NOW(), ventas.fecha_vencimiento) as dias_vencido';

        // Estadísticas globales
        $stats = DB::table('ventas')
            ->where('empresa_id', $this->empresaId)
            ->where('forma_pago', 'credito')
            ->whereNotIn('estado', ['anulada'])
            ->selectRaw('
                SUM(CASE WHEN estado_pago != "pagado" THEN total - monto_pagado ELSE 0 END) as total_pendiente,
                SUM(CASE WHEN estado_pago = "pagado" THEN total ELSE 0 END) as total_cobrado,
                COUNT(CASE WHEN fecha_vencimiento < CURRENT_DATE AND estado_pago != "pagado" THEN 1 END) as vencidas,
                COUNT(CASE WHEN estado_pago = "pendiente" OR estado_pago = "parcial" THEN 1 END) as pendientes
            ')->first();

        return view('livewire.finanzas.cuentas-cobrar', [
            'ventas' => $query
                ->select(
                    'ventas.*',
                    'clientes.razon_social as cliente_nombre',
                    'clientes.numero_documento',
                    DB::raw('ventas.total - ventas.monto_pagado as saldo_pendiente'),
                    DB::raw($diasVencidoSql)
                )
                ->orderBy('ventas.fecha_vencimiento')
                ->paginate(20),
            'stats' => $stats,
        ]);
    }
}
