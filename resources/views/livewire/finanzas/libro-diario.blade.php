<div>
    {{-- Filtros --}}
    <div style="display:flex;gap:12px;margin-bottom:20px;flex-wrap:wrap;align-items:center">
        <input type="month" wire:model.live="periodo" class="form-input" style="width:160px">
        <div class="search-global" style="width:220px">
            <i data-lucide="search"></i>
            <input type="text" wire:model.live.debounce.300ms="filtroCuenta" placeholder="Código o nombre cuenta...">
        </div>
        <select wire:model.live="filtroTipo" class="form-select" style="width:150px">
            <option value="">Todos los tipos</option>
            <option value="venta">Ventas</option>
            <option value="compra">Compras</option>
            <option value="gasto">Gastos</option>
            <option value="caja">Caja/Banco</option>
            <option value="ajuste">Ajustes</option>
        </select>
        <div style="margin-left:auto;display:flex;gap:12px">
            <div class="card" style="padding:10px 20px;text-align:center;min-width:140px">
                <div style="font-size:0.72rem;color:var(--text-muted);margin-bottom:4px">TOTAL DEBE</div>
                <div style="font-size:1.1rem;font-weight:800;color:var(--accent)">S/ {{ number_format($totalDebe, 2) }}
                </div>
            </div>
            <div class="card" style="padding:10px 20px;text-align:center;min-width:140px">
                <div style="font-size:0.72rem;color:var(--text-muted);margin-bottom:4px">TOTAL HABER</div>
                <div style="font-size:1.1rem;font-weight:800;color:var(--danger)">S/ {{ number_format($totalHaber, 2) }}
                </div>
            </div>
        </div>
    </div>

    {{-- Libro Diario --}}
    <div class="table-container" style="max-height:500px;overflow-y:auto">
        <table>
            <thead style="position:sticky;top:0;z-index:1">
                <tr>
                    <th style="width:110px">N° Asiento</th>
                    <th style="width:90px">Fecha</th>
                    <th>Glosa</th>
                    <th style="width:90px">Cta. Código</th>
                    <th>Cuenta</th>
                    <th style="text-align:right;width:120px;color:var(--accent)">DEBE</th>
                    <th style="text-align:right;width:120px;color:var(--danger)">HABER</th>
                </tr>
            </thead>
            <tbody>
                @php $ultimoAsiento = ''; @endphp
                @forelse($partidas as $partida)
                    @php $esNuevoAsiento = $partida->numero_asiento !== $ultimoAsiento;
                    $ultimoAsiento = $partida->numero_asiento; @endphp
                    <tr style="{{ $esNuevoAsiento ? 'border-top:2px solid var(--border-color)' : '' }}">
                        <td>
                            @if($esNuevoAsiento)
                                <code
                                    style="background:var(--bg-elevated);padding:2px 6px;border-radius:4px;font-size:0.75rem">{{ $partida->numero_asiento }}</code>
                            @endif
                        </td>
                        <td style="font-size:0.8rem;color:var(--text-muted)">
                            @if($esNuevoAsiento)
                                {{ \Carbon\Carbon::parse($partida->fecha)->format('d/m/Y') }}
                            @endif
                        </td>
                        <td style="font-size:0.82rem">
                            @if($esNuevoAsiento)
                                {{ \Str::limit($partida->glosa, 45) }}
                            @endif
                        </td>
                        <td style="font-family:monospace;font-size:0.8rem;color:var(--primary)">
                            {{ $partida->cuenta_codigo }}</td>
                        <td style="font-size:0.82rem">{{ $partida->cuenta_nombre }}</td>
                        <td style="text-align:right;font-weight:600;color:var(--accent);font-size:0.85rem">
                            {{ $partida->debe > 0 ? 'S/ ' . number_format($partida->debe, 2) : '' }}
                        </td>
                        <td style="text-align:right;font-weight:600;color:var(--danger);font-size:0.85rem">
                            {{ $partida->haber > 0 ? 'S/ ' . number_format($partida->haber, 2) : '' }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" style="text-align:center;padding:60px;color:var(--text-muted)">
                            <i data-lucide="book-open" style="width:36px;height:36px;margin:0 auto 12px;display:block"></i>
                            Sin movimientos en este período.
                        </td>
                    </tr>
                @endforelse
                @if($partidas->isNotEmpty())
                    <tr style="background:var(--bg-elevated);border-top:3px solid var(--border-color);font-weight:800">
                        <td colspan="5" style="padding:12px 16px">TOTALES DEL PERÍODO</td>
                        <td style="text-align:right;color:var(--accent);font-size:1rem">S/
                            {{ number_format($totalDebe, 2) }}</td>
                        <td style="text-align:right;color:var(--danger);font-size:1rem">S/
                            {{ number_format($totalHaber, 2) }}</td>
                    </tr>
                @endif
            </tbody>
        </table>
    </div>

    {{-- Mayor de Cuentas (resumen) --}}
    @if($mayorCuentas->isNotEmpty())
        <div style="margin-top:28px">
            <h3
                style="font-size:0.95rem;font-weight:700;color:var(--text-secondary);margin-bottom:12px;display:flex;align-items:center;gap:8px">
                <i data-lucide="bar-chart-3" style="width:18px;height:18px;color:var(--primary)"></i>
                Libro Mayor — Resumen por Cuenta
            </h3>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Código</th>
                            <th>Cuenta</th>
                            <th>Tipo</th>
                            <th style="text-align:right;color:var(--accent)">Debe</th>
                            <th style="text-align:right;color:var(--danger)">Haber</th>
                            <th style="text-align:right">Saldo</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($mayorCuentas as $cuenta)
                            <tr>
                                <td><code style="font-size:0.8rem;color:var(--primary)">{{ $cuenta['codigo'] }}</code></td>
                                <td>{{ $cuenta['nombre'] }}</td>
                                <td><span class="badge badge-info"
                                        style="font-size:0.7rem">{{ ucfirst($cuenta['tipo']) }}</span></td>
                                <td style="text-align:right;color:var(--accent)">S/ {{ number_format($cuenta['debe'], 2) }}</td>
                                <td style="text-align:right;color:var(--danger)">S/ {{ number_format($cuenta['haber'], 2) }}
                                </td>
                                <td
                                    style="text-align:right;font-weight:700;color:{{ $cuenta['saldo'] >= 0 ? 'var(--success)' : 'var(--danger)' }}">
                                    {{ $cuenta['saldo'] < 0 ? '-' : '' }}S/ {{ number_format(abs($cuenta['saldo']), 2) }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>