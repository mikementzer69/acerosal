<!-- BOTÓN PARA COLAPSAR -->
<button class="sidebar-toggle" onclick="toggleSidebar()">
    <i class="fa-solid fa-bars"></i>
</button>

<aside class="sidebar" id="sidebar">
  <div class="sidebar-scroll">
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
                <li><a href="{{ route('empresa.lista') }}">Nueva Empresa</a></li>
                <li><a href="{{ route('familia.lista') }}">Nueva Familia</a></li>
                <li><a href="{{ route('costo.lista') }}">Nuevo Costo</a></li>
                <li><a href="{{ route('modulo.lista') }}">Nuevo Módulo</a></li>
                <li><a href="{{ route('producto.lista') }}">Nuevo Producto</a></li>
                <li><a href="{{ route('tarea.nueva') }}">Nueva Tarea</a></li>
                <li><a href="{{ route('usuario.nuevo') }}">Nuevo Usuario</a></li>
                <li><a href="{{ route('rol.nuevo') }}">Nuevo Rol</a></li>
                <li><a href="{{ route('proveedor.nuevo') }}">Nuevo Proveedor</a></li>
                <li><a href="{{ route('compras.nueva') }}">Nueva Compra</a></li>
                <li><a href="{{ route('inventario.automatico') }}">Inventario</a></li>
                <li><a href="{{ route('inventario.manual') }}">Inventario Manual</a></li>
                <li><a href="{{ route('inventario.cierre.diario') }}">Cierre Diario</a></li>
                <li><a href="{{ route('inventario.cierre.mensual') }}">Cierre Mensual</a></li>
        
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
                <li><a href="{{ route('modulo.lista') }}">Módulos</a></li>
                <li><a href="{{ route('producto.lista') }}">Productos</a></li>
                <li><a href="{{ route('tarea.lista') }}">Tareas</a></li>
                <li><a href="{{ route('usuario.gestion') }}">Usuarios</a></li>
                <li><a href="{{ route('rol.gestion') }}">Roles</a></li>
                <li><a href="{{ route('proveedor.gestion') }}">Proveedores</a></li>
            </ul>
        </li>

        <!-- Contabilidad -->
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
