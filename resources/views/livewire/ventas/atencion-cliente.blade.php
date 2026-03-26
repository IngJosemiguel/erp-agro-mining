<div class="pos-atencion-container">

    {{-- ═══════════════════════════════════════════════════════════
         MODAL DE ÉXITO + DESCARGA PDF (aparece tras emitir)
    ═══════════════════════════════════════════════════════════ --}}
    @if($ventaEmitida)
    <div class="modal-sunat-backdrop">
        <div class="modal-sunat-box">
            <div class="modal-sunat-icon">
                @if($sunatAceptado)
                    <div class="icon-check-circle"><i data-lucide="check-circle-2"></i></div>
                @else
                    <div class="icon-warning-circle"><i data-lucide="alert-triangle"></i></div>
                @endif
            </div>
            <h2 class="modal-sunat-title">
                @if($sunatAceptado) ¡Boleta Emitida! @else Venta Registrada @endif
            </h2>
            <p class="modal-sunat-num">{{ $ventaNumero }}</p>
            <p class="modal-sunat-msg">{{ $sunatMensaje }}</p>

            <div class="modal-sunat-info">
                <div><span>Cliente</span><strong>{{ $ventaCliente }}</strong></div>
                <div><span>Total</span><strong>S/ {{ $ventaTotal }}</strong></div>
                <div><span>Estado</span><strong class="{{ $sunatAceptado ? 'text-green' : 'text-yellow' }}">
                    {{ $sunatAceptado ? 'ACEPTADO POR SUNAT' : 'GUARDADO LOCAL' }}
                </strong></div>
            </div>

            <div class="modal-sunat-actions">
                @if($ventaId)
                    <a href="{{ route('ventas.pdf', $ventaId) }}" target="_blank" class="btn-pdf-download">
                        <i data-lucide="file-down"></i> Descargar PDF
                    </a>
                    <a href="{{ route('ventas.pdf', $ventaId) }}" target="_blank" class="btn-pdf-preview">
                        <i data-lucide="eye"></i> Ver PDF
                    </a>
                @endif
                <button wire:click="nuevaVenta" class="btn-nueva-venta">
                    <i data-lucide="plus-circle"></i> Nueva Venta
                </button>
            </div>
            <button wire:click="nuevaVenta" class="modal-close-x" title="Cerrar">
                <i data-lucide="x"></i>
            </button>
        </div>
    </div>
    @endif

    {{-- ═══════════════════════════════════════════════════════════
         HEADER
    ═══════════════════════════════════════════════════════════ --}}
    <div class="ac-header">
        <div>
            <h1 class="ac-title">
                <span class="ac-title-icon"><i data-lucide="receipt-text"></i></span>
                Atención al Cliente
            </h1>
            <p class="ac-subtitle">Emisión de comprobantes electrónicos — REYES SOLUTIONS</p>
        </div>
        <a href="{{ route('ventas.listado') }}" class="btn-back">
            <i data-lucide="arrow-left"></i> Listado de Ventas
        </a>
    </div>

    @if($errors->has('general'))
        <div class="ac-alert ac-alert-error">
            <i data-lucide="alert-circle"></i> {{ $errors->first('general') }}
        </div>
    @endif

    {{-- ═══════════════════════════════════════════════════════════
         GRID PRINCIPAL
    ═══════════════════════════════════════════════════════════ --}}
    <div class="ac-grid">

        {{-- ── COLUMNA IZQUIERDA ── --}}
        <div class="ac-sidebar">

            {{-- COMPROBANTE --}}
            <div class="ac-card">
                <div class="ac-card-header">
                    <i data-lucide="file-text"></i>
                    <span>Comprobante</span>
                </div>
                <div class="ac-card-body">
                    <div class="doc-tabs">
                        <button type="button" wire:click="$set('tipoDocumento','00')"
                            class="doc-tab {{ $tipoDocumento==='00'?'active':'' }}">
                            <i data-lucide="sticky-note"></i> Nota Venta
                        </button>
                        <button type="button" wire:click="$set('tipoDocumento','03')"
                            class="doc-tab {{ $tipoDocumento==='03'?'active green':'' }}">
                            <i data-lucide="receipt"></i> Boleta
                        </button>
                        <button type="button" wire:click="$set('tipoDocumento','01')"
                            class="doc-tab {{ $tipoDocumento==='01'?'active blue':'' }}">
                            <i data-lucide="file-badge"></i> Factura
                        </button>
                    </div>

                    <div class="ac-row-2 mt-3">
                        <div class="form-group">
                            <label class="form-label">Serie</label>
                            <select wire:model="serieSeleccionada" class="form-input">
                                @foreach($seriesDisponibles as $s)
                                    <option value="{{ $s->serie }}">{{ $s->serie }}</option>
                                @endforeach
                                @if($seriesDisponibles->isEmpty())
                                    <option value="">Sin series</option>
                                @endif
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Moneda</label>
                            <select wire:model="moneda" class="form-input">
                                <option value="PEN">S/ Soles</option>
                                <option value="USD">$ Dólares</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group mt-3">
                        <label class="form-label">Forma de Pago</label>
                        <div class="pago-tabs">
                            <button type="button" wire:click="$set('formaPago','contado')"
                                class="pago-tab {{ $formaPago==='contado'?'active':'' }}">
                                <i data-lucide="banknote"></i> Contado
                            </button>
                            <button type="button" wire:click="$set('formaPago','credito')"
                                class="pago-tab {{ $formaPago==='credito'?'active':'' }}">
                                <i data-lucide="credit-card"></i> Crédito
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- CLIENTE --}}
            <div class="ac-card mt-4">
                <div class="ac-card-header">
                    <i data-lucide="user-search"></i>
                    <span>Datos del Cliente</span>
                </div>
                <div class="ac-card-body">
                    <div class="cliente-search-wrap">
                        <select wire:model="clienteDocTipo" class="form-input doc-tipo-sel">
                            <option value="DNI">DNI</option>
                            <option value="RUC">RUC</option>
                            <option value="CE">C.E.</option>
                        </select>
                        <input wire:model.defer="clienteDocNumero"
                            wire:keydown.enter="consultarDniRuc"
                            type="text" class="form-input doc-num-input"
                            placeholder="Número de documento...">
                        <button wire:click="consultarDniRuc"
                            wire:loading.attr="disabled"
                            wire:target="consultarDniRuc"
                            class="btn-buscar-doc">
                            <span wire:loading.remove wire:target="consultarDniRuc">
                                <i data-lucide="search"></i>
                            </span>
                            <span wire:loading wire:target="consultarDniRuc">...</span>
                        </button>
                    </div>

                    @if($mensajeCliente)
                        <div class="cliente-msg cliente-msg-{{ $tipoMensajeCliente }}">
                            {{ $mensajeCliente }}
                        </div>
                    @endif
                    @error('clienteDocNumero')
                        <span class="ac-error">{{ $message }}</span>
                    @enderror

                    <div class="form-group mt-3">
                        <label class="form-label">Razón Social / Nombre</label>
                        <input wire:model="clienteRazonSocial" type="text"
                            class="form-input" placeholder="Nombre del cliente">
                        @error('clienteRazonSocial')<span class="ac-error">{{ $message }}</span>@enderror
                    </div>
                    <div class="form-group mt-2">
                        <label class="form-label">Dirección <span class="text-muted">(opcional)</span></label>
                        <input wire:model="clienteDireccion" type="text"
                            class="form-input" placeholder="Av. Ejemplo 123...">
                    </div>
                </div>
            </div>

            {{-- TOTALES + COBRAR --}}
            <div class="ac-card mt-4 cobro-card">
                <div class="ac-card-header">
                    <i data-lucide="calculator"></i>
                    <span>Resumen de Cobro</span>
                </div>
                <div class="ac-card-body">
                    <div class="totales-box">
                        <div class="t-row"><span>Subtotal (sin IGV)</span><span>S/ {{ number_format($subtotal,2) }}</span></div>
                        <div class="t-row"><span>IGV (18%)</span><span>S/ {{ number_format($igv,2) }}</span></div>
                        <div class="t-row t-total">
                            <span>TOTAL</span>
                            <span class="t-monto">S/ {{ number_format($total,2) }}</span>
                        </div>
                    </div>

                    <button wire:click="procesarVenta"
                        wire:loading.attr="disabled"
                        wire:target="procesarVenta"
                        class="btn-emitir {{ count($items)===0?'btn-emitir-disabled':'' }}"
                        {{ count($items)===0?'disabled':'' }}>
                        <span wire:loading.remove wire:target="procesarVenta">
                            <i data-lucide="send"></i>
                            @if(in_array($tipoDocumento,['01','03'])) Emitir a SUNAT @else Registrar Venta @endif
                        </span>
                        <span wire:loading wire:target="procesarVenta">
                            <i data-lucide="loader-2" class="spin"></i> Procesando...
                        </span>
                    </button>
                    @error('items')<span class="ac-error mt-2 block">{{ $message }}</span>@enderror
                </div>
            </div>
        </div>

        {{-- ── COLUMNA DERECHA: PRODUCTOS ── --}}
        <div class="ac-main ac-card">
            <div class="ac-card-header" style="border-radius:16px 16px 0 0">
                <i data-lucide="package-search"></i>
                <span>Detalle de Productos</span>
                @if(count($items)>0)
                    <span class="items-badge">{{ count($items) }} ítem(s)</span>
                @endif
            </div>

            {{-- Buscador --}}
            <div class="prod-search-wrap">
                <div class="prod-search-inner">
                    <i data-lucide="search" class="prod-search-icon"></i>
                    <input wire:model.live.debounce.300ms="buscarProducto"
                        type="text" class="prod-search-input"
                        placeholder="Busca por nombre, SKU o escanea código de barras...">
                    <button class="btn-barcode" title="Escáner">
                        <i data-lucide="scan-barcode"></i>
                    </button>
                </div>

                @if(count($productosResultados)>0)
                    <div class="prod-results-dropdown">
                        @foreach($productosResultados as $prod)
                            <div class="prod-result-row" wire:click="agregarProducto({{ $prod['id'] }})">
                                <div class="prod-info">
                                    <span class="prod-nombre">{{ $prod['nombre'] }}</span>
                                    <span class="prod-meta">SKU: {{ $prod['codigo'] }} | Stock: {{ number_format($prod['stock'],2) }}</span>
                                </div>
                                <div class="prod-right">
                                    <span class="prod-precio">S/ {{ number_format($prod['precio'],2) }}</span>
                                    <span class="prod-add-btn"><i data-lucide="plus"></i></span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- Tabla Items --}}
            <div class="items-table-wrap">
                <table class="items-table">
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th class="text-center" width="130">Precio Unit.</th>
                            <th class="text-center" width="100">Cantidad</th>
                            <th class="text-center" width="100">Descuento</th>
                            <th class="text-right" width="120">Total</th>
                            <th width="50"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($items as $idx => $item)
                            <tr class="item-row">
                                <td>
                                    <div class="item-nombre">{{ $item['nombre'] }}</div>
                                    <div class="item-meta">
                                        {{ $item['codigo'] }}
                                        @if($item['es_gravado_igv'])
                                            <span class="igv-badge">IGV</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="text-center">
                                    <div class="input-prefix">
                                        <span>S/</span>
                                        <input type="number" wire:model.lazy="items.{{$idx}}.precio_unitario"
                                            wire:change="calcularTotales"
                                            class="item-input text-right" step="0.01" min="0">
                                    </div>
                                </td>
                                <td class="text-center">
                                    <div class="qty-control">
                                        <button wire:click="decrementarItem({{$idx}})" class="qty-btn">−</button>
                                        <input type="number" wire:model.lazy="items.{{$idx}}.cantidad"
                                            wire:change="calcularTotales"
                                            class="item-input text-center qty-num" step="0.01" min="0.01">
                                        <button wire:click="incrementarItem({{$idx}})" class="qty-btn">+</button>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <input type="number" wire:model.lazy="items.{{$idx}}.descuento"
                                        wire:change="calcularTotales"
                                        class="item-input text-right" step="0.01" min="0" placeholder="0.00">
                                </td>
                                <td class="text-right item-total">
                                    S/ {{ number_format(($item['precio_unitario']*$item['cantidad'])-$item['descuento'],2) }}
                                </td>
                                <td class="text-center">
                                    <button wire:click="eliminarItem({{$idx}})" class="btn-del-item" title="Quitar">
                                        <i data-lucide="x"></i>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="carrito-empty">
                                    <i data-lucide="shopping-cart"></i>
                                    <p>Carrito vacío</p>
                                    <span>Busca un producto arriba para agregarlo</span>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if(count($items)>0)
                <div class="items-info-bar">
                    <i data-lucide="info"></i>
                    El stock se descuenta automáticamente al emitir. NRUS solo emite Boletas.
                </div>
            @endif
        </div>
    </div>
