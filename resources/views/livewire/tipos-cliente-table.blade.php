<div>
    <div
        style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;flex-wrap:wrap;gap:12px">
        <div class="search-global" style="width:300px">
            <i data-lucide="search"></i>
            <input type="text" wire:model.live.debounce.300ms="buscar" placeholder="Buscar tipo de cliente...">
        </div>
        <button wire:click="openCreateModal" class="btn btn-primary">
            <i data-lucide="plus"></i> Nuevo Tipo
        </button>
    </div>

    @if(session()->has('success'))
        <div class="alert alert-success"><i data-lucide="check-circle-2"></i><span>{{ session('success') }}</span></div>
    @endif

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th style="text-align:right">Descuento Default (%)</th>
                    <th style="text-align:center">Estado</th>
                    <th style="text-align:center">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($tipos as $tipo)
                    <tr>
                        <td style="color:var(--text-muted)">{{ $tipo->id }}</td>
                        <td><strong>{{ $tipo->nombre }}</strong></td>
                        <td style="text-align:right;font-weight:600">{{ number_format($tipo->descuento_default, 2) }}%</td>
                        <td style="text-align:center">
                            <button wire:click="toggleActivo({{ $tipo->id }})"
                                class="badge {{ $tipo->activo ? 'badge-success' : 'badge-danger' }}"
                                style="cursor:pointer;border:none">
                                {{ $tipo->activo ? 'Activo' : 'Inactivo' }}
                            </button>
                        </td>
                        <td style="text-align:center">
                            <div style="display:flex;gap:4px;justify-content:center">
                                <button wire:click="openEditModal({{ $tipo->id }})" class="btn btn-sm btn-secondary"><i
                                        data-lucide="pencil"></i></button>
                                <button wire:click="delete({{ $tipo->id }})" wire:confirm="¿Eliminar este tipo?"
                                    class="btn btn-sm btn-danger"><i data-lucide="trash-2"></i></button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" style="text-align:center;padding:60px;color:var(--text-muted)">
                            <i data-lucide="user-check" style="width:48px;height:48px;display:block;margin:0 auto 12px"></i>
                            No se encontraron tipos de cliente
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div style="margin-top:16px">{{ $tipos->links() }}</div>

    @if($showModal)
        <div class="modal-backdrop" wire:click.self="$set('showModal', false)">
            <div class="modal" style="max-width:480px">
                <div class="modal-header">
                    <h3 style="font-weight:700">{{ $editMode ? 'Editar Tipo Cliente' : 'Nuevo Tipo Cliente' }}</h3>
                    <button wire:click="$set('showModal', false)"
                        style="background:none;border:none;color:var(--text-muted);cursor:pointer">
                        <i data-lucide="x" style="width:20px;height:20px"></i>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label class="form-label">Nombre *</label>
                        <input type="text" wire:model="nombre" class="form-input"
                            placeholder="Ej: Mayorista, Minorista, VIP">
                        @error('nombre') <span style="color:var(--danger);font-size:0.75rem">{{ $message }}</span> @enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label">Descuento por Defecto (%) *</label>
                        <input type="number" wire:model="descuento_default" class="form-input" step="0.01" min="0"
                            max="100">
                        @error('descuento_default') <span
                        style="color:var(--danger);font-size:0.75rem">{{ $message }}</span> @enderror
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