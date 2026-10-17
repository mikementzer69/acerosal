@extends('layouts.app')

@section('content')
<div class="erp-section">

    {{-- TÍTULO --}}
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 10px;">
        <div>
            <h2 class="erp-title" style="margin: 0;">
                <i class="fa-solid fa-tags text-warning"></i> Impresión de Etiquetas de Piezas (Viñetas)
            </h2>
            <p style="color: #94a3b8; font-size: 0.85rem; margin: 4px 0 0 0;">
                Generación de viñetas adhesivas para barras y piezas de bodega con código de barras, lote y medidas físicas.
            </p>
        </div>
        <div style="display: flex; gap: 10px; align-items: center;">
            <button type="button" id="btnCargarTodoDirecto" class="btn btn-primary" style="background-color: #2563eb; border: none; font-weight: 700; padding: 8px 16px; font-size: 0.88rem;">
                <i class="fa-solid fa-layer-group me-1"></i> Cargar TODO el Inventario
            </button>
        </div>
    </div>

    {{-- ALERTAS --}}
    @if(session('success'))
        <div class="form-alert success mb-3">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="form-alert danger mb-3">{{ session('error') }}</div>
    @endif

    {{-- CARD DE FILTROS EN CASCADA CON OPCIÓN 'TODAS' --}}
    <div class="card p-3 mb-4" style="background: #1e293b; border: 1px solid #334155; border-radius: 10px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; flex-wrap: wrap; gap: 8px;">
            <div style="color: #cbd5e1 !important; font-size: 0.85rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px;">
                <i class="fa-solid fa-filter me-1 text-primary"></i> Filtros de Selección (Puedes elegir "Todas / Todos")
            </div>
            <button type="button" id="btnBuscarManual" class="btn btn-sm" style="background-color: #3b82f6; color: #fff; font-size: 12px; font-weight: 600; padding: 4px 12px;">
                <i class="fa-solid fa-rotate me-1"></i> Actualizar Listado
            </button>
        </div>

        <div class="row g-3">
            {{-- 1. CALIDAD (FAMILIA) --}}
            <div class="col-md-4">
                <label style="color: #38bdf8 !important; font-size: 12px; font-weight: 700; text-transform: uppercase; margin-bottom: 6px; display: block;">
                    1. Calidad (Familia)
                </label>
                <select id="selectFamilia" class="form-select" style="background: #0f172a; color: #fff; border: 1px solid #475569; border-radius: 6px; padding: 9px 12px; width: 100%;">
                    <option value="todas">⭐ -- TODAS LAS CALIDADES --</option>
                    @foreach($familias as $f)
                        <option value="{{ $f->id_familia }}" {{ (string)$f->id_familia === (string)$idFamiliaSeleccionada ? 'selected' : '' }}>
                            {{ $f->nombre }} {{ $f->codigo ? '('.$f->codigo.')' : '' }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- 2. PRODUCTO (MEDIDA / DIÁMETRO) --}}
            <div class="col-md-4">
                <label style="color: #38bdf8 !important; font-size: 12px; font-weight: 700; text-transform: uppercase; margin-bottom: 6px; display: block;">
                    2. Medida / Producto
                </label>
                <select id="selectProducto" class="form-select" style="background: #0f172a; color: #fff; border: 1px solid #475569; border-radius: 6px; padding: 9px 12px; width: 100%;">
                    <option value="todos">⭐ -- TODOS LOS PRODUCTOS / MEDIDAS --</option>
                </select>
            </div>

            {{-- 3. LOTE --}}
            <div class="col-md-4">
                <label style="color: #38bdf8 !important; font-size: 12px; font-weight: 700; text-transform: uppercase; margin-bottom: 6px; display: block;">
                    3. Lote
                </label>
                <select id="selectLote" class="form-select" style="background: #0f172a; color: #fff; border: 1px solid #475569; border-radius: 6px; padding: 9px 12px; width: 100%;">
                    <option value="todos">⭐ -- TODOS LOS LOTES --</option>
                </select>
            </div>
        </div>
    </div>

    {{-- ÁREA DE PIEZAS --}}
    <div id="seccionPiezas" style="display: none;">
        
        <form id="formImprimirEtiquetas" action="{{ route('inventario.etiquetas.imprimir') }}" method="GET" target="_blank">
            
            {{-- Campos ocultos para mantener filtros en impresión masiva --}}
            <input type="hidden" name="id_familia" id="hiddenFamilia" value="todas">
            <input type="hidden" name="id_producto" id="hiddenProducto" value="todos">
            <input type="hidden" name="id_lote" id="hiddenLote" value="todos">

