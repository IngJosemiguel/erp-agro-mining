<?php
namespace App\Livewire;
use Livewire\Component;
use Livewire\WithPagination;
use App\Traits\TieneContextoEmpresa;
use Illuminate\Support\Facades\DB;

class CajaChicaListado extends Component
{
    use WithPagination, TieneContextoEmpresa;
    public string $buscar = '';
    public string $filtroTipo = '';
    public string $fechaDesde = '';
    public string $fechaHasta = '';
    public bool $showModal = false;
    public string $concepto = '';
    public string $tipo = 'egreso';
    public string $monto = '';
    public string $fecha = '';
    public string $comprobante = '';
    public string $descripcion = '';
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
    public function openModal(): void
    {
        $this->reset(['concepto', 'tipo', 'monto', 'comprobante', 'descripcion']);
        $this->tipo = 'egreso';
        $this->fecha = now()->toDateString();
        $this->showModal = true;
    }

    public function guardar(): void
    {
        $this->validate(['concepto' => 'required|string|min:3', 'tipo' => 'required', 'monto' => 'required|numeric|min:0.01', 'fecha' => 'required|date']);
        DB::table('caja_chica')->insert(['empresa_id' => $this->empresaId, 'user_id' => auth()->id() ?? 1, 'concepto' => $this->concepto, 'tipo' => $this->tipo, 'monto' => (float) $this->monto, 'fecha' => $this->fecha, 'comprobante' => $this->comprobante ?: null, 'descripcion' => $this->descripcion ?: null, 'created_at' => now(), 'updated_at' => now()]);
        session()->flash('success', 'Movimiento registrado correctamente.');
        $this->showModal = false;
    }

    public function render()
    {
        $query = DB::table('caja_chica')->where('empresa_id', $this->empresaId)->whereBetween('fecha', [$this->fechaDesde, $this->fechaHasta]);
        if ($this->buscar) {
            $query->where('concepto', 'LIKE', "%{$this->buscar}%");
        }
        if ($this->filtroTipo) {
            $query->where('tipo', $this->filtroTipo);
        }
        $ingresos = DB::table('caja_chica')->where('empresa_id', $this->empresaId)->whereBetween('fecha', [$this->fechaDesde, $this->fechaHasta])->where('tipo', 'ingreso')->sum('monto');
        $egresos = DB::table('caja_chica')->where('empresa_id', $this->empresaId)->whereBetween('fecha', [$this->fechaDesde, $this->fechaHasta])->where('tipo', 'egreso')->sum('monto');
        return view('livewire.caja-chica-listado', [
            'movimientos' => $query->orderByDesc('created_at')->paginate(25),
            'ingresos' => $ingresos,
            'egresos' => $egresos,
            'saldo' => $ingresos - $egresos,
        ]);
    }
}
