<?php

namespace App\Livewire\Finanzas;

use App\Traits\TieneContextoEmpresa;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Presupuesto vs Ejecutado
 * Permite definir montos presupuestados por cuenta y período,
 * y los compara automáticamente con lo registrado en asientos contables.
 */
class Presupuesto extends Component
{
    use WithPagination, TieneContextoEmpresa;

    public string $anio = '';
    public string $filtroPeriodo = '';
    public string $filtroTipo = '';
    public string $buscar = '';

    public bool $showModal = false;
    public bool $editMode = false;
    public ?int $editingId = null;

    // Formulario
    public ?int $cuenta_id = null;
    public string $periodo = '';
    public float $monto_presupuestado = 0;
    public string $cuentaBusqueda = '';

    // Estadísticas generales
    public float $totalPresupuestado = 0;
    public float $totalEjecutado = 0;
    public float $variacionTotal = 0;
    public float $porcentajeEjecucion = 0;

    protected function rules(): array
    {
        return [
            'cuenta_id' => 'required|integer|exists:plan_cuentas,id',
            'periodo' => 'required|regex:/^\d{4}-\d{2}$/',
            'monto_presupuestado' => 'required|numeric|min:0',
        ];
    }

    protected array $messages = [
        'cuenta_id.required' => 'Debes seleccionar una cuenta.',
        'periodo.required' => 'El período es obligatorio.',
        'periodo.regex' => 'El período debe tener formato YYYY-MM.',
        'monto_presupuestado.min' => 'El monto no puede ser negativo.',
    ];

    public function mount(): void
    {
        $this->inicializarContexto();
        $this->anio = now()->format('Y');
        $this->filtroPeriodo = now()->format('Y-m');
        $this->periodo = now()->format('Y-m');
    }

    public function openCreate(): void
    {
        $this->reset(['cuenta_id', 'monto_presupuestado', 'cuentaBusqueda', 'editingId']);
        $this->periodo = $this->filtroPeriodo ?: now()->format('Y-m');
        $this->editMode = false;
        $this->showModal = true;
    }

    public function openEdit(int $id): void
    {
        $p = DB::table('presupuestos')
            ->join('plan_cuentas', 'presupuestos.cuenta_id', '=', 'plan_cuentas.id')
            ->where('presupuestos.id', $id)
            ->select('presupuestos.*', 'plan_cuentas.codigo', 'plan_cuentas.nombre')
            ->first();

        if (!$p)
            return;

        $this->editingId = $id;
        $this->cuenta_id = $p->cuenta_id;
        $this->cuentaBusqueda = "{$p->codigo} — {$p->nombre}";
        $this->periodo = $p->periodo;
        $this->monto_presupuestado = (float) $p->monto_presupuestado;
        $this->editMode = true;
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate();

        // Verificar que la cuenta pertenece a esta empresa
        $cuenta = DB::table('plan_cuentas')
            ->where('id', $this->cuenta_id)
            ->where('empresa_id', $this->empresaId)
            ->first();

        if (!$cuenta) {
            $this->addError('cuenta_id', 'La cuenta no pertenece a esta empresa.');
            return;
        }

        // Calcular ejecutado actual para esa cuenta y período
        $ejecutado = $this->obtenerEjecutado($this->cuenta_id, $this->periodo);

        $data = [
            'empresa_id' => $this->empresaId,
            'cuenta_id' => $this->cuenta_id,
            'periodo' => $this->periodo,
            'monto_presupuestado' => $this->monto_presupuestado,
            'monto_ejecutado' => $ejecutado,
            'updated_at' => now(),
        ];

        if ($this->editMode && $this->editingId) {
            DB::table('presupuestos')
                ->where('id', $this->editingId)
                ->where('empresa_id', $this->empresaId)
                ->update($data);
            session()->flash('success', 'Presupuesto actualizado.');
        } else {
            // Verificar duplicado
            $existe = DB::table('presupuestos')
                ->where('empresa_id', $this->empresaId)
                ->where('cuenta_id', $this->cuenta_id)
                ->where('periodo', $this->periodo)
                ->exists();

            if ($existe) {
                $this->addError('cuenta_id', 'Ya existe un presupuesto para esta cuenta y período.');
                return;
            }

            $data['created_at'] = now();
            DB::table('presupuestos')->insert($data);
            session()->flash('success', 'Presupuesto registrado correctamente.');
        }

        $this->showModal = false;
        $this->reset(['cuenta_id', 'monto_presupuestado', 'cuentaBusqueda', 'editingId']);
    }

