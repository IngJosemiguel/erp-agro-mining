@extends('layouts.app')

@section('title', 'Clientes')

@section('breadcrumb')
    <a href="{{ route('dashboard') }}">Dashboard</a>
    <i data-lucide="chevron-right" style="width:14px;height:14px;color:var(--text-muted)"></i>
    <span style="color:var(--text-primary)">Clientes</span>
@endsection

@section('content')
    <div class="page-header">
        <div>
            <h1 class="page-title">Clientes</h1>
            <p class="page-subtitle">Gestión de clientes, tipos y zonas</p>
        </div>
    </div>

    @livewire('clientes-table')
@endsection

@push('scripts')
    <script>lucide.createIcons();</script>
@endpush