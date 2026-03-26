<div>
    {{-- KPIs --}}
    <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(260px, 1fr));gap:16px;margin-bottom:24px">
        <div class="card" style="padding:20px">
            <div style="display:flex;align-items:center;gap:12px">
                <div
                    style="width:44px;height:44px;border-radius:10px;background:rgba(239,68,68,0.15);display:flex;align-items:center;justify-content:center">
                    <i data-lucide="package-x" style="width:22px;height:22px;color:#ef4444"></i>
                </div>
                <div>
                    <p style="font-size:0.75rem;color:var(--text-muted);margin:0">DEVOLUCIONES</p>
                    <p style="font-size:1.5rem;font-weight:800;margin:0">{{ $cantDevoluciones }}</p>
                </div>
            </div>
        </div>
        <div class="card" style="padding:20px">
            <div style="display:flex;align-items:center;gap:12px">
                <div
                    style="width:44px;height:44px;border-radius:10px;background:rgba(239,68,68,0.15);display:flex;align-items:center;justify-content:center">
                    <i data-lucide="trending-down" style="width:22px;height:22px;color:#ef4444"></i>
                </div>
                <div>
                    <p style="font-size:0.75rem;color:var(--text-muted);margin:0">VALOR DEVUELTO</p>
                    <p style="font-size:1.5rem;font-weight:800;margin:0;color:#ef4444">S/
                        {{ number_format($totalDevuelto, 2) }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Filtros --}}
    <div
        style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;flex-wrap:wrap;gap:12px">
        <div style="display:flex;gap:10px;align-items:center">
            <div class="search-global" style="width:260px"><i data-lucide="search"></i>
                <input type="text" wire:model.live.debounce.300ms="buscar" placeholder="Buscar código, proveedor...">
            </div>
            <select wire:model.live="filtroEstado" class="form-select" style="width:160px">
                <option value="">Todos</option>
                <option value="pendiente">Pendiente</option>
                <option value="aprobada">Aprobada</option>
                <option value="completada">Completada</option>
                <option value="anulada">Anulada</option>
            </select>
        </div>
        <button wire:click="openModal" class="btn btn-primary"><i data-lucide="plus"></i> Nueva Devolución</button>
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
                    <th>Proveedor</th>
                    <th>Almacén</th>
                    <th style="text-align:center">Items</th>
                    <th>Motivo</th>
                    <th style="text-align:right">Total</th>
                    <th style="text-align:center">Estado</th>
                    <th>Usuario</th>
                    <th style="text-align:center">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($devoluciones as $dev)
                    <tr>
                        <td><strong style="color:var(--accent);font-family:monospace">{{ $dev->codigo }}</strong></td>
                        <td style="color:var(--text-muted)">{{ $dev->fecha?->format('d/m/Y') }}</td>
                        <td>
                            <strong>{{ \Str::limit($dev->proveedor->razon_social ?? '—', 28) }}</strong><br>
                            <span
                                style="font-size:0.7rem;color:var(--text-muted)">{{ $dev->proveedor->numero_documento ?? '' }}</span>
                        </td>
                        <td style="color:var(--text-muted)">{{ $dev->almacen->nombre ?? '—' }}</td>
                        <td style="text-align:center">{{ $dev->detalles_count }}</td>
                        <td style="color:var(--text-muted)">{{ \Str::limit($dev->motivo, 30) }}</td>
                        <td style="text-align:right;font-weight:700;color:#ef4444">S/ {{ number_format($dev->total, 2) }}
                        </td>
                        <td style="text-align:center"><span class="badge {{ $dev->estado_badge }}"
                                style="font-size:0.65rem">{{ $dev->estado_texto }}</span></td>
                        <td style="color:var(--text-muted);font-size:0.8rem">{{ $dev->usuario->name ?? '—' }}</td>
                        <td style="text-align:center;white-space:nowrap">
                            @if($dev->estado !== 'anulada')
                                <button wire:click="cambiarEstado({{ $dev->id }}, 'anulada')"
                                    wire:confirm="¿Anular esta devolución?" title="Anular"
                                    style="background:none;border:none;color:var(--danger);cursor:pointer">
                                    <i data-lucide="x-circle" style="width:16px;height:16px"></i>
                                </button>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" style="text-align:center;padding:60px;color:var(--text-muted)">
                            <i data-lucide="package-x" style="width:48px;height:48px;display:block;margin:0 auto 12px"></i>
                            No hay devoluciones registradas
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div style="margin-top:16px">{{ $devoluciones->links() }}</div>

    {{-- MODAL --}}
    @if($showModal)
        <div class="modal-backdrop" wire:click.self="$set('showModal', false)">
            <div class="modal" style="max-width:700px;max-height:90vh;overflow-y:auto">
                <div class="modal-header">
                    <h3 style="font-weight:700">Nueva Devolución a Proveedor</h3>
                    <button wire:click="$set('showModal', false)"
                        style="background:none;border:none;color:var(--text-muted);cursor:pointer">
                        <i data-lucide="x" style="width:20px;height:20px"></i></button>
                </div>
                <div class="modal-body">
                    {{-- Proveedor --}}
                    <div class="form-group" style="position:relative">
                        <label class="form-label">Proveedor *</label>
                        @if($proveedorTexto)
                            <div
                                style="display:flex;align-items:center;gap:8px;padding:10px;background:rgba(0,255,136,0.1);border:1px solid var(--accent);border-radius:8px">
                                <span style="flex:1;font-weight:600">{{ $proveedorTexto }}</span>
                                <button wire:click="$set('proveedorTexto', '')" wire:click.prevent="$set('proveedor_id', '')"
                                    style="background:none;border:none;color:var(--danger);cursor:pointer">
                                    <i data-lucide="x-circle" style="width:16px;height:16px"></i></button>
                            </div>
                        @else
                            <input type="text" wire:model.live.debounce.300ms="buscarProveedor" class="form-input"
                                placeholder="Buscar proveedor...">
                            @if(count($proveedoresEncontrados) > 0)
                                <div
                                    style="position:absolute;top:100%;left:0;right:0;z-index:50;background:var(--card-bg);border:1px solid var(--border);border-radius:8px;max-height:160px;overflow-y:auto;box-shadow:0 8px 24px rgba(0,0,0,0.3)">
                                    @foreach($proveedoresEncontrados as $p)
                                        <button wire:click="seleccionarProveedor({{ $p['id'] }})"
                                            style="display:block;width:100%;text-align:left;padding:8px 12px;background:none;border:none;border-bottom:1px solid var(--border);color:var(--text-primary);cursor:pointer;font-size:0.85rem"
                                            onmouseover="this.style.background='rgba(0,255,136,0.1)'"
                                            onmouseout="this.style.background='none'">
                                            <strong>{{ $p['numero_documento'] }}</strong> — {{ $p['razon_social'] }}</button>
                                    @endforeach
                                </div>
                            @endif
                        @endif
                        @error('proveedor_id')<span
                        style="color:var(--danger);font-size:0.75rem">{{ $message }}</span>@enderror
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Almacén Origen *</label>
                            <select wire:model="almacen_id" class="form-select">
                                <option value="">— Seleccionar —</option>
                                @foreach($almacenes as $a)<option value="{{ $a->id }}">{{ $a->nombre }}</option>@endforeach
                            </select>
                            @error('almacen_id')<span
                            style="color:var(--danger);font-size:0.75rem">{{ $message }}</span>@enderror
                        </div>
                        <div class="form-group">
                            <label class="form-label">Fecha *</label>
                            <input type="date" wire:model="fecha" class="form-input">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Motivo de Devolución *</label>
                        <input type="text" wire:model="motivo" class="form-input"
                            placeholder="Producto defectuoso, error de pedido, vencido...">
                        @error('motivo')<span style="color:var(--danger);font-size:0.75rem">{{ $message }}</span>@enderror
                    </div>

                    {{-- Buscar producto --}}
                    <div class="form-group" style="position:relative;margin-top:8px">
                        <label class="form-label">Agregar Productos a Devolver</label>
                        <input type="text" wire:model.live.debounce.300ms="buscarProducto" class="form-input"
                            placeholder="Buscar producto...">
                        @if(count($productosEncontrados) > 0)
                            <div
                                style="position:absolute;top:100%;left:0;right:0;z-index:50;background:var(--card-bg);border:1px solid var(--border);border-radius:8px;max-height:180px;overflow-y:auto;box-shadow:0 8px 24px rgba(0,0,0,0.3)">
                                @foreach($productosEncontrados as $p)
                                    <button wire:click="agregarProducto({{ $p['id'] }})"
                                        style="display:flex;justify-content:space-between;width:100%;text-align:left;padding:8px 12px;background:none;border:none;border-bottom:1px solid var(--border);color:var(--text-primary);cursor:pointer;font-size:0.85rem"
                                        onmouseover="this.style.background='rgba(239,68,68,0.1)'"
                                        onmouseout="this.style.background='none'">
                                        <span><strong>{{ $p['codigo_interno'] }}</strong> — {{ $p['nombre'] }}</span>
                                        <span style="color:var(--accent)">S/ {{ number_format($p['precio_compra'], 2) }}</span>
                                    </button>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    {{-- Tabla items --}}
                    <div style="margin-top:12px;border:1px solid var(--border);border-radius:8px;overflow:hidden">
                        <table style="margin:0">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Producto</th>
                                    <th style="width:90px">Cant.</th>
                                    <th style="width:120px;text-align:right">C. Unit.</th>
                                    <th style="width:110px;text-align:right">Total</th>
                                    <th>Motivo item</th>
                                    <th style="width:40px"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($items as $i => $item)
                                    <tr>
                                        <td style="color:var(--text-muted)">{{ $i + 1 }}</td>
                                        <td><strong>{{ $item['nombre'] }}</strong><br><span
                                                style="font-size:0.7rem;color:var(--text-muted)">{{ $item['codigo'] }}</span>
                                        </td>
                                        <td><input type="number" wire:model.live="items.{{ $i }}.cantidad" class="form-input"
                                                style="width:70px;text-align:center" min="1" step="1"></td>
                                        <td><input type="number" wire:model.live="items.{{ $i }}.costo_unitario"
                                                class="form-input" style="width:100px;text-align:right" step="0.01"></td>
                                        <td style="text-align:right;font-weight:600;color:#ef4444">S/
                                            {{ number_format($item['cantidad'] * $item['costo_unitario'], 2) }}</td>
                                        <td><input type="text" wire:model.live="items.{{ $i }}.motivo_item" class="form-input"
                                                placeholder="Ej: Defecto de fábrica" style="font-size:0.8rem"></td>
                                        <td><button wire:click="eliminarItem({{ $i }})"
                                                style="background:none;border:none;color:var(--danger);cursor:pointer"><i
                                                    data-lucide="trash-2" style="width:14px;height:14px"></i></button></td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" style="text-align:center;padding:20px;color:var(--text-muted)">Agregar
                                            productos a devolver</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @error('items')<span style="color:var(--danger);font-size:0.75rem">{{ $message }}</span>@enderror

                    {{-- Total --}}
                    <div style="margin-top:12px;display:flex;justify-content:flex-end">
                        <div
                            style="padding:12px 20px;background:rgba(239,68,68,0.1);border:1px solid #ef4444;border-radius:8px;text-align:right">
                            <p style="margin:0;font-size:0.75rem;color:var(--text-muted)">TOTAL A DEVOLVER</p>
                            <p style="margin:0;font-size:1.5rem;font-weight:800;color:#ef4444">S/
                                {{ number_format($total, 2) }}</p>
                        </div>
                    </div>

                    <div class="form-group" style="margin-top:12px">
                        <label class="form-label">Observaciones</label>
                        <textarea wire:model="observaciones" class="form-input" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button wire:click="$set('showModal', false)" class="btn btn-secondary">Cancelar</button>
                    <button wire:click="guardar" class="btn" style="background:#ef4444;color:#fff">
                        <i data-lucide="package-x"></i> Registrar Devolución
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>