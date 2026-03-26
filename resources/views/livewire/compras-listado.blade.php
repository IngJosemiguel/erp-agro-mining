<div>
    {{-- KPIs --}}
    <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(220px, 1fr));gap:16px;margin-bottom:24px">
        <div class="card" style="padding:20px">
            <div style="display:flex;align-items:center;gap:12px">
                <div
                    style="width:44px;height:44px;border-radius:10px;background:rgba(0,255,136,0.15);display:flex;align-items:center;justify-content:center">
                    <i data-lucide="receipt" style="width:22px;height:22px;color:var(--accent)"></i>
                </div>
                <div>
                    <p style="font-size:0.75rem;color:var(--text-muted);margin:0">COMPRAS DEL PERÍODO</p>
                    <p style="font-size:1.5rem;font-weight:800;margin:0">{{ $cantCompras }}</p>
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
                    <p style="font-size:0.75rem;color:var(--text-muted);margin:0">TOTAL COMPRAS</p>
                    <p style="font-size:1.5rem;font-weight:800;margin:0">S/ {{ number_format($totalCompras, 2) }}</p>
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
                    <p style="font-size:0.75rem;color:var(--text-muted);margin:0">PENDIENTES INGRESO</p>
                    <p style="font-size:1.5rem;font-weight:800;margin:0;color:#f59e0b">{{ $pendientes }}</p>
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
                <input type="text" wire:model.live.debounce.300ms="buscar" placeholder="Buscar serie, proveedor...">
            </div>
            <select wire:model.live="filtroEstado" class="form-select" style="width:170px">
                <option value="">Todos los estados</option>
                <option value="borrador">Borrador</option>
                <option value="registrada">Registrada</option>
                <option value="ingresada_almacen">Ingresada</option>
                <option value="anulada">Anulada</option>
            </select>
            <input type="date" wire:model.live="fechaDesde" class="form-input" style="width:150px">
            <input type="date" wire:model.live="fechaHasta" class="form-input" style="width:150px">
        </div>
        <a href="{{ route('compras.nuevo') }}" class="btn btn-primary">
            <i data-lucide="plus"></i> Nueva Compra
        </a>
    </div>

    @if(session()->has('success'))
        <div class="alert alert-success"><i data-lucide="check-circle-2"></i><span>{{ session('success') }}</span></div>
    @endif

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Documento</th>
                    <th>Proveedor</th>
                    <th>Almacén</th>
                    <th style="text-align:center">Items</th>
                    <th style="text-align:right">Subtotal</th>
                    <th style="text-align:right">IGV</th>
                    <th style="text-align:right">Total</th>
                    <th>Pago</th>
                    <th style="text-align:center">Estado</th>
                    <th>Usuario</th>
                    <th style="text-align:center">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($compras as $c)
                    <tr>
                        <td style="white-space:nowrap;color:var(--text-muted)">{{ $c->fecha_emision?->format('d/m/Y') }}
                        </td>
                        <td>
                            <span style="font-size:0.7rem;color:var(--text-muted)">{{ $c->tipo_documento_texto }}</span><br>
                            <strong>{{ $c->serie }}-{{ $c->correlativo }}</strong>
                        </td>
                        <td>
                            <strong>{{ \Str::limit($c->proveedor->razon_social ?? '—', 30) }}</strong>
                            <br><span
                                style="font-size:0.7rem;color:var(--text-muted)">{{ $c->proveedor->numero_documento ?? '' }}</span>
                        </td>
                        <td style="color:var(--text-muted)">{{ $c->almacen->nombre ?? '—' }}</td>
                        <td style="text-align:center">{{ $c->detalles_count }}</td>
                        <td style="text-align:right;color:var(--text-muted)">S/ {{ number_format($c->subtotal, 2) }}</td>
                        <td style="text-align:right;color:var(--text-muted)">S/ {{ number_format($c->igv, 2) }}</td>
                        <td style="text-align:right;font-weight:700">S/ {{ number_format($c->total, 2) }}</td>
                        <td>
                            <span class="badge {{ $c->forma_pago === 'contado' ? 'badge-success' : 'badge-neutral' }}"
                                style="font-size:0.65rem">
                                {{ strtoupper($c->forma_pago) }}
                            </span>
                        </td>
                        <td style="text-align:center">
                            <span class="badge {{ $c->estado_badge }}"
                                style="font-size:0.65rem">{{ $c->estado_texto }}</span>
                        </td>
                        <td style="color:var(--text-muted);font-size:0.8rem">{{ $c->usuario->name ?? '—' }}</td>
                        <td style="text-align:center">
                            @if($c->estado !== 'anulada')
                                <button wire:click="anular({{ $c->id }})" wire:confirm="¿Está seguro de anular esta compra?"
                                    style="background:none;border:none;color:var(--danger);cursor:pointer" title="Anular">
                                    <i data-lucide="x-circle" style="width:16px;height:16px"></i>
                                </button>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="12" style="text-align:center;padding:60px;color:var(--text-muted)">
                            <i data-lucide="shopping-cart"
                                style="width:48px;height:48px;display:block;margin:0 auto 12px"></i>
                            No hay compras registradas en el período
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div style="margin-top:16px">{{ $compras->links() }}</div>
</div>