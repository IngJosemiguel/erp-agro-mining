<div>
    {{-- Filtros Superiores --}}
    <div style="display:flex;gap:12px;margin-bottom:20px;flex-wrap:wrap;align-items:flex-end">
        <div style="display:flex;align-items:center;gap:8px">
            <label class="form-label" style="margin:0;color:var(--text-muted)">Desde:</label>
            <input type="month" wire:model.live="periodoDesde" class="form-input" style="width:140px">
        </div>
        <div style="display:flex;align-items:center;gap:8px">
            <label class="form-label" style="margin:0;color:var(--text-muted)">Hasta:</label>
            <input type="month" wire:model.live="periodoHasta" class="form-input" style="width:140px">
        </div>

        <div style="width:320px;position:relative">
            <label class="form-label" style="font-size:0.75rem;margin-bottom:4px">Filtrar por Cuenta Contable Específica</label>
            <div style="display:flex;gap:4px">
                @if($cuentaId)
                    <div style="flex:1;padding:8px 12px;background:rgba(14,165,233,0.1);border:1px solid var(--primary);border-radius:6px;display:flex;justify-content:space-between;align-items:center;">
                        <span style="font-size:0.85rem;font-weight:600;color:var(--primary)">{{ $cuentaBusqueda }}</span>
                        <button wire:click="$set('cuentaId', null)" style="background:none;border:none;cursor:pointer;color:var(--text-muted)"><i data-lucide="x" style="width:14px"></i></button>
                    </div>
                @else
                    <div class="search-global" style="flex:1;border:1px solid var(--border-color)">
                        <i data-lucide="search"></i>
                        <input type="text" wire:model.live.debounce.300ms="cuentaBusqueda" placeholder="Buscar por código...">
                    </div>
                @endif
            </div>

            @if(strlen($cuentaBusqueda) > 2 && empty($cuentaId))
                <div style="position:absolute;top:100%;left:0;width:100%;z-index:20;border:1px solid var(--border-color);border-radius:0 0 6px 6px;max-height:200px;overflow-y:auto;background:var(--bg-elevated);margin-top:-2px">
                    @forelse($cuentasSelector as $c)
                        <div wire:click="seleccionarCuenta({{ $c->id }}, '{{ $c->codigo }} — {{ $c->nombre }}')" 
                             style="padding:8px 12px;border-bottom:1px solid var(--border-color);cursor:pointer;font-size:0.85rem">
                            <span style="font-family:monospace;color:var(--primary);margin-right:8px">{{ $c->codigo }}</span>
                            {{ $c->nombre }}
                        </div>
                    @empty
                        <div style="padding:12px;text-align:center;color:var(--text-muted);font-size:0.85rem">No hay registros</div>
                    @endforelse
                </div>
            @endif
        </div>
    </div>


    {{-- Visualización de Datos --}}
    @if($cuentaId)
        {{-- VISIÓN CUENTA T (UNA SOLA CUENTA DETALLADA) --}}
        <div class="card" style="padding:0;overflow:hidden;border-top:3px solid var(--primary)">
            <div style="background:var(--bg-elevated);padding:16px 20px;border-bottom:1px solid var(--border-color);text-align:center">
                <h3 style="font-size:1.1rem;font-weight:800;color:var(--primary);margin-bottom:4px">{{ $cuentaBusqueda }}</h3>
                <p style="font-size:0.75rem;color:var(--text-muted)">Libro Mayor Individual Auxiliar</p>
            </div>
            
            <table style="width:100%;border-collapse:collapse">
                <thead>
                    <tr style="background:rgba(0,0,0,0.02)">
                        <th style="padding:10px 16px;text-align:left;font-size:0.8rem;border-bottom:1px solid var(--border-color)">Asiento/Fecha</th>
                        <th style="padding:10px 16px;text-align:left;font-size:0.8rem;border-bottom:1px solid var(--border-color)">Descripción (Glosa)</th>
                        <th style="padding:10px 16px;text-align:right;font-size:0.8rem;border-bottom:1px solid var(--border-color);color:var(--primary);width:150px">DEBE</th>
                        <th style="padding:10px 16px;text-align:right;font-size:0.8rem;border-bottom:1px solid var(--border-color);color:var(--warning);width:150px">HABER</th>
                    </tr>
                </thead>
                <tbody>
                    @php $sumDebe = 0; $sumHaber = 0; @endphp
                    @forelse($movimientosDetalle as $mov)
                        @php 
                            $sumDebe += $mov->debe; 
                            $sumHaber += $mov->haber; 
                        @endphp
                        <tr style="border-bottom:1px solid rgba(0,0,0,0.05)">
                            <td style="padding:8px 16px;">
                                <div style="font-size:0.8rem;font-weight:600">{{ $mov->numero }}</div>
                                <div style="font-size:0.75rem;color:var(--text-muted)">{{ \Carbon\Carbon::parse($mov->fecha)->format('d/m/Y') }}</div>
                            </td>
                            <td style="padding:8px 16px;font-size:0.85rem">{{ $mov->asiento_glosa }}</td>
                            <td style="padding:8px 16px;text-align:right;font-family:monospace">{{ $mov->debe > 0 ? number_format($mov->debe, 2) : '' }}</td>
                            <td style="padding:8px 16px;text-align:right;font-family:monospace">{{ $mov->haber > 0 ? number_format($mov->haber, 2) : '' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" style="text-align:center;padding:30px;color:var(--text-muted)">No hay registros para este período.</td></tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr style="background:var(--bg-elevated);border-top:2px solid var(--border-color)">
                        <td colspan="2" style="padding:12px 16px;text-align:right;font-weight:700;font-size:0.85rem">Totales Movimientos:</td>
                        <td style="padding:12px 16px;text-align:right;font-weight:800;color:var(--primary);font-family:monospace;border-right:1px solid rgba(0,0,0,0.05)">S/ {{ number_format($sumDebe, 2) }}</td>
                        <td style="padding:12px 16px;text-align:right;font-weight:800;color:var(--warning);font-family:monospace">S/ {{ number_format($sumHaber, 2) }}</td>
                    </tr>
                    @php 
                        // asumiendo que ya traemos la naturaleza, o la calculamos por si acaso
                        $saldo = $sumDebe - $sumHaber;
                        $saldoAbs = abs($saldo);
                        $esDeudor = clone $cuentasSaldos->firstWhere('cuenta_id', $this->cuentaId)->naturaleza ?? 'deudora';
                    @endphp
                    <tr style="background:{{ $saldo >= 0 ? 'rgba(34,197,94,0.05)' : 'rgba(239,68,68,0.05)' }};border-top:1px dashed var(--border-color)">
                        <td colspan="2" style="padding:12px 16px;text-align:right;font-weight:800;font-size:0.9rem">SALDO FINAL:</td>
                        <td colspan="2" style="padding:12px 16px;text-align:center;font-weight:900;font-size:1.1rem;color:{{ $saldo >= 0 ? 'var(--success)' : 'var(--danger)' }}">
                            S/ {{ number_format($saldoAbs, 2) }} 
                            <span style="font-size:0.75rem;font-weight:400;color:var(--text-muted)">({{ $saldo > 0 ? 'Deudor' : ($saldo < 0 ? 'Acreedor' : 'Saldado') }})</span>
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>

    @else
        {{-- VISIÓN GENERAL (RESUMEN DE MAYOR DE TODAS LAS CUENTAS CON MOVIMIENTO) --}}
        <div style="display:grid;grid-template-columns:repeat(auto-fill, minmax(320px, 1fr));gap:20px">
            @forelse($cuentasSaldos as $c)
                @php 
                    $saldo_cc = $c->naturaleza === 'deudora' ? ($c->total_debe - $c->total_haber) : ($c->total_haber - $c->total_debe);
                @endphp
                <div class="card" style="padding:0;overflow:hidden;border:1px solid var(--border-color);box-shadow:none">
                    <div style="background:var(--bg-elevated);padding:10px 14px;border-bottom:1px solid var(--border-color);display:flex;justify-content:space-between;align-items:center">
                        <div style="font-size:0.85rem;font-weight:700">{{ $c->codigo }} - {{ $c->nombre }}</div>
                        <button wire:click="seleccionarCuenta({{ $c->cuenta_id }}, '{{ $c->codigo }} — {{ $c->nombre }}')" style="background:none;border:none;color:var(--primary);cursor:pointer;font-size:0.75rem;font-weight:600">Ver T &rarr;</button>
                    </div>
                    <div style="display:flex;padding:12px 0;">
                        <div style="flex:1;text-align:center;border-right:1px solid rgba(0,0,0,0.05)">
                            <div style="font-size:0.65rem;color:var(--text-muted);letter-spacing:0.05em">DEBE</div>
                            <div style="font-family:monospace;font-size:0.9rem;font-weight:600;margin-top:2px">S/ {{ number_format($c->total_debe, 2) }}</div>
                        </div>
                        <div style="flex:1;text-align:center">
                            <div style="font-size:0.65rem;color:var(--text-muted);letter-spacing:0.05em">HABER</div>
                            <div style="font-family:monospace;font-size:0.9rem;font-weight:600;margin-top:2px">S/ {{ number_format($c->total_haber, 2) }}</div>
                        </div>
                    </div>
                    <div style="padding:10px 0;text-align:center;background:{{ $saldo_cc >= 0 ? 'rgba(34,197,94,0.05)' : 'rgba(239,68,68,0.08)' }};border-top:1px solid var(--border-color)">
                        <div style="font-size:0.7rem;color:var(--text-muted);text-transform:uppercase">
                            SALDO {{ $c->naturaleza }} ({{ $saldo_cc >= 0 ? 'Ok' : 'INVERSO' }})
                        </div>
                        <div style="font-size:1.1rem;font-weight:800;color:{{ $saldo_cc >= 0 ? 'var(--text-primary)' : 'var(--danger)' }}">
                            S/ {{ number_format(abs($saldo_cc), 2) }}
                        </div>
                    </div>
                </div>
            @empty
                <div style="grid-column:1/-1;text-align:center;padding:60px" class="card">
                    <i data-lucide="book-open" style="width:40px;height:40px;margin:0 auto 12px;opacity:0.3;display:block"></i>
                    <p style="color:var(--text-muted)">No hay asientos validados en este rango de meses.</p>
                </div>
            @endforelse
        </div>
    @endif
</div>
