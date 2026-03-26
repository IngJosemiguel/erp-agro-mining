<div>
    <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(260px, 1fr));gap:16px;margin-bottom:24px">
        <div class="card" style="padding:20px">
            <div style="display:flex;align-items:center;gap:12px">
                <div
                    style="width:44px;height:44px;border-radius:10px;background:rgba(0,255,136,0.15);display:flex;align-items:center;justify-content:center">
                    <i data-lucide="building-2" style="width:22px;height:22px;color:var(--accent)"></i></div>
                <div>
                    <p style="font-size:0.75rem;color:var(--text-muted);margin:0">ACTIVOS REGISTRADOS</p>
                    <p style="font-size:1.5rem;font-weight:800;margin:0">{{ $cantActivos }}</p>
                </div>
            </div>
        </div>
        <div class="card" style="padding:20px">
            <div style="display:flex;align-items:center;gap:12px">
                <div
                    style="width:44px;height:44px;border-radius:10px;background:rgba(59,130,246,0.15);display:flex;align-items:center;justify-content:center">
                    <i data-lucide="banknote" style="width:22px;height:22px;color:#3b82f6"></i></div>
                <div>
                    <p style="font-size:0.75rem;color:var(--text-muted);margin:0">VALOR TOTAL ACTIVOS</p>
                    <p style="font-size:1.5rem;font-weight:800;margin:0">S/ {{ number_format($totalValor, 2) }}</p>
                </div>
            </div>
        </div>
    </div>

    <div
        style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;flex-wrap:wrap;gap:12px">
        <div style="display:flex;gap:10px;align-items:center">
            <div class="search-global" style="width:260px"><i data-lucide="search"></i>
                <input type="text" wire:model.live.debounce.300ms="buscar"
                    placeholder="Buscar activo, código, serie...">
            </div>
            <select wire:model.live="filtroEstado" class="form-select" style="width:170px">
                <option value="">Todos</option>
                <option value="activo">Activo</option>
                <option value="en_mantenimiento">En mantenimiento</option>
                <option value="dado_baja">Dado de baja</option>
                <option value="vendido">Vendido</option>
            </select>
            <select wire:model.live="filtroCategoria" class="form-select" style="width:180px">
                <option value="">Todas las categorías</option>
                @foreach($categoriasAF as $k => $v)<option value="{{ $k }}">{{ $v }}</option>@endforeach
            </select>
        </div>
        <button wire:click="openModal" class="btn btn-primary"><i data-lucide="plus"></i> Nuevo Activo</button>
    </div>

    @if(session()->has('success'))
        <div class="alert alert-success"><i data-lucide="check-circle-2"></i><span>{{ session('success') }}</span></div>
    @endif

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Código</th>
                    <th>Nombre</th>
                    <th>Categoría</th>
                    <th>Marca/Modelo</th>
                    <th>Adquisición</th>
                    <th style="text-align:right">Valor</th>
                    <th style="text-align:right">Deprec.</th>
                    <th style="text-align:right">V. Neto</th>
                    <th>Ubicación</th>
                    <th style="text-align:center">Estado</th>
                    <th style="text-align:center">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($activos as $a)
                    @php $neto = (float) $a->valor_adquisicion - (float) $a->depreciacion_acumulada; @endphp
                    <tr>
                        <td><strong style="color:var(--accent);font-family:monospace">{{ $a->codigo }}</strong></td>
                        <td><strong>{{ \Str::limit($a->nombre, 28) }}</strong>@if($a->numero_serie)<br><span
                        style="font-size:0.7rem;color:var(--text-muted)">S/N: {{ $a->numero_serie }}</span>@endif
                        </td>
                        <td style="color:var(--text-muted);font-size:0.85rem">{{ $categoriasAF[$a->categoria] ?? '—' }}</td>
                        <td style="color:var(--text-muted);font-size:0.85rem">{{ $a->marca }} {{ $a->modelo }}</td>
                        <td style="color:var(--text-muted)">{{ $a->fecha_adquisicion?->format('d/m/Y') }}</td>
                        <td style="text-align:right;font-weight:600">S/ {{ number_format($a->valor_adquisicion, 2) }}</td>
                        <td style="text-align:right;color:var(--text-muted)">{{ $a->depreciacion_anual }}%</td>
                        <td
                            style="text-align:right;font-weight:700;color:{{ $neto > 0 ? 'var(--accent)' : 'var(--danger)' }}">
                            S/ {{ number_format($neto, 2) }}</td>
                        <td style="color:var(--text-muted);font-size:0.85rem">{{ $a->ubicacion ?? '—' }}</td>
                        <td style="text-align:center"><span class="badge {{ $a->estado_badge }}"
                                style="font-size:0.65rem">{{ $a->estado_texto }}</span></td>
                        <td style="text-align:center;white-space:nowrap">
                            <button wire:click="openModal({{ $a->id }})" title="Editar"
                                style="background:none;border:none;color:var(--accent);cursor:pointer"><i
                                    data-lucide="pencil" style="width:16px;height:16px"></i></button>
                            @if($a->estado === 'activo')<button wire:click="cambiarEstado({{ $a->id }},'dado_baja')"
                                wire:confirm="¿Dar de baja este activo?" title="Dar de baja"
                                style="background:none;border:none;color:var(--danger);cursor:pointer"><i
                            data-lucide="x-circle" style="width:16px;height:16px"></i></button>@endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="11" style="text-align:center;padding:60px;color:var(--text-muted)"><i
                                data-lucide="building-2"
                                style="width:48px;height:48px;display:block;margin:0 auto 12px"></i>No hay activos fijos
                            registrados</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div style="margin-top:16px">{{ $activos->links() }}</div>

    @if($showModal)
        <div class="modal-backdrop" wire:click.self="$set('showModal', false)">
            <div class="modal" style="max-width:640px;max-height:90vh;overflow-y:auto">
                <div class="modal-header">
                    <h3 style="font-weight:700">{{ $editingId ? 'Editar' : 'Nuevo' }} Activo Fijo</h3>
                    <button wire:click="$set('showModal', false)"
                        style="background:none;border:none;color:var(--text-muted);cursor:pointer"><i data-lucide="x"
                            style="width:20px;height:20px"></i></button>
                </div>
                <div class="modal-body">
                    <div class="form-group"><label class="form-label">Nombre del Activo *</label><input type="text"
                            wire:model="nombre" class="form-input" placeholder="Excavadora CAT 320">@error('nombre')<span
                            style="color:var(--danger);font-size:0.75rem">{{ $message }}</span>@enderror</div>
                    <div class="form-row">
                        <div class="form-group"><label class="form-label">Categoría</label><select wire:model="categoria"
                                class="form-select">
                                <option value="">— Seleccionar —</option>@foreach($categoriasAF as $k => $v)<option
                                value="{{ $k }}">{{ $v }}</option>@endforeach
                            </select></div>
                        <div class="form-group"><label class="form-label">Fecha Adquisición *</label><input type="date"
                                wire:model="fecha_adquisicion" class="form-input"></div>
                    </div>
                    <div class="form-row">
                        <div class="form-group"><label class="form-label">Marca</label><input type="text" wire:model="marca"
                                class="form-input" placeholder="CAT, Komatsu..."></div>
                        <div class="form-group"><label class="form-label">Modelo</label><input type="text"
                                wire:model="modelo" class="form-input"></div>
                    </div>
                    <div class="form-row">
                        <div class="form-group"><label class="form-label">N° Serie</label><input type="text"
                                wire:model="numero_serie" class="form-input"></div>
                        <div class="form-group"><label class="form-label">Valor Adquisición (S/) *</label><input
                                type="number" wire:model="valor_adquisicion" class="form-input"
                                step="0.01">@error('valor_adquisicion')<span
                                style="color:var(--danger);font-size:0.75rem">{{ $message }}</span>@enderror</div>
                    </div>
                    <div class="form-row">
                        <div class="form-group"><label class="form-label">Deprec. Anual (%)</label><input type="number"
                                wire:model="depreciacion_anual" class="form-input" step="0.01" min="0" max="100"></div>
                        <div class="form-group"><label class="form-label">Ubicación</label><input type="text"
                                wire:model="ubicacion" class="form-input" placeholder="Planta, Oficina..."></div>
                    </div>
                    <div class="form-group"><label class="form-label">Responsable</label><input type="text"
                            wire:model="responsable" class="form-input" placeholder="Nombre del encargado"></div>
                    <div class="form-group"><label class="form-label">Observaciones</label><textarea
                            wire:model="observaciones" class="form-input" rows="2"></textarea></div>
                </div>
                <div class="modal-footer">
                    <button wire:click="$set('showModal', false)" class="btn btn-secondary">Cancelar</button>
                    <button wire:click="save" class="btn btn-primary"><i data-lucide="save"></i>
                        {{ $editingId ? 'Actualizar' : 'Registrar' }}</button>
                </div>
            </div>
        </div>
    @endif
</div>