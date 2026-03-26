<div>
    <div
        style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;flex-wrap:wrap;gap:12px">
        <div style="display:flex;gap:10px;flex-wrap:wrap">
            <div class="search-global" style="width:240px"><i data-lucide="search"></i><input type="text"
                    wire:model.live.debounce.300ms="buscar" placeholder="Buscar código, descripción..."></div>
            <select wire:model.live="filtroAlmacen" class="form-select" style="width:180px">
                <option value="">Todos los almacenes</option>@foreach($almacenes as $a)<option value="{{ $a->id }}">
                {{ $a->nombre }}</option>@endforeach
            </select>
        </div>
        <div style="display:flex;align-items:center;gap:12px">
            <div class="card" style="padding:10px 16px;display:inline-flex;align-items:center;gap:8px"><i
                    data-lucide="map-pin" style="width:16px;height:16px;color:var(--accent)"></i><span
                    style="font-weight:700;color:var(--accent)">{{ $total }}</span><span
                    style="font-size:0.8rem;color:var(--text-muted)">ubicaciones activas</span></div>
            <button wire:click="openModal(0)" class="btn btn-primary"><i data-lucide="plus"></i> Nueva
                Ubicación</button>
        </div>
    </div>
    @if(session()->has('success'))
        <div class="alert alert-success"><i data-lucide="check-circle-2"></i><span>{{ session('success') }}</span></div>
    @endif
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Código</th>
                    <th>Descripción</th>
                    <th>Almacén</th>
                    <th style="text-align:center">Estado</th>
                    <th style="text-align:center">Acciones</th>
                </tr>
            </thead>
            <tbody>@forelse($ubicaciones as $u)
                <tr>
                    <td><strong style="font-family:monospace;color:var(--accent)">{{ $u->codigo }}</strong></td>
                    <td>{{ $u->descripcion ?? '—' }}</td>
                    <td>{{ $u->almacen }}</td>
                    <td style="text-align:center"><span class="badge {{ $u->activo ? 'badge-success' : 'badge-neutral' }}"
                            style="font-size:0.65rem">{{ $u->activo ? 'ACTIVO' : 'INACTIVO' }}</span></td>
                    <td style="text-align:center;white-space:nowrap"><button wire:click="openModal({{ $u->id }})"
                            style="background:none;border:none;color:var(--accent);cursor:pointer"><i
                                data-lucide="pencil" style="width:15px;height:15px"></i></button><button
                            wire:click="eliminar({{ $u->id }})" wire:confirm="¿Eliminar ubicación?"
                            style="background:none;border:none;color:var(--danger);cursor:pointer;margin-left:8px"><i
                                data-lucide="trash-2" style="width:15px;height:15px"></i></button></td>
                </tr>
            @empty<tr>
                    <td colspan="5" style="text-align:center;padding:60px;color:var(--text-muted)"><i
                            data-lucide="map-pin" style="width:48px;height:48px;display:block;margin:0 auto 12px"></i>No
                        hay ubicaciones registradas</td>
                </tr>@endforelse
            </tbody>
        </table>
    </div>
    <div style="margin-top:16px">{{ $ubicaciones->links() }}</div>
    @if($showModal)
        <div class="modal-backdrop" wire:click.self="$set('showModal',false)">
            <div class="modal" style="max-width:440px">
                <div class="modal-header">
                    <h3 style="font-weight:700">{{ $editId ? 'Editar' : 'Nueva' }} Ubicación</h3><button
                        wire:click="$set('showModal',false)"
                        style="background:none;border:none;color:var(--text-muted);cursor:pointer"><i data-lucide="x"
                            style="width:20px;height:20px"></i></button>
                </div>
                <div class="modal-body">
                    <div class="form-group"><label class="form-label">Almacén *</label><select wire:model="almacen_id"
                            class="form-select">
                            <option value="">— Seleccionar —</option>@foreach($almacenes as $a)<option value="{{ $a->id }}">
                            {{ $a->nombre }}</option>@endforeach
                        </select></div>
                    <div class="form-group"><label class="form-label">Código *</label><input type="text" wire:model="codigo"
                            class="form-input" placeholder="Ej: A-01, RACK-B2">@error('codigo')<span
                            style="color:var(--danger);font-size:0.75rem">{{ $message }}</span>@enderror</div>
                    <div class="form-group"><label class="form-label">Descripción</label><input type="text"
                            wire:model="descripcion" class="form-input" placeholder="Pasillo A, Estante 1..."></div>
                    <div class="form-group"><label style="display:flex;align-items:center;gap:8px;cursor:pointer"><input
                                type="checkbox" wire:model="activo"> Activo</label></div>
                </div>
                <div class="modal-footer"><button wire:click="$set('showModal',false)"
                        class="btn btn-secondary">Cancelar</button><button wire:click="guardar" class="btn btn-primary"><i
                            data-lucide="save"></i> Guardar</button></div>
            </div>
        </div>
    @endif
</div>