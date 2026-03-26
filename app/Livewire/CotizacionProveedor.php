<?php
namespace App\Livewire;
use Livewire\Component;
use Livewire\WithPagination;
use App\Traits\TieneContextoEmpresa;
use Illuminate\Support\Facades\DB;

class CotizacionProveedor extends Component
{
    use WithPagination, TieneContextoEmpresa;
    public string $buscar = '';
    public string $filtroEstado = '';
    public bool $showModal = false;
    public string $proveedor_id = '';
    public string $fecha = '';
    public string $fecha_validez = '';
    public string $condiciones = '';
    public array $proveedores = [];
    public function mount(): void
    {
        $this->inicializarContexto();
        $this->fecha = now()->toDateString();
        $this->fecha_validez = now()->addDays(15)->toDateString();
        $this->proveedores = DB::table('proveedores')->where('empresa_id', $this->empresaId)->where('activo', true)->select('id', 'razon_social', 'numero_documento')->orderBy('razon_social')->get()->toArray();
    }
    public function openModal(): void
    {
        $this->reset(['proveedor_id', 'condiciones']);
        $this->fecha = now()->toDateString();
        $this->fecha_validez = now()->addDays(15)->toDateString();
        $this->showModal = true;
    }

    public function guardar(): void
    {
        $this->validate([
            'proveedor_id' => 'required',
            'fecha' => 'required|date',
        ], [
            'proveedor_id.required' => 'Selecciona un proveedor.',
        ]);

        $ultimo = DB::table('ordenes_compra')
            ->where('empresa_id', $this->empresaId)
            ->where('estado', 'cotizacion')
            ->whereYear('created_at', now()->year)
            ->count() + 1;
        $codigo = 'COT-' . now()->format('Y') . '-' . str_pad($ultimo, 4, '0', STR_PAD_LEFT);

        DB::table('ordenes_compra')->insert([
            'empresa_id' => $this->empresaId,
            'proveedor_id' => $this->proveedor_id,
            'user_id' => auth()->id() ?? 1,
            'codigo' => $codigo,
            'fecha' => $this->fecha,
            'fecha_entrega_estimada' => $this->fecha_validez ?: null,
            'condiciones' => $this->condiciones ?: null,
            'subtotal' => 0,
            'igv' => 0,
            'total' => 0,
            'moneda' => 'PEN',
            'tipo_cambio' => 1,
            'estado' => 'cotizacion',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        session()->flash('success', "Solicitud {$codigo} enviada correctamente.");
        $this->showModal = false;
        $this->reset(['proveedor_id', 'condiciones']);
        $this->fecha = now()->toDateString();
        $this->fecha_validez = now()->addDays(15)->toDateString();
    }

    public function render()
    {
        // Cotizaciones a proveedores = solicitudes de precio/cotización de compra
        $query = DB::table('ordenes_compra')
            ->leftJoin('proveedores', 'ordenes_compra.proveedor_id', '=', 'proveedores.id')
            ->where('ordenes_compra.empresa_id', $this->empresaId)
            ->where('ordenes_compra.estado', 'cotizacion')
            ->select('ordenes_compra.*', 'proveedores.razon_social as proveedor');
        if ($this->buscar) {
            $query->where(fn($q) => $q->where('ordenes_compra.codigo', 'LIKE', "%{$this->buscar}%")->orWhere('proveedores.razon_social', 'LIKE', "%{$this->buscar}%"));
        }
        $total = DB::table('ordenes_compra')->where('empresa_id', $this->empresaId)->where('estado', 'cotizacion')->count();
        $pendientes = DB::table('ordenes_compra')->where('empresa_id', $this->empresaId)->where('estado', 'cotizacion')->where('fecha_entrega_estimada', '>=', now()->toDateString())->count();
        return view('livewire.cotizacion-proveedor', [
            'cotizaciones' => $query->orderByDesc('created_at')->paginate(20),
            'total' => $total,
            'pendientes' => $pendientes,
        ]);
    }
}
