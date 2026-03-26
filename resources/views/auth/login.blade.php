<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión — ERP AgroMine</title>
    <meta name="description" content="Accede al panel de control del ERP AgroMine — Sistema de Gestión Empresarial">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">
    <style>
        *,
        *::before,
        *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        :root {
            --bg: #0a0f1e;
            --bg-card: #111827;
            --bg-input: #1a2235;
            --border: #1e2d45;
            --border-focus: #22c55e;
            --text: #f1f5f9;
            --text-muted: #64748b;
            --text-secondary: #94a3b8;
            --primary: #22c55e;
            --primary-dark: #16a34a;
            --primary-glow: rgba(34, 197, 94, 0.2);
            --error: #ef4444;
            --error-bg: rgba(239, 68, 68, 0.08);
        }

        html,
        body {
            height: 100%;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--bg);
            color: var(--text);
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            overflow: hidden;
        }

        /* Fondo animado con gradiente */
        .bg-mesh {
            position: fixed;
            inset: 0;
            background:
                radial-gradient(ellipse 80% 60% at 20% 20%, rgba(34, 197, 94, 0.08) 0%, transparent 60%),
                radial-gradient(ellipse 60% 80% at 80% 80%, rgba(14, 165, 233, 0.06) 0%, transparent 60%),
                radial-gradient(ellipse 40% 40% at 50% 50%, rgba(34, 197, 94, 0.04) 0%, transparent 70%);
            pointer-events: none;
        }

        /* Orbe animado */
        .orb {
            position: fixed;
            border-radius: 50%;
            filter: blur(80px);
            opacity: 0.4;
            animation: float 8s ease-in-out infinite;
            pointer-events: none;
        }

        .orb-1 {
            width: 400px;
            height: 400px;
            background: radial-gradient(circle, rgba(34, 197, 94, 0.3), transparent);
            top: -100px;
            left: -100px;
            animation-delay: 0s;
        }

        .orb-2 {
            width: 300px;
            height: 300px;
            background: radial-gradient(circle, rgba(14, 165, 233, 0.2), transparent);
            bottom: -80px;
            right: -80px;
            animation-delay: -4s;
        }

        @keyframes float {

            0%,
            100% {
                transform: translate(0, 0) scale(1);
            }

            33% {
                transform: translate(30px, -20px) scale(1.05);
            }

            66% {
                transform: translate(-20px, 15px) scale(0.97);
            }
        }

        /* Card principal */
        .login-wrapper {
            position: relative;
            z-index: 1;
            width: 100%;
            max-width: 420px;
            padding: 24px;
            animation: slideUp 0.5s cubic-bezier(0.16, 1, 0.3, 1) both;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(24px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Logo / Brand */
        .brand {
            text-align: center;
            margin-bottom: 40px;
        }

        .brand-icon {
            width: 64px;
            height: 64px;
            background: linear-gradient(135deg, #22c55e, #16a34a);
            border-radius: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 16px;
            box-shadow: 0 0 40px rgba(34, 197, 94, 0.35);
            font-size: 28px;
        }

        .brand-name {
            font-size: 1.6rem;
            font-weight: 800;
            background: linear-gradient(135deg, #22c55e, #86efac);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            letter-spacing: -0.5px;
        }

        .brand-sub {
            font-size: 0.8rem;
            color: var(--text-muted);
            margin-top: 4px;
            letter-spacing: 0.05em;
            text-transform: uppercase;
        }

        /* Card */
        .card {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: 20px;
            padding: 36px 32px;
            box-shadow: 0 32px 64px rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(12px);
        }

        .card-title {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--text);
            margin-bottom: 6px;
        }

        .card-subtitle {
            font-size: 0.85rem;
            color: var(--text-muted);
            margin-bottom: 28px;
        }

        /* Error global */
        .alert-error {
            background: var(--error-bg);
            border: 1px solid rgba(239, 68, 68, 0.3);
            border-radius: 10px;
            padding: 12px 16px;
            color: #fca5a5;
            font-size: 0.85rem;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 8px;
            animation: shake 0.4s ease;
        }

        @keyframes shake {

            0%,
            100% {
                transform: translateX(0);
            }

            20% {
                transform: translateX(-6px);
            }

            40% {
                transform: translateX(6px);
            }

            60% {
                transform: translateX(-4px);
            }

            80% {
                transform: translateX(4px);
            }
        }

        /* Form */
        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            font-size: 0.82rem;
            font-weight: 500;
            color: var(--text-secondary);
            margin-bottom: 8px;
            letter-spacing: 0.02em;
        }

        .input-wrapper {
            position: relative;
        }

        .input-icon {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted);
            width: 18px;
            height: 18px;
            pointer-events: none;
        }

        input[type="email"],
        input[type="password"],
        input[type="text"] {
            width: 100%;
            background: var(--bg-input);
            border: 1.5px solid var(--border);
            border-radius: 10px;
            padding: 13px 14px 13px 44px;
            color: var(--text);
            font-size: 0.92rem;
            font-family: inherit;
            transition: border-color 0.2s, box-shadow 0.2s, background 0.2s;
            outline: none;
        }

        input:focus {
            border-color: var(--border-focus);
            background: #1e2d45;
            box-shadow: 0 0 0 3px var(--primary-glow);
        }

        input.has-error {
            border-color: var(--error);
            box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.12);
        }

        .field-error {
            color: #fca5a5;
            font-size: 0.75rem;
            margin-top: 5px;
        }

        /* Toggle password */
        .toggle-password {
            position: absolute;
            right: 14px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            color: var(--text-muted);
            padding: 2px;
            display: flex;
            align-items: center;
            transition: color 0.2s;
        }

        .toggle-password:hover {
            color: var(--text-secondary);
        }

        /* Remember + forgot row */
        .row-extra {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 24px;
        }

        .remember-label {
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
            font-size: 0.82rem;
            color: var(--text-secondary);
            margin-bottom: 0;
        }

        .remember-label input[type="checkbox"] {
            width: 16px;
            height: 16px;
            padding: 0;
            accent-color: var(--primary);
            border-radius: 4px;
            cursor: pointer;
        }

        /* Botón submit */
        .btn-login {
            width: 100%;
            background: linear-gradient(135deg, #22c55e, #16a34a);
            color: #fff;
            border: none;
            border-radius: 10px;
            padding: 14px;
            font-size: 0.95rem;
            font-weight: 600;
            font-family: inherit;
            cursor: pointer;
            transition: transform 0.15s, box-shadow 0.15s, opacity 0.15s;
            box-shadow: 0 4px 20px rgba(34, 197, 94, 0.3);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            letter-spacing: 0.02em;
        }

        .btn-login:hover {
            transform: translateY(-1px);
            box-shadow: 0 8px 28px rgba(34, 197, 94, 0.4);
        }

        .btn-login:active {
            transform: translateY(0);
        }

        .btn-login:disabled {
            opacity: 0.7;
            cursor: not-allowed;
            transform: none;
        }

        /* Spinner */
        .spinner {
            display: none;
            width: 18px;
            height: 18px;
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-top-color: #fff;
            border-radius: 50%;
            animation: spin 0.7s linear infinite;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        /* Footer de la card */
        .card-footer {
            margin-top: 24px;
            text-align: center;
            font-size: 0.78rem;
            color: var(--text-muted);
        }

        .divider {
            display: flex;
            align-items: center;
            gap: 12px;
            color: var(--text-muted);
            font-size: 0.75rem;
            margin: 20px 0;
        }

        .divider::before,
        .divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: var(--border);
        }
    </style>
</head>

<body>
    <div class="bg-mesh"></div>
    <div class="orb orb-1"></div>
    <div class="orb orb-2"></div>

    <div class="login-wrapper">

        <!-- Brand -->
        <div class="brand">
            <div class="brand-icon">🌿</div>
            <div class="brand-name">ERP AgroMine</div>
            <div class="brand-sub">Sistema de Gestión Empresarial</div>
        </div>

        <!-- Card Login -->
        <div class="card">
            <div class="card-title">Bienvenido de vuelta</div>
            <div class="card-subtitle">Ingresa tus credenciales para continuar</div>

            {{-- Error de autenticación --}}
            @if ($errors->any())
                <div class="alert-error" role="alert">
                    <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10" />
                        <line x1="12" y1="8" x2="12" y2="12" />
                        <line x1="12" y1="16" x2="12.01" y2="16" />
                    </svg>
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('login.post') }}" id="loginForm" novalidate>
                @csrf

                {{-- Email --}}
                <div class="form-group">
                    <label for="email">Correo electrónico</label>
                    <div class="input-wrapper">
                        <svg class="input-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z" />
                            <polyline points="22,6 12,13 2,6" />
                        </svg>
                        <input type="email" id="email" name="email" value="{{ old('email') }}"
                            placeholder="usuario@empresa.com" autocomplete="email" autofocus
                            class="{{ $errors->has('email') ? 'has-error' : '' }}" required>
                    </div>
                    @error('email')
                        <div class="field-error">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Contraseña --}}
                <div class="form-group">
                    <label for="password">Contraseña</label>
                    <div class="input-wrapper">
                        <svg class="input-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <rect x="3" y="11" width="18" height="11" rx="2" ry="2" />
                            <path d="M7 11V7a5 5 0 0 1 10 0v4" />
                        </svg>
                        <input type="password" id="password" name="password" placeholder="••••••••"
                            autocomplete="current-password" class="{{ $errors->has('password') ? 'has-error' : '' }}"
                            required>
                        <button type="button" class="toggle-password" onclick="togglePassword()" id="toggleBtn"
                            title="Mostrar/ocultar contraseña">
                            <svg id="eyeIcon" width="18" height="18" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor" stroke-width="2">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" />
                                <circle cx="12" cy="12" r="3" />
                            </svg>
                        </button>
                    </div>
                    @error('password')
                        <div class="field-error">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Remember me --}}
                <div class="row-extra">
                    <label class="remember-label" for="remember">
                        <input type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                        Recordarme
                    </label>
                </div>

                {{-- Submit --}}
                <button type="submit" class="btn-login" id="submitBtn">
                    <div class="spinner" id="spinner"></div>
                    <span id="btnText">Iniciar Sesión</span>
                    <svg id="btnIcon" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                        stroke-width="2">
                        <path d="M5 12h14M12 5l7 7-7 7" />
                    </svg>
                </button>
            </form>

            <div class="divider">acceso seguro</div>

            <div class="card-footer">
                ERP AgroMine &copy; {{ date('Y') }} — Todos los derechos reservados
            </div>
        </div>
    </div>

    <script>
        // Toggle mostrar/ocultar contraseña
        function togglePassword() {
            const input = document.getElementById('password');
            const icon = document.getElementById('eyeIcon');
            const isHidden = input.type === 'password';

            input.type = isHidden ? 'text' : 'password';
            icon.innerHTML = isHidden
                ? '<path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/>'
                : '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>';
        }

        // Loading state al submit
        document.getElementById('loginForm').addEventListener('submit', function () {
            const btn = document.getElementById('submitBtn');
            const spinner = document.getElementById('spinner');
            const text = document.getElementById('btnText');
            const icon = document.getElementById('btnIcon');

            btn.disabled = true;
            spinner.style.display = 'block';
            text.textContent = 'Verificando...';
            icon.style.display = 'none';
        });
    </script>
</body>

</html>