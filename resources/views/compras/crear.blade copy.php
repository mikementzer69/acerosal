@extends('layouts.app')

@section('content')



{{-- Usamos la clase form-container para limitar el ancho y centrarlo --}}
<div class="form-container">
    <style>
        /* Aplicar a inputs, selects y áreas de texto dentro del contenedor del formulario */
        .form-container input,
        .form-container select,
        .form-container textarea,
        .form-control {
            background-color: #1f2937 !important;
            color: #ffffff !important;
            border: 1px solid #374151 !important;
        }

        /* Estilo para las opciones dentro de los select (muy importante en Chrome/Edge) */
        .form-container select option {
            background-color: #1f2937 !important;
            color: white !important;
        }

        /* Quitar el fondo blanco que pone el navegador al autocompletar */
        input:-webkit-autofill,
        input:-webkit-autofill:hover,
        input:-webkit-autofill:focus {
            -webkit-text-fill-color: white !important;
            -webkit-box-shadow: 0 0 0px 1000px #1f2937 inset !important;
            transition: background-color 5000s ease-in-out 0s;
        }
    </style>
    <h2 class="form-title">
        <i class="fa-solid fa-cart-shopping"></i> Registrar Nueva Compra
    </h2>

    @if(session('error'))
        <div class="form-alert form-error">
            {{ session('error') }}
        </div>
    @endif

    {{-- Variables JS necesarias --}}
    <script>
        window.urlProductosPorFamilia = "{{ url('productos/por-familia') }}";
    </script>

    <form action="{{ route('compras.store') }}" method="POST" id="formCompra" class="erp-form">
        @csrf

        {{-- ============================================================
             SECCIÓN 1: DATOS GENERALES
           ============================================================ --}}
        <h5 style="color: #aaa; border-bottom: 1px solid #444; margin-bottom: 20px; padding-bottom: 5px; font-size: 0.9rem; text-transform: uppercase; letter-spacing: 1px;">
            Datos Generales
        </h5>

        {{-- EMPRESA (Solo lectura) --}}
        <div style="margin-bottom: 20px;">
            <label style="display:block; margin-bottom: 8px; color: #ddd;">Empresa</label>
            <input type="text" value="{{ session('nombreEmpresa') }}" readonly
                   style="width: 100%; padding: 10px; background-color: #4a5568 !important; color: #fff !important; font-weight: bold; border: 1px solid #2d3748; border-radius: 4px;">
        </div>

        {{-- FILA 1: PROVEEDOR | FACTURA --}}
        <div style="display: flex; gap: 30px; margin-bottom: 20px; flex-wrap: wrap;">
            {{-- Proveedor --}}
            <div style="flex: 1; min-width: 250px;">
                <label for="id_proveedor" style="display:block; margin-bottom: 8px; color: #ddd;">Proveedor *</label>
                <select name="id_proveedor" id="id_proveedor" required
                        style="width: 100%; height: 45px; padding: 5px 10px; background-color: #111; color: #fff; border: 1px solid #444; border-radius: 4px;">
                    <option value="">Seleccione proveedor</option>
                    @foreach($proveedores as $prov)
                        <option value="{{ $prov->id_proveedor }}">{{ $prov->nombre }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Factura --}}
            <div style="flex: 1; min-width: 250px;">
                <label for="numero_factura" style="display:block; margin-bottom: 8px; color: #ddd;">Número de factura *</label>
                <input type="text" name="numero_factura" id="numero_factura" required
                       style="width: 100%; padding: 10px; background-color: #111; color: #fff; border: 1px solid #444; border-radius: 4px;">
            </div>
        </div>

        {{-- FILA 2: FECHA INGRESO | FECHA EMISIÓN | MONEDA | TASA --}}
        <div style="display: flex; gap: 20px; margin-bottom: 20px; flex-wrap: wrap;">
            <div style="flex: 1; min-width: 150px;">
                <label for="fecha_ingreso" style="display:block; margin-bottom: 8px; color: #ddd;">Fecha Ingreso *</label>
                <input type="date" name="fecha_ingreso" id="fecha_ingreso" value="{{ date('Y-m-d') }}" required
                    style="width: 100%; padding: 10px; background-color: #111; color: #fff; border: 1px solid #444; border-radius: 4px;">
            </div>

            <div style="flex: 1; min-width: 150px;">
                <label for="fecha_emision_factura" style="display:block; margin-bottom: 8px; color: #ddd;">Fecha Emisión *</label>
                <input type="date" name="fecha_emision_factura" id="fecha_emision_factura" required
                    style="width: 100%; padding: 10px; background-color: #111; color: #fff; border: 1px solid #444; border-radius: 4px;">
            </div>

            {{-- ✨ NUEVO: SELECTOR DE MONEDA ✨ --}}
            {{-- ✨ RESTAURAMOS EL GATILLO EN EL BLADE ✨ --}}
{{-- ✨ RESTAURAMOS EL GATILLO EN EL BLADE ✨ --}}
            <div style="flex: 1; min-width: 180px;">
                <label style="display:block; margin-bottom: 8px; color: #ddd;">Moneda Factura</label>
                <div style="display: flex; gap: 15px; background: #111; padding: 10px; border: 1px solid #444; border-radius: 4px; height: 45px; align-items: center;">
                    <label style="color: #fff; cursor: pointer; font-size: 0.9em;">
                        {{-- Añadimos el onchange aquí --}}
                        <input type="radio" name="moneda" value="EUR" onchange="gestionarMoneda(this.value)"> EUR
                    </label>
                    <label style="color: #fff; cursor: pointer; font-size: 0.9em;">
                        <input type="radio" name="moneda" value="USD" checked onchange="gestionarMoneda(this.value)"> USD
                    </label>
                </div>
            </div>            <div style="flex: 1; min-width: 100px;">
                <label for="tasa_cambio" style="display:block; margin-bottom: 8px; color: #ddd;">Tasa Cambio *</label>
                <input type="number" step="0.0001" value="1.0000" name="tasa_cambio" id="tasa_cambio" readonly
                    style="width: 100%; padding: 10px; background-color: #333; color: #aaa; border: 1px solid #444; border-radius: 4px; font-weight: bold;">
            </div>
        </div>
        {{-- ============================================================
             SECCIÓN 2: COSTOS ADICIONALES
           ============================================================ --}}
        {{-- ============================================================
             SECCIÓN 2: COSTOS ADICIONALES
           ============================================================ --}}
        <h5 style="color: #aaa; border-bottom: 1px solid #444; margin: 30px 0 20px 0; padding-bottom: 5px; font-size: 0.9rem; text-transform: uppercase; letter-spacing: 1px;">
            Costos Adicionales
        </h5>

        <div style="overflow-x: auto;">
            <table id="tablaCostos" class="erp-table" style="width: 100%; margin-bottom: 15px; border-collapse: collapse;">
                <thead>
                    <tr>
                        {{-- ASIGNAMOS PORCENTAJES PARA DISTRIBUIR MEJOR EL ESPACIO --}}
                        <th style="color:#fff; width: 40%;">Costo</th>
                        {{--<th style="color:#fff; width: 25%;">Valor EUR</th>  comentariado --}}                      <th style="color:#fff; width: 25%;">Valor USD</th>
                        <th style="color:#fff; width: 10%; text-align: center;">Acción</th>
                    </tr>
                </thead>
                <tbody>
                    {{-- Filas JS --}}
                </tbody>
            </table>
        </div>

        <button type="button" id="btnAgregarCosto" class="btn-primary" style="padding: 5px 15px; font-size: 0.9em;">
            <i class="fa-solid fa-plus"></i> Agregar costo
        </button>

        {{-- ESTILO EXTRA SOLO PARA ESTA TABLA DE COSTOS --}}
        <style>
            /* Asegura que los inputs dentro de la tabla de costos llenen la celda */
            #tablaCostos input,
            #tablaCostos select {
                width: 100%;
                box-sizing: border-box; /* Para que el padding no rompa el ancho */
                padding: 8px;
                background-color: #fff;
                border: 1px solid #ccc;
                border-radius: 4px;
            }
        </style>

        {{-- ============================================================
             SECCIÓN 3: FAMILIAS Y PRODUCTOS
           ============================================================ --}}
        <h5 style="color: #aaa; border-bottom: 1px solid #444; margin: 30px 0 20px 0; padding-bottom: 5px; font-size: 0.9rem; text-transform: uppercase; letter-spacing: 1px;">
            Calidad y Productos
        </h5>

        <div style="display: flex; gap: 15px; align-items: flex-end; margin-bottom: 20px;">
            <div style="flex-grow: 1;">
                <label for="selectFamilia" style="display:block; margin-bottom: 8px; color: #ddd;">Seleccionar Calidad</label>
                <select id="selectFamilia" style="width: 100%; height: 45px; padding: 5px 10px; background-color: #111; color: #fff; border: 1px solid #444; border-radius: 4px;">
                    <option value="">-- Seleccione --</option>
                    @foreach($familias as $f)
                        <option value="{{ $f->id_familia }}">{{ $f->nombre }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <button type="button" id="btnAgregarFamilia" class="btn-primary" style="height: 45px;">
                    <i class="fa-solid fa-layer-group"></i> Agregar
                </button>
            </div>
        </div>

        {{-- Contenedor de tablas de productos --}}
        <div id="contenedorFamilias"></div>

        {{-- ============================================================
             SECCIÓN 4: RESUMEN Y TOTALES
           ============================================================ --}}
        <h5 style="color: #aaa; border-bottom: 1px solid #444; margin: 30px 0 20px 0; padding-bottom: 5px; font-size: 0.9rem; text-transform: uppercase; letter-spacing: 1px;">
            Resumen de Factura
        </h5>

        {{-- Resumen Familias --}}
        <div style="overflow-x: auto; margin-bottom: 30px;">
            <table id="tablaResumenFamilias" class="erp-table" style="width: 100%;">
                <thead>
                    <tr>
                        <th style="color:#fff;">Calidad</th>
                        <th style="color:#fff;">Total KG</th>
                        <th style="color:#fff;">Total LB</th>
                        <th style="color:#fff;">Importe EUR</th>
                        <th style="color:#fff;">Importe USD</th>
                        <th style="color:#fff;">Precio CIF</th>
                        <th style="color:#fff;">Precio Bodega</th>
                        <th style="color:#fff;">Total (USD)</th>
                    </tr>
                </thead>
                <tbody>
                    {{-- JS --}}
                </tbody>
            </table>
        </div>

        {{-- Totales Generales (Diseño Grid para ahorrar espacio vertical) --}}
        <div style="background-color: #222; padding: 20px; border-radius: 8px; border: 1px solid #444;">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px;">

                <div>
                    <label style="color: #aaa; font-size: 0.85em;">Peso Total (KG)</label>
                    <div id="lblTotalKG" style="color: #fff; font-size: 1.2em; font-weight: bold;">0.0000</div>
                </div>
                <div>
                    <label style="color: #aaa; font-size: 0.85em;">Peso Total (LB)</label>
                    <div id="lblTotalLB" style="color: #fff; font-size: 1.2em; font-weight: bold;">0.0000</div>
                </div>
                <div>
                    <label style="color: #aaa; font-size: 0.85em;">Importe Prod. (USD)</label>
                    <div id="lblTotalUSD" style="color: #4ade80; font-size: 1.2em; font-weight: bold;">0.00</div>
                </div>
                <div>
                    <label style="color: #aaa; font-size: 0.85em;">Costos Adic. (USD)</label>
                    <div id="lblCostosUSD" style="color: #f87171; font-size: 1.2em; font-weight: bold;">0.00</div>
                </div>
                <div>
                    <label style="color: #aaa; font-size: 0.85em;">Costo/Libra (USD)</label>
                    <div id="lblCostoPorLibra" style="color: #fbbf24; font-size: 1.2em; font-weight: bold;">0.0000</div>
                </div>
                <div>
                    <label style="color: #aaa; font-size: 0.85em;">TOTAL FACTURA</label>
                    <div id="lblTotalFactura" style="color: #60a5fa; font-size: 1.4em; font-weight: bold;">0.00</div>
                </div>

            </div>
        </div>

        {{-- BOTONES --}}
        <div class="erp-actions" style="margin-top: 40px; text-align: center;">
            <button type="submit" class="btn-primary" style="padding: 12px 30px; font-size: 1rem; cursor: pointer;">
                <i class="fa-solid fa-save"></i> Guardar Compra
            </button>
            <a href="{{ route('compras.index') }}" class="btn-secondary" style="padding: 12px 30px; font-size: 1rem; text-decoration: none; margin-left: 15px; cursor: pointer;">
                Cancelar
            </a>
        </div>

        {{-- DATOS AUXILIARES --}}
        <script>
            window.costosOptions = `
                @foreach($costos as $c)
                    <option value="{{ $c->id_costo }}">{{ $c->nombre }}</option>
                @endforeach
            `;
        </script>

    </form>
</div>

@vite(['resources/js/app.js'])


{{-- ESTILOS PARA CORREGIR EL DESBORDAMIENTO DE LA TABLA DINÁMICA --}}
<style>
    /* 1. Atacamos directamente a las tarjetas que genera el Javascript */
    #contenedorFamilias > div {
        /* Fuerza a que la tarjeta blanca NUNCA sea más ancha que el formulario */
        max-width: 100% !important;

        /* Esta es la magia: Si la tabla no cabe, agrega scroll horizontal */
        overflow-x: auto !important;

        /* Estilos visuales para que se vea bonita la tarjeta blanca */
        background-color: #fff;
        border-radius: 8px;
        padding: 15px;
        margin-bottom: 20px;
        box-shadow: 0 4px 6px rgba(0,0,0,0.3);
    }

    /* 2. Ajustes para la tabla que está adentro */
    #contenedorFamilias table {
        /* Asegura que ocupe todo el ancho disponible */
        width: 100%;
        /* Fuerza un ancho mínimo para que los campos no se aplasten */
        min-width: 900px;
        border-collapse: collapse;
    }

    /* 3. Pequeño ajuste para los inputs dentro de esa tabla */
    #contenedorFamilias input,
    #contenedorFamilias select {
        border: 1px solid #ccc; /* Borde gris suave para que resalte en fondo blanco */
        border-radius: 4px;
        padding: 5px;
    }
