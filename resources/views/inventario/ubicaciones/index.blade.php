@extends('layouts.app')

@section('content')
<div class="erp-section">
    <h2 class="erp-title">
        <i class="fa-solid fa-warehouse"></i> Ubicaciones
    </h2>

    @if(session('success'))
        <div class="form-alert">{{ session('success') }}</div>
    @endif

    {{-- BUSCADOR --}}
    <form method="GET" action="{{ route('inventario.ubicaciones.index') }}" class="erp-search-form">
        <div class="search-row">
            <div>
                <label>Nombre:</label>
                <input type="text" name="nombre" value="{{ $filtroNombre }}" class="search-input">
            </div>

            <button class="btn-primary" type="submit">
                <i class="fa-solid fa-magnifying-glass"></i> Buscar
            </button>

            <a class="btn-secondary" href="{{ route('inventario.ubicaciones.index') }}">
                Limpiar
            </a>
        </div>
    </form>

    {{-- NUEVO --}}
    <div class="erp-actions">
        <a class="btn-primary" href="{{ route('inventario.ubicaciones.create') }}">
            ➕ Nueva Ubicación
        </a>
    </div>

    <p class="erp-total">Total: {{ $ubicaciones->total() }}</p>

    {{-- TABLA --}}
    <div class="erp-table-container">
        <table class="erp-table">
            <thead>
                <tr>
                    <th style="width:80px;">ID</th>
                    <th style="width:250px;">Nombre</th>
                    <th>Descripción</th>
                    <th style="width:120px; text-align:center;">Estado</th>
                    <th style="width:140px; text-align:center;">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($ubicaciones as $u)
                    <tr>
                        <td>{{ $u->id_ubicacion }}</td>
                        <td><strong>{{ $u->nombre }}</strong></td>
                        <td>{{ $u->descripcion ?? '-' }}</td>
                        <td style="text-align:center;">
                            <span class="badge {{ $u->inactivo == 0 ? 'bg-success' : 'bg-danger' }}">
                                {{ $u->inactivo == 0 ? 'Activo' : 'Inactivo' }}
                            </span>
                        </td>
                        <td class="erp-actions-cell">
                            <a href="{{ route('inventario.ubicaciones.edit', $u->id_ubicacion) }}" class="btn-table btn-edit">
                                <i class="fa-solid fa-pen"></i>
                            </a>

                            <form action="{{ route('inventario.ubicaciones.destroy', $u->id_ubicacion) }}" method="POST" style="display:inline;">
                                @csrf
                                @method('DELETE')
                                <button class="btn-table btn-delete" onclick="return confirm('¿Inactivar esta ubicación?');">
                                    <i class="fa-solid fa-ban"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="no-results">Sin resultados.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- PAGINACIÓN --}}
    <div class="erp-pagination">
        {{ $ubicaciones->appends(['nombre' => $filtroNombre])->links() }}
    </div>
</div>
@endsection
