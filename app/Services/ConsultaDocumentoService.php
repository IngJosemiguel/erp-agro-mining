<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Servicio de consulta de documentos de identidad peruanos.
 *
 * Consulta DNI vía RENIEC y RUC vía SUNAT usando APIs.net.pe
 * con fallback a apiperú.dev
 *
 * Configuración en .env:
 *   APIS_NET_PE_TOKEN=tu_token_aqui
 *   APIPERU_DEV_TOKEN=tu_token_fallback  (opcional)
 */
class ConsultaDocumentoService
{
    private string $apisNetPeToken;
    private ?string $apiPeruDevToken;
    private const APIS_NET_PE_URL = 'https://api.apis.net.pe/v2';
    private const APIPERU_DEV_URL = 'https://apiperu.dev/api';

    public function __construct()
    {
        $this->apisNetPeToken = config('services.apis_net_pe.token', '');
        $this->apiPeruDevToken = config('services.apiperu_dev.token');
    }

    /**
     * Consultar DNI en RENIEC.
     *
     * @param string $dni Número de DNI (8 dígitos)
     * @return array{success: bool, data?: array, error?: string}
     */
    public function consultarDni(string $dni): array
    {
        // Validación
        if (!preg_match('/^\d{8}$/', $dni)) {
            return ['success' => false, 'error' => 'El DNI debe tener exactamente 8 dígitos numéricos.'];
        }

        // Cache de 24 horas para evitar consultas repetidas
        $cacheKey = "dni_{$dni}";
        $cached = Cache::get($cacheKey);
        if ($cached) {
            return ['success' => true, 'data' => $cached, 'source' => 'cache'];
        }

        // Intento 1: apis.net.pe v1 FREE
        $resultado = $this->consultarDniApisNetPe($dni);

        // Intento 2: fallback a apiperu.dev
        if (!$resultado['success'] && $this->apiPeruDevToken) {
            $resultado = $this->consultarDniApiPeruDev($dni);
        }

        if ($resultado['success']) {
            Cache::put($cacheKey, $resultado['data'], now()->addHours(24));
        }

        return $resultado;
    }

    /**
     * Consultar RUC en SUNAT.
     *
     * @param string $ruc Número de RUC (11 dígitos)
     * @return array{success: bool, data?: array, error?: string}
     */
    public function consultarRuc(string $ruc): array
    {
        // Validación
        if (!preg_match('/^(10|15|17|20)\d{9}$/', $ruc)) {
            return ['success' => false, 'error' => 'El RUC debe tener 11 dígitos y comenzar con 10, 15, 17 o 20.'];
        }

        $cacheKey = "ruc_{$ruc}";
        $cached = Cache::get($cacheKey);
        if ($cached) {
            return ['success' => true, 'data' => $cached, 'source' => 'cache'];
        }

        $resultado = $this->consultarRucApisNetPe($ruc);

        if (!$resultado['success'] && $this->apiPeruDevToken) {
            $resultado = $this->consultarRucApiPeruDev($ruc);
        }

        if ($resultado['success']) {
            Cache::put($cacheKey, $resultado['data'], now()->addHours(6));
        }

        return $resultado;
    }

    /**
     * Detecta tipo de documento y consulta automáticamente.
     */
    public function consultarDocumento(string $tipo, string $numero): array
    {
        $tipoMayus = strtoupper($tipo);

        if ($tipoMayus === 'A') {
            $tipoMayus = strlen(trim($numero)) === 11 ? 'RUC' : 'DNI';
        }

        return match ($tipoMayus) {
            'DNI', '1' => $this->consultarDni($numero),
            'RUC', '6' => $this->consultarRuc($numero),
            default => ['success' => false, 'error' => "Tipo de documento '{$tipo}' no soportado para consulta automática."],
        };
    }

    // ═══════════════════════════════════════════════════════════════
    //  APIS.NET.PE — Proveedor principal (v1 FREE SIN TOKEN)
    // ═══════════════════════════════════════════════════════════════

    private function consultarDniApisNetPe(string $dni): array
    {
        try {
            $response = Http::timeout(10)
                ->get("https://api.apis.net.pe/v1/dni", [
                    'numero' => $dni,
                ]);

            if ($response->successful()) {
                $data = $response->json();

                return [
                    'success' => true,
                    'data' => [
                        'tipo_documento' => 'DNI',
                        'numero_documento' => $dni,
                        'nombres' => $data['nombres'] ?? '',
                        'apellido_paterno' => $data['apellidoPaterno'] ?? '',
                        'apellido_materno' => $data['apellidoMaterno'] ?? '',
                        'nombre_completo' => trim(
                            ($data['nombres'] ?? '') . ' ' .
                            ($data['apellidoPaterno'] ?? '') . ' ' .
                            ($data['apellidoMaterno'] ?? '')
                        ),
                        'razon_social' => trim(
                            ($data['nombres'] ?? '') . ' ' .
                            ($data['apellidoPaterno'] ?? '') . ' ' .
                            ($data['apellidoMaterno'] ?? '')
                        ),
                    ],
                    'source' => 'apis.net.pe (v1 free)',
                ];
            }

            Log::warning("APIs.net.pe DNI: HTTP {$response->status()}. No se pudo consultar DNI.");

            return ['success' => false, 'error' => "Error al consultar DNI: HTTP {$response->status()}"];

        } catch (\Exception $e) {
            Log::error("APIs.net.pe DNI exception: {$e->getMessage()}");
            return ['success' => false, 'error' => 'Error de conexión con el servicio de consulta DNI.'];
        }
    }

