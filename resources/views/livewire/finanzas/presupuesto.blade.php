<div>
    @if(session('success'))
        <div class="alert alert-success" style="margin-bottom:16px"><i data-lucide="check-circle-2"></i> {{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-error" style="margin-bottom:16px"><i data-lucide="alert-triangle"></i> {{ session('error') }}</div>
    @endif

    {{-- Toolbar: Filtros y Acciones principales --}}
    <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:16px;margin-bottom:24px">
        <div style="display:flex;gap:12px;align-items:center;flex-wrap:wrap">
            <div style="display:flex;align-items:center;gap:8px">
                <label class="form-label" style="margin:0;color:var(--text-muted)">Período:</label>
                <input type="month" wire:model.live="filtroPeriodo" class="form-input" style="width:160px">
            </div>
            <select wire:model.live="filtroTipo" class="form-select" style="width:160px">
                <option value="">Cualquier tipo</option>
                <option value="ingreso">Ingresos</option>
                <option value="gasto">Gastos</option>
                <option value="costo">Costos</option>
            </select>
            <div class="search-global" style="width:240px">
                <i data-lucide="search"></i>
                <input type="text" wire:model.live.debounce.300ms="buscar" placeholder="Buscar cuenta...">
            </div>
        </div>

        <div style="display:flex;gap:8px">
            <button wire:click="sincronizarEjecutados" class="btn btn-secondary" title="Recalcular montos ejecutados desde los asientos contables">
                <i data-lucide="refresh-cw"></i> Sincronizar Ejecutado
            </button>
            <button wire:click="copiarAlSiguienteMes" class="btn btn-secondary" title="Copiar estas líneas de presupuesto al mes siguiente">
                <i data-lucide="copy"></i> Copiar a siguiente mes
            </button>
            <button wire:click="openCreate" class="btn btn-primary">
                <i data-lucide="plus"></i> Nuevo Presupuesto
            </button>
        </div>
    </div>

    {{-- Stats --}}
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:16px;margin-bottom:24px">
        <div class="card" style="padding:20px;border-top:3px solid var(--primary)">
            <div style="font-size:0.75rem;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.05em;margin-bottom:6px">PRESUPUESTADO TOTAL</div>
            <div style="font-size:1.4rem;font-weight:800;color:var(--primary)">S/ {{ number_format($totalPresupuestado, 2) }}</div>
        </div>
        <div class="card" style="padding:20px;border-top:3px solid var(--warning)">
            <div style="font-size:0.75rem;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.05em;margin-bottom:6px">EJECUTADO TOTAL</div>
            <div style="font-size:1.4rem;font-weight:800;color:var(--warning)">S/ {{ number_format($totalEjecutado, 2) }}</div>
        </div>
        <div class="card" style="padding:20px;border-top:3px solid {{ $variacionTotal <= 0 ? 'var(--success)' : 'var(--danger)' }}">
            <div style="font-size:0.75rem;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.05em;margin-bottom:6px">VARIACIÓN / DESVIACIÓN</div>
            <div style="font-size:1.4rem;font-weight:800;color:{{ $variacionTotal <= 0 ? 'var(--success)' : 'var(--danger)' }}">
                {{ $variacionTotal > 0 ? '+' : '' }}S/ {{ number_format($variacionTotal, 2) }}
            </div>
        </div>
        <div class="card" style="padding:20px;border-top:3px solid {{ $porcentajeEjecucion <= 100 ? 'var(--success)' : 'var(--danger)' }}">
            <div style="font-size:0.75rem;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.05em;margin-bottom:6px">NIVEL EJECUCIÓN</div>
            <div style="font-size:1.4rem;font-weight:800;color:{{ $porcentajeEjecucion <= 100 ? 'var(--success)' : 'var(--danger)' }}">
                {{ $porcentajeEjecucion }}%
            </div>
        </div>
    </div>

    {{-- Tabla principal --}}
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Período</th>
                    <th>Cuenta</th>
                    <th>Tipo</th>
                    <th style="text-align:right;color:var(--primary)">Ppto (S/)</th>
                    <th style="text-align:right;color:var(--warning)">Ejecutado (S/)</th>
                    <th style="text-align:right;color:var(--text-primary)">Avance (%)</th>
                    <th style="text-align:right">Diferencia</th>
                    <th style="width:100px;text-align:center">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($presupuestos as $p)
                    @php 
                        $pct = $p->monto_presupuestado > 0 ? round(($p->monto_ejecutado / $p->monto_presupuestado) * 100, 1) : 0;
                        $diff = $p->monto_ejecutado - $p->monto_presupuestado;
                        $esSobre = $diff > 0 && strtolower($p->cuenta_tipo) !== 'ingreso'; // Alerta si es gasto/costo y se pasó
                    @endphp
                    <tr>
                        <td style="font-weight:600">{{ $p->periodo }}</td>
                        <td>
                            <code style="font-size:0.8rem;color:var(--primary)">{{ $p->codigo }}</code>
                            <div style="font-size:0.85rem;margin-top:2px">{{ $p->cuenta_nombre }}</div>
                        </td>
                        <td><span class="badge badge-info" style="font-size:0.7rem">{{ ucfirst($p->cuenta_tipo) }}</span></td>
                        <td style="text-align:right;font-weight:600;color:var(--primary)">{{ number_format($p->monto_presupuestado, 2) }}</td>
                        <td style="text-align:right;font-weight:600;color:var(--warning)">{{ number_format($p->monto_ejecutado, 2) }}</td>
                        <td style="text-align:right">
                            <div style="display:flex;align-items:center;justify-content:flex-end;gap:8px">
                                <span style="font-weight:700;color:{{ $esSobre ? 'var(--danger)' : 'var(--success)' }}">{{ $pct }}%</span>
                                <div style="width:40px;height:6px;background:var(--border-color);border-radius:3px;overflow:hidden">
                                    <div style="width:{{ min($pct, 100) }}%;height:100%;background:{{ $esSobre ? 'var(--danger)' : 'var(--success)' }}"></div>
                                </div>
                            </div>
                        </td>
                        <td style="text-align:right;font-weight:700;color:{{ $esSobre ? 'var(--danger)' : 'var(--text-primary)' }}">
                            {{ $diff > 0 ? '+' : '' }}{{ number_format($diff, 2) }}
                        </td>
                        <td>
                            <div class="actions">
                                <button wire:click="openEdit({{ $p->id }})" class="btn-icon" title="Editar"><i data-lucide="edit-3"></i></button>
                                <button onclick="if(confirm('¿Eliminar esta línea del presupuesto?')) @this.call('delete', {{ $p->id }})" class="btn-icon danger" title="Eliminar"><i data-lucide="trash-2"></i></button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" style="text-align:center;padding:60px;color:var(--text-muted)">
                            <i data-lucide="target" style="width:40px;height:40px;margin:0 auto 12px;display:block"></i>
                            No hay cuentas presupuestadas para los filtros actuales.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div style="margin-top:16px">{{ $presupuestos->links() }}</div>

    {{-- Modal Crear/Editar --}}
    @if($showModal)
        <div class="modal-backdrop" wire:click.self="$set('showModal', false)">
            <div class="modal" style="max-width:500px">
                <div class="modal-header">
                    <h3 style="font-weight:700;display:flex;align-items:center;gap:8px">
                        <i data-lucide="target" style="width:18px;height:18px;color:var(--primary)"></i>
                        {{ $editMode ? 'Editar Presupuesto' : 'Nuevo Presupuesto' }}
                    </h3>
                    <button wire:click="$set('showModal', false)" style="background:none;border:none;color:var(--text-muted);cursor:pointer"><i data-lucide="x" style="width:20px"></i></button>
                </div>
                <div class="modal-body">
                    <div class="form-row">
                        <div class="form-group" style="flex:1">
                            <label class="form-label">Período (Mes)</label>
                            <input type="month" wire:model="periodo" class="form-input" {{ $editMode ? 'disabled' : '' }}>
                            @error('periodo') <span style="color:var(--danger);font-size:0.75rem">{{ $message }}</span> @enderror
                        </div>
                        <div class="form-group" style="flex:1">
                            <label class="form-label">Monto (S/)</label>
                            <input type="number" wire:model="monto_presupuestado" step="0.01" class="form-input">
                            @error('monto_presupuestado') <span style="color:var(--danger);font-size:0.75rem">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    
                    <div class="form-group" style="position:relative">
                        <label class="form-label">Cuenta Contable</label>
                        @if($editMode)
                            <input type="text" class="form-input" disabled value="{{ $cuentaBusqueda }}">
                        @else
                            <div class="search-global" style="width:100%;margin-bottom:4px;border:1px solid var(--border-color)">
                                <i data-lucide="search"></i>
                                <input type="text" wire:model.live.debounce.300ms="cuentaBusqueda" placeholder="Buscar cuenta por código o nombre...">
                            </div>
                            @error('cuenta_id') <span style="color:var(--danger);font-size:0.75rem;display:block;margin-bottom:8px">{{ $message }}</span> @enderror
                            
                            @if(strlen($cuentaBusqueda) > 2 && empty($cuenta_id))
                                <div style="border:1px solid var(--border-color);border-radius:6px;max-height:180px;overflow-y:auto;background:var(--bg-elevated)">
                                    @forelse($cuentasSelector as $c)
                                        <div wire:click="seleccionarCuenta({{ $c->id }}, '{{ $c->codigo }} — {{ $c->nombre }}')" 
                                             style="padding:8px 12px;border-bottom:1px solid var(--border-color);cursor:pointer;font-size:0.85rem">
                                            <span style="font-family:monospace;color:var(--primary);margin-right:8px">{{ $c->codigo }}</span>
                                            {{ $c->nombre }}
                                        </div>
                                    @empty
                                        <div style="padding:12px;text-align:center;color:var(--text-muted);font-size:0.85rem">No se encontraron cuentas.</div>
                                    @endforelse
                                </div>
                            @elseif($cuenta_id)
                                <div style="padding:8px 12px;background:rgba(34,197,94,0.1);border:1px solid var(--success);border-radius:6px;display:flex;justify-content:space-between;align-items:center;margin-top:4px">
                                    <span style="font-size:0.85rem;font-weight:600;color:var(--success)">{{ $cuentaBusqueda }}</span>
                                    <button wire:click="$set('cuenta_id', null)" style="background:none;border:none;cursor:pointer;color:var(--text-muted)"><i data-lucide="x" style="width:14px"></i></button>
                                </div>
                            @endif
                        @endif
                    </div>
                </div>
                <div class="modal-footer">
                    <button wire:click="$set('showModal', false)" class="btn btn-secondary">Cancelar</button>
                    <button wire:click="save" class="btn btn-primary">
                        <i data-lucide="save"></i> Guardar Presupuesto
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
