<?php

namespace App\Livewire\Finanzas;

use App\Traits\TieneContextoEmpresa;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class AsientosContables extends Component
{
    use WithPagination, TieneContextoEmpresa;

    public string $buscar = '';
    public string $filtroTipo = '';
    public string $filtroPeriodo = '';
    public string $filtroEstado = '';
    public bool $showModal = false;
    public bool $showDetalle = false;
    public ?int $asientoVerDetalleId = null;

    // Form cabecera asiento
    public string $fecha = '';
    public string $tipo = 'otro';
    public string $glosa = '';
    public string $observaciones = '';

    // Partidas (filas del asiento)
    public array $partidas = [];

    // Totales
    public float $totalDebe = 0;
    public float $totalHaber = 0;

    public function mount(): void
    {
        $this->inicializarContexto();
        $this->fecha = now()->toDateString();
        $this->filtroPeriodo = now()->format('Y-m');
        $this->agregarPartida(); // Empezar con 2 filas vacías
        $this->agregarPartida();
    }

    public function agregarPartida(): void
    {
        $this->partidas[] = [
            'cuenta_id' => '',
            'cuenta_codigo' => '',
            'cuenta_nombre' => '',
            'glosa_partida' => '',
            'debe' => '',
            'haber' => '',
        ];
    }

    public function eliminarPartida(int $index): void
    {
        if (count($this->partidas) <= 2)
            return; // Mínimo 2 filas
        array_splice($this->partidas, $index, 1);
        $this->recalcularTotales();
    }

    public function buscarCuenta(int $index): void
    {
        $codigo = trim($this->partidas[$index]['cuenta_codigo'] ?? '');
        if (!$codigo)
            return;

        $cuenta = DB::table('plan_cuentas')
            ->where('empresa_id', $this->empresaId)
            ->where('codigo', $codigo)
            ->where('acepta_movimiento', true)
            ->where('activo', true)
            ->first();

        if ($cuenta) {
            $this->partidas[$index]['cuenta_id'] = $cuenta->id;
            $this->partidas[$index]['cuenta_nombre'] = $cuenta->nombre;
        } else {
            $this->partidas[$index]['cuenta_nombre'] = '⚠ Cuenta no encontrada';
            $this->partidas[$index]['cuenta_id'] = '';
        }
    }

    public function recalcularTotales(): void
    {
        $this->totalDebe = collect($this->partidas)->sum(fn($p) => (float) ($p['debe'] ?? 0));
        $this->totalHaber = collect($this->partidas)->sum(fn($p) => (float) ($p['haber'] ?? 0));
    }

    public function updatedPartidas(): void
    {
        $this->recalcularTotales();
    }

    public function guardarAsiento(): void
    {
        $this->recalcularTotales();

        // Validaciones básicas
        if (empty($this->glosa)) {
            $this->addError('glosa', 'La glosa del asiento es obligatoria.');
            return;
        }
        if (count($this->partidas) < 2) {
            $this->addError('partidas', 'Un asiento debe tener al menos 2 partidas.');
            return;
        }
        if (abs($this->totalDebe - $this->totalHaber) > 0.01) {
            $this->addError('partidas', 'El asiento no está cuadrado. Debe = Haber.');
            return;
        }
        $partidasValidas = collect($this->partidas)->filter(fn($p) => !empty($p['cuenta_id']));
        if ($partidasValidas->isEmpty()) {
            $this->addError('partidas', 'Debes asignar cuentas válidas a las partidas.');
            return;
        }

        DB::transaction(function () {
            $periodo = substr($this->fecha, 0, 7); // YYYY-MM

            // Correlativo
            $ultimo = DB::table('asientos_contables')
                ->where('empresa_id', $this->empresaId)
                ->whereYear('fecha', substr($this->fecha, 0, 4))
                ->count() + 1;
            $numero = substr($this->fecha, 0, 4) . '-' . str_pad($ultimo, 5, '0', STR_PAD_LEFT);

            $asientoId = DB::table('asientos_contables')->insertGetId([
                'empresa_id' => $this->empresaId,
                'user_id' => auth()->id(),
                'numero_asiento' => $numero,
                'fecha' => $this->fecha,
                'periodo' => $periodo,
                'tipo' => $this->tipo,
                'glosa' => $this->glosa,
                'total_debe' => $this->totalDebe,
                'total_haber' => $this->totalHaber,
                'estado' => 'borrador',
                'observaciones' => $this->observaciones ?: null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            foreach ($this->partidas as $idx => $partida) {
                if (empty($partida['cuenta_id']))
                    continue;
                DB::table('asiento_partidas')->insert([
                    'asiento_id' => $asientoId,
                    'cuenta_id' => $partida['cuenta_id'],
                    'orden' => $idx + 1,
                    'glosa_partida' => $partida['glosa_partida'] ?: null,
                    'debe' => (float) ($partida['debe'] ?? 0),
                    'haber' => (float) ($partida['haber'] ?? 0),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        });

        session()->flash('success', 'Asiento contable registrado correctamente.');
        $this->showModal = false;
        $this->reset(['glosa', 'observaciones', 'partidas', 'totalDebe', 'totalHaber']);
        $this->fecha = now()->toDateString();
        $this->agregarPartida();
        $this->agregarPartida();
    }

    public function validarAsiento(int $id): void
    {
        DB::table('asientos_contables')
            ->where('id', $id)
            ->where('empresa_id', $this->empresaId)
            ->update(['estado' => 'validado', 'updated_at' => now()]);
        session()->flash('success', 'Asiento validado.');
    }

    public function anularAsiento(int $id): void
    {
        DB::table('asientos_contables')
            ->where('id', $id)
            ->where('empresa_id', $this->empresaId)
            ->where('estado', 'borrador') // Solo se pueden anular borradores
            ->update(['estado' => 'anulado', 'updated_at' => now()]);
        session()->flash('success', 'Asiento anulado.');
    }

    public function verDetalle(int $id): void
    {
        $this->asientoVerDetalleId = $id;
        $this->showDetalle = true;
    }

    public function render()
    {
        $query = DB::table('asientos_contables')
            ->leftJoin('users', 'asientos_contables.user_id', '=', 'users.id')
            ->where('asientos_contables.empresa_id', $this->empresaId)
            ->select('asientos_contables.*', 'users.name as usuario');

        if ($this->buscar) {
            $q = $this->buscar;
            $query->where(fn($b) => $b
                ->where('asientos_contables.numero_asiento', 'LIKE', "%{$q}%")
                ->orWhere('asientos_contables.glosa', 'LIKE', "%{$q}%"));
        }
        if ($this->filtroTipo) {
            $query->where('asientos_contables.tipo', $this->filtroTipo);
        }
        if ($this->filtroPeriodo) {
            $query->where('asientos_contables.periodo', $this->filtroPeriodo);
        }
        if ($this->filtroEstado) {
            $query->where('asientos_contables.estado', $this->filtroEstado);
        }

        // Detalle para modal
        $asientoDetalle = null;
        $partidasDetalle = [];
        if ($this->asientoVerDetalleId) {
            $asientoDetalle = DB::table('asientos_contables')->find($this->asientoVerDetalleId);
            $partidasDetalle = DB::table('asiento_partidas')
                ->join('plan_cuentas', 'asiento_partidas.cuenta_id', '=', 'plan_cuentas.id')
                ->where('asiento_partidas.asiento_id', $this->asientoVerDetalleId)
                ->orderBy('asiento_partidas.orden')
                ->select('asiento_partidas.*', 'plan_cuentas.codigo', 'plan_cuentas.nombre as cuenta_nombre')
                ->get();
        }

        // Stats del período
        $stats = DB::table('asientos_contables')
            ->where('empresa_id', $this->empresaId)
            ->where('periodo', $this->filtroPeriodo)
            ->selectRaw('COUNT(*) as total, SUM(total_debe) as total_debe, SUM(CASE WHEN estado = "validado" THEN 1 ELSE 0 END) as validados, SUM(CASE WHEN estado = "borrador" THEN 1 ELSE 0 END) as borradores')
            ->first();

        // Cuentas disponibles para el modal
        $cuentasDisponibles = [];
        if ($this->showModal) {
            $cuentasDisponibles = DB::table('plan_cuentas')
                ->where('empresa_id', $this->empresaId)
                ->where('acepta_movimiento', true)
                ->where('activo', true)
                ->orderBy('codigo')
                ->get(['id', 'codigo', 'nombre', 'tipo']);
        }

        return view('livewire.finanzas.asientos-contables', [
            'asientos' => $query->orderByDesc('fecha')->orderByDesc('id')->paginate(20),
            'stats' => $stats,
            'asientoDetalle' => $asientoDetalle,
            'partidasDetalle' => $partidasDetalle,
            'cuentasDisponibles' => $cuentasDisponibles,
        ]);
    }
}
