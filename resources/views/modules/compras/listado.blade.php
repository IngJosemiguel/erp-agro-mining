@extends('layouts.app')

@section('title', 'Compras — Listado')

@section('breadcrumb')
    <a href="{{ route('dashboard') }}">Dashboard</a>
    <i data-lucide="chevron-right" style="width:14px;height:14px;color:var(--text-muted)"></i>
    <span style="color:var(--text-primary)">Compras</span>
@endsection

@section('content')
    <div class="page-header">
        <div>
            <h1 class="page-title">Compras</h1>
            <p class="page-subtitle">Historial de compras — Documentos de adquisición de mercadería</p>
        </div>
    </div>

    @livewire('compras-listado')
@endsection

@push('scripts')
    <script>lucide.createIcons();</script>
@endpush