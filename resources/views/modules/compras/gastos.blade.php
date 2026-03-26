@extends('layouts.app')

@section('title', 'Gastos Diversos')

@section('breadcrumb')
    <a href="{{ route('dashboard') }}">Dashboard</a>
    <i data-lucide="chevron-right" style="width:14px;height:14px;color:var(--text-muted)"></i>
    <span style="color:var(--text-primary)">Gastos Diversos</span>
@endsection

@section('content')
    <div class="page-header">
        <div>
            <h1 class="page-title">Gastos Diversos</h1>
            <p class="page-subtitle">Control de gastos operativos, servicios y suministros</p>
        </div>
    </div>

    @livewire('gastos-listado')
@endsection

@push('scripts')
    <script>lucide.createIcons();</script>
@endpush