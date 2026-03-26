<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Gasto;
use App\Models\Proveedor;
use App\Traits\TieneContextoEmpresa;

class GastosListado extends Component
{
    use WithPagination, TieneContextoEmpresa;

    public string $buscar = '';
    public string $filtroEstado = '';
    public string $filtroCategoria = '';
    public string $fechaDesde = '';
    public string $fechaHasta = '';

    // Modal
    public bool $showModal = false;
    public ?int $editingId = null;
    public string $fecha = '';
    public string $concepto = '';
    public string $categoria_gasto = '';
    public string $proveedor_id = '';
    public string $tipo_documento = '';
    public string $serie = '';
    public string $correlativo = '';
    public string $subtotal = '0';
    public string $igv = '0';
    public string $total = '0';
    public string $estado = 'registrado';
    public string $observaciones = '';

    // Búsqueda proveedor
    public string $buscarProveedor = '';
    public array $proveedoresEncontrados = [];
    public string $proveedorTexto = '';

    public function mount(): void
    {
        $this->inicializarContexto();
        $this->fechaDesde = now()->startOfMonth()->toDateString();
        $this->fechaHasta = now()->toDateString();
        $this->fecha = now()->toDateString();
    }

    public function updatingBuscar(): void
    {
        $this->resetPage();
    }
    public function updatingFiltroEstado(): void
    {
        $this->resetPage();
    }
    public function updatingFiltroCategoria(): void
    {
        $this->resetPage();
    }

    public function updatedBuscarProveedor(): void
    {
        if (strlen($this->buscarProveedor) >= 2) {
            $this->proveedoresEncontrados = Proveedor::where('empresa_id', $this->empresaId)
                ->where('activo', true)
                ->where(function ($q) {
                    $q->where('razon_social', 'LIKE', "%{$this->buscarProveedor}%")
                        ->orWhere('numero_documento', 'LIKE', "%{$this->buscarProveedor}%");
                })
                ->select('id', 'numero_documento', 'razon_social')
                ->limit(8)->get()->toArray();
        } else {
            $this->proveedoresEncontrados = [];
        }
    }

    public function seleccionarProveedor(int $id): void
    {
        $p = Proveedor::find($id);
        if ($p) {
            $this->proveedor_id = (string) $p->id;
            $this->proveedorTexto = "{$p->numero_documento} — {$p->razon_social}";
            $this->buscarProveedor = '';
            $this->proveedoresEncontrados = [];
        }
    }

    public function updatedSubtotal(): void
    {
        $this->recalcular();
    }

    protected function recalcular(): void
    {
        $sub = (float) $this->subtotal;
        $this->igv = (string) round($sub * 0.18, 2);
        $this->total = (string) round($sub + (float) $this->igv, 2);
    }

    public function openModal(?int $id = null): void
    {
        $this->resetForm();
        if ($id) {
            $gasto = Gasto::find($id);
            if ($gasto) {
                $this->editingId = $gasto->id;
                $this->fecha = $gasto->fecha->toDateString();
                $this->concepto = $gasto->concepto;
                $this->categoria_gasto = $gasto->categoria_gasto ?? '';
                $this->proveedor_id = (string) ($gasto->proveedor_id ?? '');
                $this->tipo_documento = $gasto->tipo_documento ?? '';
                $this->serie = $gasto->serie ?? '';
                $this->correlativo = $gasto->correlativo ?? '';
                $this->subtotal = (string) $gasto->subtotal;
                $this->igv = (string) $gasto->igv;
                $this->total = (string) $gasto->total;
                $this->estado = $gasto->estado;
                $this->observaciones = $gasto->observaciones ?? '';
                if ($gasto->proveedor) {
                    $this->proveedorTexto = "{$gasto->proveedor->numero_documento} — {$gasto->proveedor->razon_social}";
                }
            }
        }
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate([
            'fecha' => 'required|date',
            'concepto' => 'required|string|max:255',
            'subtotal' => 'required|numeric|min:0',
            'total' => 'required|numeric|min:0',
        ]);

        $data = [
            'empresa_id' => $this->empresaId,
            'sucursal_id' => $this->sucursalId,
            'user_id' => auth()->id() ?? 1,
            'fecha' => $this->fecha,
            'concepto' => $this->concepto,
            'categoria_gasto' => $this->categoria_gasto ?: null,
            'proveedor_id' => $this->proveedor_id ?: null,
            'tipo_documento' => $this->tipo_documento ?: null,
            'serie' => $this->serie ?: null,
            'correlativo' => $this->correlativo ?: null,
            'subtotal' => (float) $this->subtotal,
            'igv' => (float) $this->igv,
            'total' => (float) $this->total,
            'estado' => $this->estado,
            'observaciones' => $this->observaciones ?: null,
        ];

        if ($this->editingId) {
            Gasto::findOrFail($this->editingId)->update($data);
            session()->flash('success', 'Gasto actualizado correctamente.');
        } else {
            $gasto = Gasto::create($data);

            // ── Automatización Contable ──
            try {
                app(\App\Services\ContabilidadAutomaticaService::class)
                    ->registrarGasto($this->empresaId, $gasto);
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::warning('Contabilidad automática: gasto', [
                    'gasto_id' => $gasto->id,
                    'error' => $e->getMessage()
                ]);
            }

            session()->flash('success', 'Gasto registrado correctamente.');
        }

        $this->showModal = false;
        $this->resetForm();
    }