</style>




<script>
/*document.addEventListener('input', function (e) {
    // 1. Buscamos el valor de la Tasa de Cambio
    const tasaInput = document.getElementById('tasa_cambio');
    const tasa = parseFloat(tasaInput.value) || 0;

    // 2. ¿Lo que estamos tocando es un campo de Valor EUR?
    // (Buscamos por nombre 'valor_eur[]' porque es el que usa Laravel para arreglos)
    if (e.target.name === 'valor_eur[]' || e.target.classList.contains('valor-eur')) {

        const fila = e.target.closest('tr');
        const inputUsd = fila.querySelector('input[name="valor_usd[]"]') ||
                         fila.querySelector('.valor-usd');

        const valorEur = parseFloat(e.target.value) || 0;

        if (inputUsd) {
            // 3. ¡Hacemos la conversión!
            const resultado = (valorEur * tasa).toFixed(2);
            inputUsd.value = resultado;

            console.log("🪄 Mago: " + valorEur + " EUR * " + tasa + " Tasa = " + resultado + " USD");
        }
    }

    // 4. Si cambias la Tasa de Cambio principal, recalculamos TODAS las filas
    if (e.target.id === 'tasa_cambio') {
        document.querySelectorAll('input[name="valor_eur[]"]').forEach(inputEur => {
            const fila = inputEur.closest('tr');
            const inputUsd = fila.querySelector('input[name="valor_usd[]"]');
            const vEur = parseFloat(inputEur.value) || 0;
            if (inputUsd) inputUsd.value = (vEur * tasa).toFixed(2);
        });
    }
}); comentariado*/

