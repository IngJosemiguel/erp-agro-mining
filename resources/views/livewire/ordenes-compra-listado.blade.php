<div>
    {{-- KPIs --}}
    <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(260px, 1fr));gap:16px;margin-bottom:24px">
        <div class="card" style="padding:20px">
            <div style="display:flex;align-items:center;gap:12px">
                <div
                    style="width:44px;height:44px;border-radius:10px;background:rgba(99,102,241,0.15);display:flex;align-items:center;justify-content:center">
                    <i data-lucide="clipboard-list" style="width:22px;height:22px;color:#6366f1"></i>
                </div>
                <div>
                    <p style="font-size:0.75rem;color:var(--text-muted);margin:0">VALOR TOTAL OC</p>
                    <p style="font-size:1.5rem;font-weight:800;margin:0">S/ {{ number_format($totalOC, 2) }}</p>
                </div>
            </div>
        </div>
        <div class="card" style="padding:20px">
            <div style="display:flex;align-items:center;gap:12px">
                <div
                    style="width:44px;height:44px;border-radius:10px;background:rgba(245,158,11,0.15);display:flex;align-items:center;justify-content:center">
                    <i data-lucide="hourglass" style="width:22px;height:22px;color:#f59e0b"></i>
                </div>
                <div>
                    <p style="font-size:0.75rem;color:var(--text-muted);margin:0">OC PENDIENTES</p>
                    <p style="font-size:1.5rem;font-weight:800;margin:0;color:#f59e0b">{{ $pendientesOC }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Filtros --}}
    <div
        style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;flex-wrap:wrap;gap:12px">
        <div style="display:flex;gap:10px;flex-wrap:wrap;align-items:center">
            <div class="search-global" style="width:260px">
                <i data-lucide="search"></i>
                <input type="text" wire:model.live.debounce.300ms="buscar" placeholder="Buscar código, proveedor...">
            </div>
            <select wire:model.live="filtroEstado" class="form-select" style="width:160px">
                <option value="">Todos</option>
                <option value="borrador">Borrador</option>
                <option value="enviada">Enviada</option>
                <option value="parcial">Parcial</option>
                <option value="completa">Completa</option>
                <option value="anulada">Anulada</option>
            </select>
            <input type="date" wire:model.live="fechaDesde" class="form-input" style="width:150px">
            <input type="date" wire:model.live="fechaHasta" class="form-input" style="width:150px">
        </div>
        <button wire:click="openModal" class="btn btn-primary">
            <i data-lucide="plus"></i> Nueva OC
        </button>
    </div>

    @if(session()->has('success'))
        <div class="alert alert-success"><i data-lucide="check-circle-2"></i><span>{{ session('success') }}</span></div>
    @endif

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Código</th>
                    <th>Fecha</th>
                    <th>Proveedor</th>
                    <th style="text-align:center">Items</th>
                    <th>Entrega Est.</th>
                    <th style="text-align:right">Subtotal</th>
                    <th style="text-align:right">IGV</th>
                    <th style="text-align:right">Total</th>
                    <th style="text-align:center">Estado</th>
                    <th>Usuario</th>
                    <th style="text-align:center">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($ordenes as $oc)
                    <tr>
                        <td><strong style="color:var(--accent);font-family:monospace">{{ $oc->codigo }}</strong></td>
                        <td style="white-space:nowrap;color:var(--text-muted)">{{ $oc->fecha_emision?->format('d/m/Y') }}
                        </td>
                        <td>
                            <strong>{{ \Str::limit($oc->proveedor->razon_social ?? '—', 28) }}</strong>
                            <br><span
                                style="font-size:0.7rem;color:var(--text-muted)">{{ $oc->proveedor->numero_documento ?? '' }}</span>
                        </td>
                        <td style="text-align:center">{{ $oc->detalles_count }}</td>
                        <td style="color:var(--text-muted);font-size:0.85rem">
                            {{ $oc->fecha_entrega_estimada?->format('d/m/Y') ?? '—' }}</td>
                        <td style="text-align:right;color:var(--text-muted)">S/ {{ number_format($oc->subtotal, 2) }}</td>
                        <td style="text-align:right;color:var(--text-muted)">S/ {{ number_format($oc->igv, 2) }}</td>
                        <td style="text-align:right;font-weight:700">S/ {{ number_format($oc->total, 2) }}</td>
                        <td style="text-align:center">
                            <span class="badge {{ $oc->estado_badge }}"
                                style="font-size:0.65rem">{{ $oc->estado_texto }}</span>
                        </td>
                        <td style="color:var(--text-muted);font-size:0.8rem">{{ $oc->usuario->name ?? '—' }}</td>
                        <td style="text-align:center;white-space:nowrap">
                            @if($oc->estado === 'borrador')
                                <button wire:click="cambiarEstado({{ $oc->id }}, 'enviada')" title="Enviar al proveedor"
                                    style="background:none;border:none;color:#6366f1;cursor:pointer">
                                    <i data-lucide="send" style="width:16px;height:16px"></i>
                                </button>
                            @endif
                            @if(in_array($oc->estado, ['enviada', 'parcial']))
                                <button wire:click="cambiarEstado({{ $oc->id }}, 'completa')" title="Marcar completa"
                                    style="background:none;border:none;color:var(--success);cursor:pointer">
                                    <i data-lucide="check-circle" style="width:16px;height:16px"></i>
                                </button>
                            @endif
                            @if($oc->estado !== 'anulada')
                                <button wire:click="cambiarEstado({{ $oc->id }}, 'anulada')" wire:confirm="¿Anular esta OC?"
                                    title="Anular" style="background:none;border:none;color:var(--danger);cursor:pointer">
                                    <i data-lucide="x-circle" style="width:16px;height:16px"></i>
                                </button>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="11" style="text-align:center;padding:60px;color:var(--text-muted)">
                            <i data-lucide="clipboard" style="width:48px;height:48px;display:block;margin:0 auto 12px"></i>
                            No hay órdenes de compra registradas
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div style="margin-top:16px">{{ $ordenes->links() }}</div>

    {{-- MODAL NUEVA OC --}}
    @if($showModal)
        <div class="modal-backdrop" wire:click.self="$set('showModal', false)">
            <div class="modal" style="max-width:750px;max-height:90vh;overflow-y:auto">
                <div class="modal-header">
                    <h3 style="font-weight:700">Nueva Orden de Compra</h3>
                    <button wire:click="$set('showModal', false)"
                        style="background:none;border:none;color:var(--text-muted);cursor:pointer">
                        <i data-lucide="x" style="width:20px;height:20px"></i>
                    </button>
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
                                    <i data-lucide="x-circle" style="width:16px;height:16px"></i>
                                </button>
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
                                            <strong>{{ $p['numero_documento'] }}</strong> — {{ $p['razon_social'] }}
                                        </button>
                                    @endforeach
                                </div>
                            @endif
                        @endif
                        @error('proveedor_id') <span style="color:var(--danger);font-size:0.75rem">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Fecha Emisión *</label>
                            <input type="date" wire:model="fecha_emision" class="form-input">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Entrega Estimada</label>
                            <input type="date" wire:model="fecha_entrega_estimada" class="form-input">
                        </div>
                    </div>

                    {{-- Agregar productos --}}
                    <div class="form-group" style="position:relative;margin-top:12px">
                        <label class="form-label">Agregar Productos</label>
                        <input type="text" wire:model.live.debounce.300ms="buscarProducto" class="form-input"
                            placeholder="Buscar producto...">
                        @if(count($productosEncontrados) > 0)
                            <div
                                style="position:absolute;top:100%;left:0;right:0;z-index:50;background:var(--card-bg);border:1px solid var(--border);border-radius:8px;max-height:200px;overflow-y:auto;box-shadow:0 8px 24px rgba(0,0,0,0.3)">
                                @foreach($productosEncontrados as $p)
                                    <button wire:click="agregarProducto({{ $p['id'] }})"
                                        style="display:flex;justify-content:space-between;width:100%;text-align:left;padding:8px 12px;background:none;border:none;border-bottom:1px solid var(--border);color:var(--text-primary);cursor:pointer;font-size:0.85rem"
                                        onmouseover="this.style.background='rgba(0,255,136,0.1)'"
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
                                    <th style="width:120px;text-align:right">P. Unit.</th>
                                    <th style="width:120px;text-align:right">Total</th>
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
                                                style="width:70px;text-align:center" step="1" min="1"></td>
                                        <td><input type="number" wire:model.live="items.{{ $i }}.precio_unitario"
                                                class="form-input" style="width:100px;text-align:right" step="0.01"></td>
                                        <td style="text-align:right;font-weight:600">S/
                                            {{ number_format($item['cantidad'] * $item['precio_unitario'], 2) }}</td>
                                        <td><button wire:click="eliminarItem({{ $i }})"
                                                style="background:none;border:none;color:var(--danger);cursor:pointer"><i
                                                    data-lucide="trash-2" style="width:14px;height:14px"></i></button></td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" style="text-align:center;padding:20px;color:var(--text-muted)">Agregar
                                            productos</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @error('items') <span style="color:var(--danger);font-size:0.75rem">{{ $message }}</span> @enderror

                    {{-- Totales --}}
                    <div style="margin-top:16px;padding:16px;background:rgba(0,0,0,0.2);border-radius:8px">
                        <div style="display:flex;justify-content:space-between;margin-bottom:6px">
                            <span style="color:var(--text-muted)">Subtotal</span><span style="font-weight:600">S/
                                {{ number_format($subtotal, 2) }}</span>
                        </div>
                        <div style="display:flex;justify-content:space-between;margin-bottom:6px">
                            <span style="color:var(--text-muted)">IGV (18%)</span><span>S/
                                {{ number_format($igv, 2) }}</span>
                        </div>
                        <div
                            style="display:flex;justify-content:space-between;padding-top:8px;border-top:2px solid var(--accent)">
                            <span style="font-weight:800">TOTAL</span><span
                                style="font-size:1.2rem;font-weight:800;color:var(--accent)">S/
                                {{ number_format($total, 2) }}</span>
                        </div>
                    </div>

                    <div class="form-group" style="margin-top:12px">
                        <label class="form-label">Condiciones de Pago</label>
                        <input type="text" wire:model="condiciones" class="form-input"
                            placeholder="Ej: 50% adelanto, 50% contra entrega">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Observaciones</label>
                        <textarea wire:model="observaciones" class="form-input" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button wire:click="$set('showModal', false)" class="btn btn-secondary">Cancelar</button>
                    <button wire:click="guardar" class="btn btn-primary">
                        <i data-lucide="save"></i> Crear Orden de Compra
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>