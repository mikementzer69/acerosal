@extends('layouts.app')

@section('content')

<div class="erp-section">

    <h2 class="erp-title">
        <i class="fa-solid fa-list-check"></i> Tareas del Sistema
    </h2>

    @if(session('msg'))
        <div class="form-alert">{{ session('msg') }}</div>
    @endif

    {{-- BUSCADOR --}}
    <form method="GET" action="{{ route('tarea.lista') }}" class="erp-search-form">
        <div class="search-row">
            <div>
                <label>Nombre:</label>
                <input type="text"
                       name="nombre"
                       value="{{ $filtroNombre }}"
                       class="search-input">
            </div>

            <button class="btn-primary" type="submit">Buscar</button>

            <a class="btn-secondary" href="{{ route('tarea.lista') }}">
                Limpiar
            </a>
        </div>
    </form>

    {{-- NUEVO --}}
    <div class="erp-actions">
        <a class="btn-primary" href="{{ route('tarea.nueva') }}">
            ➕ Nueva Tarea
        </a>
    </div>

    <p class="erp-total">Total: {{ $tareas->total() }}</p>

    {{-- TABLA --}}
    <div class="erp-table-container">
        <table class="erp-table">
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Descripción</th>
                    <th>Ruta</th>
                    <th>Módulo</th>
                    <th>Orden</th>
                    <th>Visible</th>
                    <th style="width:120px;">Acciones</th>
                </tr>
            </thead>

            <tbody>
                @forelse($tareas as $t)
                    <tr>
                        <td>{{ $t->nombre }}</td>
                        <td>{{ $t->descripcion }}</td>
                        <td>{{ $t->ruta }}</td>
                        <td>{{ $t->modulo->nombre ?? '—' }}</td>
                        <td>{{ $t->orden }}</td>
                        <td>{{ $t->visible ? 'Sí' : 'No' }}</td>

                        <td class="erp-actions-cell">

                            {{-- EDITAR --}}
                            <a href="{{ route('tarea.editar', $t->id_tarea) }}"
                                class="btn-table btn-edit">
                                <i class="fa-solid fa-pen"></i>
                            </a>

                            {{-- ELIMINAR --}}
                            <form action="{{ route('tarea.eliminar', $t->id_tarea) }}"
                                  method="POST"
                                  style="display:inline;">
                                @csrf
                                @method('DELETE')

                                <button class="btn-table btn-delete"
                                        onclick="return confirm('¿Eliminar esta tarea?');">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="no-results">Sin resultados.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- PAGINACIÓN --}}
    <div class="erp-pagination">
        {{ $tareas->appends(['nombre' => $filtroNombre])->links() }}
    </div>

</div>

@endsection
