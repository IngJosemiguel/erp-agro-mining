@extends('layouts.app')
@section('title', 'Órdenes de Pedido')
@section('breadcrumb')<a href="{{ route('dashboard') }}">Dashboard</a><i data-lucide="chevron-right"
    style="width:14px;height:14px;color:var(--text-muted)"></i><span style="color:var(--text-primary)">Órdenes de
Pedido</span>@endsection
@section('content')
    <div class="page-header">
        <div>
            <h1 class="page-title">Órdenes de Pedido</h1>
            <p class="page-subtitle">Solicitudes de compra de clientes previas a facturación</p>
        </div>
    </div>
    @livewire('ordenes-pedido')
@endsection
@push('scripts')
<script>lucide.createIcons();</script>@endpush