    private function consultarRucApisNetPe(string $ruc): array
    {
        try {
            $response = Http::timeout(10)
                ->get("https://api.apis.net.pe/v1/ruc", [
                    'numero' => $ruc,
                ]);

            if ($response->successful()) {
                $data = $response->json();

                return [
                    'success' => true,
                    'data' => [
                        'tipo_documento' => 'RUC',
                        'numero_documento' => $ruc,
                        'razon_social' => $data['nombre'] ?? '',
                        'nombre_comercial' => $data['nombreComercial'] ?? null,
                        'direccion' => $data['direccion'] ?? '',
                        'departamento' => $data['departamento'] ?? '',
                        'provincia' => $data['provincia'] ?? '',
                        'distrito' => $data['distrito'] ?? '',
                        'ubigeo' => $data['ubigeo'] ?? '',
                        'estado' => $data['estado'] ?? '', // ACTIVO, BAJA, etc.
                        'condicion' => $data['condicion'] ?? '', // HABIDO, NO HABIDO
                        'tipo_contribuyente' => $data['tipoDocumento'] ?? '',
                        'es_activo' => strtoupper($data['estado'] ?? '') === 'ACTIVO',
                        'es_habido' => strtoupper($data['condicion'] ?? '') === 'HABIDO',
                    ],
                    'source' => 'apis.net.pe (v1 free)',
                ];
            }

            Log::warning("APIs.net.pe RUC {$ruc}: HTTP {$response->status()}");
            return ['success' => false, 'error' => "Error al consultar RUC: HTTP {$response->status()}"];

        } catch (\Exception $e) {
            Log::error("APIs.net.pe RUC exception: {$e->getMessage()}");
            return ['success' => false, 'error' => 'Error de conexión con el servicio de consulta RUC.'];
        }
    }

    // ═══════════════════════════════════════════════════════════════
    //  APIPERU.DEV — Proveedor fallback
    // ═══════════════════════════════════════════════════════════════

    private function consultarDniApiPeruDev(string $dni): array
    {
        try {
            $response = Http::timeout(10)
                ->withHeaders([
                    'Authorization' => "Bearer {$this->apiPeruDevToken}",
                    'Content-Type' => 'application/json',
                ])
                ->post(self::APIPERU_DEV_URL . '/dni', [
                    'dni' => $dni,
                ]);

            if ($response->successful() && $response->json('success')) {
                $data = $response->json('data');

                return [
                    'success' => true,
                    'data' => [
                        'tipo_documento' => 'DNI',
                        'numero_documento' => $dni,
                        'nombres' => $data['nombres'] ?? '',
                        'apellido_paterno' => $data['apellido_paterno'] ?? '',
                        'apellido_materno' => $data['apellido_materno'] ?? '',
                        'nombre_completo' => $data['nombre_completo'] ?? '',
                        'razon_social' => ($data['apellido_paterno'] ?? '') . ' ' .
                            ($data['apellido_materno'] ?? '') . ', ' .
                            ($data['nombres'] ?? ''),
                    ],
                    'source' => 'apiperu.dev',
                ];
            }

            return ['success' => false, 'error' => 'No se encontró el DNI en el servicio alternativo.'];

        } catch (\Exception $e) {
            Log::error("ApiPeru.dev DNI exception: {$e->getMessage()}");
            return ['success' => false, 'error' => 'Todos los servicios de consulta DNI fallaron.'];
        }
    }

    private function consultarRucApiPeruDev(string $ruc): array
    {
        try {
            $response = Http::timeout(10)
                ->withHeaders([
                    'Authorization' => "Bearer {$this->apiPeruDevToken}",
                    'Content-Type' => 'application/json',
                ])
                ->post(self::APIPERU_DEV_URL . '/ruc', [
                    'ruc' => $ruc,
                ]);

            if ($response->successful() && $response->json('success')) {
                $data = $response->json('data');

                return [
                    'success' => true,
                    'data' => [
                        'tipo_documento' => 'RUC',
                        'numero_documento' => $ruc,
                        'razon_social' => $data['nombre_o_razon_social'] ?? '',
                        'nombre_comercial' => $data['nombre_comercial'] ?? null,
                        'direccion' => $data['direccion'] ?? '',
                        'departamento' => $data['departamento'] ?? '',
                        'provincia' => $data['provincia'] ?? '',
                        'distrito' => $data['distrito'] ?? '',
                        'ubigeo' => $data['ubigeo_sunat'] ?? '',
                        'estado' => $data['estado'] ?? '',
                        'condicion' => $data['condicion'] ?? '',
                        'es_activo' => strtoupper($data['estado'] ?? '') === 'ACTIVO',
                        'es_habido' => strtoupper($data['condicion'] ?? '') === 'HABIDO',
                    ],
                    'source' => 'apiperu.dev',
                ];
            }

            return ['success' => false, 'error' => 'No se encontró el RUC en el servicio alternativo.'];

        } catch (\Exception $e) {
            Log::error("ApiPeru.dev RUC exception: {$e->getMessage()}");
            return ['success' => false, 'error' => 'Todos los servicios de consulta RUC fallaron.'];
        }
    }
}
