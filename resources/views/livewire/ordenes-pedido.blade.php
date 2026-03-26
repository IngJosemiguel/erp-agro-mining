<div>
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:16px;margin-bottom:24px">
        <div class="card" style="padding:20px">
            <div style="display:flex;align-items:center;gap:12px">
                <div
                    style="width:44px;height:44px;border-radius:10px;background:rgba(0,255,136,0.15);display:flex;align-items:center;justify-content:center">
                    <i data-lucide="clipboard-list" style="width:22px;height:22px;color:var(--accent)"></i>
                </div>
                <div>
                    <p style="font-size:0.75rem;color:var(--text-muted);margin:0">TOTAL ÓRDENES</p>
                    <p style="font-size:1.4rem;font-weight:800;margin:0;color:var(--accent)">S/
                        {{ number_format($totalOP, 2) }}
                    </p>
                </div>
            </div>
        </div>
        <div class="card" style="padding:20px">
            <div style="display:flex;align-items:center;gap:12px">
                <div
                    style="width:44px;height:44px;border-radius:10px;background:rgba(245,158,11,0.15);display:flex;align-items:center;justify-content:center">
                    <i data-lucide="clock" style="width:22px;height:22px;color:#f59e0b"></i>
                </div>
                <div>
                    <p style="font-size:0.75rem;color:var(--text-muted);margin:0">PENDIENTES</p>
                    <p style="font-size:1.4rem;font-weight:800;margin:0;color:#f59e0b">{{ $pendientes }}</p>
                </div>
            </div>
        </div>
    </div>
    <div
        style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;flex-wrap:wrap;gap:12px">
        <div style="display:flex;gap:10px">
            <div class="search-global" style="width:260px"><i data-lucide="search"></i><input type="text"
                    wire:model.live.debounce.300ms="buscar" placeholder="Buscar código, cliente..."></div>
            <select wire:model.live="filtroEstado" class="form-select" style="width:150px">
                <option value="">Todos</option>
                <option value="pendiente">Pendiente</option>
                <option value="aprobado">Aprobado</option>
                <option value="completado">Completado</option>
                <option value="anulado">Anulado</option>
            </select>
        </div>
        <button wire:click="openModal" class="btn btn-primary"><i data-lucide="plus"></i> Nueva Orden</button>
    </div>
    @if(session()->has('success'))
        <div class="alert alert-success"><i data-lucide="check-circle-2"></i><span>{{ session('success') }}</span></div>
    @endif
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Código</th>
                    <th>Cliente</th>
                    <th>Fecha</th>
                    <th>F. Entrega</th>
                    <th style="text-align:right">Total</th>
                    <th style="text-align:center">Estado</th>
                </tr>
            </thead>
            <tbody>@forelse($ordenes as $o)
                <tr>
                    <td><strong style="font-family:monospace;color:var(--accent)">{{ $o->codigo }}</strong></td>
                    <td>{{ \Str::limit($o->cliente ?? '—', 35) }}</td>
                    <td style="color:var(--text-muted)">{{ $o->fecha }}</td>
                    <td style="color:var(--text-muted)">{{ $o->fecha_entrega ?? '—' }}</td>
                    <td style="text-align:right;font-weight:700">S/ {{ number_format($o->total, 2) }}</td>
                    <td style="text-align:center"><span
                            class="badge {{ match ($o->estado) { 'completado' => 'badge-success', 'aprobado' => 'badge-warning', 'anulado' => 'badge-danger', default => 'badge-neutral'} }}"
                            style="font-size:0.65rem">{{ strtoupper($o->estado) }}</span></td>
                </tr>
            @empty<tr>
                    <td colspan="6" style="text-align:center;padding:60px;color:var(--text-muted)"><i
                            data-lucide="clipboard-list"
                            style="width:48px;height:48px;display:block;margin:0 auto 12px"></i>No hay órdenes de pedido
                    </td>
                </tr>@endforelse
            </tbody>
        </table>
    </div>
    <div style="margin-top:16px">{{ $ordenes->links() }}</div>
    @if($showModal)
        <div class="modal-backdrop" wire:click.self="$set('showModal',false)">
            <div class="modal" style="max-width:560px">
                <div class="modal-header">
                    <h3 style="font-weight:700">Nueva Orden de Pedido</h3><button wire:click="$set('showModal',false)"
                        style="background:none;border:none;color:var(--text-muted);cursor:pointer"><i data-lucide="x"
                            style="width:20px;height:20px"></i></button>
                </div>
                <div class="modal-body">
                    <div class="form-group"><label class="form-label">Cliente *</label><select wire:model="cliente_id"
                            class="form-select">
                            <option value="">— Seleccionar —</option>@foreach($clientes as $c)<option value="{{ $c->id }}">
                                {{ $c->razon_social }}
                            </option>@endforeach
                        </select></div>
                    <div class="form-row">
                        <div class="form-group"><label class="form-label">Fecha *</label><input type="date"
                                wire:model="fecha" class="form-input"></div>
                        <div class="form-group"><label class="form-label">Fecha Entrega</label><input type="date"
                                wire:model="fecha_entrega" class="form-input"></div>
                    </div>
                    <div class="form-group"><label class="form-label">Condiciones</label><textarea wire:model="condiciones"
                            class="form-input" rows="2"></textarea></div>
                </div>
                <div class="modal-footer">
                    <button wire:click="$set('showModal',false)" class="btn btn-secondary">Cancelar</button>
                    <button wire:click="guardar" wire:loading.attr="disabled" wire:target="guardar" class="btn btn-primary">
                        <span wire:loading.remove wire:target="guardar">
                            <i data-lucide="save"
                                style="width:15px;height:15px;display:inline;vertical-align:middle;margin-right:4px;"></i>
                            Guardar Orden
                        </span>
                        <span wire:loading wire:target="guardar">Guardando...</span>
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>