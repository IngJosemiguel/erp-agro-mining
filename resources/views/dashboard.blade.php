@extends('layouts.app')

@section('title', 'Dashboard')

@section('breadcrumb')
    <span>Dashboard Gerencial</span>
@endsection

@section('content')
    <div class="page-header">
        <div>
            <h1 class="page-title">Dashboard Gerencial</h1>
            <p class="page-subtitle">Vista ejecutiva — {{ now()->translatedFormat('l, d M Y') }}</p>
        </div>
        <div style="display:flex;gap:10px">
            <a href="{{ route('ventas.atencion') }}" class="btn btn-primary">
                <i data-lucide="plus"></i> Nueva Venta
            </a>
            <a href="{{ route('pos.punto-venta') }}" class="btn btn-outline">
                <i data-lucide="monitor"></i> Abrir POS
            </a>
        </div>
    </div>

    {{-- ═══════════════════════ KPIs FILA 1: OPERATIVOS ═══════════════════════ --}}
    <div class="stats-grid" style="grid-template-columns:repeat(auto-fit, minmax(200px, 1fr))">
        <div class="stat-card">
            <div class="stat-icon green"><i data-lucide="trending-up"></i></div>
            <div class="stat-info">
                <div class="stat-value">S/ {{ number_format($ventasHoy, 2) }}</div>
                <div class="stat-label">Ventas Hoy</div>
                <div class="stat-change {{ $pctCambioVentas >= 0 ? 'up' : 'down' }}">
                    <i data-lucide="{{ $pctCambioVentas >= 0 ? 'arrow-up' : 'arrow-down' }}" style="width:12px;height:12px"></i>
                    {{ $pctCambioVentas >= 0 ? '+' : '' }}{{ $pctCambioVentas }}% vs ayer
                </div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon blue"><i data-lucide="calendar"></i></div>
            <div class="stat-info">
                <div class="stat-value">S/ {{ number_format($ventasMes, 2) }}</div>
                <div class="stat-label">Ventas del Mes</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon amber"><i data-lucide="package"></i></div>
            <div class="stat-info">
                <div class="stat-value">{{ number_format($totalProductos) }}</div>
                <div class="stat-label">Productos Activos</div>
                <div class="stat-change">
                    @if($productosStockBajo > 0)
                        <span class="badge badge-danger" style="font-size:0.65rem">{{ $productosStockBajo }} stock bajo</span>
                    @else
                        <span class="badge badge-success" style="font-size:0.65rem">Stock OK</span>
                    @endif
                </div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon" style="background:rgba(139,92,246,0.15);color:#8B5CF6"><i data-lucide="file-text"></i></div>
            <div class="stat-info">
                <div class="stat-value">{{ $comprobantesHoy }}</div>
                <div class="stat-label">Comprobantes Hoy</div>
                @if($pendientesSunat > 0)
                    <div class="stat-change"><span class="badge badge-warning" style="font-size:0.65rem">{{ $pendientesSunat }} pend. SUNAT</span></div>
                @endif
            </div>
        </div>
    </div>

    {{-- ═══════════════════════ KPIs FILA 2: FINANCIEROS ═══════════════════════ --}}
    <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(220px, 1fr));gap:16px;margin-bottom:24px">
        {{-- Utilidad del Mes --}}
        <div class="card" style="padding:20px;border-left:4px solid {{ $utilidadMes >= 0 ? 'var(--success)' : 'var(--danger)' }}">
            <div style="display:flex;justify-content:space-between;align-items:flex-start">
                <div>
                    <div style="font-size:0.72rem;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.05em;margin-bottom:6px">UTILIDAD DEL MES</div>
                    <div style="font-size:1.6rem;font-weight:900;color:{{ $utilidadMes >= 0 ? 'var(--success)' : 'var(--danger)' }}">
                        {{ $utilidadMes < 0 ? '-' : '' }}S/ {{ number_format(abs($utilidadMes), 2) }}
                    </div>
                    <div style="font-size:0.78rem;color:var(--text-muted);margin-top:4px">
                        Margen: <strong style="color:{{ $margenMes >= 0 ? 'var(--success)' : 'var(--danger)' }}">{{ $margenMes }}%</strong>
                    </div>
                </div>
                <div style="width:44px;height:44px;border-radius:12px;background:{{ $utilidadMes >= 0 ? 'rgba(34,197,94,0.12)' : 'rgba(239,68,68,0.12)' }};display:flex;align-items:center;justify-content:center">
                    <i data-lucide="{{ $utilidadMes >= 0 ? 'trending-up' : 'trending-down' }}" style="width:22px;height:22px;color:{{ $utilidadMes >= 0 ? 'var(--success)' : 'var(--danger)' }}"></i>
                </div>
            </div>
        </div>

        {{-- Compras del Mes --}}
        <div class="card" style="padding:20px;border-left:4px solid var(--warning)">
            <div style="font-size:0.72rem;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.05em;margin-bottom:6px">COMPRAS DEL MES</div>
            <div style="font-size:1.4rem;font-weight:800;color:var(--warning)">S/ {{ number_format($comprasMes, 2) }}</div>
        </div>

        {{-- Gastos del Mes --}}
        <div class="card" style="padding:20px;border-left:4px solid var(--danger)">
            <div style="font-size:0.72rem;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.05em;margin-bottom:6px">GASTOS AL MES</div>
            <div style="font-size:1.4rem;font-weight:800;color:var(--danger)">S/ {{ number_format($gastosMes, 2) }}</div>
        </div>

        {{-- CxC --}}
        <div class="card" style="padding:20px;border-left:4px solid #0ea5e9">
            <div style="display:flex;justify-content:space-between;align-items:flex-start">
                <div>
                    <div style="font-size:0.72rem;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.05em;margin-bottom:6px">CUENTAS POR COBRAR</div>
                    <div style="font-size:1.4rem;font-weight:800;color:#0ea5e9">S/ {{ number_format($cxcTotal, 2) }}</div>
                    @if($cxcVencidas > 0)
                        <div style="font-size:0.75rem;color:var(--danger);margin-top:4px;font-weight:600">⚠ {{ $cxcVencidas }} vencidas</div>
                    @endif
                </div>
                <a href="{{ route('finanzas.cuentas-cobrar') }}" style="font-size:0.72rem;color:#0ea5e9;font-weight:600;text-decoration:none">Ver CxC →</a>
            </div>
        </div>

        {{-- CxP --}}
        <div class="card" style="padding:20px;border-left:4px solid #f97316">
            <div style="display:flex;justify-content:space-between;align-items:flex-start">
                <div>
                    <div style="font-size:0.72rem;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.05em;margin-bottom:6px">CUENTAS POR PAGAR</div>
                    <div style="font-size:1.4rem;font-weight:800;color:#f97316">S/ {{ number_format($cxpTotal, 2) }}</div>
                    @if($cxpVencidas > 0)
                        <div style="font-size:0.75rem;color:var(--danger);margin-top:4px;font-weight:600">⚠ {{ $cxpVencidas }} vencidas</div>
                    @endif
                </div>
                <a href="{{ route('finanzas.cuentas-pagar') }}" style="font-size:0.72rem;color:#f97316;font-weight:600;text-decoration:none">Ver CxP →</a>
            </div>
        </div>
    </div>

    {{-- ═══════════════════════ GRÁFICOS ═══════════════════════ --}}
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:24px">
        {{-- Ventas 7 días --}}
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Ventas (Últimos 7 días)</h3>
            </div>
            <div style="height:260px;width:100%">
                <canvas id="ventasChart"></canvas>
            </div>
        </div>

        {{-- Flujo Ingresos vs Egresos --}}
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Ingresos vs Egresos (6 meses)</h3>
            </div>
            <div style="height:260px;width:100%">
                <canvas id="flujoChart"></canvas>
            </div>
        </div>
    </div>

    {{-- ═══════════════════════ COBROS Y PAGOS PRÓXIMOS ═══════════════════════ --}}
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:24px">
        {{-- Próximos Cobros (CxC) --}}
        <div class="card">
            <div class="card-header">
                <h3 class="card-title" style="display:flex;align-items:center;gap:8px">
                    <i data-lucide="arrow-down-circle" style="width:16px;color:#0ea5e9"></i> Próximos Cobros
                </h3>
                <a href="{{ route('finanzas.cuentas-cobrar') }}" class="btn btn-sm btn-secondary">Ver todo</a>
            </div>
            <div class="table-container" style="border:none">
                <table>
                    <thead>
                        <tr><th>Doc.</th><th>Cliente</th><th style="text-align:right">Saldo</th><th style="text-align:center">Vence</th></tr>
                    </thead>
                    <tbody>
                        @forelse($proximosCobros as $c)
                            @php $vencida = ($c->dias_para_vencer ?? 0) < 0; @endphp
                            <tr style="{{ $vencida ? 'background:rgba(239,68,68,0.05)' : '' }}">
                                <td style="font-size:0.82rem;font-weight:600">{{ $c->serie }}-{{ $c->correlativo }}</td>
                                <td style="font-size:0.82rem">{{ \Str::limit($c->razon_social ?? '—', 20) }}</td>
                                <td style="text-align:right;font-weight:700;color:#0ea5e9">S/ {{ number_format($c->saldo, 2) }}</td>
                                <td style="text-align:center;font-size:0.78rem">
                                    @if($vencida)
                                        <span class="badge badge-danger" style="font-size:0.65rem">{{ abs($c->dias_para_vencer) }}d vencida</span>
                                    @else
                                        <span style="color:var(--text-muted)">{{ $c->dias_para_vencer }}d</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4" style="text-align:center;color:var(--text-muted);padding:30px">Sin cobros pendientes</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Próximos Pagos (CxP) --}}
        <div class="card">
            <div class="card-header">
                <h3 class="card-title" style="display:flex;align-items:center;gap:8px">
                    <i data-lucide="arrow-up-circle" style="width:16px;color:#f97316"></i> Próximos Pagos
                </h3>
                <a href="{{ route('finanzas.cuentas-pagar') }}" class="btn btn-sm btn-secondary">Ver todo</a>
            </div>
            <div class="table-container" style="border:none">
                <table>
                    <thead>
                        <tr><th>Doc.</th><th>Proveedor</th><th style="text-align:right">Saldo</th><th style="text-align:center">Vence</th></tr>
                    </thead>
                    <tbody>
                        @forelse($proximosPagos as $p)
                            @php $vencida = ($p->dias_para_vencer ?? 0) < 0; @endphp
                            <tr style="{{ $vencida ? 'background:rgba(239,68,68,0.05)' : '' }}">
                                <td style="font-size:0.82rem;font-weight:600">{{ $p->serie }}-{{ $p->correlativo }}</td>
                                <td style="font-size:0.82rem">{{ \Str::limit($p->razon_social ?? '—', 20) }}</td>
                                <td style="text-align:right;font-weight:700;color:#f97316">S/ {{ number_format($p->saldo, 2) }}</td>
                                <td style="text-align:center;font-size:0.78rem">
                                    @if($vencida)
                                        <span class="badge badge-danger" style="font-size:0.65rem">{{ abs($p->dias_para_vencer) }}d vencida</span>
                                    @else
                                        <span style="color:var(--text-muted)">{{ $p->dias_para_vencer }}d</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4" style="text-align:center;color:var(--text-muted);padding:30px">Sin pagos pendientes</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- ═══════════════════════ TABLA VENTAS + TOP CLIENTES ═══════════════════════ --}}
    <div style="display:grid;grid-template-columns:2fr 1fr;gap:20px;margin-bottom:24px">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Últimas Ventas</h3>
                <a href="{{ route('ventas.listado') }}" class="btn btn-sm btn-secondary">Ver todo</a>
            </div>
            <div class="table-container" style="border:none">
                <table>
                    <thead><tr><th>Comprobante</th><th>Cliente</th><th>Total</th><th>Estado</th></tr></thead>
                    <tbody>
                        @forelse ($ultimasVentas as $venta)
                            <tr>
                                <td>
                                    <strong>{{ $venta->serie }}-{{ $venta->correlativo }}</strong><br>
                                    <span style="font-size:0.75rem;color:var(--text-muted)">{{ $venta->nombre_tipo_documento }}</span>
                                </td>
                                <td>{{ \Str::limit($venta->cliente->razon_social ?? '-', 25) }}</td>
                                <td><strong>S/ {{ number_format($venta->total, 2) }}</strong></td>
                                <td>
                                    @if($venta->estado === 'aceptado')
                                        <span class="badge badge-success">Aceptado</span>
                                    @elseif($venta->estado === 'emitido')
                                        <span class="badge badge-info">Emitido</span>
                                    @elseif($venta->estado === 'rechazado')
                                        <span class="badge badge-danger">Rechazado</span>
                                    @else
                                        <span class="badge badge-neutral">{{ ucfirst($venta->estado) }}</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4" style="text-align:center;color:var(--text-muted);padding:40px;">No hay ventas registradas aún</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><h3 class="card-title">Top 5 Clientes (Mes)</h3></div>
            <div class="top-clientes-list">
                @forelse ($topClientes as $index => $c)
                    <div style="display:flex;justify-content:space-between;align-items:center;padding:12px;border-bottom:1px solid var(--border-color)">
                        <div style="display:flex;align-items:center;gap:12px;">
                            <div style="width:28px;height:28px;border-radius:50%;background:rgba(16,185,129,0.1);color:var(--primary-400);display:flex;align-items:center;justify-content:center;font-weight:bold;font-size:12px;">
                                {{ $index + 1 }}
                            </div>
                            <span style="font-size:13px;font-weight:600;color:var(--text-primary)">
                                {{ \Str::limit($c->razon_social, 20) }}
                            </span>
                        </div>
                        <span style="font-weight:800;color:var(--accent)">
                            S/ {{ number_format($c->total_comprado, 2) }}
                        </span>
                    </div>
                @empty
                    <div style="text-align:center;color:var(--text-muted);padding:40px;">
                        No hay compras este mes
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- ═══════════════════════ STOCK BAJO + ACCESOS RÁPIDOS ═══════════════════════ --}}
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:24px">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Alertas de Stock Bajo</h3>
                <a href="{{ route('inventario.reporte') }}" class="btn btn-sm btn-secondary">Ver inventario</a>
            </div>
            <div class="table-container" style="border:none">
                <table>
                    <thead><tr><th>Producto</th><th>Stock</th><th>Mínimo</th><th>Estado</th></tr></thead>
                    <tbody>
                        @forelse ($productosAlerta as $prod)
                            <tr>
                                <td>
                                    <strong>{{ \Str::limit($prod->nombre, 30) }}</strong><br>
                                    <span style="font-size:0.75rem;color:var(--text-muted)">{{ $prod->codigo_interno }}</span>
                                </td>
                                <td>{{ number_format($prod->stock_total, 2) }}</td>
                                <td>{{ number_format($prod->stock_minimo, 2) }}</td>
                                <td>
                                    @if($prod->stock_total <= 0)
                                        <span class="badge badge-danger">Agotado</span>
                                    @else
                                        <span class="badge badge-warning">Bajo</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4" style="text-align:center;color:var(--text-muted);padding:40px;">No hay alertas</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><h3 class="card-title">Accesos Rápidos</h3></div>
            <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(160px, 1fr));gap:10px;">
                <a href="{{ route('productos.index') }}" class="btn btn-secondary" style="justify-content:center"><i data-lucide="package"></i> Productos</a>
                <a href="{{ route('clientes.index') }}" class="btn btn-secondary" style="justify-content:center"><i data-lucide="users"></i> Clientes</a>
                <a href="{{ route('compras.nuevo') }}" class="btn btn-secondary" style="justify-content:center"><i data-lucide="receipt"></i> Nueva Compra</a>
                <a href="{{ route('inventario.kardex') }}" class="btn btn-secondary" style="justify-content:center"><i data-lucide="scroll-text"></i> Kardex</a>
                <a href="{{ route('finanzas.contabilidad.asientos') }}" class="btn btn-secondary" style="justify-content:center"><i data-lucide="book"></i> Asientos</a>
                <a href="{{ route('finanzas.contabilidad.balance-general') }}" class="btn btn-secondary" style="justify-content:center"><i data-lucide="scale"></i> Balance</a>
                <a href="{{ route('finanzas.contabilidad.presupuesto') }}" class="btn btn-secondary" style="justify-content:center"><i data-lucide="target"></i> Presupuesto</a>
                <a href="{{ route('inventario.prediccion') }}" class="btn btn-secondary" style="justify-content:center;background:rgba(99,102,241,0.1);color:#6366f1;border-color:rgba(99,102,241,0.2);">
                    <i data-lucide="brain-circuit"></i> Predicción IA
                </a>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        lucide.createIcons();

        // ── Chart 1: Ventas 7 días ──
        const ctx1 = document.getElementById('ventasChart').getContext('2d');
        const gradient1 = ctx1.createLinearGradient(0, 0, 0, 260);
        gradient1.addColorStop(0, 'rgba(16, 185, 129, 0.35)');
        gradient1.addColorStop(1, 'rgba(16, 185, 129, 0)');

        new Chart(ctx1, {
            type: 'line',
            data: {
                labels: @json($fechas7Dias),
                datasets: [{
                    label: 'Ventas',
                    data: @json($ventas7Dias),
                    borderColor: '#10b981',
                    backgroundColor: gradient1,
                    borderWidth: 3,
                    pointBackgroundColor: '#10b981',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#1e293b', titleColor: '#f1f5f9', bodyColor: '#a7f3d0',
                        callbacks: { label: ctx => 'S/ ' + ctx.parsed.y.toLocaleString('es-PE', {minimumFractionDigits:2}) }
                    }
                },
                scales: {
                    x: { grid: { display: false }, ticks: { color: '#94a3b8', font: { size: 11 } } },
                    y: { grid: { color: 'rgba(255,255,255,0.05)' }, ticks: { color: '#94a3b8', callback: v => 'S/ ' + v } }
                }
            }
        });

        // ── Chart 2: Ingresos vs Egresos 6 meses ──
        const ctx2 = document.getElementById('flujoChart').getContext('2d');
        new Chart(ctx2, {
            type: 'bar',
            data: {
                labels: @json($flujoLabels),
                datasets: [
                    {
                        label: 'Ingresos',
                        data: @json($flujoIngresos),
                        backgroundColor: 'rgba(34, 197, 94, 0.7)',
                        borderColor: 'rgba(34, 197, 94, 1)',
                        borderWidth: 1,
                        borderRadius: 4,
                    },
                    {
                        label: 'Egresos',
                        data: @json($flujoEgresos),
                        backgroundColor: 'rgba(239, 68, 68, 0.6)',
                        borderColor: 'rgba(239, 68, 68, 1)',
                        borderWidth: 1,
                        borderRadius: 4,
                    }
                ]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'top', labels: { color: '#94a3b8', font: { size: 11 }, usePointStyle: true } },
                    tooltip: {
                        backgroundColor: '#1e293b', titleColor: '#f1f5f9',
                        callbacks: { label: ctx => ctx.dataset.label + ': S/ ' + ctx.parsed.y.toLocaleString('es-PE', {minimumFractionDigits:2}) }
                    }
                },
                scales: {
                    x: { grid: { display: false }, ticks: { color: '#94a3b8', font: { size: 11 } } },
                    y: { grid: { color: 'rgba(255,255,255,0.05)' }, ticks: { color: '#94a3b8', callback: v => 'S/ ' + v } }
                }
            }
        });
    </script>
@endpush