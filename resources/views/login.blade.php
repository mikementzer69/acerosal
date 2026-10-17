<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión &bull; ERP Grupo Acerosal</title>
    
    {{-- Tipografía Moderna Plus Jakarta Sans --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    {{-- FontAwesome 6 --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <style>
        :root {
            --primary: #2563eb;
            --primary-hover: #1d4ed8;
            --accent-amber: #f59e0b;
            --accent-amber-hover: #d97706;
            --bg-dark: #070b14;
            --card-bg: rgba(15, 23, 42, 0.82);
            --card-border: rgba(255, 255, 255, 0.09);
            --input-bg: #090e1a;
            --input-border: #334155;
            --text-main: #f8fafc;
            --text-muted: #94a3b8;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif;
        }

        body {
            background-color: var(--bg-dark);
            color: var(--text-main);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow-x: hidden;
            padding: 24px 16px;
        }

        /* FONDO DINÁMICO CON RESPLANDORES DE LUZ */
        .ambient-glow-1 {
            position: fixed;
            top: -120px;
            right: -80px;
            width: 500px;
            height: 500px;
            background: radial-gradient(circle, rgba(37, 99, 235, 0.22) 0%, rgba(37, 99, 235, 0) 70%);
            border-radius: 50%;
            pointer-events: none;
            z-index: 0;
            animation: pulseGlow 10s ease-in-out infinite alternate;
        }

        .ambient-glow-2 {
            position: fixed;
            bottom: -140px;
            left: -100px;
            width: 550px;
            height: 550px;
            background: radial-gradient(circle, rgba(245, 158, 11, 0.16) 0%, rgba(245, 158, 11, 0) 70%);
            border-radius: 50%;
            pointer-events: none;
            z-index: 0;
            animation: pulseGlow 8s ease-in-out infinite alternate-reverse;
        }

        /* TRAMA DE CUADRÍCULA INDUSTRIAL SUTIL */
        .grid-pattern {
            position: fixed;
            inset: 0;
            background-image: 
                linear-gradient(to right, rgba(255, 255, 255, 0.02) 1px, transparent 1px),
                linear-gradient(to bottom, rgba(255, 255, 255, 0.02) 1px, transparent 1px);
            background-size: 36px 36px;
            pointer-events: none;
            z-index: 0;
        }

        @keyframes pulseGlow {
            0% { transform: scale(1) translateY(0); opacity: 0.8; }
            100% { transform: scale(1.15) translateY(20px); opacity: 1; }
        }

        /* TARJETA GLASSMORPHIC */
        .login-card {
            position: relative;
            z-index: 10;
            width: 100%;
            max-width: 440px;
            background: var(--card-bg);
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
            border: 1px solid var(--card-border);
            border-radius: 24px;
            padding: 38px 34px;
            box-shadow: 
                0 25px 60px -15px rgba(0, 0, 0, 0.75),
                0 0 45px rgba(37, 99, 235, 0.1),
                inset 0 1px 0 rgba(255, 255, 255, 0.1);
            animation: cardEntrance 0.7s cubic-bezier(0.16, 1, 0.3, 1);
        }

        @keyframes cardEntrance {
            from {
                opacity: 0;
                transform: translateY(24px) scale(0.97);
            }
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        /* ENCABEZADO Y LOGO */
        .login-header {
            text-align: center;
            margin-bottom: 26px;
        }

        .logo-capsule {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: #ffffff;
            padding: 9px 24px;
            border-radius: 14px;
            box-shadow: 0 4px 18px rgba(0, 0, 0, 0.4), inset 0 1px 0 rgba(255, 255, 255, 0.8);
            margin-bottom: 18px;
            transition: transform 0.3s ease;
        }

        .logo-capsule:hover {
            transform: scale(1.03);
        }

        .logo-img {
            height: 48px;
            width: auto;
            object-fit: contain;
            display: block;
        }

        .system-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: rgba(37, 99, 235, 0.12);
            color: #60a5fa;
            border: 1px solid rgba(37, 99, 235, 0.3);
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            margin-bottom: 10px;
        }

        .login-title {
            font-size: 24px;
            font-weight: 800;
            color: #ffffff;
            letter-spacing: -0.3px;
            margin-bottom: 4px;
        }

        .login-subtitle {
            font-size: 13px;
            color: var(--text-muted);
            font-weight: 500;
        }

        /* ALERTA DE MENSAJES */
        .login-alert {
            display: flex;
            align-items: center;
            gap: 10px;
            background: rgba(239, 68, 68, 0.12);
            border: 1px solid rgba(239, 68, 68, 0.35);
            color: #fca5a5;
            padding: 12px 14px;
            border-radius: 12px;
            font-size: 12.5px;
            font-weight: 600;
            margin-bottom: 20px;
            animation: shakeAlert 0.4s ease;
        }

        @keyframes shakeAlert {
            0%, 100% { transform: translateX(0); }
            20%, 60% { transform: translateX(-4px); }
            40%, 80% { transform: translateX(4px); }
        }

        /* FORMULARIO Y CAMPOS */
        .form-group {
            margin-bottom: 18px;
        }

        .form-label {
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 12.5px;
            font-weight: 600;
            color: #e2e8f0;
            margin-bottom: 7px;
        }

        .input-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }

        .input-icon {
            position: absolute;
            left: 14px;
            color: #64748b;
            font-size: 14px;
            pointer-events: none;
            transition: color 0.2s ease;
        }

        .form-input {
            width: 100%;
            height: 46px;
            background-color: var(--input-bg);
            border: 1px solid var(--input-border);
            border-radius: 12px;
            padding: 0 14px 0 42px;
            color: #ffffff;
            font-size: 13.5px;
            font-weight: 500;
            transition: all 0.25s ease;
            outline: none;
        }

        .form-input::placeholder {
            color: #475569;
            font-weight: 400;
        }

        .form-input:focus {
            border-color: #3b82f6;
            background-color: #0d1527;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.22);
        }

        .input-wrapper:focus-within .input-icon {
            color: #3b82f6;
        }

        /* SELECT ESTILIZADO */
        select.form-input {
            cursor: pointer;
            appearance: none;
            -webkit-appearance: none;
            padding-right: 38px;
        }

        .select-arrow {
            position: absolute;
            right: 14px;
            color: #64748b;
            pointer-events: none;
            font-size: 12px;
            transition: transform 0.2s ease;
        }

        .input-wrapper:focus-within .select-arrow {
            color: #3b82f6;
            transform: rotate(180deg);
        }

        select.form-input option {
            background-color: #0f172a;
            color: #ffffff;
            padding: 10px;
        }

        /* BOTÓN MOSTRAR/OCULTAR CONTRASEÑA */
        .btn-toggle-password {
            position: absolute;
            right: 12px;
            background: none;
            border: none;
            color: #64748b;
            cursor: pointer;
            padding: 6px;
            font-size: 13px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 6px;
            transition: color 0.2s ease;
        }

        .btn-toggle-password:hover {
            color: #cbd5e1;
        }

        /* BOTÓN DE ACCESO */
        .btn-submit {
            width: 100%;
            height: 48px;
            background: linear-gradient(135deg, #f59e0b 0%, #ea580c 100%);
            border: none;
            border-radius: 12px;
            color: #0f172a;
            font-size: 14px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.6px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            margin-top: 24px;
            box-shadow: 0 10px 25px -5px rgba(245, 158, 11, 0.4), inset 0 1px 0 rgba(255, 255, 255, 0.3);
            transition: all 0.25s ease;
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 14px 30px -4px rgba(245, 158, 11, 0.55);
            background: linear-gradient(135deg, #fbbf24 0%, #ea580c 100%);
        }

        .btn-submit:active {
            transform: translateY(0);
            box-shadow: 0 6px 15px -3px rgba(245, 158, 11, 0.4);
        }

        /* PIE DE PÁGINA */
        .login-footer {
            margin-top: 24px;
            padding-top: 18px;
            border-top: 1px solid rgba(255, 255, 255, 0.06);
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 8px;
            text-align: center;
        }

        .security-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 11px;
            color: #64748b;
            font-weight: 500;
        }

        .security-badge i {
            color: #10b981;
        }

        .copyright-text {
            font-size: 11px;
            color: #475569;
        }
    </style>
</head>
<body>

    {{-- LUCES AMBIENTALES Y TRAMA DE FONDO --}}
    <div class="ambient-glow-1"></div>
    <div class="ambient-glow-2"></div>
    <div class="grid-pattern"></div>

    {{-- TARJETA PRINCIPAL DE INICIO DE SESIÓN --}}
    <main class="login-card">

        {{-- CABECERA CON LOGO Y TÍTULO --}}
        <div class="login-header">
            <div class="logo-capsule">
                <img class="logo-img" src="{{ asset('images/logo.png') }}" alt="Grupo Acerosal">
            </div>
            <div>
                <span class="system-pill">
                    <i class="fa-solid fa-shield-halved"></i> Acceso Seguro ERP
                </span>
            </div>
            <h1 class="login-title">Iniciar Sesión</h1>
            <p class="login-subtitle">Ingresa tus credenciales para acceder a la plataforma</p>
        </div>

        {{-- ALERTA DE ERROR SI EXISTE --}}
        @if(session('mensaje'))
            <div class="login-alert">
                <i class="fa-solid fa-triangle-exclamation"></i>
                <span>{{ session('mensaje') }}</span>
            </div>
        @endif

        {{-- FORMULARIO DE ACCESO (Mantiene nombres y acción intactos) --}}
        <form method="POST" action="{{ route('login') }}" autocomplete="off">
            @csrf

            {{-- 1. CAMPO USUARIO --}}
            <div class="form-group">
                <label for="usuario" class="form-label">
                    <span>Usuario</span>
                </label>
                <div class="input-wrapper">
                    <i class="fa-solid fa-user input-icon"></i>
                    <input id="usuario" 
                           type="text" 
                           name="usuario" 
                           class="form-input" 
                           placeholder="Ingresa tu usuario" 
                           required 
                           autofocus 
                           autocomplete="username">
                </div>
            </div>

            {{-- 2. CAMPO CONTRASEÑA --}}
            <div class="form-group">
                <label for="password" class="form-label">
                    <span>Contraseña</span>
                </label>
                <div class="input-wrapper">
                    <i class="fa-solid fa-lock input-icon"></i>
                    <input id="password" 
                           type="password" 
                           name="password" 
                           class="form-input" 
                           placeholder="Ingresa tu contraseña" 
                           required 
                           autocomplete="current-password">
                    <button type="button" 
                            id="togglePassword" 
                            class="btn-toggle-password" 
                            tabindex="-1" 
                            title="Mostrar u ocultar contraseña">
                        <i class="fa-solid fa-eye" id="eyeIcon"></i>
                    </button>
                </div>
            </div>

            {{-- 3. CAMPO EMPRESA --}}
            <div class="form-group">
                <label for="empresa" class="form-label">
                    <span>Empresa</span>
                </label>
                <div class="input-wrapper">
                    <i class="fa-solid fa-building input-icon"></i>
                    <select id="empresa" name="empresa" class="form-input" required>
                        <option value="">-- Seleccione empresa --</option>
                        @foreach($empresas as $emp)
                            <option value="{{ $emp->id_empresa }}">
                                {{ $emp->nombre }}
                            </option>
                        @endforeach
                    </select>
                    <i class="fa-solid fa-chevron-down select-arrow"></i>
                </div>
            </div>

            {{-- BOTÓN DE INGRESO --}}
            <button type="submit" class="btn-submit">
                <span>Ingresar</span>
                <i class="fa-solid fa-arrow-right-to-bracket"></i>
            </button>
        </form>

        {{-- PIE DE TARJETA CON SEGURIDAD Y DERECHOS --}}
        <footer class="login-footer">
            <div class="security-badge">
                <i class="fa-solid fa-lock"></i>
                <span>Conexión cifrada de alta seguridad</span>
            </div>
            <div class="copyright-text">
                &copy; {{ date('Y') }} Grupo Acerosal S.A. de C.V.
            </div>
        </footer>

    </main>

    {{-- SCRIPT INTERACTIVO PARA MOSTRAR/OCULTAR CONTRASEÑA --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const toggleBtn = document.getElementById('togglePassword');
            const passwordInput = document.getElementById('password');
            const eyeIcon = document.getElementById('eyeIcon');

            if (toggleBtn && passwordInput && eyeIcon) {
                toggleBtn.addEventListener('click', function() {
                    const isPassword = passwordInput.getAttribute('type') === 'password';
                    passwordInput.setAttribute('type', isPassword ? 'text' : 'password');
                    eyeIcon.className = isPassword ? 'fa-solid fa-eye-slash' : 'fa-solid fa-eye';
                });
            }
        });
    </script>

</body>
</html>