</div>

@push('styles')
<style>
/* ═══════════════════════════════════════════════
   ATENCIÓN AL CLIENTE — DISEÑO PREMIUM
═══════════════════════════════════════════════ */
.pos-atencion-container { padding: 0; }

/* HEADER */
.ac-header { display:flex; align-items:center; justify-content:space-between; margin-bottom:24px; flex-wrap:wrap; gap:12px; }
.ac-title { font-size:1.6rem; font-weight:800; display:flex; align-items:center; gap:10px; margin:0; }
.ac-title-icon { width:40px; height:40px; border-radius:10px; background:linear-gradient(135deg,var(--accent),#00c8a0); display:flex; align-items:center; justify-content:center; }
.ac-title-icon i { width:20px; height:20px; color:#000; }
.ac-subtitle { color:var(--text-muted); font-size:0.85rem; margin:4px 0 0 50px; }
.btn-back { display:inline-flex; align-items:center; gap:6px; padding:8px 16px; background:var(--bg-card); border:1px solid var(--border-color); border-radius:8px; color:var(--text-primary); font-size:0.85rem; font-weight:600; text-decoration:none; transition:.2s; }
.btn-back:hover { background:var(--border-color); }
.btn-back i { width:16px; height:16px; }

/* ALERT */
.ac-alert { display:flex; align-items:center; gap:10px; padding:12px 16px; border-radius:10px; margin-bottom:16px; font-size:0.875rem; font-weight:500; }
.ac-alert-error { background:rgba(239,68,68,.1); border:1px solid rgba(239,68,68,.3); color:#ef4444; }
.ac-alert i { width:18px; height:18px; flex-shrink:0; }

/* GRID */
.ac-grid { display:grid; grid-template-columns:380px 1fr; gap:20px; align-items:start; }
@media (max-width:1100px) { .ac-grid { grid-template-columns:1fr; } }

/* CARDS */
.ac-card { background:var(--bg-card); border:1px solid var(--border-color); border-radius:16px; overflow:hidden; }
.ac-card-header { display:flex; align-items:center; gap:10px; padding:14px 18px; background:rgba(255,255,255,.03); border-bottom:1px solid var(--border-color); font-weight:700; font-size:0.9rem; }
.ac-card-header i { width:18px; height:18px; color:var(--accent); }
.items-badge { margin-left:auto; background:var(--accent); color:#000; font-size:0.7rem; font-weight:800; padding:2px 8px; border-radius:20px; }
.ac-card-body { padding:18px; }
.mt-3 { margin-top:12px; } .mt-2 { margin-top:8px; } .mt-4 { margin-top:16px; }

/* DOC TABS */
.doc-tabs { display:flex; gap:6px; }
.doc-tab { flex:1; display:flex; flex-direction:column; align-items:center; gap:4px; padding:10px 6px; background:var(--bg-body); border:1.5px solid var(--border-color); border-radius:10px; font-size:0.75rem; font-weight:600; color:var(--text-muted); cursor:pointer; transition:.2s; }
.doc-tab i { width:18px; height:18px; }
.doc-tab.active { background:var(--primary-color); border-color:var(--primary-color); color:#fff; }
.doc-tab.active.green { background:var(--accent); border-color:var(--accent); color:#000; }
.doc-tab.active.blue { background:#3b82f6; border-color:#3b82f6; color:#fff; }
.ac-row-2 { display:grid; grid-template-columns:1fr 1fr; gap:10px; }

/* PAGO TABS */
.pago-tabs { display:flex; gap:6px; }
.pago-tab { flex:1; display:flex; align-items:center; justify-content:center; gap:6px; padding:9px; background:var(--bg-body); border:1.5px solid var(--border-color); border-radius:8px; font-size:0.8rem; font-weight:600; color:var(--text-muted); cursor:pointer; transition:.2s; }
.pago-tab i { width:15px; height:15px; }
.pago-tab.active { background:var(--primary-color); border-color:var(--primary-color); color:#fff; }

/* CLIENTE SEARCH */
.cliente-search-wrap { display:flex; align-items:stretch; gap:0; }
.doc-tipo-sel { width:72px; flex-shrink:0; border-radius:8px 0 0 8px; border-right:none; font-size:0.8rem; padding:8px 4px; }
.doc-num-input { flex:1; border-radius:0; border-left:none; border-right:none; }
.btn-buscar-doc { padding:0 14px; background:var(--primary-color); border:none; border-radius:0 8px 8px 0; color:#fff; cursor:pointer; display:flex; align-items:center; justify-content:center; font-weight:700; transition:.2s; }
.btn-buscar-doc i { width:16px; height:16px; }
.btn-buscar-doc:hover { opacity:.85; }
.cliente-msg { padding:6px 10px; border-radius:6px; font-size:0.78rem; font-weight:500; margin-top:8px; }
.cliente-msg-success { background:rgba(0,255,136,.1); color:var(--accent); }
.cliente-msg-error { background:rgba(239,68,68,.1); color:#ef4444; }
.cliente-msg-warning { background:rgba(245,158,11,.1); color:#f59e0b; }
.ac-error { color:#ef4444; font-size:0.75rem; display:block; margin-top:4px; }

/* TOTALES */
.cobro-card .ac-card-header { background:linear-gradient(135deg,rgba(0,255,136,.08),rgba(0,200,160,.05)); }
.totales-box { background:var(--bg-body); border-radius:10px; padding:14px; margin-bottom:14px; }
.t-row { display:flex; justify-content:space-between; font-size:0.85rem; color:var(--text-muted); padding:4px 0; }
.t-total { border-top:1px dashed var(--border-color); margin-top:8px; padding-top:10px; font-weight:800; font-size:1rem; color:var(--text-primary); }
.t-monto { font-size:1.5rem; color:var(--accent); font-weight:900; }
/* ── Botón principal Emitir / Registrar Venta ── */
.btn-emitir {
    width: 100%;
    padding: 16px 20px;
    background: linear-gradient(135deg, #00ff88 0%, #00c8a0 50%, #00a8ff 100%);
    background-size: 200% 200%;
    border: none;
    border-radius: 14px;
    color: #000;
    font-size: 1.05rem;
    font-weight: 900;
    letter-spacing: 0.4px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    position: relative;
    overflow: hidden;
    transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
    box-shadow: 0 4px 20px rgba(0, 255, 136, 0.25);
    animation: gradientShift 4s ease infinite;
}
@keyframes gradientShift {
    0%   { background-position: 0% 50%; }
    50%  { background-position: 100% 50%; }
    100% { background-position: 0% 50%; }
}
/* Shimmer sweep */
.btn-emitir::after {
    content: '';
    position: absolute;
    inset: 0;
    background: linear-gradient(105deg, transparent 30%, rgba(255,255,255,0.35) 50%, transparent 70%);
    transform: translateX(-100%);
    transition: transform 0.6s ease;
}
.btn-emitir:hover:not([disabled])::after { transform: translateX(100%); }
.btn-emitir:hover:not([disabled]) {
    transform: translateY(-3px) scale(1.01);
    box-shadow: 0 12px 35px rgba(0, 255, 136, 0.45), 0 0 0 3px rgba(0,255,136,0.15);
}
.btn-emitir:active:not([disabled]) {
    transform: translateY(-1px) scale(0.99);
    box-shadow: 0 4px 15px rgba(0, 255, 136, 0.3);
}
.btn-emitir svg, .btn-emitir i { width: 22px; height: 22px; flex-shrink: 0; }

/* Estado deshabilitado */
.btn-emitir-disabled,
.btn-emitir[disabled] {
    background: var(--border-color);
    color: var(--text-muted);
    cursor: not-allowed;
    box-shadow: none;
    animation: none;
    transform: none !important;
}
.btn-emitir[disabled]::after { display: none; }

@keyframes spin { to { transform: rotate(360deg); } }
.spin { animation: spin 0.8s linear infinite; }

/* PRODUCT SEARCH */
.prod-search-wrap { padding:16px 18px 0; position:relative; }
.prod-search-inner { display:flex; align-items:center; position:relative; }
.prod-search-icon { position:absolute; left:14px; width:18px; height:18px; color:var(--text-muted); }
.prod-search-input { width:100%; padding:12px 50px 12px 44px; background:var(--bg-body); border:1.5px solid var(--border-color); border-radius:10px; color:var(--text-primary); font-size:0.9rem; transition:.2s; }
.prod-search-input:focus { border-color:var(--accent); outline:none; box-shadow:0 0 0 3px rgba(0,255,136,.15); }
.btn-barcode { position:absolute; right:10px; background:none; border:none; color:var(--text-muted); cursor:pointer; width:32px; height:32px; display:flex; align-items:center; justify-content:center; }
.btn-barcode i { width:18px; height:18px; }
.prod-results-dropdown { position:absolute; top:calc(100% + 4px); left:18px; right:18px; background:var(--bg-card); border:1px solid var(--border-color); border-radius:12px; z-index:999; box-shadow:0 15px 40px rgba(0,0,0,.5); max-height:300px; overflow-y:auto; }
.prod-result-row { display:flex; align-items:center; padding:12px 16px; border-bottom:1px solid var(--border-color); cursor:pointer; transition:.15s; gap:12px; }
.prod-result-row:hover { background:rgba(0,255,136,.06); }
.prod-result-row:last-child { border-bottom:none; }
.prod-info { flex:1; }
.prod-nombre { font-size:0.875rem; font-weight:700; color:var(--text-primary); display:block; }
.prod-meta { font-size:0.75rem; color:var(--text-muted); }
.prod-right { display:flex; align-items:center; gap:10px; }
.prod-precio { font-size:1rem; font-weight:800; color:var(--accent); }
.prod-add-btn { width:28px; height:28px; background:var(--accent); color:#000; border-radius:6px; display:flex; align-items:center; justify-content:center; flex-shrink:0; }
.prod-add-btn i { width:14px; height:14px; }

/* ITEMS TABLE */
.items-table-wrap { padding:0 0 0 0; overflow-x:auto; }
.items-table { width:100%; border-collapse:collapse; }
.items-table thead tr { background:var(--bg-body); }
.items-table th { padding:10px 14px; font-size:0.73rem; font-weight:700; color:var(--text-muted); text-transform:uppercase; letter-spacing:.05em; border-bottom:1px solid var(--border-color); }
.item-row { border-bottom:1px solid var(--border-color); transition:.15s; }
.item-row:hover { background:rgba(255,255,255,.02); }
.items-table td { padding:10px 14px; vertical-align:middle; }
.item-nombre { font-size:0.875rem; font-weight:700; color:var(--text-primary); }
.item-meta { font-size:0.72rem; color:var(--text-muted); margin-top:2px; display:flex; align-items:center; gap:6px; }
.igv-badge { background:rgba(0,255,136,.15); color:var(--accent); font-size:0.6rem; font-weight:800; padding:1px 5px; border-radius:4px; }
.item-input { background:var(--bg-body); border:1px solid var(--border-color); border-radius:6px; color:var(--text-primary); font-size:0.85rem; font-weight:600; padding:5px 8px; width:80px; }
.item-input:focus { border-color:var(--accent); outline:none; }
.input-prefix { display:flex; align-items:center; gap:0; position:relative; }
.input-prefix span { position:absolute; left:8px; font-size:0.75rem; color:var(--text-muted); }
.input-prefix .item-input { padding-left:24px; }
.qty-control { display:flex; align-items:center; gap:4px; justify-content:center; }
.qty-btn { width:24px; height:24px; background:var(--border-color); border:none; border-radius:5px; color:var(--text-primary); font-size:1rem; font-weight:700; cursor:pointer; display:flex; align-items:center; justify-content:center; transition:.15s; }
.qty-btn:hover { background:var(--accent); color:#000; }
.qty-num { width:52px; text-align:center; }
.item-total { font-weight:800; color:var(--text-primary); font-size:0.95rem; }
.btn-del-item { width:26px; height:26px; background:rgba(239,68,68,.1); border:none; border-radius:6px; color:#ef4444; cursor:pointer; display:flex; align-items:center; justify-content:center; transition:.15s; }
.btn-del-item:hover { background:#ef4444; color:#fff; }
.btn-del-item i { width:13px; height:13px; }
.carrito-empty { text-align:center; padding:60px 20px; color:var(--text-muted); }
.carrito-empty i { width:56px; height:56px; display:block; margin:0 auto 12px; opacity:.3; }
.carrito-empty p { font-size:1rem; font-weight:700; margin:0 0 4px; }
.carrito-empty span { font-size:0.8rem; }
.items-info-bar { padding:10px 18px; background:rgba(245,158,11,.06); border-top:1px solid var(--border-color); font-size:0.78rem; color:#f59e0b; display:flex; align-items:center; gap:8px; }
.items-info-bar i { width:14px; height:14px; flex-shrink:0; }

/* ═══ MODAL ÉXITO SUNAT ═══ */
.modal-sunat-backdrop { position:fixed; inset:0; background:rgba(0,0,0,.75); backdrop-filter:blur(6px); z-index:9999; display:flex; align-items:center; justify-content:center; padding:20px; }
.modal-sunat-box { background:var(--bg-card); border:1px solid var(--border-color); border-radius:20px; padding:40px 36px 32px; max-width:480px; width:100%; text-align:center; position:relative; animation:modalIn .35s cubic-bezier(.34,1.56,.64,1); }
@keyframes modalIn { from { transform:scale(.85); opacity:0; } to { transform:scale(1); opacity:1; } }
.modal-sunat-icon { margin-bottom:20px; }
.icon-check-circle i, .icon-warning-circle i { width:64px; height:64px; }
.icon-check-circle i { color:var(--accent); filter:drop-shadow(0 0 20px rgba(0,255,136,.4)); }
.icon-warning-circle i { color:#f59e0b; }
.modal-sunat-title { font-size:1.6rem; font-weight:900; margin:0 0 6px; }
.modal-sunat-num { font-family:monospace; font-size:1.1rem; color:var(--accent); font-weight:700; margin:0 0 8px; }
.modal-sunat-msg { color:var(--text-muted); font-size:0.85rem; margin:0 0 24px; }
.modal-sunat-info { background:var(--bg-body); border-radius:10px; padding:14px 16px; margin-bottom:24px; display:flex; flex-direction:column; gap:8px; }
.modal-sunat-info div { display:flex; justify-content:space-between; font-size:0.85rem; }
.modal-sunat-info span { color:var(--text-muted); }
.modal-sunat-info strong { color:var(--text-primary); }
.text-green { color:var(--accent) !important; }
.text-yellow { color:#f59e0b !important; }
.modal-sunat-actions { display:flex; gap:10px; flex-wrap:wrap; justify-content:center; }
.btn-pdf-download { display:inline-flex; align-items:center; gap:8px; padding:11px 20px; background:linear-gradient(135deg,var(--accent),#00c8a0); color:#000; font-weight:800; border-radius:10px; text-decoration:none; font-size:0.875rem; transition:.2s; }
.btn-pdf-download:hover { transform:translateY(-2px); box-shadow:0 8px 20px rgba(0,255,136,.3); }
.btn-pdf-preview { display:inline-flex; align-items:center; gap:8px; padding:11px 20px; background:var(--bg-body); border:1.5px solid var(--border-color); color:var(--text-primary); font-weight:700; border-radius:10px; text-decoration:none; font-size:0.875rem; transition:.2s; }
.btn-pdf-preview:hover { background:var(--border-color); }
.btn-nueva-venta { display:inline-flex; align-items:center; gap:8px; padding:11px 20px; background:var(--primary-color); border:none; color:#fff; font-weight:700; border-radius:10px; cursor:pointer; font-size:0.875rem; transition:.2s; }
.btn-nueva-venta:hover { opacity:.85; }
.btn-pdf-download i, .btn-pdf-preview i, .btn-nueva-venta i { width:16px; height:16px; }
.modal-close-x { position:absolute; top:14px; right:14px; background:none; border:none; color:var(--text-muted); cursor:pointer; width:32px; height:32px; display:flex; align-items:center; justify-content:center; border-radius:8px; transition:.15s; }
.modal-close-x:hover { background:var(--border-color); }
.modal-close-x i { width:18px; height:18px; }
</style>
@endpush

@push('scripts')
<script>
  lucide.createIcons();
  document.addEventListener('livewire:update', () => lucide.createIcons());
  document.addEventListener('livewire:navigated', () => lucide.createIcons());
</script>
@endpush