@extends('layouts.app')
@section('title', 'Centros de Costo')
@section('breadcrumb')
    <a href="{{ route('dashboard') }}">Dashboard</a>
    <i data-lucide="chevron-right" style="width:14px;height:14px;color:var(--text-muted)"></i>
    <span>Finanzas / Contabilidad</span>
    <i data-lucide="chevron-right" style="width:14px;height:14px;color:var(--text-muted)"></i>
    <span style="color:var(--text-primary)">Centros de Costo</span>
@endsection
@section('content')
    <div class="page-header">
        <div>
            <h1 class="page-title">Cuentas y Centros de Costo</h1>
            <p class="page-subtitle">Define las divisiones, departamentos o proyectos para segmentar tus gastos operativos e
                ingresos.</p>
        </div>
    </div>
    @livewire('finanzas.centros-costo')
@endsection
@push('scripts')
<script>lucide.createIcons();</script>@endpush