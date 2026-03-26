@extends('layouts.app')
@section('title', 'Notas de Crédito y Débito')
@section('breadcrumb')<a href="{{ route('dashboard') }}">Dashboard</a><i data-lucide="chevron-right"
    style="width:14px;height:14px;color:var(--text-muted)"></i><span style="color:var(--text-primary)">Notas de
Crédito/Débito</span>@endsection
@section('content')
    <div class="page-header">
        <div>
            <h1 class="page-title">Notas de Crédito y Débito</h1>
            <p class="page-subtitle">Gestión de notas de crédito (07) y débito (08) emitidas</p>
        </div>
    </div>
    @livewire('notas-credito')
@endsection
@push('scripts')
<script>lucide.createIcons();</script>@endpush