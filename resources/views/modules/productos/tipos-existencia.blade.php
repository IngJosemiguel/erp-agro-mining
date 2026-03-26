@extends('layouts.app')

@section('title', 'Tipos de Existencia')

@section('breadcrumb')
    <a href="{{ route('dashboard') }}">Dashboard</a>
    <i data-lucide="chevron-right" style="width:14px;height:14px;color:var(--text-muted)"></i>
    <a href="{{ route('productos.index') }}">Productos</a>
    <i data-lucide="chevron-right" style="width:14px;height:14px;color:var(--text-muted)"></i>
    <span style="color:var(--text-primary)">Tipos de Existencia</span>
@endsection

@section('content')
    <div class="page-header">
        <div>
            <h1 class="page-title">Tipos de Existencia</h1>
            <p class="page-subtitle">Catálogo SUNAT — Tabla 5 Tipos de Existencia</p>
        </div>
    </div>

    @livewire('tipos-existencia-table')
@endsection

@push('scripts')
    <script>lucide.createIcons();</script>
@endpush