@extends('layouts.app')

@section('title', 'Buscador de Productos')

@section('breadcrumb')
    <a href="{{ route('dashboard') }}">Dashboard</a>
    <i data-lucide="chevron-right" style="width:14px;height:14px;color:var(--text-muted)"></i>
    <a href="{{ route('productos.index') }}">Productos</a>
    <i data-lucide="chevron-right" style="width:14px;height:14px;color:var(--text-muted)"></i>
    <span style="color:var(--text-primary)">Buscador</span>
@endsection

@section('content')
    <div class="page-header">
        <div>
            <h1 class="page-title">Buscador Avanzado</h1>
            <p class="page-subtitle">Búsqueda rápida por SKU, código de barras o nombre</p>
        </div>
    </div>

    <div class="card" style="padding:28px;">
        <div style="display:flex;gap:12px;margin-bottom:24px;">
            <div style="position:relative;flex:1;">
                <i data-lucide="search" style="position:absolute;left:14px;top:50%;transform:translateY(-50%);width:18px;height:18px;color:var(--text-muted);"></i>
                <input id="buscador-input" type="text" placeholder="Escribe nombre, SKU o código de barras..."
                    style="width:100%;padding:12px 16px 12px 44px;background:var(--bg-body);border:1px solid var(--border-color);border-radius:10px;color:var(--text-primary);font-size:0.95rem;"
                    oninput="buscarProductos(this.value)">
            </div>
        </div>
        <div id="buscador-resultados" style="min-height:200px;">
            <div style="text-align:center;padding:60px 20px;color:var(--text-muted);">
                <i data-lucide="search" style="width:48px;height:48px;display:block;margin:0 auto 12px;opacity:.3;"></i>
                <p style="font-weight:600;margin:0;">Escribe al menos 2 caracteres para buscar</p>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
lucide.createIcons();
let _timer = null;
function buscarProductos(q) {
    clearTimeout(_timer);
    const el = document.getElementById('buscador-resultados');
    if (q.length < 2) {
        el.innerHTML = `<div style="text-align:center;padding:60px 20px;color:var(--text-muted);"><p style="font-weight:600;margin:0;">Escribe al menos 2 caracteres para buscar</p></div>`;
        return;
    }
    el.innerHTML = `<div style="text-align:center;padding:40px;color:var(--text-muted);">Buscando...</div>`;
    _timer = setTimeout(() => {
        fetch(`/api/productos/buscar?q=${encodeURIComponent(q)}`, {headers:{'X-Requested-With':'XMLHttpRequest','Accept':'application/json'}})
            .then(r => r.json())
            .then(data => {
                if (!data.length) { el.innerHTML = `<div style="text-align:center;padding:60px;color:var(--text-muted);">Sin resultados para "<strong>${q}</strong>"</div>`; return; }
                el.innerHTML = `<table style="width:100%;border-collapse:collapse;">
                    <thead><tr style="background:var(--bg-body);">
                        <th style="padding:10px 14px;text-align:left;font-size:.75rem;color:var(--text-muted);font-weight:700;text-transform:uppercase;border-bottom:1px solid var(--border-color);">Producto</th>
                        <th style="padding:10px 14px;text-align:left;font-size:.75rem;color:var(--text-muted);font-weight:700;text-transform:uppercase;border-bottom:1px solid var(--border-color);">SKU</th>
                        <th style="padding:10px 14px;text-align:right;font-size:.75rem;color:var(--text-muted);font-weight:700;text-transform:uppercase;border-bottom:1px solid var(--border-color);">Precio</th>
                        <th style="padding:10px 14px;text-align:right;font-size:.75rem;color:var(--text-muted);font-weight:700;text-transform:uppercase;border-bottom:1px solid var(--border-color);">Stock</th>
                    </tr></thead>
                    <tbody>${data.map(p=>`<tr style="border-bottom:1px solid var(--border-color);">
                        <td style="padding:10px 14px;font-weight:600;color:var(--text-primary);">${p.nombre}</td>
                        <td style="padding:10px 14px;font-size:.8rem;color:var(--text-muted);font-family:monospace;">${p.codigo_interno??'-'}</td>
                        <td style="padding:10px 14px;text-align:right;font-weight:700;color:var(--accent);">S/ ${parseFloat(p.precio_venta).toFixed(2)}</td>
                        <td style="padding:10px 14px;text-align:right;color:var(--text-muted);">${p.stock??0}</td>
                    </tr>`).join('')}</tbody></table>`;
            }).catch(()=>{ el.innerHTML=`<div style="text-align:center;padding:60px;color:#ef4444;">Error al buscar productos.</div>`; });
    }, 350);
}
</script>
@endpush