    public function delete(int $id): void
    {
        DB::table('presupuestos')
            ->where('id', $id)
            ->where('empresa_id', $this->empresaId)
            ->delete();
        session()->flash('success', 'Presupuesto eliminado.');
    }

    /**
     * Sincroniza el monto ejecutado real de todos los presupuestos del período activo.
     */
    public function sincronizarEjecutados(): void
    {
        $presupuestos = DB::table('presupuestos')
            ->where('empresa_id', $this->empresaId)
            ->where('periodo', $this->filtroPeriodo)
            ->get();

        foreach ($presupuestos as $p) {
            $ejecutado = $this->obtenerEjecutado($p->cuenta_id, $p->periodo);
            DB::table('presupuestos')
                ->where('id', $p->id)
                ->update(['monto_ejecutado' => $ejecutado, 'updated_at' => now()]);
        }

        session()->flash('success', 'Ejecutados sincronizados desde asientos contables.');
    }

    /**
     * Replica el presupuesto de un mes al mes siguiente.
     */
    public function copiarAlSiguienteMes(): void
    {
        $periodoOrigen = $this->filtroPeriodo;
        $periodoDestino = \Carbon\Carbon::createFromFormat('Y-m', $periodoOrigen)
            ->addMonth()->format('Y-m');

        $presupuestosOrigen = DB::table('presupuestos')
            ->where('empresa_id', $this->empresaId)
            ->where('periodo', $periodoOrigen)
            ->get();

        if ($presupuestosOrigen->isEmpty()) {
            session()->flash('error', 'No hay presupuestos en el período seleccionado para copiar.');
            return;
        }

        $insertados = 0;
        foreach ($presupuestosOrigen as $p) {
            $yaExiste = DB::table('presupuestos')
                ->where('empresa_id', $this->empresaId)
                ->where('cuenta_id', $p->cuenta_id)
                ->where('periodo', $periodoDestino)
                ->exists();

            if (!$yaExiste) {
                DB::table('presupuestos')->insert([
                    'empresa_id' => $this->empresaId,
                    'cuenta_id' => $p->cuenta_id,
                    'periodo' => $periodoDestino,
                    'monto_presupuestado' => $p->monto_presupuestado,
                    'monto_ejecutado' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $insertados++;
            }
        }

        session()->flash('success', "Se copiaron {$insertados} líneas de presupuesto a {$periodoDestino}.");
    }

    /**
     * Obtiene el ejecutado real de una cuenta en un período desde asientos validados.
     */
    private function obtenerEjecutado(int $cuentaId, string $periodo): float
    {
        $result = DB::table('asiento_partidas')
            ->join('asientos_contables', 'asiento_partidas.asiento_id', '=', 'asientos_contables.id')
            ->join('plan_cuentas', 'asiento_partidas.cuenta_id', '=', 'plan_cuentas.id')
            ->where('asientos_contables.empresa_id', $this->empresaId)
            ->where('asientos_contables.periodo', $periodo)
            ->where('asientos_contables.estado', 'validado')
            ->where('asiento_partidas.cuenta_id', $cuentaId)
            ->selectRaw('
                plan_cuentas.naturaleza,
                SUM(asiento_partidas.debe) as total_debe,
                SUM(asiento_partidas.haber) as total_haber
            ')
            ->groupBy('plan_cuentas.naturaleza')
            ->first();

        if (!$result)
            return 0;

        // Para cuentas deudoras (gastos/costos): debe - haber
        // Para cuentas acreedoras (ingresos): haber - debe
        return $result->naturaleza === 'deudora'
            ? abs($result->total_debe - $result->total_haber)
            : abs($result->total_haber - $result->total_debe);
    }

    public function seleccionarCuenta(int $id, string $display): void
    {
        $this->cuenta_id = $id;
        $this->cuentaBusqueda = $display;
    }

    public function render()
    {
        // Cuentas para el selector
        $cuentasSelector = [];
        if ($this->showModal) {
            $q = $this->cuentaBusqueda;
            $cuentasSelector = DB::table('plan_cuentas')
                ->where('empresa_id', $this->empresaId)
                ->where('acepta_movimiento', true)
                ->where('activo', true)
                ->where(fn($b) => $b
                    ->where('codigo', 'LIKE', "%{$q}%")
                    ->orWhere('nombre', 'LIKE', "%{$q}%"))
                ->orderBy('codigo')
                ->limit(15)
                ->get(['id', 'codigo', 'nombre', 'tipo']);
        }

        // Query principal
        $query = DB::table('presupuestos')
            ->join('plan_cuentas', 'presupuestos.cuenta_id', '=', 'plan_cuentas.id')
            ->where('presupuestos.empresa_id', $this->empresaId);

        if ($this->filtroPeriodo) {
            $query->where('presupuestos.periodo', $this->filtroPeriodo);
        } elseif ($this->anio) {
            $query->where('presupuestos.periodo', 'LIKE', "{$this->anio}-%");
        }

        if ($this->filtroTipo) {
            $query->where('plan_cuentas.tipo', $this->filtroTipo);
        }

        if ($this->buscar) {
            $b = $this->buscar;
            $query->where(fn($q) => $q
                ->where('plan_cuentas.codigo', 'LIKE', "%{$b}%")
                ->orWhere('plan_cuentas.nombre', 'LIKE', "%{$b}%"));
        }

        $presupuestos = $query
            ->select(
                'presupuestos.*',
                'plan_cuentas.codigo',
                'plan_cuentas.nombre as cuenta_nombre',
                'plan_cuentas.tipo as cuenta_tipo',
                DB::raw('presupuestos.monto_ejecutado - presupuestos.monto_presupuestado as variacion'),
                DB::raw('CASE WHEN presupuestos.monto_presupuestado > 0 THEN ROUND(presupuestos.monto_ejecutado / presupuestos.monto_presupuestado * 100, 1) ELSE 0 END as pct_ejecucion')
            )
            ->orderBy('plan_cuentas.tipo')
            ->orderBy('plan_cuentas.codigo')
            ->paginate(25);

        // Estadísticas globales del período
        $statsQuery = DB::table('presupuestos')
            ->where('empresa_id', $this->empresaId);

        if ($this->filtroPeriodo) {
            $statsQuery->where('periodo', $this->filtroPeriodo);
        } elseif ($this->anio) {
            $statsQuery->where('periodo', 'LIKE', "{$this->anio}-%");
        }

        $stats = $statsQuery->selectRaw('
            SUM(monto_presupuestado) as presupuestado,
            SUM(monto_ejecutado) as ejecutado,
            COUNT(*) as total_lineas,
            COUNT(CASE WHEN monto_ejecutado > monto_presupuestado THEN 1 END) as sobre_ejecucion
        ')->first();

        $this->totalPresupuestado = (float) ($stats->presupuestado ?? 0);
        $this->totalEjecutado = (float) ($stats->ejecutado ?? 0);
        $this->variacionTotal = $this->totalEjecutado - $this->totalPresupuestado;
        $this->porcentajeEjecucion = $this->totalPresupuestado > 0
            ? round(($this->totalEjecutado / $this->totalPresupuestado) * 100, 1)
            : 0;

        // Datos por tipo para el resumen de categorías
        $porTipo = DB::table('presupuestos')
            ->join('plan_cuentas', 'presupuestos.cuenta_id', '=', 'plan_cuentas.id')
            ->where('presupuestos.empresa_id', $this->empresaId)
            ->where('presupuestos.periodo', $this->filtroPeriodo)
            ->groupBy('plan_cuentas.tipo')
            ->select(
                'plan_cuentas.tipo',
                DB::raw('SUM(presupuestos.monto_presupuestado) as presupuestado'),
                DB::raw('SUM(presupuestos.monto_ejecutado) as ejecutado')
            )
            ->get();

        return view('livewire.finanzas.presupuesto', [
            'presupuestos' => $presupuestos,
            'stats' => $stats,
            'porTipo' => $porTipo,
            'cuentasSelector' => $cuentasSelector,
        ]);
    }
}
