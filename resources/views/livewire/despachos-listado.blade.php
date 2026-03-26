<div>
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:16px;margin-bottom:24px">
        <div class="card" style="padding:20px">
            <div style="display:flex;align-items:center;gap:12px">
                <div
                    style="width:44px;height:44px;border-radius:10px;background:rgba(0,255,136,0.15);display:flex;align-items:center;justify-content:center">
                    <i data-lucide="truck" style="width:22px;height:22px;color:var(--accent)"></i>
                </div>
                <div>
                    <p style="font-size:0.75rem;color:var(--text-muted);margin:0">TOTAL DESPACHOS</p>
                    <p style="font-size:1.5rem;font-weight:800;margin:0">{{ $totalDespachos }}</p>
                </div>
            </div>
        </div>
        <div class="card" style="padding:20px">
            <div style="display:flex;align-items:center;gap:12px">
                <div
                    style="width:44px;height:44px;border-radius:10px;background:rgba(245,158,11,0.15);display:flex;align-items:center;justify-content:center">
                    <i data-lucide="package-open" style="width:22px;height:22px;color:#f59e0b"></i>
                </div>
                <div>
                    <p style="font-size:0.75rem;color:var(--text-muted);margin:0">EN TRÁNSITO</p>
                    <p style="font-size:1.5rem;font-weight:800;margin:0;color:#f59e0b">{{ $enTransito }}</p>
                </div>
            </div>
        </div>
    </div>
    <div
        style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;flex-wrap:wrap;gap:12px">
        <div class="search-global" style="width:280px"><i data-lucide="search"></i><input type="text"
                wire:model.live.debounce.300ms="buscar" placeholder="Buscar serie, cliente..."></div>
        <div
            style="display:flex;align-items:center;gap:8px;padding:8px 14px;background:rgba(245,158,11,.1);border:1px solid rgba(245,158,11,.3);border-radius:8px;color:#fbbf24;font-size:.82rem;">
            <i data-lucide="truck" style="width:15px;height:15px;flex-shrink:0;"></i>
            Los despachos se generan automáticamente al confirmar ventas
        </div>
    </div>
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Serie</th>
                    <th>Correlativo</th>
                    <th>Tipo</th>
                    <th>Destinatario</th>
                    <th>Fecha</th>
                    <th style="text-align:right">Total</th>
                    <th style="text-align:center">Estado</th>
                </tr>
            </thead>
            <tbody>@forelse($despachos as $d)
                <tr>
                    <td><strong style="font-family:monospace;color:var(--accent)">{{ $d->serie }}</strong></td>
                    <td>{{ $d->correlativo }}</td>
                    <td style="color:var(--text-muted)">{{ $d->tipo_doc ?? 'GR' }}</td>
                    <td>{{ \Str::limit($d->cliente, 30) }}</td>
                    <td style="color:var(--text-muted)">{{ $d->fecha_emision }}</td>
                    <td style="text-align:right;font-weight:700">S/ {{ number_format($d->total, 2) }}</td>
                    <td style="text-align:center"><span
                            class="badge {{ $d->estado === 'pagado' ? 'badge-success' : 'badge-warning' }}"
                            style="font-size:0.65rem">{{ strtoupper($d->estado ?? 'PENDIENTE') }}</span></td>
                </tr>
            @empty<tr>
                    <td colspan="7" style="text-align:center;padding:60px;color:var(--text-muted)"><i
                            data-lucide="truck" style="width:48px;height:48px;display:block;margin:0 auto 12px"></i>No
                        hay despachos registrados</td>
                </tr>@endforelse
            </tbody>
        </table>
    </div>
    <div style="margin-top:16px">{{ $despachos->links() }}</div>
</div>