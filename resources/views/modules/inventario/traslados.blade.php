@extends('layouts.app')
@section('title', 'Traslados entre Almacenes')
@section('breadcrumb')
    <a href="{{ route('dashboard') }}">Dashboard</a>
    <i data-lucide="chevron-right" style="width:14px;height:14px;color:var(--text-muted)"></i>
    <span style="color:var(--text-primary)">Traslados</span>
@endsection
@section('content')
    <div class="page-header">
        <div>
            <h1 class="page-title">Traslados entre Almacenes</h1>
            <p class="page-subtitle">Movimiento de mercadería entre almacenes con trazabilidad en Kardex</p>
        </div>
    </div>
    @livewire('traslados-inventario')
@endsection
@push('scripts')
<script>lucide.createIcons();</script>@endpush