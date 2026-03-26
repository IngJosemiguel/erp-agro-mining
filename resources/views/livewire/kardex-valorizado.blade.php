<div>
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:16px;margin-bottom:24px">
        <div class="card" style="padding:20px">
            <div style="display:flex;align-items:center;gap:12px">
                <div
                    style="width:44px;height:44px;border-radius:10px;background:rgba(0,255,136,0.15);display:flex;align-items:center;justify-content:center">
                    <i data-lucide="arrow-down-circle" style="width:22px;height:22px;color:var(--accent)"></i></div>
                <div>
                    <p style="font-size:0.75rem;color:var(--text-muted);margin:0">TOTAL INGRESOS</p>
                    <p style="font-size:1.4rem;font-weight:800;margin:0;color:var(--accent)">S/
                        {{ number_format($totalIngresos, 2) }}</p>
                </div>
            </div>
        </div>
        <div class="card" style="padding:20px">
            <div style="display:flex;align-items:center;gap:12px">
                <div
                    style="width:44px;height:44px;border-radius:10px;background:rgba(239,68,68,0.15);display:flex;align-items:center;justify-content:center">
                    <i data-lucide="arrow-up-circle" style="width:22px;height:22px;color:#ef4444"></i></div>
                <div>
                    <p style="font-size:0.75rem;color:var(--text-muted);margin:0">TOTAL SALIDAS</p>
                    <p style="font-size:1.4rem;font-weight:800;margin:0;color:#ef4444">S/
                        {{ number_format($totalSalidas, 2) }}</p>
                </div>
            </div>
        </div>
    </div>
    <div style="display:flex;gap:10px;margin-bottom:20px;flex-wrap:wrap">
        <div class="search-global" style="width:250px"><i data-lucide="search"></i><input type="text"
                wire:model.live.debounce.300ms="buscar" placeholder="Buscar producto, código..."></div>
        <select wire:model.live="filtroAlmacen" class="form-select" style="width:180px">
            <option value="">Todos los almacenes</option>@foreach($almacenes as $a)<option value="{{ $a->id }}">
            {{ $a->nombre }}</option>@endforeach
        </select>
        <input type="date" wire:model.live="fechaDesde" class="form-input" style="width:150px">
        <input type="date" wire:model.live="fechaHasta" class="form-input" style="width:150px">
    </div>
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Producto</th>
                    <th>Código</th>
                    <th>Almacén</th>
                    <th style="text-align:center">Tipo</th>
                    <th>Motivo</th>
                    <th style="text-align:right">Cantidad</th>
                    <th style="text-align:right">Costo Unit.</th>
                    <th style="text-align:right">Costo Total</th>
                    <th style="text-align:right">Saldo Val.</th>
                </tr>
            </thead>
            <tbody>@forelse($movimientos as $m)
                <tr>
                    <td style="color:var(--text-muted);white-space:nowrap">{{ $m->fecha_movimiento }}</td>
                    <td>{{ \Str::limit($m->producto ?? '—', 25) }}</td>
                    <td style="font-family:monospace;color:var(--accent);font-size:0.8rem">{{ $m->codigo_prod }}</td>
                    <td style="color:var(--text-muted)">{{ $m->almacen ?? '—' }}</td>
                    <td style="text-align:center"><span
                            class="badge {{ $m->tipo_movimiento === 'entrada' ? 'badge-success' : 'badge-danger' }}"
                            style="font-size:0.65rem">{{ strtoupper($m->tipo_movimiento) }}</span></td>
                    <td style="font-size:0.8rem;color:var(--text-muted)">{{ \Str::limit($m->motivo ?? '—', 22) }}</td>
                    <td style="text-align:right">{{ number_format($m->cantidad, 2) }}</td>
                    <td style="text-align:right">S/ {{ number_format($m->costo_unitario, 4) }}</td>
                    <td
                        style="text-align:right;font-weight:600;color:{{ $m->tipo_movimiento === 'entrada' ? 'var(--accent)' : '#ef4444' }}">
                        S/ {{ number_format($m->costo_total, 2) }}</td>
                    <td style="text-align:right;color:var(--text-muted)">S/ {{ number_format($m->saldo_valorizado, 2) }}
                    </td>
                </tr>
            @empty
                    <tr>
                        <td colspan="10" style="text-align:center;padding:60px;color:var(--text-muted)">
                            <i data-lucide="book-open" style="width:48px;height:48px;display:block;margin:0 auto 12px"></i>
                            No hay movimientos en el período seleccionado
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div style="margin-top:16px">{{ $movimientos->links() }}</div>
</div>