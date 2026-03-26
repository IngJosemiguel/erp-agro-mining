<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\InventarioService;
use App\Models\KardexMovimiento;
use App\Models\InventarioStock;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InventarioController extends Controller
{
    public function __construct(
        protected InventarioService $inventarioService,
    ) {
    }

    /**
     * GET /api/v1/inventario/stock
     * Consultar stock actual con filtros.
     */
    public function index(Request $request): JsonResponse
    {
        $query = InventarioStock::query()
            ->with(['producto:id,codigo_interno,nombre,stock_minimo', 'almacen:id,nombre']);

        if ($request->filled('almacen_id')) {
            $query->where('almacen_id', $request->input('almacen_id'));
        }

        if ($request->filled('producto_id')) {
            $query->where('producto_id', $request->input('producto_id'));
        }

        if ($request->boolean('con_stock')) {
            $query->where('stock_actual', '>', 0);
        }

        if ($request->boolean('stock_bajo')) {
            $query->whereHas('producto', function ($q) {
                $q->whereColumn('inventario_stock.stock_actual', '<=', 'productos.stock_minimo');
            });
        }

        $stock = $query->paginate($request->input('per_page', 50));

        return response()->json(['success' => true, 'data' => $stock]);
    }

    /**
     * POST /api/v1/inventario/ingreso
     * Ingreso manual de mercadería.
     */
    public function ingreso(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'empresa_id' => 'required|exists:empresas,id',
            'producto_id' => 'required|exists:productos,id',
            'almacen_id' => 'required|exists:almacenes,id',
            'cantidad' => 'required|numeric|min:0.0001',
            'costo_unitario' => 'required|numeric|min:0',
            'tipo_operacion' => 'required|string|max:2',
            'motivo' => 'nullable|string|max:500',
            'lote_id' => 'nullable|exists:lotes,id',
            'serie_id' => 'nullable|exists:series,id',
        ]);

        $movimiento = $this->inventarioService->registrarIngreso($validated);

        return response()->json([
            'success' => true,
            'message' => 'Ingreso registrado exitosamente.',
            'data' => $movimiento->load('producto:id,nombre', 'almacen:id,nombre'),
        ], 201);
    }

    /**
     * POST /api/v1/inventario/salida
     * Salida manual de mercadería.
     */
    public function salida(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'empresa_id' => 'required|exists:empresas,id',
            'producto_id' => 'required|exists:productos,id',
            'almacen_id' => 'required|exists:almacenes,id',
            'cantidad' => 'required|numeric|min:0.0001',
            'tipo_operacion' => 'required|string|max:2',
            'motivo' => 'nullable|string|max:500',
            'lote_id' => 'nullable|exists:lotes,id',
            'serie_id' => 'nullable|exists:series,id',
        ]);

        $movimiento = $this->inventarioService->registrarSalida($validated);

        return response()->json([
            'success' => true,
            'message' => 'Salida registrada exitosamente.',
            'data' => $movimiento->load('producto:id,nombre', 'almacen:id,nombre'),
        ], 201);
    }

    /**
     * GET /api/v1/inventario/kardex
     * Reporte de Kardex por producto/almacén.
     */
    public function kardex(Request $request): JsonResponse
    {
        $request->validate([
            'producto_id' => 'required|exists:productos,id',
            'almacen_id' => 'required|exists:almacenes,id',
            'fecha_desde' => 'required|date',
            'fecha_hasta' => 'required|date|after_or_equal:fecha_desde',
        ]);

        $movimientos = KardexMovimiento::kardexValorizado(
            $request->input('producto_id'),
            $request->input('almacen_id'),
            $request->input('fecha_desde'),
            $request->input('fecha_hasta'),
        )
            ->with(['lote:id,numero_lote', 'serie:id,numero_serie'])
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'movimientos' => $movimientos,
                'resumen' => [
                    'total_ingresos' => $movimientos->where('tipo_movimiento', 'ingreso')->sum('cantidad'),
                    'total_salidas' => $movimientos->where('tipo_movimiento', 'salida')->sum('cantidad'),
                    'saldo_final' => $movimientos->last()?->saldo_cantidad ?? 0,
                    'valor_final' => $movimientos->last()?->saldo_valorizado ?? 0,
                ],
            ],
        ]);
    }

    /**
     * GET /api/v1/inventario/valorizado
     * Reporte de Inventario Valorizado (Base para Formato SUNAT 13.1).
     */
    public function inventarioValorizado(Request $request): JsonResponse
    {
        $request->validate([
            'empresa_id' => 'required|exists:empresas,id',
            'almacen_id' => 'nullable|exists:almacenes,id',
            'fecha_corte' => 'required|date',
        ]);

        $query = InventarioStock::query()
            ->with([
                'producto:id,codigo_interno,nombre,tipo_existencia_id',
                'producto.tipoExistencia:id,codigo_sunat,descripcion',
                'almacen:id,nombre',
            ])
            ->where('stock_actual', '>', 0);

        if ($request->filled('almacen_id')) {
            $query->where('almacen_id', $request->input('almacen_id'));
        }

        $inventario = $query->get()->map(function ($item) {
            return [
                'codigo_interno' => $item->producto->codigo_interno,
                'producto' => $item->producto->nombre,
                'tipo_existencia' => $item->producto->tipoExistencia->codigo_sunat ?? '',
                'almacen' => $item->almacen->nombre,
                'stock' => $item->stock_actual,
                'costo_promedio' => $item->costo_promedio,
                'valor_total' => round($item->stock_actual * $item->costo_promedio, 2),
            ];
        });

        return response()->json([
            'success' => true,
            'data' => [
                'items' => $inventario,
                'total_valor' => $inventario->sum('valor_total'),
            ],
        ]);
    }
}
