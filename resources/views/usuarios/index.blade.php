@extends('layouts.app')

@section('content')


<div class="erp-section">

    <h2 class="erp-title">
        <i class="fa-solid fa-users"></i> Usuarios
    </h2>

    @if(session('msg'))
        <div class="form-alert">{{ session('msg') }}</div>
    @endif

    {{-- ACCIONES --}}
    <div class="erp-actions">
        @if(Auth::user()->id_rol == 1)
        <a class="btn-primary" href="{{ route('usuarios.create') }}">
            ➕ Nuevo Usuario
        </a>
        @endif
    </div>

    <p class="erp-total">Total: {{ $usuarios->total() }}</p>

    {{-- TABLA --}}
    <div class="erp-table-container">
        <table class="erp-table">
            <thead>
                <tr>
                    <th>Usuario</th>
                    <th>Nombre</th>
                    <th>Email</th>
                    <th>Rol</th> {{-- ✨ Nueva Columna --}}
                    <th>Celular</th>
                    <th style="width:120px; text-align:center;">Acciones</th>
                </tr>
            </thead>

            <tbody>
                @forelse($usuarios as $u)
                    <tr>
                        <td>{{ $u->username }}</td>
                        <td>{{ $u->nombre }} {{ $u->apellidos }}</td>
                        <td>{{ $u->email }}</td>

                        {{-- ✨ Mostramos el nombre del rol usando la relación de Spatie --}}
                    <td>
                        @if($u->rol)
                            <span class="badge-role">
                                {{ strtoupper($u->rol->nombre ?? $u->rol->name) }}
                            </span>
                        @else
                            {{-- Si llega aquí, es porque el id_rol en la BD es NULL --}}
                            <span class="text-muted">Sin Rol</span>
                        @endif
                    </td>

                        <td>{{ $u->celular ?: '-' }}</td>

                        <td class="erp-actions-cell">
                            {{-- EDITAR --}}
                            <a href="{{ route('usuarios.edit', $u->id_usuario) }}"
                               class="btn-table btn-edit"
                               title="Editar">
                                <i class="fa-solid fa-pen"></i>
                            </a>

                            {{-- ELIMINAR --}}
                            <form action="{{ route('usuarios.destroy', $u->id_usuario) }}"
                                  method="POST"
                                  style="display:inline;">
                                @csrf
                                @method('DELETE')

                                <button class="btn-table btn-delete"
                                        title="Desactivar"
                                        onclick="return confirm('¿Desactivar este usuario?');">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        {{-- ✨ Ajustado a colspan="6" porque ahora hay 6 columnas --}}
                        <td colspan="6" class="no-results">
                            Sin usuarios registrados.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- PAGINACIÓN --}}
    <div class="erp-pagination">
        {{ $usuarios->links() }}
    </div>

</div>

@endsection