// B. Función para el botón Cancelar de esta página
function cancelarCompra() {
    // Simplemente te saca de ahí y te manda al listado
    window.location.href = "{{ url('/compras') }}";
}

</script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('formCompra');

    // 1. Bloquear el envío accidental por tecla ENTER
    form.addEventListener('keydown', function(e) {
        if (e.key === 'Enter' || e.keyCode === 13) {
            // Si el foco está en el botón de guardar, permitimos que funcione
            if (e.target.type === 'submit' || e.target.tagName === 'BUTTON') {
                return;
            }

            // Bloqueamos el enter en cualquier otro input para evitar el envío
            e.preventDefault();

            // Opcional: Saltar al siguiente input para ayudar al usuario
            const inputs = Array.from(form.querySelectorAll('input, select, textarea'));
            const index = inputs.indexOf(e.target);
            if (index > -1 && index < inputs.length - 1) {
                inputs[index + 1].focus();
            }
            return false;
        }
    });

    // 2. Confirmación con SweetAlert2 antes de guardar
    form.addEventListener('submit', function(e) {
        e.preventDefault(); // Detenemos el envío automático

        // Verificamos si hay productos agregados (validación básica)
        const filasProductos = document.querySelectorAll('#contenedorFamilias table tbody tr');
        if (filasProductos.length === 0) {
            Swal.fire({
                icon: 'error',
                title: 'Formulario Incompleto',
                text: 'Debes agregar al menos un producto a la compra.',
                background: '#1f2937',
                color: '#fff'
            });
            return;
        }

        Swal.fire({
            title: '¿Registrar Compra?',
            text: "Se afectará el inventario y se generará el registro de costos.",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#2563eb',
            cancelButtonColor: '#4b5563',
            confirmButtonText: '<i class="fa-solid fa-check"></i> Sí, guardar',
            cancelButtonText: 'Revisar datos',
            background: '#1f2937',
            color: '#fff'
        }).then((result) => {
            if (result.isConfirmed) {
                // Deshabilitamos el botón para evitar doble clic
                const btnSubmit = form.querySelector('button[type="submit"]');
                btnSubmit.disabled = true;
                btnSubmit.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Procesando...';

                form.submit(); // Enviamos manualmente
            }
        });
    });
});
function gestionarMoneda(moneda) {
    const inputTasa = document.getElementById('tasa_cambio');

    if (moneda === 'EUR') {
        inputTasa.readOnly = false;
        inputTasa.style.backgroundColor = "#2d3748"; // Color un poco más claro para editar
        inputTasa.style.color = "#4ade80"; // Verde para resaltar
        inputTasa.focus();
    } else {
        inputTasa.readOnly = true;
        inputTasa.value = "1.0000";
        inputTasa.style.backgroundColor = "#111";
        inputTasa.style.color = "#aaa";
    }

    // Si ya tienes productos en la tabla, esto fuerza el recalculo
    if (typeof recalcularTotales === 'function') {
        recalcularTotales();
    }
}

