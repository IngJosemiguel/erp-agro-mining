<div>
    {{-- KPIs --}}
    <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(220px, 1fr));gap:16px;margin-bottom:24px">
        <div class="card" style="padding:20px">
            <div style="display:flex;align-items:center;gap:12px">
                <div
                    style="width:44px;height:44px;border-radius:10px;background:rgba(239,68,68,0.15);display:flex;align-items:center;justify-content:center">
                    <i data-lucide="receipt" style="width:22px;height:22px;color:#ef4444"></i>
                </div>
                <div>
                    <p style="font-size:0.75rem;color:var(--text-muted);margin:0">GASTOS DEL PERÍODO</p>
                    <p style="font-size:1.5rem;font-weight:800;margin:0">{{ $cantGastos }}</p>
                </div>
            </div>
        </div>
        <div class="card" style="padding:20px">
            <div style="display:flex;align-items:center;gap:12px">
                <div
                    style="width:44px;height:44px;border-radius:10px;background:rgba(239,68,68,0.15);display:flex;align-items:center;justify-content:center">
                    <i data-lucide="trending-down" style="width:22px;height:22px;color:#ef4444"></i>
                </div>
                <div>
                    <p style="font-size:0.75rem;color:var(--text-muted);margin:0">TOTAL GASTOS</p>
                    <p style="font-size:1.5rem;font-weight:800;margin:0;color:#ef4444">S/
                        {{ number_format($totalGastos, 2) }}</p>
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
                    <p style="font-size:0.75rem;color:var(--text-muted);margin:0">POR PAGAR</p>
                    <p style="font-size:1.5rem;font-weight:800;margin:0;color:#f59e0b">{{ $pendientes }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Filtros --}}
    <div
        style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;flex-wrap:wrap;gap:12px">
        <div style="display:flex;gap:10px;flex-wrap:wrap;align-items:center">
            <div class="search-global" style="width:260px">
                <i data-lucide="search"></i>
                <input type="text" wire:model.live.debounce.300ms="buscar" placeholder="Buscar concepto, serie...">
            </div>
            <select wire:model.live="filtroEstado" class="form-select" style="width:160px">
                <option value="">Todos</option>
                <option value="registrado">Registrado</option>
                <option value="pagado">Pagado</option>
                <option value="anulado">Anulado</option>
            </select>
            <select wire:model.live="filtroCategoria" class="form-select" style="width:180px">
                <option value="">Todas las categorías</option>
                @foreach($categorias as $key => $label)
                    <option value="{{ $key }}">{{ $label }}</option>
                @endforeach
            </select>
            <input type="date" wire:model.live="fechaDesde" class="form-input" style="width:150px">
            <input type="date" wire:model.live="fechaHasta" class="form-input" style="width:150px">
        </div>
        <button wire:click="openModal" class="btn btn-primary">
            <i data-lucide="plus"></i> Nuevo Gasto
        </button>
    </div>

    @if(session()->has('success'))
        <div class="alert alert-success"><i data-lucide="check-circle-2"></i><span>{{ session('success') }}</span></div>
    @endif

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Concepto</th>
                    <th>Categoría</th>
                    <th>Proveedor</th>
                    <th>Documento</th>
                    <th style="text-align:right">Subtotal</th>
                    <th style="text-align:right">IGV</th>
                    <th style="text-align:right">Total</th>
                    <th style="text-align:center">Estado</th>
                    <th>Usuario</th>
                    <th style="text-align:center">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($gastos as $g)
                    <tr>
                        <td style="white-space:nowrap;color:var(--text-muted)">{{ $g->fecha?->format('d/m/Y') }}</td>
                        <td><strong>{{ \Str::limit($g->concepto, 35) }}</strong></td>
                        <td style="color:var(--text-muted);font-size:0.85rem">{{ $categorias[$g->categoria_gasto] ?? '—' }}
                        </td>
                        <td style="color:var(--text-muted);font-size:0.85rem">
                            {{ \Str::limit($g->proveedor->razon_social ?? '—', 25) }}</td>
                        <td style="color:var(--text-muted);font-size:0.85rem">
                            {{ $g->serie && $g->correlativo ? "{$g->serie}-{$g->correlativo}" : '—' }}
                        </td>
                        <td style="text-align:right;color:var(--text-muted)">S/ {{ number_format($g->subtotal, 2) }}</td>
                        <td style="text-align:right;color:var(--text-muted)">S/ {{ number_format($g->igv, 2) }}</td>
                        <td style="text-align:right;font-weight:700;color:#ef4444">S/ {{ number_format($g->total, 2) }}</td>
                        <td style="text-align:center">
                            <span class="badge {{ $g->estado_badge }}"
                                style="font-size:0.65rem">{{ $g->estado_texto }}</span>
                        </td>
                        <td style="color:var(--text-muted);font-size:0.8rem">{{ $g->usuario->name ?? '—' }}</td>
                        <td style="text-align:center;white-space:nowrap">
                            @if($g->estado === 'registrado')
                                <button wire:click="marcarPagado({{ $g->id }})" title="Marcar pagado"
                                    style="background:none;border:none;color:var(--success);cursor:pointer">
                                    <i data-lucide="check-circle" style="width:16px;height:16px"></i>
                                </button>
                            @endif
                            <button wire:click="openModal({{ $g->id }})" title="Editar"
                                style="background:none;border:none;color:var(--accent);cursor:pointer">
                                <i data-lucide="pencil" style="width:16px;height:16px"></i>
                            </button>
                            @if($g->estado !== 'anulado')
                                <button wire:click="anular({{ $g->id }})" wire:confirm="¿Anular este gasto?" title="Anular"
                                    style="background:none;border:none;color:var(--danger);cursor:pointer">
                                    <i data-lucide="x-circle" style="width:16px;height:16px"></i>
                                </button>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="11" style="text-align:center;padding:60px;color:var(--text-muted)">
                            <i data-lucide="wallet" style="width:48px;height:48px;display:block;margin:0 auto 12px"></i>
                            No hay gastos registrados en el período
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div style="margin-top:16px">{{ $gastos->links() }}</div>

    {{-- MODAL --}}
    @if($showModal)
        <div class="modal-backdrop" wire:click.self="$set('showModal', false)">
            <div class="modal" style="max-width:640px">
                <div class="modal-header">
                    <h3 style="font-weight:700">{{ $editingId ? 'Editar Gasto' : 'Nuevo Gasto' }}</h3>
                    <button wire:click="$set('showModal', false)"
                        style="background:none;border:none;color:var(--text-muted);cursor:pointer">
                        <i data-lucide="x" style="width:20px;height:20px"></i>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Fecha *</label>
                            <input type="date" wire:model="fecha" class="form-input">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Categoría</label>
                            <select wire:model="categoria_gasto" class="form-select">
                                <option value="">— Seleccionar —</option>
                                @foreach($categorias as $key => $label)
                                    <option value="{{ $key }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Concepto / Descripción *</label>
                        <input type="text" wire:model="concepto" class="form-input"
                            placeholder="Ej: Servicio de transporte de carga">
                        @error('concepto') <span style="color:var(--danger);font-size:0.75rem">{{ $message }}</span>
                        @enderror
                    </div>

                    {{-- Proveedor opcional --}}
                    <div class="form-group" style="position:relative">
                        <label class="form-label">Proveedor (opcional)</label>
                        @if($proveedorTexto)
                            <div
                                style="display:flex;align-items:center;gap:8px;padding:8px;background:rgba(0,255,136,0.1);border:1px solid var(--accent);border-radius:8px">
                                <span style="flex:1;font-weight:600;font-size:0.85rem">{{ $proveedorTexto }}</span>
                                <button wire:click="$set('proveedorTexto', '')" wire:click.prevent="$set('proveedor_id', '')"
                                    style="background:none;border:none;color:var(--danger);cursor:pointer">
                                    <i data-lucide="x-circle" style="width:16px;height:16px"></i>
                                </button>
                            </div>
                        @else
                            <input type="text" wire:model.live.debounce.300ms="buscarProveedor" class="form-input"
                                placeholder="Buscar proveedor...">
                            @if(count($proveedoresEncontrados) > 0)
                                <div
                                    style="position:absolute;top:100%;left:0;right:0;z-index:50;background:var(--card-bg);border:1px solid var(--border);border-radius:8px;max-height:160px;overflow-y:auto;box-shadow:0 8px 24px rgba(0,0,0,0.3)">
                                    @foreach($proveedoresEncontrados as $p)
                                        <button wire:click="seleccionarProveedor({{ $p['id'] }})"
                                            style="display:block;width:100%;text-align:left;padding:8px 12px;background:none;border:none;border-bottom:1px solid var(--border);color:var(--text-primary);cursor:pointer;font-size:0.85rem"
                                            onmouseover="this.style.background='rgba(0,255,136,0.1)'"
                                            onmouseout="this.style.background='none'">
                                            <strong>{{ $p['numero_documento'] }}</strong> — {{ $p['razon_social'] }}
                                        </button>
                                    @endforeach
                                </div>
                            @endif
                        @endif
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Serie</label>
                            <input type="text" wire:model="serie" class="form-input" placeholder="E001">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Correlativo</label>
                            <input type="text" wire:model="correlativo" class="form-input" placeholder="00000001">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Subtotal (S/) *</label>
                            <input type="number" wire:model.live="subtotal" class="form-input" step="0.01" min="0">
                        </div>
                        <div class="form-group">
                            <label class="form-label">IGV (18%)</label>
                            <input type="number" wire:model="igv" class="form-input" step="0.01" readonly
                                style="opacity:0.7">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Total</label>
                            <input type="number" wire:model="total" class="form-input" step="0.01" readonly
                                style="font-weight:700;color:var(--accent)">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Observaciones</label>
                        <textarea wire:model="observaciones" class="form-input" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button wire:click="$set('showModal', false)" class="btn btn-secondary">Cancelar</button>
                    <button wire:click="save" class="btn btn-primary">
                        <i data-lucide="save"></i> {{ $editingId ? 'Actualizar' : 'Registrar' }} Gasto
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>