<style>
    /* Estilos dedicados para el módulo de etiquetas - Alto contraste y máxima legibilidad */
    .etiquetas-toolbar {
        background: #1e293b !important;
        border: 1px solid #3b82f6 !important;
        border-radius: 8px !important;
        padding: 14px 18px !important;
        margin-bottom: 15px !important;
        display: flex !important;
        justify-content: space-between !important;
        align-items: center !important;
        flex-wrap: wrap !important;
        gap: 12px !important;
        box-shadow: 0 4px 12px rgba(0,0,0,0.35) !important;
    }
    #lblInfoLote, #lblInfoLote * {
        color: #ffffff !important;
    }
    #lblSubInfoLote, #lblSubInfoLote * {
        color: #38bdf8 !important;
    }
    .etiqueta-formato-label {
        color: #ffffff !important;
        font-weight: 700 !important;
        font-size: 0.88rem !important;
    }
    /* Estilos forzados para opciones de los select */
    #selectFamilia option,
    #selectProducto option,
    #selectLote option,
    #formatoImpresion option {
        background-color: #0f172a !important;
        color: #ffffff !important;
        font-weight: 600;
        padding: 8px 12px;
    }
</style>

            {{-- BARRA DE HERRAMIENTAS Y RESUMEN --}}
            <div class="etiquetas-toolbar">
                <div>
                    <div id="lblInfoLote" style="color: #ffffff !important; font-size: 1.15rem; font-weight: 800; letter-spacing: 0.3px; margin-bottom: 4px;">
                        <span style="color: #94a3b8 !important; font-weight: 700;">Piezas:</span> <span style="color: #ffffff !important; font-weight: 800;">⭐ -- TODAS LAS CALIDADES --</span>
                    </div>
                    <div id="lblSubInfoLote" style="color: #38bdf8 !important; font-size: 0.92rem; font-weight: 700;">
                        <span style="color: #38bdf8 !important; font-weight: 700;">⭐ -- TODOS LOS PRODUCTOS / MEDIDAS --</span> <span style="color: #fbbf24 !important; font-weight: 900; margin: 0 6px;">➔</span> <span style="color: #34d399 !important; font-weight: 700;">⭐ -- TODOS LOS LOTES --</span>
                    </div>
                </div>

                <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
                    {{-- SELECTOR DE FORMATO --}}
                    <div style="display: flex; align-items: center; gap: 8px; background: #0f172a; padding: 5px 12px; border-radius: 6px; border: 1px solid #334155;">
                        <span class="etiqueta-formato-label">
                            <i class="fa-solid fa-file-lines text-warning me-1"></i> Formato:
                        </span>
                        <select name="formato" id="formatoImpresion" style="background: #1e293b; color: #ffffff !important; border: 1px solid #475569; border-radius: 5px; padding: 5px 10px; font-size: 0.85rem; font-weight: 600;">
                            <option value="rollo">🏷️ Rollo Térmico / Sticker Individual</option>
                            <option value="cuadricula">📄 Hoja Cuadrícula (Múltiples)</option>
                        </select>
                    </div>

                    {{-- BOTÓN IMPRIMIR TODO LO FILTRADO --}}
                    <button type="button" id="btnImprimirTodoFiltrado" class="btn btn-sm" style="padding: 8px 16px; font-weight: 700; background-color: #2563eb; color: #ffffff !important; border: none; border-radius: 6px; box-shadow: 0 2px 4px rgba(37,99,235,0.3);">
                        <i class="fa-solid fa-print me-1"></i> Imprimir Todas (<span id="totalPiezasBadge" style="color: #ffffff !important;">0</span>)
                    </button>

                    {{-- BOTÓN IMPRIMIR SELECCIONADAS --}}
                    <button type="submit" id="btnImprimirSeleccionadas" class="btn btn-sm btn-success" style="padding: 8px 18px; font-weight: 700; background-color: #10b981; border: none; border-radius: 6px; color: #ffffff !important; box-shadow: 0 2px 4px rgba(16,185,129,0.3);">
                        <i class="fa-solid fa-tags me-1"></i> Imprimir Seleccionadas (<span id="cantSeleccionadas" style="color: #ffffff !important;">0</span>)
                    </button>
                </div>
            </div>

            {{-- TABLA DE PIEZAS --}}
            <div class="erp-table-container">
                <table class="erp-table" style="color: #ffffff !important;">
                    <thead>
                        <tr style="background: #0f172a;">
                            <th style="width: 45px; text-align: center; color: #ffffff !important;">
                                <input type="checkbox" id="checkTodos" title="Marcar/Desmarcar todas" style="cursor: pointer; width: 16px; height: 16px;">
                            </th>
                            <th style="width: 50px; text-align: center; color: #ffffff !important;">#</th>
                            <th style="color: #ffffff !important; font-weight: 800;">Código de Pieza</th>
                            <th style="color: #ffffff !important; font-weight: 800;">Producto / Medida</th>
                            <th style="color: #ffffff !important; font-weight: 800;">Calidad</th>
                            <th style="color: #ffffff !important; font-weight: 800;">Lote</th>
                            <th style="text-align: right; color: #ffffff !important; font-weight: 800;">Longitud (Mts)</th>
                            <th style="text-align: right; color: #ffffff !important; font-weight: 800;">Peso (Lb)</th>
                            <th style="text-align: center; color: #ffffff !important; font-weight: 800;">Estado</th>
                            <th style="width: 110px; text-align: center; color: #ffffff !important; font-weight: 800;">Acción</th>
                        </tr>
                    </thead>
                    <tbody id="tablaPiezasBody">
                        {{-- Llenado dinámico vía JS --}}
                    </tbody>
                </table>
            </div>

            {{-- BARRA DE PAGINACIÓN DE LA TABLA DE PIEZAS --}}
            <div id="paginacionPiezasContainer" style="display: none; justify-content: space-between; align-items: center; margin-top: 18px; padding: 12px 18px; background: #0f172a; border: 1px solid #334155; border-radius: 8px; flex-wrap: wrap; gap: 12px;">
                <div style="color: #94a3b8; font-size: 13px;">
                    Mostrando piezas <strong id="lblPaginacionRango" style="color: #ffffff;">0 - 0</strong> de un total de <strong id="lblPaginacionTotal" style="color: #38bdf8;">0</strong>
                </div>
                
                <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                    <div style="display: flex; align-items: center; gap: 6px;">
                        <span style="color: #cbd5e1; font-size: 12px; font-weight: 600;">Ver por página:</span>
                        <select id="selectPorPagina" style="background: #1e293b; color: #ffffff; border: 1px solid #475569; border-radius: 5px; padding: 5px 8px; font-size: 12px; font-weight: 600;">
                            <option value="15" selected>15 por pág</option>
                            <option value="25">25 por pág</option>
                            <option value="50">50 por pág</option>
                            <option value="100">100 por pág</option>
                            <option value="all">Todas</option>
                        </select>
                    </div>

                    <div style="display: flex; gap: 4px; align-items: center;">
                        <button type="button" id="btnPaginaPrimera" class="btn btn-sm" style="background: #1e293b; color: #fff; border: 1px solid #475569; padding: 4px 10px; border-radius: 4px;" title="Primera página">
                            <i class="fa-solid fa-angles-left"></i>
                        </button>
                        <button type="button" id="btnPaginaAnterior" class="btn btn-sm" style="background: #1e293b; color: #fff; border: 1px solid #475569; padding: 4px 10px; border-radius: 4px;" title="Página anterior">
                            <i class="fa-solid fa-chevron-left"></i>
                        </button>
                        <span id="lblPaginaActual" style="display: inline-flex; align-items: center; padding: 4px 12px; color: #38bdf8; font-weight: 700; font-size: 12px; background: #1e293b; border: 1px solid #475569; border-radius: 4px;">
                            Pág. 1 de 1
                        </span>
                        <button type="button" id="btnPaginaSiguiente" class="btn btn-sm" style="background: #1e293b; color: #fff; border: 1px solid #475569; padding: 4px 10px; border-radius: 4px;" title="Página siguiente">
                            <i class="fa-solid fa-chevron-right"></i>
                        </button>
                        <button type="button" id="btnPaginaUltima" class="btn btn-sm" style="background: #1e293b; color: #fff; border: 1px solid #475569; padding: 4px 10px; border-radius: 4px;" title="Última página">
                            <i class="fa-solid fa-angles-right"></i>
                        </button>
                    </div>
                </div>
            </div>

        </form>
    </div>

    {{-- PANTALLA INICIAL DE ESPERA / CARGANDO --}}
    <div id="pantallaEspera" style="padding: 60px 20px; text-align: center; background: #0f172a; border: 1px dashed #334155; border-radius: 10px; margin-top: 15px;">
        <i class="fa-solid fa-tags" style="font-size: 3.5rem; color: #334155; margin-bottom: 15px; display: block;"></i>
        <h5 style="color: #94a3b8; font-weight: 600;">Cargando piezas del inventario...</h5>
        <p style="color: #64748b; font-size: 0.9rem; max-width: 520px; margin: 0 auto;">
            Puedes filtrar por una calidad o lote específico, o dejar <strong>"TODAS"</strong> para imprimir las viñetas completas de la bodega.
        </p>
    </div>

