<!DOCTYPE html>
<html lang="es">
<head>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>.
    @vite(['resources/js/app.js'])

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <meta charset="UTF-8">
    <title>ERP Acerosal</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <!-- TODOS LOS CSS ORIGINALES -->
    <link rel="stylesheet" href="{{ asset('CSS/main-grid.css') }}">
    <link rel="stylesheet" href="{{ asset('CSS/main-grid2.css') }}">
    <link rel="stylesheet" href="{{ asset('CSS/main-layout-10-06.css') }}">
    <link rel="stylesheet" href="{{ asset('CSS/main-layout-10-09.css') }}">
    <link rel="stylesheet" href="{{ asset('CSS/erp-styles.css') }}">
    <link rel="stylesheet" href="{{ asset('CSS/dashboard.css') }}">
    <link rel="stylesheet" href="{{ asset('CSS/sidebar.css') }}">
    <link rel="stylesheet" href="{{ asset('CSS/header.css') }}">
    <link rel="stylesheet" href="{{ asset('CSS/menu.css') }}">
    <link rel="stylesheet" href="{{ asset('CSS/grid.css') }}">
    <link rel="stylesheet" href="{{ asset('CSS/test.css') }}">
    <link rel="stylesheet" href="{{ asset('CSS/test2.css') }}">
    <link rel="stylesheet" href="{{ asset('CSS/form-compras6.css') }}">
    <link rel="stylesheet" href="{{ asset('CSS/inventario.css') }}">


</head>

<style>

/* Paginación estilo Dark */
.pagination {
    margin-top: 20px;
    justify-content: center; /* Centramos los números */
    gap: 5px;
}

.page-link {
    background-color: #1f2a3a !important; /* El color de tu contenedor */
    border: 1px solid #4a5568 !important;   /* Borde gris azulado */
    color: #ffffff !important;              /* Texto blanco */
    padding: 8px 16px;
    border-radius: 6px !important;
    transition: all 0.2s;
}

.page-item.active .page-link {
    background-color: #3b82f6 !important; /* El azul brillante que usás */
    border-color: #3b82f6 !important;
    font-weight: bold;
}

.page-item.disabled .page-link {
    background-color: #16202c !important;
    color: #6c757d !important;
    border-color: #2d3a4f !important;
}

.page-link:hover {
    background-color: #2d3a4f !important;
    color: #4ade80 !important; /* El verde de tus inputs al pasar el mouse */
}

/* El texto de "Mostrando resultados..." */
.pagination-info, .text-muted {
    color: #e8edf5 !important;
    font-size: 0.9em;
    text-align: center;
    margin-top: 10px;
}

/* El contenedor principal deja un margen del 10% a cada lado */
.content {
    width: 85% !important; /* Un poco más de espacio que 80% */
    max-width: 100% !important;
    margin: 0 auto; /* Centramos el contenido general */
}

/* El formulario aprovecha el espacio que le da el content */
.form-container {
    /* En lugar de 40%, usa casi todo el espacio disponible del padre */
    width: 100% !important;

    /* Pero le ponemos un techo para monitores gigantes */
    max-width: 1100px !important;

    /* Aseguramos que nunca sea tan pequeño que rompa las columnas */
    min-width: 700px !important;

    margin: 30px auto !important;
}

/* Esto habilita el scroll en la parte principal si el formulario es muy alto */
.content-wrapper, .main-content, body {
    overflow-y: auto !important;
    height: 100vh; /* Ocupa toda la altura vertical */
}

</style>


<body>

    {{-- HEADER --}}
    @include('partials.header')

    {{-- CONTENEDOR PRINCIPAL (AQUÍ ESTÁ LA SOLUCIÓN) --}}
    <div class="erp-container" style="display:flex; width:100%;">

        {{-- SIDEBAR --}}
        @include('partials.sidebar')

        {{-- CONTENIDO --}}
        <main class="content">
            @yield('content')
        </main>

    </div>

    {{-- BOTÓN DE COLAPSAR --}}
    <button class="sidebar-toggle" onclick="toggleSidebar()">
        <i class="fa-solid fa-bars"></i>
    </button>

    {{-- FOOTER --}}
    @include('partials.footer')

</body>
</html>
