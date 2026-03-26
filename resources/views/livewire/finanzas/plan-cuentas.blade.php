<div>
    {{-- Stats --}}
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:16px;margin-bottom:24px">
        <div class="card" style="padding:20px;text-align:center">
            <div style="font-size:1.8rem;font-weight:800;color:var(--primary)">{{ $stats->total ?? 0 }}</div>
            <div style="font-size:0.8rem;color:var(--text-muted);margin-top:4px">Total Cuentas</div>
        </div>
        <div class="card" style="padding:20px;text-align:center">
            <div style="font-size:1.8rem;font-weight:800;color:var(--success)">{{ $stats->activas ?? 0 }}</div>
            <div style="font-size:0.8rem;color:var(--text-muted);margin-top:4px">Activas</div>
        </div>
        <div class="card" style="padding:20px;text-align:center">
            <div style="font-size:1.8rem;font-weight:800;color:var(--accent)">{{ $stats->activos ?? 0 }}</div>
            <div style="font-size:0.8rem;color:var(--text-muted);margin-top:4px">Activos</div>
        </div>
        <div class="card" style="padding:20px;text-align:center">
            <div style="font-size:1.8rem;font-weight:800;color:var(--danger)">{{ $stats->pasivos ?? 0 }}</div>
            <div style="font-size:0.8rem;color:var(--text-muted);margin-top:4px">Pasivos</div>
        </div>
        <div class="card" style="padding:20px;text-align:center">
            <div style="font-size:1.8rem;font-weight:800;color:var(--primary)">{{ $stats->ingresos ?? 0 }}</div>
            <div style="font-size:0.8rem;color:var(--text-muted);margin-top:4px">Ingresos</div>
        </div>
    </div>

    {{-- Toolbar --}}
    @if(session('success'))
        <div class="alert alert-success" style="margin-bottom:16px"><i data-lucide="check-circle-2"></i>
            {{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-error" style="margin-bottom:16px"><i data-lucide="alert-circle"></i> {{ session('error') }}
        </div>
    @endif

    <div
        style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;flex-wrap:wrap;gap:12px">
        <div style="display:flex;gap:10px;flex-wrap:wrap">
            <div class="search-global" style="width:260px">
                <i data-lucide="search"></i>
                <input type="text" wire:model.live.debounce.300ms="buscar" placeholder="Código o nombre de cuenta...">
            </div>
            <select wire:model.live="filtroTipo" class="form-select" style="width:160px">
                <option value="">Todos los tipos</option>
                <option value="activo">Activo</option>
                <option value="pasivo">Pasivo</option>
                <option value="patrimonio">Patrimonio</option>
                <option value="ingreso">Ingreso</option>
                <option value="gasto">Gasto</option>
                <option value="costo">Costo</option>
            </select>
        </div>
        <button wire:click="openCreateModal" class="btn btn-primary">
            <i data-lucide="plus"></i> Nueva Cuenta
        </button>
    </div>

    {{-- Tabla --}}
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th style="width:120px">Código</th>
                    <th>Nombre</th>
                    <th>Tipo</th>
                    <th>Naturaleza</th>
                    <th>Nivel</th>
                    <th style="text-align:center">Mov.</th>
                    <th style="text-align:center">Estado</th>
                    <th style="text-align:center">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($cuentas as $cuenta)
                    @php
                        $colorTipo = match ($cuenta->tipo) {
                            'activo' => 'var(--accent)',
                            'pasivo' => 'var(--danger)',
                            'patrimonio' => 'var(--warning)',
                            'ingreso' => 'var(--success)',
                            'gasto', 'costo' => '#f472b6',
                            default => 'var(--text-muted)',
                        };
                    @endphp
                    <tr>
                        <td><code
                                style="background:var(--bg-elevated);padding:2px 8px;border-radius:4px;font-size:0.8rem;color:{{ $colorTipo }}">{{ $cuenta->codigo }}</code>
                        </td>
                        <td style="padding-left:{{ (($cuenta->nivel ?? 1) - 1) * 16 }}px">
                            @if(!$cuenta->acepta_movimiento)
                                <strong>{{ $cuenta->nombre }}</strong>
                            @else
                                {{ $cuenta->nombre }}
                            @endif
                        </td>
                        <td><span class="badge"
                                style="background:{{ $colorTipo }}20;color:{{ $colorTipo }};border:1px solid {{ $colorTipo }}40">{{ ucfirst($cuenta->tipo) }}</span>
                        </td>
                        <td style="color:var(--text-muted);font-size:0.82rem">{{ ucfirst($cuenta->naturaleza) }}</td>
                        <td style="text-align:center;color:var(--text-muted)">{{ $cuenta->nivel }}</td>
                        <td style="text-align:center">
                            @if($cuenta->acepta_movimiento)
                                <span style="color:var(--success)"><i data-lucide="check"
                                        style="width:16px;height:16px"></i></span>
                            @else
                                <span style="color:var(--text-muted)"><i data-lucide="minus"
                                        style="width:16px;height:16px"></i></span>
                            @endif
                        </td>
                        <td style="text-align:center">
                            <button wire:click="toggleEstado({{ $cuenta->id }})"
                                style="background:none;border:none;cursor:pointer" title="Toggle estado">
                                @if($cuenta->activo)
                                    <span class="badge badge-success" style="font-size:0.7rem">Activa</span>
                                @else
                                    <span class="badge"
                                        style="font-size:0.7rem;background:var(--bg-elevated);color:var(--text-muted)">Inactiva</span>
                                @endif
                            </button>
                        </td>
                        <td style="text-align:center">
                            <div style="display:flex;gap:4px;justify-content:center">
                                <button wire:click="openEditModal({{ $cuenta->id }})" class="btn btn-sm btn-secondary"
                                    title="Editar">
                                    <i data-lucide="pencil"></i>
                                </button>
                                <button wire:click="delete({{ $cuenta->id }})" wire:confirm="¿Eliminar esta cuenta?"
                                    class="btn btn-sm btn-danger" title="Eliminar">
                                    <i data-lucide="trash-2"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" style="text-align:center;padding:60px;color:var(--text-muted)">
                            <i data-lucide="list-tree" style="width:40px;height:40px;margin:0 auto 12px;display:block"></i>
                            No hay cuentas. Crea tu Plan de Cuentas o importa el estándar PCGE.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div style="margin-top:16px">{{ $cuentas->links() }}</div>

    {{-- Modal Crear/Editar --}}
    @if($showModal)
        <div class="modal-backdrop" wire:click.self="$set('showModal', false)">
            <div class="modal" style="max-width:560px">
                <div class="modal-header">
                    <h3 style="font-weight:700">{{ $editMode ? 'Editar Cuenta' : 'Nueva Cuenta Contable' }}</h3>
                    <button wire:click="$set('showModal', false)"
                        style="background:none;border:none;color:var(--text-muted);cursor:pointer">
                        <i data-lucide="x" style="width:20px"></i>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Código de Cuenta *</label>
                            <input type="text" wire:model="codigo" class="form-input" placeholder="Ej: 1.1.1.01"
                                style="text-transform:uppercase">
                            @error('codigo') <span style="color:var(--danger);font-size:0.75rem">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label class="form-label">Nivel</label>
                            <select wire:model="nivel" class="form-select">
                                <option value="1">1 — Clase</option>
                                <option value="2">2 — Grupo</option>
                                <option value="3">3 — Cuenta</option>
                                <option value="4">4 — Sub-cuenta</option>
                                <option value="5">5 — Analítica</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Nombre de la Cuenta *</label>
                        <input type="text" wire:model="nombre" class="form-input" placeholder="Ej: Caja y Bancos">
                        @error('nombre') <span style="color:var(--danger);font-size:0.75rem">{{ $message }}</span> @enderror
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Tipo</label>
                            <select wire:model="tipo" class="form-select">
                                @foreach($tipoOpciones as $val => $label)
                                    <option value="{{ $val }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Naturaleza</label>
                            <select wire:model="naturaleza" class="form-select">
                                <option value="deudora">Deudora (Activos/Gastos)</option>
                                <option value="acreedora">Acreedora (Pasivos/Ingresos)</option>
                            </select>
                        </div>
                    </div>

                    <div style="display:flex;gap:16px;margin-top:8px">
                        <label
                            style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:0.85rem;color:var(--text-secondary)">
                            <input type="checkbox" wire:model="acepta_movimiento"> Acepta movimiento directo
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