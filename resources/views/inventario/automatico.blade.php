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
                    <span class="badge">{{ count($comprasNuevas) }}</span>
                </div>
            </div>
        </div>

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
</style>
@endsection
