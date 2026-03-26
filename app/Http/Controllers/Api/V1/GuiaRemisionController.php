<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Empresa;
use App\Services\Sunat\GuiaRemisionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GuiaRemisionController extends Controller
{
    public function __construct(
        private GuiaRemisionService $greService,
    ) {
    }

    /**
     * POST /api/v1/guias-remision/remitente
     * Envía una Guía de Remisión Remitente a SUNAT.
     */
    public function enviarRemitente(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'empresa_id' => 'required|exists:empresas,id',
            'serie' => 'required|string|regex:/^T\d{3}$/',
            'correlativo' => 'required|string',
            'fecha_emision' => 'required|date',

            // Destinatario
            'destinatario.tipo_documento' => 'required|in:DNI,RUC,CE',
            'destinatario.numero_documento' => 'required|string',
            'destinatario.razon_social' => 'required|string|max:255',

            // Datos del envío
            'envio.modalidad' => 'required|in:01,02', // 01=Público, 02=Privado
            'envio.motivo' => 'required|string|size:2', // Código SUNAT catálogo 20
            'envio.fecha_traslado' => 'required|date',
            'envio.peso_total' => 'required|numeric|min:0.01',
            'envio.unidad_peso' => 'required|in:KGM,TNE',
            'envio.partida.ubigeo' => 'required|string|size:6',
            'envio.partida.direccion' => 'required|string|max:255',
            'envio.llegada.ubigeo' => 'required|string|size:6',
            'envio.llegada.direccion' => 'required|string|max:255',

            // Transportista (obligatorio si modalidad=01)
            'transportista.ruc' => 'required_if:envio.modalidad,01|string',
            'transportista.razon_social' => 'required_if:envio.modalidad,01|string',

            // Conductor (obligatorio si modalidad=02)
            'conductor.numero_documento' => 'required_if:envio.modalidad,02|string',
            'conductor.nombres' => 'required_if:envio.modalidad,02|string',
            'conductor.apellidos' => 'required_if:envio.modalidad,02|string',
            'conductor.licencia' => 'required_if:envio.modalidad,02|string',

            // Vehículo (obligatorio si modalidad=02)
            'vehiculo.placa' => 'required_if:envio.modalidad,02|string',

            // Items
            'items' => 'required|array|min:1',
            'items.*.descripcion' => 'required|string|max:255',
            'items.*.cantidad' => 'required|numeric|min:0.01',
            'items.*.unidad_medida' => 'required|string',
            'items.*.codigo' => 'nullable|string',
        ]);

        try {
            $empresa = Empresa::findOrFail($validated['empresa_id']);
            $this->greService->inicializar($empresa);

            $resultado = $this->greService->enviarGuiaRemitente($validated);

            return response()->json($resultado, $resultado['success'] ? 200 : 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'mensaje' => 'Error al enviar GRE: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * POST /api/v1/guias-remision/transportista
     * Envía una Guía de Remisión Transportista a SUNAT.
     */
    public function enviarTransportista(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'empresa_id' => 'required|exists:empresas,id',
            'serie' => 'required|string|regex:/^V\d{3}$/',
            'correlativo' => 'required|string',
            'fecha_emision' => 'required|date',
            'destinatario.tipo_documento' => 'required|in:DNI,RUC,CE',
            'destinatario.numero_documento' => 'required|string',
            'destinatario.razon_social' => 'required|string|max:255',
            'envio.motivo' => 'required|string|size:2',
            'envio.fecha_traslado' => 'required|date',
            'envio.peso_total' => 'required|numeric|min:0.01',
            'envio.partida.ubigeo' => 'required|string|size:6',
            'envio.partida.direccion' => 'required|string|max:255',
            'envio.llegada.ubigeo' => 'required|string|size:6',
            'envio.llegada.direccion' => 'required|string|max:255',
            'conductor.numero_documento' => 'required|string',
            'conductor.nombres' => 'required|string',
            'conductor.apellidos' => 'required|string',
            'conductor.licencia' => 'required|string',
            'vehiculo.placa' => 'required|string',
            'items' => 'required|array|min:1',
            'items.*.descripcion' => 'required|string|max:255',
            'items.*.cantidad' => 'required|numeric|min:0.01',
            'items.*.unidad_medida' => 'required|string',
        ]);

        try {
            $empresa = Empresa::findOrFail($validated['empresa_id']);
            $this->greService->inicializar($empresa);

            $resultado = $this->greService->enviarGuiaTransportista($validated);

            return response()->json($resultado, $resultado['success'] ? 200 : 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'mensaje' => 'Error al enviar GRT: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * GET /api/v1/guias-remision/consultar-cdr/{ticket}
     * Consulta el CDR usando un ticket.
     */
    public function consultarCdr(string $ticket, Request $request): JsonResponse
    {
        $request->validate([
            'empresa_id' => 'required|exists:empresas,id',
        ]);

        try {
            $empresa = Empresa::findOrFail($request->empresa_id);
            $this->greService->inicializar($empresa);

            $resultado = $this->greService->consultarCdr($ticket);

            return response()->json($resultado);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'mensaje' => 'Error al consultar CDR: ' . $e->getMessage(),
            ], 500);
        }
    }
}
