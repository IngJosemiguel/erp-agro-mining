<?php

namespace App\Livewire;

use Livewire\Component;
use App\Traits\TieneContextoEmpresa;
use Livewire\WithPagination;
use App\Models\Compra;
use App\Models\Proveedor;

class ComprasListado extends Component
{
    use WithPagination, TieneContextoEmpresa;

    public string $buscar = '';
    public string $filtroEstado = '';
    public string $filtroProveedor = '';
    public string $fechaDesde = '';
    public string $fechaHasta = '';

    public function mount(): void
    {
        $this->inicializarContexto();
        $this->fechaDesde = now()->startOfMonth()->toDateString();
        $this->fechaHasta = now()->toDateString();
    }

    public function updatingBuscar(): void
    {
        $this->resetPage();
    }
    public function updatingFiltroEstado(): void
    {
        $this->resetPage();
    }

    public function anular(int $id): void
    {
        $compra = Compra::find($id);
        if ($compra && $compra->estado !== 'anulada') {
            $compra->update(['estado' => 'anulada']);

            // ── Reversa contable automática ──
            try {
                app(\App\Services\ContabilidadAutomaticaService::class)
                    ->registrarReversa($this->empresaId, 'compras', $id, 'Anulación de compra');
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::warning('Contab. automática: reversa compra', [
                    'compra_id' => $id,
                    'error' => $e->getMessage()
                ]);
            }

            session()->flash('success', "Compra {$compra->serie}-{$compra->correlativo} anulada.");
        }
    }

    public function render()
    {
        $query = Compra::query()
            ->where('empresa_id', $this->empresaId)
            ->with(['proveedor:id,razon_social,numero_documento', 'usuario:id,name', 'almacen:id,nombre'])
            ->withCount('detalles');

        if ($this->buscar) {
            $query->where(function ($q) {
                $q->where('serie', 'LIKE', "%{$this->buscar}%")
                    ->orWhere('correlativo', 'LIKE', "%{$this->buscar}%")
                    ->orWhereHas('proveedor', function ($pq) {
                        $pq->where('razon_social', 'LIKE', "%{$this->buscar}%")
                            ->orWhere('numero_documento', 'LIKE', "%{$this->buscar}%");
                    });
            });
        }

        if ($this->filtroEstado) {
            $query->where('estado', $this->filtroEstado);
        }

        if ($this->filtroProveedor) {
            $query->where('proveedor_id', $this->filtroProveedor);
        }

        if ($this->fechaDesde && $this->fechaHasta) {
            $query->whereBetween('fecha_emision', [$this->fechaDesde, $this->fechaHasta]);
        }

        // KPIs
        $totalCompras = Compra::where('empresa_id', $this->empresaId)
            ->whereNotIn('estado', ['anulada'])
            ->whereBetween('fecha_emision', [$this->fechaDesde, $this->fechaHasta])
            ->sum('total');
        $cantCompras = Compra::where('empresa_id', $this->empresaId)
            ->whereNotIn('estado', ['anulada'])
            ->whereBetween('fecha_emision', [$this->fechaDesde, $this->fechaHasta])
            ->count();
        $pendientes = Compra::where('empresa_id', $this->empresaId)->where('estado', 'registrada')->count();

        return view('livewire.compras-listado', [
            'compras' => $query->orderByDesc('fecha_emision')->orderByDesc('id')->paginate(20),
            'proveedores' => Proveedor::where('empresa_id', $this->empresaId)->where('activo', true)->orderBy('razon_social')->get(['id', 'razon_social']),
            'totalCompras' => $totalCompras,
            'cantCompras' => $cantCompras,
            'pendientes' => $pendientes,
        ]);
    }
}
