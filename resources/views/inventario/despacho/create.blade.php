@extends('layouts.app')

@section('content')

<style>
/* Tabla detalle despacho */
#tablaDetalle {
    background: #1f2a3a;
    border-collapse: collapse;
    border-radius: 8px;
    overflow: hidden;
}

/* Encabezado tabla */
#tablaDetalle thead th {
    background: #2d3a4f;
    color: #ffffff;
    padding: 10px;
    font-weight: 600;
}

/* Filas */
#tablaDetalle tbody tr {
    background: #223047;
    transition: background 0.15s ease;
}

/* Hover */
#tablaDetalle tbody tr:hover {
    background: #2f4363;
}

/* Celdas */
#tablaDetalle td {
    color: #e8edf5;
    padding: 8px 10px;
    font-size: 14px;
}

/* Botón quitar */
#tablaDetalle button {
    background: #3b82f6;
    border: none;
    padding: 4px 10px;
    border-radius: 6px;
    color: white;
    font-size: 13px;
}
</style>

{{-- CONTENEDOR PARA ALERTAS JAVASCRIPT --}}
<div id="alerta-js-container"></div>

<div class="form-container">

    {{-- Bloque de Alertas --}}
    @if($errors->any())
        <div style="background-color: #fff3cd; color: #856404; padding: 15px; border-radius: 8px; margin-bottom: 20px; border: 1px solid #ffeeba;">
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if(session('error'))
        <div class="alert-auto-close" style="background-color: #f8d7da; color: #721c24; padding: 15px; border-radius: 8px; margin-bottom: 20px; border: 1px solid #f5c6cb; font-weight: bold; cursor: pointer;">
            ⚠️ Error: {{ session('error') }}
        </div>
    @endif

    @if(session('success'))
        <div class="alert-auto-close" style="background-color: #d4edda; color: #155724; padding: 15px; border-radius: 8px; margin-bottom: 20px; border: 1px solid #c3e6cb; cursor: pointer;">
            ✅ {{ session('success') }}
        </div>
    @endif

    <h2>Nueva Orden de Despacho</h2>

    <form id="formDespacho" method="POST" action="{{ route('inventario.despacho.store') }}">
    @csrf

    <h4>Encabezado</h4>

    <div style="display:flex; gap:12px;">
        <div class="form-group" style="flex:1;">
            <label>Número de orden</label>
            <input type="text" name="numero_orden" value="{{ $numeroOrden }}" readonly>
        </div>

        <div class="form-group" style="flex:1;">
            <label>Fecha</label>
            <input type="date" name="fecha" value="{{ date('Y-m-d') }}">
        </div>
    </div>

    <div style="display:flex; gap:12px; margin-top:10px;">
        <div class="form-group" style="flex:1;">
            <label>Cliente</label>
            <select name="id_cliente">
                <option value="">Seleccione cliente</option>
                @foreach($clientes as $c)
                    <option value="{{ $c->id_cliente }}">{{ $c->nombre }}</option>
                @endforeach
            </select>
        </div>

        <div class="form-group" style="flex:1;">
            <label>Vendedor</label>
            <select name="id_usuario">
                <option value="">Seleccione vendedor</option>
                @foreach($vendedores as $v)
                    <option value="{{ $v->id_usuario }}">
                        {{ $v->nombre }} {{ $v->apellidos }}
                    </option>
                @endforeach
            </select>
        </div>
    </div>

    <hr>

    <h4>Detalle</h4>

    <div style="display:flex; gap:12px;">
        <div class="form-group" style="flex:1;">
            <label>Calidad</label>
            <select id="familia">
                <option value="">Seleccione Calidad</option>
                @foreach($familias as $f)
                    <option value="{{ $f->id_familia }}" data-ubicacion="{{ $f->ubicacion }}">
                        {{ $f->nombre }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="form-group" style="flex:1;">
            <label>1. Producto</label>
            {{-- BUSCADORES POR MEDIDA Y CODIGO --}}
            <div style="display:flex; flex-direction:column; gap:5px; margin-bottom:5px;">
                <input type="text" id="buscar_codigo" placeholder="-- Escribe para buscar producto por código o nombre --"
                    style="width:100%; font-size:12px; padding:6px; background:#1f2a3a; border:1px solid #4a5568; color:#fff;">
                
                <div style="display:flex; gap:5px;">
                    <input type="text" id="buscar_mm" placeholder="Busca mm"
                        style="flex:1; font-size:12px; padding:4px; background:#1f2a3a; border:1px solid #4a5568; color:#4ade80;">
                    <input type="text" id="buscar_plg" placeholder="Busca plg"
                        style="flex:1; font-size:12px; padding:4px; background:#1f2a3a; border:1px solid #4a5568; color:#ffc107;">
                </div>
            </div>
            <select id="producto" disabled style="width:100%;"></select>
            
            <div style="margin-top: 10px;">
                <label style="display:block; margin-bottom:5px; color: #ffffff; font-weight: 600; font-size: 13px;">Escriba medida solicitada por cliente</label>
                <input type="text" id="medida_solicitada" placeholder="..." maxlength="200"
                       style="width:100%; background-color: #1f2a3a; color: #ffffff; padding: 6px; border: 1px solid #4a5568; border-radius: 4px;">
            </div>
        </div>
    </div>

    <div style="display:flex; gap:12px; margin-top:10px;">
        <div class="form-group" style="flex:1;">
            <label>Lote</label>
            <select id="lote" disabled></select>
        </div>

        <div class="form-group" style="flex:1;">
            <label>Pieza</label>
            <select id="pieza" disabled></select>
        </div>

        <div class="form-group" style="flex:1;">
            <label>Ubicación</label>
            <input type="text" id="ubicacion" placeholder="Ubicación" readonly>
        </div>
    </div>

    <div style="display:flex; gap:35px; margin-top:15px; align-items: flex-end; width: 100%;">
        <div class="form-group" style="flex: 2.5; min-width: 0;">
            <label style="display:block; margin-bottom:8px; color: #ffffff; font-weight: 600;">Cantidad (Metros)</label>
            <div style="display:flex; gap:10px; align-items:center;">
                <input type="number" id="cant_aux" placeholder="Cant."
                       style="width:90px; background-color: #1f2a3a; color: #4ade80; border: 1px solid #3b82f6; padding: 5px; text-align: center;">

                <select id="unid_aux" style="width:110px; background-color: #1f2a3a; color: #fff; padding: 5px; border-radius: 4px;">
                    <option value="mts">Metros</option>
                    <option value="plg">Pulgadas</option>
                    <option value="ft">Pies</option>
                    <option value="cm">Cm</option>
                    <option value="mm">Mm</option>
                </select>

                <span style="color: #60a5fa; font-weight: bold; margin: 0 5px;">➔</span>
                <div style="flex: 1; display: flex; align-items: center; background-color: #2d3a4f; border: 1px solid #4a5568; border-radius: 4px; padding: 0 8px; min-width: 120px;">
                    <input type="number" id="cantidad" step="0.0001"
                       style="flex: 1; background-color: #2d3a4f; color: #ffffff; font-weight: bold; text-align: center; border: 1px solid #4a5568;"
                       placeholder="0.0000" readonly>
                        <span style="color: #4ade80; font-weight: bold; font-size: 14px; margin-right: 5px;">Mts</span>
                </div>
            </div>
        </div>

        <div class="form-group" style="flex: 1; min-width: 0;">
            <label style="display:block; margin-bottom:8px; color: #ffffff; font-weight: 600;">Peso (lb)</label>
            <input type="number" id="peso" step="0.01" style="width:100%; background-color: #2d3a4f; color: #ffffff; text-align: center;" readonly>
        </div>

        <div class="form-group" style="flex: 1; min-width: 0;">
            <label style="display:block; margin-bottom:8px; color: #ffffff; font-weight: 600;">Tol. (mts)</label>
            <input type="number" id="tolerancia_visual" style="width:100%; background-color: #2d3a4f; color: #ffffff; text-align: center;" readonly>
        </div>

        <div class="form-group" style="flex: 1; min-width: 0;">
            <label style="display:block; margin-bottom:8px; color: #ffffff; font-weight: 600;">Tol. (lbs)</label>
            <input type="number" id="tolerancia_lbs_visual" style="width:100%; background-color: #2d3a4f; color: #ffffff; text-align: center;" readonly>
        </div>

        <div class="form-group" style="flex: 1; min-width: 0;">
            <label style="display:block; margin-bottom:8px; color: #ffffff; font-weight: 600;">Precio S/IVA</label>
            <input type="number" id="precio_venta_sin_iva" step="0.01" style="width:100%; background-color: #1f2a3a; color: #ffffff; text-align: center; border: 1px solid #4a5568; padding: 6px; border-radius: 4px;">
        </div>

        <div style="flex: 0 0 auto; margin-left: 10px;">
            <button type="button" id="agregarDetalle" class="btn-primary" style="height: 40px; padding: 0 20px; border-radius: 8px; font-weight: bold;">
                Agregar línea
            </button>
        </div>
    </div>

    <hr>

    <div style="overflow-x:auto; margin-top:10px;">
        <table border="1" width="100%" id="tablaDetalle">
            <thead>
            <tr>
                <th>Calidad</th>
                <th>Producto</th>
                <th>Medida Sol.</th>
                <th>Lote</th>
                <th>Pieza</th>
                <th style="text-align:right;">Metros</th>
                <th style="color: #ffc107;">Tolerancia (mts)</th>
                <th style="color: #ffc107;">Tolerancia (lbs)</th>
                <th>Peso (lb)</th>
                <th style="text-align:right;">Precio S/IVA</th>
                <th>Acción</th>
            </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>

    <input type="hidden" name="detalles" id="detalles">

    <div style="margin-top: 20px; display: flex; gap: 10px; align-items: center;">
        <button type="submit" class="btn-primary" style="padding: 10px 20px;">
            <i class="fa-solid fa-save"></i> Guardar orden
        </button>
        <a href="{{ route('inventario.despacho.index') }}"
           style="background-color: #6c757d; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; font-weight: 600; font-size: 14px;">
            <i class="fa-solid fa-ban"></i> Cancelar
        </a>
    </div>
    </form>
</div>

{{-- SCRIPTS ORIGINALES --}}
<script>
// Variable global para guardar los productos de la familia seleccionada
let productosLocal = [];

// 1. Cambio de Familia
document.getElementById('familia').addEventListener('change', function () {
    const idFamilia = this.value;
    const productoSelect = document.getElementById('producto');
    const ubicacionInput = document.getElementById('ubicacion');

    // Limpiar buscadores al cambiar de familia
    document.getElementById('buscar_codigo').value = '';
    document.getElementById('buscar_mm').value = '';
    document.getElementById('buscar_plg').value = '';

    if (!idFamilia) {
        ubicacionInput.value = '';
        productoSelect.innerHTML = '<option value="">Seleccione producto</option>';
        productoSelect.disabled = true;
        productosLocal = [];
        return;
    }

    productoSelect.innerHTML = '<option value="">Cargando productos...</option>';
    productoSelect.disabled = true;

    fetch(`/inventario/productos-por-familia/${idFamilia}`)
        .then(res => res.json())
        .then(data => {
            productosLocal = data; // Guardamos los datos originales
            renderizarProductos(data); // Llamamos a la función que dibuja el select
            productoSelect.disabled = false;
        });
});

// FUNCIÓN PARA FILTRAR Y DIBUJAR LOS PRODUCTOS
function renderizarProductos(lista) {
    const productoSelect = document.getElementById('producto');
    productoSelect.innerHTML = '<option value="">Seleccione producto (' + lista.length + ')</option>';

    lista.forEach(p => {
        const mm = p.milimetros && p.milimetros !== '0' ? p.milimetros + ' mm' : '';
        const plg = p.pulgadas && p.pulgadas !== '-' ? p.pulgadas + ' plg' : '';
        const peso = p.peso_lb_mts && parseFloat(p.peso_lb_mts) > 0 ? Number(p.peso_lb_mts) + ' lb/mts' : '';

        const partes = [mm, plg, peso].filter(Boolean);
        const textoMostrar = partes.join(' | ');

        productoSelect.innerHTML += `
            <option value="${p.id_producto}"
                    data-factor="${p.peso_lb_mts || 0}"
                    data-tolerancia="${p.tolerancia || 0}"
                    data-ubicacion="${p.nombre_ubicacion || ''}"
                    data-precio="${p.precio_venta_sin_iva || 0}">
                ${textoMostrar}
            </option>`;
    });
}

// LOGICA DE BUSQUEDA EN TIEMPO REAL
function filtrarLocal() {
    const valMM = document.getElementById('buscar_mm').value.toLowerCase();
    const valPLG = document.getElementById('buscar_plg').value.toLowerCase();
    
    // NUEVO FILTRO CODIGO / DESCRIPCION
    const valCodigo = document.getElementById('buscar_codigo').value.toLowerCase();

    const filtrados = productosLocal.filter(p => {
        // FILTRO MM (ahora como texto para permitir guiones, ej. 16-32):
        const matchMM = valMM === '' || String(p.milimetros || '').toLowerCase().includes(valMM);

        // FILTRO PLG:
        const matchPLG = valPLG === '' || String(p.pulgadas).toLowerCase().includes(valPLG);

        // FILTRO CODIGO:
        const strCodigo = (p.codigo || '').toLowerCase();
        const strDesc = (p.descripcion || '').toLowerCase();
        const matchCodigo = valCodigo === '' || strCodigo.includes(valCodigo) || strDesc.includes(valCodigo);

        return matchMM && matchPLG && matchCodigo;
    });

    renderizarProductos(filtrados);
}
// Eventos para los buscadores
document.getElementById('buscar_mm').addEventListener('input', filtrarLocal);
document.getElementById('buscar_plg').addEventListener('input', filtrarLocal);
document.getElementById('buscar_codigo').addEventListener('input', filtrarLocal);

// 2. Cambio de Producto -> Lotes
document.getElementById('producto').addEventListener('change', function () {
    const idProducto = this.value;
    const loteSelect = document.getElementById('lote');
    loteSelect.innerHTML = '<option value="">Cargando lotes...</option>';
    loteSelect.disabled = true;

    const ubicacionInput = document.getElementById('ubicacion');
    const selected = this.options[this.selectedIndex];
    if (selected && idProducto) {
        ubicacionInput.value = selected.getAttribute('data-ubicacion') || 'Sin ubicación';
        document.getElementById('precio_venta_sin_iva').value = selected.getAttribute('data-precio') || 0;
    }

    if (!idProducto) return;

    fetch(`/inventario/lotes-por-producto/${idProducto}`)
        .then(res => res.json())
        .then(data => {
            loteSelect.innerHTML = '<option value="">Seleccione lote</option>';
            data.forEach(l => {
                loteSelect.innerHTML += `<option value="${l.id_lote}">${l.codigo} — ${parseFloat(l.cantidad_total_metros).toFixed(2)} mts</option>`;
            });
            loteSelect.disabled = false;
        });
    calcularPeso();
});

// 3. Cambio de Lote -> Piezas
document.getElementById('lote').addEventListener('change', function () {
    const idLote = this.value;
    const piezaSelect = document.getElementById('pieza');
    piezaSelect.innerHTML = '<option value="">Cargando piezas...</option>';
    piezaSelect.disabled = true;

    if (!idLote) return;

    fetch(`/piezas/por-lote/${idLote}`)
        .then(res => res.json())
        .then(data => {
            piezaSelect.innerHTML = '<option value="">Seleccione Pieza...</option>';
            data.forEach(p => {
                piezaSelect.innerHTML += `<option value="${p.id_pieza}" data-metros="${p.cantidad_metros_actual}">${p.codigo} — ${parseFloat(p.cantidad_metros_actual).toFixed(2)} mts</option>`;
            });
            piezaSelect.disabled = false;
        });
});

// 4. Lógica de Tabla y Guardado
let detalles = [];

document.getElementById('agregarDetalle').addEventListener('click', function () {
    const familiaSel = document.getElementById('familia');
    const productoSel = document.getElementById('producto');
    const loteSel = document.getElementById('lote');
    const piezaSel = document.getElementById('pieza');
    const cantidadInp = document.getElementById('cantidad');
    const pesoInp = document.getElementById('peso');
    const tolMtsVis = document.getElementById('tolerancia_visual');
    const tolLbsVis = document.getElementById('tolerancia_lbs_visual');
    const medidaSolicitadaInp = document.getElementById('medida_solicitada');
    const precioInp = document.getElementById('precio_venta_sin_iva');

    if (!familiaSel.value || !productoSel.value || !parseFloat(cantidadInp.value)) {
        alert('Complete los campos obligatorios');
        return;
    }

    detalles.push({
        id_familia: familiaSel.value,
        id_producto: productoSel.value,
        id_lote: loteSel.value,
        id_pieza: piezaSel.value,
        medida_solicitada: medidaSolicitadaInp.value,
        cantidad_metros: parseFloat(cantidadInp.value),
        merma_mts: parseFloat(tolMtsVis.value) || 0,
        merma_lbs: parseFloat(tolLbsVis.value) || 0,
        cantidad_libras: parseFloat(pesoInp.value) || 0,
        precio_venta_sin_iva: parseFloat(precioInp.value) || 0
    });

    renderTabla();
    cantidadInp.value = ''; pesoInp.value = ''; medidaSolicitadaInp.value = '';
    if(precioInp) precioInp.value = '';
    if(tolMtsVis) tolMtsVis.value = '';
    if(tolLbsVis) tolLbsVis.value = '';
});

function renderTabla() {
    const tbody = document.querySelector('#tablaDetalle tbody');
    tbody.innerHTML = '';
    detalles.forEach((d, i) => {
        tbody.innerHTML += `
            <tr>
                <td>${d.id_familia}</td>
                <td>${d.id_producto}</td>
                <td style="color: #60a5fa; font-style: italic;">${d.medida_solicitada ? d.medida_solicitada : '-'}</td>
                <td>${d.id_lote}</td>
                <td>${d.id_pieza}</td>
                <td style="text-align:right;">${d.cantidad_metros.toFixed(2)}</td>
                <td style="text-align:right; color: #ffc107;">+ ${d.merma_mts.toFixed(4)}</td>
                <td style="text-align:right; color: #ffc107;">+ ${d.merma_lbs.toFixed(4)}</td>
                <td style="text-align:right;"><strong>${d.cantidad_libras.toFixed(2)}</strong></td>
                <td style="text-align:right;">$${d.precio_venta_sin_iva.toFixed(2)}</td>
                <td><button type="button" onclick="eliminarDetalle(${i})">Quitar</button></td>
            </tr>`;
    });
    document.getElementById('detalles').value = JSON.stringify(detalles);
}

function eliminarDetalle(index) {
    detalles.splice(index, 1);
    renderTabla();
}

// 5. Cálculos y Conversiones
function calcularPeso() {
    const cantidadInp = document.getElementById('cantidad');
    const pesoInp = document.getElementById('peso');
    const productoSel = document.getElementById('producto');
    const tolMtsVis = document.getElementById('tolerancia_visual');
    const tolLbsVis = document.getElementById('tolerancia_lbs_visual');

    const cantidad = parseFloat(cantidadInp.value) || 0;
    const opt = productoSel.options[productoSel.selectedIndex];

    if (opt && cantidad > 0) {
        const factor = parseFloat(opt.getAttribute('data-factor')) || 0;
        const tolMts = parseFloat(opt.getAttribute('data-tolerancia')) || 0;
        const tolLbs = tolMts * factor;
        const totalLbs = (cantidad + tolMts) * factor;

        if(tolMtsVis) tolMtsVis.value = tolMts.toFixed(4);
        if(tolLbsVis) tolLbsVis.value = tolLbs.toFixed(4);
        pesoInp.value = totalLbs.toFixed(2);
    }
}

document.getElementById('cantidad').addEventListener('input', calcularPeso);

// Conversor
document.addEventListener('DOMContentLoaded', function() {
    const cantAux = document.getElementById('cant_aux');
    const unidAux = document.getElementById('unid_aux');
    const cantMts = document.getElementById('cantidad');
    const FACTORES = { 'mts':1, 'plg':0.0254, 'ft':0.3048, 'cm':0.01, 'mm':0.001 };

    function ejecutarConversion() {
        const valor = parseFloat(cantAux.value) || 0;
        const factor = FACTORES[unidAux.value] || 1;
        if (valor > 0) {
            cantMts.value = (valor * factor).toFixed(4);
            cantMts.dispatchEvent(new Event('input'));
        } else {
            cantMts.value = '';
        }
    }
    cantAux.addEventListener('input', ejecutarConversion);
    unidAux.addEventListener('change', ejecutarConversion);
});

// 6. Envío Formulario AJAX
document.getElementById('formDespacho').addEventListener('submit', function(e) {
    e.preventDefault();
    const btn = this.querySelector('button[type="submit"]');
    btn.disabled = true;

    fetch(this.action, {
        method: 'POST',
        body: new FormData(this),
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(res => res.json())
    .then(data => {
        if (data.status === 'success') {
            window.location.href = "{{ route('inventario.despacho.index') }}";
        } else {
            alert(data.message);
            btn.disabled = false;
        }
    })
    .catch(() => { btn.disabled = false; });
});
</script>

@endsection
