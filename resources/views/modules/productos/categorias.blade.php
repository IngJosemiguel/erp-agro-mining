@extends('layouts.app')

@section('title', 'Categorías')

@section('breadcrumb')
    <a href="{{ route('dashboard') }}">Dashboard</a>
    <i data-lucide="chevron-right" style="width:14px;height:14px;color:var(--text-muted)"></i>
    <a href="{{ route('productos.index') }}">Productos</a>
    <i data-lucide="chevron-right" style="width:14px;height:14px;color:var(--text-muted)"></i>
    <span style="color:var(--text-primary)">Categorías</span>
@endsection

@section('content')
    <div class="page-header">
        <div>
            <h1 class="page-title">Categorías</h1>
            <p class="page-subtitle">Administración de categorías de productos y servicios</p>
        </div>
    </div>

    @livewire('categorias-table')
@endsection

@push('scripts')
    <script>lucide.createIcons();</script>
@endpush