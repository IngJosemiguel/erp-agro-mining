<div>
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:16px;margin-bottom:24px">
        <div class="card" style="padding:20px">
            <div style="display:flex;align-items:center;gap:12px">
                <div
                    style="width:44px;height:44px;border-radius:10px;background:rgba(0,255,136,0.15);display:flex;align-items:center;justify-content:center">
                    <i data-lucide="file-text" style="width:22px;height:22px;color:var(--accent)"></i>
                </div>
                <div>
                    <p style="font-size:0.75rem;color:var(--text-muted);margin:0">TOTAL COTIZACIONES</p>
                    <p style="font-size:1.5rem;font-weight:800;margin:0">S/ {{ number_format($totalCot, 2) }}</p>
                </div>
            </div>
        </div>
        <div class="card" style="padding:20px">
            <div style="display:flex;align-items:center;gap:12px">
                <div
                    style="width:44px;height:44px;border-radius:10px;background:rgba(245,158,11,0.15);display:flex;align-items:center;justify-content:center">
                    <i data-lucide="clock" style="width:22px;height:22px;color:#f59e0b"></i>
                </div>
                <div>
                    <p style="font-size:0.75rem;color:var(--text-muted);margin:0">PENDIENTES</p>
                    <p style="font-size:1.5rem;font-weight:800;margin:0;color:#f59e0b">{{ $canPendientes }}</p>
                </div>
            </div>
        </div>
    </div>
    <div
        style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;flex-wrap:wrap;gap:12px">
        <div class="search-global" style="width:280px"><i data-lucide="search"></i><input type="text"
                wire:model.live.debounce.300ms="buscar" placeholder="Buscar serie, cliente..."></div>
        <div
            style="display:flex;align-items:center;gap:8px;padding:8px 14px;background:rgba(99,102,241,.1);border:1px solid rgba(99,102,241,.3);border-radius:8px;color:#a5b4fc;font-size:.82rem;">
            <i data-lucide="info" style="width:15px;height:15px;flex-shrink:0;"></i>
            Las cotizaciones de venta se generan desde el POS o la atención al cliente
        </div>
    </div>
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Serie</th>
                    <th>Correlativo</th>
                    <th>Cliente</th>
                    <th>Fecha</th>
                    <th style="text-align:right">Total</th>
                    <th style="text-align:center">Estado</th>
                </tr>
            </thead>
            <tbody>@forelse($cotizaciones as $c)
                <tr>
                    <td><strong style="font-family:monospace;color:var(--accent)">{{ $c->serie }}</strong></td>
                    <td>{{ $c->correlativo }}</td>
                    <td>{{ \Str::limit($c->cliente, 35) }}</td>
                    <td style="color:var(--text-muted)">{{ $c->fecha_emision }}</td>
                    <td style="text-align:right;font-weight:700">S/ {{ number_format($c->total, 2) }}</td>
                    <td style="text-align:center"><span class="badge badge-warning"
                            style="font-size:0.65rem">{{ strtoupper($c->estado ?? 'PENDIENTE') }}</span></td>
                </tr>
            @empty<tr>
                    <td colspan="6" style="text-align:center;padding:60px;color:var(--text-muted)"><i
                            data-lucide="file-text"
                            style="width:48px;height:48px;display:block;margin:0 auto 12px"></i>No hay cotizaciones
                        registradas</td>
                </tr>@endforelse
            </tbody>
        </table>
    </div>
    <div style="margin-top:16px">{{ $cotizaciones->links() }}</div>
</div>