</div>

{{-- SCRIPT PARA CASCADA AJAX CON SOPORTE "TODAS" --}}
<script>
document.addEventListener('DOMContentLoaded', function() {
    const selFam  = document.getElementById('selectFamilia');
    const selProd = document.getElementById('selectProducto');
    const selLote = document.getElementById('selectLote');
    
    const seccionPiezas   = document.getElementById('seccionPiezas');
    const pantallaEspera  = document.getElementById('pantallaEspera');
    const tbody           = document.getElementById('tablaPiezasBody');
    const checkTodos      = document.getElementById('checkTodos');
    const cantBadge       = document.getElementById('cantSeleccionadas');
    const totalBadge      = document.getElementById('totalPiezasBadge');
    const btnImprimirSel  = document.getElementById('btnImprimirSeleccionadas');
    const btnImprimirTodo = document.getElementById('btnImprimirTodoFiltrado');
    const btnCargarTodoDirecto = document.getElementById('btnCargarTodoDirecto');
    const btnBuscarManual = document.getElementById('btnBuscarManual');

    const hiddenFam  = document.getElementById('hiddenFamilia');
    const hiddenProd = document.getElementById('hiddenProducto');
    const hiddenLote = document.getElementById('hiddenLote');

    // Variables de paginación y selección
    let todasLasPiezas = [];
    let piezasSeleccionadas = new Set();
    let paginaActual = 1;

    const paginacionContainer = document.getElementById('paginacionPiezasContainer');
    const lblPaginacionRango  = document.getElementById('lblPaginacionRango');
    const lblPaginacionTotal  = document.getElementById('lblPaginacionTotal');
    const lblPaginaActual     = document.getElementById('lblPaginaActual');
    const selectPorPagina     = document.getElementById('selectPorPagina');
    const btnPaginaPrimera    = document.getElementById('btnPaginaPrimera');
    const btnPaginaAnterior   = document.getElementById('btnPaginaAnterior');
    const btnPaginaSiguiente  = document.getElementById('btnPaginaSiguiente');
    const btnPaginaUltima     = document.getElementById('btnPaginaUltima');
    const formImprimir        = document.getElementById('formImprimirEtiquetas');

    // Valores iniciales si vienen por URL
    let famInit  = "{{ $idFamiliaSeleccionada ?? 'todas' }}";
    let prodInit = "{{ $idProductoSeleccionado ?? 'todos' }}";
    let loteInit = "{{ $idLoteSeleccionado ?? 'todos' }}";

    // 1. CAMBIO DE FAMILIA
    selFam.addEventListener('change', function() {
        cargarProductos(this.value);
    });

    function cargarProductos(idFamilia, preselectProd = 'todos') {
        selProd.innerHTML = '<option value="todos">⏳ Cargando medidas...</option>';
        selLote.innerHTML = '<option value="todos">⭐ -- TODOS LOS LOTES --</option>';

        const url = idFamilia === 'todas' 
            ? `{{ url('/inventario/etiquetas/ajax/productos') }}` 
            : `{{ url('/inventario/etiquetas/ajax/productos') }}/${idFamilia}`;

        fetch(url)
            .then(res => res.json())
            .then(data => {
                selProd.innerHTML = '<option value="todos">⭐ -- TODOS LOS PRODUCTOS / MEDIDAS --</option>';
                data.forEach(p => {
                    const mm = p.milimetros ? `${p.milimetros} mm` : '';
                    const plg = p.pulgadas && p.pulgadas !== '-' ? `${p.pulgadas}"` : '';
                    const sep = (mm && plg) ? ' / ' : '';
                    const medidas = (mm || plg) ? ` (${mm}${sep}${plg})` : '';
                    const famTag = (idFamilia === 'todas' && p.familia_nombre) ? ` [${p.familia_nombre}]` : '';

                    const opt = document.createElement('option');
                    opt.value = p.id_producto;
                    opt.textContent = `${p.descripcion}${medidas}${famTag}`;
                    if (preselectProd && String(p.id_producto) === String(preselectProd)) {
                        opt.selected = true;
                    }
                    selProd.appendChild(opt);
                });

                cargarLotes(selProd.value, loteInit);
            })
            .catch(err => {
                console.error(err);
                selProd.innerHTML = '<option value="todos">⭐ -- TODOS LOS PRODUCTOS / MEDIDAS --</option>';
                cargarLotes('todos', loteInit);
            });
    }

    // 2. CAMBIO DE PRODUCTO
    selProd.addEventListener('change', function() {
        cargarLotes(this.value);
    });

    function cargarLotes(idProducto, preselectLote = 'todos') {
        selLote.innerHTML = '<option value="todos">⏳ Cargando lotes...</option>';

        let url = `{{ url('/inventario/etiquetas/ajax/lotes') }}`;
        const params = new URLSearchParams();
        if (idProducto && idProducto !== 'todos') {
            params.append('id_producto', idProducto);
        }
        if (selFam.value && selFam.value !== 'todas') {
            params.append('id_familia', selFam.value);
        }
        if (params.toString()) {
            url += `?${params.toString()}`;
        }

        fetch(url)
            .then(res => res.json())
            .then(data => {
                selLote.innerHTML = '<option value="todos">⭐ -- TODOS LOS LOTES --</option>';
                data.forEach(l => {
                    const fecha = l.fecha_ingreso ? l.fecha_ingreso.split('T')[0] : '';
                    const prodDesc = (idProducto === 'todos' && l.producto_descripcion) ? ` [${l.producto_descripcion}]` : '';

                    const opt = document.createElement('option');
                    opt.value = l.id_lote;
                    opt.textContent = `Lote #${l.codigo} | ${parseFloat(l.cantidad_total_metros).toFixed(2)}m (${l.total_piezas} pzs) [${fecha}]${prodDesc}`;
                    if (preselectLote && String(l.id_lote) === String(preselectLote)) {
                        opt.selected = true;
                    }
                    selLote.appendChild(opt);
                });

                // Inmediatamente cargar las piezas correspondientes
                cargarPiezas();
            })
            .catch(err => {
                console.error(err);
                selLote.innerHTML = '<option value="todos">⭐ -- TODOS LOS LOTES --</option>';
                cargarPiezas();
            });
    }

    // 3. CAMBIO DE LOTE
    selLote.addEventListener('change', function() {
        cargarPiezas();
    });

    btnBuscarManual.addEventListener('click', function() {
        cargarPiezas();
    });

    // 4. CARGAR PIEZAS SEGÚN COMBINACIÓN SELECCIONADA
    function cargarPiezas() {
        tbody.innerHTML = '<tr><td colspan="10" class="text-center py-4 text-warning"><i class="fa-solid fa-spinner fa-spin me-2"></i> Consultando piezas del inventario...</td></tr>';
        seccionPiezas.style.display = 'block';
        pantallaEspera.style.display = 'none';

        const idFam  = selFam.value;
        const idProd = selProd.value;
        const idLote = selLote.value;

        // Actualizar valores ocultos para envío
        hiddenFam.value  = idFam;
        hiddenProd.value = idProd;
        hiddenLote.value = idLote;

        // Títulos informativos de alto contraste
        const famText  = selFam.options[selFam.selectedIndex]?.text || '';
        const prodText = selProd.options[selProd.selectedIndex]?.text || '';
        const loteText = selLote.options[selLote.selectedIndex]?.text || '';

        document.getElementById('lblInfoLote').innerHTML = 
            `<span style="color: #94a3b8 !important; font-weight: 700;">Piezas:</span> <span style="color: #ffffff !important; font-weight: 800; font-size: 1.15rem;">${famText}</span>`;

        document.getElementById('lblSubInfoLote').innerHTML = 
            `<span style="color: #38bdf8 !important; font-weight: 700;">${prodText}</span> <span style="color: #fbbf24 !important; font-weight: 900; margin: 0 6px;">➔</span> <span style="color: #34d399 !important; font-weight: 700;">${loteText}</span>`;

        const params = new URLSearchParams();
        if (idFam && idFam !== 'todas') params.append('id_familia', idFam);
        if (idProd && idProd !== 'todos') params.append('id_producto', idProd);
        if (idLote && idLote !== 'todos') params.append('id_lote', idLote);

        const url = `{{ url('/inventario/etiquetas/ajax/piezas') }}?${params.toString()}`;

        fetch(url)
            .then(res => res.json())
            .then(piezas => {
                todasLasPiezas = piezas;
                // Marcar todas por defecto al cargar nuevo filtro
                piezasSeleccionadas = new Set(piezas.map(p => String(p.id_pieza)));
                paginaActual = 1;
                renderizarTabla();
            })
            .catch(err => {
                console.error(err);
                todasLasPiezas = [];
                piezasSeleccionadas.clear();
                if (paginacionContainer) paginacionContainer.style.display = 'none';
                tbody.innerHTML = '<tr><td colspan="10" class="text-center py-4 text-danger">Error al consultar piezas del inventario.</td></tr>';
                actualizarContador();
            });
    }

    // RENDERIZADO DE TABLA PAGINADA
    function renderizarTabla() {
        tbody.innerHTML = '';
        totalBadge.textContent = todasLasPiezas.length;
        if (lblPaginacionTotal) lblPaginacionTotal.textContent = todasLasPiezas.length;

        if (todasLasPiezas.length === 0) {
            if (paginacionContainer) paginacionContainer.style.display = 'none';
            tbody.innerHTML = '<tr><td colspan="10" class="text-center py-4 text-muted">No se encontraron piezas activas con los filtros seleccionados.</td></tr>';
            actualizarContador();
            return;
        }

        if (paginacionContainer) paginacionContainer.style.display = 'flex';

        const porPag = selectPorPagina.value === 'all' ? todasLasPiezas.length : parseInt(selectPorPagina.value);
        const totalPaginas = Math.max(1, Math.ceil(todasLasPiezas.length / porPag));

        if (paginaActual > totalPaginas) paginaActual = totalPaginas;
        if (paginaActual < 1) paginaActual = 1;

        const inicio = (paginaActual - 1) * porPag;
        const fin    = Math.min(inicio + porPag, todasLasPiezas.length);
        const piezasPagina = todasLasPiezas.slice(inicio, fin);

        if (lblPaginacionRango) lblPaginacionRango.textContent = `${inicio + 1} - ${fin}`;
        if (lblPaginaActual) lblPaginaActual.textContent = `Pág. ${paginaActual} de ${totalPaginas}`;

        btnPaginaPrimera.disabled   = (paginaActual === 1);
        btnPaginaAnterior.disabled  = (paginaActual === 1);
        btnPaginaSiguiente.disabled = (paginaActual === totalPaginas);
        btnPaginaUltima.disabled    = (paginaActual === totalPaginas);

        btnPaginaPrimera.style.opacity   = (paginaActual === 1) ? '0.4' : '1';
        btnPaginaAnterior.style.opacity  = (paginaActual === 1) ? '0.4' : '1';
        btnPaginaSiguiente.style.opacity = (paginaActual === totalPaginas) ? '0.4' : '1';
        btnPaginaUltima.style.opacity    = (paginaActual === totalPaginas) ? '0.4' : '1';

        piezasPagina.forEach((pz, idxRel) => {
            const idxReal = inicio + idxRel + 1;
            const tr = document.createElement('tr');
            const mts = parseFloat(pz.cantidad_metros_actual).toFixed(4);
            const lbs = parseFloat(pz.peso_libras_actual).toFixed(2);
            const estado = pz.estado || 'ACTIVA';
            const prodDesc = pz.producto_descripcion || '-';
            const famDesc  = pz.familia_nombre || '-';
            const loteDesc = pz.lote_codigo || '-';
            const isChecked = piezasSeleccionadas.has(String(pz.id_pieza)) ? 'checked' : '';

            tr.innerHTML = `
                <td style="text-align: center;">
                    <input type="checkbox" value="${pz.id_pieza}" class="check-pieza" ${isChecked} style="cursor: pointer; width: 16px; height: 16px;">
                </td>
                <td style="text-align: center; color: #94a3b8;">${idxReal}</td>
                <td class="fw-bold" style="color: #38bdf8;">
                    <i class="fa-solid fa-tag me-1 text-warning"></i> ${pz.codigo}
                </td>
                <td style="font-size: 0.88rem; color: #e2e8f0;">${prodDesc}</td>
                <td><span class="badge" style="background-color: #334155; color: #f8fafc;">${famDesc}</span></td>
                <td><span class="badge" style="background-color: #0f172a; color: #fbbf24; border: 1px solid #374151;">${loteDesc}</span></td>
                <td style="text-align: right; font-weight: 600; color: #fff;">${mts}</td>
                <td style="text-align: right; color: #4ade80;">${lbs}</td>
                <td style="text-align: center;">
                    <span class="badge bg-success" style="font-size: 11px;">${estado}</span>
                </td>
                <td style="text-align: center;">
                    <a href="{{ route('inventario.etiquetas.imprimir') }}?piezas=${pz.id_pieza}&formato=${document.getElementById('formatoImpresion').value}" target="_blank" class="btn btn-sm" style="background-color: #8b5cf6; color: #fff; font-size: 11px; padding: 3px 8px;" title="Imprimir solo esta viñeta">
                        <i class="fa-solid fa-print"></i> Viñeta
                    </a>
                </td>
            `;
            tbody.appendChild(tr);
        });

        // Actualizar estado del checkTodos para la página actual
        const checksPagina = tbody.querySelectorAll('.check-pieza');
        const marcadosPagina = tbody.querySelectorAll('.check-pieza:checked').length;
        checkTodos.checked = (checksPagina.length > 0 && checksPagina.length === marcadosPagina);

        actualizarContador();
        asignarEventosChecks();
    }

    // CHECKBOXES
    function asignarEventosChecks() {
        const checks = tbody.querySelectorAll('.check-pieza');
        checks.forEach(c => {
            c.addEventListener('change', function() {
                if (this.checked) {
                    piezasSeleccionadas.add(String(this.value));
                } else {
                    piezasSeleccionadas.delete(String(this.value));
                }
                const total = checks.length;
                const marcados = tbody.querySelectorAll('.check-pieza:checked').length;
                checkTodos.checked = (total > 0 && total === marcados);
                actualizarContador();
            });
        });
    }

    checkTodos.addEventListener('change', function() {
        const checks = tbody.querySelectorAll('.check-pieza');
        const estado = this.checked;
        checks.forEach(c => {
            c.checked = estado;
            if (estado) {
                piezasSeleccionadas.add(String(c.value));
            } else {
                piezasSeleccionadas.delete(String(c.value));
            }
        });
        actualizarContador();
    });

    function actualizarContador() {
        cantBadge.textContent = piezasSeleccionadas.size;
        btnImprimirSel.disabled = (piezasSeleccionadas.size === 0);
    }

    // INTERCEPTAR ENVÍO: GARANTIZAR QUE SE IMPRIMAN TODAS LAS SELECCIONADAS (AUNQUE ESTÉN EN OTRAS PÁGINAS)
    formImprimir.addEventListener('submit', function(e) {
        if (piezasSeleccionadas.size === 0) {
            e.preventDefault();
            alert('Por favor selecciona al menos una pieza para imprimir.');
            return;
        }

        // Limpiar inputs viejos
        formImprimir.querySelectorAll('input[name="piezas[]"]').forEach(inp => inp.remove());

        // Inyectar todas las seleccionadas
        piezasSeleccionadas.forEach(id => {
            const inp = document.createElement('input');
            inp.type = 'hidden';
            inp.name = 'piezas[]';
            inp.value = id;
            formImprimir.appendChild(inp);
        });
    });

    // EVENTOS DE NAVEGACIÓN DE PÁGINAS
    btnPaginaPrimera.addEventListener('click', function() {
        if (paginaActual !== 1) {
            paginaActual = 1;
            renderizarTabla();
        }
    });

    btnPaginaAnterior.addEventListener('click', function() {
        if (paginaActual > 1) {
            paginaActual--;
            renderizarTabla();
        }
    });

    btnPaginaSiguiente.addEventListener('click', function() {
        const porPag = selectPorPagina.value === 'all' ? todasLasPiezas.length : parseInt(selectPorPagina.value);
        const totalPaginas = Math.ceil(todasLasPiezas.length / porPag);
        if (paginaActual < totalPaginas) {
            paginaActual++;
            renderizarTabla();
        }
    });

    btnPaginaUltima.addEventListener('click', function() {
        const porPag = selectPorPagina.value === 'all' ? todasLasPiezas.length : parseInt(selectPorPagina.value);
        const totalPaginas = Math.ceil(todasLasPiezas.length / porPag);
        if (paginaActual !== totalPaginas) {
            paginaActual = totalPaginas;
            renderizarTabla();
        }
    });

    selectPorPagina.addEventListener('change', function() {
        paginaActual = 1;
        renderizarTabla();
    });

    // BOTÓN IMPRIMIR TODO LO FILTRADO DIRECTAMENTE
    btnImprimirTodo.addEventListener('click', function() {
        const fmt = document.getElementById('formatoImpresion').value;
        const idFam  = selFam.value;
        const idProd = selProd.value;
        const idLote = selLote.value;

        const params = new URLSearchParams();
        params.append('formato', fmt);
        if (idFam) params.append('id_familia', idFam);
        if (idProd) params.append('id_producto', idProd);
        if (idLote) params.append('id_lote', idLote);

        window.open(`{{ route('inventario.etiquetas.imprimir') }}?${params.toString()}`, '_blank');
    });

    // BOTÓN RÁPIDO: CARGAR TODO EL INVENTARIO
    btnCargarTodoDirecto.addEventListener('click', function() {
        selFam.value  = 'todas';
        selProd.value = 'todos';
        selLote.value = 'todos';
        cargarProductos('todas', 'todos');
    });

    // INICIAR AUTOMÁTICAMENTE CARGANDO LA PRIMERA VISTA
    cargarProductos(famInit, prodInit);
});
</script>
@endsection
