<div>
    {{-- Toolbar --}}
    <div
        style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;flex-wrap:wrap;gap:12px">
        <div style="display:flex;gap:10px;flex-wrap:wrap;align-items:center">
            <div class="search-global" style="width:280px">
                <i data-lucide="search"></i>
                <input type="text" wire:model.live.debounce.300ms="buscar" placeholder="Buscar productos...">
            </div>

            <select wire:model.live="categoriaFiltro" class="form-select" style="width:180px">
                <option value="">Todas las categorías</option>
                @foreach($categorias as $cat)
                    <option value="{{ $cat->id }}">{{ $cat->nombre }}</option>
                @endforeach
            </select>

            <select wire:model.live="marcaFiltro" class="form-select" style="width:160px">
                <option value="">Todas las marcas</option>
                @foreach($marcas as $marca)
                    <option value="{{ $marca->id }}">{{ $marca->nombre }}</option>
                @endforeach
            </select>

            <select wire:model.live="tipoFiltro" class="form-select" style="width:140px">
                <option value="">Todos</option>
                <option value="bien">Bienes</option>
                <option value="servicio">Servicios</option>
                <option value="combo">Combos</option>
            </select>
        </div>

        <button wire:click="openCreateModal" class="btn btn-primary">
            <i data-lucide="plus"></i> Nuevo Producto
        </button>
    </div>

    {{-- Flash Messages --}}
    @if(session()->has('success'))
        <div class="alert alert-success"><i data-lucide="check-circle-2"></i><span>{{ session('success') }}</span></div>
    @endif
    @if(session()->has('error'))
        <div class="alert alert-error"><i data-lucide="alert-circle"></i><span>{{ session('error') }}</span></div>
    @endif

    {{-- Table --}}
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th wire:click="sortBy('codigo_interno')" style="cursor:pointer">
                        Código {{ $ordenar === 'codigo_interno' ? ($direccion === 'asc' ? '↑' : '↓') : '' }}
                    </th>
                    <th wire:click="sortBy('nombre')" style="cursor:pointer">
                        Nombre {{ $ordenar === 'nombre' ? ($direccion === 'asc' ? '↑' : '↓') : '' }}
                    </th>
                    <th>Categoría</th>
                    <th>Marca</th>
                    <th>Tipo</th>
                    <th>U.M.</th>
                    <th wire:click="sortBy('precio_venta')" style="cursor:pointer;text-align:right">
                        Precio {{ $ordenar === 'precio_venta' ? ($direccion === 'asc' ? '↑' : '↓') : '' }}
                    </th>
                    <th style="text-align:right">Stock</th>
                    <th style="text-align:center">POS</th>
                    <th style="text-align:center">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($productos as $producto)
                    <tr>
                        <td><code
                                style="background:var(--bg-elevated);padding:2px 8px;border-radius:4px;font-size:0.8rem">{{ $producto->codigo_interno }}</code>
                        </td>
                        <td>
                            <strong>{{ \Str::limit($producto->nombre, 40) }}</strong>
                            @if($producto->controla_lote)
                                <span class="badge badge-info" style="font-size:0.6rem;margin-left:4px">LOTE</span>
                            @endif
                            @if($producto->controla_serie)
                                <span class="badge badge-warning" style="font-size:0.6rem;margin-left:4px">SERIE</span>
                            @endif
                        </td>
                        <td style="color:var(--text-muted)">{{ $producto->categoria->nombre ?? '—' }}</td>
                        <td style="color:var(--text-muted)">{{ $producto->marca->nombre ?? '—' }}</td>
                        <td>
                            @if($producto->tipo_producto === 'bien')
                                <span class="badge badge-success">Bien</span>
                            @elseif($producto->tipo_producto === 'servicio')
                                <span class="badge badge-info">Servicio</span>
                            @else
                                <span class="badge badge-warning">Combo</span>
                            @endif
                        </td>
                        <td style="color:var(--text-muted)">{{ $producto->unidadMedida->abreviatura ?? 'UND' }}</td>
                        <td style="text-align:right;font-weight:600">S/
                            {{ number_format((float) $producto->precio_venta, 2) }}</td>
                        <td style="text-align:right">
                            @php $stock = $producto->stock_total ?? 0; @endphp
                            <span
                                style="color:{{ $stock <= ($producto->stock_minimo ?? 0) ? 'var(--danger)' : 'var(--success)' }};font-weight:600">
                                {{ number_format($stock, 2) }}
                            </span>
                        </td>
                        <td style="text-align:center">
                            @if($producto->disponible_pos)
                                <span style="color:var(--success)"><i data-lucide="check"
                                        style="width:16px;height:16px"></i></span>
                            @else
                                <span style="color:var(--text-muted)"><i data-lucide="minus"
                                        style="width:16px;height:16px"></i></span>
                            @endif
                        </td>
                        <td style="text-align:center">
                            <div style="display:flex;gap:4px;justify-content:center">
                                <button wire:click="openEditModal({{ $producto->id }})" class="btn btn-sm btn-secondary"
                                    title="Editar">
                                    <i data-lucide="pencil"></i>
                                </button>
                                <button wire:click="delete({{ $producto->id }})"
                                    wire:confirm="¿Estás seguro de eliminar este producto?" class="btn btn-sm btn-danger"
                                    title="Eliminar">
                                    <i data-lucide="trash-2"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" style="text-align:center;padding:60px;color:var(--text-muted)">
                            <i data-lucide="package-open"
                                style="width:48px;height:48px;margin-bottom:12px;display:block;margin:0 auto 12px"></i>
                            No se encontraron productos
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    <div style="margin-top:16px;display:flex;align-items:center;justify-content:space-between">
        <span style="font-size:0.8rem;color:var(--text-muted)">Mostrando {{ $productos->firstItem() ?? 0 }} -
            {{ $productos->lastItem() ?? 0 }} de {{ $productos->total() }}</span>
        {{ $productos->links() }}
    </div>

    {{-- Create/Edit Modal --}}
    @if($showModal)
        <div class="modal-backdrop" wire:click.self="$set('showModal', false)">
            <div class="modal">
                <div class="modal-header">
                    <h3 style="font-weight:700">{{ $editMode ? 'Editar Producto' : 'Nuevo Producto' }}</h3>
                    <button wire:click="$set('showModal', false)"
                        style="background:none;border:none;color:var(--text-muted);cursor:pointer">
                        <i data-lucide="x" style="width:20px;height:20px"></i>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Código Interno *</label>
                            <input type="text" wire:model="codigo_interno" class="form-input" placeholder="PROD-001">
                            @error('codigo_interno') <span
                            style="color:var(--danger);font-size:0.75rem">{{ $message }}</span> @enderror
                        </div>
                        <div class="form-group">
                            <label class="form-label">Código de Barras</label>
                            <input type="text" wire:model="codigo_barras" class="form-input" placeholder="7751234567890">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Nombre *</label>
                        <input type="text" wire:model="nombre" class="form-input" placeholder="Nombre del producto">
                        @error('nombre') <span style="color:var(--danger);font-size:0.75rem">{{ $message }}</span> @enderror
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Tipo</label>
                            <select wire:model="tipo_producto" class="form-select">
                                <option value="bien">Bien</option>
                                <option value="servicio">Servicio</option>
                                <option value="combo">Combo</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Categoría</label>
                            <select wire:model="categoria_id" class="form-select">
                                <option value="0">— Sin categoría —</option>
                                @foreach($categorias as $cat)
                                    <option value="{{ $cat->id }}">{{ $cat->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Marca</label>
                            <select wire:model="marca_id" class="form-select">
                                <option value="0">— Sin marca —</option>
                                @foreach($marcas as $marca)
                                    <option value="{{ $marca->id }}">{{ $marca->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Precio Venta (S/) *</label>
                            <input type="number" wire:model="precio_venta" class="form-input" step="0.01" min="0">
                            @error('precio_venta') <span style="color:var(--danger);font-size:0.75rem">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label class="form-label">Precio Compra (S/)</label>
                            <input type="number" wire:model="precio_compra" class="form-input" step="0.01" min="0">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Stock Mínimo</label>
                            <input type="number" wire:model="stock_minimo" class="form-input" step="1" min="0">
                        </div>
                    </div>

                    <div
                        style="display:flex;gap:20px;flex-wrap:wrap;margin-top:12px;padding-top:16px;border-top:1px solid var(--border-color)">
                        <label
                            style="display:flex;align-items:center;gap:8px;cursor:pointer;color:var(--text-secondary);font-size:0.85rem">
                            <input type="checkbox" wire:model="es_gravado_igv"> Gravado IGV
                        </label>
                        <label
                            style="display:flex;align-items:center;gap:8px;cursor:pointer;color:var(--text-secondary);font-size:0.85rem">
                            <input type="checkbox" wire:model="controla_lote"> Controla Lote
                        </label>
                        <label
                            style="display:flex;align-items:center;gap:8px;cursor:pointer;color:var(--text-secondary);font-size:0.85rem">
                            <input type="checkbox" wire:model="controla_serie"> Controla Serie
                        </label>
                        <label
                            style="display:flex;align-items:center;gap:8px;cursor:pointer;color:var(--text-secondary);font-size:0.85rem">
                            <input type="checkbox" wire:model="disponible_pos"> Disponible POS
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button wire:click="$set('showModal', false)" class="btn btn-secondary">Cancelar</button>
                    <button wire:click="save" class="btn btn-primary">
                        <i data-lucide="save"></i> {{ $editMode ? 'Actualizar' : 'Guardar' }}
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>