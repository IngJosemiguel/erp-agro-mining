<div>
    <div
        style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;flex-wrap:wrap;gap:12px">
        <div style="display:flex;gap:10px;flex-wrap:wrap;align-items:center">
            <div class="search-global" style="width:300px">
                <i data-lucide="search"></i>
                <input type="text" wire:model.live.debounce.300ms="buscar"
                    placeholder="Buscar por nombre o documento...">
            </div>
            <select wire:model.live="tipoClienteFiltro" class="form-select" style="width:180px">
                <option value="">Todos los tipos</option>
                @foreach($tiposCliente as $tc)
                    <option value="{{ $tc->id }}">{{ $tc->nombre }}</option>
                @endforeach
            </select>
        </div>
        <button wire:click="openCreateModal" class="btn btn-primary">
            <i data-lucide="user-plus"></i> Nuevo Cliente
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
                    <th>Tipo</th>
                    <th>Teléfono</th>
                    <th>Email</th>
                    <th style="text-align:right">Límite Crédito</th>
                    <th style="text-align:center">Estado</th>
                    <th style="text-align:center">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($clientes as $cliente)
                    <tr>
                        <td>
                            <span class="badge badge-neutral">{{ $cliente->tipo_documento }}</span>
                            <strong style="margin-left:6px">{{ $cliente->numero_documento }}</strong>
                        </td>
                        <td>
                            <strong>{{ \Str::limit($cliente->razon_social, 40) }}</strong>
                            @if($cliente->nombre_comercial)
                                <br><span
                                    style="font-size:0.75rem;color:var(--text-muted)">{{ $cliente->nombre_comercial }}</span>
                            @endif
                        </td>
                        <td style="color:var(--text-muted)">{{ $cliente->tipoCliente->nombre ?? '—' }}</td>
                        <td style="color:var(--text-muted)">{{ $cliente->telefono ?: $cliente->celular ?: '—' }}</td>
                        <td style="color:var(--text-muted)">{{ $cliente->email ?? '—' }}</td>
                        <td style="text-align:right;font-weight:600">S/ {{ number_format($cliente->limite_credito, 2) }}
                        </td>
                        <td style="text-align:center">
                            @if($cliente->activo)
                                <span class="badge badge-success">Activo</span>
                            @else
                                <span class="badge badge-danger">Inactivo</span>
                            @endif
                        </td>
                        <td style="text-align:center">
                            <div style="display:flex;gap:4px;justify-content:center">
                                <button wire:click="openEditModal({{ $cliente->id }})" class="btn btn-sm btn-secondary"><i
                                        data-lucide="pencil"></i></button>
                                <button wire:click="delete({{ $cliente->id }})" wire:confirm="¿Eliminar este cliente?"
                                    class="btn btn-sm btn-danger"><i data-lucide="trash-2"></i></button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" style="text-align:center;padding:60px;color:var(--text-muted)">
                            <i data-lucide="users" style="width:48px;height:48px;display:block;margin:0 auto 12px"></i>
                            No se encontraron clientes
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div style="margin-top:16px">{{ $clientes->links() }}</div>

    @if($showModal)
        <div class="modal-backdrop" wire:click.self="$set('showModal', false)">
            <div class="modal">
                <div class="modal-header">
                    <h3 style="font-weight:700">{{ $editMode ? 'Editar Cliente' : 'Nuevo Cliente' }}</h3>
                    <button wire:click="$set('showModal', false)"
                        style="background:none;border:none;color:var(--text-muted);cursor:pointer">
                        <i data-lucide="x" style="width:20px;height:20px"></i>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Tipo Doc. *</label>
                            <select wire:model="tipo_documento" class="form-select">
                                <option value="DNI">DNI</option>
                                <option value="RUC">RUC</option>
                                <option value="CE">CE</option>
                                <option value="PASAPORTE">Pasaporte</option>
                                <option value="OTROS">Otros</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">N° Documento *</label>
                            <input type="text" wire:model="numero_documento" class="form-input" placeholder="10123456789">
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

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Teléfono</label>
                            <input type="text" wire:model="telefono" class="form-input">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Celular</label>
                            <input type="text" wire:model="celular" class="form-input">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Email</label>
                            <input type="email" wire:model="email" class="form-input">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Tipo Cliente</label>
                            <select wire:model="tipo_cliente_id" class="form-select">
                                <option value="0">— Sin tipo —</option>
                                @foreach($tiposCliente as $tc)
                                    <option value="{{ $tc->id }}">{{ $tc->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Límite Crédito (S/)</label>
                            <input type="number" wire:model="limite_credito" class="form-input" step="0.01" min="0">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Días Crédito</label>
                            <input type="number" wire:model="dias_credito" class="form-input" min="0">
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