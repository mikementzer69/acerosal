<!-- BOTÓN PARA COLAPSAR -->
<button class="sidebar-toggle" onclick="toggleSidebar()">
    <i class="fa-solid fa-bars"></i>
</button>

<aside class="sidebar" id="sidebar">

    <div class="sidebar-header">
        <span>Módulos</span>
    </div>

    <ul class="sidebar-menu">

        <!-- FORMULARIOS -->
        <li class="sidebar-section">
            <button class="sidebar-item" onclick="toggleSubmenu('formMenu')">
                <i class="fa-solid fa-file-pen"></i>
                <span class="item-text">Formularios</span>
                <i class="fa-solid fa-chevron-down arrow"></i>
            </button>

            <ul id="formMenu" class="sidebar-submenu">
                <li><a href="{{ route('empresa.nueva') }}">Nueva Empresa</a></li>
                <li><a href="{{ route('familia.nueva') }}">Nueva Familia</a></li>
                <li><a href="{{ route('costo.nuevo') }}">Nuevo Costo</a></li>
                <li><a href="{{ route('modulo.nuevo') }}">Nuevo Módulo</a></li>
                <li><a href="{{ route('producto.nuevo') }}">Nuevo Producto</a></li>
                <li><a href="{{ route('tarea.nueva') }}">Nueva Tarea</a></li>
                <li><a href="{{ route('usuario.nuevo') }}">Nuevo Usuario</a></li>
                <li><a href="{{ route('rol.nuevo') }}">Nuevo Rol</a></li>
                <li><a href="{{ route('proveedor.nuevo') }}">Nuevo Proveedor</a></li>
                <li><a href="{{ route('compra.nueva') }}">Nueva Compra</a></li>
                <li><a href="{{ route('inventario.nuevo') }}">Inventario</a></li>
                <li><a href="{{ route('inventario.manual') }}">Inventario Manual</a></li>
            </ul>
        </li>


        <!-- LISTAS -->
        <li class="sidebar-section">
            <button class="sidebar-item" onclick="toggleSubmenu('listMenu')">
                <i class="fa-solid fa-list"></i>
                <span class="item-text">Listas</span>
                <i class="fa-solid fa-chevron-down arrow"></i>
            </button>

            <ul id="listMenu" class="sidebar-submenu">
                <li><a href="{{ route('empresa.lista') }}">Empresas</a></li>
                <li><a href="{{ route('familia.lista') }}">Familias</a></li>
                <li><a href="{{ route('costo.lista') }}">Costos</a></li>
                <li><a href="{{ route('modulo.lista') }}">Módulos</a></li>
                <li><a href="{{ route('producto.lista') }}">Productos</a></li>
                <li><a href="{{ route('tarea.lista') }}">Tareas</a></li>
                <li><a href="{{ route('usuario.lista') }}">Usuarios</a></li>
                <li><a href="{{ route('rol.lista') }}">Roles</a></li>
                <li><a href="{{ route('proveedor.lista') }}">Proveedores</a></li>
            </ul>
        </li>


        <!-- GESTIÓN -->
        <li class="sidebar-section">
            <button class="sidebar-item" onclick="toggleSubmenu('gestionMenu')">
                <i class="fa-solid fa-gear"></i>
                <span class="item-text">Gestión</span>
                <i class="fa-solid fa-chevron-down arrow"></i>
            </button>

            <ul id="gestionMenu" class="sidebar-submenu">
                <li><a href="{{ route('empresa.lista') }}">Empresas</a></li>
                <li><a href="{{ route('familia.gestion') }}">Familias</a></li>
                <li><a href="{{ route('costo.gestion') }}">Costos</a></li>
                <li><a href="{{ route('modulo.gestion') }}">Módulos</a></li>
                <li><a href="{{ route('producto.gestion') }}">Productos</a></li>
                <li><a href="{{ route('tarea.gestion') }}">Tareas</a></li>
                <li><a href="{{ route('usuario.gestion') }}">Usuarios</a></li>
                <li><a href="{{ route('rol.gestion') }}">Roles</a></li>
                <li><a href="{{ route('proveedor.gestion') }}">Proveedores</a></li>
            </ul>
        </li>

    </ul>
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
