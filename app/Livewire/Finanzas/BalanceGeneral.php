<?php

namespace App\Livewire\Finanzas;

use App\Traits\TieneContextoEmpresa;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

/**
 * Balance General (Estado de Situación Financiera)
 * Genera automáticamente Activo, Pasivo y Patrimonio
 * desde los asientos contables validados.
 */
class BalanceGeneral extends Component
{
    use TieneContextoEmpresa;

    public string $periodo = '';
    public string $anio = '';

    public function mount(): void
    {
        $this->inicializarContexto();
        $this->anio = now()->format('Y');
        $this->periodo = now()->format('Y-m');
    }

    private function obtenerSaldos(string $tipo): \Illuminate\Support\Collection
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
                ->orWhere('asientos_contables.periodo', '<=', $this->periodo))
            ->groupBy('plan_cuentas.id', 'plan_cuentas.codigo', 'plan_cuentas.nombre', 'plan_cuentas.naturaleza')
            ->select(
                'plan_cuentas.codigo',
                'plan_cuentas.nombre',
                'plan_cuentas.naturaleza',
                DB::raw('COALESCE(SUM(asiento_partidas.debe), 0) as total_debe'),
                DB::raw('COALESCE(SUM(asiento_partidas.haber), 0) as total_haber'),
            )
            ->orderBy('plan_cuentas.codigo')
            ->get()
            ->map(function ($cuenta) {
                $saldo = $cuenta->naturaleza === 'deudora'
                    ? $cuenta->total_debe - $cuenta->total_haber   // Activos/Gastos
                    : $cuenta->total_haber - $cuenta->total_debe;  // Pasivos/Ingresos/Patrimonio
                return (object) array_merge((array) $cuenta, ['saldo' => $saldo]);
            })
            ->filter(fn($c) => $c->saldo != 0);
    }

    public function render()
    {
        $activos = $this->obtenerSaldos('activo');
        $pasivos = $this->obtenerSaldos('pasivo');
        $patrimonio = $this->obtenerSaldos('patrimonio');
        $ingresos = $this->obtenerSaldos('ingreso');
        $gastos = $this->obtenerSaldos('gasto');
        $costos = $this->obtenerSaldos('costo');

        $totalActivo = $activos->sum('saldo');
        $totalPasivo = $pasivos->sum('saldo');
        $totalPatrimonio = $patrimonio->sum('saldo');

        // Resultado del ejercicio (utilidad/pérdida)
        $totalIngresos = $ingresos->sum('saldo');
        $totalGastos = $gastos->sum('saldo');
        $totalCostos = $costos->sum('saldo');
        $resultadoEjercicio = $totalIngresos - $totalGastos - $totalCostos;

        // Verificar ecuación: Activo = Pasivo + Patrimonio + Resultado
        $totalPasivoPatrimonio = $totalPasivo + $totalPatrimonio + $resultadoEjercicio;
        $cuadrado = abs($totalActivo - $totalPasivoPatrimonio) < 0.01;

        return view('livewire.finanzas.balance-general', [
            'activos' => $activos,
            'pasivos' => $pasivos,
            'patrimonio' => $patrimonio,
            'ingresos' => $ingresos,
            'gastos' => $gastos,
            'costos' => $costos,
            'totalActivo' => $totalActivo,
            'totalPasivo' => $totalPasivo,
            'totalPatrimonio' => $totalPatrimonio,
            'totalIngresos' => $totalIngresos,
            'totalGastos' => $totalGastos,
            'totalCostos' => $totalCostos,
            'resultadoEjercicio' => $resultadoEjercicio,
            'cuadrado' => $cuadrado,
        ]);
    }
}
