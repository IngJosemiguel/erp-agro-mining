<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Empresa;
use App\Models\Venta;
use App\Services\Sunat\FacturacionElectronicaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FacturacionController extends Controller
{
    public function __construct(
        private FacturacionElectronicaService $feService,
    ) {
    }

    /**
     * POST /api/v1/facturacion/enviar/{ventaId}
     * Envía un comprobante (Factura/Boleta) a SUNAT.
     */
    public function enviar(int $ventaId): JsonResponse
    {
        $venta = Venta::with(['detalles.producto.unidadMedida', 'cliente', 'empresa', 'cuotas'])->findOrFail($ventaId);

        if (!$venta->esComprobanteSunat()) {
            return response()->json([
                'success' => false,
                'mensaje' => 'Solo se pueden enviar Facturas, Boletas o Notas de Crédito/Débito.',
            ], 422);
        }

        if ($venta->estado === Venta::ESTADO_ACEPTADO) {
            return response()->json([
                'success' => false,
                'mensaje' => 'Este comprobante ya fue aceptado por SUNAT.',
            ], 422);
        }

        try {
            $this->feService->inicializar($venta->empresa);
            $resultado = $this->feService->enviarComprobante($venta);

            return response()->json($resultado, $resultado['success'] ? 200 : 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'mensaje' => 'Error al enviar: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * POST /api/v1/facturacion/nota-credito/{ventaId}
     * Envía una Nota de Crédito a SUNAT.
     */
    public function enviarNotaCredito(int $ventaId): JsonResponse
    {
        $nota = Venta::with(['detalles.producto.unidadMedida', 'cliente', 'empresa', 'ventaReferencia'])->findOrFail($ventaId);

        if ($nota->tipo_documento !== Venta::TIPO_NOTA_CREDITO) {
            return response()->json([
                'success' => false,
                'mensaje' => 'El documento no es una Nota de Crédito.',
            ], 422);
        }

        try {
            $this->feService->inicializar($nota->empresa);
            $resultado = $this->feService->enviarNotaCredito($nota);

            return response()->json($resultado, $resultado['success'] ? 200 : 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'mensaje' => 'Error al enviar NC: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * POST /api/v1/facturacion/resumen-diario
     * Envía el Resumen Diario de Boletas.
     */
    public function resumenDiario(Request $request): JsonResponse
    {
        $request->validate([
            'fecha' => 'required|date',
            'empresa_id' => 'required|exists:empresas,id',
        ]);

        $fecha = new \DateTime($request->fecha);
        $empresa = Empresa::findOrFail($request->empresa_id);

        $ventas = Venta::deEmpresa($empresa->id)
            ->where('tipo_documento', Venta::TIPO_BOLETA)
            ->whereDate('fecha_emision', $request->fecha)
            ->whereIn('estado', [Venta::ESTADO_EMITIDO])
            ->with('cliente')
            ->get();

        if ($ventas->isEmpty()) {
            return response()->json([
                'success' => false,
                'mensaje' => 'No hay boletas pendientes de envío para esa fecha.',
            ], 422);
        }

        try {
            $this->feService->inicializar($empresa);
            $resultado = $this->feService->enviarResumenDiario($ventas->all(), $fecha);

            return response()->json($resultado, $resultado['success'] ? 200 : 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'mensaje' => 'Error al enviar resumen: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * POST /api/v1/facturacion/anular/{ventaId}
     * Comunicación de Baja a SUNAT.
     */
    public function comunicacionBaja(int $ventaId, Request $request): JsonResponse
    {
        $request->validate([
            'motivo' => 'required|string|max:255',
        ]);

        $venta = Venta::with('empresa')->findOrFail($ventaId);

        if (!$venta->esComprobanteSunat() || !in_array($venta->estado, [Venta::ESTADO_ACEPTADO])) {
            return response()->json([
                'success' => false,
                'mensaje' => 'Solo se pueden anular comprobantes aceptados por SUNAT.',
            ], 422);
        }

        try {
            $this->feService->inicializar($venta->empresa);
            $resultado = $this->feService->enviarComunicacionBaja($venta, $request->motivo);

            return response()->json($resultado, $resultado['success'] ? 200 : 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'mensaje' => 'Error al anular: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * GET /api/v1/facturacion/consultar-ticket/{ticket}
     * Consulta el estado de un ticket asíncrono.
     */
    public function consultarTicket(string $ticket, Request $request): JsonResponse
    {
        $request->validate([
            'empresa_id' => 'required|exists:empresas,id',
        ]);

        try {
            $empresa = Empresa::findOrFail($request->empresa_id);
            $this->feService->inicializar($empresa);
            $resultado = $this->feService->consultarTicket($ticket);

            return response()->json($resultado);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'mensaje' => 'Error al consultar ticket: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * POST /api/v1/facturacion/reenviar-pendientes
     * Re-envía todos los comprobantes pendientes de una empresa.
     */
    public function reenviarPendientes(Request $request): JsonResponse
    {
        $request->validate([
            'empresa_id' => 'required|exists:empresas,id',
        ]);

        $empresa = Empresa::findOrFail($request->empresa_id);
        $this->feService->inicializar($empresa);

        $pendientes = Venta::deEmpresa($empresa->id)
            ->whereIn('estado', [Venta::ESTADO_EMITIDO, Venta::ESTADO_RECHAZADO])
            ->comprobantesElectronicos()
            ->where('tipo_documento', '!=', Venta::TIPO_BOLETA) // Boletas van por resumen
            ->with(['detalles.producto.unidadMedida', 'cliente', 'empresa', 'cuotas'])
            ->orderBy('id')
            ->limit(20) // Máximo 20 por batch
            ->get();

        $resultados = [];
        foreach ($pendientes as $venta) {
            try {
                $resultados[] = [
                    'venta_id' => $venta->id,
                    'comprobante' => "{$venta->serie}-{$venta->correlativo}",
                    'resultado' => $this->feService->enviarComprobante($venta),
                ];
                usleep(500000); // 500ms delay entre envíos para no saturar SUNAT
            } catch (\Exception $e) {
                $resultados[] = [
                    'venta_id' => $venta->id,
                    'comprobante' => "{$venta->serie}-{$venta->correlativo}",
                    'resultado' => ['success' => false, 'mensaje' => $e->getMessage()],
                ];
            }
        }

        return response()->json([
            'total_procesados' => count($resultados),
            'resultados' => $resultados,
        ]);
    }
}
