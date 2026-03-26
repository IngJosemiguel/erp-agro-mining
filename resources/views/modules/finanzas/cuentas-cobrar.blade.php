@extends('layouts.app')
@section('title', 'Cuentas por Cobrar')
@section('breadcrumb')
    <a href="{{ route('dashboard') }}">Dashboard</a>
    <i data-lucide="chevron-right" style="width:14px;height:14px;color:var(--text-muted)"></i>
    <span>Finanzas</span>
    <i data-lucide="chevron-right" style="width:14px;height:14px;color:var(--text-muted)"></i>
    <span style="color:var(--text-primary)">Cuentas por Cobrar</span>
@endsection
@section('content')
    <div class="page-header">
        <div>
            <h1 class="page-title">Cuentas por Cobrar</h1>
            <p class="page-subtitle">Ventas a crédito con saldo pendiente</p>
        </div>
    </div>
    @livewire('finanzas.cuentas-cobrar')
@endsection
@push('scripts')
<script>lucide.createIcons();</script>@endpush