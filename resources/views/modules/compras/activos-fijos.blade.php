@extends('layouts.app')
@section('title', 'Activos Fijos')
@section('breadcrumb')
    <a href="{{ route('dashboard') }}">Dashboard</a>
    <i data-lucide="chevron-right" style="width:14px;height:14px;color:var(--text-muted)"></i>
    <span style="color:var(--text-primary)">Activos Fijos</span>
@endsection
@section('content')
    <div class="page-header">
        <div>
            <h1 class="page-title">Activos Fijos</h1>
            <p class="page-subtitle">Registro y control de activos fijos — Maquinaria, vehículos, equipos y edificaciones
            </p>
        </div>
    </div>
    @livewire('activos-fijos')
@endsection
@push('scripts')
<script>lucide.createIcons();</script>@endpush