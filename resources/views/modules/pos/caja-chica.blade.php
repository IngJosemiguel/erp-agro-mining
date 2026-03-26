@extends('layouts.app')
@section('title', 'Caja Chica')
@section('breadcrumb')<a href="{{ route('dashboard') }}">Dashboard</a><i data-lucide="chevron-right"
    style="width:14px;height:14px;color:var(--text-muted)"></i><span style="color:var(--text-primary)">Caja
Chica</span>@endsection
@section('content')
    <div class="page-header">
        <div>
            <h1 class="page-title">Caja Chica</h1>
            <p class="page-subtitle">Control de ingresos y egresos de caja menor</p>
        </div>
    </div>
    @livewire('caja-chica-listado')
@endsection
@push('scripts')
<script>lucide.createIcons();</script>@endpush