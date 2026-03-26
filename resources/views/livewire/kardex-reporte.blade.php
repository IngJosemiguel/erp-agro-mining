<div>
    {{-- Formulario de consulta --}}
    <div class="card" style="padding:24px;margin-bottom:24px">
        <p
            style="font-weight:700;font-size:12px;text-transform:uppercase;color:var(--accent);margin:0 0 16px 0;letter-spacing:1px">
            Consultar Kardex</p>

        <div style="display:flex;gap:12px;flex-wrap:wrap;align-items:flex-end">
            {{-- Producto --}}
            <div class="form-group" style="flex:2;min-width:250px;position:relative">
                <label class="form-label">Producto *</label>
                @if($productoSeleccionadoTexto)
                    <div
                        style="display:flex;align-items:center;gap:8px;padding:10px;background:rgba(0,255,136,0.1);border:1px solid var(--accent);border-radius:8px">
                        <span style="flex:1;font-weight:600;font-size:0.85rem">{{ $productoSeleccionadoTexto }}</span>
                        <button wire:click="$set('productoSeleccionadoTexto', '')"
                            wire:click.prevent="$set('producto_id', '')"
                            style="background:none;border:none;color:var(--danger);cursor:pointer">
                            <i data-lucide="x-circle" style="width:16px;height:16px"></i>
                        </button>
                    </div>
                @else
                    <input type="text" wire:model.live.debounce.300ms="buscarProducto" class="form-input"
                        placeholder="Buscar producto...">
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
            </div>

            {{-- Almacén --}}
            <div class="form-group" style="flex:1;min-width:180px">
                <label class="form-label">Almacén *</label>
                <select wire:model="almacen_id" class="form-select">
                    <option value="">— Seleccionar —</option>
                    @foreach($almacenes as $alm)
                        <option value="{{ $alm->id }}">{{ $alm->nombre }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Fechas --}}
            <div class="form-group" style="min-width:140px">
                <label class="form-label">Desde</label>
                <input type="date" wire:model="fechaDesde" class="form-input">
            </div>
            <div class="form-group" style="min-width:140px">
                <label class="form-label">Hasta</label>
                <input type="date" wire:model="fechaHasta" class="form-input">
            </div>

            {{-- Botones --}}
            <div style="display:flex;gap:8px;padding-bottom:4px">
                <button wire:click="consultar" class="btn btn-primary"><i data-lucide="search"></i> Consultar</button>
                <button wire:click="limpiar" class="btn btn-secondary"><i data-lucide="rotate-ccw"></i></button>
            </div>
        </div>

        @error('producto_id') <span
        style="color:var(--danger);font-size:0.75rem;margin-top:4px;display:block">{{ $message }}</span> @enderror
        @error('almacen_id') <span
        style="color:var(--danger);font-size:0.75rem;margin-top:4px;display:block">{{ $message }}</span> @enderror
    </div>

    {{-- Resultados --}}
    @if($consultado)
        {{-- Resumen --}}
        <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(180px, 1fr));gap:12px;margin-bottom:20px">
            <div class="card" style="padding:16px;text-align:center">
                <p style="font-size:0.7rem;color:var(--text-muted);margin:0 0 4px 0;text-transform:uppercase">Total Ingresos
                </p>
                <p style="font-size:1.3rem;font-weight:800;color:var(--success);margin:0">
                    +{{ number_format($resumen['total_ingresos'], 2) }}</p>
            </div>
            <div class="card" style="padding:16px;text-align:center">
                <p style="font-size:0.7rem;color:var(--text-muted);margin:0 0 4px 0;text-transform:uppercase">Total Salidas
                </p>
                <p style="font-size:1.3rem;font-weight:800;color:var(--danger);margin:0">
                    -{{ number_format($resumen['total_salidas'], 2) }}</p>
            </div>
            <div class="card" style="padding:16px;text-align:center">
                <p style="font-size:0.7rem;color:var(--text-muted);margin:0 0 4px 0;text-transform:uppercase">Saldo Final
                </p>
                <p style="font-size:1.3rem;font-weight:800;margin:0">{{ number_format($resumen['saldo_final'], 2) }}</p>
            </div>
            <div class="card" style="padding:16px;text-align:center">
                <p style="font-size:0.7rem;color:var(--text-muted);margin:0 0 4px 0;text-transform:uppercase">Valor Final
                </p>
                <p style="font-size:1.3rem;font-weight:800;color:var(--accent);margin:0">S/
                    {{ number_format($resumen['valor_final'], 2) }}</p>
            </div>
        </div>

        {{-- Tabla Kardex --}}
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Tipo</th>
                        <th>Operación</th>
                        <th>Documento</th>
                        <th style="text-align:right">Cantidad</th>
                        <th style="text-align:right">C. Unitario</th>
                        <th style="text-align:right">C. Total</th>
                        <th style="text-align:right">Saldo Qty</th>
                        <th style="text-align:right">Saldo Valor</th>
                        <th>Motivo</th>
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
                            <td style="color:var(--text-muted);font-size:0.85rem">{{ $mov->tipo_operacion }}</td>
                            <td style="color:var(--text-muted);font-size:0.85rem">
                                {{ $mov->documento_serie ? $mov->documento_serie . '-' . $mov->documento_numero : '—' }}
                            </td>
                            <td
                                style="text-align:right;font-weight:700;color:{{ $mov->tipo_movimiento === 'ingreso' ? 'var(--success)' : 'var(--danger)' }}">
                                {{ $mov->tipo_movimiento === 'ingreso' ? '+' : '-' }}{{ number_format($mov->cantidad, 2) }}
                            </td>
                            <td style="text-align:right;color:var(--text-muted)">S/ {{ number_format($mov->costo_unitario, 4) }}
                            </td>
                            <td style="text-align:right;font-weight:600">S/ {{ number_format($mov->costo_total, 2) }}</td>
                            <td style="text-align:right;font-weight:700">{{ number_format($mov->saldo_cantidad, 2) }}</td>
                            <td style="text-align:right;font-weight:600;color:var(--accent)">S/
                                {{ number_format($mov->saldo_valorizado, 2) }}</td>
                            <td style="color:var(--text-muted);font-size:0.8rem">{{ \Str::limit($mov->motivo, 25) ?: '—' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" style="text-align:center;padding:60px;color:var(--text-muted)">
                                <i data-lucide="file-text" style="width:48px;height:48px;display:block;margin:0 auto 12px"></i>
                                No hay movimientos para este producto en el período seleccionado
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    @else
        <div class="card" style="padding:60px;text-align:center;color:var(--text-muted)">
            <i data-lucide="file-search" style="width:56px;height:56px;display:block;margin:0 auto 16px;opacity:0.5"></i>
            <p style="font-size:1rem;margin:0">Seleccione un producto y almacén para consultar el Kardex</p>
        </div>
    @endif
</div>