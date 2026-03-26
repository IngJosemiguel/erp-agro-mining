<?php

namespace App\Livewire\Finanzas;

use App\Traits\TieneContextoEmpresa;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class CentrosCosto extends Component
{
    use WithPagination, TieneContextoEmpresa;

    public string $buscar = '';
    public bool $showModal = false;
    public bool $editMode = false;
    public ?int $editingId = null;

    // Formulario
    public string $codigo = '';
    public string $nombre = '';
    public string $descripcion = '';
    public bool $activo = true;

    protected function rules(): array
    {
        return [
            'codigo' => 'required|string|max:50',
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'activo' => 'boolean',
        ];
    }

    protected array $messages = [
        'codigo.required' => 'El código es obligatorio.',
        'nombre.required' => 'El nombre es obligatorio.',
    ];

    public function mount(): void
    {
        $this->inicializarContexto();
    }

    public function openCreate(): void
    {
        $this->reset(['codigo', 'nombre', 'descripcion', 'activo', 'editingId']);
        $this->editMode = false;
        $this->activo = true;
        // Sugerir código autogenerado CC-0X
        $last = DB::table('centros_costo')->where('empresa_id', $this->empresaId)->count() + 1;
        $this->codigo = 'CC-' . str_pad($last, 3, '0', STR_PAD_LEFT);
        $this->showModal = true;
    }

    public function openEdit(int $id): void
    {
        $centro = DB::table('centros_costo')
            ->where('id', $id)
            ->where('empresa_id', $this->empresaId)
            ->first();

        if (!$centro)
            return;

        $this->editingId = $id;
        $this->codigo = $centro->codigo;
        $this->nombre = $centro->nombre;
        $this->descripcion = $centro->descripcion ?? '';
        $this->activo = (bool) $centro->activo;
        $this->editMode = true;
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate();

        // Validar unique code
        $isUnique = !DB::table('centros_costo')
            ->where('empresa_id', $this->empresaId)
            ->where('codigo', $this->codigo)
            ->when($this->editMode, fn($q) => $q->where('id', '!=', $this->editingId))
            ->exists();

        if (!$isUnique) {
            $this->addError('codigo', 'Este código ya está en uso en tus centros de costo.');
            return;
        }

        $data = [
            'empresa_id' => $this->empresaId,
            'codigo' => strtoupper($this->codigo),
            'nombre' => $this->nombre,
            'descripcion' => $this->descripcion,
            'activo' => $this->activo,
            'updated_at' => now(),
        ];

        if ($this->editMode && $this->editingId) {
            DB::table('centros_costo')
                ->where('id', $this->editingId)
                ->where('empresa_id', $this->empresaId)
                ->update($data);
            session()->flash('success', 'Centro de costo actualizado correctmente.');
        } else {
            $data['created_at'] = now();
            DB::table('centros_costo')->insert($data);
            session()->flash('success', 'Centro de costo registrado correctamente.');
        }

        $this->showModal = false;
        $this->reset(['codigo', 'nombre', 'descripcion', 'activo']);
    }

    public function toggleActivo(int $id): void
    {
        $centro = DB::table('centros_costo')->where('id', $id)->where('empresa_id', $this->empresaId)->first();
        if ($centro) {
            DB::table('centros_costo')->where('id', $id)->update(['activo' => !$centro->activo, 'updated_at' => now()]);
        }
    }

    public function delete(int $id): void
    {
        // Validar si tiene asientos contables antes de borrar
        $tieneMovimientos = DB::table('asiento_partidas')
            ->join('asientos_contables', 'asiento_partidas.asiento_id', '=', 'asientos_contables.id')
            ->where('asientos_contables.empresa_id', $this->empresaId)
            ->where('asiento_partidas.centro_costo_id', $id)
            ->exists();

        if ($tieneMovimientos) {
            session()->flash('error', 'No se puede eliminar. Este centro de costo ya tiene partidas / movimientos contables asociados.');
            return;
        }

        DB::table('centros_costo')->where('id', $id)->where('empresa_id', $this->empresaId)->delete();
        session()->flash('success', 'Centro de costo eliminado definitivamente.');
    }

    public function render()
    {
        $query = DB::table('centros_costo')->where('empresa_id', $this->empresaId);

        if (!empty($this->buscar)) {
            $b = $this->buscar;
            $query->where(fn($q) => $q->where('codigo', 'LIKE', "%{$b}%")->orWhere('nombre', 'LIKE', "%{$b}%"));
        }

        $centros = $query->orderBy('codigo')->paginate(15);

        // Opcional: Calcular gasto real acumulado por centro de costo
        // (De una tabla view/asientos_partidas donde haya asignación de id)

        return view('livewire.finanzas.centros-costo', [
            'centros' => $centros
        ]);
    }
}
