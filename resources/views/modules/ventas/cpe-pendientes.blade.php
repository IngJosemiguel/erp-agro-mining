@extends('layouts.app')
@section('title', 'CPE con Errores')
@section('breadcrumb')<a href="{{ route('dashboard') }}">Dashboard</a><i data-lucide="chevron-right"
    style="width:14px;height:14px;color:var(--text-muted)"></i><span style="color:var(--text-primary)">CPE
Pendientes</span>@endsection
@section('content')
    <div class="page-header">
        <div>
            <h1 class="page-title">CPE Pendientes / Con Errores</h1>
            <p class="page-subtitle">Comprobantes rechazados o con error en SUNAT — Requieren atención inmediata</p>
        </div>
    </div>
    @livewire('cpe-pendientes')
@endsection
@push('scripts')
<script>lucide.createIcons();</script>@endpush