<div>
    {{-- KPIs --}}
    <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(220px, 1fr));gap:16px;margin-bottom:24px">
        <div class="card" style="padding:20px">
            <div style="display:flex;align-items:center;gap:12px">
                <div
                    style="width:44px;height:44px;border-radius:10px;background:rgba(0,255,136,0.15);display:flex;align-items:center;justify-content:center">
                    <i data-lucide="package" style="width:22px;height:22px;color:var(--accent)"></i>
                </div>
                <div>
                    <p style="font-size:0.75rem;color:var(--text-muted);margin:0">PRODUCTOS EN STOCK</p>
                    <p style="font-size:1.5rem;font-weight:800;margin:0">{{ $totalItems }}</p>
                </div>
            </div>
        </div>
        <div class="card" style="padding:20px">
            <div style="display:flex;align-items:center;gap:12px">
                <div
                    style="width:44px;height:44px;border-radius:10px;background:rgba(59,130,246,0.15);display:flex;align-items:center;justify-content:center">
                    <i data-lucide="banknote" style="width:22px;height:22px;color:#3b82f6"></i>
                </div>
                <div>
                    <p style="font-size:0.75rem;color:var(--text-muted);margin:0">VALOR TOTAL INVENTARIO</p>
                    <p style="font-size:1.5rem;font-weight:800;margin:0">S/ {{ number_format($totalValor, 2) }}</p>
                </div>
            </div>
        </div>
        <div class="card" style="padding:20px">
            <div style="display:flex;align-items:center;gap:12px">
                <div
                    style="width:44px;height:44px;border-radius:10px;background:rgba(239,68,68,0.15);display:flex;align-items:center;justify-content:center">
                    <i data-lucide="alert-triangle" style="width:22px;height:22px;color:#ef4444"></i>
                </div>
                <div>
                    <p style="font-size:0.75rem;color:var(--text-muted);margin:0">SIN STOCK</p>
                    <p style="font-size:1.5rem;font-weight:800;margin:0;color:#ef4444">{{ $sinStock }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Filtros --}}
    <div
        style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;flex-wrap:wrap;gap:12px">
        <div style="display:flex;gap:10px;flex-wrap:wrap;align-items:center">
            <div class="search-global" style="width:280px">
                <i data-lucide="search"></i>
                <input type="text" wire:model.live.debounce.300ms="buscar" placeholder="Buscar producto...">
            </div>
            <select wire:model.live="filtroAlmacen" class="form-select" style="width:180px">
                <option value="">Todos los almacenes</option>
                @foreach($almacenes as $alm)
                    <option value="{{ $alm->id }}">{{ $alm->nombre }}</option>
                @endforeach
            </select>
            <label
                style="display:flex;align-items:center;gap:6px;cursor:pointer;font-size:0.85rem;color:var(--text-muted)">
                <input type="checkbox" wire:model.live="soloConStock" style="accent-color:var(--accent)"> Con stock
            </label>
            <label style="display:flex;align-items:center;gap:6px;cursor:pointer;font-size:0.85rem;color:#ef4444">
                <input type="checkbox" wire:model.live="soloStockBajo" style="accent-color:#ef4444"> Stock bajo
            </label>
        </div>
    </div>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Código</th>
                    <th>Producto</th>
                    <th>Categoría</th>
                    <th>Almacén</th>
                    <th>UM</th>
                    <th style="text-align:right">Stock Actual</th>
                    <th style="text-align:right">Comprometido</th>
                    <th style="text-align:right">Disponible</th>
                    <th style="text-align:right">C. Promedio</th>
                    <th style="text-align:right">Valor Total</th>
                    <th style="text-align:center">Estado</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($inventario as $item)
                    @php
                        $stockMin = $item->producto->stock_minimo ?? 0;
                        $disponible = $item->stock_actual - $item->stock_comprometido;
                        $valor = $item->stock_actual * $item->costo_promedio;
                        $estado = 'normal';
                        if ($item->stock_actual <= 0)
                            $estado = 'agotado';
                        elseif ($item->stock_actual <= $stockMin)
                            $estado = 'bajo';
                    @endphp
                    <tr>
                        <td><span class="badge badge-neutral"
                                style="font-family:monospace">{{ $item->producto->codigo_interno }}</span></td>
                        <td><strong>{{ \Str::limit($item->producto->nombre, 35) }}</strong></td>
                        <td style="color:var(--text-muted)">{{ $item->producto->categoria->nombre ?? '—' }}</td>
                        <td style="color:var(--text-muted)">{{ $item->almacen->nombre ?? '—' }}</td>
                        <td style="color:var(--text-muted);text-align:center">
                            {{ $item->producto->unidadMedida->abreviatura ?? 'UND' }}
                        </td>
                        <td style="text-align:right;font-weight:700">{{ number_format($item->stock_actual, 2) }}</td>
                        <td style="text-align:right;color:var(--text-muted)">
                            {{ number_format($item->stock_comprometido, 2) }}
                        </td>
                        <td
                            style="text-align:right;font-weight:600;color:{{ $disponible > 0 ? 'var(--accent)' : 'var(--danger)' }}">
                            {{ number_format($disponible, 2) }}
                        </td>
                        <td style="text-align:right;color:var(--text-muted)">S/
                            {{ number_format($item->costo_promedio, 4) }}
                        </td>
                        <td style="text-align:right;font-weight:600">S/ {{ number_format($valor, 2) }}</td>
                        <td style="text-align:center">
                            @if($estado === 'agotado')
                                <span class="badge badge-danger">AGOTADO</span>
                            @elseif($estado === 'bajo')
                                <span class="badge" style="background:rgba(245,158,11,0.15);color:#f59e0b">BAJO</span>
                            @else
                                <span class="badge badge-success">OK</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="11" style="text-align:center;padding:60px;color:var(--text-muted)">
                            <i data-lucide="package-open"
                                style="width:48px;height:48px;display:block;margin:0 auto 12px"></i>
                            No hay registros de inventario
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div style="margin-top:16px">{{ $inventario->links() }}</div>
</div>