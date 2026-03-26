<?php

namespace App\Livewire\Finanzas;

use App\Traits\TieneContextoEmpresa;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class PlanCuentas extends Component
{
    use WithPagination, TieneContextoEmpresa;

    public string $buscar = '';
    public string $filtroTipo = '';
    public bool $showModal = false;
    public bool $editMode = false;
    public ?int $editingId = null;

    // Form
    public string $codigo = '';
    public string $nombre = '';
    public string $tipo = 'activo';
    public string $naturaleza = 'deudora';
    public string $nivel = '3';
    public ?int $cuenta_padre_id = null;
    public bool $acepta_movimiento = true;

    public array $tipoOpciones = [
        'activo' => 'Activo',
        'pasivo' => 'Pasivo',
        'patrimonio' => 'Patrimonio',
        'ingreso' => 'Ingreso',
        'gasto' => 'Gasto',
        'costo' => 'Costo',
    ];

    protected function rules(): array
    {
        return [
            'codigo' => 'required|string|max:20',
            'nombre' => 'required|string|max:255',
            'tipo' => 'required|in:activo,pasivo,patrimonio,ingreso,gasto,costo',
            'naturaleza' => 'required|in:deudora,acreedora',
            'nivel' => 'required|in:1,2,3,4,5',
        ];
    }

    public function mount(): void
    {
        $this->inicializarContexto();
    }

    public function updatingBuscar(): void
    {
        $this->resetPage();
    }

    public function openCreateModal(): void
    {
        $this->reset(['codigo', 'nombre', 'tipo', 'naturaleza', 'nivel', 'cuenta_padre_id', 'acepta_movimiento', 'editingId']);
        $this->tipo = 'activo';
        $this->naturaleza = 'deudora';
        $this->nivel = '3';
        $this->acepta_movimiento = true;
        $this->editMode = false;
        $this->showModal = true;
    }

    public function openEditModal(int $id): void
    {
        $cuenta = DB::table('plan_cuentas')->find($id);
        if (!$cuenta)
            return;

        $this->editingId = $id;
        $this->codigo = $cuenta->codigo;
        $this->nombre = $cuenta->nombre;
        $this->tipo = $cuenta->tipo;
        $this->naturaleza = $cuenta->naturaleza;
        $this->nivel = (string) $cuenta->nivel;
        $this->cuenta_padre_id = $cuenta->cuenta_padre_id;
        $this->acepta_movimiento = (bool) $cuenta->acepta_movimiento;
        $this->editMode = true;
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate();

        // Validar código único por empresa
        $query = DB::table('plan_cuentas')
            ->where('empresa_id', $this->empresaId)
            ->where('codigo', $this->codigo);

        if ($this->editMode && $this->editingId) {
            $query->where('id', '!=', $this->editingId);
        }

        if ($query->exists()) {
            $this->addError('codigo', "El código '{$this->codigo}' ya existe en el plan de cuentas.");
            return;
        }

        $data = [
            'empresa_id' => $this->empresaId,
            'codigo' => strtoupper(trim($this->codigo)),
            'nombre' => trim($this->nombre),
            'tipo' => $this->tipo,
            'naturaleza' => $this->naturaleza,
            'nivel' => $this->nivel,
            'cuenta_padre_id' => $this->cuenta_padre_id ?: null,
            'acepta_movimiento' => $this->acepta_movimiento,
            'updated_at' => now(),
        ];

        if ($this->editMode && $this->editingId) {
            DB::table('plan_cuentas')->where('id', $this->editingId)->update($data);
            session()->flash('success', 'Cuenta actualizada correctamente.');
        } else {
            $data['created_at'] = now();
            DB::table('plan_cuentas')->insert($data);
            session()->flash('success', 'Cuenta creada correctamente.');
        }

        $this->showModal = false;
        $this->reset(['codigo', 'nombre', 'editingId']);
    }

    public function delete(int $id): void
    {
        // Verificar que no tenga partidas asociadas
        $tieneMovimientos = DB::table('asiento_partidas')->where('cuenta_id', $id)->exists();
        if ($tieneMovimientos) {
            session()->flash('error', 'No puedes eliminar esta cuenta porque tiene partidas contables registradas.');
            return;
        }
        DB::table('plan_cuentas')->where('id', $id)->delete();
        session()->flash('success', 'Cuenta eliminada.');
    }

    public function toggleEstado(int $id): void
    {
        $cuenta = DB::table('plan_cuentas')->find($id);
        if (!$cuenta)
            return;
        DB::table('plan_cuentas')->where('id', $id)->update(['activo' => !$cuenta->activo, 'updated_at' => now()]);
    }

    public function render()
    {
        $query = DB::table('plan_cuentas')
            ->where('empresa_id', $this->empresaId)
            ->orderBy('codigo');

        if ($this->buscar) {
            $q = $this->buscar;
            $query->where(fn($b) => $b
                ->where('codigo', 'LIKE', "%{$q}%")
                ->orWhere('nombre', 'LIKE', "%{$q}%"));
        }

        if ($this->filtroTipo) {
            $query->where('tipo', $this->filtroTipo);
        }

        // Estadísticas
        $stats = DB::table('plan_cuentas')->where('empresa_id', $this->empresaId)->selectRaw('
            COUNT(*) as total,
            SUM(CASE WHEN activo = 1 THEN 1 ELSE 0 END) as activas,
            SUM(CASE WHEN tipo = "activo" THEN 1 ELSE 0 END) as activos,
            SUM(CASE WHEN tipo = "pasivo" THEN 1 ELSE 0 END) as pasivos,
            SUM(CASE WHEN tipo = "ingreso" THEN 1 ELSE 0 END) as ingresos
        ')->first();

        return view('livewire.finanzas.plan-cuentas', [
            'cuentas' => $query->paginate(25),
            'stats' => $stats,
            'tipoOpciones' => $this->tipoOpciones,
        ]);
    }
}
