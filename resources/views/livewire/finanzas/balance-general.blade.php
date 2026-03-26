<div>
    {{-- Controles --}}
    <div style="display:flex;gap:12px;margin-bottom:24px;flex-wrap:wrap;align-items:center">
        <div class="form-group" style="margin:0;display:flex;align-items:center;gap:8px">
            <label class="form-label" style="margin:0;white-space:nowrap;color:var(--text-muted)">Período hasta:</label>
            <input type="month" wire:model.live="periodo" class="form-input" style="width:160px">
        </div>
        @if(!$cuadrado && $totalActivo > 0)
            <div
                style="background:rgba(239,68,68,0.1);border:1px solid rgba(239,68,68,0.3);border-radius:8px;padding:8px 14px;font-size:0.82rem;color:#fca5a5;display:flex;align-items:center;gap:8px">
                <i data-lucide="alert-triangle" style="width:16px;height:16px"></i>
                El balance no cuadra — revisa los asientos
            </div>
        @elseif($cuadrado && $totalActivo > 0)
            <div
                style="background:rgba(34,197,94,0.1);border:1px solid rgba(34,197,94,0.3);border-radius:8px;padding:8px 14px;font-size:0.82rem;color:#86efac;display:flex;align-items:center;gap:8px">
                <i data-lucide="check-circle-2" style="width:16px;height:16px"></i>
                Activo = Pasivo + Patrimonio ✓
            </div>
        @endif
    </div>

    @if($totalActivo == 0 && $totalPasivo == 0)
        <div class="card" style="text-align:center;padding:60px">
            <i data-lucide="scale"
                style="width:48px;height:48px;margin:0 auto 12px;display:block;color:var(--text-muted)"></i>
            <p style="color:var(--text-muted)">Sin datos contables para este período.<br>Registra asientos validados para
                ver el Balance.</p>
        </div>
    @else

        {{-- Resumen KPIs --}}
        <div style="display:grid;grid-template-columns:1fr 1fr 1fr 1fr;gap:16px;margin-bottom:28px">
            <div class="card" style="padding:20px;border-top:3px solid var(--accent)">
                <div
                    style="font-size:0.75rem;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.05em;margin-bottom:8px">
                    TOTAL ACTIVO</div>
                <div style="font-size:1.4rem;font-weight:800;color:var(--accent)">S/ {{ number_format($totalActivo, 2) }}
                </div>
            </div>
            <div class="card" style="padding:20px;border-top:3px solid var(--danger)">
                <div
                    style="font-size:0.75rem;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.05em;margin-bottom:8px">
                    TOTAL PASIVO</div>
                <div style="font-size:1.4rem;font-weight:800;color:var(--danger)">S/ {{ number_format($totalPasivo, 2) }}
                </div>
            </div>
            <div class="card" style="padding:20px;border-top:3px solid var(--warning)">
                <div
                    style="font-size:0.75rem;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.05em;margin-bottom:8px">
                    PATRIMONIO</div>
                <div style="font-size:1.4rem;font-weight:800;color:var(--warning)">S/
                    {{ number_format($totalPatrimonio, 2) }}</div>
            </div>
            <div class="card"
                style="padding:20px;border-top:3px solid {{ $resultadoEjercicio >= 0 ? 'var(--success)' : 'var(--danger)' }}">
                <div
                    style="font-size:0.75rem;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.05em;margin-bottom:8px">
                    UTILIDAD EJERCICIO</div>
                <div
                    style="font-size:1.4rem;font-weight:800;color:{{ $resultadoEjercicio >= 0 ? 'var(--success)' : 'var(--danger)' }}">
                    {{ $resultadoEjercicio >= 0 ? '' : '-' }}S/ {{ number_format(abs($resultadoEjercicio), 2) }}
                </div>
            </div>
        </div>

        {{-- Balance en 2 columnas --}}
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">

            {{-- ACTIVO --}}
            <div class="card" style="padding:0;overflow:hidden">
                <div
                    style="background:linear-gradient(135deg,rgba(14,165,233,0.15),rgba(14,165,233,0.05));padding:16px 20px;border-bottom:1px solid var(--border-color)">
                    <h3
                        style="font-size:0.9rem;font-weight:700;color:var(--accent);display:flex;align-items:center;gap:8px">
                        <i data-lucide="trending-up" style="width:18px;height:18px"></i> ACTIVO
                    </h3>
                </div>
                <table style="width:100%;border-collapse:collapse">
                    <thead>
                        <tr style="border-bottom:1px solid var(--border-color)">
                            <th style="text-align:left;padding:8px 16px;font-size:0.75rem;color:var(--text-muted)">Código
                            </th>
                            <th style="text-align:left;padding:8px 16px;font-size:0.75rem;color:var(--text-muted)">Cuenta
                            </th>
                            <th style="text-align:right;padding:8px 16px;font-size:0.75rem;color:var(--text-muted)">Saldo
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($activos as $cuenta)
                            <tr style="border-bottom:1px solid var(--border-color)20">
                                <td style="padding:8px 16px;font-size:0.78rem;font-family:monospace;color:var(--accent)">
                                    {{ $cuenta->codigo }}</td>
                                <td style="padding:8px 16px;font-size:0.82rem">{{ $cuenta->nombre }}</td>
                                <td style="padding:8px 16px;text-align:right;font-size:0.85rem;font-weight:600">S/
                                    {{ number_format($cuenta->saldo, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr style="background:rgba(14,165,233,0.08);border-top:2px solid var(--accent)40">
                            <td colspan="2" style="padding:12px 16px;font-weight:700;font-size:0.9rem">TOTAL ACTIVO</td>
                            <td
                                style="padding:12px 16px;text-align:right;font-weight:800;font-size:1rem;color:var(--accent)">
                                S/ {{ number_format($totalActivo, 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            {{-- PASIVO + PATRIMONIO + RESULTADO --}}
            <div style="display:flex;flex-direction:column;gap:16px">

                {{-- Pasivo --}}
                <div class="card" style="padding:0;overflow:hidden">
                    <div
                        style="background:linear-gradient(135deg,rgba(239,68,68,0.12),rgba(239,68,68,0.04));padding:12px 20px;border-bottom:1px solid var(--border-color)">
                        <h3
                            style="font-size:0.88rem;font-weight:700;color:var(--danger);display:flex;align-items:center;gap:8px">
                            <i data-lucide="trending-down" style="width:16px;height:16px"></i> PASIVO
                        </h3>
                    </div>
                    <table style="width:100%;border-collapse:collapse">
                        <tbody>
                            @forelse($pasivos as $cuenta)
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
                                        pasivos registrados</td>
                                </tr>
                            @endforelse
                        </tbody>
                        <tfoot>
                            <tr style="background:rgba(239,68,68,0.06);border-top:2px solid rgba(239,68,68,0.3)">
                                <td colspan="2" style="padding:10px 16px;font-weight:700">TOTAL PASIVO</td>
                                <td style="padding:10px 16px;text-align:right;font-weight:800;color:var(--danger)">S/
                                    {{ number_format($totalPasivo, 2) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                {{-- Patrimonio --}}
                <div class="card" style="padding:0;overflow:hidden">
                    <div
                        style="background:linear-gradient(135deg,rgba(245,158,11,0.12),rgba(245,158,11,0.04));padding:12px 20px;border-bottom:1px solid var(--border-color)">
                        <h3
                            style="font-size:0.88rem;font-weight:700;color:var(--warning);display:flex;align-items:center;gap:8px">
                            <i data-lucide="landmark" style="width:16px;height:16px"></i> PATRIMONIO
                        </h3>
                    </div>
                    <table style="width:100%;border-collapse:collapse">
                        <tbody>
                            @forelse($patrimonio as $cuenta)
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
                                        patrimonio registrado</td>
                                </tr>
                            @endforelse
                            {{-- Resultado del ejercicio --}}
                            <tr
                                style="background:{{ $resultadoEjercicio >= 0 ? 'rgba(34,197,94,0.06)' : 'rgba(239,68,68,0.06)' }};border-top:1px dashed var(--border-color)">
                                <td
                                    style="padding:10px 16px;font-size:0.78rem;font-family:monospace;color:{{ $resultadoEjercicio >= 0 ? 'var(--success)' : 'var(--danger)' }}">
                                    RES.</td>
                                <td style="padding:10px 16px;font-size:0.82rem;font-weight:600">Resultado del Ejercicio</td>
                                <td
                                    style="padding:10px 16px;text-align:right;font-weight:700;color:{{ $resultadoEjercicio >= 0 ? 'var(--success)' : 'var(--danger)' }}">
                                    {{ $resultadoEjercicio >= 0 ? '' : '-' }}S/
                                    {{ number_format(abs($resultadoEjercicio), 2) }}
                                </td>
                            </tr>
                        </tbody>
                        <tfoot>
                            <tr style="border-top:2px solid rgba(245,158,11,0.3);background:rgba(245,158,11,0.06)">
                                <td colspan="2" style="padding:10px 16px;font-weight:700">TOTAL PATRIMONIO + RES.</td>
                                <td style="padding:10px 16px;text-align:right;font-weight:800;color:var(--warning)">S/
                                    {{ number_format($totalPatrimonio + $resultadoEjercicio, 2) }}</td>
                            </tr>
                            <tr style="background:var(--bg-elevated);border-top:3px solid var(--border-color)">
                                <td colspan="2" style="padding:12px 16px;font-weight:800;font-size:0.9rem">TOTAL PASIVO +
                                    PATRIMONIO</td>
                                <td
                                    style="padding:12px 16px;text-align:right;font-weight:900;font-size:1rem;color:{{ $cuadrado ? 'var(--success)' : 'var(--danger)' }}">
                                    S/ {{ number_format($totalPasivo + $totalPatrimonio + $resultadoEjercicio, 2) }}
                                    @if($cuadrado) <i data-lucide="check-circle-2"
                                    style="width:16px;height:16px;display:inline;vertical-align:middle"></i> @endif
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    @endif
</div>