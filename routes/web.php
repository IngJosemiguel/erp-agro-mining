<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ModuleController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| ERP AgroMine - Web Routes
|--------------------------------------------------------------------------
*/

// ═══════════════════════════════════════════════════════════════════════
// AUTENTICACIÓN (rutas públicas)
// ═══════════════════════════════════════════════════════════════════════
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->name('login.post');
});

Route::post('/logout', [LoginController::class, 'logout'])->name('logout')->middleware('auth');

// ═══════════════════════════════════════════════════════════════════════
// ERP — Todas las rutas requieren autenticación
// ═══════════════════════════════════════════════════════════════════════
Route::middleware(['auth'])->group(function () {

    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // ═══════════════════════════════════════════════════════════════════════
// VENTAS (Livewire activo)
// ═══════════════════════════════════════════════════════════════════════
    Route::prefix('ventas')->group(function () {
        Route::get('/', fn() => view('modules.ventas.index'))->name('ventas.index');
        Route::get('/listado', fn() => view('modules.ventas.index'))->name('ventas.listado');
        Route::get('/atencion', \App\Livewire\Ventas\AtencionCliente::class)->name('ventas.atencion');
        Route::get('/notas', fn() => view('modules.ventas.notas'))->name('ventas.notas');
        Route::get('/comprobantes-no-enviados', fn() => view('modules.ventas.comprobantes-no-enviados'))->name('ventas.comprobantes-no-enviados');
        Route::get('/cpe-pendientes', fn() => view('modules.ventas.cpe-pendientes'))->name('ventas.cpe-pendientes');
        Route::get('/resumenes', fn() => view('modules.ventas.resumenes'))->name('ventas.resumenes');
        Route::get('/cotizaciones', fn() => view('modules.ventas.cotizaciones'))->name('ventas.cotizaciones');
        Route::get('/despachos', fn() => view('modules.ventas.despachos'))->name('ventas.despachos');
        Route::get('/{id}/pdf', [\App\Http\Controllers\VentaPdfController::class, 'descargar'])->name('ventas.pdf');
    });

    // ═══════════════════════════════════════════════════════════════════════
// POS (Offline-First Alpine.js)
// ═══════════════════════════════════════════════════════════════════════
    Route::prefix('pos')->group(function () {
        Route::get('/punto-venta', fn() => view('modules.pos.punto-venta'))->name('pos.punto-venta');
        Route::get('/caja-chica', fn() => view('modules.pos.caja-chica'))->name('pos.caja-chica');
    });

    // ═══════════════════════════════════════════════════════════════════════
// PRODUCTOS / SERVICIOS (Livewire activo)
// ═══════════════════════════════════════════════════════════════════════
    Route::prefix('productos')->group(function () {
        Route::get('/', fn() => view('modules.productos.index'))->name('productos.index');
        Route::get('/packs', fn() => view('modules.productos.packs'))->name('productos.packs');
        Route::get('/buscador', fn() => view('modules.productos.buscador'))->name('productos.buscador');
        Route::get('/categorias', fn() => view('modules.productos.categorias'))->name('productos.categorias');
        Route::get('/marcas', fn() => view('modules.productos.marcas'))->name('productos.marcas');
        Route::get('/series', fn() => view('modules.productos.series'))->name('productos.series');
        Route::get('/ubicaciones', fn() => view('modules.productos.ubicaciones'))->name('productos.ubicaciones');
        Route::get('/laboratorios', fn() => view('modules.productos.laboratorios'))->name('productos.laboratorios');
        Route::get('/codigos-barra', fn() => view('modules.productos.codigos-barra'))->name('productos.codigos-barra');
        Route::get('/tipos-existencia', fn() => view('modules.productos.tipos-existencia'))->name('productos.tipos-existencia');
    });

    // ═══════════════════════════════════════════════════════════════════════
// CLIENTES (Livewire activo)
// ═══════════════════════════════════════════════════════════════════════
    Route::prefix('clientes')->group(function () {
        Route::get('/', fn() => view('modules.clientes.index'))->name('clientes.index');
        Route::get('/tipos', fn() => view('modules.clientes.tipos'))->name('clientes.tipos');
        Route::get('/zonas', fn() => view('modules.clientes.zonas'))->name('clientes.zonas');
    });

    // ═══════════════════════════════════════════════════════════════════════
// PROVEEDORES
// ═══════════════════════════════════════════════════════════════════════
    Route::prefix('proveedores')->group(function () {
        Route::get('/', fn() => view('modules.proveedores.index'))->name('proveedores.index');
        Route::get('/cotizacion', fn() => view('modules.proveedores.cotizacion'))->name('proveedores.cotizacion');
    });

    // ═══════════════════════════════════════════════════════════════════════
// COMPRAS
// ═══════════════════════════════════════════════════════════════════════
    Route::prefix('compras')->group(function () {
        Route::get('/nuevo', fn() => view('modules.compras.nuevo'))->name('compras.nuevo');
        Route::get('/listado', fn() => view('modules.compras.listado'))->name('compras.listado');
        Route::get('/ordenes', fn() => view('modules.compras.ordenes'))->name('compras.ordenes');
        Route::get('/credito-bancario', fn() => view('modules.compras.credito-bancario'))->name('compras.credito-bancario');
        Route::get('/gastos', fn() => view('modules.compras.gastos'))->name('compras.gastos');
        Route::get('/activos-fijos', fn() => view('modules.compras.activos-fijos'))->name('compras.activos-fijos');
    });

    // ═══════════════════════════════════════════════════════════════════════
// INVENTARIO
// ═══════════════════════════════════════════════════════════════════════
    Route::prefix('inventario')->group(function () {
        Route::get('/movimientos', fn() => view('modules.inventario.movimientos'))->name('inventario.movimientos');
        Route::get('/traslados', fn() => view('modules.inventario.traslados'))->name('inventario.traslados');
        Route::get('/devolucion', fn() => view('modules.inventario.devolucion'))->name('inventario.devolucion');
        Route::get('/kardex', fn() => view('modules.inventario.kardex'))->name('inventario.kardex');
        Route::get('/reporte', fn() => view('modules.inventario.reporte'))->name('inventario.reporte');
        Route::get('/kardex-valorizado', fn() => view('modules.inventario.kardex-valorizado'))->name('inventario.kardex-valorizado');
        Route::get('/formato-13', fn() => view('modules.inventario.formato-13'))->name('inventario.formato-13');
        Route::get('/prediccion-stock', \App\Livewire\PrediccionStock::class)->name('inventario.prediccion');
    });

    // ═══════════════════════════════════════════════════════════════════════
// COMPROBANTES AVANZADOS
// ═══════════════════════════════════════════════════════════════════════
    Route::prefix('comprobantes')->group(function () {
        Route::get('/retenciones', fn() => view('modules.comprobantes.retenciones'))->name('comprobantes.retenciones');
        Route::get('/percepciones', fn() => view('modules.comprobantes.percepciones'))->name('comprobantes.percepciones');
        Route::get('/liquidaciones', fn() => view('modules.comprobantes.liquidaciones'))->name('comprobantes.liquidaciones');
        Route::get('/ordenes-pedido', fn() => view('modules.comprobantes.ordenes-pedido'))->name('comprobantes.ordenes-pedido');
        Route::get('/guias-remision', fn() => view('modules.comprobantes.guias-remision'))->name('comprobantes.guias-remision');
    });

    // ═══════════════════════════════════════════════════════════════════════
// FINANZAS
// ═══════════════════════════════════════════════════════════════════════
    Route::prefix('finanzas')->name('finanzas.')->group(function () {
        // Hub del módulo — redirige a la primera sección útil
        Route::get('/', fn() => redirect()->route('finanzas.cuentas-cobrar'))->name('index');

        // ── Tesorería ──────────────────────────────────────────────────
        Route::get('/caja-chica', \App\Livewire\CajaChicaListado::class)->name('caja-chica');
        Route::get('/creditos-bancarios', \App\Livewire\CreditosBancarios::class)->name('creditos-bancarios');
        Route::get('/activos-fijos', \App\Livewire\ActivosFijos::class)->name('activos-fijos');
        Route::get('/gastos', \App\Livewire\GastosListado::class)->name('gastos');
        Route::get('/cuentas-cobrar', \App\Livewire\Finanzas\CuentasCobrar::class)->name('cuentas-cobrar');
        Route::get('/cuentas-pagar', \App\Livewire\Finanzas\CuentasPagar::class)->name('cuentas-pagar');

        // ── Contabilidad ───────────────────────────────────────────────
        Route::prefix('contabilidad')->name('contabilidad.')->group(function () {
            Route::get('/plan-cuentas', \App\Livewire\Finanzas\PlanCuentas::class)->name('plan-cuentas');
            Route::get('/asientos', \App\Livewire\Finanzas\AsientosContables::class)->name('asientos');
            Route::get('/libro-diario', \App\Livewire\Finanzas\LibroDiario::class)->name('libro-diario');
            Route::get('/balance-general', \App\Livewire\Finanzas\BalanceGeneral::class)->name('balance-general');
            Route::get('/estado-resultados', \App\Livewire\Finanzas\EstadoResultados::class)->name('estado-resultados');
            Route::get('/libro-mayor', \App\Livewire\Finanzas\LibroMayor::class)->name('libro-mayor');
            Route::get('/centros-costo', \App\Livewire\Finanzas\CentrosCosto::class)->name('centros-costo');
            Route::get('/presupuesto', \App\Livewire\Finanzas\Presupuesto::class)->name('presupuesto');
        });
    });
    // ═══════════════════════════════════════════════════════════════════════
    // CONFIGURACIÓN
    // ═══════════════════════════════════════════════════════════════════════
    Route::get('/configuracion', \App\Livewire\ConfiguracionEmpresa::class)->name('configuracion.empresa');

}); // end Route::middleware(['auth'])
