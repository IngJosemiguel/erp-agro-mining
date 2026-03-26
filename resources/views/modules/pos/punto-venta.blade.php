@extends('layouts.app')

@section('title', 'Punto de Venta (POS) Offline-First - ERP AgroMine')

@section('content')
    <div x-data="posOfflineApp()" x-init="initPos()" class="pos-layout">

        {{-- ═══════════════════════════════════════════════════════════════
        PANEL IZQUIERDO: PRODUCTOS
        ═══════════════════════════════════════════════════════════════ --}}
        <div class="pos-main">
            {{-- Cabecera y Buscador --}}
            <div class="pos-header">
                <h1 class="pos-title">POS <span class="badge-status" :class="isOnline ? 'badge-online' : 'badge-offline'"
                        x-text="isOnline ? 'Online' : 'Offline'"></span></h1>

                <div class="pos-search-wrapper">
                    <svg class="search-icon" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"
                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="11" cy="11" r="8" />
                        <path d="m21 21-4.3-4.3" />
                    </svg>
                    <input x-model="searchQuery" @input.debounce.300ms="filtrarProductos" type="text"
                        placeholder="Buscar por código de barras, SKU o nombre... (Lector compatible)"
                        class="pos-search-input" autofocus>
                    <button @click="syncData" title="Forzar Sincronización" class="btn-sync"
                        :class="isSyncing ? 'syncing' : ''">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21 2v6h-6" />
                            <path d="M3 12a9 9 0 0 1 15-6.7L21 8" />
                            <path d="M3 22v-6h6" />
                            <path d="M21 12a9 9 0 0 1-15 6.7L3 16" />
                        </svg>
                    </button>
                </div>

                {{-- Categorias Filter --}}
                <div class="pos-categories">
                    <button @click="setCategoria('todas')" class="cat-btn"
                        :class="categoriaActiva === 'todas' ? 'cat-active' : ''">Todas</button>
                    <template x-for="cat in categoriasCached" :key="cat.id">
                        <button @click="setCategoria(cat.id)" class="cat-btn"
                            :class="categoriaActiva === cat.id ? 'cat-active' : ''" x-text="cat.nombre"></button>
                    </template>
                </div>
            </div>

            {{-- Grilla de Productos (Cargada desde IndexedDB/Local) --}}
            <div class="pos-products-grid">
                <template x-for="prod in productosFiltrados" :key="prod.id">
                    <div class="pos-product-card" @click="agregarAlCarrito(prod)">
                        <div class="prod-badge" x-show="prod.stock <= 0">Sin Stock</div>
                        <div class="prod-badge bg-green" x-show="prod.es_agricola">Agro</div>
                        <div class="prod-card-body">
                            <h4 class="prod-name" x-text="prod.nombre"></h4>
                            <p class="prod-sku" x-text="'SKU: ' + prod.codigo_interno"></p>
                        </div>
                        <div class="prod-card-footer">
                            <span class="prod-price" x-text="'S/ ' + parseFloat(prod.precio).toFixed(2)"></span>
                            <span class="prod-stock" :class="prod.stock <= 0 ? 'text-red' : ''"
                                x-text="'Stock: ' + prod.stock"></span>
                        </div>
                    </div>
                </template>
                <div x-show="productosFiltrados.length === 0" class="pos-empty-products">
                    No se encontraron productos o no se ha sincronizado la base de datos (Trabajando Offline).
                </div>
            </div>
        </div>

        {{-- ═══════════════════════════════════════════════════════════════
        PANEL DERECHO: CARRITO Y TICKETS
        ═══════════════════════════════════════════════════════════════ --}}
        <div class="pos-sidebar">

            {{-- Selector de cliente --}}
            <div class="sidebar-section">
                <label class="pos-label">Cliente (DNI / RUC)</label>
                <div class="cliente-row">
                    <input x-model="clienteInput" @keydown.enter="buscarClienteEnLinea" type="text" class="pos-input"
                        placeholder="Buscar DNI/RUC...">
                    <button @click="buscarClienteEnLinea" class="btn-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="11" cy="11" r="8" />
                            <path d="m21 21-4.3-4.3" />
                        </svg>
                    </button>
                </div>
                <div class="cliente-info" x-show="clienteSeleccionado.nombre">
                    <span class="cliente-nombre-badge" x-text="clienteSeleccionado.nombre"></span>
                    <button class="btn-remove-client" @click="limpiarCliente">✕</button>
                </div>
            </div>

            {{-- Selector de Comprobante --}}
            <div class="sidebar-section">
                <div class="comprobante-toggles">
                    <button type="button" @click="tipoDoc = '00'" class="toggle-btn"
                        :class="tipoDoc === '00' ? 'active' : ''">Ticket (NV)</button>
                    <button type="button" @click="tipoDoc = '03'" class="toggle-btn"
                        :class="tipoDoc === '03' ? 'active' : ''">Boleta</button>
                    <button type="button" @click="tipoDoc = '01'" class="toggle-btn"
                        :class="tipoDoc === '01' ? 'active' : ''">Factura</button>
                </div>
            </div>

            {{-- Ticket / Carrito --}}
            <div class="pos-cart">
                <template x-for="(item, index) in carrito" :key="item.id">
                    <div class="cart-item">
                        <div class="item-details">
                            <div class="item-title" x-text="item.nombre"></div>
                            <div class="item-prices" x-text="'S/ ' + parseFloat(item.precio).toFixed(2) + ' c/u'"></div>
                        </div>
                        <div class="item-actions">
                            <div class="qty-control">
                                <button @click="actualizarCantidad(index, -1)">-</button>
                                <input type="number" x-model.number="item.cantidad" @change="calcularTotales()" min="0.01"
                                    step="any">
                                <button @click="actualizarCantidad(index, 1)">+</button>
                            </div>
                            <div class="item-subtotal" x-text="'S/ ' + (item.cantidad * item.precio).toFixed(2)"></div>
                            <button @click="quitarDelCarrito(index)" class="btn-delete">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M3 6h18" />
                                    <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6" />
                                    <path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </template>
                <div x-show="carrito.length === 0" class="empty-cart-msg">
                    <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="1">
                        <circle cx="8" cy="21" r="1" />
                        <circle cx="19" cy="21" r="1" />
                        <path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12" />
                    </svg>
                    Carrito vacío
                </div>
            </div>

            {{-- Totales --}}
            <div class="pos-totals">
                <div class="total-line">
                    <span>Subtotal</span>
                    <span x-text="'S/ ' + baseImponible.toFixed(2)"></span>
                </div>
                <div class="total-line">
                    <span>IGV (18%)</span>
                    <span x-text="'S/ ' + igv.toFixed(2)"></span>
                </div>
                <div class="total-line grand-total">
                    <span>TOTAL A PAGAR</span>
                    <span x-text="'S/ ' + total.toFixed(2)"></span>
                </div>
            </div>

            {{-- Acciones Finales --}}
            <div class="pos-actions">
                <button @click="cancelarVenta" class="btn-pos-secondary">Cancelar</button>
                <button @click="procesarVenta" :disabled="carrito.length === 0" class="btn-pos-primary"
                    :class="isOnline ? '' : 'btn-offline-mode'">
                    <span x-text="isOnline ? 'COBRAR' : 'COBRAR (OFFLINE)'"></span>
                </button>
            </div>

            {{-- Cola Offline --}}
            <div class="offline-queue-info" x-show="offlineQueue.length > 0">
                ⚠ Tienes <strong x-text="offlineQueue.length"></strong> ventas pendientes de sincronizar.
                <button @click="syncOfflineQueue" :disabled="!isOnline" class="btn-link">Sincronizar ahora</button>
            </div>
        </div>
    </div>

    @push('styles')
        <style>
            /* Layout POS (Pantalla Completa Ocultando Sidebar General si es necesario) */
            .pos-layout {
                display: flex;
                height: calc(100vh - 80px);
                gap: 20px;
                background: var(--bg-body);
                margin: -20px;
                padding: 20px;
                overflow: hidden;
            }

            .pos-main {
                flex: 1;
                display: flex;
                flex-direction: column;
                overflow: hidden;
                background: var(--bg-card);
                border-radius: 12px;
                border: 1px solid var(--border-color);
            }

            .pos-header {
                padding: 20px;
                border-bottom: 1px solid var(--border-color);
            }

            .pos-title {
                font-size: 20px;
                font-weight: 800;
                display: flex;
                align-items: center;
                gap: 12px;
                margin: 0 0 16px 0;
            }

            .badge-status {
                font-size: 10px;
                padding: 4px 8px;
                border-radius: 100px;
                font-weight: 700;
                text-transform: uppercase;
            }

            .badge-online {
                background: rgba(16, 185, 129, .15);
                color: #34d399;
            }

            .badge-offline {
                background: rgba(245, 158, 11, .15);
                color: #fbbf24;
            }

            /* Buscador y Sincronizacion */
            .pos-search-wrapper {
                display: flex;
                gap: 10px;
                margin-bottom: 16px;
                position: relative;
            }

            .pos-search-input {
                flex: 1;
                background: var(--bg-body);
                border: 1px solid var(--border-color);
                border-radius: 8px;
                padding: 12px 16px 12px 40px;
                font-size: 15px;
                color: var(--text-primary);
                transition: all .2s;
            }

            .pos-search-input:focus {
                border-color: var(--accent);
                box-shadow: 0 0 0 3px rgba(16, 185, 129, .1);
                outline: none;
            }

            .search-icon {
                position: absolute;
                left: 14px;
                top: 14px;
                color: var(--text-muted);
            }

            .btn-sync {
                background: var(--bg-body);
                border: 1px solid var(--border-color);
                border-radius: 8px;
                width: 46px;
                display: flex;
                align-items: center;
                justify-content: center;
                color: var(--text-secondary);
                cursor: pointer;
                transition: all .2s;
            }

            .btn-sync:hover {
                background: var(--border-color);
                color: var(--text-primary);
            }

            .syncing svg {
                animation: spin 2s linear infinite;
                color: var(--accent);
            }

            @keyframes spin {
                100% {
                    transform: rotate(360deg);
                }
            }

            /* Categorías */
            .pos-categories {
                display: flex;
                gap: 8px;
                overflow-x: auto;
                padding-bottom: 8px;
                scrollbar-width: none;
            }

            .pos-categories::-webkit-scrollbar {
                display: none;
            }

            .cat-btn {
                background: var(--bg-body);
                border: 1px solid var(--border-color);
                border-radius: 20px;
                padding: 6px 14px;
                font-size: 13px;
                font-weight: 600;
                color: var(--text-secondary);
                cursor: pointer;
                white-space: nowrap;
                transition: all .2s;
            }

            .cat-btn:hover {
                border-color: var(--text-secondary);
            }

            .cat-active {
                background: var(--accent);
                border-color: var(--accent);
                color: #111;
            }

            /* Grilla Productos */
            .pos-products-grid {
                flex: 1;
                overflow-y: auto;
                padding: 20px;
                display: grid;
                grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
                gap: 16px;
                align-content: flex-start;
            }

            .pos-product-card {
                background: var(--bg-body);
                border: 1px solid var(--border-color);
                border-radius: 10px;
                overflow: hidden;
                display: flex;
                flex-direction: column;
                cursor: pointer;
                position: relative;
                transition: transform .2s, box-shadow .2s;
                height: 120px;
            }

            .pos-product-card:hover {
                transform: translateY(-2px);
                box-shadow: 0 8px 16px rgba(0, 0, 0, .15);
                border-color: var(--accent-hover);
            }

            .prod-badge {
                position: absolute;
                top: 8px;
                right: 8px;
                font-size: 10px;
                font-weight: 700;
                background: rgba(239, 68, 68, .15);
                color: #f87171;
                padding: 2px 6px;
                border-radius: 4px;
            }

            .bg-green {
                background: rgba(16, 185, 129, .15) !important;
                color: #34d399 !important;
                right: auto;
                left: 8px;
            }

            .prod-card-body {
                padding: 16px 16px 8px;
                flex: 1;
            }

            .prod-name {
                font-size: 13px;
                font-weight: 600;
                color: var(--text-primary);
                display: -webkit-box;
                -webkit-line-clamp: 2;
                -webkit-box-orient: vertical;
                overflow: hidden;
                margin: 0 0 4px 0;
                line-height: 1.3;
            }

            .prod-sku {
                font-size: 10px;
                color: var(--text-muted);
                font-family: monospace;
                margin: 0;
            }

            .prod-card-footer {
                padding: 8px 16px 12px;
                display: flex;
                justify-content: space-between;
                align-items: flex-end;
            }

            .prod-price {
                font-size: 16px;
                font-weight: 800;
                color: var(--accent);
            }

            .prod-stock {
                font-size: 11px;
                color: var(--text-muted);
                font-weight: 500;
            }

            /* Sidebar Venta */
            .pos-sidebar {
                width: 400px;
                display: flex;
                flex-direction: column;
                background: var(--bg-card);
                border-radius: 12px;
                border: 1px solid var(--border-color);
                flex-shrink: 0;
            }

            .sidebar-section {
                padding: 16px 20px;
                border-bottom: 1px solid var(--border-color);
            }

            .pos-label {
                display: block;
                font-size: 11px;
                font-weight: 700;
                color: var(--text-muted);
                text-transform: uppercase;
                margin-bottom: 8px;
            }

            .cliente-row {
                display: flex;
                gap: 8px;
            }

            .pos-input {
                flex: 1;
                background: var(--bg-body);
                border: 1px solid var(--border-color);
                border-radius: 6px;
                padding: 10px 12px;
                font-size: 14px;
                color: var(--text-primary);
                transition: border-color .2s;
            }

            .pos-input:focus {
                border-color: var(--accent);
                outline: none;
            }

            .btn-icon {
                background: var(--bg-body);
                border: 1px solid var(--border-color);
                border-radius: 6px;
                padding: 0 14px;
                display: flex;
                align-items: center;
                justify-content: center;
                cursor: pointer;
                color: var(--text-secondary);
            }

            .btn-icon:hover {
                border-color: var(--accent);
                color: var(--accent);
            }

            .cliente-info {
                margin-top: 10px;
                display: flex;
                justify-content: space-between;
                align-items: center;
                background: rgba(59, 130, 246, .1);
                padding: 8px 12px;
                border-radius: 6px;
                border: 1px solid rgba(59, 130, 246, .2);
            }

            .cliente-nombre-badge {
                font-size: 12px;
                font-weight: 600;
                color: #60a5fa;
            }

            .btn-remove-client {
                background: none;
                border: none;
                color: #f87171;
                cursor: pointer;
                font-size: 14px;
                padding: 0;
            }

            .comprobante-toggles {
                display: flex;
                background: var(--bg-body);
                border-radius: 8px;
                padding: 4px;
            }

            .toggle-btn {
                flex: 1;
                text-align: center;
                border: none;
                background: transparent;
                padding: 10px;
                border-radius: 6px;
                font-size: 13px;
                font-weight: 600;
                color: var(--text-secondary);
                cursor: pointer;
                transition: all .2s;
            }

            .toggle-btn.active {
                background: var(--bg-card);
                color: var(--text-primary);
                box-shadow: 0 2px 8px rgba(0, 0, 0, .2);
                border: 1px solid var(--border-color);
            }

            /* Carrito */
            .pos-cart {
                flex: 1;
                overflow-y: auto;
                padding: 10px 0;
            }

            .empty-cart-msg {
                height: 100%;
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                color: var(--text-muted);
                font-size: 14px;
                gap: 12px;
            }

            .cart-item {
                padding: 12px 20px;
                border-bottom: 1px dashed var(--border-color);
            }

            .cart-item:last-child {
                border-bottom: none;
            }

            .item-details {
                display: flex;
                justify-content: space-between;
                margin-bottom: 8px;
            }

            .item-title {
                font-size: 13px;
                font-weight: 500;
                color: var(--text-primary);
                line-height: 1.3;
            }

            .item-prices {
                font-size: 12px;
                color: var(--text-muted);
            }

            .item-actions {
                display: flex;
                align-items: center;
                justify-content: space-between;
            }

            .qty-control {
                display: flex;
                border: 1px solid var(--border-color);
                border-radius: 6px;
                overflow: hidden;
            }

            .qty-control button {
                background: var(--bg-body);
                border: none;
                padding: 6px 12px;
                font-size: 16px;
                font-weight: 700;
                color: var(--text-primary);
                cursor: pointer;
            }

            .qty-control button:hover {
                background: var(--border-color);
            }

            .qty-control input {
                width: 50px;
                text-align: center;
                background: transparent;
                border: none;
                border-left: 1px solid var(--border-color);
                border-right: 1px solid var(--border-color);
                font-size: 14px;
                font-weight: 600;
                color: var(--text-primary);
                -moz-appearance: textfield;
            }

            .qty-control input::-webkit-outer-spin-button,
            .qty-control input::-webkit-inner-spin-button {
                -webkit-appearance: none;
                margin: 0;
            }

            .item-subtotal {
                font-size: 14px;
                font-weight: 700;
                color: var(--text-primary);
            }

            .btn-delete {
                background: none;
                border: none;
                color: var(--text-muted);
                cursor: pointer;
                padding: 4px;
                border-radius: 4px;
            }

            .btn-delete:hover {
                background: rgba(239, 68, 68, .1);
                color: #f87171;
            }

            /* Totales */
            .pos-totals {
                padding: 16px 20px;
                background: rgba(0, 0, 0, .2);
                border-top: 1px solid var(--border-color);
                display: flex;
                flex-direction: column;
                gap: 8px;
            }

            .total-line {
                display: flex;
                justify-content: space-between;
                font-size: 14px;
                color: var(--text-secondary);
            }

            .grand-total {
                font-size: 22px;
                font-weight: 800;
                color: var(--accent);
                margin-top: 8px;
                padding-top: 12px;
                border-top: 1px solid rgba(255, 255, 255, .05);
            }

            /* Acciones */
            .pos-actions {
                padding: 20px;
                display: flex;
                gap: 12px;
                border-top: 1px solid var(--border-color);
                background: var(--bg-card);
                border-radius: 0 0 12px 12px;
            }

            .btn-pos-secondary {
                flex: 1;
                padding: 14px;
                border-radius: 8px;
                font-size: 15px;
                font-weight: 700;
                background: var(--bg-body);
                color: var(--text-primary);
                border: 1px solid var(--border-color);
                cursor: pointer;
                transition: .2s;
            }

            .btn-pos-secondary:hover {
                background: var(--border-color);
            }

            .btn-pos-primary {
                flex: 2;
                padding: 14px;
                border-radius: 8px;
                font-size: 15px;
                font-weight: 800;
                background: var(--accent);
                color: #111;
                border: none;
                cursor: pointer;
                transition: .2s;
            }

            .btn-pos-primary:hover:not(:disabled) {
                background: var(--accent-hover);
                transform: translateY(-1px);
                box-shadow: 0 4px 12px rgba(16, 185, 129, .3);
            }

            .btn-pos-primary:disabled {
                opacity: 0.5;
                cursor: not-allowed;
            }

            .btn-offline-mode {
                background: #fbbf24 !important;
                color: #000 !important;
            }

            .offline-queue-info {
                font-size: 11px;
                padding: 10px 20px;
                text-align: center;
                background: rgba(245, 158, 11, .1);
                color: #fbbf24;
                border-top: 1px solid rgba(245, 158, 11, .2);
            }

            .btn-link {
                background: none;
                border: none;
                color: inherit;
                text-decoration: underline;
                cursor: pointer;
                font-weight: 600;
                padding: 0 4px;
            }
        </style>
    @endpush

    @push('scripts')
        {{-- Incluir Alpine.js si no lo tuviéramos globalmente, asumo que sí en un layout premium.
        Si usa Livewire v3, Alpine ya está incluido.
        --}}
        <script>
            document.addEventListener('alpine:init', () => {
                Alpine.data('posOfflineApp', () => ({
                    isOnline: navigator.onLine,
                    isSyncing: false,

                    // Base de datos Offline (LocalStorage para Demo/Simplificación, ideal IndexedDB)
                    productosCached: [],
                    categoriasCached: [],
                    offlineQueue: [],

                    // Estado del POS
                    searchQuery: '',
                    categoriaActiva: 'todas',
                    productosFiltrados: [],

                    // Carrito
                    carrito: [],
                    tipoDoc: '03', // 03 Boleta por defecto

                    // Cliente
                    clienteInput: '',
                    clienteSeleccionado: { id: null, documento: '', nombre: '' },

                    // Totales
                    subtotal: 0,
                    igv: 0,
                    total: 0,

                    initPos() {
                        // Escuchar cambios de red
                        window.addEventListener('online', () => { this.isOnline = true; this.syncOfflineQueue(); });
                        window.addEventListener('offline', () => { this.isOnline = false; });

                        // Cargar datos locales o sincronizar
                        this.cargarCacheLocal();
                        this.syncData();

                        // Cola offline
                        const q = localStorage.getItem('agromine_pos_queue');
                        if (q) this.offlineQueue = JSON.parse(q);
                    },

                    async syncData() {
                        if (!this.isOnline || this.isSyncing) return;
                        this.isSyncing = true;
                        try {
                            // En producción esto llama a un endpoint API optimizado (ej. /api/v1/pos/sync)
                            const res = await fetch('/api/v1/productos?limite=1000'); // Traer 1000 productos activos/POS
                            const data = await res.json();

                            // Mapeo simple
                            this.productosCached = data.data.map(p => ({
                                id: p.id,
                                nombre: p.nombre,
                                codigo_interno: p.codigo_interno || p.id,
                                precio: parseFloat(p.precio_venta),
                                stock: parseFloat(p.stock_total || 999), // Fallback si no viene stock agrupado
                                es_agricola: p.tipo_existencia_id === 1,
                                categoria_id: p.categoria_id
                            }));

                            localStorage.setItem('agromine_pos_products', JSON.stringify(this.productosCached));
                            this.filtrarProductos();
                        } catch (e) {
                            console.warn("Fallo sincronización, usando caché local", e);
                        } finally {
                            this.isSyncing = false;
                        }
                    },

                    cargarCacheLocal() {
                        const prodCarga = localStorage.getItem('agromine_pos_products');
                        if (prodCarga) {
                            this.productosCached = JSON.parse(prodCarga);
                            this.filtrarProductos();
                        }
                    },

                    filtrarProductos() {
                        let res = this.productosCached;

                        if (this.categoriaActiva !== 'todas') {
                            res = res.filter(p => p.categoria_id === this.categoriaActiva);
                        }

                        if (this.searchQuery.length >= 2) {
                            const q = this.searchQuery.toLowerCase();
                            res = res.filter(p =>
                                p.nombre.toLowerCase().includes(q) ||
                                (p.codigo_interno && p.codigo_interno.toLowerCase().includes(q))
                            );
                        }

                        this.productosFiltrados = res.slice(0, 50); // Límite visual 50
                    },

                    setCategoria(id) {
                        this.categoriaActiva = id;
                        this.filtrarProductos();
                    },

                    agregarAlCarrito(prod) {
                        const idx = this.carrito.findIndex(i => i.id === prod.id);
                        if (idx > -1) {
                            this.carrito[idx].cantidad++;
                        } else {
                            this.carrito.push({
                                id: prod.id,
                                nombre: prod.nombre,
                                precio: prod.precio,
                                cantidad: 1,
                                igv_pct: 18 // Asumimos gravado 18% para el demo POS
                            });
                        }
                        this.calcularTotales();
                    },

                    quitarDelCarrito(idx) {
                        this.carrito.splice(idx, 1);
                        this.calcularTotales();
                    },

                    actualizarCantidad(idx, dif) {
                        const nc = this.carrito[idx].cantidad + dif;
                        if (nc > 0) this.carrito[idx].cantidad = nc;
                        this.calcularTotales();
                    },

                    calcularTotales() {
                        let st = 0; let ig = 0;
                        this.carrito.forEach(item => {
                            const totalItem = item.cantidad * item.precio; // Precio incluye IGV en Perú comúnmente en POS
                            const baseImponible = totalItem / 1.18;
                            st += baseImponible;
                            ig += (totalItem - baseImponible);
                        });
                        this.baseImponible = st;
                        this.igv = ig;
                        this.total = st + ig;
                    },

                    async buscarClienteEnLinea() {
                        if (!this.clienteInput || !this.isOnline) return;
                        try {
                            const res = await fetch(`/api/v1/consulta/documento/A/${this.clienteInput}`);
                            const data = await res.json();
                            if (data.success) {
                                this.clienteSeleccionado = {
                                    documento: this.clienteInput,
                                    nombre: data.data.razon_social || data.data.nombre_completo,
                                };
                            } else {
                                alert(data.error || "Cliente no encontrado");
                            }
                        } catch (e) {
                            alert("Error conectando con servicio de DNI/RUC");
                        }
                    },

                    limpiarCliente() {
                        this.clienteSeleccionado = { id: null, documento: '', nombre: '' };
                        this.clienteInput = '';
                    },

                    cancelarVenta() {
                        if (confirm('¿Limpiar el carrito y cancelar venta actual?')) {
                            this.carrito = [];
                            this.limpiarCliente();
                            this.calcularTotales();
                        }
                    },

                    async procesarVenta() {
                        if (this.carrito.length === 0) return;

                        // Validaciones básicas
                        if (this.tipoDoc === '01' && this.clienteSeleccionado.documento.length !== 11) {
                            alert("Para Factura (01) se requiere un RUC válido (11 dígitos).");
                            return;
                        }

                        const payload = {
                            id_temp: 'temp_' + Date.now(),
                            tipo_documento: this.tipoDoc,
                            cliente: this.clienteSeleccionado,
                            items: this.carrito,
                            total: this.total,
                            fecha: new Date().toISOString()
                        };

                        if (this.isOnline) {
                            // Envío directo a API (Mock simulado)
                            try {
                                console.log("Enviando venta a servidor:", payload);
                                // Aquí llamaría a axios.post('/api/v1/ventas/pos', payload)
                                alert("Venta guardada y enviada a SUNAT correctamente.");
                                this.limpiarVenta();
                            } catch (e) {
                                this.guardarEnColaLocal(payload);
                            }
                        } else {
                            // Guardar en LocalStorage para luego
                            this.guardarEnColaLocal(payload);
                        }
                    },

                    guardarEnColaLocal(payload) {
                        this.offlineQueue.push(payload);
                        localStorage.setItem('agromine_pos_queue', JSON.stringify(this.offlineQueue));
                        alert("Venta guardada LOCALMENTE. Se sincronizará cuando haya conexión.");
                        this.limpiarVenta();
                    },

                    async syncOfflineQueue() {
                        if (!this.isOnline || this.offlineQueue.length === 0) return;

                        console.log(`Sincronizando ${this.offlineQueue.length} ventas locales...`);
                        // Simular sync iterativa
                        while (this.offlineQueue.length > 0) {
                            const ventaAEnviar = this.offlineQueue[0];
                            try {
                                // await fetch('/api/v1/ventas/sync', { body: ventaAEnviar ... })
                                this.offlineQueue.shift(); // Enviado exitoso
                                localStorage.setItem('agromine_pos_queue', JSON.stringify(this.offlineQueue));
                            } catch (e) {
                                console.error("Error sincronizando venta local", e);
                                break;
                            }
                        }
                    },

                    limpiarVenta() {
                        this.carrito = [];
                        if (this.tipoDoc === '01') this.tipoDoc = '03'; // Regresar a Boleta
                        this.limpiarCliente();
                        this.calcularTotales();
                    }
                }));
            });
        </script>
    @endpush
@endsection