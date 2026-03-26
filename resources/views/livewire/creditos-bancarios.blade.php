<div>
    <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(260px, 1fr));gap:16px;margin-bottom:24px">
        <div class="card" style="padding:20px">
            <div style="display:flex;align-items:center;gap:12px">
                <div
                    style="width:44px;height:44px;border-radius:10px;background:rgba(59,130,246,0.15);display:flex;align-items:center;justify-content:center">
                    <i data-lucide="landmark" style="width:22px;height:22px;color:#3b82f6"></i></div>
                <div>
                    <p style="font-size:0.75rem;color:var(--text-muted);margin:0">DEUDA VIGENTE</p>
                    <p style="font-size:1.5rem;font-weight:800;margin:0;color:#3b82f6">S/
                        {{ number_format($totalVigente, 2) }}</p>
                </div>
            </div>
        </div>
        <div class="card" style="padding:20px">
            <div style="display:flex;align-items:center;gap:12px">
                <div
                    style="width:44px;height:44px;border-radius:10px;background:rgba(245,158,11,0.15);display:flex;align-items:center;justify-content:center">
                    <i data-lucide="credit-card" style="width:22px;height:22px;color:#f59e0b"></i></div>
                <div>
                    <p style="font-size:0.75rem;color:var(--text-muted);margin:0">CRÉDITOS VIGENTES</p>
                    <p style="font-size:1.5rem;font-weight:800;margin:0;color:#f59e0b">{{ $cantVigentes }}</p>
                </div>
            </div>
        </div>
    </div>

    <div
        style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;flex-wrap:wrap;gap:12px">
        <div style="display:flex;gap:10px;align-items:center">
            <div class="search-global" style="width:260px"><i data-lucide="search"></i>
                <input type="text" wire:model.live.debounce.300ms="buscar" placeholder="Buscar entidad, número...">
            </div>
            <select wire:model.live="filtroEstado" class="form-select" style="width:160px">
                <option value="">Todos</option>
                <option value="vigente">Vigente</option>
                <option value="pagado">Pagado</option>
                <option value="vencido">Vencido</option>
            </select>
        </div>
        <button wire:click="openModal" class="btn btn-primary"><i data-lucide="plus"></i> Nuevo Crédito</button>
    </div>

    @if(session()->has('success'))
        <div class="alert alert-success"><i data-lucide="check-circle-2"></i><span>{{ session('success') }}</span></div>
    @endif

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Entidad</th>
                    <th>N° Crédito</th>
                    <th>Desembolso</th>
                    <th>Vencimiento</th>
                    <th style="text-align:right">Monto</th>
                    <th style="text-align:right">Tasa</th>
                    <th style="text-align:center">Cuotas</th>
                    <th style="text-align:right">Cuota</th>
                    <th style="text-align:right">Pagado</th>
                    <th style="text-align:right">Saldo</th>
                    <th style="text-align:center">Estado</th>
                    <th style="text-align:center">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($creditos as $c)
                    <tr>
                        <td><strong>{{ $c->entidad_bancaria }}</strong></td>
                        <td style="font-family:monospace;color:var(--accent)">{{ $c->numero_credito }}</td>
                        <td style="color:var(--text-muted)">{{ $c->fecha_desembolso?->format('d/m/Y') }}</td>
                        <td style="color:var(--text-muted)">{{ $c->fecha_vencimiento?->format('d/m/Y') ?? '—' }}</td>
                        <td style="text-align:right;font-weight:700">S/ {{ number_format($c->monto_credito, 2) }}</td>
                        <td style="text-align:right;color:var(--text-muted)">{{ $c->tasa_interes }}%</td>
                        <td style="text-align:center">{{ $c->cuotas }}</td>
                        <td style="text-align:right;color:var(--text-muted)">S/ {{ number_format($c->cuota_mensual, 2) }}
                        </td>
                        <td style="text-align:right;color:var(--success)">S/ {{ number_format($c->monto_pagado, 2) }}</td>
                        <td style="text-align:right;font-weight:700;color:#ef4444">S/
                            {{ number_format($c->saldo_pendiente, 2) }}</td>
                        <td style="text-align:center"><span class="badge {{ $c->estado_badge }}"
                                style="font-size:0.65rem">{{ $c->estado_texto }}</span></td>
                        <td style="text-align:center;white-space:nowrap">
                            <button wire:click="openModal({{ $c->id }})" title="Editar"
                                style="background:none;border:none;color:var(--accent);cursor:pointer"><i
                                    data-lucide="pencil" style="width:16px;height:16px"></i></button>
                            @if($c->estado === 'vigente')<button wire:click="cambiarEstado({{ $c->id }},'pagado')"
                                title="Marcar pagado"
                                style="background:none;border:none;color:var(--success);cursor:pointer"><i
                            data-lucide="check-circle" style="width:16px;height:16px"></i></button>@endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="12" style="text-align:center;padding:60px;color:var(--text-muted)"><i
                                data-lucide="landmark"
                                style="width:48px;height:48px;display:block;margin:0 auto 12px"></i>No hay créditos
                            bancarios registrados</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div style="margin-top:16px">{{ $creditos->links() }}</div>

    @if($showModal)
        <div class="modal-backdrop" wire:click.self="$set('showModal', false)">
            <div class="modal" style="max-width:600px">
                <div class="modal-header">
                    <h3 style="font-weight:700">{{ $editingId ? 'Editar' : 'Nuevo' }} Crédito Bancario</h3>
                    <button wire:click="$set('showModal', false)"
                        style="background:none;border:none;color:var(--text-muted);cursor:pointer"><i data-lucide="x"
                            style="width:20px;height:20px"></i></button>
                </div>
                <div class="modal-body">
                    <div class="form-row">
                        <div class="form-group"><label class="form-label">Entidad Bancaria *</label><input type="text"
                                wire:model="entidad_bancaria" class="form-input"
                                placeholder="BCP, BBVA, Interbank...">@error('entidad_bancaria')<span
                                style="color:var(--danger);font-size:0.75rem">{{ $message }}</span>@enderror</div>
                        <div class="form-group"><label class="form-label">N° Crédito *</label><input type="text"
                                wire:model="numero_credito" class="form-input">@error('numero_credito')<span
                                style="color:var(--danger);font-size:0.75rem">{{ $message }}</span>@enderror</div>
                    </div>
                    <div class="form-row">
                        <div class="form-group"><label class="form-label">Fecha Desembolso *</label><input type="date"
                                wire:model="fecha_desembolso" class="form-input"></div>
                        <div class="form-group"><label class="form-label">Fecha Vencimiento</label><input type="date"
                                wire:model="fecha_vencimiento" class="form-input"></div>
                    </div>
                    <div class="form-row">
                        <div class="form-group"><label class="form-label">Monto Crédito (S/) *</label><input type="number"
                                wire:model.live="monto_credito" class="form-input" step="0.01">@error('monto_credito')<span
                                style="color:var(--danger);font-size:0.75rem">{{ $message }}</span>@enderror</div>
                        <div class="form-group"><label class="form-label">Tasa Interés (%)</label><input type="number"
                                wire:model.live="tasa_interes" class="form-input" step="0.01"></div>
                    </div>
                    <div class="form-row">
                        <div class="form-group"><label class="form-label">N° Cuotas *</label><input type="number"
                                wire:model.live="cuotas" class="form-input" min="1"></div>
                        <div class="form-group"><label class="form-label">Cuota Mensual</label><input type="number"
                                wire:model="cuota_mensual" class="form-input" readonly
                                style="opacity:0.7;font-weight:700;color:var(--accent)"></div>
                    </div>
                    <div class="form-group"><label class="form-label">Destino del Crédito</label><input type="text"
                            wire:model="destino" class="form-input"
                            placeholder="Compra de maquinaria, capital de trabajo..."></div>
                    <div class="form-group"><label class="form-label">Observaciones</label><textarea
                            wire:model="observaciones" class="form-input" rows="2"></textarea></div>
                </div>
                <div class="modal-footer">
                    <button wire:click="$set('showModal', false)" class="btn btn-secondary">Cancelar</button>
                    <button wire:click="save" class="btn btn-primary"><i data-lucide="save"></i>
                        {{ $editingId ? 'Actualizar' : 'Registrar' }}</button>
                </div>
            </div>
        </div>
    @endif
</div>