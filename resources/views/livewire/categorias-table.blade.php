<div>
    <div
        style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;flex-wrap:wrap;gap:12px">
        <div style="display:flex;gap:10px;flex-wrap:wrap;align-items:center">
            <div class="search-global" style="width:300px">
                <i data-lucide="search"></i>
                <input type="text" wire:model.live.debounce.300ms="buscar" placeholder="Buscar categoría...">
            </div>
        </div>
        <button wire:click="openCreateModal" class="btn btn-primary">
            <i data-lucide="plus"></i> Nueva Categoría
        </button>
    </div>

    @if(session()->has('success'))
        <div class="alert alert-success"><i data-lucide="check-circle-2"></i><span>{{ session('success') }}</span></div>
    @endif
    @if(session()->has('error'))
        <div class="alert alert-error"><i data-lucide="alert-circle"></i><span>{{ session('error') }}</span></div>
    @endif

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Categoría Padre</th>
                    <th>Productos</th>
                    <th style="text-align:center">Orden</th>
                    <th style="text-align:center">Estado</th>
                    <th style="text-align:center">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($categorias as $cat)
                    <tr>
                        <td>
                            <strong>{{ $cat->nombre }}</strong>
                            @if($cat->descripcion)
                                <br><span
                                    style="font-size:0.75rem;color:var(--text-muted)">{{ \Str::limit($cat->descripcion, 50) }}</span>
                            @endif
                        </td>
                        <td style="color:var(--text-muted)">{{ $cat->parent->nombre ?? '—' }}</td>
                        <td><span class="badge badge-neutral">{{ $cat->productos_count }}</span></td>
                        <td style="text-align:center;color:var(--text-muted)">{{ $cat->orden }}</td>
                        <td style="text-align:center">
                            <button wire:click="toggleActivo({{ $cat->id }})"
                                class="badge {{ $cat->activo ? 'badge-success' : 'badge-danger' }}"
                                style="cursor:pointer;border:none">
                                {{ $cat->activo ? 'Activo' : 'Inactivo' }}
                            </button>
                        </td>
                        <td style="text-align:center">
                            <div style="display:flex;gap:4px;justify-content:center">
                                <button wire:click="openEditModal({{ $cat->id }})" class="btn btn-sm btn-secondary"><i
                                        data-lucide="pencil"></i></button>
                                <button wire:click="delete({{ $cat->id }})" wire:confirm="¿Eliminar esta categoría?"
                                    class="btn btn-sm btn-danger"><i data-lucide="trash-2"></i></button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" style="text-align:center;padding:60px;color:var(--text-muted)">
                            <i data-lucide="folder-open"
                                style="width:48px;height:48px;display:block;margin:0 auto 12px"></i>
                            No se encontraron categorías
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div style="margin-top:16px">{{ $categorias->links() }}</div>

    @if($showModal)
        <div class="modal-backdrop" wire:click.self="$set('showModal', false)">
            <div class="modal">
                <div class="modal-header">
                    <h3 style="font-weight:700">{{ $editMode ? 'Editar Categoría' : 'Nueva Categoría' }}</h3>
                    <button wire:click="$set('showModal', false)"
                        style="background:none;border:none;color:var(--text-muted);cursor:pointer">
                        <i data-lucide="x" style="width:20px;height:20px"></i>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label class="form-label">Nombre *</label>
                        <input type="text" wire:model="nombre" class="form-input" placeholder="Ej: Fertilizantes">
                        @error('nombre') <span style="color:var(--danger);font-size:0.75rem">{{ $message }}</span> @enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label">Descripción</label>
                        <input type="text" wire:model="descripcion" class="form-input">
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Categoría Padre</label>
                            <select wire:model="parent_id" class="form-select">
                                <option value="">— Ninguna (Raíz) —</option>
                                @foreach($categoriasPadre as $cp)
                                    <option value="{{ $cp->id }}">{{ $cp->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Orden</label>
                            <input type="number" wire:model="orden" class="form-input" min="0">
                        </div>
                    </div>

                    <div class="form-group">
                        <label style="display:flex;align-items:center;gap:8px;cursor:pointer">
                            <input type="checkbox" wire:model="activo"
                                style="width:18px;height:18px;accent-color:var(--accent)">
                            <span class="form-label" style="margin:0">Activo</span>
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button wire:click="$set('showModal', false)" class="btn btn-secondary">Cancelar</button>
                    <button wire:click="save" class="btn btn-primary">
                        <i data-lucide="save"></i> {{ $editMode ? 'Actualizar' : 'Guardar' }}
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>