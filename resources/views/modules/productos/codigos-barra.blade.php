@extends('layouts.app')

@section('title', 'Códigos de Barra')

@section('breadcrumb')
    <a href="{{ route('dashboard') }}">Dashboard</a>
    <i data-lucide="chevron-right" style="width:14px;height:14px;color:var(--text-muted)"></i>
    <a href="{{ route('productos.index') }}">Productos</a>
    <i data-lucide="chevron-right" style="width:14px;height:14px;color:var(--text-muted)"></i>
    <span style="color:var(--text-primary)">Códigos de Barra</span>
@endsection

@section('content')
    @livewire('codigos-barra-listado')
@endsection

@push('scripts')
    <script>lucide.createIcons();</script>
@endpush