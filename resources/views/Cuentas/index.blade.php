@extends('layouts.app')

@section('content')

<div class="erp-section">

    <h2 class="erp-title">
        <i class="fa-solid fa-book"></i> Catálogo de Cuentas Contables
    </h2>

    {{-- ALERTA --}}
    @if(session('msg'))
        <div class="form-alert">{{ session('msg') }}</div>
    @endif

    {{-- FILTROS --}}
    <div class="filter-box">
        <form method="GET" action="{{ route('cuentas.index') }}" class="erp-search-form">

            <table style="width:100%;">
                <tr>
                    <td><strong>Código</strong></td>
                    <td><strong>Nombre</strong></td>
                    <td><strong>Tipo</strong></td>
                    <td></td>
                </tr>

                <tr>
                    <td>
                        <input type="text" name="codigo"
                               value="{{ $codigo }}"
                               class="search-input" style="width:100px;">
                    </td>

                    <td>
                        <input type="text" name="nombre"
                               value="{{ $nombre }}"
                               class="search-input" style="width:180px;">
                    </td>

                    <td>
                        <select name="tipo" class="search-input" style="width:140px;">
                            <option value="">Todos</option>
                            <option value="activo"      {{ $tipo=='activo' ? 'selected' : '' }}>Activo</option>
                            <option value="pasivo"      {{ $tipo=='pasivo' ? 'selected' : '' }}>Pasivo</option>
                            <option value="patrimonio"  {{ $tipo=='patrimonio' ? 'selected' : '' }}>Patrimonio</option>
                            <option value="ingreso"     {{ $tipo=='ingreso' ? 'selected' : '' }}>Ingreso</option>
                            <option value="gasto"       {{ $tipo=='gasto' ? 'selected' : '' }}>Gasto</option>
                        </select>
                    </td>

                    <td style="white-space: nowrap;">
                        <button type="submit" class="btn-primary" style="margin-right:5px; padding:6px 12px;">
                            <i class="fa-solid fa-magnifying-glass"></i> Buscar
                        </button>

                        <a href="{{ route('cuentas.index') }}" class="btn-secondary" style="padding:6px 12px;">
                            Limpiar
                        </a>
                    </td>
                </tr>
            </table>

        </form>
    </div>

    {{-- NUEVA CUENTA --}}
    <div class="erp-actions">
        <a class="btn-primary" href="{{ route('cuentas.create') }}">
            ➕ Nueva Cuenta
        </a>
    </div>

    {{-- TOTAL --}}
    <p class="erp-total">Resultados: {{ $cuentas->total() }}</p>

    {{-- TABLA --}}
    <div class="erp-table-container">
        <table class="erp-table">
            <thead>
                <tr>
                    <th>Código</th>
                    <th>Nombre</th>
                    <th>Tipo</th>
                    <th>Cuenta Padre</th>
                    <th>Movimiento</th>
                    <th>Activa</th>
                    <th style="width:120px; text-align:center;">Acciones</th>
                </tr>
            </thead>

            <tbody>
            @forelse($cuentas as $c)
                <tr>
                    <td>{{ $c->codigo }}</td>
                    <td>{{ $c->nombre }}</td>
                    <td>{{ ucfirst($c->tipo) }}</td>
                    <td>
                        @if($c->parent)
                            {{ $c->parent->codigo }} - {{ $c->parent->nombre }}
                        @else
                            —
                        @endif
                    </td>
                    <td>{{ $c->es_movimiento ? 'Sí' : 'No' }}</td>
                    <td>{{ $c->activo ? 'Sí' : 'No' }}</td>

                    <td class="erp-actions-cell">
                        {{-- EDITAR --}}
                        <a href="{{ route('cuentas.edit', $c->id) }}"
                           class="btn-table btn-edit">
                            <i class="fa-solid fa-pen"></i>
                        </a>

                        {{-- BORRAR --}}
                        <form action="{{ route('cuentas.destroy', $c->id) }}"
                              method="POST"
                              style="display:inline;">
                            @csrf
                            @method('DELETE')

                            <button class="btn-table btn-delete"
                                    onclick="return confirm('¿Seguro que deseas eliminar esta cuenta?');">
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
        {{ $cuentas->appends([
            'codigo' => request('codigo'),
            'nombre' => request('nombre'),
            'tipo'   => request('tipo'),
        ])->links() }}
    </div>

</div>

@endsection
