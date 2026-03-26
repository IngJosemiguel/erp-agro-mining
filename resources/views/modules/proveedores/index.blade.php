@extends('layouts.app')

@section('title', 'Proveedores')

@section('breadcrumb')
    <a href="{{ route('dashboard') }}">Dashboard</a>
    <i data-lucide="chevron-right" style="width:14px;height:14px;color:var(--text-muted)"></i>
    <span style="color:var(--text-primary)">Proveedores</span>
@endsection

@section('content')
    <div class="page-header">
        <div>
            <h1 class="page-title">Proveedores</h1>
            <p class="page-subtitle">Gestión de proveedores, datos bancarios y condiciones comerciales</p>
        </div>
    </div>

    @livewire('proveedores-table')
@endsection

@push('scripts')
    <script>lucide.createIcons();</script>
@endpush