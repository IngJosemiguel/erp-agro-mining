<?php

namespace App\Livewire\Finanzas;

use App\Traits\TieneContextoEmpresa;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class LibroMayor extends Component
{
    use TieneContextoEmpresa;

    public string $periodoDesde = '';
    public string $periodoHasta = '';
    public ?int $cuentaId = null;
    public string $cuentaBusqueda = '';

    public function mount()
    {
        $this->inicializarContexto();
        $this->periodoDesde = now()->startOfYear()->format('Y-m');
        $this->periodoHasta = now()->format('Y-m');
    }

    public function seleccionarCuenta(int $id, string $display): void
    {
        $this->cuentaId = $id;
        $this->cuentaBusqueda = $display;
    }

    public function render()
    {
        // Buscar cuentas para el selector
        $cuentasSelector = [];
        if (strlen($this->cuentaBusqueda) > 2 && empty($this->cuentaId)) {
            $q = $this->cuentaBusqueda;
            $cuentasSelector = DB::table('plan_cuentas')
                ->where('empresa_id', $this->empresaId)
                ->where('acepta_movimiento', true)
                ->where(fn($b) => $b->where('codigo', 'LIKE', "%{$q}%")->orWhere('nombre', 'LIKE', "%{$q}%"))
                ->limit(10)
                ->get();
        }

        // Obtener el mayor general agrupado por cuenta validada
        $queryPartidas = DB::table('asiento_partidas')
            ->join('asientos_contables', 'asiento_partidas.asiento_id', '=', 'asientos_contables.id')
            ->join('plan_cuentas', 'asiento_partidas.cuenta_id', '=', 'plan_cuentas.id')
            ->where('asientos_contables.empresa_id', $this->empresaId)
            ->where('asientos_contables.estado', 'validado')
            ->whereBetween('asientos_contables.periodo', [$this->periodoDesde, $this->periodoHasta]);

        if ($this->cuentaId) {
            $queryPartidas->where('asiento_partidas.cuenta_id', $this->cuentaId);
        }

        $cuentasConMovimientos = $queryPartidas
            ->select(
                'plan_cuentas.id as cuenta_id',
                'plan_cuentas.codigo',
                'plan_cuentas.nombre',
                'plan_cuentas.naturaleza',
                DB::raw('SUM(asiento_partidas.debe) as total_debe'),
                DB::raw('SUM(asiento_partidas.haber) as total_haber')
            )
            ->groupBy('plan_cuentas.id', 'plan_cuentas.codigo', 'plan_cuentas.nombre', 'plan_cuentas.naturaleza')
            ->orderBy('plan_cuentas.codigo')
            ->get();

        // Para el detalle en forma de T, sacar todas las partidas ordenadas por fecha
        $movimientosDetalle = [];
        if ($this->cuentaId) {
            $movimientosDetalle = DB::table('asiento_partidas')
                ->join('asientos_contables', 'asiento_partidas.asiento_id', '=', 'asientos_contables.id')
                ->where('asientos_contables.empresa_id', $this->empresaId)
                ->where('asientos_contables.estado', 'validado')
                ->where('asiento_partidas.cuenta_id', $this->cuentaId)
                ->whereBetween('asientos_contables.periodo', [$this->periodoDesde, $this->periodoHasta])
                ->select(
                    'asientos_contables.fecha',
                    'asientos_contables.numero',
                    'asientos_contables.glosa as asiento_glosa',
                    'asiento_partidas.debe',
                    'asiento_partidas.haber'
                )
                ->orderBy('asientos_contables.fecha')
                ->orderBy('asientos_contables.id')
                ->get();
        }

        return view('livewire.finanzas.libro-mayor', [
            'cuentasSelector' => $cuentasSelector,
            'cuentasSaldos' => $cuentasConMovimientos,
            'movimientosDetalle' => $movimientosDetalle
        ]);
    }
}
