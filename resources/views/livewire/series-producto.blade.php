<div>
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:16px;margin-bottom:24px">
        <div class="card" style="padding:20px">
            <div style="display:flex;align-items:center;gap:12px">
                <div
                    style="width:44px;height:44px;border-radius:10px;background:rgba(0,255,136,0.15);display:flex;align-items:center;justify-content:center">
                    <i data-lucide="package-check" style="width:22px;height:22px;color:var(--accent)"></i>
                </div>
                <div>
                    <p style="font-size:0.75rem;color:var(--text-muted);margin:0">SERIES DISPONIBLES</p>
                    <p style="font-size:1.4rem;font-weight:800;margin:0;color:var(--accent)">{{ $disponibles }}</p>
                </div>
            </div>
        </div>
        <div class="card" style="padding:20px">
            <div style="display:flex;align-items:center;gap:12px">
                <div
                    style="width:44px;height:44px;border-radius:10px;background:rgba(59,130,246,0.15);display:flex;align-items:center;justify-content:center">
                    <i data-lucide="shopping-cart" style="width:22px;height:22px;color:#3b82f6"></i>
                </div>
                <div>
                    <p style="font-size:0.75rem;color:var(--text-muted);margin:0">VENDIDAS</p>
                    <p style="font-size:1.4rem;font-weight:800;margin:0;color:#3b82f6">{{ $vendidas }}</p>
                </div>
            </div>
        </div>
    </div>
    <div
        style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;flex-wrap:wrap;gap:12px">
        <div style="display:flex;gap:10px;align-items:center">
            <div class="search-global" style="width:280px"><i data-lucide="search"></i><input type="text"
                    wire:model.live.debounce.300ms="buscar" placeholder="Buscar serie, producto..."></div>
            <select wire:model.live="filtroEstado" class="form-select" style="width:160px">
                <option value="">Todos</option>
                <option value="disponible">Disponible</option>
                <option value="vendido">Vendido</option>
                <option value="reservado">Reservado</option>
            </select>
        </div>
        <div
            style="display:flex;align-items:center;gap:8px;padding:8px 14px;background:rgba(99,102,241,.1);border:1px solid rgba(99,102,241,.3);border-radius:8px;color:#a5b4fc;font-size:.82rem;">
            <i data-lucide="info" style="width:15px;height:15px;flex-shrink:0;"></i>
            Las series se registran automáticamente al recibir compras con control de series
        </div>
    </div>
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Número de Serie</th>
                    <th>Producto</th>
                    <th>Código</th>
                    <th>F. Ingreso</th>
                    <th>Proveedor Origen</th>
                    <th style="text-align:center">Estado</th>
                </tr>
            </thead>
            <tbody>@forelse($series as $s)
                <tr>
                    <td><strong style="font-family:monospace;color:var(--accent)">{{ $s->numero_serie }}</strong></td>
                    <td>{{ \Str::limit($s->producto ?? '—', 30) }}</td>
                    <td style="color:var(--text-muted)">{{ $s->codigo_prod }}</td>
                    <td style="color:var(--text-muted)">{{ $s->fecha_ingreso }}</td>
                    <td style="color:var(--text-muted)">{{ \Str::limit($s->proveedor_origen ?? '—', 25) }}</td>
                    <td style="text-align:center"><span
                            class="badge {{ match ($s->estado ?? '') { 'disponible' => 'badge-success', 'vendido' => 'badge-warning', 'reservado' => 'badge-neutral', default => 'badge-neutral'} }}"
                            style="font-size:0.65rem">{{ strtoupper($s->estado ?? 'DISPONIBLE') }}</span></td>
                </tr>
            @empty<tr>
                    <td colspan="6" style="text-align:center;padding:60px;color:var(--text-muted)"><i
                            data-lucide="barcode" style="width:48px;height:48px;display:block;margin:0 auto 12px"></i>No
                        hay series registradas</td>
                </tr>@endforelse
            </tbody>
        </table>
    </div>
    <div style="margin-top:16px">{{ $series->links() }}</div>
</div>