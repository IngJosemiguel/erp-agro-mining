<div>
    {{-- MODAL --}}
    @if($showModal)
        <div class="modal-backdrop"
            style="position:fixed;inset:0;background:rgba(0,0,0,.7);backdrop-filter:blur(4px);z-index:9999;display:flex;align-items:center;justify-content:center;padding:20px;">
            <div class="modal-box"
                style="background:var(--bg-card);border:1px solid var(--border-color);border-radius:20px;padding:32px;width:100%;max-width:640px;max-height:90vh;overflow-y:auto;position:relative;">
                <h2 style="font-size:1.2rem;font-weight:800;margin:0 0 20px;display:flex;align-items:center;gap:10px;">
                    <i data-lucide="package-2" style="width:20px;height:20px;color:var(--accent)"></i>
                    {{ $editMode ? 'Editar Pack' : 'Nuevo Pack / Combo' }}
                </h2>

                {{-- Datos base --}}
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:16px;">
                    <div style="grid-column:1/-1;">
                        <label class="form-label">Nombre del Pack *</label>
                        <input wire:model="nombre" type="text" class="form-input" placeholder="Ej: Kit Fumigación Completo">
                        @error('nombre')<span style="color:#ef4444;font-size:.75rem;">{{ $message }}</span>@enderror
                    </div>
                    <div>
                        <label class="form-label">Código SKU *</label>
                        <input wire:model="codigo_interno" type="text" class="form-input" placeholder="PACK-001">
                        @error('codigo_interno')<span style="color:#ef4444;font-size:.75rem;">{{ $message }}</span>@enderror
                    </div>
                    <div>
                        <label class="form-label">Precio de Venta (S/)</label>
                        <input wire:model="precio_venta" type="number" class="form-input" step="0.01" min="0"
                            placeholder="0.00">
                        @error('precio_venta')<span style="color:#ef4444;font-size:.75rem;">{{ $message }}</span>@enderror
                    </div>
                    <div>
                        <label class="form-label">Unidad de Medida</label>
                        <select wire:model="unidad_medida_id" class="form-input">
                            @foreach($unidadesMedida as $um)
                                <option value="{{ $um->id }}">{{ $um->descripcion }} ({{ $um->abreviatura }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="form-label">Tipo de Existencia</label>
                        <select wire:model="tipo_existencia_id" class="form-input">
                            @foreach($tiposExistencia as $te)
                                <option value="{{ $te->id }}">{{ $te->descripcion }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div style="display:flex;gap:20px;margin-bottom:20px;">
                    <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:.85rem;">
                        <input wire:model="es_gravado_igv" type="checkbox"
                            style="width:16px;height:16px;accent-color:var(--accent);">
                        Gravado IGV (18%)
                    </label>
                    <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:.85rem;">
                        <input wire:model="disponible_pos" type="checkbox"
                            style="width:16px;height:16px;accent-color:var(--accent);">
                        Disponible en POS
                    </label>
                    <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:.85rem;">
                        <input wire:model="activo" type="checkbox"
                            style="width:16px;height:16px;accent-color:var(--accent);">
                        Activo
                    </label>
                </div>

                {{-- Buscador de items para incluir en el pack --}}
                <div style="border-top:1px solid var(--border-color);padding-top:16px;margin-bottom:16px;">
                    <label class="form-label" style="margin-bottom:8px;display:block;">
                        <i data-lucide="layers" style="width:14px;height:14px;display:inline;vertical-align:middle;"></i>
                        Productos incluidos en el pack
                    </label>
                    <div style="position:relative;">
                        <input wire:model.live.debounce.300ms="buscarItem" type="text" class="form-input"
                            placeholder="Buscar producto para agregar al pack...">
                        @if(count($itemsResultados) > 0)
                            <div
                                style="position:absolute;top:calc(100% + 4px);left:0;right:0;background:var(--bg-card);border:1px solid var(--border-color);border-radius:10px;z-index:100;box-shadow:0 10px 30px rgba(0,0,0,.4);max-height:200px;overflow-y:auto;">
                                @foreach($itemsResultados as $p)
                                    <div wire:click="agregarItem({{ $p['id'] }})"
                                        style="display:flex;justify-content:space-between;padding:10px 14px;cursor:pointer;border-bottom:1px solid var(--border-color);transition:.15s;"
                                        onmouseover="this.style.background='rgba(0,255,136,.06)'"
                                        onmouseout="this.style.background=''">
                                        <div>
                                            <div style="font-weight:700;font-size:.85rem;color:var(--text-primary);">
                                                {{ $p['nombre'] }}</div>
                                            <div style="font-size:.73rem;color:var(--text-muted);">{{ $p['codigo_interno'] }}</div>
                                        </div>
                                        <span style="color:var(--accent);font-weight:800;">S/
                                            {{ number_format($p['precio_venta'], 2) }}</span>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    {{-- Tabla de items del pack --}}
                    @if(count($items) > 0)
                        <table style="width:100%;border-collapse:collapse;margin-top:12px;">
                            <thead>
                                <tr style="background:var(--bg-body);">
                                    <th
                                        style="padding:8px 12px;text-align:left;font-size:.73rem;color:var(--text-muted);font-weight:700;text-transform:uppercase;">
                                        Producto</th>
                                    <th
                                        style="padding:8px 12px;text-align:center;font-size:.73rem;color:var(--text-muted);font-weight:700;text-transform:uppercase;width:80px;">
                                        Cant.</th>
                                    <th
                                        style="padding:8px 12px;text-align:right;font-size:.73rem;color:var(--text-muted);font-weight:700;text-transform:uppercase;width:90px;">
                                        Subtotal</th>
                                    <th style="width:40px;"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($items as $i => $item)
                                    <tr style="border-bottom:1px solid var(--border-color);">
                                        <td style="padding:8px 12px;font-size:.85rem;font-weight:600;">{{ $item['nombre'] }}</td>
                                        <td style="padding:8px 12px;text-align:center;">
                                            <input wire:model.lazy="items.{{ $i }}.cantidad" wire:change="calcularPrecio"
                                                type="number" min="1" step="1"
                                                style="width:56px;text-align:center;background:var(--bg-body);border:1px solid var(--border-color);border-radius:6px;padding:4px;color:var(--text-primary);">
                                        </td>
                                        <td style="padding:8px 12px;text-align:right;font-weight:700;color:var(--accent);">S/
                                            {{ number_format($item['cantidad'] * $item['precio'], 2) }}</td>
                                        <td style="padding:8px 12px;text-align:center;">
                                            <button wire:click="quitarItem({{ $i }})"
                                                style="background:rgba(239,68,68,.1);border:none;border-radius:5px;color:#ef4444;cursor:pointer;width:24px;height:24px;display:flex;align-items:center;justify-content:center;margin:0 auto;">
                                                <i data-lucide="x" style="width:13px;height:13px;"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="2"
                                        style="padding:10px 12px;font-weight:700;text-align:right;color:var(--text-muted);font-size:.85rem;">
                                        Precio total calculado:</td>
                                    <td
                                        style="padding:10px 12px;text-align:right;font-weight:900;font-size:1.1rem;color:var(--accent);">
                                        S/ {{ $precio_venta }}</td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    @else
                        <div
                            style="text-align:center;padding:24px;color:var(--text-muted);font-size:.85rem;border:1.5px dashed var(--border-color);border-radius:10px;margin-top:12px;">
                            <i data-lucide="layers"
                                style="width:28px;height:28px;display:block;margin:0 auto 8px;opacity:.4;"></i>
                            Busca y agrega productos para componer el pack
                        </div>
                    @endif
                </div>

                {{-- Acciones --}}
                <div
                    style="display:flex;gap:10px;justify-content:flex-end;margin-top:20px;border-top:1px solid var(--border-color);padding-top:16px;">
                    <button wire:click="$set('showModal', false)"
                        style="padding:10px 20px;background:var(--bg-body);border:1px solid var(--border-color);border-radius:8px;color:var(--text-primary);cursor:pointer;font-weight:600;">
                        Cancelar
                    </button>
                    <button wire:click="save" wire:loading.attr="disabled"
                        style="padding:10px 24px;background:var(--accent);border:none;border-radius:8px;color:#000;font-weight:800;cursor:pointer;">
                        <span wire:loading.remove wire:target="save">
                            <i data-lucide="save"
                                style="width:15px;height:15px;display:inline;vertical-align:middle;margin-right:4px;"></i>
                            {{ $editMode ? 'Actualizar' : 'Crear Pack' }}
                        </span>
                        <span wire:loading wire:target="save">Guardando...</span>
                    </button>
                </div>

                <button wire:click="$set('showModal', false)"
                    style="position:absolute;top:14px;right:14px;background:none;border:none;color:var(--text-muted);cursor:pointer;width:30px;height:30px;display:flex;align-items:center;justify-content:center;border-radius:6px;">
                    <i data-lucide="x" style="width:18px;height:18px;"></i>
                </button>
            </div>
        </div>
    @endif

    {{-- HEADER --}}
    <div
        style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;flex-wrap:wrap;gap:12px;">
        <div>
            <h1 style="font-size:1.6rem;font-weight:800;margin:0;display:flex;align-items:center;gap:10px;">
                <span
                    style="width:40px;height:40px;border-radius:10px;background:linear-gradient(135deg,var(--accent),#00c8a0);display:flex;align-items:center;justify-content:center;">
                    <i data-lucide="package-2" style="width:20px;height:20px;color:#000;"></i>
                </span>
                Conjuntos / Packs
            </h1>
            <p style="color:var(--text-muted);font-size:.85rem;margin:4px 0 0 50px;">
                Productos compuestos o kits para venta conjunta — {{ $packs->total() }} pack(s)
            </p>
        </div>
        <button wire:click="openCreateModal"
            style="display:inline-flex;align-items:center;gap:8px;padding:10px 20px;background:var(--accent);border:none;border-radius:10px;color:#000;font-weight:800;cursor:pointer;transition:.2s;">
            <i data-lucide="plus" style="width:18px;height:18px;"></i> Nuevo Pack
        </button>
    </div>

    {{-- FILTROS --}}
    <div class="card" style="padding:14px 18px;margin-bottom:16px;display:flex;gap:12px;align-items:center;">
        <div style="position:relative;flex:1;">
            <i data-lucide="search"
                style="position:absolute;left:12px;top:50%;transform:translateY(-50%);width:16px;height:16px;color:var(--text-muted);"></i>
            <input wire:model.live.debounce.300ms="buscar" type="text" placeholder="Buscar pack por nombre o código..."
                style="width:100%;padding:9px 14px 9px 38px;background:var(--bg-body);border:1px solid var(--border-color);border-radius:8px;color:var(--text-primary);">
        </div>
    </div>

    {{-- TABLA --}}
    <div class="card" style="overflow:hidden;">
        <table style="width:100%;border-collapse:collapse;">
            <thead>
                <tr style="background:rgba(0,0,0,.2);">
                    <th
                        style="padding:12px 16px;text-align:left;font-size:.73rem;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:.05em;">
                        Pack</th>
                    <th
                        style="padding:12px 16px;text-align:left;font-size:.73rem;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:.05em;">
                        SKU</th>
                    <th
                        style="padding:12px 16px;text-align:right;font-size:.73rem;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:.05em;">
                        Precio</th>
                    <th
                        style="padding:12px 16px;text-align:center;font-size:.73rem;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:.05em;">
                        IGV</th>
                    <th
                        style="padding:12px 16px;text-align:center;font-size:.73rem;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:.05em;">
                        POS</th>
                    <th
                        style="padding:12px 16px;text-align:center;font-size:.73rem;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:.05em;">
                        Estado</th>
                    <th style="padding:12px 16px;width:100px;"></th>
                </tr>
            </thead>
            <tbody>
                @forelse($packs as $pack)
                    <tr style="border-bottom:1px solid var(--border-color);transition:.15s;"
                        onmouseover="this.style.background='rgba(255,255,255,.02)'" onmouseout="this.style.background=''">
                        <td style="padding:12px 16px;">
                            <div style="display:flex;align-items:center;gap:10px;">
                                <div
                                    style="width:34px;height:34px;border-radius:8px;background:linear-gradient(135deg,rgba(0,255,136,.15),rgba(0,200,160,.1));display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                                    <i data-lucide="package-2" style="width:16px;height:16px;color:var(--accent);"></i>
                                </div>
                                <span
                                    style="font-weight:700;font-size:.9rem;color:var(--text-primary);">{{ $pack->nombre }}</span>
                            </div>
                        </td>
                        <td style="padding:12px 16px;font-size:.8rem;color:var(--text-muted);font-family:monospace;">
                            {{ $pack->codigo_interno }}</td>
                        <td style="padding:12px 16px;text-align:right;font-weight:800;color:var(--accent);">S/
                            {{ number_format($pack->precio_venta, 2) }}</td>
                        <td style="padding:12px 16px;text-align:center;">
                            @if($pack->es_gravado_igv)
                                <span
                                    style="background:rgba(0,255,136,.15);color:var(--accent);font-size:.7rem;font-weight:700;padding:2px 8px;border-radius:20px;">18%</span>
                            @else
                                <span
                                    style="background:rgba(156,163,175,.1);color:var(--text-muted);font-size:.7rem;font-weight:700;padding:2px 8px;border-radius:20px;">Exo.</span>
                            @endif
                        </td>
                        <td style="padding:12px 16px;text-align:center;">
                            @if($pack->disponible_pos)
                                <i data-lucide="check-circle-2" style="width:18px;height:18px;color:var(--accent);"></i>
                            @else
                                <i data-lucide="minus-circle" style="width:18px;height:18px;color:var(--text-muted);"></i>
                            @endif
                        </td>
                        <td style="padding:12px 16px;text-align:center;">
                            <button wire:click="toggleActivo({{ $pack->id }})"
                                style="background:none;border:none;cursor:pointer;padding:0;">
                                @if($pack->activo)
                                    <span
                                        style="background:rgba(0,255,136,.15);color:var(--accent);font-size:.73rem;font-weight:700;padding:3px 10px;border-radius:20px;">Activo</span>
                                @else
                                    <span
                                        style="background:rgba(239,68,68,.1);color:#ef4444;font-size:.73rem;font-weight:700;padding:3px 10px;border-radius:20px;">Inactivo</span>
                                @endif
                            </button>
                        </td>
                        <td style="padding:12px 16px;text-align:center;">
                            <div style="display:flex;gap:6px;justify-content:center;">
                                <button wire:click="openEditModal({{ $pack->id }})" title="Editar"
                                    style="width:30px;height:30px;background:rgba(59,130,246,.1);border:none;border-radius:6px;color:#60a5fa;cursor:pointer;display:flex;align-items:center;justify-content:center;">
                                    <i data-lucide="pencil" style="width:14px;height:14px;"></i>
                                </button>
                                <button wire:click="delete({{ $pack->id }})" wire:confirm="¿Eliminar este pack?"
                                    title="Eliminar"
                                    style="width:30px;height:30px;background:rgba(239,68,68,.1);border:none;border-radius:6px;color:#ef4444;cursor:pointer;display:flex;align-items:center;justify-content:center;">
                                    <i data-lucide="trash-2" style="width:14px;height:14px;"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" style="padding:60px;text-align:center;color:var(--text-muted);">
                            <i data-lucide="package-2"
                                style="width:48px;height:48px;display:block;margin:0 auto 12px;opacity:.3;"></i>
                            <p style="font-weight:700;margin:0 0 6px;">Sin packs registrados</p>
                            <span style="font-size:.85rem;">Crea tu primer pack o combo de productos</span>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        @if($packs->hasPages())
            <div style="padding:16px 18px;border-top:1px solid var(--border-color);">
                {{ $packs->links() }}
            </div>
        @endif
    </div>
</div>