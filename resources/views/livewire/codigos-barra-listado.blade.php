<div>
    {{-- HEADER --}}
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;flex-wrap:wrap;gap:12px;">
        <div>
            <h1 style="font-size:1.6rem;font-weight:800;margin:0;display:flex;align-items:center;gap:10px;">
                <span style="width:40px;height:40px;border-radius:10px;background:linear-gradient(135deg,#6366f1,#8b5cf6);display:flex;align-items:center;justify-content:center;">
                    <i data-lucide="barcode" style="width:20px;height:20px;color:#fff;"></i>
                </span>
                Códigos de Barra
            </h1>
            <p style="color:var(--text-muted);font-size:.85rem;margin:4px 0 0 50px;">
                Asigna y genera etiquetas de códigos de barra para tus productos
            </p>
        </div>
        @if(count($seleccionados) > 0)
        <div style="display:flex;gap:10px;align-items:center;">
            <span style="font-size:.85rem;color:var(--text-muted);">{{ count($seleccionados) }} seleccionado(s)</span>
            <div style="display:flex;gap:8px;align-items:center;background:var(--bg-body);border:1px solid var(--border-color);border-radius:8px;padding:6px 12px;">
                <label style="font-size:.8rem;color:var(--text-muted);">Copias:</label>
                <input wire:model="copias" type="number" min="1" max="50" style="width:50px;background:transparent;border:none;color:var(--text-primary);font-weight:700;text-align:center;">
                <label style="font-size:.8rem;color:var(--text-muted);">Formato:</label>
                <select wire:model="formato" style="background:transparent;border:none;color:var(--text-primary);font-size:.8rem;font-weight:600;">
                    <option value="CODE128">CODE128</option>
                    <option value="EAN13">EAN-13</option>
                    <option value="QR">QR Code</option>
                </select>
            </div>
            <button onclick="imprimirEtiquetas()" style="padding:9px 18px;background:linear-gradient(135deg,#6366f1,#8b5cf6);border:none;border-radius:8px;color:#fff;font-weight:700;cursor:pointer;display:flex;align-items:center;gap:6px;">
                <i data-lucide="printer" style="width:16px;height:16px;"></i> Imprimir etiquetas
            </button>
            <button wire:click="limpiarSeleccion" style="padding:9px 14px;background:var(--bg-body);border:1px solid var(--border-color);border-radius:8px;color:var(--text-muted);cursor:pointer;">
                Limpiar
            </button>
        </div>
        @endif
    </div>

    {{-- FILTROS --}}
    <div class="card" style="padding:14px 18px;margin-bottom:16px;display:flex;gap:12px;align-items:center;">
        <div style="position:relative;flex:1;">
            <i data-lucide="search" style="position:absolute;left:12px;top:50%;transform:translateY(-50%);width:16px;height:16px;color:var(--text-muted);"></i>
            <input wire:model.live.debounce.300ms="buscar" type="text"
                placeholder="Buscar por nombre, SKU o código de barras..."
                style="width:100%;padding:9px 14px 9px 38px;background:var(--bg-body);border:1px solid var(--border-color);border-radius:8px;color:var(--text-primary);">
        </div>
        <button wire:click="seleccionarTodos" style="padding:9px 16px;background:var(--bg-body);border:1px solid var(--border-color);border-radius:8px;color:var(--text-secondary);font-size:.85rem;cursor:pointer;white-space:nowrap;display:flex;align-items:center;gap:6px;">
            <i data-lucide="check-square" style="width:15px;height:15px;"></i> Seleccionar todos
        </button>
    </div>

    {{-- TABLA --}}
    <div class="card" style="overflow:hidden;">
        <table style="width:100%;border-collapse:collapse;">
            <thead>
                <tr style="background:rgba(0,0,0,.2);">
                    <th style="padding:12px 16px;width:40px;"></th>
                    <th style="padding:12px 16px;text-align:left;font-size:.73rem;font-weight:700;color:var(--text-muted);text-transform:uppercase;">Producto</th>
                    <th style="padding:12px 16px;text-align:left;font-size:.73rem;font-weight:700;color:var(--text-muted);text-transform:uppercase;">SKU</th>
                    <th style="padding:12px 16px;text-align:left;font-size:.73rem;font-weight:700;color:var(--text-muted);text-transform:uppercase;">Código de Barras</th>
                    <th style="padding:12px 16px;text-align:right;font-size:.73rem;font-weight:700;color:var(--text-muted);text-transform:uppercase;">Precio</th>
                    <th style="padding:12px 16px;text-align:center;font-size:.73rem;font-weight:700;color:var(--text-muted);text-transform:uppercase;">Preview</th>
                </tr>
            </thead>
            <tbody>
                @forelse($productos as $prod)
                <tr style="border-bottom:1px solid var(--border-color);transition:.15s;{{ in_array($prod->id, $seleccionados) ? 'background:rgba(99,102,241,.06);' : '' }}"
                    onmouseover="this.style.background='{{ in_array($prod->id, $seleccionados) ? 'rgba(99,102,241,.1)' : 'rgba(255,255,255,.02)' }}'"
                    onmouseout="this.style.background='{{ in_array($prod->id, $seleccionados) ? 'rgba(99,102,241,.06)' : '' }}'">
                    <td style="padding:12px 16px;text-align:center;">
                        <input type="checkbox"
                            wire:click="toggleSeleccion({{ $prod->id }})"
                            {{ in_array($prod->id, $seleccionados) ? 'checked' : '' }}
                            style="width:16px;height:16px;accent-color:#6366f1;cursor:pointer;">
                    </td>
                    <td style="padding:12px 16px;">
                        <div style="font-weight:700;font-size:.9rem;color:var(--text-primary);">{{ $prod->nombre }}</div>
                        <div style="font-size:.73rem;color:var(--text-muted);">{{ $prod->unidadMedida->abreviatura ?? '-' }} · {{ $prod->tipo_producto }}</div>
                    </td>
                    <td style="padding:12px 16px;font-size:.8rem;color:var(--text-muted);font-family:monospace;">{{ $prod->codigo_interno }}</td>
                    <td style="padding:12px 16px;">
                        <input type="text"
                            id="barcode-{{ $prod->id }}"
                            value="{{ $prod->codigo_barras ?? '' }}"
                            placeholder="Sin código asignado"
                            style="background:var(--bg-body);border:1px solid var(--border-color);border-radius:6px;padding:6px 10px;color:var(--text-primary);font-size:.85rem;font-family:monospace;width:200px;"
                            onblur="guardarCodigo({{ $prod->id }}, this.value)"
                            onkeydown="if(event.key==='Enter') guardarCodigo({{ $prod->id }}, this.value)">
                    </td>
                    <td style="padding:12px 16px;text-align:right;font-weight:800;color:var(--accent);">S/ {{ number_format((float) $prod->precio_venta, 2) }}</td>
                    <td style="padding:12px 16px;text-align:center;">
                        @if($prod->codigo_barras)
                        <canvas id="canvas-{{ $prod->id }}" style="max-height:40px;"></canvas>
                        @else
                        <span style="font-size:.73rem;color:var(--text-muted);">—</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" style="padding:60px;text-align:center;color:var(--text-muted);">
                        <i data-lucide="barcode" style="width:48px;height:48px;display:block;margin:0 auto 12px;opacity:.3;"></i>
                        <p style="font-weight:700;margin:0 0 6px;">Sin productos</p>
                        <span style="font-size:.85rem;">Agrega productos para gestionar sus códigos</span>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
        @if($productos->hasPages())
        <div style="padding:16px 18px;border-top:1px solid var(--border-color);">
            {{ $productos->links() }}
        </div>
        @endif
    </div>

    {{-- Área de impresión oculta --}}
    <div id="print-area" style="display:none;"></div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.6/dist/JsBarcode.all.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>
