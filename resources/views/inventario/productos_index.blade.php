@extends('layouts.app')

@section('content')
<div class="consulta-container">

<style>
    .consulta-container {
        width: 100%;
        padding: 5px 0 20px 0;
    }

    .consulta-header {
        margin-bottom: 20px;
    }

    .consulta-filter-card {
        background: #1e293b;
        border: 1px solid #334155;
        border-radius: 12px;
        padding: 20px 24px;
        margin-bottom: 25px;
        box-shadow: 0 4px 14px rgba(0, 0, 0, 0.2);
    }

    .consulta-filter-grid {
        display: grid;
        grid-template-columns: 1fr 1.3fr 1fr auto;
        gap: 16px;
        align-items: flex-end;
    }

    @media (max-width: 1024px) {
        .consulta-filter-grid {
            grid-template-columns: 1fr 1fr;
        }
    }

    @media (max-width: 640px) {
        .consulta-filter-grid {
            grid-template-columns: 1fr;
        }
    }

    .consulta-col {
        display: flex;
        flex-direction: column;
    }

    .consulta-label {
        color: #38bdf8 !important; /* Celeste vibrante de alta legibilidad en fondo oscuro */
        font-size: 12px !important;
        font-weight: 700 !important;
        letter-spacing: 0.5px;
        margin-bottom: 6px !important;
        display: flex !important;
        align-items: center;
        gap: 6px;
        text-transform: uppercase;
    }

    .consulta-select {
        width: 100%;
        height: 42px;
        background-color: #0f172a !important;
        border: 1px solid #475569 !important;
        border-radius: 8px !important;
        color: #f8fafc !important;
        padding: 8px 12px;
        font-size: 13.5px;
        font-family: Arial, Helvetica, sans-serif !important;
        outline: none;
        transition: border-color 0.2s, box-shadow 0.2s;
        cursor: pointer;
    }

    .consulta-select:focus {
        border-color: #38bdf8 !important;
        box-shadow: 0 0 0 3px rgba(56, 189, 248, 0.25) !important;
    }

    .consulta-select:disabled {
        background-color: #1e293b !important;
        color: #64748b !important;
        border-color: #334155 !important;
        cursor: not-allowed;
    }

    .consulta-select option {
        background-color: #0f172a;
        color: #f8fafc;
    }

    .consulta-btn-col {
        display: flex;
        justify-content: flex-end;
    }

    .consulta-btn {
        height: 42px;
        padding: 0 24px !important;
        background: #2563eb !important;
        color: #ffffff !important;
        border: none !important;
        border-radius: 8px !important;
        font-size: 13.5px !important;
        font-weight: 700 !important;
        letter-spacing: 0.3px;
        display: inline-flex !important;
        align-items: center;
        justify-content: center;
        gap: 8px;
        cursor: pointer;
        transition: all 0.2s ease;
        white-space: nowrap;
    }

    .consulta-btn:hover {
        background: #1d4ed8 !important;
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(37, 99, 235, 0.4);
    }
