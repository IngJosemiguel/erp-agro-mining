@extends('layouts.app')
@section('title', 'Crédito Bancario')
@section('breadcrumb')
    <a href="{{ route('dashboard') }}">Dashboard</a>
    <i data-lucide="chevron-right" style="width:14px;height:14px;color:var(--text-muted)"></i>
    <span style="color:var(--text-primary)">Crédito Bancario</span>
@endsection
@section('content')
    <div class="page-header">
        <div>
            <h1 class="page-title">Crédito Bancario</h1>
            <p class="page-subtitle">Control de préstamos bancarios, tasas de interés y saldos pendientes</p>
        </div>
    </div>
    @livewire('creditos-bancarios')
@endsection
@push('scripts')
<script>lucide.createIcons();</script>@endpush