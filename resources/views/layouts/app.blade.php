<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') — ERP AgroMine</title>

    {{-- Google Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">

    {{-- Lucide Icons CDN --}}
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.min.js"></script>

    <link rel="stylesheet" href="{{ asset('css/erp.css') }}">

    @livewireStyles
    @stack('styles')
</head>

<body>
    <div class="app-container" id="app">
        {{-- SIDEBAR --}}
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <div class="logo-container">
                    <div class="logo-icon">
                        <i data-lucide="mountain-snow"></i>
                    </div>
                    <div class="logo-text">
                        <span class="logo-name">AgroMine</span>
                        <span class="logo-subtitle">ERP & POS</span>
                    </div>
                </div>
                <button class="sidebar-toggle" onclick="toggleSidebar()">
                    <i data-lucide="panel-left-close" id="toggle-icon"></i>
                </button>
            </div>

            <nav class="sidebar-nav">
                {{-- DASHBOARD --}}
                <a href="{{ route('dashboard') }}"
                    class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <i data-lucide="layout-dashboard"></i>
                    <span>Dashboard</span>
                </a>

                {{-- VENTAS --}}
                <div class="nav-group">
                    <button class="nav-group-toggle" onclick="toggleMenu('ventas')">
                        <div class="nav-item-left">
                            <i data-lucide="shopping-cart"></i>
                            <span>Ventas</span>
                        </div>
                        <i data-lucide="chevron-down" class="chevron" id="chevron-ventas"></i>
                    </button>
                    <div class="nav-submenu" id="menu-ventas">
                        <a href="{{ route('ventas.atencion') }}"
                            class="nav-subitem {{ request()->routeIs('ventas.atencion') ? 'active' : '' }}">Atención al
                            cliente</a>
                        <a href="{{ route('ventas.listado') }}"
                            class="nav-subitem {{ request()->routeIs('ventas.listado') ? 'active' : '' }}">Listado de
                            atención</a>
                        <a href="{{ route('ventas.notas') }}"
                            class="nav-subitem {{ request()->routeIs('ventas.notas') ? 'active' : '' }}">Notas de
                            Venta</a>
                        <a href="{{ route('ventas.comprobantes-no-enviados') }}"
                            class="nav-subitem {{ request()->routeIs('ventas.comprobantes-no-enviados') ? 'active' : '' }}">Comprobantes
                            no enviados</a>
                        <a href="{{ route('ventas.cpe-pendientes') }}"
                            class="nav-subitem {{ request()->routeIs('ventas.cpe-pendientes') ? 'active' : '' }}">CPE
                            pendientes rectificación</a>
                        <a href="{{ route('ventas.resumenes') }}"
                            class="nav-subitem {{ request()->routeIs('ventas.resumenes') ? 'active' : '' }}">Resúmenes y
                            Anulaciones</a>
                        <a href="{{ route('ventas.cotizaciones') }}"
                            class="nav-subitem {{ request()->routeIs('ventas.cotizaciones') ? 'active' : '' }}">Cotizaciones</a>
                        <a href="{{ route('ventas.despachos') }}"
                            class="nav-subitem {{ request()->routeIs('ventas.despachos') ? 'active' : '' }}">Despachos</a>
                    </div>
                </div>

                {{-- POS --}}
                <div class="nav-group">
                    <button class="nav-group-toggle" onclick="toggleMenu('pos')">
                        <div class="nav-item-left">
                            <i data-lucide="monitor"></i>
                            <span>POS</span>
                        </div>
                        <i data-lucide="chevron-down" class="chevron" id="chevron-pos"></i>
                    </button>
                    <div class="nav-submenu" id="menu-pos">
                        <a href="{{ route('pos.punto-venta') }}"
                            class="nav-subitem {{ request()->routeIs('pos.punto-venta') ? 'active' : '' }}">Punto de
                            venta</a>
                    </div>
                </div>

                {{-- PRODUCTOS --}}
                <div class="nav-group">
                    <button class="nav-group-toggle" onclick="toggleMenu('productos')">
                        <div class="nav-item-left">
                            <i data-lucide="package"></i>
                            <span>Productos/Servicios</span>
                        </div>
                        <i data-lucide="chevron-down" class="chevron" id="chevron-productos"></i>
                    </button>
                    <div class="nav-submenu" id="menu-productos">
                        <a href="{{ route('productos.index') }}"
                            class="nav-subitem {{ request()->routeIs('productos.index') ? 'active' : '' }}">Productos/Servicios</a>
                        <a href="{{ route('productos.packs') }}"
                            class="nav-subitem {{ request()->routeIs('productos.packs') ? 'active' : '' }}">Conjuntos/Packs</a>
                        <a href="{{ route('productos.buscador') }}"
                            class="nav-subitem {{ request()->routeIs('productos.buscador') ? 'active' : '' }}">Buscador</a>
                        <a href="{{ route('productos.categorias') }}"
                            class="nav-subitem {{ request()->routeIs('productos.categorias') ? 'active' : '' }}">Categorías</a>
                        <a href="{{ route('productos.marcas') }}"
                            class="nav-subitem {{ request()->routeIs('productos.marcas') ? 'active' : '' }}">Marcas</a>
                        <a href="{{ route('productos.series') }}"
                            class="nav-subitem {{ request()->routeIs('productos.series') ? 'active' : '' }}">Series</a>
                        <a href="{{ route('productos.ubicaciones') }}"
                            class="nav-subitem {{ request()->routeIs('productos.ubicaciones') ? 'active' : '' }}">Ubicaciones</a>
                        <a href="{{ route('productos.laboratorios') }}"
                            class="nav-subitem {{ request()->routeIs('productos.laboratorios') ? 'active' : '' }}">Laboratorios</a>
                        <a href="{{ route('productos.codigos-barra') }}"
                            class="nav-subitem {{ request()->routeIs('productos.codigos-barra') ? 'active' : '' }}">Códigos
                            de barra</a>
                        <a href="{{ route('productos.tipos-existencia') }}"
                            class="nav-subitem {{ request()->routeIs('productos.tipos-existencia') ? 'active' : '' }}">Tipos
                            de existencia</a>
                    </div>
                </div>

                {{-- CLIENTES --}}
                <div class="nav-group">
                    <button class="nav-group-toggle" onclick="toggleMenu('clientes')">
                        <div class="nav-item-left">
                            <i data-lucide="users"></i>
                            <span>Clientes</span>
                        </div>
                        <i data-lucide="chevron-down" class="chevron" id="chevron-clientes"></i>
                    </button>
                    <div class="nav-submenu" id="menu-clientes">
                        <a href="{{ route('clientes.index') }}"
                            class="nav-subitem {{ request()->routeIs('clientes.index') ? 'active' : '' }}">Clientes</a>
                        <a href="{{ route('clientes.tipos') }}"
                            class="nav-subitem {{ request()->routeIs('clientes.tipos') ? 'active' : '' }}">Tipos de
                            clientes</a>
                        <a href="{{ route('clientes.zonas') }}"
                            class="nav-subitem {{ request()->routeIs('clientes.zonas') ? 'active' : '' }}">Zonas</a>
                    </div>
                </div>

                {{-- PROVEEDORES --}}
                <div class="nav-group">
                    <button class="nav-group-toggle" onclick="toggleMenu('proveedores')">
                        <div class="nav-item-left">
                            <i data-lucide="truck"></i>
                            <span>Proveedores</span>
                        </div>
                        <i data-lucide="chevron-down" class="chevron" id="chevron-proveedores"></i>
                    </button>
                    <div class="nav-submenu" id="menu-proveedores">
                        <a href="{{ route('proveedores.index') }}"
                            class="nav-subitem {{ request()->routeIs('proveedores.index') ? 'active' : '' }}">Listado</a>
                        <a href="{{ route('proveedores.cotizacion') }}"
                            class="nav-subitem {{ request()->routeIs('proveedores.cotizacion') ? 'active' : '' }}">Solicitar
                            cotización</a>
                    </div>
                </div>

                {{-- COMPRAS --}}
                <div class="nav-group">
                    <button class="nav-group-toggle" onclick="toggleMenu('compras')">
                        <div class="nav-item-left">
                            <i data-lucide="receipt"></i>
                            <span>Compras</span>
                        </div>
                        <i data-lucide="chevron-down" class="chevron" id="chevron-compras"></i>
                    </button>
                    <div class="nav-submenu" id="menu-compras">
                        <a href="{{ route('compras.nuevo') }}"
                            class="nav-subitem {{ request()->routeIs('compras.nuevo') ? 'active' : '' }}">Nueva
                            Compra</a>
                        <a href="{{ route('compras.listado') }}"
                            class="nav-subitem {{ request()->routeIs('compras.listado') ? 'active' : '' }}">Listado de
                            Compras</a>
                        <a href="{{ route('compras.ordenes') }}"
                            class="nav-subitem {{ request()->routeIs('compras.ordenes') ? 'active' : '' }}">Órdenes de
                            Compra</a>
                    </div>
                </div>

                {{-- INVENTARIO --}}
                <div class="nav-group">
                    <button class="nav-group-toggle" onclick="toggleMenu('inventario')">
                        <div class="nav-item-left">
                            <i data-lucide="warehouse"></i>
                            <span>Inventario</span>
                        </div>
                        <i data-lucide="chevron-down" class="chevron" id="chevron-inventario"></i>
                    </button>
                    <div class="nav-submenu" id="menu-inventario">
                        <a href="{{ route('inventario.movimientos') }}"
                            class="nav-subitem {{ request()->routeIs('inventario.movimientos') ? 'active' : '' }}">Movimientos</a>
                        <a href="{{ route('inventario.traslados') }}"
                            class="nav-subitem {{ request()->routeIs('inventario.traslados') ? 'active' : '' }}">Traslados</a>
                        <a href="{{ route('inventario.devolucion') }}"
                            class="nav-subitem {{ request()->routeIs('inventario.devolucion') ? 'active' : '' }}">Devolución
                            a proveedor</a>
                        <a href="{{ route('inventario.kardex') }}"
                            class="nav-subitem {{ request()->routeIs('inventario.kardex') ? 'active' : '' }}">Reporte
                            Kardex</a>
                        <a href="{{ route('inventario.reporte') }}"
                            class="nav-subitem {{ request()->routeIs('inventario.reporte') ? 'active' : '' }}">Reporte
                            Inventario</a>
                        <a href="{{ route('inventario.kardex-valorizado') }}"
                            class="nav-subitem {{ request()->routeIs('inventario.kardex-valorizado') ? 'active' : '' }}">Kardex
                            valorizado</a>
                        <a href="{{ route('inventario.formato-13') }}"
                            class="nav-subitem {{ request()->routeIs('inventario.formato-13') ? 'active' : '' }}">Formato
                            13.1 SUNAT</a>
                        <a href="{{ route('inventario.prediccion') }}"
                            class="nav-subitem {{ request()->routeIs('inventario.prediccion') ? 'active' : '' }}">
                            <i data-lucide="brain-circuit"
                                style="width:14px;height:14px;color:#818cf8;margin-right:2px"></i>
                            Predicción IA
                        </a>
                    </div>
                </div>

                {{-- COMPROBANTES AVANZADOS --}}
                <div class="nav-group">
                    <button class="nav-group-toggle" onclick="toggleMenu('comprobantes')">
                        <div class="nav-item-left">
                            <i data-lucide="file-check-2"></i>
                            <span>Comprobantes Avanzados</span>
                        </div>
                        <i data-lucide="chevron-down" class="chevron" id="chevron-comprobantes"></i>
                    </button>
                    <div class="nav-submenu" id="menu-comprobantes">
                        <a href="{{ route('comprobantes.retenciones') }}"
                            class="nav-subitem {{ request()->routeIs('comprobantes.retenciones') ? 'active' : '' }}">Retenciones</a>
                        <a href="{{ route('comprobantes.percepciones') }}"
                            class="nav-subitem {{ request()->routeIs('comprobantes.percepciones') ? 'active' : '' }}">Percepciones</a>
                        <a href="{{ route('comprobantes.liquidaciones') }}"
                            class="nav-subitem {{ request()->routeIs('comprobantes.liquidaciones') ? 'active' : '' }}">Liquidaciones
                            de compra</a>
                        <a href="{{ route('comprobantes.ordenes-pedido') }}"
                            class="nav-subitem {{ request()->routeIs('comprobantes.ordenes-pedido') ? 'active' : '' }}">Órdenes
                            de pedido</a>
                        <a href="{{ route('comprobantes.guias-remision') }}"
                            class="nav-subitem {{ request()->routeIs('comprobantes.guias-remision') ? 'active' : '' }}">Guías
                            de remisión</a>
                    </div>
                </div>

                {{-- FINANZAS --}}
                <div class="nav-group">
                    <button class="nav-group-toggle" onclick="toggleMenu('finanzas')">
                        <div class="nav-item-left">
                            <i data-lucide="landmark"></i>
                            <span>Finanzas</span>
                        </div>
                        <i data-lucide="chevron-down" class="chevron" id="chevron-finanzas"></i>
                    </button>
                    <div class="nav-submenu" id="menu-finanzas">
                        {{-- Tesorería — items propios sin duplicar lo de Compras --}}
                        <div
                            style="padding:6px 12px 2px;font-size:0.65rem;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.08em">
                            Tesorería</div>
                        <a href="{{ route('finanzas.caja-chica') }}"
                            class="nav-subitem {{ request()->routeIs('finanzas.caja-chica') ? 'active' : '' }}"><i
                                data-lucide="vault" style="width:13px;height:13px"></i> Caja Chica</a>
                        <a href="{{ route('finanzas.creditos-bancarios') }}"
                            class="nav-subitem {{ request()->routeIs('finanzas.creditos-bancarios') ? 'active' : '' }}"><i
                                data-lucide="building-2" style="width:13px;height:13px"></i> Créditos Bancarios</a>
                        <a href="{{ route('finanzas.gastos') }}"
                            class="nav-subitem {{ request()->routeIs('finanzas.gastos') ? 'active' : '' }}"><i
                                data-lucide="receipt" style="width:13px;height:13px"></i> Gastos Diversos</a>
                        <a href="{{ route('finanzas.activos-fijos') }}"
                            class="nav-subitem {{ request()->routeIs('finanzas.activos-fijos') ? 'active' : '' }}"><i
                                data-lucide="hard-drive" style="width:13px;height:13px"></i> Activos Fijos</a>
                        <a href="{{ route('finanzas.cuentas-cobrar') }}"
                            class="nav-subitem {{ request()->routeIs('finanzas.cuentas-cobrar') ? 'active' : '' }}"><i
                                data-lucide="arrow-down-circle" style="width:13px;height:13px"></i> Cuentas por
                            Cobrar</a>
                        <a href="{{ route('finanzas.cuentas-pagar') }}"
                            class="nav-subitem {{ request()->routeIs('finanzas.cuentas-pagar') ? 'active' : '' }}"><i
                                data-lucide="arrow-up-circle" style="width:13px;height:13px"></i> Cuentas por Pagar</a>
                        {{-- Contabilidad --}}
                        <div
                            style="padding:8px 12px 2px;font-size:0.65rem;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.08em;border-top:1px solid var(--border-color);margin-top:4px">
                            Contabilidad</div>
                        <a href="{{ route('finanzas.contabilidad.plan-cuentas') }}"
                            class="nav-subitem {{ request()->routeIs('finanzas.contabilidad.plan-cuentas') ? 'active' : '' }}">
                            <i data-lucide="list-tree" style="width:13px;height:13px"></i> Plan de Cuentas</a>
                        <a href="{{ route('finanzas.contabilidad.asientos') }}"
                            class="nav-subitem {{ request()->routeIs('finanzas.contabilidad.asientos') ? 'active' : '' }}">
                            <i data-lucide="book-open" style="width:13px;height:13px"></i> Asientos Contables</a>
                        <a href="{{ route('finanzas.contabilidad.libro-diario') }}"
                            class="nav-subitem {{ request()->routeIs('finanzas.contabilidad.libro-diario') ? 'active' : '' }}">
                            <i data-lucide="scroll-text" style="width:13px;height:13px"></i> Libro Diario</a>
                        <a href="{{ route('finanzas.contabilidad.balance-general') }}"
                            class="nav-subitem {{ request()->routeIs('finanzas.contabilidad.balance-general') ? 'active' : '' }}">
                            <i data-lucide="scale" style="width:13px;height:13px"></i> Balance General</a>
                        <a href="{{ route('finanzas.contabilidad.estado-resultados') }}"
                            class="nav-subitem {{ request()->routeIs('finanzas.contabilidad.estado-resultados') ? 'active' : '' }}">
                            <i data-lucide="bar-chart-3" style="width:13px;height:13px"></i> Estado de Resultados</a>
                        <a href="{{ route('finanzas.contabilidad.presupuesto') }}"
                            class="nav-subitem {{ request()->routeIs('finanzas.contabilidad.presupuesto') ? 'active' : '' }}">
                            <i data-lucide="target" style="width:13px;height:13px"></i> Presupuesto</a>
                    </div>
                </div>

                {{-- CONFIGURACIÓN --}}
                <a href="{{ route('configuracion.empresa') }}"
                    class="nav-item {{ request()->routeIs('configuracion.*') ? 'active' : '' }}">
                    <i data-lucide="settings"></i>
                    <span>Configuración</span>
                </a>
            </nav>

            {{-- User section --}}
            <div class="sidebar-footer">
                <div class="user-info">
                    <div class="user-avatar">
                        <i data-lucide="user-circle"></i>
                    </div>
                    <div class="user-details">
                        <span class="user-name">{{ Str::limit(auth()->user()->name ?? 'Usuario', 18) }}</span>
                        <span class="user-role">{{ ucfirst(auth()->user()->rol ?? 'admin') }}</span>
                    </div>
                    <form method="POST" action="{{ route('logout') }}" style="margin:0">
                        @csrf
                        <button type="submit" class="btn-logout" title="Cerrar sesión">
                            <i data-lucide="log-out"></i>
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        {{-- MAIN CONTENT --}}
        <main class="main-content" id="main-content">
            {{-- Top bar --}}
            <header class="top-bar">
                <div class="top-bar-left">
                    <button class="mobile-toggle" onclick="toggleSidebar()">
                        <i data-lucide="menu"></i>
                    </button>
                    <div class="breadcrumb">
                        @yield('breadcrumb')
                    </div>
                </div>
                <div class="top-bar-right">
                    <div class="search-global">
                        <i data-lucide="search"></i>
                        <input type="text" placeholder="Buscar productos, clientes, comprobantes..." id="global-search">
                    </div>
                    <button class="top-btn" title="Notificaciones">
                        <i data-lucide="bell"></i>
                        <span class="notification-badge">3</span>
                    </button>
                    <a href="{{ route('configuracion.empresa') }}" class="top-btn" title="Configuración" style="text-decoration:none">
                        <i data-lucide="settings"></i>
                    </a>
                </div>
            </header>

            {{-- Page content --}}
            <div class="page-content">
                {{-- Flash messages --}}
                @if(session('success'))
                    <div class="alert alert-success">
                        <i data-lucide="check-circle-2"></i>
                        <span>{{ session('success') }}</span>
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-error">
                        <i data-lucide="alert-circle"></i>
                        <span>{{ session('error') }}</span>
                    </div>
                @endif

                {{ $slot ?? '' }}
                @yield('content')
            </div>
        </main>
    </div>

    @livewireScripts

    <script>
        // ─── Lucide Icons: re-inicialización segura post-Livewire ─────────────────
        // PROBLEMA: Livewire reemplaza nodos del DOM → los <i data-lucide="...">
        // recién insertados no tienen SVG → iconos desaparecen.
        // SOLUCIÓN: MutationObserver que se DESCONECTA antes de llamar createIcons()
        // y se RECONECTA después, con debounce para agrupar múltiples mutaciones.
        // Esto evita el bucle infinito (createIcons modifica DOM → Observer → loop).

        let _lucideTimer = null;
        let _lucideObserver = null;

        function initLucide() {
            if (typeof lucide === 'undefined') return;
            // Desconectar para que createIcons() no dispare el observer
            if (_lucideObserver) _lucideObserver.disconnect();
            lucide.createIcons();
            // Reconectar después de que el stack de llamadas se limpie
            if (_lucideObserver) {
                _lucideObserver.observe(document.body, { childList: true, subtree: true });
            }
        }

        function scheduleLucide() {
            clearTimeout(_lucideTimer);
            _lucideTimer = setTimeout(initLucide, 50);
        }

        // Carga inicial
        initLucide();

        // Hooks Livewire v3
        document.addEventListener('livewire:navigated', initLucide);
        document.addEventListener('livewire:update', initLucide);

        // Hook Livewire v3: después de cada commit (el más confiable)
        document.addEventListener('livewire:init', () => {
            Livewire.hook('commit', ({ succeed }) => {
                succeed(() => scheduleLucide());
            });
        });

        // Observer universal como red de seguridad (con debounce + desconexión)
        _lucideObserver = new MutationObserver(scheduleLucide);
        _lucideObserver.observe(document.body, { childList: true, subtree: true });

        // ─── Sidebar toggle ────────────────────────────────────────────────────────
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const main = document.getElementById('main-content');
            sidebar.classList.toggle('collapsed');
            main.classList.toggle('expanded');
            localStorage.setItem('sidebar-collapsed', sidebar.classList.contains('collapsed'));
        }

        // ─── Menu accordion ────────────────────────────────────────────────────────
        function toggleMenu(name) {
            const menu = document.getElementById('menu-' + name);
            const chevron = document.getElementById('chevron-' + name);
            const allMenus = document.querySelectorAll('.nav-submenu');

            allMenus.forEach(m => {
                if (m.id !== 'menu-' + name && m.classList.contains('open')) {
                    m.classList.remove('open');
                    document.getElementById(m.id.replace('menu-', 'chevron-'))?.classList.remove('rotated');
                }
            });

            menu.classList.toggle('open');
            chevron?.classList.toggle('rotated');
        }

        // ─── Inicialización al cargar ──────────────────────────────────────────────
        document.addEventListener('DOMContentLoaded', () => {
            const activeSubitem = document.querySelector('.nav-subitem.active');
            if (activeSubitem) {
                const submenu = activeSubitem.closest('.nav-submenu');
                if (submenu) {
                    submenu.classList.add('open');
                    document.getElementById(submenu.id.replace('menu-', 'chevron-'))?.classList.add('rotated');
                }
            }
            if (localStorage.getItem('sidebar-collapsed') === 'true') {
                document.getElementById('sidebar').classList.add('collapsed');
                document.getElementById('main-content').classList.add('expanded');
            }
        });
    </script>

    @stack('scripts')
</body>

</html>