    public function marcarPagado(int $id): void
    {
        $gasto = Gasto::find($id);
        if ($gasto && $gasto->estado === 'registrado') {
            $gasto->update(['estado' => 'pagado']);
            session()->flash('success', "Gasto marcado como pagado.");
        }
    }

    public function anular(int $id): void
    {
        $gasto = Gasto::find($id);
        if ($gasto && $gasto->estado !== 'anulado') {
            $gasto->update(['estado' => 'anulado']);

            // ── Reversa contable automática ──
            try {
                app(\App\Services\ContabilidadAutomaticaService::class)
                    ->registrarReversa($this->empresaId, 'gastos', $id, 'Anulación de gasto');
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::warning('Contabilidad automática: reversa gasto', [
                    'gasto_id' => $id,
                    'error' => $e->getMessage()
                ]);
            }

            session()->flash('success', "Gasto anulado.");
        }
    }

    protected function resetForm(): void
    {
        $this->editingId = null;
        $this->fecha = now()->toDateString();
        $this->concepto = '';
        $this->categoria_gasto = '';
        $this->proveedor_id = '';
        $this->tipo_documento = '';
        $this->serie = '';
        $this->correlativo = '';
        $this->subtotal = '0';
        $this->igv = '0';
        $this->total = '0';
        $this->estado = 'registrado';
        $this->observaciones = '';
        $this->buscarProveedor = '';
        $this->proveedoresEncontrados = [];
        $this->proveedorTexto = '';
    }

    public function render()
    {
        $query = Gasto::query()
            ->where('empresa_id', $this->empresaId)
            ->with(['proveedor:id,razon_social,numero_documento', 'usuario:id,name']);

        if ($this->buscar) {
            $query->where(function ($q) {
                $q->where('concepto', 'LIKE', "%{$this->buscar}%")
                    ->orWhere('serie', 'LIKE', "%{$this->buscar}%")
                    ->orWhere('correlativo', 'LIKE', "%{$this->buscar}%");
            });
        }

        if ($this->filtroEstado) {
            $query->where('estado', $this->filtroEstado);
        }
        if ($this->filtroCategoria) {
            $query->where('categoria_gasto', $this->filtroCategoria);
        }
        if ($this->fechaDesde && $this->fechaHasta) {
            $query->whereBetween('fecha', [$this->fechaDesde, $this->fechaHasta]);
        }

        $totalGastos = Gasto::where('empresa_id', $this->empresaId)
            ->whereNotIn('estado', ['anulado'])
            ->whereBetween('fecha', [$this->fechaDesde ?: '2000-01-01', $this->fechaHasta ?: now()])
            ->sum('total');
        $cantGastos = Gasto::where('empresa_id', $this->empresaId)
            ->whereNotIn('estado', ['anulado'])
            ->whereBetween('fecha', [$this->fechaDesde ?: '2000-01-01', $this->fechaHasta ?: now()])
            ->count();
        $pendientes = Gasto::where('empresa_id', $this->empresaId)->where('estado', 'registrado')->count();

        return view('livewire.gastos-listado', [
            'gastos' => $query->orderByDesc('fecha')->orderByDesc('id')->paginate(20),
            'categorias' => Gasto::$categorias,
            'totalGastos' => $totalGastos,
            'cantGastos' => $cantGastos,
            'pendientes' => $pendientes,
        ]);
    }
}
