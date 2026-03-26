<?php

namespace App\Livewire\Finanzas;

use App\Traits\TieneContextoEmpresa;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

/**
 * Estado de Resultados (PyG)
 * Ingresos - Costos - Gastos = Utilidad Neta
 */
class EstadoResultados extends Component
{
    use TieneContextoEmpresa;

    public string $periodoDesde = '';
    public string $periodoHasta = '';
    public string $anio = '';

    public function mount(): void
    {
        $this->inicializarContexto();
        $this->anio = now()->format('Y');
        $this->periodoDesde = $this->anio . '-01';
        $this->periodoHasta = now()->format('Y-m');
    }

    private function obtenerSaldosPorTipo(string $tipo): \Illuminate\Support\Collection
    {
        return DB::table('plan_cuentas')
            ->leftJoin('asiento_partidas', 'plan_cuentas.id', '=', 'asiento_partidas.cuenta_id')
            ->leftJoin('asientos_contables', function ($join) {
                $join->on('asiento_partidas.asiento_id', '=', 'asientos_contables.id')
                    ->where('asientos_contables.estado', '=', 'validado');
            })
            ->where('plan_cuentas.empresa_id', $this->empresaId)
            ->where('plan_cuentas.tipo', $tipo)
            ->where('plan_cuentas.acepta_movimiento', true)
            ->where(fn($q) => $q
                ->whereNull('asientos_contables.periodo')
                ->orWhere(fn($b) => $b
                    ->where('asientos_contables.periodo', '>=', $this->periodoDesde)
                    ->where('asientos_contables.periodo', '<=', $this->periodoHasta)))
            ->groupBy('plan_cuentas.id', 'plan_cuentas.codigo', 'plan_cuentas.nombre')
            ->select(
                'plan_cuentas.codigo',
                'plan_cuentas.nombre',
                DB::raw('COALESCE(SUM(asiento_partidas.haber), 0) - COALESCE(SUM(asiento_partidas.debe), 0) as saldo')
            )
            ->orderBy('plan_cuentas.codigo')
            ->get()
            ->filter(fn($c) => $c->saldo != 0);
    }

    public function render()
    {
        $ingresos = $this->obtenerSaldosPorTipo('ingreso');
        $costos = $this->obtenerSaldosPorTipo('costo');
        $gastos = $this->obtenerSaldosPorTipo('gasto');

        $totalIngresos = $ingresos->sum('saldo');
        $totalCostos = $costos->sum('saldo');
        $totalGastos = $gastos->sum('saldo');

        $utilidadBruta = $totalIngresos - $totalCostos;
        $utilidadOperativa = $utilidadBruta - $totalGastos;

        // IGV estimado no deducible (solo referencia)
        $margenBruto = $totalIngresos > 0 ? round(($utilidadBruta / $totalIngresos) * 100, 1) : 0;
        $margenOperativo = $totalIngresos > 0 ? round(($utilidadOperativa / $totalIngresos) * 100, 1) : 0;

        // Datos por mes para el gráfico (solo si hay datos)
        $porMes = DB::table('asientos_contables')
            ->join('asiento_partidas', 'asientos_contables.id', '=', 'asiento_partidas.asiento_id')
            ->join('plan_cuentas', 'asiento_partidas.cuenta_id', '=', 'plan_cuentas.id')
            ->where('asientos_contables.empresa_id', $this->empresaId)
            ->where('asientos_contables.estado', 'validado')
            ->where('asientos_contables.periodo', '>=', $this->periodoDesde)
            ->where('asientos_contables.periodo', '<=', $this->periodoHasta)
            ->whereIn('plan_cuentas.tipo', ['ingreso', 'gasto', 'costo'])
            ->groupBy('asientos_contables.periodo', 'plan_cuentas.tipo')
            ->select(
                'asientos_contables.periodo',
                'plan_cuentas.tipo',
                DB::raw('SUM(asiento_partidas.haber) - SUM(asiento_partidas.debe) as total')
            )
            ->orderBy('asientos_contables.periodo')
            ->get()
            ->groupBy('periodo');

        return view('livewire.finanzas.estado-resultados', [
            'ingresos' => $ingresos,
            'costos' => $costos,
            'gastos' => $gastos,
            'totalIngresos' => $totalIngresos,
            'totalCostos' => $totalCostos,
            'totalGastos' => $totalGastos,
            'utilidadBruta' => $utilidadBruta,
            'utilidadOperativa' => $utilidadOperativa,
            'margenBruto' => $margenBruto,
            'margenOperativo' => $margenOperativo,
            'porMes' => $porMes,
        ]);
    }
}
