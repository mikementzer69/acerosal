@extends('layouts.app')

@section('content')
@php
    $codigo      = $codigo      ?? request('codigo', '');
    $descripcion = $descripcion ?? request('descripcion', '');
    $idfamilia   = $idFamilia   ?? request('id_familia', '');
@endphp


<div class="erp-section">

    <h2 class="erp-title">
        <i class="fa-solid fa-box"></i> Productos (solo lectura)
    </h2>

    {{-- ALERTA --}}
    @if(session('msg'))
        <div class="form-alert">{{ session('msg') }}</div>
    @endif

    {{-- BUSCADOR --}}
    <form method="GET" action="{{ route('producto.lista') }}" class="erp-search-form">

        <div class="search-row">

            <div>
                <label>Código:</label>
                <input type="text"
                       name="codigo"
                       value="{{ $codigo }}"
                       class="search-input">
            </div>

            <div>
                <label>Descripción:</label>
                <input type="text"
                       name="descripcion"
                       value="{{ $descripcion }}"
                       class="search-input">
            </div>

            <div>
                <label>Familia:</label>
                <select name="id_familia" class="search-input">
                    <option value="">Todas</option>
                    @foreach($familias as $f)
                        <option value="{{ $f->idfamilia }}"
                            {{ $idfamilia == $f->idfamilia ? 'selected' : '' }}>
                            {{ $f->nombre }}
                        </option>
                    @endforeach
                </select>
            </div>

            <button class="btn-primary" type="submit">
                <i class="fa-solid fa-magnifying-glass"></i> Buscar
            </button>

            <a class="btn-secondary" href="{{ route('producto.lista') }}">
                Limpiar
            </a>
        </div>

    </form>

    {{-- NUEVO --}}
    <div class="erp-actions">
        <a class="btn-primary" href="{{ route('producto.nuevo') }}">
            ➕ Nuevo Producto
        </a>
    </div>

    {{-- TOTAL --}}
    <p class="erp-total">Total: {{ $productos->total() }}</p>

    {{-- TABLA --}}
    <div class="erp-table-container">
        <table class="erp-table">
            <thead>
                <tr>
                    <th>Código</th>
                    <th>Descripción</th>
                    <th>Familia</th>
                    <th>Unidad</th>
                    <th>MM</th>
                    <th>Pulgadas</th>
                    <th>Tolerancia</th>
                    <th>Peso LB/MTS</th>
                    <th>Precio sin IVA</th>
                    <th>Precio Fijo</th>
                    <th style="width:120px; text-align:center;">Acciones</th>
                </tr>
            </thead>

            <tbody>
                @forelse($productos as $p)
                    <tr>
                        <td>{{ $p->codigo }}</td>
                        <td>{{ $p->descripcion }}</td>
                        <td>{{ $p->familianombre }}</td>
                        <td>{{ $p->unidad_medida }}</td>
                        <td>{{ $p->milimetros }}</td>
                        <td>{{ $p->pulgadas }}</td>
                        <td>{{ $p->tolerancia }}</td>
                        <td>{{ $p->peso_lb_mts }}</td>
                        <td>${{ number_format($p->precio_venta_sin_iva, 2) }}</td>
                        <td>{{ $p->precio_fijo ? 'Sí' : 'No' }}</td>

                        <td class="erp-actions-cell">

                            {{-- EDITAR --}}
                            <a href="{{ route('producto.editar', $p->idproductos) }}"
                                class="btn-table btn-edit">
                                <i class="fa-solid fa-pen"></i>
                            </a>

                            {{-- BORRAR --}}
                            <form action="{{ route('producto.eliminar', $p->idproductos) }}"
                                  method="POST"
                                  style="display:inline;">
                                @csrf
                                @method('DELETE')

                                <button class="btn-table btn-delete"
                                        onclick="return confirm('¿Seguro que deseas eliminar este producto?');">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </form>

                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="11" class="no-results">Sin resultados.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- PAGINACIÓN --}}
    <div class="erp-pagination">
        {{ $productos
            ->appends([
                'codigo'      => $codigo,
                'descripcion' => $descripcion,
                'id_familia'  => $idfamilia,
            ])->links() }}
    </div>

</div>

@endsection
