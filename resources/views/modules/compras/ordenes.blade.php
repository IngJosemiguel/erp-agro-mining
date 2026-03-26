@extends('layouts.app')

@section('title', 'Órdenes de Compra')

@section('breadcrumb')
    <a href="{{ route('dashboard') }}">Dashboard</a>
    <i data-lucide="chevron-right" style="width:14px;height:14px;color:var(--text-muted)"></i>
    <span style="color:var(--text-primary)">Órdenes de Compra</span>
@endsection

@section('content')
    <div class="page-header">
        <div>
            <h1 class="page-title">Órdenes de Compra</h1>
            <p class="page-subtitle">Gestión de órdenes de compra a proveedores — Aprobación y seguimiento</p>
        </div>
    </div>

    @livewire('ordenes-compra-listado')
@endsection

@push('scripts')
    <script>lucide.createIcons();</script>
@endpush