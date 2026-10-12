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

/* --- TIPOGRAFIA GLOBAL ARIAL --- */
body, h1, h2, h3, h4, h5, h6, p, span, a, div, td, th, label, button, input, select, textarea, li, ul, ol {
    font-family: Arial, Helvetica, sans-serif !important;
}
/* Protegemos los íconos de FontAwesome para que no desaparezcan */
.fa, .fas, .far, .fal, .fab, .fa-solid, .fa-regular, .fa-brands, i {
    font-family: "Font Awesome 6 Free", "Font Awesome 6 Brands" !important;
}

/* --- ETIQUETAS DE FILTROS Y BUSCADORES EN TODOS LOS INDEX --- */
.erp-section label,
.erp-search-form label,
.search-row label,
.search-row > div > label,
.search-row div label,
.filter-box label,
.filter-box strong,
.filter-box td strong,
.filter-item label,
.erp-card label,
.form-label {
    color: #0f172a !important; /* Azul pizarra oscuro / casi negro para máxima legibilidad */
    font-weight: 700 !important;
    font-size: 13px !important;
    display: block !important;
    margin-bottom: 6px !important;
    letter-spacing: 0.2px;
}

/* Alineación limpia para grupos de búsqueda */
.search-row {
    display: flex !important;
    align-items: flex-end !important;
    gap: 12px !important;
    flex-wrap: wrap !important;
}

.search-row > div {
    display: flex !important;
    flex-direction: column !important;
}

/* Títulos y Subtítulos en formularios y tarjetas oscuras */
.form-container h1,
.form-container h2,
.form-container h3,
.form-container .form-title,
.ajuste-wrapper h1,
.ajuste-wrapper h2,
.ajuste-wrapper h3,
.modal-content h1,
.modal-content h2,
.modal-content h3,
.card[style*="background: #1e293b"] h2,
.card[style*="background: #1e293b"] h3 {
    color: #f8fafc !important; /* Blanco brillante */
    font-weight: 700 !important;
}

.form-container h4,
.form-container h5,
.form-container legend,
.ajuste-wrapper h4,
.ajuste-wrapper h5,
.modal-content h4,
.modal-content h5,
.card[style*="background: #1e293b"] h4,
.card[style*="background: #1e293b"] h5 {
    color: #38bdf8 !important; /* Cyan claro moderno, súper legible */
    font-weight: 700 !important;
    letter-spacing: 0.5px !important;
    margin-top: 15px !important;
    margin-bottom: 12px !important;
}

/* Mantener legibles las etiquetas en formularios de fondo oscuro (creación/edición/inventario) */
.form-container label,
.form-container .form-group label,
.ajuste-wrapper label,
.modal-content label,
.card-detalle-pieza label,
.mayorizacion-card label,
.mayorizacion-card .form-label,
.card[style*="background: #1e293b"] label,
.card[style*="background:#1e293b"] label,
.inventory-dashboard label,
.grid-lote label,
#contenedorLotes label {
    color: #f1f5f9 !important; /* Blanco hueso / Slate claro para máxima legibilidad */
    font-weight: 600 !important;
}

/* Opciones de select y placeholders en fondo oscuro */
.form-container select option,
.card[style*="background: #1e293b"] select option {
    background-color: #1e293b !important;
    color: #ffffff !important;
}

.form-container input::placeholder,
.form-container textarea::placeholder {
    color: #94a3b8 !important; /* Gris claro legible */
    opacity: 1 !important;
}

.form-container hr {
    border: 0 !important;
    border-top: 1px solid #334155 !important;
    margin: 20px 0 !important;
}

/* Paginación estilo Dark */
.pagination {
    display: flex;
    flex-wrap: wrap;
    list-style: none;
    padding: 0;
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
    text-decoration: none !important;
    display: block;
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
