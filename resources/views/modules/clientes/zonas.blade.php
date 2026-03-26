@extends('layouts.app')

@section('title', 'Zonas')

@section('breadcrumb')
    <a href="{{ route('dashboard') }}">Dashboard</a>
    <i data-lucide="chevron-right" style="width:14px;height:14px;color:var(--text-muted)"></i>
    <a href="{{ route('clientes.index') }}">Clientes</a>
    <i data-lucide="chevron-right" style="width:14px;height:14px;color:var(--text-muted)"></i>
    <span style="color:var(--text-primary)">Zonas</span>
@endsection

@section('content')
    <div class="page-header">
        <div>
            <h1 class="page-title">Zonas</h1>
            <p class="page-subtitle">Zonificación geográfica para clientes y despachos</p>
        </div>
    </div>

    @livewire('zonas-table')
@endsection

@push('scripts')
    <script>lucide.createIcons();</script>
@endpush