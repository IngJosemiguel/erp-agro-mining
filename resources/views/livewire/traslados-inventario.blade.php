<div>
    <div
        style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;flex-wrap:wrap;gap:12px">
        <div style="display:flex;gap:10px;flex-wrap:wrap;align-items:center">
            <div class="search-global" style="width:260px"><i data-lucide="search"></i>
                <input type="text" wire:model.live.debounce.300ms="buscar" placeholder="Buscar código...">
            </div>
            <select wire:model.live="filtroEstado" class="form-select" style="width:160px">
                <option value="">Todos</option>
                <option value="pendiente">Pendiente</option>
                <option value="en_transito">En tránsito</option>
                <option value="completado">Completado</option>
                <option value="anulado">Anulado</option>
            </select>
        </div>
        <button wire:click="openModal" class="btn btn-primary"><i data-lucide="plus"></i> Nuevo Traslado</button>
    </div>

    @if(session()->has('success'))
        <div class="alert alert-success"><i data-lucide="check-circle-2"></i><span>{{ session('success') }}</span></div>
    @endif
    @if(session()->has('error'))
    <div class="alert alert-error"><i data-lucide="alert-circle"></i><span>{{ session('error') }}</span></div>@endif

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Código</th>
                    <th>Fecha</th>
                    <th>Origen</th>
                    <th>Destino</th>
                    <th style="text-align:center">Items</th>
                    <th>Motivo</th>
                    <th style="text-align:center">Estado</th>
                    <th>Usuario</th>
                </tr>
            </thead>
            <tbody>
                @forelse($traslados as $t)
                    <tr>
                        <td><strong style="color:var(--accent);font-family:monospace">{{ $t->codigo }}</strong></td>
                        <td style="color:var(--text-muted)">{{ $t->fecha?->format('d/m/Y') }}</td>
                        <td style="font-weight:600">{{ $t->almacenOrigen->nombre ?? '—' }}</td>
                        <td style="font-weight:600">{{ $t->almacenDestino->nombre ?? '—' }}</td>
                        <td style="text-align:center">{{ $t->detalles_count }}</td>
                        <td style="color:var(--text-muted)">{{ \Str::limit($t->motivo, 30) ?? '—' }}</td>
                        <td style="text-align:center"><span class="badge {{ $t->estado_badge }}"
                                style="font-size:0.65rem">{{ $t->estado_texto }}</span></td>
                        <td style="color:var(--text-muted);font-size:0.8rem">{{ $t->usuario->name ?? '—' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" style="text-align:center;padding:60px;color:var(--text-muted)">
                            <i data-lucide="arrow-right-left"
                                style="width:48px;height:48px;display:block;margin:0 auto 12px"></i>
                            No hay traslados registrados
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div style="margin-top:16px">{{ $traslados->links() }}</div>

    @if($showModal)
        <div class="modal-backdrop" wire:click.self="$set('showModal', false)">
            <div class="modal" style="max-width:680px;max-height:90vh;overflow-y:auto">
                <div class="modal-header">
                    <h3 style="font-weight:700">Nuevo Traslado</h3>
                    <button wire:click="$set('showModal', false)"
                        style="background:none;border:none;color:var(--text-muted);cursor:pointer"><i data-lucide="x"
                            style="width:20px;height:20px"></i></button>
                </div>
                <div class="modal-body">
                    <div class="form-row">
                        <div class="form-group"><label class="form-label">Almacén Origen *</label>
                            <select wire:model="almacen_origen_id" class="form-select">
                                <option value="">— Seleccionar —</option>
                                @foreach($almacenes as $a)<option value="{{ $a->id }}">{{ $a->nombre }}</option>@endforeach
                            </select>
                            @error('almacen_origen_id')<span
                            style="color:var(--danger);font-size:0.75rem">{{ $message }}</span>@enderror
                        </div>
                        <div class="form-group"><label class="form-label">Almacén Destino *</label>
                            <select wire:model="almacen_destino_id" class="form-select">
                                <option value="">— Seleccionar —</option>
                                @foreach($almacenes as $a)<option value="{{ $a->id }}">{{ $a->nombre }}</option>@endforeach
                            </select>
                            @error('almacen_destino_id')<span
                            style="color:var(--danger);font-size:0.75rem">{{ $message }}</span>@enderror
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group"><label class="form-label">Fecha *</label><input type="date"
                                wire:model="fecha" class="form-input"></div>
                        <div class="form-group"><label class="form-label">Motivo</label><input type="text"
                                wire:model="motivo" class="form-input" placeholder="Reabastecimiento, reorganización...">
                        </div>
                    </div>
                    <div class="form-group" style="position:relative;margin-top:8px">
                        <label class="form-label">Agregar Productos</label>
                        <input type="text" wire:model.live.debounce.300ms="buscarProducto" class="form-input"
                            placeholder="Buscar producto...">
                        @if(count($productosEncontrados) > 0)
                            <div
                                style="position:absolute;top:100%;left:0;right:0;z-index:50;background:var(--card-bg);border:1px solid var(--border);border-radius:8px;max-height:180px;overflow-y:auto;box-shadow:0 8px 24px rgba(0,0,0,0.3)">
                                @foreach($productosEncontrados as $p)
                                    <button wire:click="agregarProducto({{ $p['id'] }})"
                                        style="display:block;width:100%;text-align:left;padding:8px 12px;background:none;border:none;border-bottom:1px solid var(--border);color:var(--text-primary);cursor:pointer;font-size:0.85rem"
                                        onmouseover="this.style.background='rgba(0,255,136,0.1)'"
                                        onmouseout="this.style.background='none'">
                                        <strong>{{ $p['codigo_interno'] }}</strong> — {{ $p['nombre'] }}</button>
                                @endforeach
                            </div>
                        @endif
                    </div>
                    <div style="margin-top:12px;border:1px solid var(--border);border-radius:8px;overflow:hidden">
                        <table style="margin:0">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Producto</th>
                                    <th style="width:100px">Cantidad</th>
                                    <th style="width:40px"></th>
                                </tr>
                            </thead>
                            <tbody>@forelse($items as $i => $item)
                                <tr>
                                    <td style="color:var(--text-muted)">{{ $i + 1 }}</td>
                                    <td><strong>{{ $item['nombre'] }}</strong></td>
                                    <td><input type="number" wire:model.live="items.{{ $i }}.cantidad" class="form-input"
                                            style="width:80px;text-align:center" min="1"></td>
                                    <td><button wire:click="eliminarItem({{ $i }})"
                                            style="background:none;border:none;color:var(--danger);cursor:pointer"><i
                                                data-lucide="trash-2" style="width:14px;height:14px"></i></button></td>
                                </tr>
                            @empty<tr>
                                    <td colspan="4" style="text-align:center;padding:20px;color:var(--text-muted)">Agregar
                                        productos</td>
                                </tr>@endforelse
                            </tbody>
                        </table>
                    </div>
                    @error('items')<span style="color:var(--danger);font-size:0.75rem">{{ $message }}</span>@enderror
                </div>
                <div class="modal-footer">
                    <button wire:click="$set('showModal', false)" class="btn btn-secondary">Cancelar</button>
                    <button wire:click="guardar" class="btn btn-primary"><i data-lucide="save"></i> Ejecutar
                        Traslado</button>
                </div>
            </div>
        </div>
    @endif
</div>