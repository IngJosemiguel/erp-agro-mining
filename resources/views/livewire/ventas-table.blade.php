<div>
    {{-- ─── Mensajes flash ──────────────────────────────────────────── --}}
    @if(session('success'))
        <div class="alert-success-erp">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert-error-erp">{{ session('error') }}</div>
    @endif

    {{-- ═══════════════════════════════════════════════════════════════
    ESTADÍSTICAS DEL DÍA
    ═══════════════════════════════════════════════════════════════ --}}
    <div class="stats-bar">
        <div class="stat-pill stat-green">
            <span class="stat-label">HOY</span>
            <span class="stat-value">S/ {{ number_format($estadisticas['total_dia'], 2) }}</span>
        </div>
        <div class="stat-pill stat-blue">
            <span class="stat-label">VENTAS HOY</span>
            <span class="stat-value">{{ $estadisticas['count_dia'] }}</span>
        </div>
        @if($estadisticas['pendientes_sunat'] > 0)
            <div class="stat-pill stat-amber">
                <span class="stat-label">PENDIENTES SUNAT</span>
                <span class="stat-value">{{ $estadisticas['pendientes_sunat'] }}</span>
            </div>
        @endif
    </div>

    {{-- ═══════════════════════════════════════════════════════════════
    BARRA DE CONTROLES
    ═══════════════════════════════════════════════════════════════ --}}
    <div class="controls-bar">
        <div class="controls-left">
            <div class="search-wrap">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="11" cy="11" r="8" />
                    <path d="m21 21-4.3-4.3" />
                </svg>
                <input wire:model.live.debounce.400ms="buscar" type="text" placeholder="Buscar comprobante, cliente..."
                    class="search-input">
            </div>
            <select wire:model.live="filtroTipoDoc" class="filter-select">
                <option value="">Todos los tipos</option>
                <option value="00">Nota de Venta</option>
                <option value="01">Factura</option>
                <option value="03">Boleta</option>
                <option value="07">Nota de Crédito</option>
            </select>
            <select wire:model.live="filtroEstado" class="filter-select">
                <option value="">Todos los estados</option>
                <option value="borrador">Borrador</option>
                <option value="emitido">Emitido</option>
                <option value="enviado_sunat">Enviado SUNAT</option>
                <option value="aceptado">Aceptado</option>
                <option value="rechazado">Rechazado</option>
                <option value="anulado">Anulado</option>
            </select>
            <input wire:model.live="filtroFechaDesde" type="date" class="filter-select" style="width:140px">
            <input wire:model.live="filtroFechaHasta" type="date" class="filter-select" style="width:140px">
        </div>
        <div class="controls-right">
            <button wire:click="abrirModal" class="btn-primary">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M5 12h14" />
                    <path d="M12 5v14" />
                </svg>
                Nueva Venta
            </button>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════════════════
    TABLA DE VENTAS
    ═══════════════════════════════════════════════════════════════ --}}
    <div class="table-container">
        <table class="erp-table">
            <thead>
                <tr>
                    <th>Comprobante</th>
                    <th>Fecha</th>
                    <th>Cliente</th>
                    <th>Moneda</th>
                    <th class="text-right">Total</th>
                    <th>Pago</th>
                    <th>Estado SUNAT</th>
                    <th class="text-center">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($ventas as $venta)
                                <tr class="table-row {{ $venta->estado === 'anulado' ? 'row-anulado' : '' }}">
                                    <td>
                                        <div class="comprobante-cell">
                                            <span class="tipo-doc-badge tipo-{{ $venta->tipo_documento }}">
                                                {{ match ($venta->tipo_documento) {
                        '00' => 'NV',
                        '01' => 'F',
                        '03' => 'B',
                        '07' => 'NC',
                        '08' => 'ND',
                        default => '?'
                    } }}
                                            </span>
                                            <span
                                                class="numero-doc">{{ $venta->serie }}-{{ str_pad($venta->correlativo, 8, '0', STR_PAD_LEFT) }}</span>
                                        </div>
                                    </td>
                                    <td class="fecha-cell">{{ $venta->fecha_emision?->format('d/m/Y') }}</td>
                                    <td>
                                        <div class="cliente-cell">
                                            <span class="cliente-nombre">{{ $venta->cliente?->razon_social ?? '—' }}</span>
                                            @if($venta->cliente?->numero_documento)
                                                <span class="cliente-doc">{{ $venta->cliente->numero_documento }}</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td><span class="moneda-badge">{{ $venta->moneda }}</span></td>
                                    <td class="text-right font-semibold">{{ number_format($venta->total, 2) }}</td>
                                    <td>
                                        <span class="pago-badge pago-{{ $venta->forma_pago }}">
                                            {{ ucfirst($venta->forma_pago ?? 'contado') }}
                                        </span>
                                    </td>
                                    <td>
                                        @php
                                            $estadoConfig = [
                                                'borrador' => ['color' => 'estado-gray', 'label' => 'Borrador'],
                                                'emitido' => ['color' => 'estado-blue', 'label' => 'Emitido'],
                                                'enviado_sunat' => ['color' => 'estado-blue', 'label' => 'Enviado'],
                                                'aceptado' => ['color' => 'estado-green', 'label' => 'Aceptado'],
                                                'rechazado' => ['color' => 'estado-red', 'label' => 'Rechazado'],
                                                'por_rectificar' => ['color' => 'estado-amber', 'label' => 'Rectificar'],
                                                'anulado' => ['color' => 'estado-gray', 'label' => 'Anulado'],
                                            ];
                                            $config = $estadoConfig[$venta->estado] ?? ['color' => 'estado-gray', 'label' => $venta->estado];
                                        @endphp
                                        <div class="estado-wrap">
                                            <span class="estado-badge {{ $config['color'] }}">{{ $config['label'] }}</span>
                                            @if($venta->sunat_codigo_respuesta)
                                                <span class="sunat-code" title="{{ $venta->sunat_mensaje_respuesta }}">
                                                    {{ $venta->sunat_codigo_respuesta }}
                                                </span>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <div class="actions-cell">
                                            {{-- Ver detalle --}}
                                            <button wire:click="verDetalle({{ $venta->id }})" class="btn-icon btn-icon-blue"
                                                title="Ver detalle">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24"
                                                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                                    stroke-linejoin="round">
                                                    <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z" />
                                                    <circle cx="12" cy="12" r="3" />
                                                </svg>
                                            </button>

                                            {{-- Enviar a SUNAT (solo facturas/boletas pendientes) --}}
                                            @if(in_array($venta->tipo_documento, ['01', '03', '07', '08']) && in_array($venta->estado, ['emitido', 'rechazado']))
                                                <button wire:click="enviarSunat({{ $venta->id }})" wire:loading.attr="disabled"
                                                    wire:target="enviarSunat({{ $venta->id }})" class="btn-icon btn-icon-green"
                                                    title="Enviar a SUNAT">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24"
                                                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                                        stroke-linejoin="round">
                                                        <path d="m22 2-7 20-4-9-9-4Z" />
                                                        <path d="M22 2 11 13" />
                                                    </svg>
                                                </button>
                                            @endif

                                            {{-- Descargar XML --}}
                                            @if($venta->xml_path)
                                                <a href="{{ Storage::url($venta->xml_path) }}" target="_blank"
                                                    class="btn-icon btn-icon-amber" title="Descargar XML">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24"
                                                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                                        stroke-linejoin="round">
                                                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
                                                        <polyline points="7 10 12 15 17 10" />
                                                        <line x1="12" x2="12" y1="15" y2="3" />
                                                    </svg>
                                                </a>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                @empty
                    <tr>
                        <td colspan="8" class="empty-state">
                            <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
                                <polyline points="14 2 14 8 20 8" />
                                <line x1="16" x2="8" y1="13" y2="13" />
                                <line x1="16" x2="8" y1="17" y2="17" />
                                <polyline points="10 9 9 9 8 9" />
                            </svg>
                            <p>No hay ventas registradas con los filtros actuales.</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Paginación --}}
    <div class="pagination-wrap">
        {{ $ventas->links() }}
    </div>

    {{-- ═══════════════════════════════════════════════════════════════
    MODAL: NUEVA VENTA
    ═══════════════════════════════════════════════════════════════ --}}
    @if($mostrarModal)
        <div class="modal-overlay" wire:click.self="cerrarModal">
            <div class="modal-panel modal-xl">
                <div class="modal-header">
                    <div>
                        <h2 class="modal-title">Nueva Venta</h2>
                        <p class="modal-subtitle">Completa los datos del comprobante</p>
                    </div>
                    <button wire:click="cerrarModal" class="modal-close">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M18 6 6 18" />
                            <path d="m6 6 12 12" />
                        </svg>
                    </button>
                </div>

                <div class="modal-body">
                    @if($errors->has('general'))
                        <div class="alert-error-erp mb-4">{{ $errors->first('general') }}</div>
                    @endif

                    <div class="venta-form-grid">
                        {{-- ── Columna Izquierda: Cabecera + Cliente ── --}}
                        <div class="venta-col-left">

                            {{-- Tipo de comprobante y moneda --}}
                            <div class="form-section">
                                <h3 class="section-title">Comprobante</h3>
                                <div class="tipo-doc-selector">
                                    @foreach([['00', 'Nota de Venta', 'nv'], ['01', 'Factura', 'f'], ['03', 'Boleta', 'b']] as [$cod, $label, $cls])
                                        <button type="button" wire:click="$set('tipoDocumento', '{{ $cod }}')"
                                            class="tipo-btn {{ $tipoDocumento === $cod ? 'tipo-btn-active tipo-btn-' . $cls : '' }}">
                                            <span
                                                class="tipo-prefix">{{ $cod === '00' ? 'NV' : ($cod === '01' ? 'F' : 'B') }}</span>
                                            <span class="tipo-label">{{ $label }}</span>
                                        </button>
                                    @endforeach
                                </div>
                                <div class="form-row-2">
                                    <div class="form-group">
                                        <label class="form-label">Moneda</label>
                                        <select wire:model="moneda" class="form-input">
                                            <option value="PEN">PEN - Soles</option>
                                            <option value="USD">USD - Dólares</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label">Forma de Pago</label>
                                        <select wire:model="formaPago" class="form-input">
                                            <option value="contado">Contado</option>
                                            <option value="credito">Crédito</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            {{-- Cliente --}}
                            <div class="form-section">
                                <h3 class="section-title">Cliente</h3>
                                <div class="cliente-search-row">
                                    <select wire:model="clienteDocTipo" class="form-input"
                                        style="width:100px;flex-shrink:0">
                                        <option value="DNI">DNI</option>
                                        <option value="RUC">RUC</option>
                                        <option value="CE">C.E.</option>
                                    </select>
                                    <input wire:model.blur="clienteDocNumero" wire:keydown.enter="consultarDocumentoCliente"
                                        type="text"
                                        placeholder="{{ $clienteDocTipo === 'RUC' ? '11 dígitos' : '8 dígitos' }}"
                                        class="form-input flex-1 {{ $errors->has('clienteDocNumero') ? 'input-error' : '' }}"
                                        maxlength="{{ $clienteDocTipo === 'RUC' ? 11 : 8 }}">
                                    <button wire:click="consultarDocumentoCliente" wire:loading.attr="disabled"
                                        wire:target="consultarDocumentoCliente" class="btn-secondary btn-icon-only"
                                        title="Buscar en RENIEC/SUNAT">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                                            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                            stroke-linejoin="round">
                                            <circle cx="11" cy="11" r="8" />
                                            <path d="m21 21-4.3-4.3" />
                                        </svg>
                                        <span wire:loading wire:target="consultarDocumentoCliente"
                                            class="loading-text">...</span>
                                    </button>
                                </div>

                                @if($mensajeCliente)
                                    <p
                                        class="mensaje-cliente {{ str_starts_with($mensajeCliente, '✓') ? 'text-success' : 'text-warning' }}">
                                        {{ $mensajeCliente }}
                                    </p>
                                @endif

                                <div class="form-group mt-2">
                                    <label class="form-label">Razón Social / Nombre</label>
                                    <input wire:model="clienteRazonSocial" type="text"
                                        class="form-input {{ $errors->has('clienteRazonSocial') ? 'input-error' : '' }}"
                                        placeholder="Razón social o nombre completo">
                                    @error('clienteRazonSocial')<span class="error-text">{{ $message }}</span>@enderror
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Dirección</label>
                                    <input wire:model="clienteDireccion" type="text" class="form-input"
                                        placeholder="Dirección del cliente">
                                </div>
                            </div>

                            {{-- Observaciones --}}
                            <div class="form-group">
                                <label class="form-label">Observaciones</label>
                                <textarea wire:model="observaciones" class="form-input" rows="2"
                                    placeholder="Observaciones del comprobante"></textarea>
                            </div>
                        </div>

                        {{-- ── Columna Derecha: Productos + Totales ── --}}
                        <div class="venta-col-right">

                            {{-- Búsqueda de productos --}}
                            <div class="form-section">
                                <h3 class="section-title">Productos</h3>
                                <div class="producto-search-wrap">
                                    <input wire:model.live.debounce.300ms="buscarProducto" type="text"
                                        placeholder="Buscar por nombre, código o código de barras..." class="form-input">
                                    @if($buscarProducto)
                                        <button wire:click="limpiarBusquedaProducto" class="clear-search">✕</button>
                                    @endif

                                    {{-- Dropdown de resultados --}}
                                    @if(count($productosEncontrados) > 0)
                                        <div class="productos-dropdown">
                                            @foreach($productosEncontrados as $prod)
                                                <button type="button" wire:click="agregarProducto({{ $prod['id'] }})"
                                                    class="producto-item">
                                                    <div class="prod-info">
                                                        <span class="prod-codigo">{{ $prod['codigo'] }}</span>
                                                        <span class="prod-nombre">{{ $prod['nombre'] }}</span>
                                                    </div>
                                                    <div class="prod-meta">
                                                        <span class="prod-precio">S/ {{ number_format($prod['precio'], 2) }}</span>
                                                        <span class="prod-stock {{ $prod['stock'] <= 0 ? 'stock-cero' : '' }}">
                                                            Stock: {{ number_format($prod['stock'], 2) }}
                                                        </span>
                                                    </div>
                                                </button>
                                            @endforeach
                                        </div>
                                    @elseif($buscarProducto && strlen($buscarProducto) >= 2)
                                        <div class="no-results">No se encontraron productos.</div>
                                    @endif
                                </div>

                                @error('items')<span class="error-text">{{ $message }}</span>@enderror
                            </div>

                            {{-- Tabla de ítems --}}
                            <div class="items-table-wrap">
                                @if(count($items) === 0)
                                    <div class="items-empty">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24"
                                            fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round"
                                            stroke-linejoin="round">
                                            <circle cx="8" cy="21" r="1" />
                                            <circle cx="19" cy="21" r="1" />
                                            <path
                                                d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12" />
                                        </svg>
                                        <p>Agrega productos usando el buscador</p>
                                    </div>
                                @else
                                    <table class="items-table">
                                        <thead>
                                            <tr>
                                                <th>Producto</th>
                                                <th style="width:70px">Cant.</th>
                                                <th style="width:90px">Precio</th>
                                                <th style="width:80px">Desc.</th>
                                                <th style="width:90px" class="text-right">Total</th>
                                                <th style="width:30px"></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($items as $idx => $item)
                                                <tr>
                                                    <td>
                                                        <div class="item-nombre">{{ $item['nombre'] }}</div>
                                                        <div class="item-codigo">{{ $item['codigo'] }}</div>
                                                    </td>
                                                    <td>
                                                        <input wire:model.blur="items.{{ $idx }}.cantidad"
                                                            wire:change="calcularTotales" type="number" step="0.01" min="0.01"
                                                            class="item-input">
                                                    </td>
                                                    <td>
                                                        <input wire:model.blur="items.{{ $idx }}.precio_unitario"
                                                            wire:change="calcularTotales" type="number" step="0.01" min="0"
                                                            class="item-input">
                                                    </td>
                                                    <td>
                                                        <input wire:model.blur="items.{{ $idx }}.descuento"
                                                            wire:change="calcularTotales" type="number" step="0.01" min="0"
                                                            class="item-input">
                                                    </td>
                                                    <td class="text-right item-total">
                                                        S/
                                                        {{ number_format($item['total_item'] ?? ($item['cantidad'] * $item['precio_unitario']), 2) }}
                                                    </td>
                                                    <td>
                                                        <button wire:click="quitarItem({{ $idx }})" class="btn-remove">✕</button>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                @endif
                            </div>

                            {{-- Totales --}}
                            @if(count($items) > 0)
                                <div class="totales-box">
                                    <div class="total-row">
                                        <span>Base Imponible (BI):</span>
                                        <span>S/ {{ number_format($baseImponible, 2) }}</span>
                                    </div>
                                    <div class="total-row">
                                        <span>IGV (18%):</span>
                                        <span>S/ {{ number_format($igv, 2) }}</span>
                                    </div>
                                    @if($descuentoGlobal > 0)
                                        <div class="total-row text-red">
                                            <span>Descuento Global:</span>
                                            <span>- S/ {{ number_format($descuentoGlobal, 2) }}</span>
                                        </div>
                                    @endif
                                    <div class="total-row total-final">
                                        <span>TOTAL {{ $moneda }}:</span>
                                        <span>S/ {{ number_format($total, 2) }}</span>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Footer del modal --}}
                <div class="modal-footer">
                    <button wire:click="cerrarModal" class="btn-secondary">Cancelar</button>
                    <button wire:click="guardarVenta" wire:loading.attr="disabled" wire:target="guardarVenta"
                        class="btn-primary">
                        <span wire:loading.remove wire:target="guardarVenta">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z" />
                                <polyline points="17 21 17 13 7 13 7 21" />
                                <polyline points="7 3 7 8 15 8" />
                            </svg>
                            Guardar Venta
                        </span>
                        <span wire:loading wire:target="guardarVenta">Guardando...</span>
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>

