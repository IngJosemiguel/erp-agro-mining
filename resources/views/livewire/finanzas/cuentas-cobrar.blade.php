<div>
    @if(session('success'))
        <div class="alert alert-success" style="margin-bottom:16px"><i data-lucide="check-circle-2"></i>
            {{ session('success') }}</div>
    @endif

    {{-- Stats --}}
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:16px;margin-bottom:24px">
        <div class="card" style="padding:20px;border-top:3px solid var(--danger)">
            <div
                style="font-size:0.72rem;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.05em;margin-bottom:6px">
                TOTAL POR COBRAR</div>
            <div style="font-size:1.4rem;font-weight:800;color:var(--danger)">S/
                {{ number_format($stats->total_pendiente ?? 0, 2) }}</div>
        </div>
        <div class="card" style="padding:20px;border-top:3px solid var(--warning)">
            <div
                style="font-size:0.72rem;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.05em;margin-bottom:6px">
                FACTURAS PENDIENTES</div>
            <div style="font-size:1.4rem;font-weight:800;color:var(--warning)">{{ $stats->pendientes ?? 0 }}</div>
        </div>
        <div class="card" style="padding:20px;border-top:3px solid var(--danger)">
            <div
                style="font-size:0.72rem;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.05em;margin-bottom:6px">
                FACTURAS VENCIDAS</div>
            <div style="font-size:1.4rem;font-weight:800;color:var(--danger)">{{ $stats->vencidas ?? 0 }}</div>
        </div>
        <div class="card" style="padding:20px;border-top:3px solid var(--success)">
            <div
                style="font-size:0.72rem;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.05em;margin-bottom:6px">
                TOTAL COBRADO</div>
            <div style="font-size:1.4rem;font-weight:800;color:var(--success)">S/
                {{ number_format($stats->total_cobrado ?? 0, 2) }}</div>
        </div>
    </div>

    {{-- Filtros --}}
    <div style="display:flex;gap:12px;margin-bottom:16px;flex-wrap:wrap;align-items:center">
        <div class="search-global" style="width:260px">
            <i data-lucide="search"></i>
            <input type="text" wire:model.live.debounce.300ms="buscar" placeholder="Cliente, serie o número...">
        </div>
        <div style="display:flex;gap:4px">
            <button wire:click="$set('filtroEstado','pendiente')"
                class="btn {{ $filtroEstado === 'pendiente' ? 'btn-primary' : 'btn-secondary' }}"
                style="font-size:0.8rem">Pendientes</button>
            <button wire:click="$set('filtroEstado','vencido')"
                class="btn {{ $filtroEstado === 'vencido' ? 'btn-danger' : 'btn-secondary' }}"
                style="font-size:0.8rem">Vencidas</button>
            <button wire:click="$set('filtroEstado','pagado')"
                class="btn {{ $filtroEstado === 'pagado' ? 'btn-success' : 'btn-secondary' }}"
                style="font-size:0.8rem">Cobradas</button>
            <button wire:click="$set('filtroEstado','')"
                class="btn {{ $filtroEstado === '' ? 'btn-primary' : 'btn-secondary' }}"
                style="font-size:0.8rem">Todas</button>
        </div>
    </div>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Documento</th>
                    <th>Cliente</th>
                    <th>Fecha Doc.</th>
                    <th>Vencimiento</th>
                    <th style="text-align:right">Total</th>
                    <th style="text-align:right">Pagado</th>
                    <th style="text-align:right;color:var(--danger)">Saldo</th>
                    <th style="text-align:center">Estado</th>
                    <th style="text-align:center">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($ventas as $venta)
                    @php
                        $esVencida = $venta->dias_vencido > 0 && $venta->estado_pago !== 'pagado';
                    @endphp
                    <tr style="{{ $esVencida ? 'background:rgba(239,68,68,0.04)' : '' }}">
                        <td>
                            <code style="background:var(--bg-elevated);padding:2px 8px;border-radius:4px;font-size:0.8rem">
                                    {{ $venta->tipo_documento }}: {{ $venta->serie }}-{{ $venta->numero }}
                                </code>
                        </td>
                        <td>
                            <div style="font-size:0.85rem;font-weight:600">{{ $venta->cliente_nombre }}</div>
                            <div style="font-size:0.75rem;color:var(--text-muted)">{{ $venta->numero_documento }}</div>
                        </td>
                        <td style="font-size:0.82rem;color:var(--text-muted)">
                            {{ \Carbon\Carbon::parse($venta->fecha_emision)->format('d/m/Y') }}</td>
                        <td style="font-size:0.82rem">
                            @if($venta->fecha_vencimiento)
                                <span
                                    style="color:{{ $esVencida ? 'var(--danger)' : 'var(--text-secondary)' }};font-weight:{{ $esVencida ? '700' : '400' }}">
                                    {{ \Carbon\Carbon::parse($venta->fecha_vencimiento)->format('d/m/Y') }}
                                    @if($esVencida) <br><span style="font-size:0.72rem">{{ $venta->dias_vencido }}d
                                    vencida</span> @endif
                                </span>
                            @else —
                            @endif
                        </td>
                        <td style="text-align:right;font-weight:600">S/ {{ number_format($venta->total, 2) }}</td>
                        <td style="text-align:right;color:var(--success)">S/ {{ number_format($venta->monto_pagado, 2) }}
                        </td>
                        <td style="text-align:right;font-weight:700;color:var(--danger)">
                            {{ $venta->saldo_pendiente > 0 ? 'S/ ' . number_format($venta->saldo_pendiente, 2) : '—' }}
                        </td>
                        <td style="text-align:center">
                            @if($venta->estado_pago === 'pagado')
                                <span class="badge badge-success" style="font-size:0.7rem">Cobrado</span>
                            @elseif($esVencida)
                                <span class="badge badge-danger" style="font-size:0.7rem">Vencida</span>
                            @else
                                <span class="badge badge-warning" style="font-size:0.7rem">Pendiente</span>
                            @endif
                        </td>
                        <td style="text-align:center">
                            @if($venta->saldo_pendiente > 0)
                                <button wire:click="abrirPago({{ $venta->id }}, {{ $venta->saldo_pendiente }})"
                                    class="btn btn-sm btn-primary" title="Registrar cobro">
                                    <i data-lucide="dollar-sign"></i> Cobrar
                                </button>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" style="text-align:center;padding:60px;color:var(--text-muted)">
                            <i data-lucide="arrow-down-circle"
                                style="width:40px;height:40px;margin:0 auto 12px;display:block"></i>
                            No hay cuentas por cobrar.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div style="margin-top:16px">{{ $ventas->links() }}</div>

    {{-- Modal Registrar Cobro --}}
    @if($showModal)
        <div class="modal-backdrop" wire:click.self="$set('showModal', false)">
            <div class="modal" style="max-width:440px">
                <div class="modal-header">
                    <h3 style="font-weight:700;display:flex;align-items:center;gap:8px">
                        <i data-lucide="dollar-sign" style="width:18px;height:18px;color:var(--success)"></i>
                        Registrar Cobro
                    </h3>
                    <button wire:click="$set('showModal', false)"
                        style="background:none;border:none;color:var(--text-muted);cursor:pointer"><i data-lucide="x"
                            style="width:20px"></i></button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label class="form-label">Monto a Cobrar (S/)</label>
                        <input type="number" wire:model="montoPago" class="form-input" step="0.01" min="0.01">
                        @error('montoPago') <span style="color:var(--danger);font-size:0.75rem">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Fecha de Cobro</label>
                            <input type="date" wire:model="fechaPago" class="form-input">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Método</label>
                            <select wire:model="metodoPago" class="form-select">
                                <option value="efectivo">Efectivo</option>
                                <option value="transferencia">Transferencia</option>
                                <option value="yape_plin">Yape/Plin</option>
                                <option value="tarjeta">Tarjeta</option>
                                <option value="cheque">Cheque</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Referencia (opcional)</label>
                        <input type="text" wire:model="referencia" class="form-input"
                            placeholder="N° operación, N° cheque...">
                    </div>
                </div>
                <div class="modal-footer">
                    <button wire:click="$set('showModal', false)" class="btn btn-secondary">Cancelar</button>
                    <button wire:click="registrarPago" class="btn btn-primary">
                        <i data-lucide="check"></i> Confirmar Cobro
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>