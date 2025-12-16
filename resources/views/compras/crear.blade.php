@extends('layouts.app')

@section('content')

<div class="container">

    <h2 class="titulo-modulo">Registrar Nueva Compra</h2>

    @if(session('error'))
        <div class="alerta-error">
            {{ session('error') }}
        </div>
    @endif

    {{-- 
        Formulario principal de la compra.
        - Se envía a compras.store
        - El id="formCompra" lo usa el JS para validaciones y cálculos
    --}}
    <form action="{{ route('compras.store') }}" method="POST" id="formCompra">
        @csrf

        {{-- ============================================================
             SECCIÓN: DATOS GENERALES
             - Empresa (solo lectura, desde sesión)
             - Proveedor
             - Número de factura
             - Fechas
             - Tasa de cambio
           ============================================================ --}}
        <fieldset class="form-section">
            <legend>Datos Generales</legend>

            {{-- Empresa solo como referencia visual; el id_empresa se toma de sesión en el controlador --}}
            <div class="form-group">
                <label>Empresa</label>
                <input type="text" value="{{ session('nombreEmpresa') }}" readonly>
            </div>

            <div class="form-group">
                <label for="id_proveedor">Proveedor</label>
                <select name="id_proveedor" id="id_proveedor" required>
                    <option value="">Seleccione proveedor</option>
                    @foreach($proveedores as $prov)
                        <option value="{{ $prov->id_proveedor }}">{{ $prov->nombre }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label for="numero_factura">Número de factura</label>
                <input type="text" name="numero_factura" id="numero_factura" required>
            </div>

            <div class="form-group">
                <label for="fecha_ingreso">Fecha de ingreso</label>
                <input type="date" name="fecha_ingreso" id="fecha_ingreso" value="{{ date('Y-m-d') }}" required>
            </div>

            <div class="form-group">
                <label for="fecha_emision_factura">Fecha emisión factura</label>
                <input type="date" name="fecha_emision_factura" id="fecha_emision_factura" required>
            </div>

            <div class="form-group">
                <label for="tasa_cambio">Tasa de cambio</label>
                <input type="number" step="0.01" value="1" name="tasa_cambio" id="tasa_cambio" required>
            </div>
        </fieldset>


        {{-- ============================================================
             SECCIÓN: COSTOS ADICIONALES
             - Tabla dinámica manejada por JS
             - Cada fila: costo + valor en EUR
             - El valor en USD se calcula con la tasa de cambio
           ============================================================ --}}
        <fieldset class="form-section">
            <legend>Costos Adicionales</legend>

            <table id="tablaCostos" class="tabla-datos">
                <thead>
                    <tr>
                        <th>Costo</th>
                        <th>Valor EUR</th>
                        <th>Valor USD</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody>
                    {{-- Filas a agregar por JS --}}
                </tbody>
            </table>

            <button type="button" id="btnAgregarCosto" class="btn-agregar">
                + Agregar costo
            </button>
        </fieldset>


        {{-- ============================================================
             SECCIÓN: FAMILIAS Y PRODUCTOS
             - Se selecciona una familia y se agrega un bloque (tabla)
             - Cada bloque tiene productos de esa familia
             - JS bloquea la familia en el select si ya está usada
           ============================================================ --}}
        <fieldset class="form-section">
            <legend>Familias y Productos</legend>

            {{-- Selector de familia y botón para crear tabla de productos --}}
            <div class="form-group">
                <label for="selectFamilia">Agregar familia</label>
                <select id="selectFamilia">
                    <option value="">Seleccione familia</option>
                    @foreach($familias as $f)
                        <option value="{{ $f->id_familia }}">{{ $f->nombre }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <button type="button" id="btnAgregarFamilia" class="btn-agregar">
                    + Agregar familia
                </button>
            </div>

            {{-- Contenedor donde JS va insertando las tablas por familia --}}
            <div id="contenedorFamilias">
                {{-- Cada bloque de familia se inyecta aquí por JS --}}
            </div>
        </fieldset>


        {{-- ============================================================
             SECCIÓN: RESUMEN POR FAMILIA
             - Calculado en tiempo real por JS
             - Muestra totales por familia:
               KG, LB, importe EUR/USD, precio CIF, precio bodega, total familia
           ============================================================ --}}
        <fieldset class="form-section">
            <legend>Resumen por Familia</legend>

            <table id="tablaResumenFamilias" class="tabla-datos">
                <thead>
                    <tr>
                        <th>Familia</th>
                        <th>Total KG</th>
                        <th>Total LB</th>
                        <th>Importe EUR</th>
                        <th>Importe USD</th>
                        <th>Precio CIF (USD/LB)</th>
                        <th>Precio Bodega (USD/LB)</th>
                        <th>Total Familia (USD)</th>
                    </tr>
                </thead>
                <tbody>
                    {{-- Filas generadas automáticamente por JS --}}
                </tbody>
            </table>
        </fieldset>


        {{-- ============================================================
             SECCIÓN: TOTALES GENERALES DE FACTURA
             - Totales globales calculados por JS
             - Estos valores se usan para presentar al usuario
             - En el backend se recalculan por seguridad
           ============================================================ --}}
        <fieldset class="form-section">
            <legend>Totales de Factura</legend>

            <div class="form-group">
                <label>Peso total (KG)</label>
                <span id="lblTotalKG">0.0000</span>
            </div>

            <div class="form-group">
                <label>Peso total (LB)</label>
                <span id="lblTotalLB">0.0000</span>
            </div>

            <div class="form-group">
                <label>Importe productos (USD)</label>
                <span id="lblTotalUSD">0.00</span>
            </div>

            <div class="form-group">
                <label>Costos adicionales (USD)</label>
                <span id="lblCostosUSD">0.00</span>
            </div>

            <div class="form-group">
                <label>Costo adicional por libra (USD/LB)</label>
                <span id="lblCostoPorLibra">0.0000</span>
            </div>

            <div class="form-group">
                <label>Total factura (USD)</label>
                <span id="lblTotalFactura">0.00</span>
            </div>
        </fieldset>


        {{-- ============================================================
             ACCIONES
             - Guardar y cancelar
             - Botones centrados
           ============================================================ --}}
        <div class="acciones-form">
            <button type="submit" class="btn-guardar">
                Guardar compra
            </button>

            <a href="{{ route('compras.lista') }}" class="btn-cancelar">
                Cancelar
            </a>
        </div>


        {{-- ============================================================
             DATOS AUXILIARES PARA JS
             - Aquí se inyectan las opciones de costos para la tabla dinámica
             - El JS toma window.costosOptions para armar el <select> de cada fila
           ============================================================ --}}
        <script>
            window.costosOptions = `
                @foreach($costos as $c)
                    <option value="{{ $c->id_costo }}">{{ $c->nombre }}</option>
                @endforeach
            `;
        </script>
    
    </form>
</div>

{{-- IMPORTAR JS --}}
@vite(['resources/js/app.js'])
@endsection




