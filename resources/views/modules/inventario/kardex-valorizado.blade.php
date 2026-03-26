@extends('layouts.app')
@section('title', 'Kardex Valorizado')
@section('breadcrumb')<a href="{{ route('dashboard') }}">Dashboard</a><i data-lucide="chevron-right"
    style="width:14px;height:14px;color:var(--text-muted)"></i><span style="color:var(--text-primary)">Kardex
Valorizado</span>@endsection
@section('content')
    <div class="page-header">
        <div>
            <h1 class="page-title">Kardex Valorizado</h1>
            <p class="page-subtitle">Movimientos de inventario con costos y saldos valorizados en tiempo real</p>
        </div>
    </div>
    @livewire('kardex-valorizado')
@endsection
@push('scripts')
<script>lucide.createIcons();</script>@endpush