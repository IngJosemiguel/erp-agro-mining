<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Producto;

class CodigosBarraListado extends Component
{
    use WithPagination;

    public string $buscar = '';
    public array $seleccionados = [];   // IDs de productos seleccionados para imprimir
    public int $copias = 1;
    public string $formato = 'CODE128'; // CODE128 | EAN13 | QR

    public function updatingBuscar()
    {
        $this->resetPage();
    }

    public function toggleSeleccion(int $id): void
    {
        if (in_array($id, $this->seleccionados)) {
            $this->seleccionados = array_values(array_diff($this->seleccionados, [$id]));
        } else {
            $this->seleccionados[] = $id;
        }
    }

    public function seleccionarTodos(): void
    {
        $ids = Producto::deEmpresa(1)
            ->activos()
            ->when($this->buscar, fn($q) => $q->buscar($this->buscar))
            ->pluck('id')
            ->toArray();
        $this->seleccionados = $ids;
    }

    public function limpiarSeleccion(): void
    {
        $this->seleccionados = [];
    }

    public function guardarCodigoBarras(int $id, string $codigo): void
    {
        $codigo = trim($codigo);
        if (!$codigo)
            return;
        Producto::findOrFail($id)->update(['codigo_barras' => $codigo]);
        session()->flash('success', 'Código de barras actualizado.');
    }

    public function render()
    {
        $productos = Producto::deEmpresa(1)
            ->activos()
            ->with(['unidadMedida:id,abreviatura'])
            ->when($this->buscar, fn($q) => $q->buscar($this->buscar))
            ->orderBy('nombre')
            ->paginate(25);

        return view('livewire.codigos-barra-listado', [
            'productos' => $productos,
        ]);
    }
}
