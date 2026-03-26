@extends('layouts.app')
@section('title', 'Cotización a Proveedor')
@section('breadcrumb')<a href="{{ route('dashboard') }}">Dashboard</a><i data-lucide="chevron-right"
    style="width:14px;height:14px;color:var(--text-muted)"></i><span style="color:var(--text-primary)">Cotización
Proveedor</span>@endsection
@section('content')
    <div class="page-header">
        <div>
            <h1 class="page-title">Cotización a Proveedor</h1>
            <p class="page-subtitle">Solicitudes de cotización enviadas a proveedores para comparar precios</p>
        </div>
    </div>
    @livewire('cotizacion-proveedor')
@endsection
@push('scripts')
    <script>lucide.createIcons();</script>
@endpush