@extends('layouts.app')
@section('title', 'Asientos Contables')

@section('breadcrumb')
    <a href="{{ route('dashboard') }}">Dashboard</a>
    <i data-lucide="chevron-right" style="width:14px;height:14px;color:var(--text-muted)"></i>
    <span>Finanzas / Contabilidad</span>
    <i data-lucide="chevron-right" style="width:14px;height:14px;color:var(--text-muted)"></i>
    <span style="color:var(--text-primary)">Asientos Contables</span>
@endsection

@section('content')
    <div class="page-header">
        <div>
            <h1 class="page-title">Asientos Contables</h1>
            <p class="page-subtitle">Partida doble — Libro Diario</p>
        </div>
    </div>

    @livewire('finanzas.asientos-contables')
@endsection

@push('scripts')
<script>lucide.createIcons();</script>@endpush