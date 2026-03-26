@extends('layouts.app')
@section('title', 'Laboratorios')
@section('breadcrumb')<a href="{{ route('dashboard') }}">Dashboard</a><i data-lucide="chevron-right"
    style="width:14px;height:14px;color:var(--text-muted)"></i><span
style="color:var(--text-primary)">Laboratorios</span>@endsection
@section('content')
    <div class="page-header">
        <div>
            <h1 class="page-title">Laboratorios / Fabricantes</h1>
            <p class="page-subtitle">Registro de laboratorios y fabricantes de productos</p>
        </div>
    </div>
    @livewire('laboratorios-listado')
@endsection
@push('scripts')
<script>lucide.createIcons();</script>@endpush