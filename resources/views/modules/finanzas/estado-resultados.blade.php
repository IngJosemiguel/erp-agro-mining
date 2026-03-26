@extends('layouts.app')
@section('title', 'Estado de Resultados')
@section('breadcrumb')
    <a href="{{ route('dashboard') }}">Dashboard</a>
    <i data-lucide="chevron-right" style="width:14px;height:14px;color:var(--text-muted)"></i>
    <span>Finanzas / Contabilidad</span>
    <i data-lucide="chevron-right" style="width:14px;height:14px;color:var(--text-muted)"></i>
    <span style="color:var(--text-primary)">Estado de Resultados</span>
@endsection
@section('content')
    <div class="page-header">
        <div>
            <h1 class="page-title">Estado de Resultados</h1>
            <p class="page-subtitle">Pérdidas y Ganancias — calculado desde asientos validados</p>
        </div>
    </div>
    @livewire('finanzas.estado-resultados')
@endsection
@push('scripts')
<script>lucide.createIcons();</script>@endpush