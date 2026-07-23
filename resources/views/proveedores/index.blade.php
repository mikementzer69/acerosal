@extends('layouts.app')

@section('content')

<div class="erp-section">

    <h2 class="erp-title">
        <i class="fa-solid fa-truck"></i> Proveedores
    </h2>

    @if(session('msg'))
        <div class="form-alert">{{ session('msg') }}</div>
    @endif

    {{-- BUSCADOR --}}
    <form method="GET" action="{{ route('proveedores.index') }}" class="erp-search-form">
        <div class="search-row">

            <div>
                <label>Nombre:</label>
                <input type="text"
                       name="nombre"
                       value="{{ request('nombre') }}"
                       class="search-input">
            </div>

            <div>
                <label>Origen:</label>
                <input type="text"
                       name="origen"
                       value="{{ request('origen') }}"
                       class="search-input">
            </div>

            <button class="btn-primary" type="submit">
                <i class="fa-solid fa-magnifying-glass"></i> Buscar
            </button>

            <a class="btn-secondary" href="{{ route('proveedores.index') }}">
                Limpiar
            </a>

        </div>
    </form>

    {{-- NUEVO --}}
    <div class="erp-actions">
        <a class="btn-primary" href="{{ route('proveedores.create') }}">
            ➕ Nuevo Proveedor
        </a>
    </div>

    <p class="erp-total">Total: {{ $proveedores->total() }}</p>

    {{-- TABLA --}}
    <div class="erp-table-container">
        <table class="erp-table">
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Origen</th>
                    <th>Dirección</th>
                    <th style="width:120px; text-align:center;">Acciones</th>
                </tr>
            </thead>

            <tbody>
                @forelse($proveedores as $p)
                    <tr>
                        <td>{{ $p->nombre }}</td>
                        <td>{{ $p->origen ?? '-' }}</td>
                        <td>{{ $p->direccion ?? '-' }}</td>

                        <td class="erp-actions-cell">

                            {{-- EDITAR --}}
                            <a href="{{ route('proveedores.edit', $p->id_proveedor) }}"
                               class="btn-table btn-edit"
                               title="Editar">
                                <i class="fa-solid fa-pen"></i>
                            </a>

                            {{-- ELIMINAR --}}
                            <form action="{{ route('proveedores.destroy', $p->id_proveedor) }}"
                                  method="POST"
                                  style="display:inline;">
                                @csrf
                                @method('DELETE')

                                <button class="btn-table btn-delete"
                                        title="Eliminar"
                                        onclick="return confirm('¿Eliminar este proveedor?');">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </form>

                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="no-results">
                            Sin proveedores registrados.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- PAGINACIÓN --}}
    <div class="erp-pagination">
        {{ $proveedores->links() }}
    </div>

</div>

@endsection
