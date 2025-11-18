@extends('layouts.app')

@section('content')

<div class="erp-section">

    <h2 class="erp-title">
        <i class="fa-solid fa-money-bill"></i> Costos (solo lectura)
    </h2>

    {{-- ALERTA --}}
    @if(session('msg'))
        <div class="form-alert">{{ session('msg') }}</div>
    @endif

    {{-- BUSCADOR --}}
    <form method="GET" action="{{ route('costo.lista') }}" class="erp-search-form">

        <div class="search-row">
            <div>
                <label>Nombre:</label>
                <input type="text" name="nombre" value="{{ $fNombre }}" class="search-input">
            </div>

            <button class="btn-primary" type="submit">
                <i class="fa-solid fa-magnifying-glass"></i> Buscar
            </button>

            <a class="btn-secondary" href="{{ route('costo.lista') }}">
                Limpiar
            </a>
        </div>
    </form>

    {{-- NUEVO --}}
    <div class="erp-actions">
        <a class="btn-primary" href="{{ route('costo.nuevo') }}">
            ➕ Nuevo Costo
        </a>
    </div>

    {{-- TOTAL --}}
    <p class="erp-total">Total: {{ $costos->total() }}</p>

    {{-- TABLA --}}
    <div class="erp-table-container">
        <table class="erp-table">
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Descripción</th>
                </tr>
            </thead>

            <tbody>
                @forelse($costos as $c)
                    <tr>
                        <td>{{ $c->Nombre }}</td>
                        <td>{!! nl2br(e($c->Descripcion)) !!}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="2" class="no-results">Sin resultados.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- PAGINACIÓN --}}
    <div class="erp-pagination">
        {{ $costos->links() }}
    </div>

</div>

@endsection
