@extends('layouts.app')
@section('title', 'Percepciones')
@section('breadcrumb')<a href="{{ route('dashboard') }}">Dashboard</a><i data-lucide="chevron-right"
    style="width:14px;height:14px;color:var(--text-muted)"></i><span
style="color:var(--text-primary)">Percepciones</span>@endsection
@section('content')
    <div class="page-header">
        <div>
            <h1 class="page-title">Percepciones</h1>
            <p class="page-subtitle">Comprobantes de Percepción emitidos a clientes</p>
        </div>
    </div>
    @livewire('percepciones-listado')
@endsection
@push('scripts')
<script>lucide.createIcons();</script>@endpush