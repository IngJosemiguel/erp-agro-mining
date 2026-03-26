@extends('layouts.app')
@section('title', 'Presupuesto Contable')
@section('breadcrumb')
    <a href="{{ route('dashboard') }}">Dashboard</a>
    <i data-lucide="chevron-right" style="width:14px;height:14px;color:var(--text-muted)"></i>
    <span>Finanzas / Contabilidad</span>
    <i data-lucide="chevron-right" style="width:14px;height:14px;color:var(--text-muted)"></i>
    <span style="color:var(--text-primary)">Presupuesto</span>
@endsection
@section('content')
    <div class="page-header">
        <div>
            <h1 class="page-title">Control Presupuestario</h1>
            <p class="page-subtitle">Compara el presupuesto planificado vs lo ejecutado en asientos contables reales</p>
        </div>
    </div>
    @livewire('finanzas.presupuesto')
@endsection
@push('scripts')
<script>lucide.createIcons();</script>@endpush