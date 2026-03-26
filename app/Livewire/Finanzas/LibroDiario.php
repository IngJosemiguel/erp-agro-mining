<?php

namespace App\Livewire\Finanzas;

use App\Traits\TieneContextoEmpresa;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class LibroDiario extends Component
{
    use TieneContextoEmpresa;

    public string $periodo = '';
    public string $filtroCuenta = '';
    public string $filtroTipo = '';

    public float $totalDebe = 0;
    public float $totalHaber = 0;

    public function mount(): void
    {
        $this->inicializarContexto();
        $this->periodo = now()->format('Y-m');
    }

    public function render()
    {
        $partidas = DB::table('asiento_partidas')
            ->join('asientos_contables', 'asiento_partidas.asiento_id', '=', 'asientos_contables.id')
            ->join('plan_cuentas', 'asiento_partidas.cuenta_id', '=', 'plan_cuentas.id')
            ->where('asientos_contables.empresa_id', $this->empresaId)
            ->where('asientos_contables.periodo', $this->periodo)
            ->where('asientos_contables.estado', '!=', 'anulado')
            ->when($this->filtroCuenta, fn($q) => $q->where(fn($b) => $b
                ->where('plan_cuentas.codigo', 'LIKE', "%{$this->filtroCuenta}%")
                ->orWhere('plan_cuentas.nombre', 'LIKE', "%{$this->filtroCuenta}%")))
            ->when($this->filtroTipo, fn($q) => $q->where('asientos_contables.tipo', $this->filtroTipo))
            ->orderBy('asientos_contables.fecha')
            ->orderBy('asientos_contables.numero_asiento')
            ->orderBy('asiento_partidas.orden')
            ->select(
                'asientos_contables.numero_asiento',
                'asientos_contables.fecha',
                'asientos_contables.tipo',
                'asientos_contables.glosa',
                'plan_cuentas.codigo as cuenta_codigo',
                'plan_cuentas.nombre as cuenta_nombre',
                'plan_cuentas.tipo as cuenta_tipo',
                'asiento_partidas.glosa_partida',
                'asiento_partidas.debe',
                'asiento_partidas.haber',
            )
            ->get();

        $this->totalDebe = $partidas->sum('debe');
        $this->totalHaber = $partidas->sum('haber');

        // Balance por cuenta (Mayor simplificado)
        $mayorCuentas = $partidas->groupBy('cuenta_codigo')->map(function ($rows) {
            return [
                'codigo' => $rows->first()->cuenta_codigo,
                'nombre' => $rows->first()->cuenta_nombre,
                'tipo' => $rows->first()->cuenta_tipo,
                'debe' => $rows->sum('debe'),
                'haber' => $rows->sum('haber'),
                'saldo' => $rows->sum('debe') - $rows->sum('haber'),
            ];
        })->values();

        return view('livewire.finanzas.libro-diario', [
            'partidas' => $partidas,
            'mayorCuentas' => $mayorCuentas,
        ]);
    }
}
