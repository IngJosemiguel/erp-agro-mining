<?php
namespace App\Livewire;
use Livewire\Component;
use App\Traits\TieneContextoEmpresa;
use Livewire\WithPagination;
use App\Models\Venta;
use Illuminate\Support\Facades\DB;

class NotasCredito extends Component
{
    use WithPagination, TieneContextoEmpresa;
    public string $buscar = '';
    public string $filtroTipo = '';
    public string $filtroEstado = '';
    public string $fechaDesde = '';
    public string $fechaHasta = '';
    public bool $showModal = false;
    public string $venta_id = '';
    public string $tipo = 'credito';
    public string $motivo = '';
    public string $observaciones = '';
    public string $buscarVenta = '';
    public array $ventasEncontradas = [];
    public ?array $ventaSeleccionada = null;
    public float $totalNota = 0;

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

    public function updatedBuscarVenta(): void
    {
        if (strlen($this->buscarVenta) >= 2) {
            $this->ventasEncontradas = DB::table('ventas')
                ->join('clientes', 'ventas.cliente_id', '=', 'clientes.id')
                ->where('ventas.empresa_id', 1)
                ->where(fn($q) => $q->where('ventas.serie', 'LIKE', "%{$this->buscarVenta}%")
                    ->orWhere('ventas.correlativo', 'LIKE', "%{$this->buscarVenta}%")
                    ->orWhere('clientes.razon_social', 'LIKE', "%{$this->buscarVenta}%"))
                ->select('ventas.id', 'ventas.serie', 'ventas.correlativo', 'ventas.fecha_emision', 'ventas.total', 'clientes.razon_social as cliente')
                ->limit(8)->get()->map(fn($v) => (array) $v)->toArray();
        } else {
            $this->ventasEncontradas = [];
        }
    }

    public function seleccionarVenta(int $id): void
    {
        $v = collect($this->ventasEncontradas)->firstWhere('id', $id);
        if ($v) {
            $this->ventaSeleccionada = $v;
            $this->venta_id = (string) $id;
            $this->totalNota = (float) $v['total'];
            $this->buscarVenta = '';
            $this->ventasEncontradas = [];
        }
    }

    public function openModal(): void
    {
        $this->reset(['venta_id', 'tipo', 'motivo', 'observaciones', 'buscarVenta', 'ventasEncontradas', 'ventaSeleccionada', 'totalNota']);
        $this->tipo = 'credito';
        $this->showModal = true;
    }

    public function render()
    {
        // Notas = ventas con tipo_documento 07 (NC) o 08 (ND)
        $query = DB::table('ventas')
            ->leftJoin('clientes', 'ventas.cliente_id', '=', 'clientes.id')
            ->where('ventas.empresa_id', 1)
            ->whereIn('ventas.tipo_documento', ['07', '08'])
            ->select('ventas.*', 'clientes.razon_social as cliente_nombre');

        if ($this->buscar) {
            $query->where(fn($q) => $q->where('ventas.serie', 'LIKE', "%{$this->buscar}%")->orWhere('clientes.razon_social', 'LIKE', "%{$this->buscar}%"));
        }
        if ($this->filtroEstado) {
            $query->where('ventas.estado', $this->filtroEstado);
        }

        $kpiNC = DB::table('ventas')->where('empresa_id', $this->empresaId)->where('tipo_documento', '07')->count();
        $kpiND = DB::table('ventas')->where('empresa_id', $this->empresaId)->where('tipo_documento', '08')->count();

        return view('livewire.notas-credito', [
            'notas' => $query->orderByDesc('ventas.created_at')->paginate(20),
            'kpiNC' => $kpiNC,
            'kpiND' => $kpiND,
        ]);
    }
}
