<div>
    @if(session('success'))
        <div class="alert alert-success" style="margin-bottom:16px"><i data-lucide="check-circle-2"></i>
            {{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-error" style="margin-bottom:16px"><i data-lucide="alert-triangle"></i>
            {{ session('error') }}</div>
    @endif

    <div
        style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:16px;margin-bottom:24px">
        <div class="search-global" style="width:300px">
            <i data-lucide="search"></i>
            <input type="text" wire:model.live.debounce.300ms="buscar" placeholder="Buscar por código o nombre...">
        </div>
        <button wire:click="openCreate" class="btn btn-primary">
            <i data-lucide="plus"></i> Nuevo Centro de Costo
        </button>
    </div>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th style="width:120px">Código</th>
                    <th>Nombre</th>
                    <th>Descripción</th>
                    <th style="text-align:center;width:100px">Estado</th>
                    <th style="text-align:center;width:120px">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($centros as $c)
                    <tr style="{{ !$c->activo ? 'opacity:0.6' : '' }}">
                        <td><code
                                style="background:var(--bg-elevated);color:var(--primary);padding:2px 6px;border-radius:4px">{{ $c->codigo }}</code>
                        </td>
                        <td style="font-weight:600">{{ $c->nombre }}</td>
                        <td style="color:var(--text-muted);font-size:0.85rem">{{ $c->descripcion ?: '—' }}</td>
                        <td style="text-align:center">
                            <button wire:click="toggleActivo({{ $c->id }})"
                                class="badge {{ $c->activo ? 'badge-success' : 'badge-secondary' }}"
                                style="border:none;cursor:pointer">
                                {{ $c->activo ? 'Activo' : 'Inactivo' }}
                            </button>
                        </td>
                        <td style="text-align:center">
                            <div class="actions">
                                <button wire:click="openEdit({{ $c->id }})" class="btn-icon" title="Editar"><i
                                        data-lucide="edit-2"></i></button>
                                <button
                                    onclick="if(confirm('¿Eliminar Centro de Costo?')) @this.call('delete', {{ $c->id }})"
                                    class="btn-icon danger" title="Eliminar"><i data-lucide="trash-2"></i></button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" style="text-align:center;padding:40px;color:var(--text-muted)">
                            <i data-lucide="layers"
                                style="width:40px;height:40px;margin:0 auto 12px;opacity:0.5;display:block"></i>
                            No se encontraron centros de costo.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div style="margin-top:16px">{{ $centros->links() }}</div>

    @if($showModal)
        <div class="modal-backdrop" wire:click.self="$set('showModal', false)">
            <div class="modal" style="max-width:460px">
                <div class="modal-header">
                    <h3 style="font-weight:700;display:flex;align-items:center;gap:8px">
                        <i data-lucide="layers" style="color:var(--primary);width:18px"></i>
                        {{ $editMode ? 'Editar Centro de Costo' : 'Nuevo Centro de Costo' }}
                    </h3>
                    <button wire:click="$set('showModal', false)"
                        style="background:none;border:none;color:var(--text-muted);cursor:pointer"><i
                            data-lucide="x"></i></button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label class="form-label">Código</label>
                        <input type="text" wire:model="codigo" class="form-input" placeholder="Ej: CC-001">
                        @error('codigo') <span style="color:var(--danger);font-size:0.75rem">{{ $message }}</span> @enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label">Nombre del Centro</label>
                        <input type="text" wire:model="nombre" class="form-input"
                            placeholder="Ej: Proyecto Minero Sur / Dpto. Logística">
                        @error('nombre') <span style="color:var(--danger);font-size:0.75rem">{{ $message }}</span> @enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label">Descripción (Opcional)</label>
                        <textarea wire:model="descripcion" class="form-textarea" rows="2"
                            placeholder="Detalle de su alcance..."></textarea>
                        @error('descripcion') <span style="color:var(--danger);font-size:0.75rem">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label" style="display:flex;align-items:center;gap:8px;cursor:pointer">
                            <input type="checkbox" wire:model="activo" style="width:16px;height:16px">
                            Centro de Costo Activo
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button wire:click="$set('showModal', false)" class="btn btn-secondary">Cancelar</button>
                    <button wire:click="save" class="btn btn-primary"><i data-lucide="save"></i>
                        {{ $editMode ? 'Guardar Cambios' : 'Registrar Centro' }}</button>
                </div>
            </div>
        </div>
    @endif
</div>