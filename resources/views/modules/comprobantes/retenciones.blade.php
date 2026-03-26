@extends('layouts.app')
@section('title', 'Retenciones')
@section('breadcrumb')<a href="{{ route('dashboard') }}">Dashboard</a><i data-lucide="chevron-right"
    style="width:14px;height:14px;color:var(--text-muted)"></i><span
style="color:var(--text-primary)">Retenciones</span>@endsection
@section('content')
    <div class="page-header">
        <div>
            <h1 class="page-title">Retenciones</h1>
            <p class="page-subtitle">Comprobantes de Retención emitidos a proveedores</p>
        </div>
    </div>
    @livewire('retenciones-listado')
@endsection
@push('scripts')
<script>lucide.createIcons();</script>@endpush