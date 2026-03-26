<?php

namespace App\Services\Sunat;

use App\Models\Empresa;
use Greenter\Api;
use Greenter\Model\Despatch\Despatch;
use Greenter\Model\Despatch\DespatchDetail;
use Greenter\Model\Despatch\Direction;
use Greenter\Model\Despatch\Driver;
use Greenter\Model\Despatch\Shipment;
use Greenter\Model\Despatch\Transportist;
use Greenter\Model\Despatch\Vehicle;
use Greenter\Model\Client\Client;
use Greenter\Model\Company\Company;
use Greenter\Model\Company\Address;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class GuiaRemisionService
{
    private Api $api;
    private Empresa $empresa;

    /**
     * Inicializa conexión con la API GRE de SUNAT.
     * SUNAT migró las Guías de Remisión al nuevo sistema REST (GRE).
     */
    public function inicializar(Empresa $empresa): self
    {
        $this->empresa = $empresa;

        $certPath = storage_path("app/certificados/{$empresa->ruc}.pfx");
        if (!file_exists($certPath)) {
            throw new \RuntimeException("Certificado digital no encontrado para RUC {$empresa->ruc}");
        }

        $this->api = new Api(
            $empresa->sunat_entorno === 'produccion'
            ? [
                'auth' => 'https://api-seguridad.sunat.gob.pe/v1',
                'cpe' => 'https://api-cpe.sunat.gob.pe/v1',
            ]
            : [
                'auth' => 'https://gre-beta.sunat.gob.pe/v1',
                'cpe' => 'https://gre-beta.sunat.gob.pe/v1',
            ]
        );

        $this->api->setBuilderOptions([
            'strict_variables' => true,
            'optimizations' => 0,
            'debug' => $empresa->sunat_entorno !== 'produccion',
        ]);

        $this->api->setCertificate(file_get_contents($certPath));
        $this->api->setCredentials(
            $empresa->sunat_client_id ?? ($empresa->ruc . $empresa->sunat_usuario_sol),
            $empresa->sunat_client_secret ?? $empresa->sunat_clave_sol
        );

        return $this;
    }

    /**
     * Enviar Guía de Remisión Remitente (09).
     */
    public function enviarGuiaRemitente(array $datos): array
    {
        $company = $this->crearCompany();

        $despatch = new Despatch();
        $despatch->setVersion('2022')
            ->setTipoDoc('09')
            ->setSerie($datos['serie'])
            ->setCorrelativo($datos['correlativo'])
            ->setFechaEmision(new \DateTime($datos['fecha_emision']))
            ->setCompany($company);

        // Destinatario
        $client = new Client();
        $client->setTipoDoc($datos['destinatario']['tipo_documento'] === 'RUC' ? '6' : '1')
            ->setNumDoc($datos['destinatario']['numero_documento'])
            ->setRznSocial($datos['destinatario']['razon_social']);
        $despatch->setDestinatario($client);

        // Observaciones
        if (!empty($datos['observacion'])) {
            $despatch->setObservacion($datos['observacion']);
        }

        // Datos del envío
        $shipment = new Shipment();
        $shipment->setModTraslado($datos['envio']['modalidad'] ?? '01') // 01=Público, 02=Privado
            ->setCodTraslado($datos['envio']['motivo'] ?? '01') // 01=Venta, 04=Traslado entre establecimientos
            ->setFecTraslado(new \DateTime($datos['envio']['fecha_traslado']))
            ->setPesoTotal((float) ($datos['envio']['peso_total'] ?? 0))
            ->setUndPesoTotal($datos['envio']['unidad_peso'] ?? 'KGM')
            ->setNumBultos((int) ($datos['envio']['num_bultos'] ?? 0));

        // Punto de partida
        $partida = new Direction();
        $partida->setUbigueo($datos['envio']['partida']['ubigeo'])
            ->setDireccion($datos['envio']['partida']['direccion']);
        if (!empty($datos['envio']['partida']['cod_local'])) {
            $partida->setCodLocal($datos['envio']['partida']['cod_local']);
        }
        $shipment->setLlegada(
            (new Direction())
                ->setUbigueo($datos['envio']['llegada']['ubigeo'])
                ->setDireccion($datos['envio']['llegada']['direccion'])
        );
        $shipment->setPartida($partida);

        // Transportista (si modalidad pública)
        if (($datos['envio']['modalidad'] ?? '01') === '01' && !empty($datos['transportista'])) {
            $trans = new Transportist();
            $trans->setTipoDoc('6') // RUC
                ->setNumDoc($datos['transportista']['ruc'])
                ->setRznSocial($datos['transportista']['razon_social'])
                ->setNroMtc($datos['transportista']['mtc'] ?? null);
            $shipment->setTransportista($trans);
        }

        // Conductor y vehículo (si modalidad privada)
        if (($datos['envio']['modalidad'] ?? '01') === '02') {
            if (!empty($datos['conductor'])) {
                $driver = new Driver();
                $driver->setTipoDoc($datos['conductor']['tipo_documento'] ?? '1')
                    ->setNroDoc($datos['conductor']['numero_documento'])
                    ->setNames($datos['conductor']['nombres'])
                    ->setLastnames($datos['conductor']['apellidos'])
                    ->setLicencia($datos['conductor']['licencia']);
                $shipment->setDrivers([$driver]);
            }
            if (!empty($datos['vehiculo'])) {
                $vehicle = new Vehicle();
                $vehicle->setPlaca($datos['vehiculo']['placa']);
                $shipment->setVehicles([$vehicle]);
            }
        }

        $despatch->setEnvio($shipment);

        // Detalles (ítems)
        $details = [];
        foreach ($datos['items'] as $item) {
            $detail = new DespatchDetail();
            $detail->setCantidad((float) $item['cantidad'])
                ->setUnidad($item['unidad_medida'] ?? 'NIU')
                ->setDescripcion($item['descripcion'])
                ->setCodigo($item['codigo'] ?? '')
                ->setCodProdSunat($item['codigo_sunat'] ?? null);
            $details[] = $detail;
        }
        $despatch->setDetails($details);

        // Enviar a SUNAT
        $result = $this->api->send($despatch);
        $xmlSigned = $this->api->getLastXml();

        $nombreXml = "{$this->empresa->ruc}-09-{$datos['serie']}-{$datos['correlativo']}";
        $this->guardarXml($nombreXml, $xmlSigned);

        if ($result->isSuccess()) {
            $ticket = $result->getTicket();
            Log::channel('sunat')->info("GRE {$nombreXml} enviada. Ticket: {$ticket}");

            return [
                'success' => true,
                'ticket' => $ticket,
                'xml_path' => "sunat/xml/{$nombreXml}.xml",
                'mensaje' => 'Guía enviada exitosamente. Use el ticket para verificar el CDR.',
            ];
        }

        $error = $result->getError();
        Log::channel('sunat')->error("Error GRE {$nombreXml}: {$error->getCode()} - {$error->getMessage()}");

        return [
            'success' => false,
            'codigo' => $error->getCode(),
            'mensaje' => $error->getMessage(),
        ];
    }

    /**
     * Consultar el CDR de una GRE usando el ticket.
     */
    public function consultarCdr(string $ticket): array
    {
        $result = $this->api->getStatus($ticket);

        if ($result->isSuccess()) {
            $cdr = $result->getCdrResponse();

            return [
                'success' => true,
                'codigo' => $cdr->getCode(),
                'mensaje' => $cdr->getDescription(),
                'cdr_zip' => $result->getCdrZip(),
            ];
        }

        return [
            'success' => false,
            'codigo' => $result->getError()->getCode(),
            'mensaje' => $result->getError()->getMessage(),
        ];
    }

    /**
     * Enviar Guía de Remisión Transportista (31).
     */
    public function enviarGuiaTransportista(array $datos): array
    {
        // Misma lógica pero con tipoDoc='31'
        $datos['tipo_doc'] = '31';
        // El proceso es idéntico, cambia el código de tipo de documento
        return $this->enviarGuiaRemitente($datos);
    }

    // ═══════════════════════════════════════════════════════════════
    //  HELPERS
    // ═══════════════════════════════════════════════════════════════

    private function crearCompany(): Company
    {
        $address = new Address();
        $address->setUbigueo($this->empresa->ubigeo ?? '150101')
            ->setDireccion($this->empresa->direccion_fiscal);

        $company = new Company();
        $company->setRuc($this->empresa->ruc)
            ->setRazonSocial($this->empresa->razon_social)
            ->setNombreComercial($this->empresa->nombre_comercial ?? $this->empresa->razon_social)
            ->setAddress($address);

        return $company;
    }

    private function guardarXml(string $nombre, string $xml): void
    {
        $dir = storage_path('app/sunat/xml');
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        file_put_contents("{$dir}/{$nombre}.xml", $xml);
    }
}
