<div>
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:16px;margin-bottom:24px">
        <div class="card" style="padding:20px">
            <div style="display:flex;align-items:center;gap:12px">
                <div
                    style="width:44px;height:44px;border-radius:10px;background:rgba(59,130,246,0.15);display:flex;align-items:center;justify-content:center">
                    <i data-lucide="database" style="width:22px;height:22px;color:#3b82f6"></i></div>
                <div>
                    <p style="font-size:0.75rem;color:var(--text-muted);margin:0">REGISTROS ACTIVOS</p>
                    <p style="font-size:1.4rem;font-weight:800;margin:0">{{ $totalRegistros }}</p>
                </div>
            </div>
        </div>
        <div class="card" style="padding:20px">
            <div style="display:flex;align-items:center;gap:12px">
                <div
                    style="width:44px;height:44px;border-radius:10px;background:rgba(0,255,136,0.15);display:flex;align-items:center;justify-content:center">
                    <i data-lucide="coins" style="width:22px;height:22px;color:var(--accent)"></i></div>
                <div>
                    <p style="font-size:0.75rem;color:var(--text-muted);margin:0">VALOR TOTAL INVENTARIO</p>
                    <p style="font-size:1.4rem;font-weight:800;margin:0;color:var(--accent)">S/
                        {{ number_format($totalValorizado, 2) }}</p>
                </div>
            </div>
        </div>
    </div>
    <div style="display:flex;gap:10px;margin-bottom:20px;flex-wrap:wrap;align-items:center">
        <div class="form-group" style="margin:0"><label class="form-label"
                style="font-size:0.75rem">Mes/Año</label><input type="month" wire:model.live="mesAnio"
                class="form-input" style="width:160px"></div>
        <select wire:model.live="filtroAlmacen" class="form-select" style="width:180px;align-self:flex-end">
            <option value="">Todos los almacenes</option>@foreach($almacenes as $a)<option value="{{ $a->id }}">
            {{ $a->nombre }}</option>@endforeach
        </select>
        <span style="color:var(--text-muted);font-size:0.85rem;align-self:flex-end">Formato 13.1 SUNAT —
            {{ $mesActual }}</span>
    </div>
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Código</th>
                    <th>Producto</th>
                    <th>Código SUNAT</th>
                    <th>Almacén</th>
                    <th style="text-align:right">Stock Actual</th>
                    <th style="text-align:right">Costo Prom.</th>
                    <th style="text-align:right">Valor Total</th>
                </tr>
            </thead>
            <tbody>@forelse($stocks as $i => $s)
                <tr>
                    <td style="color:var(--text-muted)">{{ $stocks->firstItem() + $i }}</td>
                    <td style="font-family:monospace;color:var(--accent)">{{ $s->codigo_interno }}</td>
                    <td>{{ \Str::limit($s->producto ?? '—', 35) }}</td>
                    <td style="color:var(--text-muted)">{{ $s->codigo_sunat ?? '—' }}</td>
                    <td style="color:var(--text-muted)">{{ $s->almacen }}</td>
                    <td style="text-align:right;font-weight:700">{{ number_format($s->stock_actual, 2) }}</td>
                    <td style="text-align:right">S/ {{ number_format($s->costo_promedio, 4) }}</td>
                    <td style="text-align:right;font-weight:700;color:var(--accent)">S/
                        {{ number_format($s->stock_actual * $s->costo_promedio, 2) }}</td>
                </tr>
            @empty<tr>
                    <td colspan="8" style="text-align:center;padding:60px;color:var(--text-muted)"><i
                            data-lucide="archive-x"
                            style="width:48px;height:48px;display:block;margin:0 auto 12px"></i>No hay stock disponible
                    </td>
                </tr>@endforelse
            </tbody>
        </table>
    </div>
    <div style="margin-top:16px">{{ $stocks->links() }}</div>
</div>