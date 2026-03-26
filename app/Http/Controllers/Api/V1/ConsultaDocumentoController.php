<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\ConsultaDocumentoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ConsultaDocumentoController extends Controller
{
    public function __construct(
        private ConsultaDocumentoService $consultaService,
    ) {
    }

    /**
     * GET /api/v1/consulta/dni/{numero}
     * Consulta un DNI en RENIEC.
     */
    public function consultarDni(string $numero): JsonResponse
    {
        $resultado = $this->consultaService->consultarDni($numero);

        return response()->json($resultado, $resultado['success'] ? 200 : 422);
    }

    /**
     * GET /api/v1/consulta/ruc/{numero}
     * Consulta un RUC en SUNAT.
     */
    public function consultarRuc(string $numero): JsonResponse
    {
        $resultado = $this->consultaService->consultarRuc($numero);

        return response()->json($resultado, $resultado['success'] ? 200 : 422);
    }

    /**
     * GET /api/v1/consulta/documento/{tipo}/{numero}
     * Detección automática de tipo de documento.
     */
    public function consultarDocumento(string $tipo, string $numero): JsonResponse
    {
        $resultado = $this->consultaService->consultarDocumento($tipo, $numero);

        return response()->json($resultado, $resultado['success'] ? 200 : 422);
    }
}
