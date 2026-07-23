<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="{{ asset('CSS/main-layout-10-09.css') }}">
    <title>Login - ERP Acerosal</title>
</head>
<body class="auth-bg">

<main class="auth-wrapper">

    <section class="auth-card">

        <div class="auth-header">
            <img class="auth-logo" src="{{ asset('images/logo.png') }}" alt="Acerosal">
            <h2 class="auth-title">Iniciar Sesión</h2>
            <p class="auth-subtitle">ERP Grupo Acerosal</p>
        </div>

        @if(session('mensaje'))
            <div class="auth-alert">
                {{ session('mensaje') }}
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}">

            @csrf

            <div class="form-field">
                <label for="usuario">Usuario</label>
                <input id="usuario" type="text" name="usuario" required>
            </div>

            <div class="form-field">
                <label for="password">Contraseña</label>
                <input id="password" type="password" name="password" required>
            </div>

            <div class="form-field">
                <label for="empresa">Empresa</label>
                <select id="empresa" name="empresa" required>
                    <option value="">-- Seleccione empresa --</option>
                    @foreach($empresas as $emp)
                        <option value="{{ $emp->id_empresa }}">
                            {{ $emp->nombre }}
                        </option>
                    @endforeach
                </select>
            </div>

            <button type="submit" class="btn btn-primary w-full">Ingresar</button>
        </form>

        <footer class="auth-footer">
            <small>&copy; {{ date('Y') }} Grupo Acerosal</small>
        </footer>

    </section>

</main>

</body>
</html>

