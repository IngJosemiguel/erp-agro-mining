<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class ModuleController extends Controller
{
    /**
     * Renderiza la vista placeholder para un módulo.
     */
    public function show(string $module, string $section): View
    {
        $titles = [
            'ventas.atencion' => 'Atención al Cliente',
            'ventas.listado' => 'Listado de Atención',
            'ventas.notas' => 'Notas de Venta',
            'ventas.comprobantes-no-enviados' => 'Comprobantes No Enviados',
            'ventas.cpe-pendientes' => 'CPE Pendientes de Rectificación',
            'ventas.resumenes' => 'Resúmenes y Anulaciones',
            'ventas.cotizaciones' => 'Cotizaciones',
            'ventas.despachos' => 'Despachos',
            'pos.punto-venta' => 'Punto de Venta',
            'pos.caja-chica' => 'Caja Chica POS',
            'productos.packs' => 'Conjuntos / Packs / Promociones',
            'productos.buscador' => 'Buscador de Productos',
            'productos.categorias' => 'Categorías',
            'productos.marcas' => 'Marcas',
            'productos.series' => 'Series',
            'productos.ubicaciones' => 'Ubicaciones',
            'productos.laboratorios' => 'Laboratorios',
            'productos.codigos-barra' => 'Códigos de Barra',
            'productos.tipos-existencia' => 'Tipos de Existencia',
            'clientes.tipos' => 'Tipos de Clientes',
            'clientes.zonas' => 'Zonas',
            'proveedores.index' => 'Listado de Proveedores',
            'proveedores.cotizacion' => 'Solicitar Cotización',
            'compras.nuevo' => 'Nueva Compra',
            'compras.listado' => 'Listado de Compras',
            'compras.ordenes' => 'Órdenes de Compra',
            'compras.credito-bancario' => 'Crédito Bancario',
            'compras.gastos' => 'Gastos Diversos',
            'compras.activos-fijos' => 'Activos Fijos',
            'inventario.movimientos' => 'Movimientos de Inventario',
            'inventario.traslados' => 'Traslados',
            'inventario.devolucion' => 'Devolución a Proveedor',
            'inventario.kardex' => 'Reporte Kardex',
            'inventario.reporte' => 'Reporte de Inventario',
            'inventario.kardex-valorizado' => 'Kardex Valorizado',
            'inventario.formato-13' => 'Formato 13.1 Inventario Valorizado SUNAT',
            'comprobantes.retenciones' => 'Retenciones',
            'comprobantes.percepciones' => 'Percepciones',
            'comprobantes.liquidaciones' => 'Liquidaciones de Compra',
            'comprobantes.ordenes-pedido' => 'Órdenes de Pedido',
            'comprobantes.guias-remision' => 'Guías de Remisión',
        ];

        $routeName = "{$module}.{$section}";
        $title = $titles[$routeName] ?? ucfirst(str_replace('-', ' ', $section));

        return view('modules.placeholder', [
            'title' => $title,
            'module' => ucfirst($module),
            'routeName' => $routeName,
        ]);
    }
}
