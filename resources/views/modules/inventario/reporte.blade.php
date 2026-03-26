@extends('layouts.app')

@section('title', 'Reporte de Inventario')

@section('breadcrumb')
    <a href="{{ route('dashboard') }}">Dashboard</a>
    <i data-lucide="chevron-right" style="width:14px;height:14px;color:var(--text-muted)"></i>
    <span style="color:var(--text-primary)">Reporte de Inventario</span>
@endsection

@section('content')
    <div class="page-header">
        <div>
            <h1 class="page-title">Reporte de Inventario</h1>
            <p class="page-subtitle">Stock actual por almacén — Valorizado con costo promedio ponderado</p>
        </div>
    </div>

    @livewire('reporte-inventario')
@endsection

@push('scripts')
    <script>lucide.createIcons();</script>
@endpush