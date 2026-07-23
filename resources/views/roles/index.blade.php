@extends('layouts.app')

@section('content')

<div class="erp-section">

    <h2 class="erp-title">
        <i class="fa-solid fa-user-shield"></i> Roles
    </h2>

    {{-- MENSAJE --}}
    @if(session('msg'))
        <div class="form-alert">{{ session('msg') }}</div>
    @endif

    {{-- BUSCADOR --}}
    <form method="GET" action="{{ route('roles.index') }}" class="erp-search-form">
        <div class="search-row">
            <div>
                <label>Nombre:</label>
                <input type="text"
                       name="name"
                       value="{{ request('name') }}"
                       class="search-input">
            </div>

            <button class="btn-primary" type="submit">
                <i class="fa-solid fa-magnifying-glass"></i> Buscar
            </button>

            <a class="btn-secondary" href="{{ route('roles.index') }}">
                Limpiar
            </a>
        </div>
    </form>

    {{-- NUEVO --}}
    <div class="erp-actions">
        <a class="btn-primary" href="{{ route('roles.create') }}">
            ➕ Nuevo Rol
        </a>
    </div>

    <p class="erp-total">Total: {{ $roles->total() }}</p>

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
                @forelse($roles as $rol)
                    <tr>
                        <td>{{ $rol->name }}</td>
                        <td>{{ $rol->guard_name ?: '-' }}</td>

                        <td class="erp-actions-cell">

                            {{-- EDITAR --}}
                            <a href="{{ route('roles.edit', $rol->id_rol) }}"
                               class="btn-table btn-edit"
                               title="Editar">
                                <i class="fa-solid fa-pen"></i>
                            </a>

                            {{-- ELIMINAR (LÓGICO) --}}
                            <form action="{{ route('roles.destroy', $rol->id_rol) }}"
                                  method="POST"
                                  style="display:inline;">
                                @csrf
                                @method('DELETE')

                                <button class="btn-table btn-delete"
                                        title="Desactivar"
                                        onclick="return confirm('¿Desactivar este rol?');">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </form>

                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="no-results">Sin roles registrados.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- PAGINACIÓN --}}
    <div class="erp-pagination">
        {{ $roles->appends(['nombre' => request('nombre')])->links() }}
    </div>

</div>

@endsection
