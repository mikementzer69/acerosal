<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>ERP Acerosal</title>

    <!-- FontAwesome -->
    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <!-- Estilos del proyecto original -->
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

<body>

    {{-- HEADER --}}
    @include('partials.header')

    {{-- SIDEBAR --}}
    @include('partials.sidebar')

    {{-- BOTÓN DE COLAPSAR --}}
    <button class="sidebar-toggle" onclick="toggleSidebar()">
        <i class="fa-solid fa-bars"></i>
    </button>

    {{-- CONTENIDO PRINCIPAL (IMPORTANTE: class="content") --}}
    <main class="content">
        @yield('content')
    </main>

    {{-- FOOTER --}}
    @include('partials.footer')

</body>


</html>
