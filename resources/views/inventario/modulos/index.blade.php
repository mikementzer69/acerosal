@extends('layouts.app')

@section('content')

<div class="erp-section">

    <h2 class="erp-title">
        <i class="fa-solid fa-diagram-project"></i> Módulos del Sistema
    </h2>

    @if(session('msg'))
        <div class="form-alert">{{ session('msg') }}</div>
    @endif

    {{-- BUSCADOR --}}
    <form method="GET" action="{{ route('modulo.lista') }}" class="erp-search-form">
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

            <a class="btn-secondary" href="{{ route('modulo.lista') }}">Limpiar</a>
        </div>
    </form>

    {{-- NUEVO --}}
    <div class="erp-actions">
        <a class="btn-primary" href="{{ route('modulo.nuevo') }}">
            ➕ Nuevo Módulo
        </a>
    </div>

    <p class="erp-total">Total: {{ $modulos->total() }}</p>

    {{-- TABLA --}}
    <div class="erp-table-container">
        <table class="erp-table">
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Descripción</th>
                    <th>Módulo Padre</th>
                    <th style="width:120px; text-align:center;">Acciones</th>
                </tr>
            </thead>

            <tbody>
                @forelse($modulos as $m)
                    <tr>
                        <td>{{ $m->nombre }}</td>
                        <td>{!! nl2br(e($m->descripcion)) !!}</td>
                        <td>{{ $m->padre->nombre ?? '—' }}</td>

                        <td class="erp-actions-cell">

                            {{-- EDITAR --}}
                            <a href="{{ route('modulo.editar', $m->idmodulos) }}"
                               class="btn-table btn-edit">
                               <i class="fa-solid fa-pen"></i>
                            </a>

                            {{-- ELIMINAR --}}
                            <form action="{{ route('modulo.eliminar', $m->idmodulos) }}"
                                  method="POST" style="display:inline;">
                                @csrf
                                @method('DELETE')

                                <button class="btn-table btn-delete"
                                    onclick="return confirm('¿Eliminar este módulo?');">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </form>

                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="no-results">Sin resultados.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="erp-pagination">
        {{ $modulos->appends(['nombre' => $filtroNombre])->links() }}
    </div>

</div>

@endsection
