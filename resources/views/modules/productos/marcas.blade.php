@extends('layouts.app')

@section('title', 'Marcas')

@section('breadcrumb')
    <a href="{{ route('dashboard') }}">Dashboard</a>
    <i data-lucide="chevron-right" style="width:14px;height:14px;color:var(--text-muted)"></i>
    <a href="{{ route('productos.index') }}">Productos</a>
    <i data-lucide="chevron-right" style="width:14px;height:14px;color:var(--text-muted)"></i>
    <span style="color:var(--text-primary)">Marcas</span>
@endsection

@section('content')
    <div class="page-header">
        <div>
            <h1 class="page-title">Marcas</h1>
            <p class="page-subtitle">Gestión de marcas y fabricantes</p>
        </div>
    </div>

    @livewire('marcas-table')
@endsection

@push('scripts')
    <script>lucide.createIcons();</script>
@endpush