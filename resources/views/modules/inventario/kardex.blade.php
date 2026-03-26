@extends('layouts.app')

@section('title', 'Reporte Kardex')

@section('breadcrumb')
    <a href="{{ route('dashboard') }}">Dashboard</a>
    <i data-lucide="chevron-right" style="width:14px;height:14px;color:var(--text-muted)"></i>
    <span style="color:var(--text-primary)">Kardex</span>
@endsection

@section('content')
    <div class="page-header">
        <div>
            <h1 class="page-title">Reporte Kardex</h1>
            <p class="page-subtitle">Kardex por producto — Movimientos, saldos y valorización</p>
        </div>
    </div>

    @livewire('kardex-reporte')
@endsection

@push('scripts')
    <script>lucide.createIcons();</script>
@endpush