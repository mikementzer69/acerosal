@extends('layouts.app')

@section('content')
<div class="container">
    <h2 class="titulo-modulo">Gestión de Compras</h2>

    {{-- FILTROS --}}
    <form method="GET" action="{{ route('compras.lista') }}" class="filtros">
        <input type="text" name="numero_factura" placeholder="Buscar por número de factura"
               value="{{ request('numero_factura') }}">

        <select name="id_proveedor">
            <option value="0">-- Todos los Proveedores --</option>
            @foreach($proveedores as $prov)
                <option value="{{ $prov->id_proveedor }}"
                    {{ request('id_proveedor') == $prov->id_proveedor ? 'selected' : '' }}>
                    {{ $prov->nombre }}
                </option>
            @endforeach
        </select>

        <button type="submit" class="btn-filtrar">Filtrar</button>
        <a href="{{ route('compras.nueva') }}" class="btn-nueva">+ Nueva Compra</a>
    </form>

    {{-- MENSAJES --}}
    @if(session('ok'))
    <div class="mensaje-ok">{{ session('ok') }}</div>
    @endif

    {{-- TABLA --}}
    <table class="tabla">
        <thead>
            <tr>
                <th>Proveedor</th>
                <th>Número Factura</th>
                <th>Fecha Ingreso</th>
                <th>Fecha Emisión</th>
                <th>Peso Total (Lb.)</th>
                <th>Total Factura</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @forelse($compras as $compra)
            <tr class="{{ $compra->nueva_compra ? 'resaltado' : '' }}">
                <td>{{ $compra->proveedor->nombre }}</td>
                <td>{{ $compra->numero_factura }}</td>
                <td>{{ $compra->fecha_ingreso }}</td>
                <td>{{ $compra->fecha_emision_factura }}</td>
                <td>{{ number_format($compra->peso_total_libras, 4) }}</td>
                <td>${{ number_format($compra->total_factura, 2) }}</td>
                <td class="acciones">
                    <a href="{{ route('compras.detalle', $compra->id_compra) }}" class="btn-ver">Ver Detalle</a>

                    <form action="{{ route('compras.eliminar', $compra->id_compra) }}" method="POST"
                          onsubmit="return confirm('¿Seguro que deseas anular esta compra?');">
                        @csrf
                        <button type="submit" class="btn-eliminar">Anular</button>
                    </form>
                </td>
            </tr>
            @empty
            <tr><td colspan="7">No hay compras registradas.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
