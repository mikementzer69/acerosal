<!-- BOTÓN PARA COLAPSAR -->


<button class="sidebar-toggle" onclick="toggleSidebar()">
    <i class="fa-solid fa-bars"></i>
</button>

<aside class="sidebar" id="sidebar">
  <div class="sidebar-scroll">
    <div class="sidebar-header">
        <span>Módulos</span>
    </div>

    @php
        $idRol = auth()->check() ? auth()->user()->id_rol : null;
        $esVendedor = ($idRol == 3);
    @endphp

    <ul class="sidebar-menu">

        <!-- FORMULARIOS -->
        <li class="sidebar-section">
            <button class="sidebar-item" onclick="toggleSubmenu('formMenu')">
                <i class="fa-solid fa-file-pen"></i>
                <span class="item-text">Formularios</span>
                <i class="fa-solid fa-chevron-down arrow"></i>
            </button>

            <ul id="formMenu" class="sidebar-submenu">
                @if(!$esVendedor)
                    <li><a href="{{ route('empresa.lista') }}">Nueva Empresa</a></li>
                    <li><a href="{{ route('familia.lista') }}">Nueva Calidad</a></li>
                    <li><a href="{{ route('costo.lista') }}">Nuevo Costo</a></li>
                    <li><a href="{{ route('inventario.ubicaciones.index') }}">Nueva Ubicacion</a></li>
                    <li><a href="{{ route('modulo.lista') }}">Nuevo Módulo</a></li>
                    <li><a href="{{ route('producto.lista') }}">Nuevo Producto</a></li>
                    <li><a href="{{ route('tarea.lista') }}">Nueva Tarea</a></li>

                    @if(auth()->check() && auth()->user()->id_rol == 1)
                        <li><a href="{{ route('usuarios.index') }}">Nuevo Usuario</a></li>
                        <li><a href="{{ route('roles.index') }}">Nuevo Rol</a></li>
                    @endif

                    <li><a href="{{ route('proveedores.index') }}">Nuevo Proveedor</a></li>
                @endif

                {{-- Clientes: visible para todos (incluyendo Vendedor) --}}
                <li><a href="{{ route('clientes.index') }}">Nuevo Cliente</a></li>
            </ul>
        </li>

        <!-- INVENTARIO -->
        <li class="sidebar-section">
            <button class="sidebar-item" onclick="toggleSubmenu('inventarioMenu')">
                <i class="fa-solid fa-boxes-stacked"></i>
                <span class="item-text">Inventario</span>
                <i class="fa-solid fa-chevron-down arrow"></i>
            </button>

            <ul id="inventarioMenu" class="sidebar-submenu">
                @if(!$esVendedor)
                    <li>
                        <a href="{{ route('compras.index') }}">Nueva Compra</a>
                    </li>
                    <li>
                        <a href="{{ route('inventario.inventario.productos.index') }}">Consulta de inventario</a>
                    </li>
                    <li>
                        <a href="{{ route('inventario.automatico') }}">Ingreso a inventario</a>
                    </li>
                @endif

                {{-- Órdenes de despacho: visible para todos (incluyendo Vendedor) --}}
                <li>
                    <a href="{{ route('inventario.despacho.index') }}">
                        Órdenes de despacho
                    </a>
                </li>

                @if(!$esVendedor)
                    <li>
                        <a href="{{ route('inventario.etiquetas.index') }}">
                            Etiquetas de Piezas
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('inventario.inicial.index') }}" class="nav-link {{ request()->is('inventario/ajustes/inicial*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-boxes"></i>
                            <p>Inventario Inicial</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('inventario.ajuste.index') }}" class="nav-link {{ request()->is('inventario/ajustes/inicial*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-boxes"></i>
                            <p>Ajuste Inventario</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('inventario.ajuste.kardex') }}" class="nav-link {{ request()->is('inventario/ajustes/kardex*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-boxes"></i>
                            <p>Kardex</p>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('inventario.cierre.diario') }}">
                            Cierre diario
                        </a>
                    </li>
                @endif
            </ul>
        </li>

        <!-- CONTABILIDAD (Oculto para Vendedores) -->
        @if(!$esVendedor)
        <li class="sidebar-section">
            <button class="sidebar-item" onclick="toggleSubmenu('ContabilidadMenu')">
                <i class="fa-solid fa-gear"></i>
                <span class="item-text">Contabilidad</span>
                <i class="fa-solid fa-chevron-down arrow"></i>
            </button>

            <ul id="ContabilidadMenu" class="sidebar-submenu">
                <li><a href="{{ route('cuentas.index')}}">Cuentas</a></li>
                <li><a href="{{ route('asientos.index') }}">Partidas</a></li>
                <li><a href="{{ route('mayorizacion.index') }}">Mayorizacion</a></li>
                <li><a href="{{ route('cierre.index') }}">Cierre</a></li>
            </ul>
        </li>
        @endif

    </ul>
  </div>
</aside>

<script>
function toggleSubmenu(id) {
    const submenu = document.getElementById(id);
    submenu.classList.toggle("open");

    const arrow = submenu.previousElementSibling.querySelector(".arrow");
    arrow.classList.toggle("rotate");
}

function toggleSidebar() {
    const sidebar = document.getElementById("sidebar");
    sidebar.classList.toggle("collapsed");

    document.querySelector(".content").classList.toggle("content-collapsed");
}
</script>

<script>
document.addEventListener("DOMContentLoaded", function() {

    // === Al cargar: abrir el submenu almacenado ===
    let openMenu = localStorage.getItem("sidebar_open");

    if (openMenu) {
        const submenu = document.getElementById(openMenu);
        if (submenu) {
            submenu.classList.add("open");
            submenu.previousElementSibling.querySelector(".arrow").classList.add("rotate");
        }
    }

    // === Guardar menú abierto cuando se hace clic ===
    document.querySelectorAll(".sidebar-item").forEach(item => {
        item.addEventListener("click", function() {
            let target = this.getAttribute("onclick").match(/'(.+)'/)[1];
            localStorage.setItem("sidebar_open", target);
        });
    });
});
</script>
