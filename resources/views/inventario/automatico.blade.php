@extends('layouts.app')

@section('content')

{{-- CONTENEDOR PRINCIPAL --}}
<div class="inventory-dashboard">

    {{-- 1. SELECT OCULTO (EL PUENTE REAL) --}}
    <select id="listboxCompras" style="display: none;">
        <option value="">Seleccione...</option>
        @foreach($comprasNuevas as $compra)
            <option value="{{ $compra->id_compra }}">{{ $compra->numero_factura }}</option>
        @endforeach
    </select>

    {{-- 2. PANEL IZQUIERDO (SIDEBAR VISUAL) --}}
    <aside class="dashboard-sidebar">
        <div class="sidebar-header">
            <div style="display: flex; justify-content: space-between; align-items: center; width: 100%;">
                <div>
                    <h3><i class="fa-solid fa-boxes-packing"></i> Inventario</h3>
                    <span class="badge">{{ $comprasNuevas instanceof \Illuminate\Pagination\LengthAwarePaginator ? $comprasNuevas->total() : count($comprasNuevas) }}</span>
                </div>
            </div>
        </div>

        {{-- BÚSQUEDA Y FILTRO RÁPIDO --}}
        <form method="GET" action="{{ route('inventario.automatico') }}" style="padding: 10px; background-color: #111; border-bottom: 1px solid #222; display: flex; flex-direction: column; gap: 6px;">
            <div style="display: flex; gap: 6px;">
                <input type="text"
                       name="busqueda"
                       value="{{ request('busqueda') }}"
                       placeholder="Buscar factura o proveedor..."
                       style="padding: 5px 8px; font-size: 0.8rem; background: #000; color: #fff; border: 1px solid #333; border-radius: 4px; flex: 1;">
                <button type="submit" style="background: #2563eb; color: #fff; border: none; border-radius: 4px; padding: 5px 10px; cursor: pointer; font-size: 0.8rem;">
                    <i class="fa-solid fa-magnifying-glass"></i>
                </button>
            </div>
            <div style="display: flex; gap: 6px; align-items: center;">
                <select name="estado" onchange="this.form.submit()" style="padding: 4px 6px; font-size: 0.75rem; background: #000; color: #bbb; border: 1px solid #333; border-radius: 4px; flex: 1;">
                    <option value="">-- Todos los estados --</option>
                    <option value="pendiente" {{ request('estado') === 'pendiente' ? 'selected' : '' }}>Pendientes</option>
                    <option value="ingresado" {{ request('estado') === 'ingresado' ? 'selected' : '' }}>Ingresados</option>
                </select>
                @if(request('busqueda') || request('estado'))
                    <a href="{{ route('inventario.automatico') }}" title="Limpiar filtro" style="color: #ef4444; font-size: 0.75rem; padding: 4px 6px; text-decoration: none; display: flex; align-items: center;">
                        <i class="fa-solid fa-xmark"></i>
                    </a>
                @endif
            </div>
        </form>

        <div class="sidebar-list">
            @forelse($comprasNuevas as $compra)

                {{-- 🪄 LA MAGIA: Si es INGRESADO (0), lo convertimos en un link directo --}}
                @if($compra->nueva_compra == 0)
                    <a href="{{ route('inventario.consulta', $compra->id_compra) }}" style="text-decoration: none; color: inherit; display: block;">
                @endif

                <div class="purchase-item"
                    {{-- Solo ejecutamos el JS si la compra es PENDIENTE (1) --}}
                    @if($compra->nueva_compra == 1)
                        onclick="seleccionarCompra({{ $compra->id_compra }}, {{ $compra->nueva_compra }})"
                    @endif
                    id="card-{{ $compra->id_compra }}"
                    style="border-left: 5px solid {{ $compra->nueva_compra == 1 ? '#fbbf24' : '#10b981' }}; cursor: pointer;">

                    <div class="pi-top">
                        <span class="pi-factura">#{{ $compra->numero_factura }}</span>
                        <span class="pi-date">{{ $compra->fecha_ingreso }}</span>
                    </div>

                    <div class="pi-status" style="color: {{ $compra->nueva_compra == 1 ? '#fbbf24' : '#10b981' }}; font-weight: bold;">
                        @if($compra->nueva_compra == 1)
                            <i class="fa-regular fa-clock"></i> PENDIENTE
                        @else
                            <i class="fa-solid fa-check-double"></i> INGRESADO
                        @endif
                    </div>
                </div>

                @if($compra->nueva_compra == 0)
                    </a>
                @endif

            @empty
                <div style="padding: 20px; text-align: center; color: #666;">No hay registros</div>
            @endforelse
        </div>

        {{-- PAGINACIÓN COMPACTA EN SIDEBAR --}}
        @if($comprasNuevas instanceof \Illuminate\Pagination\LengthAwarePaginator && $comprasNuevas->hasPages())
            <div class="sidebar-pagination" style="padding: 8px 5px; background: #000; border-top: 1px solid #222; display: flex; justify-content: center;">
                {{ $comprasNuevas->links('pagination::bootstrap-5') }}
            </div>
        @endif
    </aside>

    {{-- 3. PANEL DERECHO (MAIN AREA) --}}
    <main class="dashboard-main">

        {{-- Pantalla de Bienvenida --}}
        <div id="mensaje-bienvenida" class="welcome-screen">
            <div class="welcome-content">
                <i class="fa-solid fa-hand-pointer"></i>
                <h2>Seleccione una compra</h2>
                <p>Haga clic en el menú izquierdo para comenzar el ingreso.</p>
            </div>
        </div>

        {{-- Área de Trabajo (Se llena con JS) --}}
        <div id="area-trabajo" style="display: none;">

            {{-- Información General --}}
            <div class="dark-card mb-4">
                <div class="dark-card-header">Información General</div>
                <div class="dark-card-body">
                    <div id="infoCompra" class="info-grid"></div>
                </div>
            </div>

            {{-- Contenedor de Lotes --}}
            <div id="contenedorLotes"></div>

            {{-- Botón Guardar --}}
            <div class="action-footer">
                <div class="action-footer" style="display: flex; gap: 15px;">

                    {{-- Botón Cancelar (Gris/Rojo) --}}
                    <button type="button" onclick="cancelarEdicion()" class="btn-cancel">
                        <i class="fa-solid fa-xmark"></i> Cancelar
                    </button>

                    {{-- Botón Guardar Borrador --}}
                    <button type="button" id="btnGuardarBorrador" class="btn-draft" style="flex: 1; padding: 12px; background-color: #4b5563; color: white; border: none; border-radius: 6px; font-size: 1rem; cursor: pointer; display: flex; justify-content: center; align-items: center; gap: 8px;">
                        <i class="fa-solid fa-file-lines"></i> Borrador
                    </button>
                    
                    {{-- Botón Restaurar Borrador --}}
                    <button type="button" id="btnRestaurarBorrador" class="btn-info" style="display: none; flex: 1; padding: 12px; background-color: #0ea5e9; color: white; border: none; border-radius: 6px; font-size: 1rem; cursor: pointer; justify-content: center; align-items: center; gap: 8px;">
                        <i class="fa-solid fa-clock-rotate-left"></i> Restaurar
                    </button>

                    {{-- Botón Guardar (Azul) --}}
                    {{-- NOTA: El ID se mantiene para que tu JS original siga funcionando --}}
                    <button type="button" id="btnGuardarPREVIO" class="btn-save">
                        <i class="fa-solid fa-floppy-disk"></i> Guardar Inventario
                    </button>
                </div>
                <button type="button" id="btnGuardarInventario" hidden style="display: none !important; position: absolute; visibility: hidden;"></button>            </div>
        </div>
    </main>

