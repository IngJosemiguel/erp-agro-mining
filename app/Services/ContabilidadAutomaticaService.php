<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Motor de Automatización Contable
 *
 * Genera asientos contables automáticos (validados) a partir de eventos
 * transaccionales del ERP: Ventas, Compras, Gastos, Pagos CxC/CxP.
 *
 * Principio: cada transacción económica debe registrar automáticamente
 * su efecto contable de doble partida sin intervención del contador.
 *
 * CUENTAS POR DEFECTO (se crean si no existen):
 * ───────────────────────────────────────────────
 * 10.01  Caja y Bancos
 * 12.01  Cuentas por Cobrar Comerciales
 * 40.11  IGV por Pagar
 * 42.01  Cuentas por Pagar Comerciales
 * 60.01  Compras de Mercaderías
 * 63.XX  Gastos por Servicios / Otros Gastos
 * 70.01  Ventas de Mercaderías
 * 69.01  Costo de Ventas
 */
class ContabilidadAutomaticaService
{
    /**
     * Mapa de códigos contables y su configuración predeterminada.
     * Se usan como fallback si la empresa no tiene estas cuentas creadas.
     */
    private const CUENTAS_DEFAULT = [
        'caja' => ['codigo' => '10.01', 'nombre' => 'Caja y Bancos', 'tipo' => 'activo', 'naturaleza' => 'deudora'],
        'cxc' => ['codigo' => '12.01', 'nombre' => 'Cuentas por Cobrar Comerciales', 'tipo' => 'activo', 'naturaleza' => 'deudora'],
        'igv' => ['codigo' => '40.11', 'nombre' => 'IGV - Tributo por Pagar', 'tipo' => 'pasivo', 'naturaleza' => 'acreedora'],
        'cxp' => ['codigo' => '42.01', 'nombre' => 'Cuentas por Pagar Comerciales', 'tipo' => 'pasivo', 'naturaleza' => 'acreedora'],
        'compras' => ['codigo' => '60.01', 'nombre' => 'Compras de Mercaderías', 'tipo' => 'gasto', 'naturaleza' => 'deudora'],
        'gastos_srv' => ['codigo' => '63.01', 'nombre' => 'Gastos por Servicios de Terceros', 'tipo' => 'gasto', 'naturaleza' => 'deudora'],
        'gastos_adm' => ['codigo' => '65.01', 'nombre' => 'Otros Gastos de Gestión', 'tipo' => 'gasto', 'naturaleza' => 'deudora'],
        'ventas' => ['codigo' => '70.01', 'nombre' => 'Ventas de Mercaderías', 'tipo' => 'ingreso', 'naturaleza' => 'acreedora'],
        'costo_vta' => ['codigo' => '69.01', 'nombre' => 'Costo de Ventas', 'tipo' => 'costo', 'naturaleza' => 'deudora'],
    ];

    /**
     * Mapeo de categoría de gasto → alias de cuenta contable
     */
    private const MAPA_CATEGORIA_GASTO = [
        'servicios' => 'gastos_srv',
        'suministros' => 'gastos_adm',
        'transporte' => 'gastos_srv',
        'alquiler' => 'gastos_srv',
        'mantenimiento' => 'gastos_adm',
        'otros' => 'gastos_adm',
    ];

    // ════════════════════════════════════════════════════════════════════
    //  A. ASIENTO POR VENTA AL CONTADO
    // ════════════════════════════════════════════════════════════════════
    /**
     * Venta contado:
     *   DEBE  10.01 Caja            (total)
     *   HABER 40.11 IGV             (igv)
     *   HABER 70.01 Ventas          (subtotal)
     */
    public function registrarVentaContado(int $empresaId, object $venta): void
    {
        $this->crearAsiento($empresaId, [
            'fecha' => $venta->fecha_emision ?? now()->toDateString(),
            'tipo' => 'venta',
            'glosa' => "Venta contado {$venta->serie}-{$venta->correlativo}",
            'origen' => 'ventas',
            'origen_id' => $venta->id,
            'partidas' => [
                ['alias' => 'caja', 'debe' => (float) $venta->total, 'haber' => 0],
                ['alias' => 'igv', 'debe' => 0, 'haber' => (float) $venta->igv],
                ['alias' => 'ventas', 'debe' => 0, 'haber' => (float) ($venta->total - $venta->igv)],
            ],
        ]);
    }

