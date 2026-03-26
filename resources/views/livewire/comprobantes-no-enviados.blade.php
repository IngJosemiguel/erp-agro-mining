<div>
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:16px;margin-bottom:24px">
        <div class="card" style="padding:20px">
            <div style="display:flex;align-items:center;gap:12px">
                <div
                    style="width:44px;height:44px;border-radius:10px;background:rgba(239,68,68,0.15);display:flex;align-items:center;justify-content:center">
                    <i data-lucide="cloud-off" style="width:22px;height:22px;color:#ef4444"></i></div>
                <div>
                    <p style="font-size:0.75rem;color:var(--text-muted);margin:0">PENDIENTES DE ENVÍO</p>
                    <p style="font-size:1.5rem;font-weight:800;margin:0;color:#ef4444">{{ $totalPendientes }}</p>
                </div>
            </div>
        </div>
    </div>
    <div style="display:flex;align-items:center;margin-bottom:20px">
        <div class="search-global" style="width:280px"><i data-lucide="search"></i><input type="text"
                wire:model.live.debounce.300ms="buscar" placeholder="Buscar serie, cliente..."></div>
    </div>
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Serie</th>
                    <th>Correlativo</th>
                    <th>Tipo Doc.</th>
                    <th>Cliente</th>
                    <th>Fecha</th>
                    <th style="text-align:right">Total</th>
                    <th style="text-align:center">Est. SUNAT</th>
                </tr>
            </thead>
            <tbody>@forelse($comprobantes as $c)
                <tr>
                    <td><strong style="font-family:monospace;color:var(--accent)">{{ $c->serie }}</strong></td>
                    <td>{{ $c->correlativo }}</td>
                    <td style="color:var(--text-muted)">{{ $c->cod_tipo ?? '—' }}</td>
                    <td>{{ \Str::limit($c->cliente, 30) }}</td>
                    <td style="color:var(--text-muted)">{{ $c->fecha_emision }}</td>
                    <td style="text-align:right;font-weight:700">S/ {{ number_format($c->total, 2) }}</td>
                    <td style="text-align:center"><span class="badge badge-warning"
                            style="font-size:0.65rem">{{ strtoupper($c->estado_sunat ?? 'PENDIENTE') }}</span></td>
                </tr>
            @empty<tr>
                    <td colspan="7" style="text-align:center;padding:60px;color:var(--text-muted)"><i
                            data-lucide="check-circle-2"
                            style="width:48px;height:48px;display:block;margin:0 auto 12px;color:var(--success)"></i><span
                            style="color:var(--success)">¡Todos los comprobantes enviados a SUNAT!</span></td>
                </tr>@endforelse
            </tbody>
        </table>
    </div>
    <div style="margin-top:16px">{{ $comprobantes->links() }}</div>
</div>