</style>

    {{-- =========================
         TÍTULO Y ENCABEZADO
    ========================== --}}
    <div class="consulta-header">
        <h2 class="erp-title" style="margin-bottom: 6px;">
            <i class="fa-solid fa-boxes-stacked"></i> Consulta de Inventario
        </h2>
        <p style="color: #64748b; font-size: 14px; margin: 0;">
            Seleccione una calidad o producto para consultar existencias, lotes y piezas disponibles.
        </p>
    </div>

    {{-- =========================
         FILTROS DE BÚSQUEDA
    ========================== --}}
    <div class="consulta-filter-card">
        <div class="consulta-filter-grid">
            <div class="consulta-col">
                <label class="consulta-label">
                    <i class="fa-solid fa-sitemap"></i> 1. FAMILIA / CALIDAD
                </label>
                <select id="selectFamilia" class="consulta-select" onchange="cargarProductos(this.value)">
                    <option value="">-- Seleccione Calidad --</option>
                    @foreach($familias as $f)
                        <option value="{{ $f->id_familia }}">{{ $f->nombre }}</option>
                    @endforeach
                </select>
            </div>

            <div class="consulta-col">
                <label class="consulta-label">
                    <i class="fa-solid fa-box"></i> 2. PRODUCTO
                </label>
                <select id="selectProducto" class="consulta-select" disabled onchange="cargarPiezas(this.value)">
                    <option value="">-- Primero elija familia --</option>
                </select>
            </div>

            <div class="consulta-col">
                <label class="consulta-label">
                    <i class="fa-solid fa-barcode"></i> 3. PIEZA (OPCIONAL)
                </label>
                <select id="selectPieza" class="consulta-select" disabled>
                    <option value="">-- Todas las piezas --</option>
                </select>
            </div>

            <div class="consulta-col consulta-btn-col">
                <button type="button" class="btn-primary consulta-btn" onclick="realizarConsulta()">
                    <i class="fa-solid fa-magnifying-glass"></i> CONSULTAR
                </button>
            </div>
        </div>
    </div>

    {{-- =========================
         LISTA DE PRODUCTOS
    ========================== --}}
    <table class="table table-bordered table-hover table-sm">
        <thead class="table-light">
            <tr>
                <th style="width: 15%">Código</th>
                <th>Descripción</th>
                <th style="width: 15%">Unidad</th>
                <th style="width: 15%">Metros disponibles</th>
                <th style="width: 15%">libras disponibles</th>
                <th style="width: 15%">Acción</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($productos as $producto)
                {{-- 🟢 LÓGICA DE MEDIDAS --}}
                @php
                    $mm   = floatval($producto->milimetros ?? 0);
                    $plg  = $producto->pulgadas;
                    $textoMedidas = "";

                    if ($mm > 0 || ($plg && $plg != '-')) {
                        $txtMM  = $mm > 0 ? "$mm mm" : '';
                        $txtPLG = ($plg && $plg != '-') ? "$plg plg" : '';
                        $sep    = ($txtMM && $txtPLG) ? ' / ' : '';

                        $textoMedidas = "($txtMM $sep $txtPLG)";
                    }
                @endphp

                <tr>
                    <td style="font-weight: bold;">{{ $producto->codigo }}</td>

                    <td>
                        {{ $producto->descripcion }}

                        {{-- Muestra las medidas si existen --}}
                        @if($textoMedidas)
                            <span style="color: #d97706; font-weight: bold; font-size: 0.9em; margin-left: 5px;">
                                {{ $textoMedidas }}
                            </span>
                        @endif
                    </td>

                    <td>{{ $producto->unidad_medida_longitud }}</td>
                     <td>
                        {{-- OJO: Aquí dice 'Metros disponibles' en el encabezado,
                             pero estás mostrando 'peso_total_libras'.
                             Si quisieras metros sería: $producto->stock_metros --}}
                        {{ number_format($producto->stock_metros, 2) }}
                    </td>

                    <td>
                        {{-- OJO: Aquí dice 'Metros disponibles' en el encabezado,
                             pero estás mostrando 'peso_total_libras'.
                             Si quisieras metros sería: $producto->stock_metros --}}
                        {{ number_format($producto->peso_total_libras, 2) }}
                    </td>

                    <td class="text-center">
                        <a href="{{ route('inventario.inventario.producto', $producto->id_producto) }}"
                           class="btn btn-sm btn-primary">
                            <i class="fas fa-eye"></i> Ver lotes
                        </a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center text-muted">
                        No hay productos registrados.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

</div>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $(document).ready(function() {
    // Inicializamos Select2 para que se vea pro
    $('.select2-filtro').select2({
        theme: 'bootstrap-5',
        width: '100%'
    });
});

function cargarProductos(idFamilia) {
    const selProd = document.getElementById('selectProducto');
    selProd.innerHTML = '<option value="">Cargando...</option>';

    fetch("{{ route('inventario.productos.familia', ['id_familia' => ':id']) }}".replace(':id', idFamilia))
        .then(res => res.json())
        .then(data => {
            selProd.innerHTML = '<option value="">-- Seleccione Producto --</option>';
            data.forEach(p => {
                const mm = p.milimetros ? p.milimetros + 'mm' : '';
                const plg = p.pulgadas ? p.pulgadas + '"' : '';
                const texto = `${p.descripcion} (${mm} / ${plg})`; // Agregamos medidas aquí

                selProd.innerHTML += `<option value="${p.id_producto}">${texto}</option>`;
            });
            selProd.disabled = false;
        });
}