    // ════════════════════════════════════════════════════════════════════
    //  B. ASIENTO POR VENTA A CRÉDITO
    // ════════════════════════════════════════════════════════════════════
    /**
     * Venta crédito:
     *   DEBE  12.01 CxC             (total)
     *   HABER 40.11 IGV             (igv)
     *   HABER 70.01 Ventas          (subtotal)
     */
    public function registrarVentaCredito(int $empresaId, object $venta): void
    {
        $this->crearAsiento($empresaId, [
            'fecha' => $venta->fecha_emision ?? now()->toDateString(),
            'tipo' => 'venta',
            'glosa' => "Venta crédito {$venta->serie}-{$venta->correlativo}",
            'origen' => 'ventas',
            'origen_id' => $venta->id,
            'partidas' => [
                ['alias' => 'cxc', 'debe' => (float) $venta->total, 'haber' => 0],
                ['alias' => 'igv', 'debe' => 0, 'haber' => (float) $venta->igv],
                ['alias' => 'ventas', 'debe' => 0, 'haber' => (float) ($venta->total - $venta->igv)],
            ],
        ]);
    }

    // ════════════════════════════════════════════════════════════════════
    //  C. ASIENTO POR COBRO DE CxC
    // ════════════════════════════════════════════════════════════════════
    /**
     * Cobro de CxC:
     *   DEBE  10.01 Caja            (monto cobrado)
     *   HABER 12.01 CxC             (monto cobrado)
     */
    public function registrarCobroCxC(int $empresaId, float $monto, string $referencia = ''): void
    {
        $this->crearAsiento($empresaId, [
            'fecha' => now()->toDateString(),
            'tipo' => 'cobro',
            'glosa' => "Cobro CxC — {$referencia}",
            'partidas' => [
                ['alias' => 'caja', 'debe' => $monto, 'haber' => 0],
                ['alias' => 'cxc', 'debe' => 0, 'haber' => $monto],
            ],
        ]);
    }

    // ════════════════════════════════════════════════════════════════════
    //  D. ASIENTO POR COMPRA
    // ════════════════════════════════════════════════════════════════════
    /**
     * Compra (contado o crédito):
     *   DEBE  60.01 Compras         (subtotal)
     *   DEBE  40.11 IGV Crédito     (igv)
     *   HABER 42.01 CxP / 10.01 Caja
     */
    public function registrarCompra(int $empresaId, object $compra, string $formaPago = 'contado'): void
    {
        $subtotal = (float) ($compra->subtotal ?? ($compra->total - ($compra->igv ?? 0)));
        $igv = (float) ($compra->igv ?? 0);
        $total = (float) $compra->total;

        $contrapartida = $formaPago === 'credito' ? 'cxp' : 'caja';

        $this->crearAsiento($empresaId, [
            'fecha' => $compra->fecha_emision ?? now()->toDateString(),
            'tipo' => 'compra',
            'glosa' => "Compra {$compra->serie}-{$compra->correlativo}",
            'origen' => 'compras',
            'origen_id' => $compra->id,
            'partidas' => [
                ['alias' => 'compras', 'debe' => $subtotal, 'haber' => 0],
                ['alias' => 'igv', 'debe' => $igv, 'haber' => 0],
                ['alias' => $contrapartida, 'debe' => 0, 'haber' => $total],
            ],
        ]);
    }

