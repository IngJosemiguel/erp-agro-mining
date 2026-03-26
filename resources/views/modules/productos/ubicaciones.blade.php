@extends('layouts.app')
@section('title', 'Ubicaciones de Almacén')
@section('breadcrumb')<a href="{{ route('dashboard') }}">Dashboard</a><i data-lucide="chevron-right"
    style="width:14px;height:14px;color:var(--text-muted)"></i><span
style="color:var(--text-primary)">Ubicaciones</span>@endsection
@section('content')
    <div class="page-header">
        <div>
            <h1 class="page-title">Ubicaciones de Almacén</h1>
            <p class="page-subtitle">Pasillos, racks y estantes de los almacenes registrados</p>
        </div>
    </div>
    @livewire('ubicaciones-almacen')
@endsection
@push('scripts')
<script>lucide.createIcons();</script>@endpush