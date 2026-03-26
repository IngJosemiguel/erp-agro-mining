<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Producto;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProductoController extends Controller
{
    /**
     * GET /api/v1/productos
     * Listado paginado de productos con filtros.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Producto::query()
            ->deEmpresa($request->input('empresa_id', 1))
            ->with(['tipoExistencia', 'categoria', 'marca', 'laboratorio', 'unidadMedida']);

        // Filtros
        if ($request->filled('buscar')) {
            $query->buscar($request->input('buscar'));
        }

        if ($request->filled('categoria_id')) {
            $query->where('categoria_id', $request->input('categoria_id'));
        }

        if ($request->filled('marca_id')) {
            $query->where('marca_id', $request->input('marca_id'));
        }

        if ($request->filled('tipo_producto')) {
            $query->where('tipo_producto', $request->input('tipo_producto'));
        }

        if ($request->boolean('solo_activos', true)) {
            $query->activos();
        }

        if ($request->boolean('disponible_pos')) {
            $query->disponiblePos();
        }

        if ($request->boolean('stock_bajo')) {
            $query->stockBajo();
        }

        if ($request->boolean('por_vencer')) {
            $query->porVencer($request->input('dias_vencer', 30));
        }

        $productos = $query->orderBy($request->input('orden', 'nombre'))
            ->paginate($request->input('per_page', 25));

        return response()->json([
            'success' => true,
            'data' => $productos,
        ]);
    }

    /**
     * GET /api/v1/productos/{id}
     */
    public function show(int $id): JsonResponse
    {
        $producto = Producto::with([
            'tipoExistencia',
            'categoria',
            'marca',
            'laboratorio',
            'unidadMedida',
            'inventarioStock.almacen',
            'lotes' => fn($q) => $q->where('activo', true)->orderBy('fecha_vencimiento'),
            'series' => fn($q) => $q->where('estado', 'disponible'),
        ])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $producto,
        ]);
    }

    /**
     * POST /api/v1/productos
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'empresa_id' => 'required|exists:empresas,id',
            'tipo_existencia_id' => 'required|exists:tipos_existencia,id',
            'unidad_medida_id' => 'required|exists:unidades_medida,id',
            'codigo_interno' => 'required|string|max:30',
            'nombre' => 'required|string|max:255',
            'tipo_producto' => 'required|in:bien,servicio,combo',
            'precio_venta' => 'required|numeric|min:0',
            // Opcionales
            'categoria_id' => 'nullable|exists:categorias,id',
            'marca_id' => 'nullable|exists:marcas,id',
            'laboratorio_id' => 'nullable|exists:laboratorios,id',
            'codigo_barras' => 'nullable|string|max:50',
            'codigo_sunat' => 'nullable|string|max:20',
            'nombre_corto' => 'nullable|string|max:80',
            'descripcion' => 'nullable|string',
            'controla_lote' => 'boolean',
            'controla_serie' => 'boolean',
            'controla_vencimiento' => 'boolean',
            'es_gravado_igv' => 'boolean',
            'precio_compra' => 'nullable|numeric|min:0',
            'precio_venta_minimo' => 'nullable|numeric|min:0',
            'precio_mayorista' => 'nullable|numeric|min:0',
            'stock_minimo' => 'nullable|numeric|min:0',
            'stock_maximo' => 'nullable|numeric|min:0',
            'disponible_pos' => 'boolean',
            'disponible_web' => 'boolean',
        ]);

        $producto = Producto::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Producto creado exitosamente.',
            'data' => $producto->load('tipoExistencia', 'unidadMedida'),
        ], 201);
    }

    /**
     * PUT /api/v1/productos/{id}
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $producto = Producto::findOrFail($id);

        $validated = $request->validate([
            'nombre' => 'sometimes|string|max:255',
            'codigo_barras' => 'nullable|string|max:50',
            'tipo_existencia_id' => 'sometimes|exists:tipos_existencia,id',
            'categoria_id' => 'nullable|exists:categorias,id',
            'marca_id' => 'nullable|exists:marcas,id',
            'laboratorio_id' => 'nullable|exists:laboratorios,id',
            'unidad_medida_id' => 'sometimes|exists:unidades_medida,id',
            'tipo_producto' => 'sometimes|in:bien,servicio,combo',
            'controla_lote' => 'boolean',
            'controla_serie' => 'boolean',
            'controla_vencimiento' => 'boolean',
            'es_gravado_igv' => 'boolean',
            'precio_compra' => 'nullable|numeric|min:0',
            'precio_venta' => 'nullable|numeric|min:0',
            'precio_venta_minimo' => 'nullable|numeric|min:0',
            'precio_mayorista' => 'nullable|numeric|min:0',
            'stock_minimo' => 'nullable|numeric|min:0',
            'stock_maximo' => 'nullable|numeric|min:0',
            'activo' => 'boolean',
            'disponible_pos' => 'boolean',
        ]);

        $producto->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Producto actualizado exitosamente.',
            'data' => $producto->fresh()->load('tipoExistencia', 'unidadMedida', 'categoria', 'marca'),
        ]);
    }

    /**
     * DELETE /api/v1/productos/{id}
     */
    public function destroy(int $id): JsonResponse
    {
        $producto = Producto::findOrFail($id);

        // No eliminar si tiene stock
        $stockTotal = $producto->inventarioStock()->sum('stock_actual');
        if ($stockTotal > 0) {
            return response()->json([
                'success' => false,
                'message' => "No se puede eliminar el producto porque tiene {$stockTotal} unidades en stock.",
            ], 422);
        }

        $producto->delete(); // Soft delete

        return response()->json([
            'success' => true,
            'message' => 'Producto eliminado.',
        ]);
    }

    /**
     * GET /api/v1/productos/buscar-codigo-barras/{codigo}
     */
    public function buscarPorCodigoBarras(string $codigo): JsonResponse
    {
        $producto = Producto::where('codigo_barras', $codigo)
            ->activos()
            ->with(['unidadMedida', 'inventarioStock'])
            ->first();

        if (!$producto) {
            return response()->json(['success' => false, 'message' => 'Producto no encontrado.'], 404);
        }

        return response()->json(['success' => true, 'data' => $producto]);
    }
}
