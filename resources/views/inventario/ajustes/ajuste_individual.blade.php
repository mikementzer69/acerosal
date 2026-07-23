@extends('layouts.app')

<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
@section('content')
<style>
    /* Aplicamos el mismo ancho a ambos contenedores para que luzcan alineados */
    .ajuste-wrapper,
    .card-detalle-pieza {
        background: #1f2a3a;
        padding: 25px;
        border-radius: 12px;
        box-shadow: 0 8px 20px rgba(0,0,0,0.4);
        color: #e8edf5;
        margin-bottom: 30px;

        /* AJUSTE DE ANCHO: 1300px lo hace "menos angosto" y más imponente */
        max-width: 1300px;
        margin-left: auto !important;
        margin-right: auto !important;
    }

    .card-detalle-pieza {
        background: #27354a;
        border: 1px solid #3b4b61;
        display: none; /* Se activa por JS */
    }

    .input-dark {
        background: #2d3a4f !important;
        border: 1px solid #3b82f6 !important;
        color: white !important;
        padding: 12px 15px !important; /* Un poco más de aire interno */
        border-radius: 8px !important;
        width: 100%;
        height: 48px; /* Altura más profesional */
    }

    .input-dark:focus {
        border-color: #60a5fa !important;
        box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.4) !important;
    }
/* Ajuste de Select2 para el tema oscuro de Acerosal */
.select2-container--default .select2-selection--single {
    background-color: #2d3a4f !important;
    border: 1px solid #3b82f6 !important;
    height: 48px !important;
    color: white !important;
}

.select2-container--default .select2-selection--single .select2-selection__rendered {
    color: white !important;
    line-height: 48px !important;
}

.select2-container--default .select2-selection--single .select2-selection__arrow {
    height: 46px !important;
}

.select2-dropdown {
    background-color: #2d3a4f !important;
    border: 1px solid #3b82f6 !important;
    color: white !important;
}

.select2-search__field {
    background-color: #1f2a3a !important;
    color: white !important;
    border: 1px solid #3b4b61 !important;
}

</style>


