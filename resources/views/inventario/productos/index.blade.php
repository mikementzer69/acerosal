@extends('layouts.app')

@section('content')
<style>
    /* Estilo para la paginación del ERP */
.erp-pagination {
    margin-top: 25px;
    display: flex;
    justify-content: center;
}

/* Ajuste para los elementos de la lista que genera Laravel */
.erp-pagination nav {
    display: flex;
    flex-direction: column;
    align-items: center;
}

.erp-pagination .pagination {
    display: flex;
    list-style: none;
    gap: 5px;
    padding: 0;
}

.erp-pagination .page-link {
    background-color: #1f2a3a !important; /* El fondo de tu ERP */
    border: 1px solid #4a5568 !important;   /* Borde sutil */
    color: #ffffff !important;              /* Texto blanco */
    padding: 8px 14px;
    border-radius: 6px;
    text-decoration: none;
    transition: all 0.3s ease;
}

.erp-pagination .page-item.active .page-link {
    background-color: #3b82f6 !important; /* El azul de tus botones */
    border-color: #3b82f6 !important;
    font-weight: bold;
    box-shadow: 0 0 10px rgba(59, 130, 246, 0.4);
}

.erp-pagination .page-link:hover {
    background-color: #2d3a4f !important;
    color: #4ade80 !important; /* Verde al pasar el mouse */
    border-color: #4ade80 !important;
}

/* Arreglo para el texto de "Showing X to Y..." */
.erp-pagination .text-muted,
.erp-pagination p {
    color: #e8edf5 !important;
    font-size: 0.85em;
    margin-bottom: 15px !important;
}

/* Arreglo para las flechitas si salen gigantes */
.erp-pagination svg {
    width: 20px;
    height: 20px;
}
</style>

@php
    $codigo      = $codigo      ?? request('codigo', '');
    $descripcion = $descripcion ?? request('descripcion', '');
    $idfamilia   = $idFamilia   ?? request('id_familia', '');
    $idUbicacion = $idUbicacion ?? request('id_ubicacion', '');
@endphp

<div class="erp-section">

    <h2 class="erp-title">
        <i class="fa-solid fa-box"></i> Productos
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
                <label>Calidad:</label>
                <select name="familia" class="search-input">
                    <option value="">Todas</option>
                    @foreach($familias as $f)
                        <option value="{{ $f->id_familia }}"
                            {{ $idfamilia == $f->id_familia ? 'selected' : '' }}>
                            {{ $f->nombre }}
                        </option>
                    @endforeach
                </select>
            </div>

                  {{-- NUEVO FILTRO DE UBICACIÓN --}}
            <div>
                <label>Ubicación:</label>
                <select name="id_ubicacion" class="search-input">
                    <option value="">Todas</option>
                    @foreach($ubicaciones as $u)
                        <option value="{{ $u->id_ubicacion }}" {{ $idUbicacion == $u->id_ubicacion ? 'selected' : '' }}>
                            {{ $u->nombre }}
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
                    <th>Calidad</th>
                    <th>Ubicación</th>
                    <th>Unidad Long.</th>
                    <th>Unidad Peso</th>
                    <th>MM</th>
                    <th>Pulgadas</th>
                    <th>Tolerancia</th>
                    <th>Peso LB/MTS</th>
                    <th>Stock Metros</th>
                    <th>Precio sin IVA</th>
                    <!--<th>Precio Fijo</th>-->
                    <th>Precio Unitario Bodega</th>
                    <th style="width:120px; text-align:center;">Acciones</th>
                </tr>
            </thead>

            <tbody>
                @forelse($productos as $p)
                    <tr>
                        <td>{{ $p->codigo }}</td>
                        <td>{{ $p->descripcion }}</td>
                        <td>{{ $p->familia_nombre }}</td>
                        <td>
                            @if($p->ubicacion_nombre)
                                <span style="color: #60a5fa; font-weight: bold;">
                                    <i class="fa-solid fa-location-dot" style="font-size: 0.8em;"></i> {{ $p->ubicacion_nombre }}
                                </span>
                            @else
                                <span style="color: #6b7280; font-style: italic;">No asignada</span>
                            @endif
                        </td>
                        <td>{{ $p->unidad_medida_longitud }}</td>
                        <td>{{ $p->unidad_medida_peso }}</td>
                        <td>{{ $p->milimetros }}</td>
                        <td>{{ $p->pulgadas }}</td>
                        <td>{{ $p->tolerancia }}</td>
                        <td>{{ $p->peso_lb_mts }}</td>
                        <td>{{ number_format($p->stock_metros, 2) }}</td>
                        <td>${{ number_format($p->precio_venta_sin_iva, 2) }}</td>
                        <!--<td>{{ $p->precio_fijo ? 'Sí' : 'No' }}</td>-->
                        <td>{{ $p->precio_unitario_bodega }}</td>

                        <td class="erp-actions-cell">

                            {{-- EDITAR --}}
                            <a href="{{ route('producto.editar', $p->id_producto) }}"
                                class="btn-table btn-edit">
                                <i class="fa-solid fa-pen"></i>
                            </a>

                            {{-- BORRAR --}}
                            <form action="{{ route('producto.eliminar', $p->id_producto) }}"
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
                        <td colspan="12" class="no-results">Sin resultados.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- PAGINACIÓN --}}
    <div class="erp-pagination">
        {!! str_replace(
            ['Showing', 'to', 'of', 'results', 'Previous', 'Next'],
            ['Mostrando', 'al', 'de', 'resultados', 'Anterior', 'Siguiente'],
            $productos->appends([
                'codigo'      => $codigo,
                'descripcion' => $descripcion,
                'id_familia'  => $idfamilia,
                'id_ubicacion'=> $idUbicacion,
            ])
            ->onEachSide(1)
            ->links()
        ) !!}
    </div>

</div>

@endsection
