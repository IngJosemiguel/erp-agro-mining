<div>
    {{-- ── NOTIFICACIÓN FLASH ── --}}
    @if($mostrarNotificacion)
    <div class="alert alert-{{ $notificacionTipo }}" style="margin-bottom:1rem;display:flex;align-items:center;gap:0.5rem;padding:0.75rem 1rem;border-radius:8px;font-size:0.875rem;animation:slideDown .3s ease"
         wire:click="cerrarAlerta" wire:poll.4000ms="cerrarAlerta">
        @if($notificacionTipo === 'success')
            <i data-lucide="check-circle-2" style="width:18px;height:18px;color:#22c55e"></i>
        @else
            <i data-lucide="alert-circle" style="width:18px;height:18px;color:#ef4444"></i>
        @endif
        <span>{{ $notificacion }}</span>
    </div>
    @endif

    {{-- ── HEADER ── --}}
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.5rem">
        <div>
            <h1 style="font-size:1.5rem;font-weight:700;color:var(--text-primary);margin:0">Configuración de Empresa</h1>
            <p style="font-size:0.85rem;color:var(--text-secondary);margin:0.25rem 0 0">Datos fiscales, credenciales SUNAT y certificado digital para facturación electrónica</p>
        </div>
        <button wire:click="guardar" class="btn-primary" style="display:flex;align-items:center;gap:0.5rem;padding:0.625rem 1.25rem;border-radius:8px;font-weight:600;font-size:0.85rem;cursor:pointer;border:none;background:linear-gradient(135deg,#6366f1,#818cf8);color:#fff;box-shadow:0 2px 8px rgba(99,102,241,0.3);transition:all .2s">
            <i data-lucide="save" style="width:16px;height:16px"></i>
            Guardar Configuración
        </button>
    </div>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:1.5rem">

        {{-- ═══════════════════════════════════════════════════════
             COLUMNA IZQUIERDA: DATOS DE LA EMPRESA
        ═══════════════════════════════════════════════════════ --}}
        <div style="display:flex;flex-direction:column;gap:1.25rem">

            {{-- Card: Datos Generales --}}
            <div class="card" style="background:var(--card-bg);border:1px solid var(--border-color);border-radius:12px;padding:1.25rem">
                <div style="display:flex;align-items:center;gap:0.5rem;margin-bottom:1rem;padding-bottom:0.75rem;border-bottom:1px solid var(--border-color)">
                    <div style="width:32px;height:32px;border-radius:8px;background:linear-gradient(135deg,#6366f1,#818cf8);display:flex;align-items:center;justify-content:center">
                        <i data-lucide="building-2" style="width:16px;height:16px;color:#fff"></i>
                    </div>
                    <h3 style="font-size:0.95rem;font-weight:600;color:var(--text-primary);margin:0">Datos de la Empresa</h3>
                </div>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:0.75rem">
                    {{-- RUC --}}
                    <div>
                        <label style="font-size:0.75rem;font-weight:600;color:var(--text-secondary);text-transform:uppercase;letter-spacing:0.05em;display:block;margin-bottom:0.25rem">RUC *</label>
                        <input type="text" wire:model="ruc" maxlength="11" placeholder="20XXXXXXXXX"
                            style="width:100%;padding:0.5rem 0.75rem;border:1px solid var(--border-color);border-radius:8px;background:var(--input-bg,var(--card-bg));color:var(--text-primary);font-size:0.875rem;font-family:monospace;letter-spacing:0.05em;box-sizing:border-box">
                        @error('ruc') <span style="font-size:0.75rem;color:#ef4444">{{ $message }}</span> @enderror
                    </div>

                    {{-- Razón Social --}}
                    <div>
                        <label style="font-size:0.75rem;font-weight:600;color:var(--text-secondary);text-transform:uppercase;letter-spacing:0.05em;display:block;margin-bottom:0.25rem">Razón Social *</label>
                        <input type="text" wire:model="razon_social" placeholder="EMPRESA S.A.C."
                            style="width:100%;padding:0.5rem 0.75rem;border:1px solid var(--border-color);border-radius:8px;background:var(--input-bg,var(--card-bg));color:var(--text-primary);font-size:0.875rem;box-sizing:border-box">
                        @error('razon_social') <span style="font-size:0.75rem;color:#ef4444">{{ $message }}</span> @enderror
                    </div>

                    {{-- Nombre Comercial --}}
                    <div style="grid-column:span 2">
                        <label style="font-size:0.75rem;font-weight:600;color:var(--text-secondary);text-transform:uppercase;letter-spacing:0.05em;display:block;margin-bottom:0.25rem">Nombre Comercial</label>
                        <input type="text" wire:model="nombre_comercial" placeholder="Mi Negocio"
                            style="width:100%;padding:0.5rem 0.75rem;border:1px solid var(--border-color);border-radius:8px;background:var(--input-bg,var(--card-bg));color:var(--text-primary);font-size:0.875rem;box-sizing:border-box">
                    </div>

                    {{-- Dirección Fiscal --}}
                    <div style="grid-column:span 2">
                        <label style="font-size:0.75rem;font-weight:600;color:var(--text-secondary);text-transform:uppercase;letter-spacing:0.05em;display:block;margin-bottom:0.25rem">Dirección Fiscal *</label>
                        <input type="text" wire:model="direccion_fiscal" placeholder="Av. Principal 123, Lima"
                            style="width:100%;padding:0.5rem 0.75rem;border:1px solid var(--border-color);border-radius:8px;background:var(--input-bg,var(--card-bg));color:var(--text-primary);font-size:0.875rem;box-sizing:border-box">
                        @error('direccion_fiscal') <span style="font-size:0.75rem;color:#ef4444">{{ $message }}</span> @enderror
                    </div>

                    {{-- Ubigeo / Departamento / Provincia / Distrito --}}
                    <div>
                        <label style="font-size:0.75rem;font-weight:600;color:var(--text-secondary);text-transform:uppercase;letter-spacing:0.05em;display:block;margin-bottom:0.25rem">Ubigeo</label>
                        <input type="text" wire:model="ubigeo" maxlength="6" placeholder="150101"
                            style="width:100%;padding:0.5rem 0.75rem;border:1px solid var(--border-color);border-radius:8px;background:var(--input-bg,var(--card-bg));color:var(--text-primary);font-size:0.875rem;font-family:monospace;box-sizing:border-box">
                    </div>
                    <div>
                        <label style="font-size:0.75rem;font-weight:600;color:var(--text-secondary);text-transform:uppercase;letter-spacing:0.05em;display:block;margin-bottom:0.25rem">Departamento</label>
                        <input type="text" wire:model="departamento" placeholder="Lima"
                            style="width:100%;padding:0.5rem 0.75rem;border:1px solid var(--border-color);border-radius:8px;background:var(--input-bg,var(--card-bg));color:var(--text-primary);font-size:0.875rem;box-sizing:border-box">
                    </div>
                    <div>
                        <label style="font-size:0.75rem;font-weight:600;color:var(--text-secondary);text-transform:uppercase;letter-spacing:0.05em;display:block;margin-bottom:0.25rem">Provincia</label>
                        <input type="text" wire:model="provincia" placeholder="Lima"
                            style="width:100%;padding:0.5rem 0.75rem;border:1px solid var(--border-color);border-radius:8px;background:var(--input-bg,var(--card-bg));color:var(--text-primary);font-size:0.875rem;box-sizing:border-box">
                    </div>
                    <div>
                        <label style="font-size:0.75rem;font-weight:600;color:var(--text-secondary);text-transform:uppercase;letter-spacing:0.05em;display:block;margin-bottom:0.25rem">Distrito</label>
                        <input type="text" wire:model="distrito" placeholder="Lima"
                            style="width:100%;padding:0.5rem 0.75rem;border:1px solid var(--border-color);border-radius:8px;background:var(--input-bg,var(--card-bg));color:var(--text-primary);font-size:0.875rem;box-sizing:border-box">
                    </div>
                </div>
            </div>

            {{-- Card: Contacto --}}
            <div class="card" style="background:var(--card-bg);border:1px solid var(--border-color);border-radius:12px;padding:1.25rem">
                <div style="display:flex;align-items:center;gap:0.5rem;margin-bottom:1rem;padding-bottom:0.75rem;border-bottom:1px solid var(--border-color)">
                    <div style="width:32px;height:32px;border-radius:8px;background:linear-gradient(135deg,#06b6d4,#22d3ee);display:flex;align-items:center;justify-content:center">
                        <i data-lucide="phone" style="width:16px;height:16px;color:#fff"></i>
                    </div>
                    <h3 style="font-size:0.95rem;font-weight:600;color:var(--text-primary);margin:0">Contacto</h3>
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:0.75rem">
                    <div>
                        <label style="font-size:0.75rem;font-weight:600;color:var(--text-secondary);text-transform:uppercase;letter-spacing:0.05em;display:block;margin-bottom:0.25rem">Teléfono</label>
                        <input type="text" wire:model="telefono" placeholder="01-1234567"
                            style="width:100%;padding:0.5rem 0.75rem;border:1px solid var(--border-color);border-radius:8px;background:var(--input-bg,var(--card-bg));color:var(--text-primary);font-size:0.875rem;box-sizing:border-box">
                    </div>
                    <div>
                        <label style="font-size:0.75rem;font-weight:600;color:var(--text-secondary);text-transform:uppercase;letter-spacing:0.05em;display:block;margin-bottom:0.25rem">Email</label>
                        <input type="email" wire:model="email" placeholder="contacto@empresa.pe"
                            style="width:100%;padding:0.5rem 0.75rem;border:1px solid var(--border-color);border-radius:8px;background:var(--input-bg,var(--card-bg));color:var(--text-primary);font-size:0.875rem;box-sizing:border-box">
                    </div>
                    <div>
                        <label style="font-size:0.75rem;font-weight:600;color:var(--text-secondary);text-transform:uppercase;letter-spacing:0.05em;display:block;margin-bottom:0.25rem">Web</label>
                        <input type="text" wire:model="web" placeholder="www.empresa.pe"
                            style="width:100%;padding:0.5rem 0.75rem;border:1px solid var(--border-color);border-radius:8px;background:var(--input-bg,var(--card-bg));color:var(--text-primary);font-size:0.875rem;box-sizing:border-box">
                    </div>
                </div>
            </div>
        </div>

        {{-- ═══════════════════════════════════════════════════════
             COLUMNA DERECHA: SUNAT / FACTURACIÓN ELECTRÓNICA
        ═══════════════════════════════════════════════════════ --}}
        <div style="display:flex;flex-direction:column;gap:1.25rem">

            {{-- Card: Credenciales SUNAT --}}
            <div class="card" style="background:var(--card-bg);border:1px solid var(--border-color);border-radius:12px;padding:1.25rem">
                <div style="display:flex;align-items:center;gap:0.5rem;margin-bottom:1rem;padding-bottom:0.75rem;border-bottom:1px solid var(--border-color)">
                    <div style="width:32px;height:32px;border-radius:8px;background:linear-gradient(135deg,#f59e0b,#fbbf24);display:flex;align-items:center;justify-content:center">
                        <i data-lucide="shield-check" style="width:16px;height:16px;color:#fff"></i>
                    </div>
                    <h3 style="font-size:0.95rem;font-weight:600;color:var(--text-primary);margin:0">Facturación Electrónica — SUNAT</h3>
                </div>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:0.75rem">
                    {{-- Entorno --}}
                    <div style="grid-column:span 2">
                        <label style="font-size:0.75rem;font-weight:600;color:var(--text-secondary);text-transform:uppercase;letter-spacing:0.05em;display:block;margin-bottom:0.25rem">Entorno SUNAT *</label>
                        <select wire:model="sunat_entorno"
                            style="width:100%;padding:0.5rem 0.75rem;border:1px solid var(--border-color);border-radius:8px;background:var(--input-bg,var(--card-bg));color:var(--text-primary);font-size:0.875rem;box-sizing:border-box;cursor:pointer">
                            <option value="beta">🧪 BETA (Pruebas)</option>
                            <option value="produccion">🚀 PRODUCCIÓN (Real)</option>
                        </select>
                        @if($sunat_entorno === 'produccion')
                        <div style="margin-top:0.375rem;padding:0.5rem 0.75rem;border-radius:6px;background:rgba(239,68,68,0.1);border:1px solid rgba(239,68,68,0.2);font-size:0.75rem;color:#ef4444;display:flex;align-items:center;gap:0.375rem">
                            <i data-lucide="alert-triangle" style="width:14px;height:14px"></i>
                            <strong>PRODUCCIÓN:</strong> Los comprobantes emitidos tienen validez tributaria real ante la SUNAT.
                        </div>
                        @endif
                    </div>

                    {{-- Usuario SOL --}}
                    <div>
                        <label style="font-size:0.75rem;font-weight:600;color:var(--text-secondary);text-transform:uppercase;letter-spacing:0.05em;display:block;margin-bottom:0.25rem">Usuario SOL</label>
                        <input type="text" wire:model="sunat_usuario_sol" placeholder="MODDATOS"
                            style="width:100%;padding:0.5rem 0.75rem;border:1px solid var(--border-color);border-radius:8px;background:var(--input-bg,var(--card-bg));color:var(--text-primary);font-size:0.875rem;font-family:monospace;box-sizing:border-box">
                    </div>

                    {{-- Clave SOL --}}
                    <div>
                        <label style="font-size:0.75rem;font-weight:600;color:var(--text-secondary);text-transform:uppercase;letter-spacing:0.05em;display:block;margin-bottom:0.25rem">Clave SOL</label>
                        <input type="password" wire:model="sunat_clave_sol" placeholder="••••••••"
                            style="width:100%;padding:0.5rem 0.75rem;border:1px solid var(--border-color);border-radius:8px;background:var(--input-bg,var(--card-bg));color:var(--text-primary);font-size:0.875rem;font-family:monospace;box-sizing:border-box">
                    </div>

                    {{-- Client ID (API REST) --}}
                    <div>
                        <label style="font-size:0.75rem;font-weight:600;color:var(--text-secondary);text-transform:uppercase;letter-spacing:0.05em;display:block;margin-bottom:0.25rem">Client ID <span style="font-weight:400;text-transform:none">(API REST, opcional)</span></label>
                        <input type="text" wire:model="sunat_client_id" placeholder="Opcional"
                            style="width:100%;padding:0.5rem 0.75rem;border:1px solid var(--border-color);border-radius:8px;background:var(--input-bg,var(--card-bg));color:var(--text-primary);font-size:0.875rem;box-sizing:border-box">
                    </div>

                    {{-- Client Secret (API REST) --}}
                    <div>
                        <label style="font-size:0.75rem;font-weight:600;color:var(--text-secondary);text-transform:uppercase;letter-spacing:0.05em;display:block;margin-bottom:0.25rem">Client Secret <span style="font-weight:400;text-transform:none">(API REST, opcional)</span></label>
                        <input type="password" wire:model="sunat_client_secret" placeholder="Opcional"
                            style="width:100%;padding:0.5rem 0.75rem;border:1px solid var(--border-color);border-radius:8px;background:var(--input-bg,var(--card-bg));color:var(--text-primary);font-size:0.875rem;box-sizing:border-box">
                    </div>
                </div>
            </div>

            {{-- Card: Certificado Digital --}}
            <div class="card" style="background:var(--card-bg);border:1px solid var(--border-color);border-radius:12px;padding:1.25rem">
                <div style="display:flex;align-items:center;gap:0.5rem;margin-bottom:1rem;padding-bottom:0.75rem;border-bottom:1px solid var(--border-color)">
                    <div style="width:32px;height:32px;border-radius:8px;background:linear-gradient(135deg,#10b981,#34d399);display:flex;align-items:center;justify-content:center">
                        <i data-lucide="file-key" style="width:16px;height:16px;color:#fff"></i>
                    </div>
                    <h3 style="font-size:0.95rem;font-weight:600;color:var(--text-primary);margin:0">Certificado Digital (.pfx)</h3>
                </div>

                @if($tieneCertificado)
                <div style="padding:0.75rem;border-radius:8px;background:rgba(16,185,129,0.08);border:1px solid rgba(16,185,129,0.2);display:flex;align-items:center;gap:0.5rem;margin-bottom:0.75rem">
                    <i data-lucide="shield-check" style="width:20px;height:20px;color:#10b981"></i>
                    <div>
                        <div style="font-size:0.85rem;font-weight:600;color:#10b981">Certificado cargado</div>
                        <div style="font-size:0.75rem;color:var(--text-secondary)">{{ $certificadoNombre }}</div>
                    </div>
                </div>
                @else
                <div style="padding:0.75rem;border-radius:8px;background:rgba(245,158,11,0.08);border:1px solid rgba(245,158,11,0.2);display:flex;align-items:center;gap:0.5rem;margin-bottom:0.75rem">
                    <i data-lucide="alert-triangle" style="width:20px;height:20px;color:#f59e0b"></i>
                    <div>
                        <div style="font-size:0.85rem;font-weight:600;color:#f59e0b">Sin certificado</div>
                        <div style="font-size:0.75rem;color:var(--text-secondary)">Suba su archivo .pfx para producción</div>
                    </div>
                </div>
                @endif

                <div style="display:flex;align-items:center;gap:0.75rem">
                    <input type="file" wire:model="certificado" accept=".pfx,.p12" id="cert-upload"
                        style="flex:1;padding:0.5rem;border:1px dashed var(--border-color);border-radius:8px;background:var(--input-bg,var(--card-bg));color:var(--text-primary);font-size:0.8rem;cursor:pointer;box-sizing:border-box">
                    <button wire:click="subirCertificado" wire:loading.attr="disabled"
                        style="padding:0.5rem 1rem;border-radius:8px;border:1px solid #10b981;background:rgba(16,185,129,0.1);color:#10b981;font-weight:600;font-size:0.8rem;cursor:pointer;white-space:nowrap;display:flex;align-items:center;gap:0.375rem;transition:all .2s"
                        onmouseover="this.style.background='#10b981';this.style.color='#fff'" onmouseout="this.style.background='rgba(16,185,129,0.1)';this.style.color='#10b981'">
                        <i data-lucide="upload" style="width:14px;height:14px"></i>
                        Subir .pfx
                    </button>
                </div>
                <div wire:loading wire:target="certificado" style="margin-top:0.5rem;font-size:0.75rem;color:var(--text-secondary)">
                    <i data-lucide="loader-2" style="width:12px;height:12px;animation:spin 1s linear infinite"></i> Cargando archivo...
                </div>
            </div>

            {{-- Card: Probar Conexión --}}
            <div class="card" style="background:var(--card-bg);border:1px solid var(--border-color);border-radius:12px;padding:1.25rem">
                <div style="display:flex;align-items:center;gap:0.5rem;margin-bottom:1rem;padding-bottom:0.75rem;border-bottom:1px solid var(--border-color)">
                    <div style="width:32px;height:32px;border-radius:8px;background:linear-gradient(135deg,#ef4444,#f87171);display:flex;align-items:center;justify-content:center">
                        <i data-lucide="plug-zap" style="width:16px;height:16px;color:#fff"></i>
                    </div>
                    <h3 style="font-size:0.95rem;font-weight:600;color:var(--text-primary);margin:0">Probar Conexión con SUNAT</h3>
                </div>

                <p style="font-size:0.8rem;color:var(--text-secondary);margin:0 0 1rem">
                    Primero guarde la configuración, luego presione el botón para verificar que las credenciales y el certificado son correctos.
                </p>

                <button wire:click="probarConexionSunat" wire:loading.attr="disabled"
                    style="width:100%;padding:0.75rem;border-radius:8px;border:none;background:linear-gradient(135deg,#ef4444,#f87171);color:#fff;font-weight:700;font-size:0.9rem;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:0.5rem;box-shadow:0 2px 8px rgba(239,68,68,0.3);transition:all .2s"
                    onmouseover="this.style.boxShadow='0 4px 16px rgba(239,68,68,0.5)'" onmouseout="this.style.boxShadow='0 2px 8px rgba(239,68,68,0.3)'">
                    <span wire:loading.remove wire:target="probarConexionSunat">
                        <i data-lucide="zap" style="width:18px;height:18px"></i>
                    </span>
                    <span wire:loading wire:target="probarConexionSunat">
                        <i data-lucide="loader-2" style="width:18px;height:18px;animation:spin 1s linear infinite"></i>
                    </span>
                    <span wire:loading.remove wire:target="probarConexionSunat">Probar Conexión SUNAT</span>
                    <span wire:loading wire:target="probarConexionSunat">Conectando...</span>
                </button>

                {{-- Resultado de la conexión --}}
                @if($resultadoConexion)
                <div style="margin-top:1rem;padding:1rem;border-radius:10px;border:1px solid {{ $resultadoConexion['success'] ? 'rgba(16,185,129,0.3)' : 'rgba(239,68,68,0.3)' }};background:{{ $resultadoConexion['success'] ? 'rgba(16,185,129,0.06)' : 'rgba(239,68,68,0.06)' }};animation:slideDown .3s ease">

                    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:0.5rem">
                        <div style="display:flex;align-items:center;gap:0.5rem">
                            @if($resultadoConexion['success'])
                                <i data-lucide="check-circle-2" style="width:22px;height:22px;color:#10b981"></i>
                            @else
                                <i data-lucide="x-circle" style="width:22px;height:22px;color:#ef4444"></i>
                            @endif
                            <strong style="font-size:0.9rem;color:{{ $resultadoConexion['success'] ? '#10b981' : '#ef4444' }}">
                                {{ $resultadoConexion['titulo'] }}
                            </strong>
                        </div>
                        <button wire:click="cerrarResultado" style="background:none;border:none;cursor:pointer;color:var(--text-secondary);padding:0.25rem">
                            <i data-lucide="x" style="width:16px;height:16px"></i>
                        </button>
                    </div>

                    <p style="font-size:0.8rem;color:var(--text-secondary);margin:0 0 0.75rem">{{ $resultadoConexion['mensaje'] }}</p>

                    @if(isset($resultadoConexion['entorno']))
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:0.5rem;font-size:0.775rem">
                        <div style="padding:0.375rem 0.5rem;border-radius:6px;background:rgba(99,102,241,0.08)">
                            <span style="color:var(--text-muted)">Entorno:</span>
                            <strong style="color:var(--text-primary);margin-left:0.25rem">{{ $resultadoConexion['entorno'] }}</strong>
                        </div>
                        <div style="padding:0.375rem 0.5rem;border-radius:6px;background:rgba(99,102,241,0.08)">
                            <span style="color:var(--text-muted)">RUC:</span>
                            <strong style="color:var(--text-primary);margin-left:0.25rem;font-family:monospace">{{ $resultadoConexion['ruc'] ?? '-' }}</strong>
                        </div>
                        @if(isset($resultadoConexion['usuario_sol']))
                        <div style="padding:0.375rem 0.5rem;border-radius:6px;background:rgba(99,102,241,0.08)">
                            <span style="color:var(--text-muted)">Usuario SOL:</span>
                            <strong style="color:var(--text-primary);margin-left:0.25rem">{{ $resultadoConexion['usuario_sol'] }}</strong>
                        </div>
                        @endif
                        @if(isset($resultadoConexion['certificado']))
                        <div style="padding:0.375rem 0.5rem;border-radius:6px;background:rgba(99,102,241,0.08)">
                            <span style="color:var(--text-muted)">Certificado:</span>
                            <strong style="color:#10b981;margin-left:0.25rem">{{ $resultadoConexion['certificado'] }}</strong>
                        </div>
                        @endif
                        @if(isset($resultadoConexion['endpoint']))
                        <div style="padding:0.375rem 0.5rem;border-radius:6px;background:rgba(99,102,241,0.08);grid-column:span 2">
                            <span style="color:var(--text-muted)">Endpoint:</span>
                            <strong style="color:var(--text-primary);margin-left:0.25rem;font-size:0.725rem">{{ $resultadoConexion['endpoint'] }}</strong>
                        </div>
                        @endif
                    </div>
                    @endif
                </div>
                @endif
            </div>
        </div>
    </div>

    <style>
        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-8px); }
            to { opacity: 1; transform: translateY(0); }
        }
        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
    </style>
</div>
