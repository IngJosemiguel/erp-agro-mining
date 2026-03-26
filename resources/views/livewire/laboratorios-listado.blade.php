<div>
    <div
        style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;flex-wrap:wrap;gap:12px">
        <div class="search-global" style="width:280px"><i data-lucide="search"></i><input type="text"
                wire:model.live.debounce.300ms="buscar" placeholder="Buscar laboratorio, RUC..."></div>
        <button wire:click="openModal(0)" class="btn btn-primary"><i data-lucide="plus"></i> Nuevo Laboratorio</button>
    </div>
    @if(session()->has('success'))
        <div class="alert alert-success"><i data-lucide="check-circle-2"></i><span>{{ session('success') }}</span></div>
    @endif
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Laboratorio / Fabricante</th>
                    <th>RUC</th>
                    <th>País</th>
                    <th style="text-align:center">Productos</th>
                    <th style="text-align:center">Acciones</th>
                </tr>
            </thead>
            <tbody>@forelse($laboratorios as $l)
                <tr>
                    <td><strong>{{ $l->nombre }}</strong></td>
                    <td style="color:var(--text-muted);font-family:monospace">{{ $l->ruc ?? '—' }}</td>
                    <td style="color:var(--text-muted)">{{ $l->pais ?? '—' }}</td>
                    <td style="text-align:center"><span style="font-weight:700">{{ $l->productos_count }}</span></td>
                    <td style="text-align:center;white-space:nowrap"><button wire:click="openModal({{ $l->id }})"
                            style="background:none;border:none;color:var(--accent);cursor:pointer"><i
                                data-lucide="pencil" style="width:15px;height:15px"></i></button><button
                            wire:click="eliminar({{ $l->id }})" wire:confirm="¿Eliminar este laboratorio?"
                            style="background:none;border:none;color:var(--danger);cursor:pointer;margin-left:8px"><i
                                data-lucide="trash-2" style="width:15px;height:15px"></i></button></td>
                </tr>
            @empty<tr>
                    <td colspan="5" style="text-align:center;padding:60px;color:var(--text-muted)"><i
                            data-lucide="flask-conical"
                            style="width:48px;height:48px;display:block;margin:0 auto 12px"></i>No hay laboratorios
                        registrados</td>
                </tr>@endforelse
            </tbody>
        </table>
    </div>
    <div style="margin-top:16px">{{ $laboratorios->links() }}</div>
    @if($showModal)
        <div class="modal-backdrop" wire:click.self="$set('showModal',false)">
            <div class="modal" style="max-width:440px">
                <div class="modal-header">
                    <h3 style="font-weight:700">{{ $editId ? 'Editar' : 'Nuevo' }} Laboratorio</h3><button
                        wire:click="$set('showModal',false)"
                        style="background:none;border:none;color:var(--text-muted);cursor:pointer"><i data-lucide="x"
                            style="width:20px;height:20px"></i></button>
                </div>
                <div class="modal-body">
                    <div class="form-group"><label class="form-label">Nombre *</label><input type="text" wire:model="nombre"
                            class="form-input" placeholder="Nombre del laboratorio">@error('nombre')<span
                            style="color:var(--danger);font-size:0.75rem">{{ $message }}</span>@enderror</div>
                    <div class="form-row">
                        <div class="form-group"><label class="form-label">RUC</label><input type="text" wire:model="ruc"
                                class="form-input" placeholder="RUC (opcional)"></div>
                        <div class="form-group"><label class="form-label">País</label><input type="text" wire:model="pais"
                                class="form-input"></div>
                    </div>
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