<header class="app-header">
    <div class="header-grid container">

        {{-- Columna 1: Logo --}}
        <a class="header-logo" href="{{ url('/dashboard') }}" aria-label="Inicio">
            <img src="{{ asset('images/logo.png') }}" alt="Logo">
        </a>

        {{-- Columna 2: Título + Empresa --}}
        <div class="header-center">
            <h1 class="header-title">ERP GRUPO ACEROSAL</h1>

            <div class="header-subtitle">
                Empresa:
                <strong>{{ session('nombreEmpresa') }}</strong>
            </div>
        </div>

        {{-- Columna 3: Usuario --}}
        <div class="header-user" tabindex="0" aria-haspopup="true" aria-expanded="false">

            @php
                $usuario = session('nombreUsuario', 'Usuario');
                $partes = explode(' ', $usuario);
                $iniciales = strtoupper(
                    (mb_substr($partes[0] ?? '', 0, 1)) .
                    (mb_substr($partes[1] ?? '', 0, 1))
                );
            @endphp

            <div class="user-chip">
                <span class="user-avatar" aria-hidden="true">
                    {{ $iniciales ?: 'U' }}
                </span>

                <span class="user-name">{{ $usuario }}</span>

                <svg class="user-caret" width="16" height="16" viewBox="0 0 24 24" aria-hidden="true">
                    <path d="M6 9l6 6 6-6" fill="currentColor"/>
                </svg>
            </div>

            <ul class="user-menu" role="menu">
                <li role="menuitem">
                    <a href="#">Ver perfil</a>
                </li>
                <li role="menuitem">
                    <a href="{{ url('/logout') }}">Logout</a>
                </li>
            </ul>

        </div>
    </div>
</header>
