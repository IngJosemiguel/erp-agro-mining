<div>
    @if(session()->has('error'))
        <div class="alert alert-error"><i data-lucide="alert-circle"></i><span>{{ session('error') }}</span></div>
    @endif

    <div style="display:grid;grid-template-columns:1fr 340px;gap:20px">
        {{-- Panel izquierdo: Detalle --}}
        <div>
            {{-- Proveedor --}}
            <div class="card" style="padding:20px;margin-bottom:16px">
                <p
                    style="font-weight:700;font-size:12px;text-transform:uppercase;color:var(--accent);margin:0 0 12px 0;letter-spacing:1px">
                    Proveedor</p>
                <div style="position:relative">
                    @if($proveedorSeleccionado)
                        <div
                            style="display:flex;align-items:center;gap:8px;padding:10px;background:rgba(0,255,136,0.1);border:1px solid var(--accent);border-radius:8px">
                            <span style="flex:1;font-weight:600">{{ $proveedorSeleccionado }}</span>
                            <button wire:click="$set('proveedorSeleccionado', '')"
                                wire:click.prevent="$set('proveedor_id', '')"
                                style="background:none;border:none;color:var(--danger);cursor:pointer">
                                <i data-lucide="x-circle" style="width:16px;height:16px"></i>
                            </button>
                        </div>
                    @else
                        <input type="text" wire:model.live.debounce.300ms="buscarProveedor" class="form-input"
                            placeholder="Buscar por RUC o razón social...">
                        @if(count($proveedoresEncontrados) > 0)
                            <div
                                style="position:absolute;top:100%;left:0;right:0;z-index:50;background:var(--card-bg);border:1px solid var(--border);border-radius:8px;max-height:200px;overflow-y:auto;box-shadow:0 8px 24px rgba(0,0,0,0.3)">
                                @foreach($proveedoresEncontrados as $p)
                                    <button wire:click="seleccionarProveedor({{ $p['id'] }})"
                                        style="display:block;width:100%;text-align:left;padding:10px 14px;background:none;border:none;border-bottom:1px solid var(--border);color:var(--text-primary);cursor:pointer;font-size:0.85rem"
                                        onmouseover="this.style.background='rgba(0,255,136,0.1)'"
                                        onmouseout="this.style.background='none'">
                                        <strong>{{ $p['numero_documento'] }}</strong> — {{ $p['razon_social'] }}
                                    </button>
                                @endforeach
                            </div>
                        @endif
                    @endif
                </div>
                @error('proveedor_id') <span style="color:var(--danger);font-size:0.75rem">{{ $message }}</span>
                @enderror
            </div>

            {{-- Agregar productos --}}
            <div class="card" style="padding:20px;margin-bottom:16px">
                <p
                    style="font-weight:700;font-size:12px;text-transform:uppercase;color:var(--accent);margin:0 0 12px 0;letter-spacing:1px">
                    Agregar Productos</p>
                <div style="position:relative">
                    <input type="text" wire:model.live.debounce.300ms="buscarProducto" class="form-input"
                        placeholder="Buscar producto por nombre o código...">
                    @if(count($productosEncontrados) > 0)
                        <div
                            style="position:absolute;top:100%;left:0;right:0;z-index:50;background:var(--card-bg);border:1px solid var(--border);border-radius:8px;max-height:240px;overflow-y:auto;box-shadow:0 8px 24px rgba(0,0,0,0.3)">
                            @foreach($productosEncontrados as $p)
                                <button wire:click="agregarProducto({{ $p['id'] }})"
                                    style="display:flex;justify-content:space-between;width:100%;text-align:left;padding:10px 14px;background:none;border:none;border-bottom:1px solid var(--border);color:var(--text-primary);cursor:pointer;font-size:0.85rem"
                                    onmouseover="this.style.background='rgba(0,255,136,0.1)'"
                                    onmouseout="this.style.background='none'">
                                    <span><strong>{{ $p['codigo_interno'] }}</strong> — {{ $p['nombre'] }}</span>
                                    <span style="color:var(--accent);font-weight:600">S/
                                        {{ number_format($p['precio_compra'], 2) }}</span>
                                </button>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            {{-- Tabla de items --}}
            <div class="card" style="padding:0;overflow:hidden">
                <table>
                    <thead>
                        <tr>
                            <th style="width:60px">#</th>
                            <th>Producto</th>
                            <th style="width:100px;text-align:center">Cantidad</th>
                            <th style="width:130px;text-align:right">P. Unitario</th>
                            <th style="width:100px;text-align:right">Descuento</th>
                            <th style="width:130px;text-align:right">Subtotal</th>
                            <th style="width:50px"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($items as $index => $item)
                            <tr>
                                <td style="color:var(--text-muted)">{{ $index + 1 }}</td>
                                <td>
                                    <strong>{{ $item['nombre'] }}</strong>
                                    <br><span style="font-size:0.7rem;color:var(--text-muted)">{{ $item['codigo'] }}</span>
                                </td>
                                <td>
                                    <input type="number" wire:model.live="items.{{ $index }}.cantidad" class="form-input"
                                        style="text-align:center;width:80px" step="0.01" min="0.01">
                                </td>
                                <td>
                                    <input type="number" wire:model.live="items.{{ $index }}.precio_unitario"
                                        class="form-input" style="text-align:right;width:110px" step="0.01" min="0">
                                </td>
                                <td>
                                    <input type="number" wire:model.live="items.{{ $index }}.descuento" class="form-input"
                                        style="text-align:right;width:80px" step="0.01" min="0">
                                </td>
                                <td style="text-align:right;font-weight:600">
                                    S/
                                    {{ number_format(($item['cantidad'] * $item['precio_unitario']) - $item['descuento'], 2) }}
                                </td>
                                <td style="text-align:center">
                                    <button wire:click="eliminarItem({{ $index }})"
                                        style="background:none;border:none;color:var(--danger);cursor:pointer">
                                        <i data-lucide="trash-2" style="width:16px;height:16px"></i>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" style="text-align:center;padding:40px;color:var(--text-muted)">
                                    <i data-lucide="package-plus"
                                        style="width:32px;height:32px;display:block;margin:0 auto 8px;opacity:0.5"></i>
                                    Agregue productos a la compra
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                @error('items') <p style="color:var(--danger);font-size:0.75rem;padding:8px 16px">{{ $message }}</p>
                @enderror
            </div>
        </div>

        {{-- Panel derecho: Documento + Totales --}}
        <div>
            <div class="card" style="padding:20px;margin-bottom:16px">
                <p
                    style="font-weight:700;font-size:12px;text-transform:uppercase;color:var(--accent);margin:0 0 16px 0;letter-spacing:1px">
                    Documento</p>

                <div class="form-group">
                    <label class="form-label">Tipo Documento *</label>
                    <select wire:model="tipo_documento" class="form-select">
                        <option value="01">Factura</option>
                        <option value="03">Boleta</option>
                    </select>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Serie *</label>
                        <input type="text" wire:model="serie" class="form-input" placeholder="F001"
                            style="text-transform:uppercase">
                        @error('serie') <span style="color:var(--danger);font-size:0.75rem">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label">Correlativo *</label>
                        <input type="text" wire:model="correlativo" class="form-input" placeholder="00000001">
                        @error('correlativo') <span style="color:var(--danger);font-size:0.75rem">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Fecha Emisión *</label>
                        <input type="date" wire:model="fecha_emision" class="form-input">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Vencimiento</label>
                        <input type="date" wire:model="fecha_vencimiento" class="form-input">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Almacén *</label>
                        <select wire:model="almacen_id" class="form-select">
                            <option value="">— Seleccionar —</option>
                            @foreach($almacenes as $alm)
                                <option value="{{ $alm->id }}">{{ $alm->nombre }}</option>
                            @endforeach
                        </select>
                        @error('almacen_id') <span style="color:var(--danger);font-size:0.75rem">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label">Forma de Pago</label>
                        <select wire:model="forma_pago" class="form-select">
                            <option value="contado">Contado</option>
                            <option value="credito">Crédito</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Observaciones</label>
                    <textarea wire:model="observaciones" class="form-input" rows="2"
                        placeholder="Notas adicionales..."></textarea>
                </div>
            </div>

            {{-- Totales --}}
            <div class="card" style="padding:20px;margin-bottom:16px">
                <p
                    style="font-weight:700;font-size:12px;text-transform:uppercase;color:var(--accent);margin:0 0 16px 0;letter-spacing:1px">
                    Totales</p>
                <div style="display:flex;justify-content:space-between;margin-bottom:8px">
                    <span style="color:var(--text-muted)">Subtotal</span>
                    <span style="font-weight:600">S/ {{ number_format($subtotal, 2) }}</span>
                </div>
                <div style="display:flex;justify-content:space-between;margin-bottom:8px">
                    <span style="color:var(--text-muted)">IGV (18%)</span>
                    <span style="font-weight:600">S/ {{ number_format($igv, 2) }}</span>
                </div>
                <div
                    style="display:flex;justify-content:space-between;padding-top:12px;border-top:2px solid var(--accent)">
                    <span style="font-size:1.1rem;font-weight:800">TOTAL</span>
                    <span style="font-size:1.3rem;font-weight:800;color:var(--accent)">S/
                        {{ number_format($total, 2) }}</span>
                </div>
            </div>

            {{-- Botón guardar --}}
            <button wire:click="guardar" class="btn btn-primary" style="width:100%;padding:14px;font-size:1rem">
                <i data-lucide="save"></i> Registrar Compra
            </button>
        </div>
    </div>
</div>