<?php
namespace App\Livewire;
use Livewire\Component;
use Livewire\WithPagination;
use App\Traits\TieneContextoEmpresa;
use Illuminate\Support\Facades\DB;

class OrdenesPedido extends Component
{
    use WithPagination, TieneContextoEmpresa;
    public string $buscar = '';
    public string $filtroEstado = '';
    public bool $showModal = false;
    public string $cliente_id = '';
    public string $fecha = '';
    public string $fecha_entrega = '';
    public string $condiciones = '';
    public string $observaciones = '';
    public float $total = 0;
    public function mount(): void
    {
        $this->inicializarContexto();
        $this->fecha = now()->toDateString();
    }
    public function updatingBuscar(): void
    {
        $this->resetPage();
    }
    public function openModal(): void
    {
        $this->reset(['cliente_id', 'fecha_entrega', 'condiciones', 'observaciones', 'total']);
        $this->fecha = now()->toDateString();
        $this->showModal = true;
    }

    public function guardar(): void
    {
        $this->validate([
            'cliente_id' => 'required|exists:clientes,id',
            'fecha' => 'required|date',
        ], [
            'cliente_id.required' => 'Selecciona un cliente.',
            'fecha.required' => 'La fecha es obligatoria.',
        ]);

        // Generar código correlativo: OP-YYYYMMDD-XXXX
        $ultimo = DB::table('ordenes_pedido')
            ->where('empresa_id', $this->empresaId)
            ->whereYear('created_at', now()->year)
            ->count() + 1;
        $codigo = 'OP-' . now()->format('Y') . '-' . str_pad($ultimo, 4, '0', STR_PAD_LEFT);

        DB::table('ordenes_pedido')->insert([
            'empresa_id' => $this->empresaId,
            'cliente_id' => $this->cliente_id,
            'user_id' => auth()->id() ?? 1,
            'codigo' => $codigo,
            'fecha' => $this->fecha,
            'fecha_entrega' => $this->fecha_entrega ?: null,
            'condiciones' => $this->condiciones ?: null,
            'observaciones' => $this->observaciones ?: null,
            'subtotal' => 0,
            'igv' => 0,
            'total' => 0,
            'estado' => 'pendiente',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        session()->flash('success', "Orden {$codigo} creada correctamente.");
        $this->showModal = false;
        $this->reset(['cliente_id', 'fecha_entrega', 'condiciones', 'observaciones', 'total']);
        $this->fecha = now()->toDateString();
    }

    public function render()
    {
        $query = DB::table('ordenes_pedido')
            ->leftJoin('clientes', 'ordenes_pedido.cliente_id', '=', 'clientes.id')
            ->where('ordenes_pedido.empresa_id', $this->empresaId)
            ->select('ordenes_pedido.*', 'clientes.razon_social as cliente');
        if ($this->buscar) {
            $query->where(fn($q) => $q->where('ordenes_pedido.codigo', 'LIKE', "%{$this->buscar}%")->orWhere('clientes.razon_social', 'LIKE', "%{$this->buscar}%"));
        }
        if ($this->filtroEstado) {
            $query->where('ordenes_pedido.estado', $this->filtroEstado);
        }
        $totalOP = DB::table('ordenes_pedido')->where('empresa_id', $this->empresaId)->whereNotIn('estado', ['anulado'])->sum('total');
        $pendientes = DB::table('ordenes_pedido')->where('empresa_id', $this->empresaId)->where('estado', 'pendiente')->count();
        $clientes = DB::table('clientes')->where('empresa_id', $this->empresaId)->where('activo', true)->select('id', 'razon_social', 'numero_documento')->orderBy('razon_social')->get();
        return view('livewire.ordenes-pedido', [
            'ordenes' => $query->orderByDesc('ordenes_pedido.created_at')->paginate(20),
            'totalOP' => $totalOP,
            'pendientes' => $pendientes,
            'clientes' => $clientes,
        ]);
    }
}
