@extends('layouts.app')

@section('content')
<div class="container">

    <h2 class="titulo-modulo">Compras Anuladas</h2>

    <table class="tabla">
        <thead>
            <tr>
                <th>Proveedor</th>
                <th>Número Factura</th>
                <th>Fecha Ingreso</th>
                <th>Fecha Emisión</th>
                <th>Peso Total LB</th>
                <th>Total Factura USD</th>
            </tr>
        </thead>
        <tbody>
            @forelse($compras as $compra)
            <tr>
                <td>{{ $compra->proveedor->nombre ?? 'N/A' }}</td>
                <td>{{ $compra->numero_factura }}</td>
                <td>{{ $compra->fecha_ingreso }}</td>
                <td>{{ $compra->fecha_emision_factura }}</td>
                <td>{{ number_format($compra->peso_total_libras,4) }}</td>
                <td>${{ number_format($compra->total_factura,2) }}</td>
            </tr>
            @empty
            <tr><td colspan="6">No hay compras anuladas.</td></tr>
            @endforelse
        </tbody>
    </table>

    <a href="{{ route('compras.lista') }}" class="btn-volver">Volver</a>
</div>
@endsection
