@extends('layouts.app')
@section('title', 'Cotizaciones')
@section('breadcrumb')<a href="{{ route('dashboard') }}">Dashboard</a><i data-lucide="chevron-right"
    style="width:14px;height:14px;color:var(--text-muted)"></i><span
style="color:var(--text-primary)">Cotizaciones</span>@endsection
@section('content')
    <div class="page-header">
        <div>
            <h1 class="page-title">Cotizaciones</h1>
            <p class="page-subtitle">Proformas y cotizaciones enviadas a clientes</p>
        </div>
    </div>
    @livewire('cotizaciones-listado')
@endsection
@push('scripts')
<script>lucide.createIcons();</script>@endpush