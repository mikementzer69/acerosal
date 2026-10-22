@extends('layouts.app')

@section('content')

<div class="dashboard">

    <!-- TÍTULO PRINCIPAL -->
    <h1 class="dash-title">📊 Panel de Control</h1>
    <p class="dash-subtitle">Bienvenido al ERP Acerosal. Usa los accesos rápidos para trabajar más rápido.</p>

    @php
        $idRol = auth()->check() ? auth()->user()->id_rol : null;
        $esVendedor = ($idRol == 3);
        $esBodeguero = ($idRol == 2);
    @endphp

    <!-- TARJETAS PRINCIPALES -->
    <div class="dash-cards">

        @if($esBodeguero)
            {{-- Accesos para Bodeguero --}}
            <a href="{{ route('inventario.inventario.productos.index') }}" class="dash-card">
                <div class="dash-icon bg-blue"><i class="fa-solid fa-boxes-stacked"></i></div>
                <div class="dash-info">
                    <h3>Consultar Inventario</h3>
                    <p>Stock físico y piezas</p>
                </div>
            </a>

            <a href="{{ route('inventario.ajuste.kardex') }}" class="dash-card">
                <div class="dash-icon bg-purple"><i class="fa-solid fa-history"></i></div>
                <div class="dash-info">
                    <h3>Kardex</h3>
                    <p>Historial de movimientos</p>
                </div>
            </a>

            <a href="{{ route('inventario.despacho.index') }}" class="dash-card">
                <div class="dash-icon bg-green"><i class="fa-solid fa-truck-ramp-box"></i></div>
                <div class="dash-info">
                    <h3>Órdenes de Despacho</h3>
                    <p>Ver órdenes y despachos</p>
                </div>
            </a>
        @elseif($esVendedor)
            {{-- Accesos para Vendedor --}}
            <a href="{{ route('clientes.index') }}" class="dash-card">
                <div class="dash-icon bg-blue"><i class="fa-solid fa-user-tie"></i></div>
                <div class="dash-info">
                    <h3>Clientes</h3>
                    <p>Gestionar cartera de clientes</p>
                </div>
            </a>

            <a href="{{ route('inventario.despacho.index') }}" class="dash-card">
                <div class="dash-icon bg-green"><i class="fa-solid fa-file-invoice-dollar"></i></div>
                <div class="dash-info">
                    <h3>Órdenes de Despacho</h3>
                    <p>Cotizaciones y despachos</p>
                </div>
            </a>
        @else
            {{-- Accesos Administrador / General --}}
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

            <a href="{{ route('proveedores.index') }}" class="dash-card">
                <div class="dash-icon bg-purple"><i class="fa-solid fa-truck"></i></div>
                <div class="dash-info">
                    <h3>Proveedores</h3>
                    <p>Ver lista</p>
                </div>
            </a>

            @if(auth()->check() && auth()->user()->id_rol == 1)
                <a href="{{ route('usuarios.nuevo') }}" class="dash-card">
                    <div class="dash-icon bg-yellow"><i class="fa-solid fa-user-plus"></i></div>
                    <div class="dash-info">
                        <h3>Nuevo Usuario</h3>
                        <p>Crear cuenta</p>
                    </div>
                </a>
            @endif
        @endif

    </div>

    <!-- SECCIÓN DE ACCESOS RÁPIDOS -->
    <h2 class="dash-subsection-title">⚡ Accesos rápidos</h2>
    <div class="dash-quick-access">

        @if($esBodeguero)
            <a href="{{ route('inventario.inventario.productos.index') }}" class="quick-btn">
                <i class="fa-solid fa-boxes-stacked"></i> Consultar Inventario
            </a>

            <a href="{{ route('inventario.ajuste.kardex') }}" class="quick-btn">
                <i class="fa-solid fa-book"></i> Kardex
            </a>

            <a href="{{ route('inventario.despacho.index') }}" class="quick-btn">
                <i class="fa-solid fa-truck-ramp-box"></i> Órdenes de Despacho
            </a>
        @elseif($esVendedor)
            <a href="{{ route('clientes.index') }}" class="quick-btn">
                <i class="fa-solid fa-users"></i> Clientes
            </a>

            <a href="{{ route('inventario.despacho.index') }}" class="quick-btn">
                <i class="fa-solid fa-truck-ramp-box"></i> Órdenes de Despacho
            </a>
        @else
            <a href="{{ route('inventario.form') }}" class="quick-btn">
                <i class="fa-solid fa-warehouse"></i> Inventario
            </a>

            @if(auth()->check() && auth()->user()->id_rol == 1)
                <a href="{{ route('usuarios.index') }}" class="quick-btn">
                    <i class="fa-solid fa-users"></i> Usuarios
                </a>
            @endif

            <a href="{{ route('productos.nuevo') }}" class="quick-btn">
                <i class="fa-solid fa-plus"></i> Nuevo Producto
            </a>

            @if(auth()->check() && auth()->user()->id_rol == 1)
                <a href="{{ route('roles.index') }}" class="quick-btn">
                    <i class="fa-solid fa-id-card"></i> Roles
                </a>
            @endif
        @endif

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
