<?php

namespace App\Services\Sunat;

use App\Models\Empresa;
use App\Models\Venta;
use App\Models\VentaDetalle;
use Greenter\See;
use Greenter\Model\Client\Client;
use Greenter\Model\Company\Company;
use Greenter\Model\Company\Address;
use Greenter\Model\Sale\Invoice;
use Greenter\Model\Sale\Note;
use Greenter\Model\Sale\SaleDetail;
use Greenter\Model\Sale\Legend;
use Greenter\Model\Summary\Summary;
use Greenter\Model\Summary\SummaryDetail;
use Greenter\Model\Voided\Voided;
use Greenter\Model\Voided\VoidedDetail;
use Greenter\Ws\Services\SunatEndpoints;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class FacturacionElectronicaService
{
    private See $see;
    private Empresa $empresa;
    private string $storageBase;

    public function __construct()
    {
        $this->storageBase = storage_path('app/sunat');
    }

    private bool $mockMode = false;

    /**
     * Inicializa la conexión con SUNAT usando credenciales de la empresa.
     */
    public function inicializar(Empresa $empresa): self
    {
        $this->empresa = $empresa;
        $this->see = new See();

        // Certificado digital PFX
        $certPath = storage_path("app/certificados/{$empresa->ruc}.pfx");

        if (!file_exists($certPath)) {
            if ($empresa->sunat_entorno === 'produccion') {
                throw new \RuntimeException("Certificado digital no encontrado para RUC {$empresa->ruc} en Producción.");
            } else {
                // En BETA, si no hay certificado, activamos MOCK MODE
                Log::warning("Certificado no encontrado en BETA para { $empresa->ruc }. Activando MOCK MODE para Facturación.");
                $this->mockMode = true;
                return $this;
            }
        }

        $this->see->setCertificate(file_get_contents($certPath));
        $this->see->setService(
            $empresa->sunat_entorno === 'produccion'
            ? SunatEndpoints::FE_PRODUCCION
            : SunatEndpoints::FE_BETA
        );
        $this->see->setClaveSOL(
            $empresa->ruc,
            $empresa->sunat_usuario_sol,
            $empresa->sunat_clave_sol
        );

        return $this;
    }

    /**
     * Envía una Factura (01) o Boleta (03) a SUNAT.
     */
    public function enviarComprobante(Venta $venta): array
    {
        $venta->loadMissing(['detalles.producto.unidadMedida', 'cliente', 'empresa']);

        if (!isset($this->empresa)) {
            $this->inicializar($venta->empresa);
        }

        $invoice = $this->construirComprobante($venta);
        $nombreXml = "{$this->empresa->ruc}-{$venta->tipo_documento}-{$venta->serie}-{$venta->correlativo}";

        // MOCK MODE: Entorno pruebas sin certificado
        if ($this->mockMode) {
            $venta->update([
                'estado' => Venta::ESTADO_ACEPTADO,
                'sunat_codigo_respuesta' => '0',
                'sunat_mensaje_respuesta' => 'MOCK: La Factura ha sido aceptada',
                'sunat_hash' => 'MOCK-HASH-435435DFSDFS',
            ]);
            Log::channel('sunat')->info("CPE {$nombreXml} enviado EN MODO MOCK.");

            return [
                'success' => true,
                'codigo' => '0',
                'mensaje' => 'MOCK: La comprobante numero ' . "{$venta->serie}-{$venta->correlativo}" . ' ha sido aceptada',
                'observaciones' => [],
                'hash' => 'MOCK-HASH...',
                'xml_path' => null,
                'cdr_path' => null,
            ];
        }

        // FLUJO NORMAL:
        $result = $this->see->send($invoice);

        // Guardar XML firmado
        $this->guardarXml($nombreXml, $this->see->getFactory()->getLastXml());

        if ($result->isSuccess()) {
            $cdr = $result->getCdrResponse();
            $this->guardarCdr($nombreXml, $result->getCdrZip());

            $venta->update([
                'estado' => $cdr->getCode() === '0' ? Venta::ESTADO_ACEPTADO : Venta::ESTADO_RECHAZADO,
                'sunat_codigo_respuesta' => $cdr->getCode(),
                'sunat_mensaje_respuesta' => $cdr->getDescription(),
                'sunat_hash' => $invoice->getHash() ?? null,
                'xml_path' => "sunat/xml/{$nombreXml}.xml",
                'cdr_path' => "sunat/cdr/R-{$nombreXml}.zip",
            ]);

            Log::channel('sunat')->info("CPE {$nombreXml} enviado. Código: {$cdr->getCode()}", [
                'descripcion' => $cdr->getDescription(),
                'observaciones' => $cdr->getNotes(),
            ]);

            return [
                'success' => true,
                'codigo' => $cdr->getCode(),
                'mensaje' => $cdr->getDescription(),
                'observaciones' => $cdr->getNotes(),
                'hash' => $invoice->getHash(),
                'xml_path' => "sunat/xml/{$nombreXml}.xml",
                'cdr_path' => "sunat/cdr/R-{$nombreXml}.zip",
            ];
        }

        // Error de envío
        $error = $result->getError();
        $venta->update([
            'estado' => Venta::ESTADO_RECHAZADO,
            'sunat_codigo_respuesta' => $error->getCode(),
            'sunat_mensaje_respuesta' => $error->getMessage(),
        ]);

        Log::channel('sunat')->error("Error CPE {$nombreXml}: {$error->getCode()} - {$error->getMessage()}");

        return [
            'success' => false,
            'codigo' => $error->getCode(),
            'mensaje' => $error->getMessage(),
        ];
    }

    /**
     * Genera el Resumen Diario de Boletas.
     */
    public function enviarResumenDiario(array $ventas, \DateTime $fechaEmision): array
    {
        $empresa = $this->empresa;
        $company = $this->crearCompany();

        $correlativo = str_pad(
            Venta::whereDate('fecha_emision', $fechaEmision)->where('tipo_documento', '03')->count(),
            1,
            '0'
        );

        $summary = new Summary();
        $summary->setFecGeneracion(new \DateTime())
            ->setFecResumen($fechaEmision)
            ->setCorrelativo($correlativo)
            ->setCompany($company);

        $detalles = [];
        foreach ($ventas as $i => $venta) {
            $det = new SummaryDetail();
            $det->setTipoDoc($venta->tipo_documento)
                ->setSerieNro("{$venta->serie}-{$venta->correlativo}")
                ->setEstado('1') // 1=Agregar, 2=Modificar, 3=Anulado
                ->setClienteTipo($venta->cliente->tipo_documento === 'RUC' ? '6' : '1')
                ->setClienteNro($venta->cliente->numero_documento)
                ->setTotal((float) $venta->total)
                ->setMtoOperGravadas((float) $venta->base_imponible)
                ->setMtoIGV((float) $venta->igv);
            $detalles[] = $det;
        }

        $summary->setDetails($detalles);
        $result = $this->see->send($summary);

        if ($result->isSuccess()) {
            return [
                'success' => true,
                'ticket' => $result->getTicket(),
                'mensaje' => 'Resumen diario enviado. Use el ticket para consultar el estado.',
            ];
        }

        return [
            'success' => false,
            'codigo' => $result->getError()->getCode(),
            'mensaje' => $result->getError()->getMessage(),
        ];
    }

    /**
     * Comunicación de Baja (anulación vía SUNAT).
     */
    public function enviarComunicacionBaja(Venta $venta, string $motivo): array
    {
        $company = $this->crearCompany();

        $correlativo = str_pad(
            Venta::whereDate('fecha_emision', today())->where('estado', Venta::ESTADO_ANULADO)->count() + 1,
            1,
            '0'
        );

        $voided = new Voided();
        $voided->setFecGeneracion(new \DateTime())
            ->setFecComunicacion($venta->fecha_emision)
            ->setCorrelativo($correlativo)
            ->setCompany($company);

        $detail = new VoidedDetail();
        $detail->setTipoDoc($venta->tipo_documento)
            ->setSerie($venta->serie)
            ->setCorrelativo($venta->correlativo)
            ->setDesMotivoBaja($motivo);

        $voided->setDetails([$detail]);
        $result = $this->see->send($voided);

        if ($result->isSuccess()) {
            $venta->update([
                'estado' => Venta::ESTADO_ANULADO,
                'sunat_ticket' => $result->getTicket(),
            ]);

            return [
                'success' => true,
                'ticket' => $result->getTicket(),
                'mensaje' => 'Comunicación de baja enviada.',
            ];
        }

        return [
            'success' => false,
            'codigo' => $result->getError()->getCode(),
            'mensaje' => $result->getError()->getMessage(),
        ];
    }

    /**
     * Consulta el estado de un ticket asíncrono (resúmenes, bajas).
     */
    public function consultarTicket(string $ticket): array
    {
        $result = $this->see->getStatus($ticket);

        if ($result->isSuccess()) {
            $cdr = $result->getCdrResponse();
            return [
                'success' => true,
                'codigo' => $cdr->getCode(),
                'mensaje' => $cdr->getDescription(),
                'observaciones' => $cdr->getNotes(),
            ];
        }

        return [
            'success' => false,
            'codigo' => $result->getError()->getCode(),
            'mensaje' => $result->getError()->getMessage(),
        ];
    }

    /**
     * Enviar Nota de Crédito.
     */
    public function enviarNotaCredito(Venta $nota): array
    {
        $nota->loadMissing(['detalles.producto.unidadMedida', 'cliente', 'empresa', 'ventaReferencia']);

        if (!isset($this->empresa)) {
            $this->inicializar($nota->empresa);
        }

        $creditNote = $this->construirNotaCredito($nota);
        $result = $this->see->send($creditNote);

        $nombreXml = "{$this->empresa->ruc}-07-{$nota->serie}-{$nota->correlativo}";
        $this->guardarXml($nombreXml, $this->see->getFactory()->getLastXml());

        if ($result->isSuccess()) {
            $cdr = $result->getCdrResponse();
            $this->guardarCdr($nombreXml, $result->getCdrZip());

            $nota->update([
                'estado' => $cdr->getCode() === '0' ? Venta::ESTADO_ACEPTADO : Venta::ESTADO_RECHAZADO,
                'sunat_codigo_respuesta' => $cdr->getCode(),
                'sunat_mensaje_respuesta' => $cdr->getDescription(),
                'xml_path' => "sunat/xml/{$nombreXml}.xml",
                'cdr_path' => "sunat/cdr/R-{$nombreXml}.zip",
            ]);

            return [
                'success' => true,
                'codigo' => $cdr->getCode(),
                'mensaje' => $cdr->getDescription(),
            ];
        }

        $error = $result->getError();
        $nota->update([
            'estado' => Venta::ESTADO_RECHAZADO,
            'sunat_codigo_respuesta' => $error->getCode(),
            'sunat_mensaje_respuesta' => $error->getMessage(),
        ]);

        return [
            'success' => false,
            'codigo' => $error->getCode(),
            'mensaje' => $error->getMessage(),
        ];
    }

    // ═══════════════════════════════════════════════════════════════
    //  CONSTRUCTORES DE DOCUMENTOS UBL 2.1
    // ═══════════════════════════════════════════════════════════════

    private function construirComprobante(Venta $venta): Invoice
    {
        $client = $this->crearClient($venta->cliente);
        $company = $this->crearCompany();

        $invoice = new Invoice();
        $invoice->setUblVersion('2.1')
            ->setTipoOperacion('0101') // Venta interna
            ->setTipoDoc($venta->tipo_documento)
            ->setSerie($venta->serie)
            ->setCorrelativo($venta->correlativo)
            ->setFechaEmision($venta->fecha_emision)
            ->setTipoMoneda($venta->moneda === 'USD' ? 'USD' : 'PEN')
            ->setCompany($company)
            ->setClient($client)
            ->setMtoOperGravadas((float) $venta->base_imponible)
            ->setMtoIGV((float) $venta->igv)
            ->setTotalImpuestos((float) $venta->igv)
            ->setValorVenta((float) $venta->base_imponible)
            ->setSubTotal((float) $venta->total)
            ->setMtoImpVenta((float) $venta->total);

        if ($venta->descuento_global > 0) {
            $invoice->setDescuentos([
                (new \Greenter\Model\Sale\Charge())
                    ->setCodTipo('02')
                    ->setFactor(round($venta->descuento_global / $venta->subtotal, 5))
                    ->setMonto((float) $venta->descuento_global)
                    ->setMontoBase((float) $venta->subtotal)
            ]);
        }

        // Forma de pago
        if ($venta->forma_pago === 'credito' && $venta->cuotas->isNotEmpty()) {
            $formaPago = new \Greenter\Model\Sale\FormaPagos\FormaPagoCredito();
            $formaPago->setTipo('Credito')
                ->setMonto((float) $venta->total);

            $cuotas = [];
            foreach ($venta->cuotas as $i => $cuota) {
                $det = new \Greenter\Model\Sale\FormaPagos\CuotaDet();
                $det->setMoneda($venta->moneda === 'USD' ? 'USD' : 'PEN')
                    ->setMonto((float) $cuota->monto)
                    ->setFechaVencimiento($cuota->fecha_vencimiento);
                $cuotas[] = $det;
            }

            $invoice->setFormaPago($formaPago);
            $invoice->setCuotas($cuotas);
        }

        // Detalle de ítems
        $details = [];
        foreach ($venta->detalles as $det) {
            $detail = new SaleDetail();
            $detail->setCodProducto($det->producto->codigo_interno ?? $det->codigo_producto)
                ->setUnidad($det->producto->unidadMedida->codigo_sunat ?? 'NIU')
                ->setDescripcion($det->descripcion)
                ->setCantidad((float) $det->cantidad)
                ->setMtoValorUnitario((float) $det->valor_unitario)
                ->setMtoValorVenta((float) $det->valor_venta)
                ->setMtoBaseIgv((float) $det->valor_venta)
                ->setPorcentajeIgv($det->porcentaje_igv ?? 18.00)
                ->setIgv((float) $det->igv)
                ->setTipAfeIgv($det->tipo_afectacion_igv ?? '10') // 10=Gravado
                ->setTotalImpuestos((float) $det->igv)
                ->setMtoPrecioUnitario((float) $det->precio_unitario);

            if ($det->descuento > 0) {
                $detail->setDescuento((float) $det->descuento);
            }

            $details[] = $detail;
        }
        $invoice->setDetails($details);

        // Leyendas
        $legends = [];
        $legends[] = (new Legend())
            ->setCode('1000')
            ->setValue($venta->total_letras ?? $this->montoALetras($venta->total, $venta->moneda));

        $invoice->setLegends($legends);

        return $invoice;
    }

    private function construirNotaCredito(Venta $nota): Note
    {
        $client = $this->crearClient($nota->cliente);
        $company = $this->crearCompany();
        $ref = $nota->ventaReferencia;

        $creditNote = new Note();
        $creditNote->setUblVersion('2.1')
            ->setTipoDoc('07')
            ->setSerie($nota->serie)
            ->setCorrelativo($nota->correlativo)
            ->setFechaEmision($nota->fecha_emision)
            ->setTipoMoneda($nota->moneda === 'USD' ? 'USD' : 'PEN')
            ->setCompany($company)
            ->setClient($client)
            ->setMtoOperGravadas((float) $nota->base_imponible)
            ->setMtoIGV((float) $nota->igv)
            ->setTotalImpuestos((float) $nota->igv)
            ->setValorVenta((float) $nota->base_imponible)
            ->setSubTotal((float) $nota->total)
            ->setMtoImpVenta((float) $nota->total)
            ->setCodMotivo($nota->tipo_nota ?? '01') // 01=Anulación de la operación
            ->setDesMotivo($nota->motivo_nota ?? 'Anulación de la operación')
            ->setTipDocAfectado($ref->tipo_documento)
            ->setNumDocfectado("{$ref->serie}-{$ref->correlativo}");

        $details = [];
        foreach ($nota->detalles as $det) {
            $detail = new SaleDetail();
            $detail->setCodProducto($det->codigo_producto ?? $det->producto->codigo_interno)
                ->setUnidad($det->producto->unidadMedida->codigo_sunat ?? 'NIU')
                ->setDescripcion($det->descripcion)
                ->setCantidad((float) $det->cantidad)
                ->setMtoValorUnitario((float) $det->valor_unitario)
                ->setMtoValorVenta((float) $det->valor_venta)
                ->setMtoBaseIgv((float) $det->valor_venta)
                ->setPorcentajeIgv(18.00)
                ->setIgv((float) $det->igv)
                ->setTipAfeIgv('10')
                ->setTotalImpuestos((float) $det->igv)
                ->setMtoPrecioUnitario((float) $det->precio_unitario);
            $details[] = $detail;
        }
        $creditNote->setDetails($details);
        $creditNote->setLegends([
            (new Legend())->setCode('1000')->setValue($this->montoALetras($nota->total, $nota->moneda))
        ]);

        return $creditNote;
    }

    // ═══════════════════════════════════════════════════════════════
    //  HELPERS
    // ═══════════════════════════════════════════════════════════════

    private function crearCompany(): Company
    {
        $address = new Address();
        $address->setUbigueo($this->empresa->ubigeo ?? '150101')
            ->setDepartamento($this->empresa->departamento ?? 'LIMA')
            ->setProvincia($this->empresa->provincia ?? 'LIMA')
            ->setDistrito($this->empresa->distrito ?? 'LIMA')
            ->setDireccion($this->empresa->direccion_fiscal);

        $company = new Company();
        $company->setRuc($this->empresa->ruc)
            ->setRazonSocial($this->empresa->razon_social)
            ->setNombreComercial($this->empresa->nombre_comercial ?? $this->empresa->razon_social)
            ->setAddress($address);

        return $company;
    }

    private function crearClient($cliente): Client
    {
        $tipoDocSunat = match ($cliente->tipo_documento) {
            'RUC' => '6',
            'DNI' => '1',
            'CE' => 'A',
            'PASAPORTE' => '7',
            default => '-',
        };

        $client = new Client();
        $client->setTipoDoc($tipoDocSunat)
            ->setNumDoc($cliente->numero_documento)
            ->setRznSocial($cliente->razon_social);

        if ($cliente->direccion) {
            $address = new Address();
            $address->setDireccion($cliente->direccion);
            $client->setAddress($address);
        }

        return $client;
    }

    private function guardarXml(string $nombre, string $xml): void
    {
        $dir = "{$this->storageBase}/xml";
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        file_put_contents("{$dir}/{$nombre}.xml", $xml);
    }

    private function guardarCdr(string $nombre, string $cdrZip): void
    {
        $dir = "{$this->storageBase}/cdr";
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        file_put_contents("{$dir}/R-{$nombre}.zip", $cdrZip);
    }

    /**
     * Convierte monto a texto para la leyenda 1000. Versión simplificada.
     */
    private function montoALetras(float $monto, string $moneda = 'PEN'): string
    {
        $entero = (int) $monto;
        $decimal = round(($monto - $entero) * 100);
        $monedaTexto = $moneda === 'USD' ? 'DÓLARES AMERICANOS' : 'SOLES';

        // Convertir entero a texto
        $texto = $this->numeroALetras($entero);

        return strtoupper($texto) . " CON {$decimal}/100 {$monedaTexto}";
    }

    private function numeroALetras(int $num): string
    {
        $unidades = ['', 'UNO', 'DOS', 'TRES', 'CUATRO', 'CINCO', 'SEIS', 'SIETE', 'OCHO', 'NUEVE'];
        $decenas = ['', 'DIEZ', 'VEINTE', 'TREINTA', 'CUARENTA', 'CINCUENTA', 'SESENTA', 'SETENTA', 'OCHENTA', 'NOVENTA'];
        $especiales = [
            11 => 'ONCE',
            12 => 'DOCE',
            13 => 'TRECE',
            14 => 'CATORCE',
            15 => 'QUINCE',
            16 => 'DIECISEIS',
            17 => 'DIECISIETE',
            18 => 'DIECIOCHO',
            19 => 'DIECINUEVE'
        ];
        $centenas = [
            '',
            'CIENTO',
            'DOSCIENTOS',
            'TRESCIENTOS',
            'CUATROCIENTOS',
            'QUINIENTOS',
            'SEISCIENTOS',
            'SETECIENTOS',
            'OCHOCIENTOS',
            'NOVECIENTOS'
        ];

        if ($num == 0)
            return 'CERO';
        if ($num == 100)
            return 'CIEN';

        $texto = '';
        if ($num >= 1000000) {
            $millones = (int) ($num / 1000000);
            $texto .= ($millones == 1 ? 'UN MILLON' : $this->numeroALetras($millones) . ' MILLONES');
            $num %= 1000000;
            if ($num > 0)
                $texto .= ' ';
        }
        if ($num >= 1000) {
            $miles = (int) ($num / 1000);
            $texto .= ($miles == 1 ? 'MIL' : $this->numeroALetras($miles) . ' MIL');
            $num %= 1000;
            if ($num > 0)
                $texto .= ' ';
        }
        if ($num >= 100) {
            $texto .= $centenas[(int) ($num / 100)];
            $num %= 100;
            if ($num > 0)
                $texto .= ' ';
        }
        if ($num >= 11 && $num <= 19) {
            $texto .= $especiales[$num];
        } elseif ($num >= 10) {
            $texto .= $decenas[(int) ($num / 10)];
            $num %= 10;
            if ($num > 0)
                $texto .= ' Y ' . $unidades[$num];
        } elseif ($num > 0) {
            $texto .= $unidades[$num];
        }

        return $texto;
    }
}