</div>

{{-- ==========================================================
     SCRIPTS
   ========================================================== --}}
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
{{-- SweetAlert2 (Necesario para las alertas bonitas) --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@vite(['resources/js/inventario.js'])

<script>
    window.APP_URL = "{{ url('/') }}";
    window.csrfToken = "{{ csrf_token() }}";
    let idCompraActiva = null;

    // 1. SELECCIÓN (Siempre disponible para el panel izquierdo)
    function seleccionarCompra(id, tipo) {
        idCompraActiva = id;
        $('.purchase-item').removeClass('active');
        $('#card-' + id).addClass('active');
        $('#mensaje-bienvenida').hide();
        $('#area-trabajo').show();

        const btnVisible = $('#btnGuardarPREVIO');
        if (tipo == 0) {
            btnVisible.hide();
        } else {
            btnVisible.show();
            // Aseguramos que el botón esté activo al cambiar de factura
            btnVisible.prop('disabled', false).html('<i class="fa-solid fa-floppy-disk"></i> Guardar Inventario');
        }

        let select = document.getElementById('listboxCompras');
        if(select) {
            select.value = id;
            select.dispatchEvent(new Event('change'));
        }
    }

    function cancelarEdicion() {
        $('#area-trabajo').hide();
        $('#mensaje-bienvenida').css('display', 'flex');
        $('.purchase-item').removeClass('active');
    }

    // 2. LÓGICA DE GUARDADO CON RESET (Mimetiza el botón "Revisar")
    $(document).ready(function() {
        const btnVisible = $('#btnGuardarPREVIO');
        const btnReal = $('#btnGuardarInventario');

        btnVisible.on('click', function() {
            Swal.fire({
                title: '¿Confirmar Ingreso?',
                text: "Se crearán las piezas físicas y se actualizará el stock de Acerosal.",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#2563eb',
                confirmButtonText: 'Sí, guardar',
                cancelButtonText: 'Revisar', // Este botón solo cierra el diálogo
                background: '#1a1a1a',
                color: '#ddd'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Entramos en estado "Guardando..."
                    btnVisible.prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin"></i> Guardando...');

                    // --- APLICAMOS LA LÓGICA DE "REVISAR" A LOS ERRORES ---
                    const originalAlert = window.alert;
                    window.alert = function(msg) {
                        originalAlert(msg); // Muestra el error: "Faltan lotes..."
                        // Al darle "Aceptar" al error, hacemos lo mismo que el botón "Revisar":
                        // Reactivamos el botón para que el usuario pueda corregir.
                        btnVisible.prop('disabled', false).html('<i class="fa-solid fa-floppy-disk"></i> Guardar Inventario');
                    };

                    btnReal.click(); // Ejecuta inventario.js

                    // Restauramos el alert normal después de un momento
                    setTimeout(() => { window.alert = originalAlert; }, 2000);
                }
                // Si el usuario presiona "Revisar" en el cuadro negro,
                // el botón simplemente se queda como estaba (activo).
            });
        });

        // Vigilante Nocturno (Modo Oscuro)
        const targetNode = document.getElementById('contenedorLotes');
        if (targetNode) {
            const observer = new MutationObserver(() => {
                $('#contenedorLotes .card, #contenedorLotes > div, #contenedorLotes .bg-white')
                    .css({'background-color': '#1a1a1a', 'color': '#ddd', 'border': '1px solid #333'})
                    .removeClass('bg-white table-light card');
            });
            observer.observe(targetNode, { childList: true, subtree: true });
        }
    });
</script>

{{-- ==========================================================
     ESTILOS CSS
   ========================================================== --}}
<style>
    /* 1. LAYOUT PRINCIPAL */
    .inventory-dashboard { display: flex; height: 85vh; background-color: #0f0f0f; border: 1px solid #333; border-radius: 8px; overflow: hidden; font-family: sans-serif; }

    /* SIDEBAR */
    .dashboard-sidebar { width: 300px; background-color: #161616; border-right: 1px solid #333; display: flex; flex-direction: column; }
    .sidebar-header { padding: 15px; background-color: #000; border-bottom: 1px solid #333; display: flex; justify-content: space-between; color: #fff; }
    .sidebar-list { flex: 1; overflow-y: auto; }

    .purchase-item { padding: 15px; border-bottom: 1px solid #222; cursor: pointer; border-left: 4px solid transparent; color: #aaa; transition: all 0.2s; }
    .purchase-item:hover { background-color: #1f1f1f; color: #fff; }
    .purchase-item.active { background-color: #1c2533; border-left-color: #3b82f6; color: #fff; }

    .pi-top { display: flex; justify-content: space-between; margin-bottom: 5px; }
    .pi-factura { font-weight: bold; color: #fff; }
    .pi-date { font-size: 0.8rem; }
    .pi-status { font-size: 0.75rem; color: #fbbf24; }
    .badge { background: #2563eb; color: #fff; padding: 2px 8px; border-radius: 10px; font-size: 0.8rem; }

    /* MAIN CONTENT */
    .dashboard-main { flex: 1; background-color: #0f0f0f; padding: 20px; overflow-y: auto; position: relative; }
    .welcome-screen { height: 100%; display: flex; justify-content: center; align-items: center; color: #444; text-align: center; }
    .welcome-content i { font-size: 3rem; margin-bottom: 15px; }

    /* 2. ÁREA DE TRABAJO Y TARJETAS */
    #area-trabajo { max-width: 1200px; margin: 0 auto; }

    .dark-card, #contenedorLotes > div, .card { background-color: #1a1a1a !important; border: 1px solid #333 !important; border-radius: 8px !important; margin-bottom: 20px !important; color: #ddd !important; box-shadow: none !important; }
    .dark-card-header, .card-header { background-color: #000 !important; border-bottom: 1px solid #333 !important; color: #fff !important; padding: 10px 15px !important; font-weight: bold !important; }
    .dark-card-body, .card-body { padding: 15px !important; }

    /* ETIQUETAS DE CAMPOS Y LOTES */
    .grid-lote label,
    #contenedorLotes label,
    .inventory-dashboard label {
        color: #94a3b8 !important;
        font-size: 12px !important;
        font-weight: 700 !important;
        margin-bottom: 5px !important;
        display: block !important;
        text-transform: uppercase;
        letter-spacing: 0.3px;
    }

    /* 3. INPUTS Y TABLAS */
    input, select, textarea, .form-control { background-color: #000 !important; border: 1px solid #444 !important; color: #fff !important; border-radius: 4px !important; padding: 8px !important; }
    input[readonly], input:disabled { background-color: #2c2c2c !important; color: #fbbf24 !important; opacity: 1 !important; }
    table, .table { width: 100% !important; background-color: #1a1a1a !important; color: #ddd !important; border-collapse: separate !important; border-spacing: 0 !important; border: 1px solid #333 !important; }
    thead, th { background-color: #000 !important; color: #fff !important; border-bottom: 1px solid #444 !important; }
    td { border-bottom: 1px solid #333 !important; }
    .bg-white, .table-light { background-color: #1a1a1a !important; color: #fff !important; }

    /* ARREGLO QUIRÚRGICO PARA LA SEGUNDA PIEZA */
    #contenedorLotes .table-striped tbody tr:nth-of-type(even), #contenedorLotes table tr:nth-of-type(even), #contenedorLotes table td, #contenedorLotes .card-body, #contenedorLotes .bg-light { background-color: #1a1a1a !important; color: #ffffff !important; }
    #contenedorLotes table tr:hover td { background-color: #252525 !important; color: #3b82f6 !important; }

    /* Botones Footer */
    .btn-cancel { flex: 1; padding: 12px; background-color: transparent; color: #888; border: 1px solid #444; border-radius: 6px; font-size: 1rem; cursor: pointer; transition: all 0.2s ease; display: flex; justify-content: center; align-items: center; gap: 8px; }
    .btn-cancel:hover { background-color: #2c2c2c; color: #ef4444; border-color: #ef4444; }
    .btn-save { flex: 2; padding: 12px; background-color: #2563eb; color: white; border: none; border-radius: 6px; font-size: 1.1rem; cursor: pointer; font-weight: bold; display: flex; justify-content: center; align-items: center; gap: 8px; }
    .btn-save:hover { background-color: #1d4ed8; }

    /* PAGINACIÓN COMPACTA SIDEBAR */
    .sidebar-pagination {
        margin-top: auto;
    }
    .sidebar-pagination nav {
        display: flex;
        flex-direction: column;
        align-items: center;
    }
    .sidebar-pagination .pagination {
        display: flex;
        list-style: none;
        gap: 4px;
        padding: 0;
        margin: 0;
        align-items: center;
    }
    .sidebar-pagination .page-item {
        display: inline-block;
    }
    .sidebar-pagination .page-link {
        background-color: #1e293b !important;
        border: 1px solid #334155 !important;
        color: #e2e8f0 !important;
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 0.75rem;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }
    .sidebar-pagination .page-item.active .page-link {
        background-color: #3b82f6 !important;
        border-color: #3b82f6 !important;
        color: #ffffff !important;
        font-weight: 700;
    }
    .sidebar-pagination .page-item.disabled .page-link {
        background-color: #0f172a !important;
        border-color: #1e293b !important;
        color: #475569 !important;
        opacity: 0.5;
    }
    .sidebar-pagination .page-link:hover:not(.disabled) {
        background-color: #334155 !important;
        color: #38bdf8 !important;
    }
    .sidebar-pagination .text-muted,
    .sidebar-pagination p {
        display: none !important;
    }
    .sidebar-pagination svg {
        width: 12px;
        height: 12px;
    }
</style>
@endsection
