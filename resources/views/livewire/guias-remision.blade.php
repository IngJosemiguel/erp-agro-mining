<div>
    <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:16px;margin-bottom:24px">
        <div class="card" style="padding:20px">
            <div style="display:flex;align-items:center;gap:12px">
                <div
                    style="width:44px;height:44px;border-radius:10px;background:rgba(0,255,136,0.15);display:flex;align-items:center;justify-content:center">
                    <i data-lucide="truck" style="width:22px;height:22px;color:var(--accent)"></i>
                </div>
                <div>
                    <p style="font-size:0.75rem;color:var(--text-muted);margin:0">TOTAL GUÍAS</p>
                    <p style="font-size:1.4rem;font-weight:800;margin:0">{{ $totalGuias }}</p>
                </div>
            </div>
        </div>
        <div class="card" style="padding:20px">
            <div style="display:flex;align-items:center;gap:12px">
                <div
                    style="width:44px;height:44px;border-radius:10px;background:rgba(0,255,136,0.2);display:flex;align-items:center;justify-content:center">
                    <i data-lucide="check-circle-2" style="width:22px;height:22px;color:var(--accent)"></i>
                </div>
                <div>
                    <p style="font-size:0.75rem;color:var(--text-muted);margin:0">ACEPTADAS SUNAT</p>
                    <p style="font-size:1.4rem;font-weight:800;margin:0;color:var(--accent)">{{ $aceptadas }}</p>
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
                    <p style="font-size:0.75rem;color:var(--text-muted);margin:0">PENDIENTES</p>
                    <p style="font-size:1.4rem;font-weight:800;margin:0;color:#f59e0b">{{ $pendientes }}</p>
                </div>
            </div>
        </div>
    </div>
    <div
        style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;flex-wrap:wrap;gap:12px">
        <div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap">
            <div class="search-global" style="width:260px"><i data-lucide="search"></i><input type="text"
                    wire:model.live.debounce.300ms="buscar" placeholder="Serie, correlativo, destinatario..."></div>
            <select wire:model.live="filtroEstado" class="form-select" style="width:160px">
                <option value="">Todos los estados</option>
                <option value="borrador">Borrador</option>
                <option value="emitida">Emitida</option>
                <option value="enviada_sunat">Enviada SUNAT</option>
                <option value="aceptada">Aceptada</option>
                <option value="rechazada">Rechazada</option>
                <option value="anulada">Anulada</option>
            </select>
            <select wire:model.live="filtroTipo" class="form-select" style="width:160px">
                <option value="">Todos los tipos</option>
                <option value="remitente">Remitente</option>
                <option value="transportista">Transportista</option>
            </select>
        </div>
        <div
            style="display:flex;align-items:center;gap:8px;padding:8px 14px;background:rgba(0,255,136,.07);border:1px solid rgba(0,255,136,.25);border-radius:8px;color:var(--accent);font-size:.82rem;">
            <i data-lucide="info" style="width:15px;height:15px;flex-shrink:0;"></i>
            Las guías se generan al despachar una venta o transferencia de almacén
        </div>
    </div>
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Serie</th>
                    <th>Correlativo</th>
                    <th>Tipo</th>
                    <th>Destinatario</th>
                    <th>Fecha Emisión</th>
                    <th>Fecha Traslado</th>
                    <th>Motivo</th>
                    <th style="text-align:center">Estado</th>
                </tr>
            </thead>
            <tbody>@forelse($guias as $g)
                <tr>
                    <td><strong style="font-family:monospace;color:var(--accent)">{{ $g->serie }}</strong></td>
                    <td>{{ $g->correlativo }}</td>
                    <td style="color:var(--text-muted);font-size:0.8rem">{{ ucfirst($g->tipo_guia) }}</td>
                    <td><strong>{{ \Str::limit($g->destinatario_razon_social ?? '—', 28) }}</strong><br><span
                            style="font-size:0.7rem;color:var(--text-muted)">{{ $g->destinatario_numero_documento }}</span>
                    </td>
                    <td style="color:var(--text-muted)">{{ $g->fecha_emision }}</td>
                    <td style="color:var(--text-muted)">{{ $g->fecha_traslado }}</td>
                    <td style="color:var(--text-muted);font-size:0.8rem">
                        {{ \Str::limit($g->descripcion_motivo ?? '—', 25) }}
                    </td>
                    <td style="text-align:center"><span
                            class="badge {{ match ($g->estado) { 'aceptada' => 'badge-success', 'rechazada' => 'badge-danger', 'anulada' => 'badge-danger', 'enviada_sunat' => 'badge-warning', 'emitida' => 'badge-warning', default => 'badge-neutral'} }}"
                            style="font-size:0.65rem">{{ strtoupper($g->estado) }}</span></td>
                </tr>
            @empty<tr>
                    <td colspan="8" style="text-align:center;padding:60px;color:var(--text-muted)"><i
                            data-lucide="truck" style="width:48px;height:48px;display:block;margin:0 auto 12px"></i>No
                        hay guías de remisión registradas</td>
                </tr>@endforelse
            </tbody>
        </table>
    </div>
    <div style="margin-top:16px">{{ $guias->links() }}</div>
</div>