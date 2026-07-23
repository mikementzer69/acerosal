@extends('layouts.app')

@section('content')

<style>
    /* === ESTILOS DARK MODE === */
    .dark-container {
        background-color: #1a202c;
        min-height: 100vh;
        padding: 40px 20px;
        color: #e2e8f0;
        display: flex;
        justify-content: center;
        align-items: flex-start;
    }

    .card-dark {
        background-color: #2d3748;
        border: 1px solid #4a5568;
        border-radius: 12px;
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.5);
        width: 100%;
        max-width: 1050px; /* Un poco más ancho para la nueva columna */
    }

    .card-header-dark {
        background: linear-gradient(90deg, #2b6cb0 0%, #2c5282 100%);
        color: white;
        padding: 20px 30px;
        border-radius: 12px 12px 0 0;
        font-weight: 700;
        letter-spacing: 0.5px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-bottom: 1px solid #4a5568;
    }

    .label-dark {
        color: #a0aec0;
        font-size: 0.8rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        font-weight: 700;
        margin-bottom: 8px;
        display: block;
    }

    .input-dark, .select-dark {
        background-color: #171923 !important;
        border: 1px solid #4a5568 !important;
        color: #fff !important;
        border-radius: 6px;
        padding: 12px;
        width: 100%;
        transition: all 0.2s ease;
    }

    .input-dark:focus, .select-dark:focus {
        border-color: #63b3ed !important;
        outline: none;
        box-shadow: 0 0 0 3px rgba(99, 179, 237, 0.15) !important;
        background-color: #1a202c !important;
    }

    /* Hack Autocomplete */
    input:-webkit-autofill,
    input:-webkit-autofill:hover,
    input:-webkit-autofill:focus,
    input:-webkit-autofill:active {
        -webkit-box-shadow: 0 0 0 30px #171923 inset !important;
        -webkit-text-fill-color: white !important;
    }

    /* Tabla */
    .table-dark-custom {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0 8px;
    }

    .table-dark-custom th {
        color: #cbd5e0;
        font-size: 0.85rem;
        text-transform: uppercase;
        padding: 10px 15px;
        border-bottom: 1px solid #4a5568;
        font-weight: 600;
        opacity: 0.8;
    }

    .table-dark-custom td {
        background-color: #232936;
        padding: 8px;
        border: 1px solid #2d3748;
        vertical-align: middle;
    }

    .table-dark-custom tr td:first-child { border-radius: 6px 0 0 6px; }
    .table-dark-custom tr td:last-child { border-radius: 0 6px 6px 0; }

    /* Botones */
    .btn-action {
        border: none;
        padding: 8px 12px;
        border-radius: 6px;
        cursor: pointer;
        transition: transform 0.1s;
    }
    .btn-action:active { transform: scale(0.95); }
    .btn-delete { background-color: #fc8181; color: #742a2a; }
    .btn-delete:hover { background-color: #f56565; color: white; }

    .btn-add {
        background-color: #38a169;
        color: white;
        width: 100%;
        padding: 12px;
        margin-top: 15px;
        font-weight: bold;
        border: none;
        border-radius: 6px;
        transition: background 0.3s;
        text-transform: uppercase;
        font-size: 0.9rem;
    }
    .btn-add:hover { background-color: #2f855a; }

    .btn-save {
        background: linear-gradient(to right, #3182ce, #2b6cb0);
        color: white;
        padding: 14px 40px;
        font-size: 1rem;
        font-weight: bold;
        border: none;
        border-radius: 8px;
        box-shadow: 0 4px 6px rgba(0,0,0,0.3);
        cursor: pointer;
        letter-spacing: 1px;
    }
    .btn-save:hover { filter: brightness(1.1); transform: translateY(-1px); }

    /* Estilo para opción seleccionada */
    option { background-color: #1a202c; color: white; }
</style>

<div class="dark-container">

    {{-- Contenedor de Alertas JS --}}
    <div id="alerta-container"></div>

    <div class="card-dark">
        <div class="card-header-dark">
            <span><i class="fas fa-boxes"></i> CARGA INICIAL DE INVENTARIO</span>
            <small style="opacity: 0.8;">Modo: Ingreso Directo</small>
        </div>

        <div class="card-body" style="padding: 25px;">

            {{-- FORMULARIO --}}
            <form id="formInicial" method="POST" action="{{ route('inventario.inicial.store') }}">
                @csrf
            <div class="row">
                {{-- 1. FAMILIA (EL AMARRE) --}}
                <div class="col-md-3 mb-3">
                    <label class="label-dark">1. Calidad *</label>
                    <select id="id_familia" class="select-dark" onchange="cargarProductos(this.value)" required>
                        <option value="">-- Seleccione Calidad --</option>
                        @foreach($familias as $f)
                            <option value="{{ $f->id_familia }}">{{ $f->nombre }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-4 mb-3">
                    <label class="label-dark">2. Producto *</label>
                    <select id="id_producto" name="id_producto" class="select-dark" required disabled>
                        <option value="">-- Seleccione Calidad Primero --</option>
                    </select>
                    <small style="color: #63b3ed; font-size: 0.8rem; margin-top: 5px; display:block;">
                        <i class="fas fa-info-circle"></i> Factor: <span id="info-factor">0.00</span> LB/MTS
                    </small>
                </div>
                {{-- FILA 1: DATOS GENERALES --}}
                    {{-- PRODUCTO (CON MEDIDAS) --}}


                    {{-- CÓDIGO LOTE --}}
                <div class="col-md-3 mb-3">
                        <label class="label-dark">3. Código de Lote *</label>
                        <input type="text" id="codigo_lote" name="codigo_lote"
                            class="input-dark"
                            value="{{ $siguienteCodigo }}"
                            placeholder="Ej: INI-001" required>
                    </div>

                    {{-- FECHA --}}
                    <div class="col-md-2 mb-3">
                            <label class="label-dark">4. Fecha *</label>
                            <input type="date" name="fecha" class="input-dark" value="{{ date('Y-m-d') }}" required>
                     </div>

                <hr style="border-color: #4a5568; margin: 20px 0;">

                {{-- SECCIÓN DETALLE --}}
                <div class="row">
                    <div class="col-12">
                        <h5 style="color: #e2e8f0; margin-bottom: 15px;">Detalle de Piezas Físicas</h5>

                        <table class="table-dark-custom">
                            <thead>
                                <tr style="background-color: #2b6cb0; color: white;">
                                    <th width="5%" class="text-center">#</th>
                                    {{-- 🔥 COLUMNA NUEVA: CANTIDAD --}}
                                    <th width="15%" style="color:#fbbf24;">CANTIDAD</th>
                                    <th width="35%">LONGITUD (Metros)</th>
                                    <th width="35%">PESO TOTAL (Libras)</th>
                                    <th width="10%" class="text-center">ACCIÓN</th>
                                </tr>
                            </thead>
                            <tbody id="cuerpoPiezas">
                                {{-- JS inyectará filas aquí --}}
                            </tbody>
                        </table>

                        <button type="button" class="btn-add" onclick="agregarFila()">
                            <i class="fas fa-plus"></i> Agregar Nuevo Grupo (Enter)
                        </button>
                    </div>
                </div>

                {{-- BOTONERA --}}
                <div class="mt-4 text-right">
                    <a href="{{ url()->previous() }}" class="btn btn-secondary btn-lg mr-2" style="margin-right: 10px;">
                        <i class="fas fa-times-circle"></i> Cancelar
                    </a>
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="fas fa-save"></i> Procesar Inventario
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>

{{-- JAVASCRIPT MAGISTRAL --}}
<script>
let contadorFilas = 0;
let relacionActual = 0;

document.addEventListener('DOMContentLoaded', function() {
    agregarFila(); // Iniciar con una fila

    // Evento cambio de producto
    const selectProducto = document.getElementById('id_producto');
    selectProducto.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        relacionActual = parseFloat(selectedOption.getAttribute('data-relacion')) || 0;

        // Actualizar etiqueta visual
        document.getElementById('info-factor').textContent = relacionActual.toFixed(4);

        // Recalcular todo si ya había datos
        recalcularTodaLaTabla();
    });
});

function agregarFila() {
    contadorFilas++;
    // Nota: Quitamos los 'name' de los inputs para que NO se envíen automáticamente.
    // Nosotros construiremos los datos manualmente en el submit (Explosión).
    const html = `
        <tr id="fila_${contadorFilas}">
            <td class="text-center" style="font-weight:bold; color: #63b3ed;">${contadorFilas}</td>

            <td>
                <input type="number"
                       class="input-dark input-cantidad"
                       value="1" min="1" step="1"
                       oninput="calcularPeso(this)"
                       style="text-align: center; color: #fbbf24; font-weight: bold;">
            </td>

            <td>
                <input type="number" step="0.01"
                       class="input-dark input-metros"
                       oninput="calcularPeso(this)"
                       placeholder="0.00" style="text-align: right;">
            </td>

            <td>
                <input type="text"
                       class="input-dark input-libras-visual"
                       readonly
                       placeholder="0.00"
                       style="background-color: #2d3748; cursor: not-allowed; text-align: right;">
            </td>

            <td class="text-center">
                <button type="button" class="btn-action btn-delete" onclick="eliminarFila(${contadorFilas})">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        </tr>
    `;
    document.getElementById('cuerpoPiezas').insertAdjacentHTML('beforeend', html);

    // Auto-focus al input de metros (más cómodo)
    const nuevasFilas = document.querySelectorAll('.input-metros');
    if(nuevasFilas.length > 0) nuevasFilas[nuevasFilas.length - 1].focus();
}

function calcularPeso(elemento) {
    const fila = elemento.closest('tr');

    const inputCant = fila.querySelector('.input-cantidad');
    const inputMetros = fila.querySelector('.input-metros');
    const inputLibrasVisual = fila.querySelector('.input-libras-visual');

    const cantidad = parseInt(inputCant.value) || 1;
    const metros = parseFloat(inputMetros.value) || 0;

    // Cálculo visual: (Metros * Factor * Cantidad)
    // Para que el usuario sepa cuánto pesa TODO el grupo que está metiendo
    if (relacionActual > 0) {
        const pesoUnitario = metros * relacionActual;
        const pesoTotalGrupo = pesoUnitario * cantidad;

        inputLibrasVisual.value = pesoTotalGrupo.toFixed(2);
    } else {
        inputLibrasVisual.value = '';
    }
}

function recalcularTodaLaTabla() {
    document.querySelectorAll('.input-metros').forEach(input => {
        calcularPeso(input);
    });
}

function eliminarFila(id) {
    const cuerpo = document.getElementById('cuerpoPiezas');
    if (cuerpo.querySelectorAll('tr').length > 1) {
        document.getElementById('fila_' + id).remove();
        reindexar();
    } else {
        mostrarAlerta('error', 'Debe haber al menos una línea.');
    }
}

function reindexar() {
    let filas = document.querySelectorAll('#cuerpoPiezas tr');
    let i = 1;
    filas.forEach(fila => {
        fila.querySelector('td').textContent = i;
        i++;
    });
    contadorFilas = i - 1;
}

// Navegación con Enter
document.addEventListener('keydown', function(e) {
    if (e.key === 'Enter') {
        const activo = document.activeElement;
        if (activo.classList.contains('input-metros') || activo.classList.contains('input-cantidad')) {
            e.preventDefault();
            agregarFila();
        }
    }
});

// === 🎩 MAGIA: EL SUBMIT CON EXPLOSIÓN DE DATOS ===
document.getElementById('formInicial').addEventListener('submit', function(e) {
    e.preventDefault(); // Detenemos el envío normal

    // Validaciones básicas
    const prod = document.getElementById('id_producto').value;
    if(!prod) { mostrarAlerta('error', 'Seleccione un producto'); return; }

    const boton = this.querySelector('button[type="submit"]');
    boton.disabled = true;
    boton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Procesando...';

    // 1. Creamos un FormData MANUAL
    const formData = new FormData();

    // 2. Agregamos los campos fijos (Header)
    formData.append('_token', document.querySelector('input[name="_token"]').value);
    formData.append('id_producto', prod);
    formData.append('codigo_lote', document.getElementById('codigo_lote').value);
    formData.append('fecha', document.querySelector('input[name="fecha"]').value);

    // 3. LA EXPLOSIÓN: Recorremos las filas visuales
    const filas = document.querySelectorAll('#cuerpoPiezas tr');
    let contadorReal = 0;

    filas.forEach(fila => {
        const qty = parseInt(fila.querySelector('.input-cantidad').value) || 1;
        const mts = parseFloat(fila.querySelector('.input-metros').value) || 0;

        if (mts > 0) {
            // Calculamos peso UNITARIO (Para la base de datos)
            // Ojo: Enviamos el peso de 1 sola pieza, no del grupo entero.
            const pesoUnitario = (mts * relacionActual).toFixed(4);

            // Bucle mágico: Agregamos N veces los datos al FormData
            for (let i = 0; i < qty; i++) {
                formData.append('metros[]', mts);
                formData.append('libras[]', pesoUnitario);
                contadorReal++;
            }
        }
    });

    if (contadorReal === 0) {
        mostrarAlerta('error', 'Ingrese al menos una cantidad válida.');
        boton.disabled = false;
        boton.innerHTML = '<i class="fas fa-save"></i> Procesar Inventario';
        return;
    }

    // 4. Enviamos el FormData manual (Que ahora tiene 50 filas si pusiste 50)
    fetch(this.action, {
        method: 'POST',
        body: formData,
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(res => res.json())
    .then(data => {
        if(data.status === 'success') {
            mostrarAlerta('success', 'Inventario guardado: ' + contadorReal + ' piezas creadas.');
            setTimeout(() => {
                window.location.reload();
            }, 1500);
        } else {
            mostrarAlerta('error', data.message || 'Error desconocido');
            boton.disabled = false;
            boton.innerHTML = '<i class="fas fa-save"></i> Procesar Inventario';
        }
    })
    .catch(err => {
        console.error(err);
        mostrarAlerta('error', 'Error de conexión');
        boton.disabled = false;
        boton.innerHTML = '<i class="fas fa-save"></i> Procesar Inventario';
    });
});

function mostrarAlerta(tipo, msj) {
    const color = tipo === 'success' ? '#48bb78' : '#f56565';
    const html = `
        <div style="background-color: ${color}; color: white; padding: 15px; border-radius: 8px; margin-bottom: 20px; box-shadow: 0 4px 6px rgba(0,0,0,0.2);">
            <strong>${tipo === 'success' ? 'ÉXITO' : 'ERROR'}:</strong> ${msj}
        </div>
    `;
    const container = document.getElementById('alerta-container');
    container.innerHTML = html;
    window.scrollTo({ top: 0, behavior: 'smooth' });
    setTimeout(() => { container.innerHTML = ''; }, 5000);
}


// --- NUEVA FUNCIÓN: CARGAR PRODUCTOS EN CASCADA ---
/**
 * Función magistral para cargar productos por familia
 * Replicando la lógica de medidas (mm / plg) de Acerosal
 */
function cargarProductos(idFamilia) {
    const selectProd = document.getElementById('id_producto');
    const factorTxt = document.getElementById('info-factor');

    if (!idFamilia) {
        selectProd.innerHTML = '<option value="">-- Seleccione Calidad Primero --</option>';
        selectProd.disabled = true;
        return;
    }

    selectProd.innerHTML = '<option value="">Cargando material...</option>';
    selectProd.disabled = true;

    // 🔮 RUTA CONFIRMADA POR TU TERMINAL
    fetch("{{ url('inventario/ajustes/inventario/inicial/productos') }}/" + idFamilia)
        .then(response => {
            if (!response.ok) throw new Error('Error ' + response.status);
            return response.json();
        })
        .then(productos => {
            // Limpiamos el selector
            selectProd.innerHTML = '<option value="">-- Seleccione Producto --</option>';

            productos.forEach(p => {
                // 🛠️ LÓGICA DE MEDIDAS (mm y plg)
                const mm = parseFloat(p.milimetros ?? 0);
                const plg = p.pulgadas;
                let txtMedidas = "";

                if (mm > 0 || (plg && plg !== '-' && plg !== '')) {
                    const tMM = mm > 0 ? `${mm} mm` : '';
                    const tPLG = (plg && plg !== '-' && plg !== '') ? `${plg} plg` : '';
                    const sep = (tMM && tPLG) ? ' / ' : '';
                    txtMedidas = ` (${tMM}${sep}${tPLG})`;
                }

                // Creamos la opción físicamente
                const option = document.createElement('option');
                option.value = p.id_producto;
                // Importante: Guardamos el factor de peso para que tu tabla calcule bien
                option.setAttribute('data-relacion', p.peso_lb_mts);
                option.setAttribute('data-codigo', p.codigo);
                option.textContent = `${p.descripcion}${txtMedidas} (Cod: ${p.codigo})`;

                selectProd.appendChild(option);
            });

            selectProd.disabled = false;
        })
        .catch(error => {
            console.error('Error en el amarre:', error);
            // Si sale 404 aquí, revisá que no falte un prefijo en web.php
            alert("No se pudieron cargar los productos. Revisá la consola.");
        });
}
// Actualizamos tu listener original para que funcione con el nuevo select dinámico
document.addEventListener('DOMContentLoaded', function() {
    if (document.querySelectorAll('#cuerpoPiezas tr').length === 0) {
        agregarFila();
    }

    const selectProducto = document.getElementById('id_producto');
    selectProducto.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        // Validamos que exista la opción seleccionada (evita errores al limpiar el select)
        if (selectedOption && selectedOption.value) {
            relacionActual = parseFloat(selectedOption.getAttribute('data-relacion')) || 0;
            document.getElementById('info-factor').textContent = relacionActual.toFixed(4);
            recalcularTodaLaTabla();
        }
    });
});

</script>

@endsection
