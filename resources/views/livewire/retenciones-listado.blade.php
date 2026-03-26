<div>
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:16px;margin-bottom:24px">
        <div class="card" style="padding:20px">
            <div style="display:flex;align-items:center;gap:12px">
                <div
                    style="width:44px;height:44px;border-radius:10px;background:rgba(239,68,68,0.15);display:flex;align-items:center;justify-content:center">
                    <i data-lucide="shield-minus" style="width:22px;height:22px;color:#ef4444"></i>
                </div>
                <div>
                    <p style="font-size:0.75rem;color:var(--text-muted);margin:0">MONTO RETENIDO</p>
                    <p style="font-size:1.4rem;font-weight:800;margin:0;color:#ef4444">S/
                        {{ number_format($totalRetenido, 2) }}
                    </p>
                </div>
            </div>
        </div>
        <div class="card" style="padding:20px">
            <div style="display:flex;align-items:center;gap:12px">
                <div
                    style="width:44px;height:44px;border-radius:10px;background:rgba(0,255,136,0.15);display:flex;align-items:center;justify-content:center">
                    <i data-lucide="file-check-2" style="width:22px;height:22px;color:var(--accent)"></i>
                </div>
                <div>
                    <p style="font-size:0.75rem;color:var(--text-muted);margin:0">RETENCIONES EMITIDAS</p>
                    <p style="font-size:1.4rem;font-weight:800;margin:0">{{ $cantEmitidas }}</p>
                </div>
            </div>
        </div>
    </div>
    <div
        style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;flex-wrap:wrap;gap:12px">
        <div style="display:flex;gap:10px;align-items:center">
            <div class="search-global" style="width:280px"><i data-lucide="search"></i><input type="text"
                    wire:model.live.debounce.300ms="buscar" placeholder="Buscar serie, proveedor..."></div>
            <select wire:model.live="filtroEstado" class="form-select" style="width:160px">
                <option value="">Todos</option>
                <option value="borrador">Borrador</option>
                <option value="emitido">Emitido</option>
                <option value="aceptado">Aceptado</option>
                <option value="rechazado">Rechazado</option>
                <option value="anulado">Anulado</option>
            </select>
        </div>
        <div
            style="display:flex;align-items:center;gap:8px;padding:8px 14px;background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.3);border-radius:8px;color:#fca5a5;font-size:.82rem;">
            <i data-lucide="info" style="width:15px;height:15px;flex-shrink:0;"></i>
            Las retenciones se generan al registrar una compra con retención aplicada
        </div>
    </div>
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Serie</th>
                    <th>Correlativo</th>
                    <th>Proveedor</th>
                    <th>RUC</th>
                    <th>Fecha</th>
                    <th style="text-align:right">Base</th>
                    <th style="text-align:right">Tasa</th>
                    <th style="text-align:right">Retención</th>
                    <th style="text-align:center">Estado</th>
                </tr>
            </thead>
            <tbody>@forelse($retenciones as $r)
                <tr>
                    <td><strong style="font-family:monospace;color:var(--accent)">{{ $r->serie }}</strong></td>
                    <td>{{ $r->correlativo }}</td>
                    <td>{{ \Str::limit($r->proveedor ?? '—', 30) }}</td>
                    <td style="color:var(--text-muted)">{{ $r->ruc }}</td>
                    <td style="color:var(--text-muted)">{{ $r->fecha_emision }}</td>
                    <td style="text-align:right">S/ {{ number_format($r->base_retencion, 2) }}</td>
                    <td style="text-align:right;color:var(--text-muted)">{{ $r->tasa_retencion }}%</td>
                    <td style="text-align:right;font-weight:700;color:#ef4444">S/
                        {{ number_format($r->monto_retencion, 2) }}
                    </td>
                    <td style="text-align:center"><span
                            class="badge {{ match ($r->estado) { 'aceptado' => 'badge-success', 'rechazado' => 'badge-danger', 'anulado' => 'badge-danger', 'emitido' => 'badge-warning', default => 'badge-neutral'} }}"
                            style="font-size:0.65rem">{{ strtoupper($r->estado) }}</span></td>
                </tr>
            @empty<tr>
                    <td colspan="9" style="text-align:center;padding:60px;color:var(--text-muted)"><i
                            data-lucide="shield-minus"
                            style="width:48px;height:48px;display:block;margin:0 auto 12px"></i>No hay retenciones
                        registradas</td>
                </tr>@endforelse
            </tbody>
        </table>
    </div>
    <div style="margin-top:16px">{{ $retenciones->links() }}</div>
</div>