@extends('layouts.app')

@section('content')

<div class="erp-section">

    <h2 class="erp-title">
        <i class="fa-solid fa-layer-group"></i> Familias (solo lectura)
    </h2>

    @if(session('msg'))
        <div class="form-alert">{{ session('msg') }}</div>
    @endif

    {{-- BUSCADOR --}}
    <form method="GET" action="{{ route('familia.lista') }}" class="erp-search-form">
        <div class="search-row">
            <div>
                <label>Nombre:</label>
                <input type="text"
                       name="nombre"
                       value="{{ $filtroNombre }}"
                       class="search-input">
            </div>

            <button class="btn-primary" type="submit">
                <i class="fa-solid fa-magnifying-glass"></i> Buscar
            </button>

            <a class="btn-secondary" href="{{ route('familia.lista') }}">
                Limpiar
            </a>
        </div>
    </form>

    {{-- NUEVO --}}
    <div class="erp-actions">
        <a class="btn-primary" href="{{ route('familia.nueva') }}">
            ➕ Nueva Familia
        </a>
    </div>

    <p class="erp-total">Total: {{ $familias->total() }}</p>

    {{-- TABLA --}}
    <div class="erp-table-container">
        <table class="erp-table">
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Descripción</th>
                    <th style="width:120px; text-align:center;">Acciones</th>
                </tr>
            </thead>

            <tbody>
                @forelse($familias as $f)
                    <tr>
                        <td>{{ $f->nombre }}</td>
                        <td>{!! nl2br(e($f->descripcion)) !!}</td>

                        <td class="erp-actions-cell">

                            {{-- EDITAR --}}
                            <a href="{{ route('familia.editar', $f->idfamilia) }}"
                               class="btn-table btn-edit">
                                <i class="fa-solid fa-pen"></i>
                            </a>

                            {{-- ELIMINAR --}}
                            <form action="{{ route('familia.eliminar', $f->idfamilia) }}"
                                  method="POST"
                                  style="display:inline;">
                                @csrf
                                @method('DELETE')

                                <button class="btn-table btn-delete"
                                        onclick="return confirm('¿Eliminar esta familia?');">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </form>

                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="no-results">Sin resultados.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- PAGINACIÓN --}}
    <div class="erp-pagination">
        {{ $familias->appends(['nombre' => $filtroNombre])->links() }}
    </div>

</div>

@endsection
