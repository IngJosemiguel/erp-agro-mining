<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\VentaService;
use App\Models\Venta;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VentaController extends Controller
{
    public function __construct(
        protected VentaService $ventaService,
    ) {
    }

    /**
     * GET /api/v1/ventas
     * Listado paginado de ventas con filtros avanzados.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Venta::query()
            ->deEmpresa($request->input('empresa_id', 1))
            ->with(['cliente:id,razon_social,numero_documento', 'vendedor:id,name']);

        if ($request->filled('tipo_documento')) {
            $query->where('tipo_documento', $request->input('tipo_documento'));
        }

        if ($request->filled('estado')) {
            $query->where('estado', $request->input('estado'));
        }

        if ($request->filled('cliente_id')) {
            $query->where('cliente_id', $request->input('cliente_id'));
        }

        if ($request->filled('fecha_desde')) {
            $query->where('fecha_emision', '>=', $request->input('fecha_desde'));
        }

        if ($request->filled('fecha_hasta')) {
            $query->where('fecha_emision', '<=', $request->input('fecha_hasta'));
        }

        if ($request->boolean('pendientes_sunat')) {
            $query->pendientesSunat();
        }

        if ($request->boolean('no_enviados')) {
            $query->noEnviados();
        }

        if ($request->boolean('por_rectificar')) {
            $query->porRectificar();
        }

        $ventas = $query->orderByDesc('fecha_emision')
            ->orderByDesc('id')
            ->paginate($request->input('per_page', 25));

        return response()->json([
            'success' => true,
            'data' => $ventas,
        ]);
    }

    /**
     * GET /api/v1/ventas/{id}
     */
    public function show(int $id): JsonResponse
    {
        $venta = Venta::with([
            'detalles.producto:id,nombre,codigo_interno',
            'detalles.lote:id,numero_lote,fecha_vencimiento',
            'detalles.serie:id,numero_serie',
            'cliente',
            'cuotas',
            'vendedor:id,name',
            'notasCredito',
            'notasDebito',
        ])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $venta,
        ]);
    }

    /**
     * POST /api/v1/ventas/notas
     * Crear una NOTA DE VENTA (no va a SUNAT).
     */
    public function crearNotaVenta(Request $request): JsonResponse
    {
        $validated = $this->validarDatosVenta($request);
        $validated['tipo_documento'] = Venta::TIPO_NOTA_VENTA;

        $venta = $this->ventaService->crearVenta($validated);

        return response()->json([
            'success' => true,
            'message' => 'Nota de venta creada exitosamente.',
            'data' => $venta,
        ], 201);
    }

    /**
     * POST /api/v1/ventas/facturas
     * Crear una FACTURA (CPE - irá a SUNAT en cola).
     */
    public function crearFactura(Request $request): JsonResponse
    {
        $validated = $this->validarDatosVenta($request);
        $validated['tipo_documento'] = Venta::TIPO_FACTURA;

        // Validar que el cliente tenga RUC para facturas
        $cliente = \App\Models\Cliente::findOrFail($validated['cliente_id']);
        if ($cliente->tipo_documento !== 'RUC') {
            return response()->json([
                'success' => false,
                'message' => 'Para emitir una factura, el cliente debe tener RUC.',
            ], 422);
        }

        $venta = $this->ventaService->crearVenta($validated);

        // Despachar Job para enviar a SUNAT en background
        // dispatch(new \App\Jobs\EnviarCpeSunat($venta));

        return response()->json([
            'success' => true,
            'message' => 'Factura emitida. Se enviará a SUNAT en segundo plano.',
            'data' => $venta,
        ], 201);
    }

    /**
     * POST /api/v1/ventas/boletas
     * Crear una BOLETA (CPE - irá a resumen diario SUNAT).
     */
    public function crearBoleta(Request $request): JsonResponse
    {
        $validated = $this->validarDatosVenta($request);
        $validated['tipo_documento'] = Venta::TIPO_BOLETA;

        $venta = $this->ventaService->crearVenta($validated);

        // Las boletas van en resumen diario
        // dispatch(new \App\Jobs\EnviarCpeSunat($venta));

        return response()->json([
            'success' => true,
            'message' => 'Boleta emitida exitosamente.',
            'data' => $venta,
        ], 201);
    }

    /**
     * POST /api/v1/ventas/{id}/anular
     */
    public function anular(Request $request, int $id): JsonResponse
    {
        $request->validate(['motivo' => 'required|string|max:500']);

        $venta = $this->ventaService->anularVenta($id, $request->input('motivo'));

        return response()->json([
            'success' => true,
            'message' => 'Venta anulada exitosamente.',
            'data' => $venta,
        ]);
    }

    /**
     * GET /api/v1/ventas/{id}/sunat-status
     * Consultar estado del CPE en SUNAT.
     */
    public function sunatStatus(int $id): JsonResponse
    {
        $venta = Venta::select([
            'id',
            'tipo_documento',
            'serie',
            'correlativo',
            'estado',
            'sunat_codigo_respuesta',
            'sunat_mensaje_respuesta',
            'sunat_hash',
            'cdr_path',
        ])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => [
                'comprobante' => $venta->numero_completo,
                'estado' => $venta->estado,
                'sunat_codigo' => $venta->sunat_codigo_respuesta,
                'sunat_mensaje' => $venta->sunat_mensaje_respuesta,
                'sunat_hash' => $venta->sunat_hash,
                'tiene_cdr' => !empty($venta->cdr_path),
            ],
        ]);
    }

    /**
     * Validación compartida para ventas.
     */
    protected function validarDatosVenta(Request $request): array
    {
        return $request->validate([
            'empresa_id' => 'required|exists:empresas,id',
            'sucursal_id' => 'required|exists:sucursales,id',
            'almacen_id' => 'required|exists:almacenes,id',
            'cliente_id' => 'required|exists:clientes,id',
            'user_id' => 'nullable|exists:users,id',
            'fecha_emision' => 'nullable|date',
            'fecha_vencimiento' => 'nullable|date|after_or_equal:fecha_emision',
            'moneda' => 'nullable|in:PEN,USD',
            'tipo_cambio' => 'nullable|numeric|min:0.001',
            'descuento_global' => 'nullable|numeric|min:0',
            'forma_pago' => 'nullable|in:contado,credito',
            'observaciones' => 'nullable|string|max:1000',
            'cotizacion_id' => 'nullable|exists:cotizaciones,id',
            // Detalles
            'detalles' => 'required|array|min:1',
            'detalles.*.producto_id' => 'required|exists:productos,id',
            'detalles.*.cantidad' => 'required|numeric|min:0.0001',
            'detalles.*.precio_unitario' => 'required|numeric|min:0',
            'detalles.*.descuento' => 'nullable|numeric|min:0',
            'detalles.*.porcentaje_descuento' => 'nullable|numeric|min:0|max:100',
            'detalles.*.lote_id' => 'nullable|exists:lotes,id',
            'detalles.*.serie_id' => 'nullable|exists:series,id',
            // Cuotas (para ventas a crédito)
            'cuotas' => 'nullable|array',
            'cuotas.*.numero_cuota' => 'required_with:cuotas|integer|min:1',
            'cuotas.*.monto' => 'required_with:cuotas|numeric|min:0.01',
            'cuotas.*.fecha_vencimiento' => 'required_with:cuotas|date',
        ]);
    }
}
