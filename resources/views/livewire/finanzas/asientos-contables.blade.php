<div>
    {{-- Mensajes --}}
    @if(session('success'))
        <div class="alert alert-success" style="margin-bottom:16px"><i data-lucide="check-circle-2"></i>
            {{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-error" style="margin-bottom:16px"><i data-lucide="alert-circle"></i> {{ session('error') }}
        </div>
    @endif

    {{-- Stats --}}
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:16px;margin-bottom:24px">
        <div class="card" style="padding:20px;text-align:center">
            <div style="font-size:1.5rem;font-weight:800;color:var(--text-primary)">{{ $stats->total ?? 0 }}</div>
            <div style="font-size:0.78rem;color:var(--text-muted);margin-top:4px">Asientos {{ $filtroPeriodo }}</div>
        </div>
        <div class="card" style="padding:20px;text-align:center">
            <div style="font-size:1.5rem;font-weight:800;color:var(--success)">{{ $stats->validados ?? 0 }}</div>
            <div style="font-size:0.78rem;color:var(--text-muted);margin-top:4px">Validados</div>
        </div>
        <div class="card" style="padding:20px;text-align:center">
            <div style="font-size:1.5rem;font-weight:800;color:var(--warning)">{{ $stats->borradores ?? 0 }}</div>
            <div style="font-size:0.78rem;color:var(--text-muted);margin-top:4px">Borradores</div>
        </div>
        <div class="card" style="padding:20px;text-align:center">
            <div style="font-size:1.2rem;font-weight:800;color:var(--primary)">S/
                {{ number_format((float) ($stats->total_debe ?? 0), 2) }}</div>
            <div style="font-size:0.78rem;color:var(--text-muted);margin-top:4px">Total Debe</div>
        </div>
    </div>

    {{-- Toolbar --}}
    <div
        style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;margin-bottom:16px">
        <div style="display:flex;gap:10px;flex-wrap:wrap">
            <div class="search-global" style="width:240px">
                <i data-lucide="search"></i>
                <input type="text" wire:model.live.debounce.300ms="buscar" placeholder="N° asiento o glosa...">
            </div>
            <input type="month" wire:model.live="filtroPeriodo" class="form-input" style="width:160px">
            <select wire:model.live="filtroEstado" class="form-select" style="width:140px">
                <option value="">Todos</option>
                <option value="borrador">Borrador</option>
                <option value="validado">Validado</option>
                <option value="anulado">Anulado</option>
            </select>
        </div>
        <button wire:click="$set('showModal', true)" class="btn btn-primary">
            <i data-lucide="plus"></i> Nuevo Asiento
        </button>
    </div>

    {{-- Tabla de asientos --}}
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>N° Asiento</th>
                    <th>Fecha</th>
                    <th>Tipo</th>
                    <th>Glosa</th>
                    <th style="text-align:right">Debe</th>
                    <th style="text-align:right">Haber</th>
                    <th style="text-align:center">Estado</th>
                    <th style="text-align:center">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($asientos as $asiento)
                    <tr>
                        <td><code
                                style="background:var(--bg-elevated);padding:2px 8px;border-radius:4px;font-size:0.8rem">{{ $asiento->numero_asiento }}</code>
                        </td>
                        <td style="color:var(--text-muted);font-size:0.85rem">
                            {{ \Carbon\Carbon::parse($asiento->fecha)->format('d/m/Y') }}</td>
                        <td>
                            <span class="badge badge-info" style="font-size:0.7rem">{{ ucfirst($asiento->tipo) }}</span>
                        </td>
                        <td>{{ \Str::limit($asiento->glosa, 50) }}</td>
                        <td style="text-align:right;font-weight:600;color:var(--accent)">S/
                            {{ number_format($asiento->total_debe, 2) }}</td>
                        <td style="text-align:right;font-weight:600;color:var(--danger)">S/
                            {{ number_format($asiento->total_haber, 2) }}</td>
                        <td style="text-align:center">
                            @if($asiento->estado === 'validado')
                                <span class="badge badge-success" style="font-size:0.7rem">Validado</span>
                            @elseif($asiento->estado === 'borrador')
                                <span class="badge badge-warning" style="font-size:0.7rem">Borrador</span>
                            @else
                                <span class="badge"
                                    style="font-size:0.7rem;background:var(--bg-elevated);color:var(--text-muted)">Anulado</span>
                            @endif
                        </td>
                        <td style="text-align:center">
                            <div style="display:flex;gap:4px;justify-content:center">
                                <button wire:click="verDetalle({{ $asiento->id }})" class="btn btn-sm btn-secondary"
                                    title="Ver detalle">
                                    <i data-lucide="eye"></i>
                                </button>
                                @if($asiento->estado === 'borrador')
                                    <button wire:click="validarAsiento({{ $asiento->id }})" class="btn btn-sm btn-primary"
                                        title="Validar asiento">
                                        <i data-lucide="check"></i>
                                    </button>
                                    <button wire:click="anularAsiento({{ $asiento->id }})" wire:confirm="¿Anular este asiento?"
                                        class="btn btn-sm btn-danger" title="Anular">
                                        <i data-lucide="x"></i>
                                    </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" style="text-align:center;padding:60px;color:var(--text-muted)">
                            <i data-lucide="book-open" style="width:40px;height:40px;margin:0 auto 12px;display:block"></i>
                            No hay asientos en este período.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div style="margin-top:16px">{{ $asientos->links() }}</div>

    {{-- Modal nuevo asiento --}}
    @if($showModal)
        <div class="modal-backdrop" wire:click.self="$set('showModal', false)">
            <div class="modal" style="max-width:860px;max-height:90vh;overflow-y:auto">
                <div class="modal-header"
                    style="position:sticky;top:0;background:var(--bg-card);z-index:2;border-bottom:1px solid var(--border-color)">
                    <h3 style="font-weight:700;display:flex;align-items:center;gap:8px">
                        <i data-lucide="book-plus" style="width:20px;height:20px;color:var(--primary)"></i>
                        Nuevo Asiento Contable
                    </h3>
                    <button wire:click="$set('showModal', false)"
                        style="background:none;border:none;color:var(--text-muted);cursor:pointer">
                        <i data-lucide="x" style="width:20px"></i>
                    </button>
                </div>
                <div class="modal-body">
                    {{-- Cabecera del asiento --}}
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Fecha *</label>
                            <input type="date" wire:model="fecha" class="form-input">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Tipo de Asiento</label>
                            <select wire:model="tipo" class="form-select">
                                <option value="apertura">Apertura</option>
                                <option value="venta">Venta</option>
                                <option value="compra">Compra</option>
                                <option value="gasto">Gasto</option>
                                <option value="caja">Caja/Banco</option>
                                <option value="ajuste">Ajuste</option>
                                <option value="depreciacion">Depreciación</option>
                                <option value="nómina">Nómina</option>
                                <option value="otro">Otro</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Glosa (descripción) *</label>
                        <input type="text" wire:model="glosa" class="form-input"
                            placeholder="Ej: Venta al contado según F001-00125">
                        @error('glosa') <span style="color:var(--danger);font-size:0.75rem">{{ $message }}</span> @enderror
                    </div>

                    {{-- Partidas --}}
                    <div style="margin-top:20px">
                        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px">
                            <h4 style="font-size:0.9rem;font-weight:700;color:var(--text-secondary)">PARTIDAS (Partida
                                Doble)</h4>
                            <button wire:click="agregarPartida" type="button" class="btn btn-sm btn-secondary">
                                <i data-lucide="plus"></i> Agregar fila
                            </button>
                        </div>

                        @error('partidas') <div
                            style="color:var(--danger);font-size:0.8rem;margin-bottom:8px;padding:8px;background:rgba(239,68,68,0.1);border-radius:6px">
                        ⚠ {{ $message }}</div> @enderror

                        <table style="width:100%;border-collapse:collapse;font-size:0.85rem">
                            <thead>
                                <tr style="border-bottom:2px solid var(--border-color)">
                                    <th
                                        style="text-align:left;padding:8px 6px;color:var(--text-muted);font-weight:600;width:100px">
                                        Código</th>
                                    <th style="text-align:left;padding:8px 6px;color:var(--text-muted);font-weight:600">
                                        Cuenta</th>
                                    <th style="text-align:left;padding:8px 6px;color:var(--text-muted);font-weight:600">
                                        Glosa Partida</th>
                                    <th
                                        style="text-align:right;padding:8px 6px;color:var(--accent);font-weight:600;width:120px">
                                        DEBE</th>
                                    <th
                                        style="text-align:right;padding:8px 6px;color:var(--danger);font-weight:600;width:120px">
                                        HABER</th>
                                    <th style="width:36px"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($partidas as $idx => $partida)
                                    <tr style="border-bottom:1px solid var(--border-color)">
                                        <td style="padding:6px">
                                            <input type="text" wire:model="partidas.{{ $idx }}.cuenta_codigo"
                                                wire:blur="buscarCuenta({{ $idx }})" class="form-input"
                                                style="width:100%;font-size:0.82rem;padding:6px 10px;font-family:monospace"
                                                placeholder="1.1.1">
                                        </td>
                                        <td style="padding:6px">
                                            <input type="text" wire:model="partidas.{{ $idx }}.cuenta_nombre" class="form-input"
                                                style="width:100%;font-size:0.82rem;padding:6px 10px;background:var(--bg-elevated)"
                                                placeholder="Busca por código →" readonly>
                                        </td>
                                        <td style="padding:6px">
                                            <input type="text" wire:model="partidas.{{ $idx }}.glosa_partida" class="form-input"
                                                style="width:100%;font-size:0.82rem;padding:6px 10px"
                                                placeholder="Referencia...">
                                        </td>
                                        <td style="padding:6px">
                                            <input type="number" wire:model.blur="partidas.{{ $idx }}.debe" class="form-input"
                                                style="width:100%;font-size:0.82rem;padding:6px 10px;text-align:right;color:var(--accent)"
                                                step="0.01" min="0" placeholder="0.00">
                                        </td>
                                        <td style="padding:6px">
                                            <input type="number" wire:model.blur="partidas.{{ $idx }}.haber" class="form-input"
                                                style="width:100%;font-size:0.82rem;padding:6px 10px;text-align:right;color:var(--danger)"
                                                step="0.01" min="0" placeholder="0.00">
                                        </td>
                                        <td style="padding:6px;text-align:center">
                                            <button wire:click="eliminarPartida({{ $idx }})" type="button"
                                                style="background:none;border:none;cursor:pointer;color:var(--text-muted)">
                                                <i data-lucide="x" style="width:14px;height:14px"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr style="border-top:2px solid var(--border-color);background:var(--bg-elevated)">
                                    <td colspan="3"
                                        style="padding:10px 6px;font-size:0.82rem;color:var(--text-muted);font-weight:600">
                                        TOTALES</td>
                                    <td style="padding:10px 6px;text-align:right;font-weight:700;color:var(--accent)">
                                        S/ {{ number_format($totalDebe, 2) }}
                                    </td>
                                    <td style="padding:10px 6px;text-align:right;font-weight:700;color:var(--danger)">
                                        S/ {{ number_format($totalHaber, 2) }}
                                    </td>
                                    <td style="padding:6px;text-align:center">
                                        @if(abs($totalDebe - $totalHaber) < 0.01 && $totalDebe > 0)
                                            <i data-lucide="check-circle-2" style="width:16px;height:16px;color:var(--success)"
                                                title="Asiento cuadrado ✓"></i>
                                        @elseif($totalDebe > 0 || $totalHaber > 0)
                                            <i data-lucide="alert-triangle" style="width:16px;height:16px;color:var(--warning)"
                                                title="Asiento descuadrado"></i>
                                        @endif
                                    </td>
                                </tr>
                                @if($totalDebe > 0 && abs($totalDebe - $totalHaber) > 0.01)
                                    <tr>
                                        <td colspan="6" style="padding:6px;text-align:center">
                                            <span style="color:var(--warning);font-size:0.78rem;font-weight:600">
                                                ⚠ Diferencia: S/ {{ number_format(abs($totalDebe - $totalHaber), 2) }} — El
                                                asiento debe cuadrar (Debe = Haber)
                                            </span>
                                        </td>
                                    </tr>
                                @endif
                            </tfoot>
                        </table>
                    </div>

                    <div class="form-group" style="margin-top:16px">
                        <label class="form-label">Observaciones</label>
                        <textarea wire:model="observaciones" class="form-input" rows="2"
                            placeholder="Notas adicionales..."></textarea>
                    </div>
                </div>
                <div class="modal-footer"
                    style="position:sticky;bottom:0;background:var(--bg-card);border-top:1px solid var(--border-color)">
                    <button wire:click="$set('showModal', false)" class="btn btn-secondary">Cancelar</button>
                    <button wire:click="guardarAsiento" class="btn btn-primary" @if(abs($totalDebe - $totalHaber) > 0.01 && ($totalDebe > 0 || $totalHaber > 0)) title="El asiento no está cuadrado" @endif>
                        <i data-lucide="save"></i> Guardar Asiento
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- Modal Ver Detalle --}}
    @if($showDetalle && $asientoDetalle)
        <div class="modal-backdrop" wire:click.self="$set('showDetalle', false)">
            <div class="modal" style="max-width:700px">
                <div class="modal-header">
                    <h3 style="font-weight:700">Asiento {{ $asientoDetalle->numero_asiento }}</h3>
                    <button wire:click="$set('showDetalle', false)"
                        style="background:none;border:none;color:var(--text-muted);cursor:pointer">
                        <i data-lucide="x" style="width:20px"></i>
                    </button>
                </div>
                <div class="modal-body">
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:16px;font-size:0.85rem">
                        <div><span style="color:var(--text-muted)">Fecha:</span>
                            <strong>{{ \Carbon\Carbon::parse($asientoDetalle->fecha)->format('d/m/Y') }}</strong></div>
                        <div><span style="color:var(--text-muted)">Tipo:</span>
                            <strong>{{ ucfirst($asientoDetalle->tipo) }}</strong></div>
                        <div><span style="color:var(--text-muted)">Glosa:</span>
                            <strong>{{ $asientoDetalle->glosa }}</strong></div>
                        <div><span style="color:var(--text-muted)">Estado:</span> <span
                                class="badge badge-{{ $asientoDetalle->estado === 'validado' ? 'success' : 'warning' }}">{{ ucfirst($asientoDetalle->estado) }}</span>
                        </div>
                    </div>
                    <table style="width:100%;border-collapse:collapse;font-size:0.85rem">
                        <thead>
                            <tr style="border-bottom:2px solid var(--border-color)">
                                <th style="text-align:left;padding:8px;color:var(--text-muted)">Código</th>
                                <th style="text-align:left;padding:8px;color:var(--text-muted)">Cuenta</th>
                                <th style="text-align:right;padding:8px;color:var(--accent)">DEBE</th>
                                <th style="text-align:right;padding:8px;color:var(--danger)">HABER</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($partidasDetalle as $partida)
                                <tr style="border-bottom:1px solid var(--border-color)">
                                    <td style="padding:8px;font-family:monospace;font-size:0.8rem;color:var(--primary)">
                                        {{ $partida->codigo }}</td>
                                    <td style="padding:8px">{{ $partida->cuenta_nombre }}<br>@if($partida->glosa_partida)<small
                                    style="color:var(--text-muted)">{{ $partida->glosa_partida }}</small>@endif</td>
                                    <td style="padding:8px;text-align:right;color:var(--accent);font-weight:600">
                                        {{ $partida->debe > 0 ? 'S/ ' . number_format($partida->debe, 2) : '—' }}</td>
                                    <td style="padding:8px;text-align:right;color:var(--danger);font-weight:600">
                                        {{ $partida->haber > 0 ? 'S/ ' . number_format($partida->haber, 2) : '—' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr style="background:var(--bg-elevated);border-top:2px solid var(--border-color)">
                                <td colspan="2" style="padding:10px 8px;font-weight:700">TOTAL</td>
                                <td style="padding:10px 8px;text-align:right;font-weight:700;color:var(--accent)">S/
                                    {{ number_format($asientoDetalle->total_debe, 2) }}</td>
                                <td style="padding:10px 8px;text-align:right;font-weight:700;color:var(--danger)">S/
                                    {{ number_format($asientoDetalle->total_haber, 2) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                <div class="modal-footer">
                    <button wire:click="$set('showDetalle', false)" class="btn btn-secondary">Cerrar</button>
                </div>
            </div>
        </div>
    @endif
</div>