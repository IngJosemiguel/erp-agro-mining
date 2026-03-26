@extends('layouts.app')

@section('title', $title)

@section('breadcrumb')
    <a href="{{ route('dashboard') }}">Dashboard</a>
    <i data-lucide="chevron-right" style="width:14px;height:14px;color:var(--text-muted)"></i>
    <span>{{ $module }}</span>
    <i data-lucide="chevron-right" style="width:14px;height:14px;color:var(--text-muted)"></i>
    <span style="color:var(--text-primary)">{{ $title }}</span>
@endsection

@section('content')
    <div class="page-header">
        <div>
            <h1 class="page-title">{{ $title }}</h1>
            <p class="page-subtitle">Módulo {{ $module }} — En desarrollo</p>
        </div>
        <div style="display:flex;gap:10px">
            <button class="btn btn-primary">
                <i data-lucide="plus"></i> Nuevo
            </button>
            <button class="btn btn-secondary">
                <i data-lucide="download"></i> Exportar
            </button>
        </div>
    </div>

    <div class="card" style="text-align:center;padding:80px 40px;">
        <div style="display:flex;flex-direction:column;align-items:center;gap:16px;">
            <div
                style="width:80px;height:80px;background:linear-gradient(135deg,rgba(16,185,129,0.1),rgba(245,158,11,0.1));border-radius:20px;display:flex;align-items:center;justify-content:center;">
                <i data-lucide="construction" style="width:40px;height:40px;color:var(--accent-500)"></i>
            </div>
            <h2 style="font-size:1.3rem;font-weight:700;color:var(--text-primary)">{{ $title }}</h2>
            <p style="color:var(--text-muted);max-width:500px;line-height:1.8">
                Este módulo está listo para ser implementado con un componente Livewire dedicado.
                La estructura de datos, APIs y rutas ya están preparadas en el backend.
            </p>
            <div style="display:flex;gap:8px;margin-top:8px">
                <span class="badge badge-success">Backend ✓</span>
                <span class="badge badge-success">API ✓</span>
                <span class="badge badge-info">Frontend en progreso</span>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>lucide.createIcons();</script>
@endpush