// FUNCIÓN A: Cargar el selector de piezas (Selector #3)
function cargarPiezas(idProducto) {
    const selPieza = document.getElementById('selectPieza');
    selPieza.innerHTML = '<option value="">⏳ Cargando...</option>';
    selPieza.disabled = true;

    if (!idProducto) {
        selPieza.innerHTML = '<option value="">-- Todas las piezas --</option>';
        return;
    }

    const url = "{{ route('consulta.piezas.medidas', ['id_producto' => ':id']) }}".replace(':id', idProducto);
    fetch(url)
        .then(res => res.json())
        .then(data => {
            selPieza.innerHTML = '<option value="">-- Todas las piezas --</option>';
            data.forEach(p => {
                const mm = p.milimetros ? parseFloat(p.milimetros).toFixed(2) + ' mm' : '0.00 mm';
                const pulg = p.pulgadas ? p.pulgadas + '"' : '0"';

                // ✨ Ahora usamos los nombres exactos: p.id_pieza, p.codigo y p.cantidad_metros_actual
                selPieza.innerHTML += `<option value="${p.id_pieza}">${p.codigo} [${p.cantidad_metros_actual} mts] (${mm} / ${pulg})</option>`;
            });
            selPieza.disabled = false;
        });
}

// FUNCIÓN B: El botón CONSULTAR (Muestra el resumen)
function realizarConsulta() {
    const idFam = document.getElementById('selectFamilia').value; // Ajustá al ID real de tu combo
    const idProd = document.getElementById('selectProducto').value; // Ajustá al ID real de tu combo
    const tablaBody = document.querySelector('table tbody');

    tablaBody.innerHTML = '<tr><td colspan="6" class="text-center">🔍 Consultando Acerosal...</td></tr>';

    // Asegurate de que esta ruta esté en tu web.php
    const url = `{{ route('inventario.filtrar') }}?id_familia=${idFam}&id_producto=${idProd}`;

    fetch(url)
        .then(res => res.json())
        .then(data => {
            tablaBody.innerHTML = '';

            if (data.length === 0) {
                tablaBody.innerHTML = '<tr><td colspan="6" class="text-center">No hay productos con esos filtros.</td></tr>';
                return;
            }

            data.forEach(p => {
                // Lógica de medidas idéntica a tu @php del Blade
                let mm = parseFloat(p.milimetros || 0);
                let plg = p.pulgadas;
                let textoMedidas = "";
                if (mm > 0 || (plg && plg !== '-')) {
                    let txtMM = mm > 0 ? `${mm} mm` : '';
                    let txtPLG = (plg && plg !== '-') ? `${plg} plg` : '';
                    let sep = (txtMM && txtPLG) ? ' / ' : '';
                    textoMedidas = `<span style="color: #d97706; font-weight: bold; font-size: 0.9em; margin-left: 5px;">(${txtMM}${sep}${txtPLG})</span>`;
                }

                // Generamos la URL del botón "Ver lotes" dinámicamente
                const urlVer = `{{ url('/inventario/producto') }}/${p.id_producto}`;

                tablaBody.innerHTML += `
                    <tr>
                        <td class="fw-bold">${p.codigo}</td>
                        <td>${p.descripcion} ${textoMedidas}</td>
                        <td>${p.unidad_medida_longitud}</td>
                        <td class="text-end">${parseFloat(p.stock_metros).toFixed(2)}</td>
                        <td class="text-end">${parseFloat(p.peso_total_libras).toFixed(2)}</td>
                        <td class="text-center">
                            <a href="${urlVer}" class="btn btn-sm btn-primary">
                                <i class="fas fa-eye"></i> Ver lotes
                            </a>
                        </td>
                    </tr>`;
            });
        })
        .catch(err => {
            console.error(err);
            tablaBody.innerHTML = '<tr><td colspan="6" class="text-center text-danger">Error al conectar con el servidor.</td></tr>';
        });
}


