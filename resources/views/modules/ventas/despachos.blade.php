@extends('layouts.app')
@section('title', 'Despachos / Guías de Remisión')
@section('breadcrumb')<a href="{{ route('dashboard') }}">Dashboard</a><i data-lucide="chevron-right"
    style="width:14px;height:14px;color:var(--text-muted)"></i><span
style="color:var(--text-primary)">Despachos</span>@endsection
@section('content')
    <div class="page-header">
        <div>
            <h1 class="page-title">Despachos</h1>
            <p class="page-subtitle">Guías de remisión y control de entregas a clientes</p>
        </div>
    </div>
    @livewire('despachos-listado')
@endsection
@push('scripts')
<script>lucide.createIcons();</script>@endpush