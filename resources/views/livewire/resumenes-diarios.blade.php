<div>
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:16px;margin-bottom:24px">
        <div class="card" style="padding:20px">
            <div style="display:flex;align-items:center;gap:12px">
                <div
                    style="width:44px;height:44px;border-radius:10px;background:rgba(0,255,136,0.15);display:flex;align-items:center;justify-content:center">
                    <i data-lucide="calendar-check-2" style="width:22px;height:22px;color:var(--accent)"></i></div>
                <div>
                    <p style="font-size:0.75rem;color:var(--text-muted);margin:0">COMPROBANTES</p>
                    <p style="font-size:1.5rem;font-weight:800;margin:0">
                        {{ $totalesGlobales?->total_comprobantes ?? 0 }}</p>
                </div>
            </div>
        </div>
        <div class="card" style="padding:20px">
            <div style="display:flex;align-items:center;gap:12px">
                <div
                    style="width:44px;height:44px;border-radius:10px;background:rgba(59,130,246,0.15);display:flex;align-items:center;justify-content:center">
                    <i data-lucide="banknote" style="width:22px;height:22px;color:#3b82f6"></i></div>
                <div>
                    <p style="font-size:0.75rem;color:var(--text-muted);margin:0">TOTAL PERÍODO</p>
                    <p style="font-size:1.5rem;font-weight:800;margin:0">S/
                        {{ number_format($totalesGlobales?->suma_total ?? 0, 2) }}</p>
                </div>
            </div>
        </div>
    </div>
    <div style="display:flex;gap:10px;margin-bottom:20px">
        <div class="form-group" style="margin:0"><label class="form-label" style="font-size:0.75rem">Desde</label><input
                type="date" wire:model.live="fechaDesde" class="form-input" style="width:160px"></div>
        <div class="form-group" style="margin:0"><label class="form-label" style="font-size:0.75rem">Hasta</label><input
                type="date" wire:model.live="fechaHasta" class="form-input" style="width:160px"></div>
    </div>
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th style="text-align:center">Comprobantes</th>
                    <th style="text-align:right">Total Ventas</th>
                    <th style="text-align:right">IGV</th>
                    <th style="text-align:center">Estado SUNAT</th>
                </tr>
            </thead>
            <tbody>@forelse($resumenes as $r)
                        <tr>
                            <td><strong>{{ \Carbon\Carbon::parse($r->fecha)->format('d/m/Y') }}</strong><br><span
                                    style="font-size:0.7rem;color:var(--text-muted)">{{ \Carbon\Carbon::parse($r->fecha)->isoFormat('dddd') }}</span>
                            </td>
                            <td style="text-align:center;font-weight:600">{{ $r->cantidad }}</td>
                            <td style="text-align:right;font-weight:700;color:var(--accent)">S/ {{ number_format($r->total, 2) }}
                            </td>
                            <td style="text-align:right;color:var(--text-muted)">S/ {{ number_format($r->total_igv, 2) }}</td>
                            <td style="text-align:center"><span
                                    class="badge {{ match ($r->estado_sunat ?? '') {
                'aceptado' => 'badge-success', 'pendiente' => 'badge-warning', 'rechazado' => 'badge-danger', default => 'badge-neutral'} }}"
                                    style="font-size:0.65rem">{{ strtoupper($r->estado_sunat ?? 'SIN ENVIAR') }}</span></td>
                        </tr>
            @empty<tr>
                    <td colspan="5" style="text-align:center;padding:60px;color:var(--text-muted)"><i
                            data-lucide="calendar-x-2"
                            style="width:48px;height:48px;display:block;margin:0 auto 12px"></i>No hay datos en el
                        período</td>
                </tr>@endforelse
            </tbody>
        </table>
    </div>
    <div style="margin-top:16px">{{ $resumenes->links() }}</div>
</div>