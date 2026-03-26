<div>
    <div
        style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;flex-wrap:wrap;gap:12px">
        <div class="search-global" style="width:300px">
            <i data-lucide="search"></i>
            <input type="text" wire:model.live.debounce.300ms="buscar" placeholder="Buscar por RUC o razón social...">
        </div>
        <button wire:click="openCreateModal" class="btn btn-primary">
            <i data-lucide="plus"></i> Nuevo Proveedor
        </button>
    </div>

    @if(session()->has('success'))
        <div class="alert alert-success"><i data-lucide="check-circle-2"></i><span>{{ session('success') }}</span></div>
    @endif

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Documento</th>
                    <th>Razón Social</th>
                    <th>Contacto</th>
                    <th>Teléfono</th>
                    <th>Email</th>
                    <th style="text-align:center">Días Crédito</th>
                    <th style="text-align:center">Estado</th>
                    <th style="text-align:center">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($proveedores as $prov)
                    <tr>
                        <td>
                            <span class="badge badge-neutral">{{ $prov->tipo_documento }}</span>
                            <strong style="margin-left:6px">{{ $prov->numero_documento }}</strong>
                        </td>
                        <td>
                            <strong>{{ \Str::limit($prov->razon_social, 35) }}</strong>
                            @if($prov->nombre_comercial)
                                <br><span style="font-size:0.75rem;color:var(--text-muted)">{{ $prov->nombre_comercial }}</span>
                            @endif
                        </td>
                        <td style="color:var(--text-muted)">{{ $prov->contacto_nombre ?: '—' }}</td>
                        <td style="color:var(--text-muted)">{{ $prov->telefono ?: '—' }}</td>
                        <td style="color:var(--text-muted)">{{ $prov->email ?: '—' }}</td>
                        <td style="text-align:center;font-weight:600">{{ $prov->dias_credito }}</td>
                        <td style="text-align:center">
                            <button wire:click="toggleActivo({{ $prov->id }})"
                                class="badge {{ $prov->activo ? 'badge-success' : 'badge-danger' }}"
                                style="cursor:pointer;border:none">
                                {{ $prov->activo ? 'Activo' : 'Inactivo' }}
                            </button>
                        </td>
                        <td style="text-align:center">
                            <div style="display:flex;gap:4px;justify-content:center">
                                <button wire:click="openEditModal({{ $prov->id }})" class="btn btn-sm btn-secondary"><i
                                        data-lucide="pencil"></i></button>
                                <button wire:click="delete({{ $prov->id }})" wire:confirm="¿Eliminar este proveedor?"
                                    class="btn btn-sm btn-danger"><i data-lucide="trash-2"></i></button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" style="text-align:center;padding:60px;color:var(--text-muted)">
                            <i data-lucide="truck" style="width:48px;height:48px;display:block;margin:0 auto 12px"></i>
                            No se encontraron proveedores
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div style="margin-top:16px">{{ $proveedores->links() }}</div>

    @if($showModal)
        <div class="modal-backdrop" wire:click.self="$set('showModal', false)">
            <div class="modal" style="max-width:680px">
                <div class="modal-header">
                    <h3 style="font-weight:700">{{ $editMode ? 'Editar Proveedor' : 'Nuevo Proveedor' }}</h3>
                    <button wire:click="$set('showModal', false)"
                        style="background:none;border:none;color:var(--text-muted);cursor:pointer">
                        <i data-lucide="x" style="width:20px;height:20px"></i>
                    </button>
                </div>
                <div class="modal-body" style="max-height:70vh;overflow-y:auto">
                    {{-- Sección: Identificación --}}
                    <p
                        style="font-weight:700;font-size:12px;text-transform:uppercase;color:var(--accent);margin:0 0 12px 0;letter-spacing:1px">
                        Identificación</p>
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Tipo Doc. *</label>
                            <select wire:model="tipo_documento" class="form-select">
                                <option value="RUC">RUC</option>
                                <option value="DNI">DNI</option>
                                <option value="CE">CE</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">N° Documento *</label>
                            <input type="text" wire:model="numero_documento" class="form-input" placeholder="20100000001">
                            @error('numero_documento') <span
                            style="color:var(--danger);font-size:0.75rem">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Razón Social *</label>
                        <input type="text" wire:model="razon_social" class="form-input">
                        @error('razon_social') <span style="color:var(--danger);font-size:0.75rem">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label">Nombre Comercial</label>
                        <input type="text" wire:model="nombre_comercial" class="form-input">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Dirección</label>
                        <input type="text" wire:model="direccion" class="form-input">
                    </div>

                    {{-- Sección: Contacto --}}
                    <p
                        style="font-weight:700;font-size:12px;text-transform:uppercase;color:var(--accent);margin:20px 0 12px 0;letter-spacing:1px">
                        Contacto</p>
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Teléfono</label>
                            <input type="text" wire:model="telefono" class="form-input">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Email</label>
                            <input type="email" wire:model="email" class="form-input">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Nombre Contacto</label>
                            <input type="text" wire:model="contacto_nombre" class="form-input">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Teléfono Contacto</label>
                            <input type="text" wire:model="contacto_telefono" class="form-input">
                        </div>
                    </div>

                    {{-- Sección: Bancario --}}
                    <p
                        style="font-weight:700;font-size:12px;text-transform:uppercase;color:var(--accent);margin:20px 0 12px 0;letter-spacing:1px">
                        Datos Bancarios</p>
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Banco</label>
                            <input type="text" wire:model="banco" class="form-input" placeholder="BCP, BBVA, Interbank...">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Cuenta Bancaria</label>
                            <input type="text" wire:model="cuenta_bancaria" class="form-input">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">CCI</label>
                            <input type="text" wire:model="cuenta_cci" class="form-input">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Cuenta Detracción</label>
                            <input type="text" wire:model="cuenta_detraccion" class="form-input">
                        </div>
                    </div>

                    {{-- Sección: Comercial --}}
                    <p
                        style="font-weight:700;font-size:12px;text-transform:uppercase;color:var(--accent);margin:20px 0 12px 0;letter-spacing:1px">
                        Condiciones Comerciales</p>
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Días de Crédito</label>
                            <input type="number" wire:model="dias_credito" class="form-input" min="0">
                        </div>
                        <div class="form-group" style="display:flex;align-items:flex-end">
                            <label style="display:flex;align-items:center;gap:8px;cursor:pointer;padding:10px 0">
                                <input type="checkbox" wire:model="activo"
                                    style="width:18px;height:18px;accent-color:var(--accent)">
                                <span class="form-label" style="margin:0">Activo</span>
                            </label>
                        </div>
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