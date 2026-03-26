@extends('layouts.app')

@section('title', 'Nueva Compra')

@section('breadcrumb')
    <a href="{{ route('dashboard') }}">Dashboard</a>
    <i data-lucide="chevron-right" style="width:14px;height:14px;color:var(--text-muted)"></i>
    <a href="{{ route('compras.listado') }}">Compras</a>
    <i data-lucide="chevron-right" style="width:14px;height:14px;color:var(--text-muted)"></i>
    <span style="color:var(--text-primary)">Nueva Compra</span>
@endsection

@section('content')
    <div class="page-header">
        <div>
            <h1 class="page-title">Nueva Compra</h1>
            <p class="page-subtitle">Registrar documento de compra e ingresar al almacén</p>
        </div>
    </div>

    @livewire('compras-nuevo')
@endsection

@push('scripts')
    <script>lucide.createIcons();</script>
@endpush