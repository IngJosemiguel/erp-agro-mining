@extends('layouts.app')
@section('title', 'Liquidaciones de Compra')
@section('breadcrumb')<a href="{{ route('dashboard') }}">Dashboard</a><i data-lucide="chevron-right"
    style="width:14px;height:14px;color:var(--text-muted)"></i><span style="color:var(--text-primary)">Liquidaciones de
Compra</span>@endsection
@section('content')
    <div class="page-header">
        <div>
            <h1 class="page-title">Liquidaciones de Compra</h1>
            <p class="page-subtitle">Liquidaciones emitidas a proveedores no habituales (productos agropecuarios)</p>
        </div>
    </div>
    @livewire('liquidaciones-listado')
@endsection
@push('scripts')
<script>lucide.createIcons();</script>@endpush