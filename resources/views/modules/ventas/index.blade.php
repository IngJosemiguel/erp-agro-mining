@extends('layouts.app')

@section('title', 'Ventas y Comprobantes - ERP AgroMine')

@section('content')
    <div class="page-header">
        <div class="header-content">
            <h1 class="page-title">Ventas & Comprobantes</h1>
            <p class="page-subtitle">Gestión de Facturas, Boletas, Notas de Venta y envíos a SUNAT</p>
        </div>
    </div>

    <div class="page-body">
        @livewire('ventas-table')
    </div>

    @push('styles')
        <style>
            .page-header {
                background: var(--bg-card);
                padding: 24px;
                border-radius: 12px;
                border: 1px solid var(--border-color);
                margin-bottom: 24px;
                display: flex;
                justify-content: space-between;
                align-items: center;
            }

            .page-title {
                font-size: 20px;
                font-weight: 700;
                color: var(--text-primary);
                margin: 0 0 4px 0;
            }

            .page-subtitle {
                font-size: 13px;
                color: var(--text-muted);
                margin: 0;
            }
        </style>
    @endpush
@endsection