@extends('layouts.app')

@section('content')

<div class="form-container">

    <h2 class="form-title">
        <i class="fa-solid fa-pen-to-square"></i> Editar Compra
        <span style="font-size: 0.6em; color: #888; font-weight: normal; margin-left: 10px;">
            #{{ $compra->numero_factura }}
        </span>
    </h2>

    @if(session('error'))
        <div class="form-alert form-error">
            {{ session('error') }}
        </div>
    @endif

    {{-- Variables JS --}}
    <script>
        window.urlProductosPorFamilia = "{{ url('productos/por-familia') }}";
    </script>

    <form action="{{ route('compras.update', $compra->id_compra) }}" method="POST" id="formCompraEdit" class="erp-form">
        @csrf
        @method('PUT')

        {{-- ============================================================
             SECCIÓN 1: DATOS GENERALES
            ============================================================ --}}
        <h5 style="color: #aaa; border-bottom: 1px solid #444; margin-bottom: 20px; padding-bottom: 5px; font-size: 0.9rem; text-transform: uppercase; letter-spacing: 1px;">
            Datos Generales
        </h5>

        <div style="display: flex; gap: 30px; margin-bottom: 20px; flex-wrap: wrap;">
            <div style="flex: 1; min-width: 200px;">
                <label style="display:block; margin-bottom: 8px; color: #ddd;">Empresa</label>
                <input type="text" value="{{ session('nombreEmpresa') }}" readonly
                       style="width: 100%; padding: 10px; background-color: #4a5568 !important; color: #fff !important; font-weight: bold; border: 1px solid #2d3748; border-radius: 4px;">
            </div>

            <div style="flex: 1; min-width: 250px;">
                <label for="id_proveedor" style="display:block; margin-bottom: 8px; color: #ddd;">Proveedor *</label>
                <select name="id_proveedor" id="id_proveedor" required
                        style="width: 100%; height: 45px; padding: 5px 10px; background-color: #111; color: #fff; border: 1px solid #444; border-radius: 4px;">
                    <option value="">Seleccione proveedor</option>
                    @foreach($proveedores as $prov)
                        <option value="{{ $prov->id_proveedor }}" {{ $compra->id_proveedor == $prov->id_proveedor ? 'selected' : '' }}>
                            {{ $prov->nombre }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        {{-- ✨ NUEVO: SELECTOR DE TIPO DE FACTURA (IGUAL QUE EN CREACIÓN) ✨ --}}
        <div style="margin-bottom: 20px; background: #1f2937; padding: 15px; border-radius: 8px; border: 1px solid #3b82f6;">
            <label style="display:block; margin-bottom: 10px; color: #60a5fa; font-weight: bold;">Tipo de Facturación</label>
            <div style="display: flex; gap: 20px;">
                <label style="color: #fff; cursor: pointer;">
                    <input type="radio" name="tipo_facturacion" value="unica" {{ $compra->tipo_facturacion === 'unica' ? 'checked' : '' }} onchange="gestionarTipoFactura(this.value)">
                    Factura Única (Global)
                </label>
                <label style="color: #fff; cursor: pointer;">
                    <input type="radio" name="tipo_facturacion" value="multiple" {{ $compra->tipo_facturacion === 'multiple' ? 'checked' : '' }} onchange="gestionarTipoFactura(this.value)">
                    Facturas Múltiples (Por Calidad)
                </label>
            </div>
        </div>

        <div style="display: flex; gap: 30px; margin-bottom: 20px; flex-wrap: wrap;">
            <div id="contenedor_factura_general" style="flex: 1; min-width: 200px; display: {{ $compra->tipo_facturacion === 'multiple' ? 'none' : 'block' }};">
                <label for="numero_factura" style="display:block; margin-bottom: 8px; color: #ddd;">Número de factura *</label>
                <input type="text" name="numero_factura" id="numero_factura" value="{{ old('numero_factura', $compra->numero_factura) }}"
                       style="width: 100%; padding: 10px; background-color: #111; color: #fff; border: 1px solid #444; border-radius: 4px;">
            </div>

            <div style="flex: 1; min-width: 150px;">
                <label for="fecha_ingreso" style="display:block; margin-bottom: 8px; color: #ddd;">Fecha Ingreso *</label>
                <input type="date" name="fecha_ingreso" id="fecha_ingreso" value="{{ old('fecha_ingreso', $compra->fecha_ingreso) }}" required
                       style="width: 100%; padding: 10px; background-color: #111; color: #fff; border: 1px solid #444; border-radius: 4px;">
            </div>

            <div style="flex: 1; min-width: 150px;">
                <label for="fecha_emision_factura" style="display:block; margin-bottom: 8px; color: #ddd;">Fecha Emisión *</label>
                <input type="date" name="fecha_emision_factura" id="fecha_emision_factura" value="{{ old('fecha_emision_factura', $compra->fecha_emision_factura) }}" required
                       style="width: 100%; padding: 10px; background-color: #111; color: #fff; border: 1px solid #444; border-radius: 4px;">
            </div>

            <div style="flex: 1; min-width: 100px;">
                <label for="tasa_cambio" style="display:block; margin-bottom: 8px; color: #ddd;">Tasa Cambio *</label>
                <input type="number" step="0.0001" name="tasa_cambio" id="tasa_cambio" value="{{ old('tasa_cambio', $compra->tasa_cambio) }}" required
                       style="width: 100%; padding: 10px; background-color: #111; color: #fff; border: 1px solid #444; border-radius: 4px;">
            </div>
        </div>

        {{-- SECCIÓN 2: COSTOS ADICIONALES --}}
        <h5 style="color: #aaa; border-bottom: 1px solid #444; margin: 30px 0 20px 0; padding-bottom: 5px; font-size: 0.9rem; text-transform: uppercase; letter-spacing: 1px;">
            Costos Adicionales
        </h5>

        <div style="overflow-x: auto;">
            <table id="tablaCostos" class="erp-table" style="width: 100%; margin-bottom: 15px; border-collapse: collapse;">
                <thead>
                    <tr>
                        <th style="color:#fff; width: 40%;">Costo</th>
                        <th style="color:#fff; width: 25%;">Valor USD</th>
                        <th style="color:#fff; width: 10%; text-align: center;">Acción</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($compra->compraCostos as $index => $costo)
                    <tr>
                        <td>
                            <select name="id_costo[]" class="form-control">
                                @foreach($costos as $c)
                                    <option value="{{ $c->id_costo }}" {{ $costo->id_costo == $c->id_costo ? 'selected' : '' }}>{{ $c->nombre }}</option>
                                @endforeach
                            </select>
                        </td>
                        <td>
                            <input type="number" step="0.01" name="valor_usd[]" value="{{ $costo->valor_usd }}" class="form-control campo-costo-usd">
                        </td>
                        <td style="text-align: center;">
                            <button type="button" class="btn-eliminar-fila btn btn-danger btn-sm">X</button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <button type="button" id="btnAgregarCosto" class="btn-primary" style="padding: 5px 15px; font-size: 0.9em;">
            <i class="fa-solid fa-plus"></i> Agregar costo
        </button>

        {{-- SECCIÓN 3: FAMILIAS Y PRODUCTOS --}}
        <h5 style="color: #aaa; border-bottom: 1px solid #444; margin: 30px 0 20px 0; padding-bottom: 5px; font-size: 0.9rem; text-transform: uppercase; letter-spacing: 1px;">
            Calidad y Productos
        </h5>

        <div style="display: flex; gap: 15px; align-items: flex-end; margin-bottom: 20px;">
            <div style="flex-grow: 1;">
                <label for="selectFamilia" style="display:block; margin-bottom: 8px; color: #ddd;">Agregar nueva Calidad</label>
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

        <div id="contenedorFamilias">
            @foreach($compra->compraFamilias as $familiaComp)
            <div class="familia-wrapper" id="familia-{{ $familiaComp->id_familia }}" data-id="{{ $familiaComp->id_familia }}">
                <div style="background-color: #fff; padding: 15px; border-radius: 8px; margin-bottom: 20px; box-shadow: 0 4px 6px rgba(0,0,0,0.3);">

                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; border-bottom: 2px solid #eee; padding-bottom: 10px;">
                        <div style="display: flex; align-items: center; gap: 20px;">
                            <h4 style="margin: 0; color: #333; font-weight: 800;">Calidad: {{ $familiaComp->familia->nombre }}</h4>

                            {{-- ✨ AQUÍ ESTÁ EL CAMPO DE FACTURA POR CALIDAD ✨ --}}
                            <div class="contenedor-factura-multiple" style="display: {{ $compra->tipo_facturacion === 'multiple' ? 'flex' : 'none' }}; align-items: center; gap: 10px; background: #f3f4f6; padding: 6px 12px; border-radius: 6px; border: 1px solid #d1d5db;">
                                <label style="color: #000 !important; font-size: 1em; margin: 0; font-weight: 900;">No. Factura:</label>
                                <input type="text" name="factura_familia[{{ $familiaComp->id_familia }}]"
                                       value="{{ $familiaComp->numero_factura }}"
                                       class="input-factura-familia"
                                       style="width: 140px; padding: 5px 10px; background: #1f2937 !important; border: 1px solid #3b82f6 !important; color: #ffffff !important; font-weight: bold;" placeholder="F-123">
                            </div>
                        </div>

                        <div style="display: flex; gap: 10px;">
                            <button type="button" class="btn-primary btn-agregar-producto" data-familia="{{ $familiaComp->id_familia }}" style="padding: 5px 15px; font-size: 0.85em;">+ Producto</button>
                            <button type="button" class="btn-eliminar-familia" onclick="eliminarFamilia({{ $familiaComp->id_familia }})" style="background-color: #dc2626; color: white; border: none; padding: 5px 15px; border-radius: 4px; font-size: 0.85em; cursor: pointer;">Eliminar Calidad</button>
                        </div>
                    </div>

                    <div style="overflow-x: auto;">
                        <table class="table-productos" style="width: 100%; min-width: 900px; border-collapse: separate; border-spacing: 0;">
                            <thead style="background: #f4f4f4; color: #333; text-align: center; font-size: 0.85em; font-weight: bold;">
                                <tr>
                                    <th style="padding: 10px; width: 20%;">Producto</th>
                                    <th style="padding: 10px; width: 50px;">MM</th>
                                    <th style="padding: 10px; width: 50px;">PULG</th>
                                    <th style="padding: 10px; width: 55px;">Cant.</th>
                                    <th style="padding: 10px; width: 115px;">Peso KG</th>
                                    <th style="padding: 10px; width: 115px;">Peso LB</th>
                                    <th style="padding: 10px; width: 80px; color: #059669;">P. EUR</th>
                                    <th style="padding: 10px; width: 80px;">P. USD</th>
                                    <th style="padding: 10px; width: 90px; color: #059669;">Tot. EUR</th>
                                    <th style="padding: 10px; width: 90px;">Tot. USD</th>
                                    <th style="padding: 10px; width: 40px;"></th>
                                </tr>
                            </thead>
                            <tbody id="tbody-familia-{{ $familiaComp->id_familia }}">
                                @foreach($compra->compraProductos as $prod)
                                    @if($prod->producto->id_familia == $familiaComp->id_familia)
                                    <tr class="fila-producto">
                                        <td>
                                            <input type="hidden" name="id_producto[]" value="{{ $prod->id_producto }}">
                                            <input type="text" value="{{ $prod->producto->descripcion }}" class="form-input-table" readonly style="background: #eee; text-align: left; font-weight: bold;">
                                        </td>
                                        <td><input type="text" value="{{ $prod->producto->milimetros }}" class="form-input-table" readonly style="background: #f9f9f9; text-align: center;"></td>
                                        <td><input type="text" value="{{ $prod->producto->pulgadas }}" class="form-input-table" readonly style="background: #f9f9f9; text-align: center;"></td>
                                        <td><input type="number" name="cantidad[]" value="{{ $prod->cantidad }}" class="form-input-table input-cantidad" oninput="calcularFila(this)"></td>
                                        <td><input type="number" step="0.0001" name="peso_kg[]" value="{{ number_format($prod->peso_kg, 4, '.', '') }}" class="form-input-table input-peso" oninput="calcularFila(this)"></td>
                                        <td><input type="number" step="0.0001" value="{{ number_format($prod->peso_libra, 4, '.', '') }}" class="form-input-table input-libras" readonly style="background: #f9f9f9;"></td>
                                        <td><input type="number" step="0.0001" name="precio_kg_eur[]" value="{{ number_format($prod->precio_kg_eu, 4, '.', '') }}" class="form-input-table input-precio-eu" oninput="calcularFila(this)"></td>
                                        <td><input type="number" step="0.0001" name="precio_kg_usd[]" value="{{ number_format($prod->precio_kg_usd, 4, '.', '') }}" class="form-input-table input-precio-usd" readonly style="background: #eef;"></td>
                                        <td><input type="number" step="0.01" name="importe_eur[]" value="{{ number_format($prod->importe_eu, 2, '.', '') }}" class="form-input-table input-importe-eu" readonly></td>
                                        <td><input type="number" step="0.01" name="importe_usd[]" value="{{ number_format($prod->importe_dolares, 2, '.', '') }}" class="form-input-table input-importe-usd" readonly style="font-weight: bold;"></td>
                                        <td style="text-align: center;">
                                            <input type="hidden" name="familia_producto[]" value="{{ $familiaComp->id_familia }}">
                                            <button type="button" class="btn-eliminar-fila" style="background: #dc2626; color: #fff; border: none; border-radius: 4px; width: 32px; height: 32px; cursor: pointer;">X</button>
                                        </td>
                                    </tr>
                                    @endif
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        {{-- SECCIÓN 4: TOTALES --}}
        <h5 style="color: #aaa; border-bottom: 1px solid #444; margin-top: 30px;">Resumen de Factura</h5>
        <div style="overflow-x: auto; margin-bottom: 30px;">
            <table id="tablaResumenFamilias" class="erp-table" style="width: 100%;">
                <thead>
                    <tr>
                        <th style="color:#fff;">Calidad</th>
                        <th style="color:#fff;">Total KG</th>
                        <th style="color:#fff;">Total LB</th>
                        <th style="color:#4ade80;">Importe EUR</th>
                        <th style="color:#fff;">Importe USD</th>
                        <th style="color:#fff;">Precio CIF</th>
                        <th style="color:#fff;">Precio Bodega</th>
                        <th style="color:#60a5fa;">Total (USD)</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($compra->compraFamilias as $fam)
                    <tr data-familia="{{ $fam->id_familia }}">
                        <td>{{ $fam->familia->nombre }}</td>
                        <td class="res-kg">{{ number_format($fam->peso_total_kg, 4) }}</td>
                        <td class="res-lb">{{ number_format($fam->peso_total_libras, 4) }}</td>
                        <td class="res-eur" style="color: #4ade80;">{{ number_format($fam->importe_total_eu, 2) }}</td>
                        <td class="res-usd">{{ number_format($fam->importe_total_dolares, 2) }}</td>
                        <td class="res-cif">{{ number_format($fam->precio_cif, 4) }}</td>
                        <td class="res-bodega">{{ number_format($fam->precio_unitario_bodega, 4) }}</td>
                        <td class="res-total" style="font-weight: bold; color: #60a5fa;">{{ number_format($fam->total_familia, 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div style="background-color: #222; padding: 20px; border-radius: 8px; border: 1px solid #444;">
            <div style="display: flex; gap: 30px; flex-wrap: wrap;">
                <div>Peso KG: <span id="lblTotalKG" style="color: #fff; font-weight: bold;">{{ number_format($compra->peso_total_kg, 4) }}</span></div>
                <div>Peso LB: <span id="lblTotalLB" style="color: #fff; font-weight: bold;">{{ number_format($compra->peso_total_libras, 4) }}</span></div>
                <div>Total Prod. (USD): <span id="lblTotalUSD" style="color: #4ade80; font-weight: bold;">$ {{ number_format($compra->importe_total_factura, 2) }}</span></div>
                <div>TOTAL FACTURA: <span id="lblTotalFactura" style="color: #60a5fa; font-weight: bold; font-size: 1.2em;">$ {{ number_format($compra->total_factura, 2) }}</span></div>
            </div>
        </div>

        <div class="erp-actions" style="margin-top: 40px; text-align: center; display: flex; justify-content: center; gap: 20px;">
            <button type="submit" class="btn-primary" style="padding: 12px 30px; font-size: 1rem;"><i class="fa-solid fa-rotate"></i> Actualizar Compra</button>
            <button type="button" class="btn btn-danger" onclick="confirmarAnulacion()"><i class="fa-solid fa-trash-can"></i> Anular Factura</button>
            <a href="{{ route('compras.index') }}" class="btn-secondary" style="padding: 12px 30px; text-decoration: none;">Cancelar</a>
        </div>
    </form>
</div>

<style>
    .form-input-table { width: 100%; height: 38px !important; padding: 5px 8px; border: 1px solid #ccc; border-radius: 4px; text-align: right; }
    #contenedorFamilias > div { max-width: 100% !important; overflow-x: auto !important; background-color: #fff; border-radius: 8px; padding: 15px; margin-bottom: 20px; box-shadow: 0 4px 6px rgba(0,0,0,0.3); }
    input[type="date"]::-webkit-calendar-picker-indicator { filter: invert(1); cursor: pointer; }
</style>

<script>
// --- ✨ LÓGICA DEL MAGO PARA FACTURAS MÚLTIPLES ---
function gestionarTipoFactura(tipo) {
    const contenedorFactura = document.getElementById('contenedor_factura_general');
    const inputFactura = document.getElementById('numero_factura');
    const contenedoresMultiples = document.querySelectorAll('.contenedor-factura-multiple');

    if (tipo === 'unica') {
        contenedorFactura.style.display = 'block';
        inputFactura.required = true;
        contenedoresMultiples.forEach(c => c.style.display = 'none');
    } else {
        contenedorFactura.style.display = 'none';
        inputFactura.required = false;
        inputFactura.value = '';
        contenedoresMultiples.forEach(c => c.style.display = 'flex');
    }
}

function calcularFila(elemento) {
    let fila = elemento.closest('tr');
    let cantidad = parseFloat(fila.querySelector('.input-cantidad').value) || 0;
    let pesoKG = parseFloat(fila.querySelector('.input-peso').value) || 0;
    let precioEU = parseFloat(fila.querySelector('.input-precio-eu').value) || 0;
    let tasa = parseFloat(document.getElementById('tasa_cambio').value) || 1;

    let pesoLB = pesoKG * 2.20462;
    let precioUSD = precioEU * tasa;
    let importeEU = pesoKG * precioEU;
    let importeUSD = pesoKG * precioUSD;

    fila.querySelector('.input-libras').value = pesoLB.toFixed(4);
    fila.querySelector('.input-precio-usd').value = precioUSD.toFixed(4);
    fila.querySelector('.input-importe-eu').value = importeEU.toFixed(2);
    fila.querySelector('.input-importe-usd').value = importeUSD.toFixed(2);

    if(typeof recalcularTotales === 'function') recalcularTotales();
}

function confirmarAnulacion() {
    if (confirm("¿Estás seguro de ANULAR esta factura? Se revertirá el inventario.")) {
        // Lógica de anulación (puedes enviar a una ruta específica)
        alert("Anulando...");
    }
}

document.addEventListener("DOMContentLoaded", function() {
    // Al cargar, deshabilitamos familias ya agregadas en el select
    let familiasCargadas = @json($compra->compraFamilias->pluck('id_familia'));
    familiasCargadas.forEach(id => {
        let opt = document.querySelector(`#selectFamilia option[value="${id}"]`);
        if (opt) opt.disabled = true;
    });
});
</script>

@vite(['resources/js/app.js'])
@endsection
