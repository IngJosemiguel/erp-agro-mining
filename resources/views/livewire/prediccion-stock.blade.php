<div>
    {{-- ═══ HEADER ═══ --}}
    <div
        style="display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:28px;flex-wrap:wrap;gap:16px;">
        <div>
            <h1 style="font-size:1.7rem;font-weight:900;margin:0 0 6px;display:flex;align-items:center;gap:12px;">
                <span
                    style="width:42px;height:42px;border-radius:12px;background:linear-gradient(135deg,#6366f1,#a855f7);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <i data-lucide="brain-circuit" style="width:22px;height:22px;color:#fff;"></i>
                </span>
                Predicción de Stock IA
            </h1>
            <p style="color:var(--text-muted);font-size:.88rem;margin:0 0 0 54px;">
                Análisis de consumo histórico → proyección de agotamiento por producto
            </p>
        </div>
        <div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap;">
            <div
                style="display:flex;align-items:center;gap:8px;background:var(--bg-body);border:1px solid var(--border-color);border-radius:8px;padding:8px 14px;">
                <label style="font-size:.8rem;color:var(--text-muted);white-space:nowrap;">Ventana análisis:</label>
                <select wire:model.live="diasAnalisis"
                    style="background:transparent;border:none;color:var(--text-primary);font-weight:700;font-size:.85rem;">
                    <option value="7">7 días</option>
                    <option value="14">14 días</option>
                    <option value="30">30 días</option>
                    <option value="60">60 días</option>
                    <option value="90">90 días</option>
                </select>
            </div>
        </div>
    </div>

    {{-- ═══ KPI CARDS ═══ --}}
    <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-bottom:24px;">
        <div
            style="background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.25);border-radius:14px;padding:18px 20px;display:flex;align-items:center;gap:14px;">
            <div
                style="width:40px;height:40px;background:rgba(239,68,68,.2);border-radius:10px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <i data-lucide="package-x" style="width:20px;height:20px;color:#ef4444;"></i>
            </div>
            <div>
                <div style="font-size:1.6rem;font-weight:900;color:#ef4444;line-height:1;">{{ $resumen['agotados'] }}
                </div>
                <div
                    style="font-size:.73rem;color:#fca5a5;font-weight:600;text-transform:uppercase;letter-spacing:.05em;">
                    Agotados</div>
            </div>
        </div>
        <div
            style="background:rgba(220,38,38,.08);border:1px solid rgba(220,38,38,.2);border-radius:14px;padding:18px 20px;display:flex;align-items:center;gap:14px;">
            <div
                style="width:40px;height:40px;background:rgba(220,38,38,.15);border-radius:10px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <i data-lucide="alert-triangle" style="width:20px;height:20px;color:#dc2626;"></i>
            </div>
            <div>
                <div style="font-size:1.6rem;font-weight:900;color:#dc2626;line-height:1;">{{ $resumen['criticos'] }}
                </div>
                <div
                    style="font-size:.73rem;color:#fca5a5;font-weight:600;text-transform:uppercase;letter-spacing:.05em;">
                    Críticos ≤7 días</div>
            </div>
        </div>
        <div
            style="background:rgba(245,158,11,.08);border:1px solid rgba(245,158,11,.2);border-radius:14px;padding:18px 20px;display:flex;align-items:center;gap:14px;">
            <div
                style="width:40px;height:40px;background:rgba(245,158,11,.15);border-radius:10px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <i data-lucide="clock" style="width:20px;height:20px;color:#f59e0b;"></i>
            </div>
            <div>
                <div style="font-size:1.6rem;font-weight:900;color:#f59e0b;line-height:1;">{{ $resumen['advertencia'] }}
                </div>
                <div
                    style="font-size:.73rem;color:#fcd34d;font-weight:600;text-transform:uppercase;letter-spacing:.05em;">
                    Advertencia ≤15 días</div>
            </div>
        </div>
        <div
            style="background:rgba(99,102,241,.08);border:1px solid rgba(99,102,241,.2);border-radius:14px;padding:18px 20px;display:flex;align-items:center;gap:14px;">
            <div
                style="width:40px;height:40px;background:rgba(99,102,241,.15);border-radius:10px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <i data-lucide="eye" style="width:20px;height:20px;color:#6366f1;"></i>
            </div>
            <div>
                <div style="font-size:1.6rem;font-weight:900;color:#6366f1;line-height:1;">{{ $resumen['atencion'] }}
                </div>
                <div
                    style="font-size:.73rem;color:#a5b4fc;font-weight:600;text-transform:uppercase;letter-spacing:.05em;">
                    Atención ≤30 días</div>
            </div>
        </div>
    </div>

    {{-- ═══ TABLA PREDICCIONES ═══ --}}
    <div class="card" style="overflow:hidden;">
        <div
            style="padding:16px 20px;border-bottom:1px solid var(--border-color);display:flex;align-items:center;justify-content:space-between;">
            <div style="display:flex;align-items:center;gap:8px;">
                <i data-lucide="sparkles" style="width:16px;height:16px;color:#a855f7;"></i>
                <span style="font-weight:700;font-size:.9rem;">Proyección de agotamiento</span>
                <span style="font-size:.75rem;color:var(--text-muted);">— basado en {{ $diasAnalisis }} días de
                    historial de ventas</span>
            </div>
            <div style="display:flex;align-items:center;gap:6px;font-size:.73rem;color:var(--text-muted);">
                <i data-lucide="info" style="width:13px;height:13px;"></i>
                Solo muestra productos con consumo o stock crítico
            </div>
        </div>
        <table style="width:100%;border-collapse:collapse;">
            <thead>
                <tr style="background:rgba(0,0,0,.2);">
                    <th
                        style="padding:10px 16px;text-align:left;font-size:.7rem;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:.05em;">
                        Producto</th>
                    <th
                        style="padding:10px 16px;text-align:right;font-size:.7rem;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:.05em;">
                        Stock Actual</th>
                    <th
                        style="padding:10px 16px;text-align:right;font-size:.7rem;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:.05em;">
                        Consumo/día</th>
                    <th
                        style="padding:10px 16px;text-align:center;font-size:.7rem;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:.05em;">
                        Días restantes</th>
                    <th
                        style="padding:10px 16px;text-align:center;font-size:.7rem;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:.05em;">
                        Fecha estimada agotamiento</th>
                    <th
                        style="padding:10px 16px;text-align:center;font-size:.7rem;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:.05em;">
                        Estado</th>
                </tr>
            </thead>
            <tbody>
                @forelse($predicciones as $p)
                    @php
                        $rowBg = match ($p['urgencia']) {
                            'agotado' => 'rgba(239,68,68,.06)',
                            'critico' => 'rgba(220,38,38,.04)',
                            'advertencia' => 'rgba(245,158,11,.04)',
                            'atencion' => 'rgba(99,102,241,.03)',
                            default => '',
                        };
                        $textColor = match ($p['urgencia']) {
                            'agotado' => '#ef4444',
                            'critico' => '#dc2626',
                            'advertencia' => '#f59e0b',
                            'atencion' => '#6366f1',
                            default => 'var(--accent)',
                        };
                    @endphp
                    <tr style="border-bottom:1px solid var(--border-color);background:{{ $rowBg }}">
                        <td style="padding:12px 16px;">
                            <div style="font-weight:700;font-size:.88rem;color:var(--text-primary);">
                                {{ Str::limit($p['nombre'], 38) }}</div>
                            <div style="font-size:.72rem;color:var(--text-muted);font-family:monospace;">{{ $p['codigo'] }}
                            </div>
                        </td>
                        <td style="padding:12px 16px;text-align:right;">
                            <span
                                style="font-weight:800;color:{{ $p['stock_actual'] <= 0 ? '#ef4444' : ($p['stock_actual'] <= $p['stock_minimo'] ? '#f59e0b' : 'var(--accent)') }};">
                                {{ number_format($p['stock_actual'], 2) }}
                            </span>
                            <span
                                style="font-size:.73rem;color:var(--text-muted);margin-left:2px;">{{ $p['unidad'] }}</span>
                            @if($p['stock_minimo'] > 0)
                                <div style="font-size:.7rem;color:var(--text-muted);">mín:
                                    {{ number_format($p['stock_minimo'], 0) }}</div>
                            @endif
                        </td>
                        <td style="padding:12px 16px;text-align:right;color:var(--text-secondary);font-size:.85rem;">
                            @if($p['consumo_diario'] > 0)
                                {{ number_format($p['consumo_diario'], 2) }} <span
                                    style="color:var(--text-muted);font-size:.73rem;">{{ $p['unidad'] }}/día</span>
                            @else
                                <span style="color:var(--text-muted);font-size:.8rem;">Sin movimiento</span>
                            @endif
                        </td>
                        <td style="padding:12px 16px;text-align:center;">
                            @if($p['urgencia'] === 'agotado')
                                <span style="font-size:1.2rem;font-weight:900;color:#ef4444;">AGOTADO</span>
                            @elseif($p['dias_hasta_agotamiento'] !== null)
                                <div style="display:inline-flex;flex-direction:column;align-items:center;">
                                    <span
                                        style="font-size:1.4rem;font-weight:900;color:{{ $textColor }};line-height:1;">{{ $p['dias_hasta_agotamiento'] }}</span>
                                    <span style="font-size:.7rem;color:var(--text-muted);">días</span>
                                </div>
                                {{-- Barra de progreso visual --}}
                                @php $pct = min(100, max(0, ($p['dias_hasta_agotamiento'] / 30) * 100)); @endphp
                                <div
                                    style="width:70px;height:4px;background:rgba(255,255,255,.1);border-radius:9px;margin:4px auto 0;overflow:hidden;">
                                    <div style="width:{{ $pct }}%;height:100%;background:{{ $textColor }};border-radius:9px;">
                                    </div>
                                </div>
                            @else
                                <span style="color:var(--text-muted);font-size:.8rem;">—</span>
                            @endif
                        </td>
                        <td
                            style="padding:12px 16px;text-align:center;font-size:.83rem;color:{{ $textColor }};font-weight:700;">
                            {{ $p['fecha_agotamiento'] ?? '—' }}
                        </td>
                        <td style="padding:12px 16px;text-align:center;">
                            @php
                                $badge = match ($p['urgencia']) {
                                    'agotado' => ['Agotado', 'background:#ef4444;color:#fff'],
                                    'critico' => ['Crítico', 'background:rgba(220,38,38,.2);color:#dc2626;border:1px solid #dc2626'],
                                    'advertencia' => ['Advertencia', 'background:rgba(245,158,11,.15);color:#f59e0b;border:1px solid rgba(245,158,11,.4)'],
                                    'atencion' => ['Atención', 'background:rgba(99,102,241,.15);color:#818cf8;border:1px solid rgba(99,102,241,.4)'],
                                    default => ['OK', 'background:rgba(0,255,136,.15);color:var(--accent)'],
                                };
                            @endphp
                            <span
                                style="padding:3px 10px;border-radius:20px;font-size:.7rem;font-weight:700;{{ $badge[1] }}">
                                {{ $badge[0] }}
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" style="padding:60px;text-align:center;color:var(--text-muted);">
                            <i data-lucide="shield-check"
                                style="width:48px;height:48px;display:block;margin:0 auto 14px;color:var(--accent);opacity:.5;"></i>
                            <p style="font-weight:700;margin:0 0 6px;font-size:1rem;">¡Todo en orden!</p>
                            <span style="font-size:.85rem;">No hay productos con stock crítico en los próximos
                                {{ $diasAnalisis }} días</span>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- ═══ NOTA METODOLOGÍA ═══ --}}
    <div
        style="margin-top:16px;padding:12px 16px;background:rgba(99,102,241,.06);border:1px solid rgba(99,102,241,.15);border-radius:10px;display:flex;align-items:flex-start;gap:10px;">
        <i data-lucide="lightbulb" style="width:16px;height:16px;color:#818cf8;flex-shrink:0;margin-top:1px;"></i>
        <p style="margin:0;font-size:.8rem;color:var(--text-muted);line-height:1.5;">
            <strong style="color:#a5b4fc;">Metodología:</strong>
            Se calcula el <strong>promedio de salidas diarias</strong> del kardex en los últimos {{ $diasAnalisis }}
            días (solo días con movimiento).
            Dividiendo el stock actual entre ese promedio se obtiene los días estimados hasta el agotamiento.
            Los registros sin historial de salidas no aparecen en esta lista.
        </p>
    </div>
</div>