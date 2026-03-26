<div>
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:16px;margin-bottom:24px">
        <div class="card" style="padding:20px">
            <div style="display:flex;align-items:center;gap:12px">
                <div
                    style="width:44px;height:44px;border-radius:10px;background:rgba(245,158,11,0.15);display:flex;align-items:center;justify-content:center">
                    <i data-lucide="shield-plus" style="width:22px;height:22px;color:#f59e0b"></i>
                </div>
                <div>
                    <p style="font-size:0.75rem;color:var(--text-muted);margin:0">MONTO PERCIBIDO</p>
                    <p style="font-size:1.4rem;font-weight:800;margin:0;color:#f59e0b">S/
                        {{ number_format($totalPercibido, 2) }}
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
                    <p style="font-size:0.75rem;color:var(--text-muted);margin:0">PERCEPCIONES EMITIDAS</p>
                    <p style="font-size:1.4rem;font-weight:800;margin:0">{{ $cantEmitidas }}</p>
                </div>
            </div>
        </div>
    </div>
    <div
        style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;flex-wrap:wrap;gap:12px">
        <div style="display:flex;gap:10px;align-items:center">
            <div class="search-global" style="width:280px"><i data-lucide="search"></i><input type="text"
                    wire:model.live.debounce.300ms="buscar" placeholder="Buscar serie, cliente..."></div>
            <select wire:model.live="filtroEstado" class="form-select" style="width:160px">
                <option value="">Todos</option>
                <option value="borrador">Borrador</option>
                <option value="emitido">Emitido</option>
                <option value="aceptado">Aceptado</option>
                <option value="anulado">Anulado</option>
            </select>
        </div>
        <div
            style="display:flex;align-items:center;gap:8px;padding:8px 14px;background:rgba(245,158,11,.1);border:1px solid rgba(245,158,11,.3);border-radius:8px;color:#fbbf24;font-size:.82rem;">
            <i data-lucide="info" style="width:15px;height:15px;flex-shrink:0;"></i>
            Las percepciones se generan desde las ventas a clientes agente de percepción
        </div>
    </div>
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Serie</th>
                    <th>Correlativo</th>
                    <th>Cliente</th>
                    <th>RUC</th>
                    <th>Fecha</th>
                    <th style="text-align:right">Base</th>
                    <th style="text-align:right">Tasa</th>
                    <th style="text-align:right">Percepción</th>
                    <th style="text-align:center">Estado</th>
                </tr>
            </thead>
            <tbody>@forelse($percepciones as $p)
                <tr>
                    <td><strong style="font-family:monospace;color:var(--accent)">{{ $p->serie }}</strong></td>
                    <td>{{ $p->correlativo }}</td>
                    <td>{{ \Str::limit($p->cliente ?? '—', 30) }}</td>
                    <td style="color:var(--text-muted)">{{ $p->ruc }}</td>
                    <td style="color:var(--text-muted)">{{ $p->fecha_emision }}</td>
                    <td style="text-align:right">S/ {{ number_format($p->base_percepcion, 2) }}</td>
                    <td style="text-align:right;color:var(--text-muted)">{{ $p->tasa_percepcion }}%</td>
                    <td style="text-align:right;font-weight:700;color:#f59e0b">S/
                        {{ number_format($p->monto_percepcion, 2) }}
                    </td>
                    <td style="text-align:center"><span
                            class="badge {{ match ($p->estado) { 'aceptado' => 'badge-success', 'rechazado' => 'badge-danger', 'anulado' => 'badge-danger', 'emitido' => 'badge-warning', default => 'badge-neutral'} }}"
                            style="font-size:0.65rem">{{ strtoupper($p->estado) }}</span></td>
                </tr>
            @empty<tr>
                    <td colspan="9" style="text-align:center;padding:60px;color:var(--text-muted)"><i
                            data-lucide="shield-plus"
                            style="width:48px;height:48px;display:block;margin:0 auto 12px"></i>No hay percepciones
                        registradas</td>
                </tr>@endforelse
            </tbody>
        </table>
    </div>
    <div style="margin-top:16px">{{ $percepciones->links() }}</div>
</div>