    // ════════════════════════════════════════════════════════════════════
    //  E. ASIENTO POR PAGO A PROVEEDOR (CxP)
    // ════════════════════════════════════════════════════════════════════
    /**
     * Pago CxP:
     *   DEBE  42.01 CxP             (monto)
     *   HABER 10.01 Caja            (monto)
     */
    public function registrarPagoCxP(int $empresaId, float $monto, string $referencia = ''): void
    {
        $this->crearAsiento($empresaId, [
            'fecha' => now()->toDateString(),
            'tipo' => 'pago',
            'glosa' => "Pago a proveedor — {$referencia}",
            'partidas' => [
                ['alias' => 'cxp', 'debe' => $monto, 'haber' => 0],
                ['alias' => 'caja', 'debe' => 0, 'haber' => $monto],
            ],
        ]);
    }

    // ════════════════════════════════════════════════════════════════════
    //  F. ASIENTO POR GASTO OPERATIVO
    // ════════════════════════════════════════════════════════════════════
    /**
     * Gasto:
     *   DEBE  63.XX / 65.XX Gasto correspondiente (subtotal)
     *   DEBE  40.11 IGV (igv)
     *   HABER 10.01 Caja (total)
     */
    public function registrarGasto(int $empresaId, object $gasto): void
    {
        $categoria = strtolower($gasto->categoria_gasto ?? 'otros');
        $alias = self::MAPA_CATEGORIA_GASTO[$categoria] ?? 'gastos_adm';

        $subtotal = (float) ($gasto->subtotal ?? $gasto->total);
        $igv = (float) ($gasto->igv ?? 0);
        $total = (float) $gasto->total;

        $partidas = [
            ['alias' => $alias, 'debe' => $subtotal, 'haber' => 0],
        ];

        if ($igv > 0) {
            $partidas[] = ['alias' => 'igv', 'debe' => $igv, 'haber' => 0];
        }

        $partidas[] = ['alias' => 'caja', 'debe' => 0, 'haber' => $total];

        $this->crearAsiento($empresaId, [
            'fecha' => $gasto->fecha ?? now()->toDateString(),
            'tipo' => 'gasto',
            'glosa' => "Gasto: {$gasto->concepto}",
            'origen' => 'gastos',
            'origen_id' => $gasto->id,
            'partidas' => $partidas,
        ]);
    }

    // ════════════════════════════════════════════════════════════════════
    //  G. ASIENTO DE ANULACIÓN (REVERSA CONTABLE)
    // ════════════════════════════════════════════════════════════════════
    /**
     * Genera un asiento espejo que revierte el original.
     */
    public function registrarReversa(int $empresaId, string $origenTabla, int $origenId, string $motivo = ''): void
    {
        $asientoOriginal = DB::table('asientos_contables')
            ->where('empresa_id', $empresaId)
            ->where('origen', $origenTabla)
            ->where('origen_id', $origenId)
            ->where('estado', 'validado')
            ->first();

        if (!$asientoOriginal)
            return;

        $partidasOriginales = DB::table('asiento_partidas')
            ->where('asiento_id', $asientoOriginal->id)
            ->get();

        $partidasReversadas = $partidasOriginales->map(fn($p) => [
            'cuenta_id' => $p->cuenta_id,
            'debe' => $p->haber,  // swap
            'haber' => $p->debe,   // swap
        ])->toArray();

        $this->crearAsientoDirecto($empresaId, [
            'fecha' => now()->toDateString(),
            'tipo' => 'anulacion',
            'glosa' => "Anulación: {$asientoOriginal->glosa}" . ($motivo ? " — {$motivo}" : ''),
            'origen' => $origenTabla,
            'origen_id' => $origenId,
            'partidas' => $partidasReversadas,
        ]);
    }

    // ════════════════════════════════════════════════════════════════════
    //  CORE: Crear asiento real en la BD
    // ════════════════════════════════════════════════════════════════════

