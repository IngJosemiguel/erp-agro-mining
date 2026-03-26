@extends('layouts.app')

@section('title', 'Tipos de Clientes')

@section('breadcrumb')
    <a href="{{ route('dashboard') }}">Dashboard</a>
    <i data-lucide="chevron-right" style="width:14px;height:14px;color:var(--text-muted)"></i>
    <a href="{{ route('clientes.index') }}">Clientes</a>
    <i data-lucide="chevron-right" style="width:14px;height:14px;color:var(--text-muted)"></i>
    <span style="color:var(--text-primary)">Tipos de Clientes</span>
@endsection

@section('content')
    <div class="page-header">
        <div>
            <h1 class="page-title">Tipos de Clientes</h1>
            <p class="page-subtitle">Clasificación de clientes con descuento por defecto</p>
        </div>
    </div>

    @livewire('tipos-cliente-table')
@endsection

@push('scripts')
    <script>lucide.createIcons();</script>
@endpush