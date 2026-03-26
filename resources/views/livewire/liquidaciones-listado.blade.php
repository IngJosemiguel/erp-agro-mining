<div>
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:16px;margin-bottom:24px">
        <div class="card" style="padding:20px">
            <div style="display:flex;align-items:center;gap:12px">
                <div
                    style="width:44px;height:44px;border-radius:10px;background:rgba(0,255,136,0.15);display:flex;align-items:center;justify-content:center">
                    <i data-lucide="receipt" style="width:22px;height:22px;color:var(--accent)"></i>
                </div>
                <div>
                    <p style="font-size:0.75rem;color:var(--text-muted);margin:0">TOTAL LIQUIDACIONES</p>
                    <p style="font-size:1.4rem;font-weight:800;margin:0;color:var(--accent)">S/
                        {{ number_format($totalLC, 2) }}
                    </p>
                </div>
            </div>
        </div>
        <div class="card" style="padding:20px">
            <div style="display:flex;align-items:center;gap:12px">
                <div
                    style="width:44px;height:44px;border-radius:10px;background:rgba(59,130,246,0.15);display:flex;align-items:center;justify-content:center">
                    <i data-lucide="hash" style="width:22px;height:22px;color:#3b82f6"></i>
                </div>
                <div>
                    <p style="font-size:0.75rem;color:var(--text-muted);margin:0">LIQUIDACIONES EMITIDAS</p>
                    <p style="font-size:1.4rem;font-weight:800;margin:0">{{ $cantEmitidas }}</p>
                </div>
            </div>
        </div>
    </div>
    <div
        style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;flex-wrap:wrap;gap:12px">
        <div class="search-global" style="width:280px"><i data-lucide="search"></i><input type="text"
                wire:model.live.debounce.300ms="buscar" placeholder="Buscar serie, proveedor..."></div>
        <div
            style="display:flex;align-items:center;gap:8px;padding:8px 14px;background:rgba(0,255,136,.07);border:1px solid rgba(0,255,136,.25);border-radius:8px;color:var(--accent);font-size:.82rem;">
            <i data-lucide="info" style="width:15px;height:15px;flex-shrink:0;"></i>
            Las liquidaciones de compra se emiten a productores naturales sin RUC
        </div>
    </div>
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Serie</th>
                    <th>Correlativo</th>
                    <th>Proveedor</th>
                    <th>Fecha</th>
                    <th style="text-align:right">Total</th>
                    <th style="text-align:center">Estado</th>
                </tr>
            </thead>
            <tbody>@forelse($liquidaciones as $l)
                <tr>
                    <td><strong style="font-family:monospace;color:var(--accent)">{{ $l->serie }}</strong></td>
                    <td>{{ $l->correlativo }}</td>
                    <td>{{ \Str::limit($l->proveedor ?? '—', 35) }}</td>
                    <td style="color:var(--text-muted)">{{ $l->fecha_emision }}</td>
                    <td style="text-align:right;font-weight:700">S/ {{ number_format($l->total, 2) }}</td>
                    <td style="text-align:center"><span class="badge badge-success"
                            style="font-size:0.65rem">{{ strtoupper($l->estado ?? 'EMITIDO') }}</span></td>
                </tr>
            @empty<tr>
                    <td colspan="6" style="text-align:center;padding:60px;color:var(--text-muted)"><i
                            data-lucide="receipt" style="width:48px;height:48px;display:block;margin:0 auto 12px"></i>No
                        hay liquidaciones de compra registradas</td>
                </tr>@endforelse
            </tbody>
        </table>
    </div>
    <div style="margin-top:16px">{{ $liquidaciones->links() }}</div>
</div>