<?php

namespace App\Livewire;

use App\Models\Empresa;
use App\Traits\TieneContextoEmpresa;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;

class ConfiguracionEmpresa extends Component
{
    use TieneContextoEmpresa, WithFileUploads;

    // ── Datos de la Empresa ──
    public string $razon_social = '';
    public string $nombre_comercial = '';
    public string $ruc = '';
    public string $direccion_fiscal = '';
    public string $ubigeo = '';
    public string $departamento = '';
    public string $provincia = '';
    public string $distrito = '';
    public string $telefono = '';
    public string $email = '';
    public string $web = '';

    // ── Credenciales SUNAT ──
    public string $sunat_usuario_sol = '';
    public string $sunat_clave_sol = '';
    public string $sunat_entorno = 'beta';
    public string $sunat_client_id = '';
    public string $sunat_client_secret = '';

    // ── Certificado Digital ──
    public $certificado = null; // Upload
    public bool $tieneCertificado = false;
    public string $certificadoNombre = '';

    // ── Notificaciones ──
    public string $notificacion = '';
    public string $notificacionTipo = ''; // success, error, warning, info
    public bool $mostrarNotificacion = false;

    // ── Test de Conexión ──
    public bool $probandoConexion = false;
    public ?array $resultadoConexion = null;

    public function mount(): void
    {
        $this->inicializarContexto();

        $empresa = Empresa::find($this->empresaId);
        if (!$empresa) return;

        $this->razon_social = $empresa->razon_social ?? '';
        $this->nombre_comercial = $empresa->nombre_comercial ?? '';
        $this->ruc = $empresa->ruc ?? '';
        $this->direccion_fiscal = $empresa->direccion_fiscal ?? '';
        $this->ubigeo = $empresa->ubigeo ?? '';
        $this->departamento = $empresa->departamento ?? '';
        $this->provincia = $empresa->provincia ?? '';
        $this->distrito = $empresa->distrito ?? '';
        $this->telefono = $empresa->telefono ?? '';
        $this->email = $empresa->email ?? '';
        $this->web = $empresa->web ?? '';

        $this->sunat_usuario_sol = $empresa->sunat_usuario_sol ?? '';
        $this->sunat_clave_sol = $empresa->sunat_clave_sol ?? '';
        $this->sunat_entorno = $empresa->sunat_entorno ?? 'beta';
        $this->sunat_client_id = $empresa->sunat_client_id ?? '';
        $this->sunat_client_secret = $empresa->sunat_client_secret ?? '';

        // Verificar si existe certificado
        $certPath = storage_path("app/certificados/{$empresa->ruc}.pfx");
        $this->tieneCertificado = file_exists($certPath);
        if ($this->tieneCertificado) {
            $this->certificadoNombre = "{$empresa->ruc}.pfx";
        }
    }

    public function guardar(): void
    {
        $this->validate([
            'razon_social' => 'required|string|max:200',
            'ruc' => 'required|string|size:11',
            'direccion_fiscal' => 'required|string|max:300',
            'sunat_entorno' => 'required|in:beta,produccion',
        ]);

        $empresa = Empresa::find($this->empresaId);
        if (!$empresa) return;

        $empresa->update([
            'razon_social' => $this->razon_social,
            'nombre_comercial' => $this->nombre_comercial,
            'ruc' => $this->ruc,
            'direccion_fiscal' => $this->direccion_fiscal,
            'ubigeo' => $this->ubigeo,
            'departamento' => $this->departamento,
            'provincia' => $this->provincia,
            'distrito' => $this->distrito,
            'telefono' => $this->telefono,
            'email' => $this->email,
            'web' => $this->web,
            'sunat_usuario_sol' => $this->sunat_usuario_sol,
            'sunat_clave_sol' => $this->sunat_clave_sol,
            'sunat_entorno' => $this->sunat_entorno,
            'sunat_client_id' => $this->sunat_client_id,
            'sunat_client_secret' => $this->sunat_client_secret,
        ]);

        $this->mostrarAlerta('success', '✅ Configuración guardada correctamente.');
    }

    public function subirCertificado(): void
    {
        $this->validate([
            'certificado' => 'required|file|max:5120',
            'ruc' => 'required|string|size:11',
        ]);

        $certDir = storage_path('app/certificados');
        if (!is_dir($certDir)) {
            mkdir($certDir, 0755, true);
        }

        $destino = "{$certDir}/{$this->ruc}.pfx";
        $this->certificado->storeAs('certificados', "{$this->ruc}.pfx");

        $this->tieneCertificado = file_exists($destino);
        $this->certificadoNombre = "{$this->ruc}.pfx";
        $this->certificado = null;

        // Actualizar en la empresa
        $empresa = Empresa::find($this->empresaId);
        if ($empresa) {
            $empresa->update(['sunat_certificado_path' => "certificados/{$this->ruc}.pfx"]);
        }

        $this->mostrarAlerta('success', '✅ Certificado digital subido correctamente como ' . $this->certificadoNombre);
    }