    /**
     * Crea un asiento contable completo usando aliases de cuentas.
     * Resuelve cada alias a su cuenta_id real y auto-crea las cuentas si no existen.
     */
    private function crearAsiento(int $empresaId, array $config): void
    {
        try {
            DB::transaction(function () use ($empresaId, $config) {
                // Resolver aliases → cuenta_id
                $partidasResueltas = [];
                foreach ($config['partidas'] as $p) {
                    $cuentaId = $this->resolverCuenta($empresaId, $p['alias']);
                    if (!$cuentaId)
                        continue;

                    $partidasResueltas[] = [
                        'cuenta_id' => $cuentaId,
                        'debe' => $p['debe'],
                        'haber' => $p['haber'],
                    ];
                }

                if (count($partidasResueltas) < 2)
                    return;

                $this->insertarAsiento($empresaId, $config, $partidasResueltas);
            });
        } catch (\Exception $e) {
            Log::error("ContabilidadAutomática: Error generando asiento", [
                'glosa' => $config['glosa'] ?? '',
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Crea un asiento contable usando cuenta_id directos (para anulaciones).
     */
    private function crearAsientoDirecto(int $empresaId, array $config): void
    {
        try {
            DB::transaction(function () use ($empresaId, $config) {
                $this->insertarAsiento($empresaId, $config, $config['partidas']);
            });
        } catch (\Exception $e) {
            Log::error("ContabilidadAutomática: Error generando reversa", [
                'glosa' => $config['glosa'] ?? '',
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Inserta el registro del asiento y sus líneas de partida.
     */
    private function insertarAsiento(int $empresaId, array $config, array $partidas): void
    {
        $fecha = $config['fecha'] ?? now()->toDateString();
        $periodo = substr($fecha, 0, 7);

        // Correlativo
        $ultimo = DB::table('asientos_contables')
            ->where('empresa_id', $empresaId)
            ->whereYear('fecha', substr($fecha, 0, 4))
            ->count() + 1;
        $numero = substr($fecha, 0, 4) . '-' . str_pad($ultimo, 5, '0', STR_PAD_LEFT);

        $totalDebe = collect($partidas)->sum('debe');
        $totalHaber = collect($partidas)->sum('haber');

        // Validar cuadre
        if (abs($totalDebe - $totalHaber) > 0.02) {
            Log::warning("ContabilidadAutomática: Asiento descuadrado — {$config['glosa']}", [
                'debe' => $totalDebe,
                'haber' => $totalHaber
            ]);
            return;
        }

        $asientoId = DB::table('asientos_contables')->insertGetId([
            'empresa_id' => $empresaId,
            'user_id' => auth()->id() ?? 1,
            'numero_asiento' => $numero,
            'fecha' => $fecha,
            'periodo' => $periodo,
            'tipo' => $config['tipo'] ?? 'otro',
            'glosa' => $config['glosa'],
            'total_debe' => $totalDebe,
            'total_haber' => $totalHaber,
            'estado' => 'validado',  // Los asientos automáticos se validan inmediatamente
            'origen' => $config['origen'] ?? null,
            'origen_id' => $config['origen_id'] ?? null,
            'observaciones' => 'Generado automáticamente por el sistema.',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        foreach ($partidas as $idx => $partida) {
            DB::table('asiento_partidas')->insert([
                'asiento_id' => $asientoId,
                'cuenta_id' => $partida['cuenta_id'],
                'orden' => $idx + 1,
                'glosa_partida' => null,
                'debe' => $partida['debe'],
                'haber' => $partida['haber'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Resuelve un alias de cuenta a un ID real de plan_cuentas.
     * Si la cuenta no existe, la crea automáticamente.
     */
    private function resolverCuenta(int $empresaId, string $alias): ?int
    {
        $config = self::CUENTAS_DEFAULT[$alias] ?? null;
        if (!$config)
            return null;

        $cuenta = DB::table('plan_cuentas')
            ->where('empresa_id', $empresaId)
            ->where('codigo', $config['codigo'])
            ->first();

        if ($cuenta)
            return $cuenta->id;

        // Auto-crear la cuenta
        return DB::table('plan_cuentas')->insertGetId([
            'empresa_id' => $empresaId,
            'codigo' => $config['codigo'],
            'nombre' => $config['nombre'],
            'tipo' => $config['tipo'],
            'naturaleza' => $config['naturaleza'],
            'nivel' => 2,
            'acepta_movimiento' => true,
            'activo' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
