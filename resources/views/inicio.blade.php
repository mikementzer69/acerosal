@extends('layouts.app')

@section('content')

<div class="dashboard">

    <!-- TÍTULO PRINCIPAL -->
    <h1 class="dash-title">📊 Panel de Control</h1>
    <p class="dash-subtitle">Bienvenido al ERP Acerosal. Usa los accesos rápidos para trabajar más rápido.</p>

    <!-- TARJETAS PRINCIPALES -->
    <div class="dash-cards">

        <a href="{{ route('compras.nueva') }}" class="dash-card">
            <div class="dash-icon bg-blue"><i class="fa-solid fa-cart-shopping"></i></div>
            <div class="dash-info">
                <h3>Nueva Compra</h3>
                <p>Registrar una compra</p>
            </div>
        </a>

        <a href="{{ route('productos.lista') }}" class="dash-card">
            <div class="dash-icon bg-green"><i class="fa-solid fa-box"></i></div>
            <div class="dash-info">
                <h3>Productos</h3>
                <p>Ver inventario</p>
            </div>
        </a>

        <a href="{{ route('proveedores.lista') }}" class="dash-card">
            <div class="dash-icon bg-purple"><i class="fa-solid fa-truck"></i></div>
            <div class="dash-info">
                <h3>Proveedores</h3>
                <p>Ver lista</p>
            </div>
        </a>

        <a href="{{ route('usuarios.nuevo') }}" class="dash-card">
            <div class="dash-icon bg-yellow"><i class="fa-solid fa-user-plus"></i></div>
            <div class="dash-info">
                <h3>Nuevo Usuario</h3>
                <p>Crear cuenta</p>
            </div>
        </a>

    </div>

    <!-- SECCIÓN DE ACCESOS RÁPIDOS -->
    <h2 class="dash-subsection-title">⚡ Accesos rápidos</h2>
    <div class="dash-quick-access">

        <a href="{{ route('inventario.form') }}" class="quick-btn">
            <i class="fa-solid fa-warehouse"></i> Inventario
        </a>

        <a href="{{ route('usuarios.lista') }}" class="quick-btn">
            <i class="fa-solid fa-users"></i> Usuarios
        </a>

        <a href="{{ route('productos.nuevo') }}" class="quick-btn">
            <i class="fa-solid fa-plus"></i> Nuevo Producto
        </a>

        <a href="{{ route('roles.lista') }}" class="quick-btn">
            <i class="fa-solid fa-id-card"></i> Roles
        </a>

    </div>

    <!-- ACTIVIDAD RECIENTE -->
    <h2 class="dash-subsection-title">🕒 Actividad reciente</h2>
    <div class="dash-recent">
        <ul>
            <li><i class="fa-solid fa-circle-check text-green"></i> Última compra registrada satisfactoriamente.</li>
            <li><i class="fa-solid fa-user-plus text-blue"></i> Se creó un nuevo usuario.</li>
            <li><i class="fa-solid fa-box text-purple"></i> Se actualizó el inventario.</li>
        </ul>
    </div>

</div>

@endsection