    public function probarConexionSunat(): void
    {
        $this->probandoConexion = true;
        $this->resultadoConexion = null;

        try {
            $empresa = Empresa::find($this->empresaId);
            if (!$empresa) {
                $this->resultadoConexion = [
                    'success' => false,
                    'titulo' => 'Error de configuración',
                    'mensaje' => 'No se encontró la empresa en la base de datos.',
                ];
                $this->probandoConexion = false;
                return;
            }

            // Validar que los campos mínimos estén rellenados
            if (empty($empresa->ruc) || strlen($empresa->ruc) !== 11) {
                $this->resultadoConexion = [
                    'success' => false,
                    'titulo' => 'RUC inválido',
                    'mensaje' => 'El RUC debe tener exactamente 11 dígitos.',
                ];
                $this->probandoConexion = false;
                return;
            }

            if (empty($empresa->sunat_usuario_sol) || empty($empresa->sunat_clave_sol)) {
                $this->resultadoConexion = [
                    'success' => false,
                    'titulo' => 'Credenciales SOL incompletas',
                    'mensaje' => 'Ingresa tu Usuario SOL y Clave SOL antes de probar la conexión.',
                ];
                $this->probandoConexion = false;
                return;
            }

            // Verificar certificado
            $certPath = storage_path("app/certificados/{$empresa->ruc}.pfx");
            if (!file_exists($certPath)) {
                if ($empresa->sunat_entorno === 'produccion') {
                    $this->resultadoConexion = [
                        'success' => false,
                        'titulo' => 'Certificado digital no encontrado',
                        'mensaje' => "No se encontró el archivo {$empresa->ruc}.pfx en storage/app/certificados/. Es obligatorio para producción.",
                    ];
                    $this->probandoConexion = false;
                    return;
                } else {
                    $this->resultadoConexion = [
                        'success' => true,
                        'titulo' => ' Conexión BETA (sin certificado)',
                        'mensaje' => "Entorno BETA activo. El sistema funcionará en MODO MOCK (simulación) porque no se encontró el certificado digital. Para producción suba su archivo .pfx.",
                        'entorno' => 'BETA',
                        'ruc' => $empresa->ruc,
                        'usuario_sol' => $empresa->sunat_usuario_sol,
                    ];
                    $this->probandoConexion = false;
                    return;
                }
            }

            // Intentar inicializar el servicio de facturación
            $service = app(\App\Services\Sunat\FacturacionElectronicaService::class);
            $service->inicializar($empresa);

            $entornoLabel = $empresa->sunat_entorno === 'produccion' ? 'PRODUCCIÓN' : 'BETA';
            $endpoint = $empresa->sunat_entorno === 'produccion'
                ? 'https://e-factura.sunat.gob.pe'
                : 'https://e-beta.sunat.gob.pe';

            $this->resultadoConexion = [
                'success' => true,
                'titulo' => '✅ Conexión con SUNAT establecida',
                'mensaje' => "Los datos de facturación electrónica están configurados correctamente.",
                'entorno' => $entornoLabel,
                'endpoint' => $endpoint,
                'ruc' => $empresa->ruc,
                'usuario_sol' => $empresa->sunat_usuario_sol,
                'certificado' => '✅ Cargado (' . $this->certificadoNombre . ')',
            ];

            Log::channel('sunat')->info("Prueba de conexión SUNAT exitosa", [
                'ruc' => $empresa->ruc,
                'entorno' => $entornoLabel,
            ]);

        } catch (\Exception $e) {
            $this->resultadoConexion = [
                'success' => false,
                'titulo' => '❌ Error de conexión con SUNAT',
                'mensaje' => $e->getMessage(),
            ];

            Log::channel('sunat')->error("Prueba de conexión SUNAT fallida", [
                'error' => $e->getMessage(),
            ]);
        }

        $this->probandoConexion = false;
    }

    public function cerrarResultado(): void
    {
        $this->resultadoConexion = null;
    }

    private function mostrarAlerta(string $tipo, string $mensaje): void
    {
        $this->notificacion = $mensaje;
        $this->notificacionTipo = $tipo;
        $this->mostrarNotificacion = true;

        // Auto-ocultar después de 4 segundos
        $this->dispatch('cerrar-alerta');
    }

    public function cerrarAlerta(): void
    {
        $this->mostrarNotificacion = false;
    }

    public function render()
    {
        return view('livewire.configuracion-empresa')
            ->layout('layouts.app')
            ->title('Configuración — ERP AgroMine');
    }
}
