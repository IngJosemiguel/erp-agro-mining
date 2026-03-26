<?php

namespace App\Http\Controllers;

use App\Models\Venta;
use App\Models\Producto;
use App\Models\Cliente;
use App\Models\Compra;
use App\Models\Gasto;
use App\Traits\TieneContextoEmpresa;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    use TieneContextoEmpresa;

    public function index(): View
    {
        $empresaId = self::resolverEmpresaId();
        $hoy = now()->toDateString();
        $inicioMes = now()->startOfMonth()->toDateString();
        $finMes = now()->endOfMonth()->toDateString();

        // ═══════════════════════════════════════════════════════════════
        //  KPIs OPERATIVOS (ORIGINALES, MEJORADOS)
        // ═══════════════════════════════════════════════════════════════
        $ventasHoy = Venta::deEmpresa($empresaId)
            ->where('fecha_emision', today())
            ->whereNotIn('estado', ['anulado', 'borrador'])
            ->sum('total');

        $ventasMes = Venta::deEmpresa($empresaId)
            ->whereBetween('fecha_emision', [$inicioMes, $finMes])
            ->whereNotIn('estado', ['anulado', 'borrador'])
            ->sum('total');

        $ventasAyer = Venta::deEmpresa($empresaId)
            ->where('fecha_emision', now()->subDay()->toDateString())
            ->whereNotIn('estado', ['anulado', 'borrador'])
            ->sum('total');

        $pctCambioVentas = $ventasAyer > 0
            ? round((($ventasHoy - $ventasAyer) / $ventasAyer) * 100, 1)
            : ($ventasHoy > 0 ? 100 : 0);

        $comprobantesHoy = Venta::deEmpresa($empresaId)
            ->where('fecha_emision', today())
            ->comprobantesElectronicos()
            ->count();

        $pendientesSunat = Venta::deEmpresa($empresaId)
            ->pendientesSunat()
            ->count();

        $totalProductos = Producto::deEmpresa($empresaId)->activos()->count();
        $totalClientes = Cliente::where('empresa_id', $empresaId)->where('activo', true)->count();

        $productosStockBajo = Producto::deEmpresa($empresaId)
            ->activos()
            ->where('stock_minimo', '>', 0)
            ->whereRaw('(SELECT COALESCE(SUM(stock_actual),0) FROM inventario_stock WHERE inventario_stock.producto_id = productos.id) <= productos.stock_minimo')
            ->count();

        // ═══════════════════════════════════════════════════════════════
        //  KPIs FINANCIEROS (NUEVOS)
        // ═══════════════════════════════════════════════════════════════

        // Compras del mes
        $comprasMes = DB::table('compras')
            ->where('empresa_id', $empresaId)
            ->whereNotIn('estado', ['anulada', 'borrador'])
            ->whereBetween('fecha_emision', [$inicioMes, $finMes])
            ->sum('total');

        // Gastos del mes
        $gastosMes = DB::table('gastos')
            ->where('empresa_id', $empresaId)
            ->whereNotIn('estado', ['anulado'])
            ->whereBetween('fecha', [$inicioMes, $finMes])
            ->sum('total');

        // Utilidad estimada del mes (Ventas - Compras - Gastos)
        $utilidadMes = $ventasMes - $comprasMes - $gastosMes;
        $margenMes = $ventasMes > 0 ? round(($utilidadMes / $ventasMes) * 100, 1) : 0;

        // Cuentas por Cobrar (CxC)
        $cxcTotal = DB::table('ventas')
            ->where('empresa_id', $empresaId)
            ->where('forma_pago', 'credito')
            ->whereNotIn('estado', ['anulada', 'anulado'])
            ->where(DB::raw('total - monto_pagado'), '>', 0)
            ->sum(DB::raw('total - monto_pagado'));

        $cxcVencidas = DB::table('ventas')
            ->where('empresa_id', $empresaId)
            ->where('forma_pago', 'credito')
            ->whereNotIn('estado', ['anulada', 'anulado'])
            ->where('fecha_vencimiento', '<', $hoy)
            ->where(DB::raw('total - monto_pagado'), '>', 0)
            ->count();

        // Cuentas por Pagar (CxP)
        $cxpTotal = DB::table('compras')
            ->where('empresa_id', $empresaId)
            ->where('forma_pago', 'credito')
            ->whereNotIn('estado', ['anulada', 'borrador'])
            ->where(DB::raw('total - monto_pagado'), '>', 0)
            ->sum(DB::raw('total - monto_pagado'));

        $cxpVencidas = DB::table('compras')
            ->where('empresa_id', $empresaId)
            ->where('forma_pago', 'credito')
            ->whereNotIn('estado', ['anulada', 'borrador'])
            ->where('fecha_vencimiento', '<', $hoy)
            ->where(DB::raw('total - monto_pagado'), '>', 0)
            ->count();

        // ═══════════════════════════════════════════════════════════════
        //  GRÁFICOS
        // ═══════════════════════════════════════════════════════════════

        // Ventas últimos 7 días
        $ventasPorDia = Venta::deEmpresa($empresaId)
            ->where('fecha_emision', '>=', now()->subDays(6)->startOfDay())
            ->whereNotIn('estado', ['anulado', 'borrador'])
            ->select(
                DB::raw('DATE(fecha_emision) as fecha'),
                DB::raw('SUM(total) as total_dia')
            )
            ->groupBy('fecha')
            ->orderBy('fecha')
            ->get();

        $fechas7Dias = collect();
        $ventas7Dias = collect();
        for ($i = 6; $i >= 0; $i--) {
            $fecha = now()->subDays($i)->format('Y-m-d');
            $fechas7Dias->push(now()->subDays($i)->format('d M'));
            $ventaDia = $ventasPorDia->firstWhere('fecha', $fecha);
            $ventas7Dias->push($ventaDia ? (float) $ventaDia->total_dia : 0);
        }

        // Ingresos vs Egresos últimos 6 meses (nuevo chart)
        $flujoPorMes = [];
        for ($i = 5; $i >= 0; $i--) {
            $m = now()->subMonths($i);
            $mes = $m->format('Y-m');
            $mesLabel = $m->translatedFormat('M Y');
            $inicioP = $m->copy()->startOfMonth()->toDateString();
            $finP = $m->copy()->endOfMonth()->toDateString();

            $ingresos = Venta::deEmpresa($empresaId)
                ->whereBetween('fecha_emision', [$inicioP, $finP])
                ->whereNotIn('estado', ['anulado', 'borrador'])
                ->sum('total');

            $egresos = DB::table('compras')
                ->where('empresa_id', $empresaId)
                ->whereNotIn('estado', ['anulada', 'borrador'])
                ->whereBetween('fecha_emision', [$inicioP, $finP])
                ->sum('total');
            $egresos += DB::table('gastos')
                ->where('empresa_id', $empresaId)
                ->whereNotIn('estado', ['anulado'])
                ->whereBetween('fecha', [$inicioP, $finP])
                ->sum('total');

            $flujoPorMes[] = [
                'mes' => $mesLabel,
                'ingresos' => (float) $ingresos,
                'egresos' => (float) $egresos,
            ];
        }

        $flujoLabels = array_column($flujoPorMes, 'mes');
        $flujoIngresos = array_column($flujoPorMes, 'ingresos');
        $flujoEgresos = array_column($flujoPorMes, 'egresos');

        // ═══════════════════════════════════════════════════════════════
        //  TABLAS Y LISTADOS
        // ═══════════════════════════════════════════════════════════════
        $ultimasVentas = Venta::deEmpresa($empresaId)
            ->with('cliente:id,razon_social')
            ->orderByDesc('id')
            ->limit(6)
            ->get();

        $productosAlerta = Producto::deEmpresa($empresaId)
            ->activos()
            ->where('tipo_producto', 'bien')
            ->where('stock_minimo', '>', 0)
            ->whereRaw('(SELECT COALESCE(SUM(stock_actual),0) FROM inventario_stock WHERE inventario_stock.producto_id = productos.id) <= productos.stock_minimo')
            ->selectRaw('productos.*, (SELECT COALESCE(SUM(stock_actual),0) FROM inventario_stock WHERE inventario_stock.producto_id = productos.id) as stock_total')
            ->orderBy('stock_total')
            ->limit(6)
            ->get();

        $topClientes = Venta::where('ventas.empresa_id', $empresaId)
            ->whereMonth('fecha_emision', now()->month)
            ->whereNotIn('estado', ['anulado', 'borrador'])
            ->join('clientes', 'ventas.cliente_id', '=', 'clientes.id')
            ->select(
                'clientes.razon_social',
                DB::raw('SUM(ventas.total) as total_comprado')
            )
            ->groupBy('ventas.cliente_id', 'clientes.razon_social')
            ->orderByDesc('total_comprado')
            ->limit(5)
            ->get();

        // Facturas próximas a vencer (CxC y CxP)
        $isSqlite = DB::connection()->getDriverName() === 'sqlite';
        $diasParaVencerVentasSql = $isSqlite
            ? 'CAST(julianday(ventas.fecha_vencimiento) - julianday(\'now\') AS INTEGER) as dias_para_vencer'
            : 'DATEDIFF(ventas.fecha_vencimiento, NOW()) as dias_para_vencer';
            
        $diasParaVencerComprasSql = $isSqlite
            ? 'CAST(julianday(compras.fecha_vencimiento) - julianday(\'now\') AS INTEGER) as dias_para_vencer'
            : 'DATEDIFF(compras.fecha_vencimiento, NOW()) as dias_para_vencer';

        $proximosCobros = DB::table('ventas')
            ->leftJoin('clientes', 'ventas.cliente_id', '=', 'clientes.id')
            ->where('ventas.empresa_id', $empresaId)
            ->where('ventas.forma_pago', 'credito')
            ->whereNotIn('ventas.estado', ['anulada', 'anulado'])
            ->whereRaw('ventas.total - ventas.monto_pagado > 0')
            ->select(
                'ventas.serie',
                'ventas.correlativo',
                'ventas.total',
                'ventas.monto_pagado',
                'ventas.fecha_vencimiento',
                'clientes.razon_social',
                DB::raw('ventas.total - ventas.monto_pagado as saldo'),
                DB::raw($diasParaVencerVentasSql)
            )
            ->orderBy('ventas.fecha_vencimiento')
            ->limit(5)
            ->get();

        $proximosPagos = DB::table('compras')
            ->leftJoin('proveedores', 'compras.proveedor_id', '=', 'proveedores.id')
            ->where('compras.empresa_id', $empresaId)
            ->where('compras.forma_pago', 'credito')
            ->whereNotIn('compras.estado', ['anulada', 'borrador'])
            ->whereRaw('compras.total - compras.monto_pagado > 0')
            ->select(
                'compras.serie',
                'compras.correlativo',
                'compras.total',
                'compras.monto_pagado',
                'compras.fecha_vencimiento',
                'proveedores.razon_social',
                DB::raw('compras.total - compras.monto_pagado as saldo'),
                DB::raw($diasParaVencerComprasSql)
            )
            ->orderBy('compras.fecha_vencimiento')
            ->limit(5)
            ->get();

        return view('dashboard', compact(
            'ventasHoy',
            'ventasMes',
            'pctCambioVentas',
            'comprobantesHoy',
            'pendientesSunat',
            'totalProductos',
            'totalClientes',
            'productosStockBajo',
            'comprasMes',
            'gastosMes',
            'utilidadMes',
            'margenMes',
            'cxcTotal',
            'cxcVencidas',
            'cxpTotal',
            'cxpVencidas',
            'ultimasVentas',
            'productosAlerta',
            'topClientes',
            'fechas7Dias',
            'ventas7Dias',
            'flujoLabels',
            'flujoIngresos',
            'flujoEgresos',
            'proximosCobros',
            'proximosPagos',
        ));
    }
}
