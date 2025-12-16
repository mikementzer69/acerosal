@extends('layouts.app')

@section('content')
<div class="container">

    <h2 class="titulo-modulo">Detalle de Compra</h2>

    {{-- DATOS GENERALES --}}
    <div class="bloque">
        <h3>Información General</h3>

        <p><strong>Proveedor:</strong> {{ $compra->proveedor->nombre }}</p>
        <p><strong>Número Factura:</strong> {{ $compra->numero_factura }}</p>
        <p><strong>Fecha Ingreso:</strong> {{ $compra->fecha_ingreso }}</p>
        <p><strong>Fecha Emisión:</strong> {{ $compra->fecha_emision_factura }}</p>
        <p><strong>Tasa de Cambio:</strong> {{ number_format($compra->tasa_cambio,4) }}</p>
    </div>

    {{-- PRODUCTOS --}}
    <div class="bloque">
        <h3>Productos Comprados</h3>

        <table class="tabla">
            <thead>
                <tr>
                    <th>Producto</th>
                    <th>Cantidad</th>
                    <th>Peso KG</th>
                    <th>Peso LB</th>
                    <th>Precio EUR</th>
                    <th>Precio USD</th>
                    <th>Importe EUR</th>
                    <th>Importe USD</th>
                </tr>
            </thead>
            <tbody>
                @foreach($compra->compraProductos as $p)
                <tr>
                    <td>{{ $p->producto->descripcion }}</td>
                    <td>{{ $p->cantidad }}</td>
                    <td>{{ number_format($p->peso_kg,4) }}</td>
                    <td>{{ number_format($p->peso_libra,4) }}</td>
                    <td>{{ number_format($p->precio_kg_eu,2) }}</td>
                    <td>{{ number_format($p->precio_kg_usd,2) }}</td>
                    <td>{{ number_format($p->importe_eu,2) }}</td>
                    <td>{{ number_format($p->importe_dolares,2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- COSTOS --}}
    <div class="bloque">
        <h3>Costos Adicionales</h3>

        @if(count($compra->compraCostos))
        <table class="tabla">
            <thead>
                <tr>
                    <th>Descripción</th>
                    <th>Valor EUR</th>
                    <th>Valor USD</th>
                </tr>
            </thead>
            <tbody>
                @foreach($compra->compraCostos as $c)
                <tr>
                    <td>{{ $c->costo->nombre }}</td>
                    <td>{{ number_format($c->valor_eu,2) }}</td>
                    <td>{{ number_format($c->valor_usd,2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @else
        <p>No hay costos adicionales.</p>
        @endif
    </div>

    {{-- TOTALES --}}
    <div class="totales">
        <p><strong>Peso Total KG:</strong> {{ number_format($compra->peso_total_kg,4) }}</p>
        <p><strong>Peso Total LB:</strong> {{ number_format($compra->peso_total_libras,4) }}</p>
        <p><strong>Total Costos USD:</strong> {{ number_format($compra->total_costos_adicionales,2) }}</p>
        <p><strong>Total Factura USD:</strong> {{ number_format($compra->total_factura,2) }}</p>
    </div>

    <a href="{{ route('compras.lista') }}" class="btn-volver">Volver</a>

</div>
@endsection