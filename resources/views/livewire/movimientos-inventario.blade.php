<div>
    <div
        style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;flex-wrap:wrap;gap:12px">
        <div style="display:flex;gap:10px;flex-wrap:wrap;align-items:center">
            <div class="search-global" style="width:280px">
                <i data-lucide="search"></i>
                <input type="text" wire:model.live.debounce.300ms="buscar" placeholder="Buscar producto...">
            </div>
            <select wire:model.live="filtroTipo" class="form-select" style="width:160px">
                <option value="">Todos los tipos</option>
                <option value="ingreso">Ingresos</option>
                <option value="salida">Salidas</option>
            </select>
            <select wire:model.live="filtroAlmacen" class="form-select" style="width:180px">
                <option value="">Todos los almacenes</option>
                @foreach($almacenes as $alm)
                    <option value="{{ $alm->id }}">{{ $alm->nombre }}</option>
                @endforeach
            </select>
            <input type="date" wire:model.live="fechaDesde" class="form-input" style="width:150px">
            <input type="date" wire:model.live="fechaHasta" class="form-input" style="width:150px">
        </div>
        <button wire:click="openModal" class="btn btn-primary">
            <i data-lucide="plus"></i> Nuevo Movimiento
        </button>
    </div>

    @if(session()->has('success'))
        <div class="alert alert-success"><i data-lucide="check-circle-2"></i><span>{{ session('success') }}</span></div>
    @endif
    @if(session()->has('error'))
        <div class="alert alert-error"><i data-lucide="alert-circle"></i><span>{{ session('error') }}</span></div>
    @endif

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Tipo</th>
                    <th>Producto</th>
                    <th>Almacén</th>
                    <th style="text-align:right">Cantidad</th>
                    <th style="text-align:right">C. Unit.</th>
                    <th style="text-align:right">C. Total</th>
                    <th style="text-align:right">Saldo Qty</th>
                    <th>Motivo</th>
                    <th>Usuario</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($movimientos as $mov)
                    <tr>
                        <td style="white-space:nowrap;color:var(--text-muted)">
                            {{ $mov->fecha_movimiento?->format('d/m/Y') }}</td>
                        <td>
                            @if($mov->tipo_movimiento === 'ingreso')
                                <span class="badge badge-success" style="font-size:0.7rem">INGRESO</span>
                            @else
                                <span class="badge badge-danger" style="font-size:0.7rem">SALIDA</span>
                            @endif
                        </td>
                        <td>
                            <strong>{{ $mov->producto->nombre ?? '—' }}</strong>
                            <br><span
                                style="font-size:0.7rem;color:var(--text-muted)">{{ $mov->producto->codigo_interno ?? '' }}</span>
                        </td>
                        <td style="color:var(--text-muted)">{{ $mov->almacen->nombre ?? '—' }}</td>
                        <td
                            style="text-align:right;font-weight:700;color:{{ $mov->tipo_movimiento === 'ingreso' ? 'var(--success)' : 'var(--danger)' }}">
                            {{ $mov->tipo_movimiento === 'ingreso' ? '+' : '-' }}{{ number_format($mov->cantidad, 2) }}
                        </td>
                        <td style="text-align:right;color:var(--text-muted)">S/ {{ number_format($mov->costo_unitario, 4) }}
                        </td>
                        <td style="text-align:right;font-weight:600">S/ {{ number_format($mov->costo_total, 2) }}</td>
                        <td style="text-align:right;font-weight:600">{{ number_format($mov->saldo_cantidad, 2) }}</td>
                        <td style="color:var(--text-muted);font-size:0.8rem">{{ \Str::limit($mov->motivo, 30) ?: '—' }}</td>
                        <td style="color:var(--text-muted);font-size:0.8rem">{{ $mov->usuario->name ?? '—' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" style="text-align:center;padding:60px;color:var(--text-muted)">
                            <i data-lucide="archive" style="width:48px;height:48px;display:block;margin:0 auto 12px"></i>
                            No hay movimientos en el período seleccionado
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div style="margin-top:16px">{{ $movimientos->links() }}</div>

    {{-- MODAL NUEVO MOVIMIENTO --}}
    @if($showModal)
        <div class="modal-backdrop" wire:click.self="$set('showModal', false)">
            <div class="modal" style="max-width:600px">
                <div class="modal-header">
                    <h3 style="font-weight:700">Registrar Movimiento de Inventario</h3>
                    <button wire:click="$set('showModal', false)"
                        style="background:none;border:none;color:var(--text-muted);cursor:pointer">
                        <i data-lucide="x" style="width:20px;height:20px"></i>
                    </button>
                </div>
                <div class="modal-body">
                    {{-- Tipo de Movimiento --}}
                    <div class="form-group">
                        <label class="form-label">Tipo de Movimiento *</label>
                        <div style="display:flex;gap:8px">
                            <button type="button" wire:click="$set('tipo_movimiento', 'ingreso')"
                                class="btn {{ $tipo_movimiento === 'ingreso' ? 'btn-primary' : 'btn-secondary' }}"
                                style="flex:1">
                                <i data-lucide="arrow-down-circle"></i> Ingreso
                            </button>
                            <button type="button" wire:click="$set('tipo_movimiento', 'salida')"
                                class="btn {{ $tipo_movimiento === 'salida' ? 'btn-danger' : 'btn-secondary' }}"
                                style="flex:1;{{ $tipo_movimiento === 'salida' ? 'background:var(--danger);border-color:var(--danger)' : '' }}">
                                <i data-lucide="arrow-up-circle"></i> Salida
                            </button>
                        </div>
                    </div>

                    {{-- Buscar Producto --}}
                    <div class="form-group" style="position:relative">
                        <label class="form-label">Producto *</label>
                        @if($productoSeleccionado)
                            <div
                                style="display:flex;align-items:center;gap:8px;padding:10px;background:rgba(0,255,136,0.1);border:1px solid var(--accent);border-radius:8px">
                                <span style="flex:1;font-weight:600">{{ $productoSeleccionado }}</span>
                                <button wire:click="$set('productoSeleccionado', '')"
                                    wire:click.prevent="$set('producto_id', '')"
                                    style="background:none;border:none;color:var(--danger);cursor:pointer">
                                    <i data-lucide="x-circle" style="width:18px;height:18px"></i>
                                </button>
                            </div>
                        @else
                            <input type="text" wire:model.live.debounce.300ms="buscarProducto" class="form-input"
                                placeholder="Buscar por nombre o código...">
                            @if(count($productosEncontrados) > 0)
                                <div
                                    style="position:absolute;top:100%;left:0;right:0;z-index:50;background:var(--card-bg);border:1px solid var(--border);border-radius:8px;max-height:200px;overflow-y:auto;box-shadow:0 8px 24px rgba(0,0,0,0.3)">
                                    @foreach($productosEncontrados as $p)
                                        <button wire:click="seleccionarProducto({{ $p['id'] }})"
                                            style="display:block;width:100%;text-align:left;padding:10px 14px;background:none;border:none;border-bottom:1px solid var(--border);color:var(--text-primary);cursor:pointer;font-size:0.85rem"
                                            onmouseover="this.style.background='rgba(0,255,136,0.1)'"
                                            onmouseout="this.style.background='none'">
                                            <strong>{{ $p['codigo_interno'] }}</strong> — {{ $p['nombre'] }}
                                        </button>
                                    @endforeach
                                </div>
                            @endif
                        @endif
                        @error('producto_id') <span style="color:var(--danger);font-size:0.75rem">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Almacén *</label>
                            <select wire:model="almacen_id" class="form-select">
                                <option value="">— Seleccionar —</option>
                                @foreach($almacenes as $alm)
                                    <option value="{{ $alm->id }}">{{ $alm->nombre }}</option>
                                @endforeach
                            </select>
                            @error('almacen_id') <span style="color:var(--danger);font-size:0.75rem">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label class="form-label">Fecha *</label>
                            <input type="date" wire:model="fecha_movimiento" class="form-input">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Cantidad *</label>
                            <input type="number" wire:model="cantidad" class="form-input" step="0.01" min="0.01"
                                placeholder="0.00">
                            @error('cantidad') <span style="color:var(--danger);font-size:0.75rem">{{ $message }}</span>
                            @enderror
                        </div>
                        @if($tipo_movimiento === 'ingreso')
                            <div class="form-group">
                                <label class="form-label">Costo Unitario (S/) *</label>
                                <input type="number" wire:model="costo_unitario" class="form-input" step="0.0001" min="0">
                            </div>
                        @endif
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Tipo Operación</label>
                            <select wire:model="tipo_operacion" class="form-select">
                                <option value="02">02 — Ajuste</option>
                                <option value="01">01 — Compra</option>
                                <option value="16">16 — Merma</option>
                                <option value="21">21 — Devolución</option>
                                <option value="99">99 — Otros</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Motivo / Observación</label>
                            <input type="text" wire:model="motivo" class="form-input" placeholder="Detalle del movimiento">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button wire:click="$set('showModal', false)" class="btn btn-secondary">Cancelar</button>
                    <button wire:click="save" class="btn btn-primary">
                        <i data-lucide="save"></i> Registrar {{ $tipo_movimiento === 'ingreso' ? 'Ingreso' : 'Salida' }}
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>