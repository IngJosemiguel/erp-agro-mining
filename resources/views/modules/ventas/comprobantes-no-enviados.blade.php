@extends('layouts.app')
@section('title', 'Comprobantes No Enviados')
@section('breadcrumb')<a href="{{ route('dashboard') }}">Dashboard</a><i data-lucide="chevron-right"
    style="width:14px;height:14px;color:var(--text-muted)"></i><span style="color:var(--text-primary)">CPE Pendientes de
Envío</span>@endsection
@section('content')
    <div class="page-header">
        <div>
            <h1 class="page-title">Comprobantes No Enviados</h1>
            <p class="page-subtitle">CPE pendientes de envío a SUNAT — Verificar conectividad y reenviar</p>
        </div>
    </div>
    @livewire('comprobantes-no-enviados')
@endsection
@push('scripts')
<script>lucide.createIcons();</script>@endpush