@extends('layouts.app')

@section('title', 'Conjuntos / Packs')

@section('breadcrumb')
    <a href="{{ route('dashboard') }}">Dashboard</a>
    <i data-lucide="chevron-right" style="width:14px;height:14px;color:var(--text-muted)"></i>
    <a href="{{ route('productos.index') }}">Productos</a>
    <i data-lucide="chevron-right" style="width:14px;height:14px;color:var(--text-muted)"></i>
    <span style="color:var(--text-primary)">Conjuntos / Packs</span>
@endsection

@section('content')
    @livewire('packs-listado')
@endsection

@push('scripts')
    <script>lucide.createIcons();</script>
@endpush