@push('styles')
    <style>
        /* ─── Stats Bar ────────────────────────────────────────────── */
        .stats-bar {
            display: flex;
            gap: 12px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }

        .stat-pill {
            display: flex;
            flex-direction: column;
            padding: 12px 20px;
            border-radius: 10px;
            min-width: 140px;
        }

        .stat-green {
            background: rgba(16, 185, 129, .15);
            border: 1px solid rgba(16, 185, 129, .3);
        }

        .stat-blue {
            background: rgba(59, 130, 246, .15);
            border: 1px solid rgba(59, 130, 246, .3);
        }

        .stat-amber {
            background: rgba(245, 158, 11, .15);
            border: 1px solid rgba(245, 158, 11, .3);
        }

        .stat-label {
            font-size: 10px;
            font-weight: 700;
            letter-spacing: .08em;
            color: var(--text-muted);
        }

        .stat-value {
            font-size: 22px;
            font-weight: 700;
            color: var(--text-primary);
        }

        /* ─── Tipo doc badge ───────────────────────────────────────── */
        .tipo-doc-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 28px;
            height: 28px;
            border-radius: 6px;
            font-size: 10px;
            font-weight: 800;
            flex-shrink: 0;
        }

        .tipo-00 {
            background: rgba(100, 116, 139, .2);
            color: #94a3b8;
        }

        .tipo-01 {
            background: rgba(59, 130, 246, .2);
            color: #60a5fa;
        }

        .tipo-03 {
            background: rgba(16, 185, 129, .2);
            color: #34d399;
        }

        .tipo-07 {
            background: rgba(245, 158, 11, .2);
            color: #fbbf24;
        }

        .tipo-08 {
            background: rgba(239, 68, 68, .2);
            color: #f87171;
        }

        /* ─── Comprobante cell ─────────────────────────────────────── */
        .comprobante-cell {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .numero-doc {
            font-family: 'Courier New', monospace;
            font-size: 13px;
            font-weight: 600;
            color: var(--text-primary);
        }

        .cliente-cell {
            display: flex;
            flex-direction: column;
            gap: 2px;
        }

        .cliente-nombre {
            font-size: 13px;
            font-weight: 500;
            color: var(--text-primary);
        }

        .cliente-doc {
            font-size: 11px;
            color: var(--text-muted);
        }

        .moneda-badge {
            font-size: 11px;
            font-weight: 700;
            color: var(--text-muted);
            background: var(--bg-card);
            padding: 2px 6px;
            border-radius: 4px;
        }

        /* ─── Estado badges ────────────────────────────────────────── */
        .estado-wrap {
            display: flex;
            flex-direction: column;
            gap: 2px;
        }

        .estado-badge {
            font-size: 11px;
            font-weight: 600;
            padding: 2px 8px;
            border-radius: 99px;
            width: fit-content;
        }

        .estado-green {
            background: rgba(16, 185, 129, .15);
            color: #34d399;
        }

        .estado-blue {
            background: rgba(59, 130, 246, .15);
            color: #60a5fa;
        }

        .estado-amber {
            background: rgba(245, 158, 11, .15);
            color: #fbbf24;
        }

        .estado-red {
            background: rgba(239, 68, 68, .15);
            color: #f87171;
        }

        .estado-gray {
            background: rgba(100, 116, 139, .15);
            color: #94a3b8;
        }

        .sunat-code {
            font-size: 10px;
            color: var(--text-muted);
            font-family: monospace;
        }

        .pago-badge {
            font-size: 11px;
            font-weight: 600;
        }

        .pago-contado {
            color: #34d399;
        }

        .pago-credito {
            color: #fbbf24;
        }

        .row-anulado td {
            opacity: 0.5;
        }

        .fecha-cell {
            font-size: 13px;
            white-space: nowrap;
            color: var(--text-secondary);
        }

        /* ─── Modal Nueva Venta ─────────────────────────────────────── */
        .modal-xl {
            max-width: 1100px;
            width: 95vw;
        }

        .venta-form-grid {
            display: grid;
            grid-template-columns: 380px 1fr;
            gap: 24px;
        }

        .venta-col-left,
        .venta-col-right {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        .form-section {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 10px;
            padding: 16px;
        }

        .section-title {
            font-size: 12px;
            font-weight: 700;
            letter-spacing: .06em;
            text-transform: uppercase;
            color: var(--text-muted);
            margin-bottom: 12px;
        }

        /* Tipo doc selector */
        .tipo-doc-selector {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 8px;
            margin-bottom: 12px;
        }

        .tipo-btn {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 10px 8px;
            border-radius: 8px;
            border: 1px solid var(--border-color);
            background: var(--bg-card);
            cursor: pointer;
            transition: all .2s;
        }

        .tipo-btn:hover {
            border-color: var(--accent);
        }

        .tipo-btn-active {
            border-color: var(--accent) !important;
            background: rgba(16, 185, 129, .1) !important;
        }

        .tipo-prefix {
            font-size: 16px;
            font-weight: 800;
        }

        .tipo-label {
            font-size: 10px;
            color: var(--text-muted);
            margin-top: 2px;
        }

        /* Cliente search */
        .cliente-search-row {
            display: flex;
            gap: 8px;
            align-items: center;
        }

        .mensaje-cliente {
            font-size: 11px;
            margin-top: 6px;
        }

        .text-success {
            color: #34d399;
        }

        .text-warning {
            color: #fbbf24;
        }

        /* Producto search */
        .producto-search-wrap {
            position: relative;
        }

        .productos-dropdown {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            z-index: 50;
            background: var(--bg-sidebar);
            border: 1px solid var(--border-color);
            border-radius: 10px;
            max-height: 280px;
            overflow-y: auto;
            box-shadow: 0 20px 40px rgba(0, 0, 0, .4);
            margin-top: 4px;
        }

        .producto-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            width: 100%;
            padding: 10px 14px;
            border: none;
            background: transparent;
            cursor: pointer;
            border-bottom: 1px solid var(--border-color);
        }

        .producto-item:hover {
            background: rgba(16, 185, 129, .08);
        }

        .producto-item:last-child {
            border-bottom: none;
        }

        .prod-info {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            gap: 2px;
        }

        .prod-codigo {
            font-size: 10px;
            font-family: monospace;
            color: var(--text-muted);
        }

        .prod-nombre {
            font-size: 13px;
            color: var(--text-primary);
            font-weight: 500;
        }

        .prod-meta {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            gap: 2px;
        }

        .prod-precio {
            font-size: 13px;
            font-weight: 700;
            color: var(--accent);
        }

        .prod-stock {
            font-size: 11px;
            color: var(--text-muted);
        }

        .stock-cero {
            color: #f87171 !important;
        }

        .no-results {
            padding: 12px;
            text-align: center;
            font-size: 13px;
            color: var(--text-muted);
        }

        .clear-search {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: var(--text-muted);
            cursor: pointer;
            font-size: 14px;
        }

        /* Items table */
        .items-table-wrap {
            flex: 1;
            overflow: auto;
            min-height: 160px;
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 10px;
        }

        .items-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
        }

        .items-table th {
            padding: 8px 10px;
            font-size: 11px;
            font-weight: 700;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: .05em;
            border-bottom: 1px solid var(--border-color);
            background: var(--bg-card);
        }

        .items-table td {
            padding: 8px 10px;
            border-bottom: 1px solid rgba(255, 255, 255, .03);
            color: var(--text-primary);
            vertical-align: middle;
        }

        .items-table tr:last-child td {
            border-bottom: none;
        }

        .item-nombre {
            font-weight: 500;
            font-size: 12px;
        }

        .item-codigo {
            font-size: 10px;
            color: var(--text-muted);
            font-family: monospace;
        }

        .item-input {
            width: 100%;
            background: var(--bg-sidebar);
            border: 1px solid var(--border-color);
            border-radius: 6px;
            color: var(--text-primary);
            padding: 4px 6px;
            font-size: 12px;
            text-align: right;
        }

        .item-total {
            font-weight: 700;
            color: var(--text-primary);
            font-size: 13px;
        }

        .btn-remove {
            background: none;
            border: none;
            color: var(--text-muted);
            cursor: pointer;
            font-size: 12px;
            padding: 4px;
        }

        .btn-remove:hover {
            color: #f87171;
        }

        /* Items empty */
        .items-empty {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 40px 20px;
            color: var(--text-muted);
        }

        .items-empty p {
            font-size: 13px;
        }

        /* Totales */
        .totales-box {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 10px;
            padding: 14px 16px;
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .total-row {
            display: flex;
            justify-content: space-between;
            font-size: 13px;
            color: var(--text-secondary);
        }

        .total-final {
            font-size: 18px;
            font-weight: 800;
            color: var(--accent);
            padding-top: 8px;
            border-top: 1px solid var(--border-color);
            margin-top: 4px;
        }

        /* Alerts */
        .alert-success-erp {
            background: rgba(16, 185, 129, .15);
            border: 1px solid rgba(16, 185, 129, .3);
            color: #6ee7b7;
            padding: 10px 16px;
            border-radius: 8px;
            font-size: 13px;
            margin-bottom: 16px;
        }

        .alert-error-erp {
            background: rgba(239, 68, 68, .15);
            border: 1px solid rgba(239, 68, 68, .3);
            color: #fca5a5;
            padding: 10px 16px;
            border-radius: 8px;
            font-size: 13px;
            margin-bottom: 16px;
        }

        /* Btn icon solo */
        .btn-icon-only {
            display: flex;
            align-items: center;
            gap: 4px;
            padding: 8px 12px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 500;
            cursor: pointer;
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            color: var(--text-secondary);
        }

        .btn-icon-only:hover {
            border-color: var(--accent);
            color: var(--accent);
        }

        .loading-text {
            font-size: 11px;
        }

        @media (max-width: 900px) {
            .venta-form-grid {
                grid-template-columns: 1fr;
            }

            .modal-xl {
                max-width: 98vw;
            }
        }
    </style>
@endpush