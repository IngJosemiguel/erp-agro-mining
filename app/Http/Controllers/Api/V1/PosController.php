<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Traits\TieneContextoEmpresa;
use Illuminate\Http\Request;
use App\Services\VentaService;
use App\Services\ContabilidadAutomaticaService;
use App\Models\Cliente;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PosController extends Controller
{
    use TieneContextoEmpresa;

    protected VentaService $ventaService;
    protected ContabilidadAutomaticaService $contabilidadService;

    public function __construct(VentaService $ventaService, ContabilidadAutomaticaService $contabilidadService)
    {
        $this->ventaService = $ventaService;
        $this->contabilidadService = $contabilidadService;
    }

    /**
     * Sincronizar un lote de ventas o una sola venta desde el POS Offline
     */
    public function syncVentas(Request $request)
    {
        // Se espera un array de ventas o una sola venta
        $ventasPayload = $request->isMethod('post') ? $request->all() : [$request->all()];

        $exitosas = [];
        $errores = [];

        foreach ($ventasPayload as $ventaData) {
            DB::beginTransaction();
            try {
                // 1. Resolver Cliente
                $clienteId = $this->resolverCliente($ventaData['cliente']);

                // 2. Formatear items para VentaService
                $items = array_map(function ($item) {
                    return [
                        'producto_id' => $item['id'],
                        'descripcion' => $item['nombre'],
                        'cantidad' => $item['cantidad'],
                        'precio_unitario' => $item['precio'],
                        // Asumimos IGV gravado por defecto en la demo POS offline
                        'tipo_afectacion_igv' => '10',
                        'porcentaje_igv' => $item['igv_pct'] ?? 18,
                        'descuento' => 0
                    ];
                }, $ventaData['items']);

                // 3. Crear Venta usando VentaService (maneja stock, kardex, igv y correlativos atómicos)
                $venta = $this->ventaService->crearVenta([
                    'empresa_id' => self::resolverEmpresaId(),
                    'sucursal_id' => self::resolverSucursalId(),
                    'almacen_id' => self::resolverAlmacenId(),
                    'cliente_id' => $clienteId,
                    'user_id' => auth()->id() ?? 1,
                    'tipo_documento' => $ventaData['tipo_documento'] ?? '03',
                    'moneda' => 'PEN',
                    'forma_pago' => 'contado',
                    'items' => $items,
                ]);

                // ── Automatización Contable ──
                $empresaId = self::resolverEmpresaId();
                $formaPago = $ventaData['forma_pago'] ?? 'contado';
                if ($formaPago === 'credito') {
                    $this->contabilidadService->registrarVentaCredito($empresaId, $venta);
                } else {
                    $this->contabilidadService->registrarVentaContado($empresaId, $venta);
                }

                DB::commit();

                $exitosas[] = [
                    'id_temp' => $ventaData['id_temp'] ?? null,
                    'id_real' => $venta->id,
                    'correlativo' => "{$venta->serie}-{$venta->correlativo}"
                ];

            } catch (\Exception $e) {
                DB::rollBack();
                Log::channel('sunat')->error("Error sincronizando venta POS offline", [
                    'temp_id' => $ventaData['id_temp'] ?? null,
                    'error' => $e->getMessage()
                ]);
                $errores[] = [
                    'id_temp' => $ventaData['id_temp'] ?? null,
                    'error' => $e->getMessage()
                ];
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Sincronización POS completada',
            'data' => [
                'sincronizadas' => $exitosas,
                'fallidas' => $errores
            ]
        ]);
    }

    /**
     * Resuelve o crea un cliente a partir de los datos locales del POS
     */
    private function resolverCliente(array $clienteData): int
    {
        if (empty($clienteData['documento'])) {
            $empresaId = self::resolverEmpresaId();
            $clienteGen = Cliente::firstOrCreate(
                ['numero_documento' => '00000000', 'empresa_id' => $empresaId],
                ['tipo_documento' => '00', 'razon_social' => 'CLIENTES VARIOS', 'activo' => true]
            );
            return $clienteGen->id;
        }

        $empresaId = self::resolverEmpresaId();
        $cliente = Cliente::where('numero_documento', $clienteData['documento'])
            ->where('empresa_id', $empresaId)
            ->first();

        if ($cliente) {
            return $cliente->id;
        }

        // Crear si no existe
        $nuevoCliente = Cliente::create([
            'empresa_id' => $empresaId,
            'tipo_documento' => strlen($clienteData['documento']) === 11 ? 'RUC' : 'DNI',
            'numero_documento' => $clienteData['documento'],
            'razon_social' => $clienteData['nombre'] ?: 'CLIENTE SIN NOMBRE',
            'activo' => true
        ]);

        return $nuevoCliente->id;
    }
}