// Usamos una función directa para evitar problemas de carga
document.onreadystatechange = function () {
    if (document.readyState === "complete") {
        const form = document.getElementById('formCompra');

        form.onsubmit = function(e) {
            e.preventDefault(); // 1. Frenamos el envío de entrada

            // --- 🕵️‍♂️ PASO A: VALIDACIÓN DE PRECIOS ---
            const moneda = document.querySelector('input[name="moneda"]:checked').value;
            const nombreCampo = (moneda === 'EUR') ? 'precio_kg_eur[]' : 'precio_kg_usd[]';
            const campos = document.getElementsByName(nombreCampo);

            let incompleto = false;
            for (let i = 0; i < campos.length; i++) {
                let valor = parseFloat(campos[i].value) || 0;
                if (valor <= 0) {
                    incompleto = true;
                    campos[i].style.border = "2px solid #f87171";
                    campos[i].style.backgroundColor = "#fee2e2";
                } else {
                    campos[i].style.border = "";
                    campos[i].style.backgroundColor = "";
                }
            }

            // --- 🛑 SI FALTA INFORMACIÓN: MOSTRAR "REVISAR" ---
            if (incompleto) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Precios Requeridos',
                    text: `faltan precios en ${moneda}. Por favor, revísalos antes de grabar.`,
                    confirmButtonText: 'Revisar Datos', // Este es tu botón de "volver"
                    confirmButtonColor: '#f59e0b',
                    background: '#1f2937',
                    color: '#fff'
                });
                return false; // Cortamos el flujo aquí. NO llegará a la pregunta de grabar.
            }

            // --- ✅ SI TODO ESTÁ BIEN: PREGUNTAR SI GRABA ---
            Swal.fire({
                title: '¿Registrar Compra?',
                text: "Se afectará el inventario de Acerosal.",
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Sí, guardar',
                cancelButtonText: 'Revisar datos',
                confirmButtonColor: '#2563eb',
                background: '#1f2937',
                color: '#fff'
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit(); // Enviamos solo si el mago confirmó
                }
            });
        };
    }
}
</script>
@endsection
