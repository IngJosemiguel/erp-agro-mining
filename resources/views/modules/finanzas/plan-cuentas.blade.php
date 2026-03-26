@extends('layouts.app')
@section('title', 'Plan de Cuentas')

@section('breadcrumb')
    <a href="{{ route('dashboard') }}">Dashboard</a>
    <i data-lucide="chevron-right" style="width:14px;height:14px;color:var(--text-muted)"></i>
    <span>Finanzas</span>
    <i data-lucide="chevron-right" style="width:14px;height:14px;color:var(--text-muted)"></i>
    <span style="color:var(--text-primary)">Plan de Cuentas</span>
@endsection

@section('content')
    <div class="page-header">
        <div>
            <h1 class="page-title">Plan de Cuentas</h1>
            <p class="page-subtitle">Catálogo contable estructurado por tipo</p>
        </div>
    </div>

    @livewire('finanzas.plan-cuentas')
@endsection

@push('scripts')
<script>lucide.createIcons();</script>@endpush