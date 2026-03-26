@extends('layouts.app')
@section('title', 'Libro Mayor')
@section('breadcrumb')
    <a href="{{ route('dashboard') }}">Dashboard</a>
    <i data-lucide="chevron-right" style="width:14px;height:14px;color:var(--text-muted)"></i>
    <span>Finanzas / Contabilidad</span>
    <i data-lucide="chevron-right" style="width:14px;height:14px;color:var(--text-muted)"></i>
    <span style="color:var(--text-primary)">Libro Mayor</span>
@endsection
@section('content')
    <div class="page-header">
        <div>
            <h1 class="page-title">Libro Mayor</h1>
            <p class="page-subtitle">Saldos agrupados por cuenta (Cuentas "T") según los asientos contables validados.</p>
        </div>
    </div>
    @livewire('finanzas.libro-mayor')
@endsection
@push('scripts')
<script>lucide.createIcons();</script>@endpush