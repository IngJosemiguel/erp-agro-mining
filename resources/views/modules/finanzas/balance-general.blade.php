@extends('layouts.app')
@section('title', 'Balance General')

@section('breadcrumb')
    <a href="{{ route('dashboard') }}">Dashboard</a>
    <i data-lucide="chevron-right" style="width:14px;height:14px;color:var(--text-muted)"></i>
    <span>Finanzas / Contabilidad</span>
    <i data-lucide="chevron-right" style="width:14px;height:14px;color:var(--text-muted)"></i>
    <span style="color:var(--text-primary)">Balance General</span>
@endsection

@section('content')
    <div class="page-header">
        <div>
            <h1 class="page-title">Balance General</h1>
            <p class="page-subtitle">Estado de Situación Financiera — calculado automáticamente</p>
        </div>
    </div>

    @livewire('finanzas.balance-general')
@endsection

@push('scripts')
<script>lucide.createIcons();</script>@endpush