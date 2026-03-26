<div>
    <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:16px;margin-bottom:24px">
        <div class="card" style="padding:20px">
            <div style="display:flex;align-items:center;gap:12px">
                <div
                    style="width:44px;height:44px;border-radius:10px;background:rgba(0,255,136,0.15);display:flex;align-items:center;justify-content:center">
                    <i data-lucide="trending-up" style="width:22px;height:22px;color:var(--accent)"></i></div>
                <div>
                    <p style="font-size:0.75rem;color:var(--text-muted);margin:0">INGRESOS</p>
                    <p style="font-size:1.4rem;font-weight:800;margin:0;color:var(--accent)">S/
                        {{ number_format($ingresos, 2) }}</p>
                </div>
            </div>
        </div>
        <div class="card" style="padding:20px">
            <div style="display:flex;align-items:center;gap:12px">
                <div
                    style="width:44px;height:44px;border-radius:10px;background:rgba(239,68,68,0.15);display:flex;align-items:center;justify-content:center">
                    <i data-lucide="trending-down" style="width:22px;height:22px;color:#ef4444"></i></div>
                <div>
                    <p style="font-size:0.75rem;color:var(--text-muted);margin:0">EGRESOS</p>
                    <p style="font-size:1.4rem;font-weight:800;margin:0;color:#ef4444">S/
                        {{ number_format($egresos, 2) }}</p>
                </div>
            </div>
        </div>
        <div class="card" style="padding:20px">
            <div style="display:flex;align-items:center;gap:12px">
                <div
                    style="width:44px;height:44px;border-radius:10px;background:rgba(59,130,246,0.15);display:flex;align-items:center;justify-content:center">
                    <i data-lucide="wallet" style="width:22px;height:22px;color:#3b82f6"></i></div>
                <div>
                    <p style="font-size:0.75rem;color:var(--text-muted);margin:0">SALDO</p>
                    <p style="font-size:1.4rem;font-weight:800;margin:0;color:{{ $saldo >= 0 ? '#3b82f6' : '#ef4444' }}">S/
                        {{ number_format($saldo, 2) }}</p>
                </div>
            </div>
        </div>
    </div>
    <div
        style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;flex-wrap:wrap;gap:12px">
        <div style="display:flex;gap:10px;flex-wrap:wrap;align-items:center">
            <div class="search-global" style="width:220px"><i data-lucide="search"></i><input type="text"
                    wire:model.live.debounce.300ms="buscar" placeholder="Buscar concepto..."></div>
            <select wire:model.live="filtroTipo" class="form-select" style="width:140px">
                <option value="">Todos</option>
                <option value="ingreso">Ingresos</option>
                <option value="egreso">Egresos</option>
            </select>
            <div style="display:flex;gap:8px"><input type="date" wire:model.live="fechaDesde" class="form-input"
                    style="width:150px"><input type="date" wire:model.live="fechaHasta" class="form-input"
                    style="width:150px"></div>
        </div>
        <button wire:click="openModal" class="btn btn-primary"><i data-lucide="plus"></i> Nuevo Movimiento</button>
    </div>
    @if(session()->has('success'))
        <div class="alert alert-success"><i data-lucide="check-circle-2"></i><span>{{ session('success') }}</span></div>
    @endif
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Tipo</th>
                    <th>Concepto</th>
                    <th>Comprobante</th>
                    <th style="text-align:right">Monto</th>
                </tr>
            </thead>
            <tbody>@forelse($movimientos as $m)
                <tr>
                    <td style="color:var(--text-muted)">{{ $m->fecha }}</td>
                    <td><span class="badge {{ $m->tipo === 'ingreso' ? 'badge-success' : 'badge-danger' }}"
                            style="font-size:0.7rem">{{ strtoupper($m->tipo) }}</span></td>
                    <td>{{ $m->concepto }}</td>
                    <td style="color:var(--text-muted);font-size:0.8rem">{{ $m->comprobante ?? '—' }}</td>
                    <td
                        style="text-align:right;font-weight:700;color:{{ $m->tipo === 'ingreso' ? 'var(--accent)' : '#ef4444' }}">
                        S/ {{ number_format($m->monto, 2) }}</td>
                </tr>
            @empty<tr>
                    <td colspan="5" style="text-align:center;padding:60px;color:var(--text-muted)"><i
                            data-lucide="wallet" style="width:48px;height:48px;display:block;margin:0 auto 12px"></i>No
                        hay movimientos en el período</td>
                </tr>@endforelse
            </tbody>
        </table>
    </div>
    <div style="margin-top:16px">{{ $movimientos->links() }}</div>
    @if($showModal)
        <div class="modal-backdrop" wire:click.self="$set('showModal',false)">
            <div class="modal" style="max-width:480px">
                <div class="modal-header">
                    <h3 style="font-weight:700">Nuevo Movimiento</h3><button wire:click="$set('showModal',false)"
                        style="background:none;border:none;color:var(--text-muted);cursor:pointer"><i data-lucide="x"
                            style="width:20px;height:20px"></i></button>
                </div>
                <div class="modal-body">
                    <div class="form-group"><label class="form-label">Tipo *</label><select wire:model="tipo"
                            class="form-select">
                            <option value="egreso">Egreso</option>
                            <option value="ingreso">Ingreso</option>
                        </select></div>
                    <div class="form-group"><label class="form-label">Concepto *</label><input type="text"
                            wire:model="concepto" class="form-input" placeholder="Descripción del movimiento"></div>
                    <div class="form-row">
                        <div class="form-group"><label class="form-label">Monto *</label><input type="number"
                                wire:model="monto" class="form-input" step="0.01" min="0.01"></div>
                        <div class="form-group"><label class="form-label">Fecha *</label><input type="date"
                                wire:model="fecha" class="form-input"></div>
                    </div>
                    <div class="form-group"><label class="form-label">Comprobante</label><input type="text"
                            wire:model="comprobante" class="form-input" placeholder="Nº comprobante (opcional)"></div>
                </div>
                <div class="modal-footer"><button wire:click="$set('showModal',false)"
                        class="btn btn-secondary">Cancelar</button><button wire:click="guardar" class="btn btn-primary"><i
                            data-lucide="save"></i> Guardar</button></div>
            </div>
        </div>
    @endif
</div>