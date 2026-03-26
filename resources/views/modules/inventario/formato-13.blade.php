@extends('layouts.app')
@section('title', 'Formato 13.1 SUNAT')
@section('breadcrumb')<a href="{{ route('dashboard') }}">Dashboard</a><i data-lucide="chevron-right"
    style="width:14px;height:14px;color:var(--text-muted)"></i><span style="color:var(--text-primary)">Formato 13
SUNAT</span>@endsection
@section('content')
    <div class="page-header">
        <div>
            <h1 class="page-title">Formato 13.1 SUNAT</h1>
            <p class="page-subtitle">Inventario valorizado al cierre del período para declaración anual</p>
        </div>
    </div>
    @livewire('formato13-sunat')
@endsection
@push('scripts')
    <script>lucide.createIcons();</script>
@endpush