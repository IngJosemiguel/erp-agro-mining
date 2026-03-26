<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\ProductoController;
use App\Http\Controllers\Api\V1\VentaController;
use App\Http\Controllers\Api\V1\InventarioController;
use App\Http\Controllers\Api\V1\ClienteController;
use App\Http\Controllers\Api\V1\FacturacionController;
use App\Http\Controllers\Api\V1\GuiaRemisionController;
use App\Http\Controllers\Api\V1\ConsultaDocumentoController;

/*
|--------------------------------------------------------------------------
| ERP Agro-Mining API Routes v1
|--------------------------------------------------------------------------
*/

Route::prefix('v1')->group(function () {

    // ─── PRODUCTOS ──────────────────────────────────────────────
    Route::prefix('productos')->group(function () {
        Route::get('/', [ProductoController::class, 'index']);
        Route::get('/{id}', [ProductoController::class, 'show']);
        Route::post('/', [ProductoController::class, 'store']);
        Route::put('/{id}', [ProductoController::class, 'update']);
        Route::delete('/{id}', [ProductoController::class, 'destroy']);
        Route::get('/buscar-codigo-barras/{codigo}', [ProductoController::class, 'buscarPorCodigoBarras']);
    });

    // ─── CLIENTES ───────────────────────────────────────────────
    Route::prefix('clientes')->group(function () {
        Route::get('/', [ClienteController::class, 'index']);
        Route::get('/{id}', [ClienteController::class, 'show']);
        Route::post('/', [ClienteController::class, 'store']);
        Route::put('/{id}', [ClienteController::class, 'update']);
        Route::delete('/{id}', [ClienteController::class, 'destroy']);
        Route::get('/consultar-documento/{tipo}/{numero}', [ClienteController::class, 'consultarDocumento']);
    });

    // ─── VENTAS & POS ───────────────────────────────────────────
    Route::prefix('ventas')->group(function () {
        Route::get('/', [VentaController::class, 'index']);
        Route::get('/{id}', [VentaController::class, 'show']);
        Route::post('/notas', [VentaController::class, 'crearNotaVenta']);
        Route::post('/facturas', [VentaController::class, 'crearFactura']);
        Route::post('/boletas', [VentaController::class, 'crearBoleta']);
        Route::post('/{id}/anular', [VentaController::class, 'anular']);
        Route::get('/{id}/sunat-status', [VentaController::class, 'sunatStatus']);

        // POS Offline-First Sync
        Route::post('/pos/sync', [\App\Http\Controllers\Api\V1\PosController::class, 'syncVentas']);
    });

    // ─── INVENTARIO ─────────────────────────────────────────────
    Route::prefix('inventario')->group(function () {
        Route::get('/stock', [InventarioController::class, 'index']);
        Route::post('/ingreso', [InventarioController::class, 'ingreso']);
        Route::post('/salida', [InventarioController::class, 'salida']);
        Route::get('/kardex', [InventarioController::class, 'kardex']);
        Route::get('/valorizado', [InventarioController::class, 'inventarioValorizado']);
    });

    // ═══════════════════════════════════════════════════════════════
    // FACTURACIÓN ELECTRÓNICA SUNAT
    // ═══════════════════════════════════════════════════════════════
    Route::prefix('facturacion')->group(function () {
        Route::post('/enviar/{ventaId}', [FacturacionController::class, 'enviar']);
        Route::post('/nota-credito/{ventaId}', [FacturacionController::class, 'enviarNotaCredito']);
        Route::post('/resumen-diario', [FacturacionController::class, 'resumenDiario']);
        Route::post('/anular/{ventaId}', [FacturacionController::class, 'comunicacionBaja']);
        Route::get('/consultar-ticket/{ticket}', [FacturacionController::class, 'consultarTicket']);
        Route::post('/reenviar-pendientes', [FacturacionController::class, 'reenviarPendientes']);
    });

    // ═══════════════════════════════════════════════════════════════
    // GUÍAS DE REMISIÓN ELECTRÓNICAS (GRE)
    // ═══════════════════════════════════════════════════════════════
    Route::prefix('guias-remision')->group(function () {
        Route::post('/remitente', [GuiaRemisionController::class, 'enviarRemitente']);
        Route::post('/transportista', [GuiaRemisionController::class, 'enviarTransportista']);
        Route::get('/consultar-cdr/{ticket}', [GuiaRemisionController::class, 'consultarCdr']);
    });

    // ═══════════════════════════════════════════════════════════════
    // CONSULTA DNI / RUC (RENIEC / SUNAT)
    // ═══════════════════════════════════════════════════════════════
    Route::prefix('consulta')->group(function () {
        Route::get('/dni/{numero}', [ConsultaDocumentoController::class, 'consultarDni']);
        Route::get('/ruc/{numero}', [ConsultaDocumentoController::class, 'consultarRuc']);
        Route::get('/documento/{tipo}/{numero}', [ConsultaDocumentoController::class, 'consultarDocumento']);
    });

});


