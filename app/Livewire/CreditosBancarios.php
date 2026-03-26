<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\CreditoBancario;
use App\Traits\TieneContextoEmpresa;

class CreditosBancarios extends Component
{
    use WithPagination, TieneContextoEmpresa;

    public string $buscar = '';
    public string $filtroEstado = '';
    public bool $showModal = false;
    public ?int $editingId = null;

    public string $entidad_bancaria = '';
    public string $numero_credito = '';
    public string $fecha_desembolso = '';
    public string $fecha_vencimiento = '';
    public string $monto_credito = '0';
    public string $tasa_interes = '0';
    public string $cuotas = '1';
    public string $cuota_mensual = '0';
    public string $destino = '';
    public string $observaciones = '';

    public function mount(): void
    {
        $this->inicializarContexto();
        $this->fecha_desembolso = now()->toDateString();
    }
    public function updatingBuscar(): void
    {
        $this->resetPage();
    }

    public function updatedMontoCreditoOrCuotas(): void
    {
        $this->calcularCuota();
    }
    public function updatedMontoCredito(): void
    {
        $this->calcularCuota();
    }
    public function updatedCuotas(): void
    {
        $this->calcularCuota();
    }
    public function updatedTasaInteres(): void
    {
        $this->calcularCuota();
    }

    protected function calcularCuota(): void
    {
        $monto = (float) $this->monto_credito;
        $cuotas = max(1, (int) $this->cuotas);
        $tasa = (float) $this->tasa_interes;
        if ($tasa > 0) {
            $r = $tasa / 100 / 12;
            $this->cuota_mensual = (string) round($monto * $r * pow(1 + $r, $cuotas) / (pow(1 + $r, $cuotas) - 1), 2);
        } else {
            $this->cuota_mensual = (string) round($monto / $cuotas, 2);
        }
    }

    public function openModal(?int $id = null): void
    {
        $this->resetForm();
        if ($id) {
            $c = CreditoBancario::find($id);
            if ($c) {
                $this->editingId = $c->id;
                $this->entidad_bancaria = $c->entidad_bancaria;
                $this->numero_credito = $c->numero_credito;
                $this->fecha_desembolso = $c->fecha_desembolso->toDateString();
                $this->fecha_vencimiento = $c->fecha_vencimiento?->toDateString() ?? '';
                $this->monto_credito = (string) $c->monto_credito;
                $this->tasa_interes = (string) $c->tasa_interes;
                $this->cuotas = (string) $c->cuotas;
                $this->cuota_mensual = (string) $c->cuota_mensual;
                $this->destino = $c->destino ?? '';
                $this->observaciones = $c->observaciones ?? '';
            }
        }
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate([
            'entidad_bancaria' => 'required|string|max:255',
            'numero_credito' => 'required|string|max:50',
            'fecha_desembolso' => 'required|date',
            'monto_credito' => 'required|numeric|min:0.01',
            'cuotas' => 'required|integer|min:1',
        ]);

        $data = [
            'empresa_id' => $this->empresaId,
            'user_id' => auth()->id() ?? 1,
            'entidad_bancaria' => $this->entidad_bancaria,
            'numero_credito' => $this->numero_credito,
            'fecha_desembolso' => $this->fecha_desembolso,
            'fecha_vencimiento' => $this->fecha_vencimiento ?: null,
            'monto_credito' => (float) $this->monto_credito,
            'tasa_interes' => (float) $this->tasa_interes,
            'cuotas' => (int) $this->cuotas,
            'cuota_mensual' => (float) $this->cuota_mensual,
            'saldo_pendiente' => (float) $this->monto_credito,
            'destino' => $this->destino ?: null,
            'observaciones' => $this->observaciones ?: null,
        ];

        if ($this->editingId) {
            CreditoBancario::findOrFail($this->editingId)->update($data);
            session()->flash('success', 'Crédito actualizado.');
        } else {
            CreditoBancario::create($data);
            session()->flash('success', 'Crédito bancario registrado.');
        }
        $this->showModal = false;
    }

    public function cambiarEstado(int $id, string $estado): void
    {
        $c = CreditoBancario::find($id);
        if ($c) {
            $c->update(['estado' => $estado]);
            session()->flash('success', "Crédito marcado como {$estado}.");
        }
    }

    protected function resetForm(): void
    {
        $this->editingId = null;
        $this->entidad_bancaria = '';
        $this->numero_credito = '';
        $this->fecha_desembolso = now()->toDateString();
        $this->fecha_vencimiento = '';
        $this->monto_credito = '0';
        $this->tasa_interes = '0';
        $this->cuotas = '1';
        $this->cuota_mensual = '0';
        $this->destino = '';
        $this->observaciones = '';
    }

    public function render()
    {
        $query = CreditoBancario::where('empresa_id', $this->empresaId);
        if ($this->buscar) {
            $query->where(fn($q) => $q->where('entidad_bancaria', 'LIKE', "%{$this->buscar}%")->orWhere('numero_credito', 'LIKE', "%{$this->buscar}%"));
        }
        if ($this->filtroEstado) {
            $query->where('estado', $this->filtroEstado);
        }

        $totalVigente = CreditoBancario::where('empresa_id', $this->empresaId)->where('estado', 'vigente')->sum('saldo_pendiente');
        $cantVigentes = CreditoBancario::where('empresa_id', $this->empresaId)->where('estado', 'vigente')->count();

        return view('livewire.creditos-bancarios', [
            'creditos' => $query->orderByDesc('fecha_desembolso')->paginate(20),
            'totalVigente' => $totalVigente,
            'cantVigentes' => $cantVigentes,
        ]);
    }
}
