@extends('layouts.app')
@section('title', 'Guías de Remisión')
@section('breadcrumb')<a href="{{ route('dashboard') }}">Dashboard</a><i data-lucide="chevron-right"
    style="width:14px;height:14px;color:var(--text-muted)"></i><span style="color:var(--text-primary)">Guías de
Remisión</span>@endsection
@section('content')
    <div class="page-header">
        <div>
            <h1 class="page-title">Guías de Remisión</h1>
            <p class="page-subtitle">Guías electrónicas de remitente y transportista enviadas a SUNAT</p>
        </div>
    </div>
    @livewire('guias-remision')
@endsection
@push('scripts')
<script>lucide.createIcons();</script>@endpush