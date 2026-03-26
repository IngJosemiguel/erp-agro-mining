@extends('layouts.app')
@section('title', 'Devolución a Proveedor')
@section('breadcrumb')
    <a href="{{ route('dashboard') }}">Dashboard</a>
    <i data-lucide="chevron-right" style="width:14px;height:14px;color:var(--text-muted)"></i>
    <span style="color:var(--text-primary)">Devolución a Proveedor</span>
@endsection
@section('content')
    <div class="page-header">
        <div>
            <h1 class="page-title">Devolución a Proveedor</h1>
            <p class="page-subtitle">Registro de mercadería devuelta — El stock se descuenta automáticamente del almacén</p>
        </div>
    </div>
    @livewire('devolucion-proveedor-component')
@endsection
@push('scripts')
<script>lucide.createIcons();</script>@endpush