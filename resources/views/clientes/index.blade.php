@extends('layouts.app')

@section('content')

<div class="erp-section">

    <h2 class="erp-title">
        <i class="fa-solid fa-users"></i> Directorio de Clientes
    </h2>

    @if(session('success'))
        <div class="form-alert" style="background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb;">
            <i class="fa-solid fa-check-circle"></i> {{ session('success') }}
        </div>
    @endif

    {{-- BUSCADOR --}}
    <form method="GET" action="{{ route('clientes.index') }}" class="erp-search-form">
        <div class="search-row">
            <div>
                <label>Buscar Cliente:</label>
                <input type="text"
                       name="busqueda"
                       value="{{ request('busqueda') }}"
                       class="search-input"
                       placeholder="Código, Nombre o NIT...">
            </div>

            <button class="btn-primary" type="submit">
                <i class="fa-solid fa-magnifying-glass"></i> Buscar
            </button>

            <a class="btn-secondary" href="{{ route('clientes.index') }}">
                <i class="fa-solid fa-eraser"></i> Limpiar
            </a>
        </div>
    </form>

    {{-- NUEVO --}}
    <div class="erp-actions">
        <a class="btn-primary" href="{{ route('clientes.create') }}">
            <i class="fa-solid fa-user-plus"></i> Nuevo Cliente
        </a>
    </div>

    {{-- CONTADOR (Validamos si es paginación o colección) --}}
    <p class="erp-total">
        Total Registros: {{ $clientes instanceof \Illuminate\Pagination\LengthAwarePaginator ? $clientes->total() : $clientes->count() }}
    </p>

    {{-- TABLA --}}
    <div class="erp-table-container">
        <table class="erp-table">
            <thead>
                <tr>
                    <th>Código</th>
                    <th>Nombre Fiscal / Razón Social</th>
                    <th>Nombre Comercial</th>
                    <th>Teléfono</th>
                    <th>Tipo</th>
                    <th>Estado</th>
                    <th style="width:120px; text-align:center;">Acciones</th>
                </tr>
            </thead>

            <tbody>
                @forelse($clientes as $cliente)
                    <tr>
                        <td style="font-weight: bold;">{{ $cliente->codigo }}</td>
                        <td>{{ $cliente->nombre }}</td>

                        {{-- Nombre Comercial (Gris si está vacío) --}}
                        <td>
                            @if($cliente->nombre_comercial)
                                {{ $cliente->nombre_comercial }}
                            @else
                                <span style="color:#ccc;">---</span>
                            @endif
                        </td>

                        <td>{{ $cliente->telefono ?? '---' }}</td>

                        {{-- TIPO DE CLIENTE --}}
                        <td>
                            @if($cliente->tipo_cliente == 'JURIDICO')
                                <span style="background:#e3f2fd; color:#0d47a1; padding:3px 8px; border-radius:10px; font-size:0.85em;">
                                    🏢 Empresa
                                </span>
                            @else
                                <span style="background:#f3e5f5; color:#4a148c; padding:3px 8px; border-radius:10px; font-size:0.85em;">
                                    👤 Natural
                                </span>
                            @endif
                        </td>

                        {{-- ESTADO --}}
                        <td>
                            @if($cliente->estado == 'ACTIVO')
                                <span style="color: green; font-weight: bold;">● Activo</span>
                            @elseif($cliente->estado == 'BLOQUEADO')
                                <span style="color: red; font-weight: bold;">● Bloqueado</span>
                            @else
                                <span style="color: gray;">● {{ $cliente->estado }}</span>
                            @endif
                        </td>

                        <td class="erp-actions-cell">
                            {{-- EDITAR --}}
                            <a href="{{ route('clientes.edit', $cliente->id_cliente) }}"
                               class="btn-table btn-edit" title="Editar">
                                <i class="fa-solid fa-pen"></i>
                            </a>

                            {{-- ELIMINAR --}}
                            <form action="{{ route('clientes.destroy', $cliente->id_cliente) }}"
                                  method="POST"
                                  style="display:inline;">
                                @csrf
                                @method('DELETE')

                                <button class="btn-table btn-delete"
                                        onclick="return confirm('¿Confirma eliminar al cliente {{ $cliente->codigo }}?');"
                                        title="Eliminar">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="no-results" style="text-align: center; padding: 20px;">
                            <i class="fa-solid fa-folder-open" style="font-size: 2em; color: #ddd;"></i>
                            <p style="color: #999;">No se encontraron clientes registrados.</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- PAGINACIÓN --}}
    <div class="erp-pagination">
        {{-- Solo mostramos los links si $clientes es un objeto paginado --}}
        @if($clientes instanceof \Illuminate\Pagination\LengthAwarePaginator)
            {{ $clientes->withQueryString()->links() }}
        @endif
    </div>

</div>

@endsection