function verLotesIndividuales(id, nombre) {
    const tablaBody = document.querySelector('table tbody');
    tablaBody.innerHTML = `<tr><td colspan="6" class="text-center">📂 Desglosando lotes de: ${nombre}...</td></tr>`;

    // Usamos el ID del producto para traer sus piezas
    fetch(`{{ url('/inventario/consulta/lotes') }}/${id}`)
        .then(res => res.json())
        .then(data => {
            tablaBody.innerHTML = '';
            data.forEach(p => {
                tablaBody.innerHTML += `
                    <tr class="table-info">
                        <td>${p.codigo}</td>
                        <td colspan="2"><i class="fa fa-arrow-right"></i> Lote individual</td>
                        <td class="text-end">${parseFloat(p.m).toFixed(2)}</td>
                        <td class="text-end">${parseFloat(p.lb).toFixed(2)}</td>
                        <td class="text-center"><span class="badge badge-success">Disponible</span></td>
                    </tr>`;
            });
            tablaBody.innerHTML += `<tr><td colspan="6" class="text-center"><button class="btn btn-secondary btn-sm" onclick="realizarConsulta()">⬅ Volver al Resumen</button></td></tr>`;
        });
}

function verDetalleLotes(id, nombreProducto) {
    const tablaBody = document.querySelector('table tbody');
    tablaBody.innerHTML = `<tr><td colspan="6" class="text-center">📂 Desglosando lotes de: ${nombreProducto}...</td></tr>`;

    // Ruta hacia tu método de detalles
    fetch(`{{ url('/inventario/consulta/lotes') }}/${id}`)
        .then(res => res.json())
        .then(data => {
            tablaBody.innerHTML = '';

            if (data.length === 0) {
                tablaBody.innerHTML = '<tr><td colspan="6" class="text-center">No hay lotes activos para este producto.</td></tr>';
            } else {
                data.forEach(p => {
                    tablaBody.innerHTML += `
                        <tr class="table-info">
                            <td>${p.codigo_lote}</td>
                            <td>
                                <strong>${p.descripcion}</strong><br>
                                <small class="text-warning">(${p.milimetros} mm / ${p.pulgadas})</small>
                            </td>
                            <td>${p.unidad}</td>
                            <td class="text-end">${parseFloat(p.m).toFixed(2)}</td>
                            <td class="text-end">${parseFloat(p.lb).toFixed(2)}</td>
                            <td class="text-center"><span class="badge badge-success">Disponible</span></td>
                        </tr>`;
                });
            }
            // Botón para volver al resumen sumado
            tablaBody.innerHTML += `<tr><td colspan="6" class="text-center"><button class="btn btn-dark btn-sm" onclick="realizarConsulta()">⬅ VOLVER AL RESUMEN</button></td></tr>`;
        });
}

function verDetalleEnOtraPagina(idProducto) {
    const tablaBody = document.querySelector('table tbody');
    tablaBody.innerHTML = '<tr><td colspan="6" class="text-center">📂 Cargando piezas detalladas...</td></tr>';

    fetch("{{ url('/inventario/consulta/lotes') }}/" + idProducto)
        .then(res => res.json())
        .then(data => {
            tablaBody.innerHTML = '';
            data.forEach(p => {
                // Ahora usamos los datos reales del JOIN
                const metros = parseFloat(p.m).toFixed(2);
                const libras = parseFloat(p.lb).toFixed(2);

                tablaBody.innerHTML += `
                    <tr class="table-info">
                        <td>${p.codigo}</td>
                        <td>
                            <strong>${p.descripcion}</strong><br>
                            <span style="color: #f59e0b; font-weight: bold; font-size: 0.85em;">
                                (${p.milimetros} mm / ${p.pulgadas})
                            </span>
                        </td>
                        <td>${p.unidad}</td>
                        <td class="text-end">${metros}</td>
                        <td class="text-end">${libras}</td>
                        <td class="text-center">
                            <span class="badge badge-success">Disponible</span>
                        </td>
                    </tr>`;
            });
            // Botón para regresar al resumen (el que ya te funciona)
            tablaBody.innerHTML += `<tr><td colspan="6" class="text-center"><button class="btn btn-dark btn-sm" onclick="realizarConsulta()">⬅ VOLVER AL RESUMEN</button></td></tr>`;
        })
        .catch(err => {
            tablaBody.innerHTML = '<tr><td colspan="6" class="text-center text-danger">Error al cargar piezas.</td></tr>';
        });
}
</script>
@endsection
