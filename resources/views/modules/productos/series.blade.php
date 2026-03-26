@extends('layouts.app')
@section('title', 'Series de Producto')
@section('breadcrumb')<a href="{{ route('dashboard') }}">Dashboard</a><i data-lucide="chevron-right"
    style="width:14px;height:14px;color:var(--text-muted)"></i><span
style="color:var(--text-primary)">Series</span>@endsection
@section('content')
    <div class="page-header">
        <div>
            <h1 class="page-title">Series de Producto</h1>
            <p class="page-subtitle">Control de números de serie por producto — trazabilidad individual</p>
        </div>
    </div>
    @livewire('series-producto')
@endsection
@push('scripts')
<script>lucide.createIcons();</script>@endpush