<script>
// Renderizar barcodes en canvas de la tabla
function renderBarcodes() {
    document.querySelectorAll('[id^="canvas-"]').forEach(canvas => {
        const id = canvas.id.replace('canvas-', '');
        const input = document.getElementById('barcode-' + id);
        const code = input ? input.value : '';
        if (!code) return;
        try {
            JsBarcode(canvas, code, {
                format: 'CODE128',
                height: 36,
                displayValue: false,
                margin: 2,
                lineColor: '#ffffff',
                background: 'transparent',
            });
        } catch(e) { /* código inválido */ }
    });
}

// Guardar código de barras via Livewire
function guardarCodigo(id, valor) {
    @this.call('guardarCodigoBarras', id, valor).then(() => {
        setTimeout(renderBarcodes, 300);
    });
}

// Imprimir etiquetas seleccionadas
function imprimirEtiquetas() {
    const seleccionados = @json($seleccionados);
    const copias = parseInt(@this.copias) || 1;
    const formato = @this.formato;

    if (!seleccionados.length) { alert('Selecciona al menos un producto.'); return; }

    let html = `
        <html><head><title>Etiquetas de códigos de barra</title>
        <style>
            body { margin: 0; font-family: Arial, sans-serif; }
            .etiqueta { display: inline-block; width: 160px; padding: 8px; margin: 4px; border: 1px dashed #ccc; text-align: center; page-break-inside: avoid; }
            .etiqueta .nombre { font-size: 9px; font-weight: bold; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; max-width: 140px; }
            .etiqueta .precio { font-size: 11px; font-weight: 900; color: #000; }
            .etiqueta .sku { font-size: 7px; color: #666; font-family: monospace; }
            svg, canvas { max-width: 100%; }
            @media print { @page { size: A4; margin: 10mm; } }
        </style>
        <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.6/dist/JsBarcode.all.min.js"><\/script>
        </head><body><div id="contenedor">`;

    seleccionados.forEach(id => {
        const input = document.getElementById('barcode-' + id);
        const nombre = input ? input.closest('tr').querySelector('td:nth-child(2) div:first-child')?.textContent.trim() : 'Producto';
        const precio = input ? input.closest('tr').querySelector('td:nth-child(5)')?.textContent.trim() : '';
        const codigo = input ? input.value : '';
        const sku = input ? input.closest('tr').querySelector('td:nth-child(3)')?.textContent.trim() : '';

        for (let c = 0; c < copias; c++) {
            html += `<div class="etiqueta">
                <div class="nombre">${nombre}</div>
                <svg id="bc-${id}-${c}"></svg>
                <div class="sku">${sku}${codigo ? ' | ' + codigo : ''}</div>
                <div class="precio">${precio}</div>
            </div>`;
        }
    });

    html += `</div>
        <script>
        document.querySelectorAll('[id^="bc-"]').forEach(svg => {
            const parts = svg.id.split('-');
            const id = parts[1];
            const inputs = document.querySelectorAll('[id]');
            // Usa el codigo del primer barcode de ese id
            try {
                JsBarcode(svg, svg.id, { format: 'CODE128', height: 40, displayValue: false, lineColor: '#000', background: '#fff' });
            } catch(e) {}
        });
        window.onload = () => setTimeout(() => window.print(), 600);
        <\/script></body></html>`;

    const w = window.open('', '_blank');
    w.document.write(html);
    w.document.close();
}

document.addEventListener('livewire:update', () => setTimeout(renderBarcodes, 100));
document.addEventListener('livewire:navigated', renderBarcodes);
document.addEventListener('DOMContentLoaded', () => setTimeout(renderBarcodes, 200));
</script>
@endpush
