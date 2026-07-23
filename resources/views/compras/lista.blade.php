t
@extends('layouts.app')

@section('content')
<div class="container">
    <h2 class="titulo-modulo">Listado de Compras</h2>

    @if(session('success'))
        <div class="alerta-exito">
            {{ session('success') }}
        </div>
    @endif

    <table class="tabla-datos">
        <thead>
            <tr>
                <th>ID</th>
                <th>Factura</th>
                <th>Proveedor</th>
                <th>Fecha</th>
                <th>Total USD</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @forelse($compras as $c)
                <tr>
                    <td>{{ $c->id_compra }}</td>
                    <td>{{ $c->numero_factura }}</td>
                    <td>{{ $c->proveedor->nombre ?? '-' }}</td>
                    <td>{{ $c->fecha_ingreso }}</td>
                    <td>{{ number_format($c->total_factura, 2) }}</td>
                    <td>
                        <a href="{{ route('compras.detalle', $c->id_compra) }}">
                            Ver
                        </a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6">No hay compras registradas.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
