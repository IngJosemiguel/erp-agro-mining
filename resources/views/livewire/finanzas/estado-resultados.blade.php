<div>
    {{-- Filtros de período --}}
    <div style="display:flex;gap:12px;margin-bottom:20px;flex-wrap:wrap;align-items:center">
        <div style="display:flex;align-items:center;gap:8px">
            <label class="form-label" style="margin:0;white-space:nowrap;color:var(--text-muted)">Desde:</label>
            <input type="month" wire:model.live="periodoDesde" class="form-input" style="width:160px">
        </div>
        <div style="display:flex;align-items:center;gap:8px">
            <label class="form-label" style="margin:0;white-space:nowrap;color:var(--text-muted)">Hasta:</label>
            <input type="month" wire:model.live="periodoHasta" class="form-input" style="width:160px">
        </div>
    </div>

    @if($totalIngresos == 0 && $totalGastos == 0)
        <div class="card" style="text-align:center;padding:60px">
            <i data-lucide="bar-chart-3"
                style="width:48px;height:48px;margin:0 auto 12px;display:block;color:var(--text-muted)"></i>
            <p style="color:var(--text-muted)">Sin asientos validados para este período.<br>Registra y valida asientos
                contables para ver el Estado de Resultados.</p>
        </div>
    @else

        {{-- KPIs --}}
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:16px;margin-bottom:28px">
            <div class="card" style="padding:20px;border-top:3px solid var(--success)">
                <div
                    style="font-size:0.72rem;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.05em;margin-bottom:6px">
                    INGRESOS TOTALES</div>
                <div style="font-size:1.4rem;font-weight:800;color:var(--success)">S/ {{ number_format($totalIngresos, 2) }}
                </div>
            </div>
            <div class="card" style="padding:20px;border-top:3px solid var(--warning)">
                <div
                    style="font-size:0.72rem;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.05em;margin-bottom:6px">
                    COSTO DE VENTAS</div>
                <div style="font-size:1.4rem;font-weight:800;color:var(--warning)">S/ {{ number_format($totalCostos, 2) }}
                </div>
            </div>
            <div class="card" style="padding:20px;border-top:3px solid var(--primary)">
                <div
                    style="font-size:0.72rem;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.05em;margin-bottom:6px">
                    UTILIDAD BRUTA</div>
                <div
                    style="font-size:1.4rem;font-weight:800;color:{{ $utilidadBruta >= 0 ? 'var(--primary)' : 'var(--danger)' }}">
                    {{ $utilidadBruta < 0 ? '-' : '' }}S/ {{ number_format(abs($utilidadBruta), 2) }}
                    <span style="font-size:0.75rem;color:var(--text-muted);font-weight:400"> ({{ $margenBruto }}%)</span>
                </div>
            </div>
            <div class="card" style="padding:20px;border-top:3px solid var(--danger)">
                <div
                    style="font-size:0.72rem;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.05em;margin-bottom:6px">
                    GASTOS OPERATIVOS</div>
                <div style="font-size:1.4rem;font-weight:800;color:var(--danger)">S/ {{ number_format($totalGastos, 2) }}
                </div>
            </div>
            <div class="card"
                style="padding:20px;border-top:3px solid {{ $utilidadOperativa >= 0 ? 'var(--success)' : 'var(--danger)' }}">
                <div
                    style="font-size:0.72rem;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.05em;margin-bottom:6px">
                    UTILIDAD NETA</div>
                <div
                    style="font-size:1.5rem;font-weight:900;color:{{ $utilidadOperativa >= 0 ? 'var(--success)' : 'var(--danger)' }}">
                    {{ $utilidadOperativa < 0 ? '-' : '' }}S/ {{ number_format(abs($utilidadOperativa), 2) }}
                    <span style="font-size:0.75rem;font-weight:400"> ({{ $margenOperativo }}%)</span>
                </div>
            </div>
        </div>

        {{-- Estado de Resultados formal --}}
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">

            {{-- Columna izquierda: detalle --}}
            <div style="display:flex;flex-direction:column;gap:16px">
                {{-- Ingresos --}}
                <div class="card" style="padding:0;overflow:hidden">
                    <div
                        style="background:linear-gradient(135deg,rgba(34,197,94,0.12),rgba(34,197,94,0.04));padding:12px 20px;border-bottom:1px solid var(--border-color);display:flex;justify-content:space-between;align-items:center">
                        <h3
                            style="font-size:0.88rem;font-weight:700;color:var(--success);display:flex;align-items:center;gap:8px">
                            <i data-lucide="trending-up" style="width:16px;height:16px"></i> INGRESOS
                        </h3>
                        <span style="font-weight:800;color:var(--success)">S/ {{ number_format($totalIngresos, 2) }}</span>
                    </div>
                    <table style="width:100%;border-collapse:collapse">
                        <tbody>
                            @forelse($ingresos as $cuenta)
                                <tr style="border-bottom:1px solid var(--border-color)20">
                                    <td style="padding:8px 16px;font-size:0.78rem;font-family:monospace;color:var(--success)">
                                        {{ $cuenta->codigo }}</td>
                                    <td style="padding:8px 16px;font-size:0.82rem">{{ $cuenta->nombre }}</td>
                                    <td style="padding:8px 16px;text-align:right;font-size:0.85rem;font-weight:600">S/
                                        {{ number_format($cuenta->saldo, 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3"
                                        style="padding:16px;text-align:center;color:var(--text-muted);font-size:0.82rem">Sin
                                        ingresos en el período</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Costos --}}
                <div class="card" style="padding:0;overflow:hidden">
                    <div
                        style="background:linear-gradient(135deg,rgba(245,158,11,0.12),rgba(245,158,11,0.04));padding:12px 20px;border-bottom:1px solid var(--border-color);display:flex;justify-content:space-between;align-items:center">
                        <h3
                            style="font-size:0.88rem;font-weight:700;color:var(--warning);display:flex;align-items:center;gap:8px">
                            <i data-lucide="package" style="width:16px;height:16px"></i> COSTO DE VENTAS
                        </h3>
                        <span style="font-weight:800;color:var(--warning)">S/ {{ number_format($totalCostos, 2) }}</span>
                    </div>
                    <table style="width:100%;border-collapse:collapse">
                        <tbody>
                            @forelse($costos as $cuenta)
                                <tr style="border-bottom:1px solid var(--border-color)20">
                                    <td style="padding:8px 16px;font-size:0.78rem;font-family:monospace;color:var(--warning)">
                                        {{ $cuenta->codigo }}</td>
                                    <td style="padding:8px 16px;font-size:0.82rem">{{ $cuenta->nombre }}</td>
                                    <td style="padding:8px 16px;text-align:right;font-size:0.85rem;font-weight:600">S/
                                        {{ number_format($cuenta->saldo, 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3"
                                        style="padding:16px;text-align:center;color:var(--text-muted);font-size:0.82rem">Sin
                                        costos registrados</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Gastos --}}
                <div class="card" style="padding:0;overflow:hidden">
                    <div
                        style="background:linear-gradient(135deg,rgba(239,68,68,0.10),rgba(239,68,68,0.04));padding:12px 20px;border-bottom:1px solid var(--border-color);display:flex;justify-content:space-between;align-items:center">
                        <h3
                            style="font-size:0.88rem;font-weight:700;color:var(--danger);display:flex;align-items:center;gap:8px">
                            <i data-lucide="minus-circle" style="width:16px;height:16px"></i> GASTOS OPERATIVOS
                        </h3>
                        <span style="font-weight:800;color:var(--danger)">S/ {{ number_format($totalGastos, 2) }}</span>
                    </div>
                    <table style="width:100%;border-collapse:collapse">
                        <tbody>
                            @forelse($gastos as $cuenta)
                                <tr style="border-bottom:1px solid var(--border-color)20">
                                    <td style="padding:8px 16px;font-size:0.78rem;font-family:monospace;color:var(--danger)">
                                        {{ $cuenta->codigo }}</td>
                                    <td style="padding:8px 16px;font-size:0.82rem">{{ $cuenta->nombre }}</td>
                                    <td style="padding:8px 16px;text-align:right;font-size:0.85rem;font-weight:600">S/
                                        {{ number_format($cuenta->saldo, 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3"
                                        style="padding:16px;text-align:center;color:var(--text-muted);font-size:0.82rem">Sin
                                        gastos registrados</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Columna derecha: resumen formal --}}
            <div class="card" style="padding:0;overflow:hidden;align-self:start">
                <div style="background:var(--bg-elevated);padding:16px 20px;border-bottom:2px solid var(--border-color)">
                    <h3 style="font-size:0.9rem;font-weight:700;color:var(--text-primary)">ESTADO DE RESULTADOS</h3>
                    <p style="font-size:0.75rem;color:var(--text-muted);margin-top:4px">
                        {{ \Carbon\Carbon::createFromFormat('Y-m', $periodoDesde)->format('M Y') }}
                        — {{ \Carbon\Carbon::createFromFormat('Y-m', $periodoHasta)->format('M Y') }}
                    </p>
                </div>
                <div style="padding:20px;display:flex;flex-direction:column;gap:0">
                    <div
                        style="display:flex;justify-content:space-between;padding:12px 0;border-bottom:1px solid var(--border-color)">
                        <span style="font-size:0.88rem;font-weight:600">Ventas Netas</span>
                        <span style="font-weight:700;color:var(--success)">S/ {{ number_format($totalIngresos, 2) }}</span>
                    </div>
                    <div
                        style="display:flex;justify-content:space-between;padding:12px 0;border-bottom:1px solid var(--border-color)">
                        <span style="font-size:0.88rem;color:var(--text-muted)">(−) Costo de Ventas</span>
                        <span style="color:var(--warning)">(S/ {{ number_format($totalCostos, 2) }})</span>
                    </div>
                    <div
                        style="display:flex;justify-content:space-between;padding:12px 0;border-bottom:2px solid var(--border-color);background:rgba(14,165,233,0.04)">
                        <span style="font-size:0.9rem;font-weight:700">= Utilidad Bruta</span>
                        <span style="font-weight:800;color:{{ $utilidadBruta >= 0 ? 'var(--primary)' : 'var(--danger)' }}">
                            {{ $utilidadBruta < 0 ? '-' : '' }}S/ {{ number_format(abs($utilidadBruta), 2) }}
                        </span>
                    </div>
                    <div
                        style="display:flex;justify-content:space-between;padding:12px 0;border-bottom:1px solid var(--border-color)">
                        <span style="font-size:0.88rem;color:var(--text-muted)">(−) Gastos Operativos</span>
                        <span style="color:var(--danger)">(S/ {{ number_format($totalGastos, 2) }})</span>
                    </div>
                    <div
                        style="padding:16px 0;background:{{ $utilidadOperativa >= 0 ? 'rgba(34,197,94,0.06)' : 'rgba(239,68,68,0.06)' }};border-top:3px solid {{ $utilidadOperativa >= 0 ? 'var(--success)' : 'var(--danger)' }};border-radius:0 0 8px 8px;margin-top:8px;text-align:center">
                        <div
                            style="font-size:0.72rem;text-transform:uppercase;letter-spacing:0.08em;color:var(--text-muted);margin-bottom:6px">
                            UTILIDAD NETA DEL PERÍODO</div>
                        <div
                            style="font-size:2rem;font-weight:900;color:{{ $utilidadOperativa >= 0 ? 'var(--success)' : 'var(--danger)' }}">
                            {{ $utilidadOperativa < 0 ? '-' : '' }}S/ {{ number_format(abs($utilidadOperativa), 2) }}
                        </div>
                        <div style="font-size:0.82rem;color:var(--text-muted);margin-top:8px">
                            Margen operativo: <strong>{{ $margenOperativo }}%</strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>