<div class="container-fluid">
    <div class="ajuste-wrapper">
        <h3><i class="fa-solid fa-layer-group"></i> Localizador de Inventario</h3>
        <p class="text-muted"><i class="fa-solid fa-circle-info"></i> Selecciona la pieza navegando por la jerarquía o escanea el código.</p>

        {{-- 1. BUSCADOR JERÁRQUICO --}}
        <div class="row">
            <div class="col-md-4">
                <label>1. Producto</label>
                <select id="selectProducto" class="input-dark" onchange="cargarLotes(this.value)">
                    <option value="">-- Buscar Producto --</option>
                    @foreach($productos as $p)
                        <option value="{{ $p->id_producto }}" data-factor="{{ $p->peso_lb_mts ?? 0 }}">
                            {{ $p->codigo }} - {{ $p->descripcion }}
                            ({{ number_format((float)($p->milimetros ?? 0), 2) }}mm / {{ number_format((float)($p->pulgadas ?? 0), 3) }}" )
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label>2. Lote</label>
                <select id="selectLote" class="input-dark" onchange="cargarPiezas(this.value)" disabled>
                    <option value="">-- Seleccione Producto --</option>
                </select>
            </div>
            <div class="col-md-4">
                <label>3. Pieza</label>
                <select id="selectPieza" class="input-dark" onchange="obtenerDetallePieza(this.value)" disabled>
                    <option value="">-- Seleccione Lote --</option>
                </select>
            </div>
        </div>

        <div class="row mt-4 pt-3" style="border-top: 1px solid #2d3a4f;">
            <div class="col-md-12 text-center">
                <span>O escanea directamente:</span>
                <input type="text" id="codigoBusqueda" class="input-dark" style="width: 250px; display: inline-block; margin: 0 10px;" placeholder="Código de pieza...">
                <button type="button" class="btn-ajuste-pro" onclick="buscarPieza()"><i class="fa-solid fa-search"></i> BUSCAR</button>
            </div>
        </div>
    </div>

    {{-- 2. FORMULARIO DE AJUSTE (ÚNICO Y CONSOLIDADO) --}}
  {{-- CARD DE AJUSTE RECONFIGURADO --}}

<div id="cardDetalle" class="card-detalle-pieza" style="display:none; background: #27354a; border: 1px solid #3b4b61; border-radius: 12px; padding: 30px; margin-top: 25px; box-shadow: 0 10px 25px rgba(0,0,0,0.3);">
    <div style="border-bottom: 2px solid #3b4b61; padding-bottom: 15px; margin-bottom: 25px; display: flex; justify-content: space-between; align-items: center;">
        <div>
            <h4 id="txtProducto" style="color: #60a5fa; margin: 0; font-weight: bold; font-size: 1.4rem;">---</h4>
            <small id="txtCodigo" style="color: #94a3b8; font-size: 0.9rem;">Pieza: ---</small>
        </div>
        <div style="background: #1e3a8a; padding: 10px 20px; border-radius: 10px; border: 1px solid #3b82f6; text-align: center;">
            <span style="display: block; font-size: 0.75rem; color: #bfdbfe; text-transform: uppercase; letter-spacing: 1px;">Stock Actual</span>
            <strong style="font-size: 1.5rem; color: #fff;"><span id="valStockActual">0.00</span> <small>mts</small></strong>
        </div>
    </div>

 <form id="formAjusteActual" style="max-width: 850px; margin: 0 auto;">
    @csrf
    <input type="hidden" name="id_pieza" id="id_pieza_hidden">
    <input type="hidden" name="id_lote" id="id_lote_hidden">
    <input type="hidden" id="factor_peso_hidden" value="0">

    <div style="display: grid; grid-template-columns: 1fr 220px; gap: 40px; margin-bottom: 25px;">
        <div>
            <label style="color: #94a3b8; font-weight: 600; margin-bottom: 8px; display: block;">Tipo de Ajuste</label>
            <select name="tipo_ajuste" class="input-dark" style="height: 50px;">
                <option value="RESTA">DISMINUIR</option>
                <option value="SUMA">AUMENTAR</option>
                <option value="REINGRESO" style="color: #fbbf24; font-weight: bold;">REINGRESO (Sobrante de corte / Devolución)</option>
            </select>

            <div style="margin-top: 25px;">
                <label style="color: #94a3b8; font-weight: 600; margin-bottom: 8px; display: block;">Motivo del Ajuste</label>
                <textarea name="motivo" class="input-dark"
                          style="height: 125px; resize: none;"
                          required placeholder="Describa el motivo..."></textarea>
            </div>
        </div>

        <div style="display: flex; flex-direction: column; gap: 20px;">
            <div>
                <label style="color: #fbbf24; font-weight: 600; margin-bottom: 8px; display: block;">Metros a ajustar</label>
                <input type="number" name="cantidad" id="in_metros" step="0.01" class="input-dark"
                       style="text-align: right; font-weight: bold; font-size: 1.2rem; border-color: #fbbf24 !important;"
                       placeholder="0.00" required oninput="calcularPesoAutomatico()">
            </div>

            <div style="background: #1e3a8a33; padding: 15px; border-radius: 10px; border: 1px dashed #3b82f6;">
                <label style="color: #60a5fa; font-weight: 600; margin-bottom: 8px; display: block; font-size: 0.85rem;">Peso Proyectado (lbs)</label>
                <input type="number" id="in_peso_ajuste" class="input-dark"
                       style="background: #1a2533; border: none !important; text-align: right; font-weight: bold; font-size: 1.2rem; padding: 0 !important; height: auto !important;"
                       readonly value="0.00">
            </div>
        </div>
    </div>

    <div style="border-top: 1px solid #3b4b61; pt-4; margin-top: 20px; padding-top: 25px; text-align: center;">
        <button type="submit" id="btnGuardarAjuste" class="btn-ajuste-pro"
                style="width: 100%; max-width: 400px; padding: 15px; background: #059669; border-radius: 10px; font-size: 1.1rem;">
            <i class="fa-solid fa-shield-check"></i> GUARDAR AJUSTE DE STOCK
        </button>
    </div>
</form>
</div>
</div>

{{-- SCRIPTS SIN DUPLICADOS --}}
<script>

    // Función para capturar el factor cuando se selecciona la pieza
function llenarCardAjuste(p) {
    document.getElementById('cardDetalle').style.display = 'block';
    document.getElementById('id_lote_hidden').value = p.id_lote;
    // Capturamos el 5.2 del JSON
    const factorRecibido = p.peso_lb_mts ? parseFloat(p.peso_lb_mts) : 0;

    // Intentamos guardar en el puente
    const campoOculto = document.getElementById('factor_peso_hidden');

    if (campoOculto) {
        campoOculto.value = factorRecibido;
        console.log("ÉXITO: Factor " + factorRecibido + " cargado correctamente.");
    } else {
        // Si sale esta alerta, es que falta el <input hidden> de arriba
        alert("ERROR CRÍTICO: No se encontró el elemento 'factor_peso_hidden' en el HTML.");
    }

    // Datos visuales
    document.getElementById('txtProducto').innerText = p.nombre_producto;
    document.getElementById('valStockActual').innerText = parseFloat(p.cantidad_metros_actual).toFixed(2);
    document.getElementById('id_pieza_hidden').value = p.id_pieza;

    // Limpieza de inputs
    document.getElementById('in_metros').value = "";
    document.getElementById('in_peso_ajuste').value = "0.00";
}

function calcularPesoAutomatico() {
    const metros = parseFloat(document.getElementById('in_metros').value) || 0;
    const factor = parseFloat(document.getElementById('factor_peso_hidden').value) || 0;

    const resultado = metros * factor;

    // Mostramos el cálculo en el Radar para confirmar
    console.log("Radar de cuenta: " + metros + " mts x " + factor + " factor = " + resultado);

    document.getElementById('in_peso_ajuste').value = resultado.toFixed(2);
}

function cargarLotes(idProducto) {
    if(!idProducto) return;
    const selLote = document.getElementById('selectLote');
    selLote.disabled = true;
    selLote.innerHTML = '<option>Cargando...</option>';

    fetch(`/inventario/ajustes/lotes/${idProducto}`)
        .then(res => res.json())
        .then(data => {
            selLote.innerHTML = '<option value="">-- Seleccione Lote --</option>';
            data.forEach(l => selLote.innerHTML += `<option value="${l.id_lote}">${l.codigo} (${l.fecha_ingreso})</option>`);
            selLote.disabled = false;
        });
}

function cargarPiezas(idLote) {
    if(!idLote) return;

    // Guardamos el lote en el input oculto por si acaso
    document.getElementById('id_lote_hidden').value = idLote;

    const selPieza = document.getElementById('selectPieza');
    selPieza.disabled = true;

    fetch(`/inventario/ajustes/piezas/${idLote}`)
        .then(res => res.json())
        .then(data => {
            selPieza.innerHTML = '<option value="">-- Seleccione Pieza --</option>';

            // 1. Cargamos las piezas que ya están en bodega
            data.forEach(p => {
                selPieza.innerHTML += `<option value="${p.id_pieza}">${p.codigo} [${p.cantidad_metros_actual} mts]</option>`;
            });

            // 2. AGREGAMOS LA OPCIÓN MÁGICA
            selPieza.innerHTML += `<option value="NUEVA" style="background: #1e3a8a; color: #fbbf24; font-weight: bold;">[+] REGISTRAR COMO PIEZA NUEVA</option>`;

            selPieza.disabled = false;
        });
}

function obtenerDetallePieza(idPieza) {
    const card = document.getElementById('cardDetalle');
    const tipoAjuste = document.getElementsByName('tipo_ajuste')[0];

    // Si el usuario elige la opción de crear nueva...
    if (idPieza === 'NUEVA') {
        card.style.display = 'block';
        tipoAjuste.value = 'REINGRESO'; // Cambiamos el modo automáticamente

        document.getElementById('id_pieza_hidden').value = ""; // No hay pieza vieja
        document.getElementById('txtCodigo').innerText = "GENERANDO NUEVA IDENTIDAD...";
        document.getElementById('valStockActual').innerText = "0.00";

        // El factor de peso lo sacamos del selector de producto que ya tiene el data-factor
        const factorProd = document.querySelector('#selectProducto option:checked').dataset.factor;
        document.getElementById('factor_peso_hidden').value = factorProd;

        // Enfocamos los metros para que el usuario escriba de una vez
        document.getElementById('in_metros').focus();
        return;
    }

    // Si es una pieza normal, sigue con tu lógica de siempre...
    if(!idPieza) return card.style.display = 'none';
    fetch("{{ route('inventario.ajuste.buscar') }}?id_pieza=" + idPieza)
        .then(res => res.json())
        .then(res => res.status === 'success' ? llenarCardAjuste(res.data) : alert(res.message));
}
function buscarPieza() {
    const codigo = document.getElementById('codigoBusqueda').value;
    if(!codigo) return;
    fetch("{{ route('inventario.ajuste.buscar') }}?codigo=" + codigo)
        .then(res => res.json())
        .then(res => res.status === 'success' ? llenarCardAjuste(res.data) : alert(res.message));
}


function convertirDimension(origen) {
    const mm = document.getElementById('in_mm');
    const pulg = document.getElementById('in_pulg');
    if (origen === 'mm') pulg.value = mm.value ? (parseFloat(mm.value) / 25.4).toFixed(4) : '';
    else mm.value = pulg.value ? (parseFloat(pulg.value) * 25.4).toFixed(2) : '';
}

document.getElementById('formAjusteActual').addEventListener('submit', function(e) {
    e.preventDefault();

    // 1. CAPTURA DE DATOS PARA VALIDACIÓN
    const btn = document.getElementById('btnGuardarAjuste');
    const tipoAjuste = document.getElementsByName('tipo_ajuste')[0].value;
    const metrosIngresados = parseFloat(document.getElementById('in_metros').value) || 0;
    const stockActual = parseFloat(document.getElementById('valStockActual').innerText) || 0;
    const motivo = document.getElementsByName('motivo')[0].value.trim();

    // 2. VALIDACIÓN: Cantidad mayor a cero
    if (metrosIngresados <= 0) {
        alert('⚠️ Por favor, ingresa una cantidad de metros válida mayor a 0.00');
        return;
    }

    // 3. VALIDACIÓN: No permitir saldos negativos (El Blindaje)
    if (tipoAjuste === 'RESTA' && metrosIngresados > stockActual) {
        alert('❌ ERROR DE STOCK: No puedes restar ' + metrosIngresados + ' mts porque solo hay ' + stockActual + ' mts disponibles en esta pieza.');
        return;
    }

    // 4. VALIDACIÓN: Motivo obligatorio y descriptivo
    if (motivo.length < 10) {
        alert('📝 El motivo del ajuste es muy corto. Por favor, describe mejor por qué estás realizando este cambio (mínimo 10 caracteres).');
        return;
    }

    // 5. SI TODO ESTÁ BIEN, PROCEDEMOS AL ENVÍO
    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> BLINDANDO DATOS...';

    fetch("{{ route('inventario.ajuste.store') }}", {
        method: 'POST',
        body: new FormData(this),
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(res => res.json())
    .then(data => {
        if(data.status === 'success') {
            alert('✅ ¡Éxito! ' + data.message);
            location.reload();
        } else {
            alert('❌ Error del Servidor: ' + data.message);
            btn.disabled = false;
            btn.innerHTML = '<i class="fa-solid fa-shield-check"></i> GUARDAR AJUSTE DE STOCK';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('❌ Error de conexión. Intenta de nuevo.');
        btn.disabled = false;
        btn.innerHTML = '<i class="fa-solid fa-shield-check"></i> GUARDAR AJUSTE DE STOCK';
    });
});

// A. Controla la interfaz cuando eliges "REINGRESO"
function gestionarModoReingreso() {
    const modo = document.getElementById('tipo_ajuste').value;
    const selPieza = document.getElementById('selectPieza');
    const factorProd = document.querySelector('#selectProducto option:checked').dataset.factor;

    if (modo === 'REINGRESO') {
        selPieza.disabled = true;
        document.getElementById('cardDetalle').style.display = 'block';
        document.getElementById('txtCodigo').innerText = "MODO: Registro de Pieza Nueva (Retal)";
        document.getElementById('id_pieza_hidden').value = ""; // No hay ID de pieza
        document.getElementById('factor_peso_hidden').value = factorProd; // Usamos el del producto
        document.getElementsByName('motivo')[0].value = "Reingreso por sobrante de corte / devolución.";
    } else {
        selPieza.disabled = false;
    }
}

// B. Sustituye tu evento 'submit' actual por este (Lógica de Doble Ruta)
document.getElementById('formAjusteActual').addEventListener('submit', function(e) {
    e.preventDefault();
    const tipo = document.getElementById('tipo_ajuste').value;

    // Si es REINGRESO, usamos la nueva ruta; si no, la de siempre
    const url = (tipo === 'REINGRESO')
        ? "{{ route('inventario.reingreso.store') }}"
        : "{{ route('inventario.ajuste.store') }}";

    const btn = document.getElementById('btnGuardarAjuste');
    btn.disabled = true;
    btn.innerHTML = 'PROCESANDO...';

    fetch(url, {
        method: 'POST',
        body: new FormData(this),
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(res => res.json())
    .then(data => {
        if(data.status === 'success') {
            alert('✅ ' + data.message);
            location.reload();
        } else {
            alert('❌ ' + data.message);
            btn.disabled = false;
            btn.innerHTML = 'GUARDAR AJUSTE DE STOCK';
        }
    });
});

$(document).ready(function() {
    $('#selectProducto').select2({
        placeholder: '-- Escribe para buscar producto --',
        allowClear: true
    });

    // Evento para cargar lotes cuando se selecciona un producto en Select2
    $('#selectProducto').on('select2:select', function (e) {
        var idProducto = e.params.data.id;
        cargarLotes(idProducto); // Llamamos a tu función existente
    });
});
</script>
@endsection
