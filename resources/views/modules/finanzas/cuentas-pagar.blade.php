@extends('layouts.app')
@section('title', 'Cuentas por Pagar')
@section('breadcrumb')
    <a href="{{ route('dashboard') }}">Dashboard</a>
    <i data-lucide="chevron-right" style="width:14px;height:14px;color:var(--text-muted)"></i>
    <span>Finanzas</span>
    <i data-lucide="chevron-right" style="width:14px;height:14px;color:var(--text-muted)"></i>
    <span style="color:var(--text-primary)">Cuentas por Pagar</span>
@endsection
@section('content')
    <div class="page-header">
        <div>
            <h1 class="page-title">Cuentas por Pagar</h1>
            <p class="page-subtitle">Compras a crédito con deuda pendiente al proveedor</p>
        </div>
    </div>
    @livewire('finanzas.cuentas-pagar')
@endsection
@push('scripts')
<script>lucide.createIcons();</script>@endpush