<div>

    {{-- ═══ MODAL NUEVA COTIZACIÓN ═══ --}}
    @if($showModal)
        <div style="position:fixed;inset:0;background:rgba(0,0,0,.7);backdrop-filter:blur(4px);z-index:9999;display:flex;align-items:center;justify-content:center;padding:20px;"
            wire:click.self="$set('showModal',false)">
            <div
                style="background:var(--bg-card);border:1px solid var(--border-color);border-radius:20px;padding:32px;width:100%;max-width:520px;position:relative;">
                <h2 style="font-size:1.15rem;font-weight:800;margin:0 0 20px;display:flex;align-items:center;gap:10px;">
                    <i data-lucide="send" style="width:18px;height:18px;color:var(--accent)"></i>
                    Nueva Solicitud de Cotización
                </h2>

                <div class="form-group" style="margin-bottom:14px;">
                    <label class="form-label">Proveedor *</label>
                    <select wire:model="proveedor_id" class="form-select">
                        <option value="">— Seleccionar proveedor —</option>
                        @foreach($proveedores as $p)
                            <option value="{{ $p->id }}">{{ $p->razon_social }} ({{ $p->numero_documento }})</option>
                        @endforeach
                    </select>
                    @error('proveedor_id')<span style="color:#ef4444;font-size:.75rem;">{{ $message }}</span>@enderror
                </div>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:14px;">
                    <div class="form-group">
                        <label class="form-label">Fecha emisión *</label>
                        <input wire:model="fecha" type="date" class="form-input">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Válida hasta</label>
                        <input wire:model="fecha_validez" type="date" class="form-input">
                    </div>
                </div>

                <div class="form-group" style="margin-bottom:20px;">
                    <label class="form-label">Condiciones / Descripción</label>
                    <textarea wire:model="condiciones" class="form-input" rows="3"
                        placeholder="Ej: Solicitud de precios para agroquímicos campaña 2026..."></textarea>
                </div>

                <div style="display:flex;gap:10px;justify-content:flex-end;">
                    <button wire:click="$set('showModal',false)" class="btn btn-secondary">Cancelar</button>
                    <button wire:click="guardar" wire:loading.attr="disabled" wire:target="guardar" class="btn btn-primary">
                        <span wire:loading.remove wire:target="guardar">
                            <i data-lucide="send"
                                style="width:14px;height:14px;display:inline;vertical-align:middle;margin-right:4px;"></i>
                            Enviar Solicitud
                        </span>
                        <span wire:loading wire:target="guardar">Guardando...</span>
                    </button>
                </div>

                <button wire:click="$set('showModal',false)"
                    style="position:absolute;top:14px;right:14px;background:none;border:none;color:var(--text-muted);cursor:pointer;width:30px;height:30px;display:flex;align-items:center;justify-content:center;border-radius:6px;">
                    <i data-lucide="x" style="width:18px;height:18px;"></i>
                </button>
            </div>
        </div>
    @endif

    {{-- ═══ KPI CARDS ═══ --}}
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:16px;margin-bottom:24px">
        <div class="card" style="padding:20px">
            <div style="display:flex;align-items:center;gap:12px">
                <div
                    style="width:44px;height:44px;border-radius:10px;background:rgba(0,255,136,0.15);display:flex;align-items:center;justify-content:center">
                    <i data-lucide="file-text" style="width:22px;height:22px;color:var(--accent)"></i>
                </div>
                <div>
                    <p style="font-size:0.75rem;color:var(--text-muted);margin:0">TOTAL SOLICITUDES</p>
                    <p style="font-size:1.4rem;font-weight:800;margin:0">{{ $total }}</p>
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
                    <p style="font-size:0.75rem;color:var(--text-muted);margin:0">VIGENTES</p>
                    <p style="font-size:1.4rem;font-weight:800;margin:0;color:#f59e0b">{{ $pendientes }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- ═══ BARRA DE HERRAMIENTAS ═══ --}}
    <div
        style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;flex-wrap:wrap;gap:12px">
        <div class="search-global" style="width:280px">
            <i data-lucide="search"></i>
            <input type="text" wire:model.live.debounce.300ms="buscar" placeholder="Buscar código, proveedor...">
        </div>
        <button wire:click="openModal" class="btn btn-primary">
            <i data-lucide="plus"
                style="width:16px;height:16px;display:inline;vertical-align:middle;margin-right:4px;"></i>
            Nueva Solicitud
        </button>
    </div>

    @if(session()->has('success'))
        <div class="alert alert-success" style="margin-bottom:16px;">
            <i data-lucide="check-circle-2"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    {{-- ═══ TABLA ═══ --}}
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Código</th>
                    <th>Proveedor</th>
                    <th>Fecha Emisión</th>
                    <th>Válida Hasta</th>
                    <th>Condiciones</th>
                    <th style="text-align:center">Estado</th>
                </tr>
            </thead>
            <tbody>
                @forelse($cotizaciones as $c)
                    <tr>
                        <td><strong style="font-family:monospace;color:var(--accent)">{{ $c->codigo }}</strong></td>
                        <td>{{ \Str::limit($c->proveedor ?? '—', 35) }}</td>
                        <td style="color:var(--text-muted)">{{ $c->fecha ?? '—' }}</td>
                        <td style="color:var(--text-muted)">{{ $c->fecha_entrega_estimada ?? '—' }}</td>
                        <td style="color:var(--text-muted);font-size:.8rem;">{{ \Str::limit($c->condiciones ?? '—', 40) }}
                        </td>
                        <td style="text-align:center">
                            <span class="badge badge-warning" style="font-size:0.65rem">COTIZACIÓN</span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" style="text-align:center;padding:60px;color:var(--text-muted)">
                            <i data-lucide="file-text"
                                style="width:48px;height:48px;display:block;margin:0 auto 12px;opacity:.3"></i>
                            <p style="font-weight:700;margin:0 0 6px;">Sin solicitudes de cotización</p>
                            <span style="font-size:.85rem;">Usa el botón <strong>Nueva Solicitud</strong> para crear la
                                primera</span>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div style="margin-top:16px">{{ $cotizaciones->links() }}</div>
</div>