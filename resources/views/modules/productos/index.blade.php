@extends('layouts.app')

@section('title', 'Productos y Servicios')

@section('breadcrumb')
    <a href="{{ route('dashboard') }}">Dashboard</a>
    <i data-lucide="chevron-right" style="width:14px;height:14px;color:var(--text-muted)"></i>
    <span style="color:var(--text-primary)">Productos / Servicios</span>
@endsection

@section('content')
    <div class="page-header">
        <div>
            <h1 class="page-title">Productos y Servicios</h1>
            <p class="page-subtitle">Gestión completa del catálogo de productos, bienes y servicios</p>
        </div>
    </div>

    @livewire('productos-table')
@endsection

@push('scripts')
    <script>lucide.createIcons();</script>
@endpush