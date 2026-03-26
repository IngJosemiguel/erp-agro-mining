@extends('layouts.app')

@section('title', 'Movimientos de Inventario')

@section('breadcrumb')
    <a href="{{ route('dashboard') }}">Dashboard</a>
    <i data-lucide="chevron-right" style="width:14px;height:14px;color:var(--text-muted)"></i>
    <span style="color:var(--text-primary)">Movimientos de Inventario</span>
@endsection

@section('content')
    <div class="page-header">
        <div>
            <h1 class="page-title">Movimientos de Inventario</h1>
            <p class="page-subtitle">Registro de ingresos y salidas de mercadería — Kardex en tiempo real</p>
        </div>
    </div>

    @livewire('movimientos-inventario')
@endsection

@push('scripts')
    <script>lucide.createIcons();</script>
@endpush