@extends('layouts.app')
@section('title', 'Resúmenes Diarios SUNAT')
@section('breadcrumb')<a href="{{ route('dashboard') }}">Dashboard</a><i data-lucide="chevron-right"
    style="width:14px;height:14px;color:var(--text-muted)"></i><span style="color:var(--text-primary)">Resúmenes
Diarios</span>@endsection
@section('content')
    <div class="page-header">
        <div>
            <h1 class="page-title">Resúmenes Diarios</h1>
            <p class="page-subtitle">Resúmenes de comprobantes por fecha para comunicación a SUNAT</p>
        </div>
    </div>
    @livewire('resumenes-diarios')
@endsection
@push('scripts')
<script>lucide.createIcons();</script>@endpush