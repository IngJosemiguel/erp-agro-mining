<div>
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:16px;margin-bottom:24px">
        <div class="card" style="padding:20px">
            <div style="display:flex;align-items:center;gap:12px">
                <div
                    style="width:44px;height:44px;border-radius:10px;background:rgba(99,102,241,0.15);display:flex;align-items:center;justify-content:center">
                    <i data-lucide="file-minus-2" style="width:22px;height:22px;color:#6366f1"></i>
                </div>
                <div>
                    <p style="font-size:0.75rem;color:var(--text-muted);margin:0">NOTAS DE CRÉDITO</p>
                    <p style="font-size:1.5rem;font-weight:800;margin:0;color:#6366f1">{{ $kpiNC }}</p>
                </div>
            </div>
        </div>
        <div class="card" style="padding:20px">
            <div style="display:flex;align-items:center;gap:12px">
                <div
                    style="width:44px;height:44px;border-radius:10px;background:rgba(245,158,11,0.15);display:flex;align-items:center;justify-content:center">
                    <i data-lucide="file-plus-2" style="width:22px;height:22px;color:#f59e0b"></i>
                </div>
                <div>
                    <p style="font-size:0.75rem;color:var(--text-muted);margin:0">NOTAS DE DÉBITO</p>
                    <p style="font-size:1.5rem;font-weight:800;margin:0;color:#f59e0b">{{ $kpiND }}</p>
                </div>
            </div>
        </div>
    </div>
    <div
        style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;flex-wrap:wrap;gap:12px">
        <div style="display:flex;gap:10px;align-items:center">
            <div class="search-global" style="width:260px"><i data-lucide="search"></i><input type="text"
                    wire:model.live.debounce.300ms="buscar" placeholder="Buscar serie, cliente..."></div>
            <select wire:model.live="filtroEstado" class="form-select" style="width:150px">
                <option value="">Todos</option>
                <option value="emitido">Emitido</option>
                <option value="anulado">Anulado</option>
            </select>
        </div>
        <div
            style="display:flex;align-items:center;gap:8px;padding:8px 14px;background:rgba(99,102,241,.1);border:1px solid rgba(99,102,241,.3);border-radius:8px;color:#a5b4fc;font-size:.82rem;">
            <i data-lucide="info" style="width:15px;height:15px;flex-shrink:0;"></i>
            Las NC/ND se generan desde el listado de comprobantes de venta
        </div>
    </div>
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Serie</th>
                    <th>Correlativo</th>
                    <th>Tipo</th>
                    <th>Cliente</th>
                    <th>Fecha</th>
                    <th style="text-align:right">Total</th>
                    <th style="text-align:center">Est. SUNAT</th>
                </tr>
            </thead>
            <tbody>@forelse($notas as $n)
                        <tr>
                            <td><strong style="font-family:monospace;color:var(--accent)">{{ $n->serie }}</strong></td>
                            <td>{{ $n->correlativo }}</td>
                            <td style="color:var(--text-muted)">{{ $n->tipo_doc ?? '—' }}</td>
                            <td>{{ \Str::limit($n->cliente_nombre, 30) }}</td>
                            <td style="color:var(--text-muted)">{{ $n->fecha_emision }}</td>
                            <td style="text-align:right;font-weight:700">S/ {{ number_format($n->total, 2) }}</td>
                            <td style="text-align:center">
                                <span
                                    class="badge {{ match ($n->estado_sunat ?? '') {
                'aceptado' => 'badge-success', 'rechazado' => 'badge-danger', 'pendiente' => 'badge-warning', default => 'badge-neutral'} }}"
                                    style="font-size:0.65rem">{{ strtoupper($n->estado_sunat ?? 'PENDIENTE') }}</span>
                            </td>
                        </tr>
            @empty<tr>
                    <td colspan="7" style="text-align:center;padding:60px;color:var(--text-muted)"><i
                            data-lucide="file-minus-2"
                            style="width:48px;height:48px;display:block;margin:0 auto 12px"></i>No hay notas registradas
                    </td>
                </tr>@endforelse
            </tbody>
        </table>
    </div>
    <div style="margin-top:16px">{{ $notas->links() }}</div>
</div>