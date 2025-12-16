@extends('layouts.app')

@section('content')

<div class="erp-section">

    <h2 class="erp-title">
        <i class="fa-solid fa-building"></i> Empresas (solo lectura)
    </h2>

    {{-- ALERTA --}}
    @if(session('msg'))
        <div class="form-alert">{{ session('msg') }}</div>
    @endif

    {{-- FILTROS COMPACTOS --}}
    <div class="filter-box">
    <form method="GET" action="{{ route('empresa.lista') }}" class="erp-search-form">

        <table style="width:100%;">
            <tr>
                <td><strong>Nombre</strong></td>
                <td><strong>NIT</strong></td>
                <td><strong>NRC</strong></td>
                <td><strong>Correo</strong></td>
                <td><strong>Teléfono</strong></td>
                <td></td>
            </tr>

            <tr>
                <td><input type="text" name="nombre" value="{{ $nombre }}" class="search-input" style="width:110px;"></td>
                <td><input type="text" name="nit" value="{{ $nit }}" class="search-input" style="width:100px;"></td>
                <td><input type="text" name="nrc" value="{{ $nrc }}" class="search-input" style="width:100px;"></td>
                <td><input type="text" name="correo" value="{{ $correo }}" class="search-input" style="width:140px;"></td>
                <td><input type="text" name="telefono" value="{{ $telefono }}" class="search-input" style="width:90px;"></td>

                <td style="white-space: nowrap;">
                    <button type="submit" class="btn-primary" style="margin-right:5px; padding:6px 12px;">
                        <i class="fa-solid fa-magnifying-glass"></i> Buscar
                    </button>
                    <a href="{{ route('empresa.lista') }}" class="btn-secondary" style="padding:6px 12px;">
                        Limpiar
                    </a>
                </td>
            </tr>
        </table>

    </form>
    </div>

    {{-- NUEVA EMPRESA --}}
    <div class="erp-actions">
        <a class="btn-primary" href="{{ route('empresa.nueva') }}">
            ➕ Nueva Empresa
        </a>
    </div>

    {{-- TOTAL --}}
    <p class="erp-total">Resultados: {{ $empresas->total() }}</p>

    {{-- TABLA --}}
    <div class="erp-table-container">
        <table class="erp-table">
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>NIT</th>
                    <th>NRC</th>
                    <th>Razón Social</th>
                    <th>Dirección</th>
                    <th>Teléfono</th>
                    <th>Correo</th>
                    <th style="width:120px; text-align:center;">Acciones</th>
                </tr>
            </thead>

            <tbody>
            @forelse($empresas as $e)
                <tr>
                    <td>{{ $e->nombre }}</td>
                    <td>{{ $e->nit }}</td>
                    <td>{{ $e->nrc }}</td>
                    <td>{{ $e->razon_social }}</td>
                    <td>{!! nl2br(e($e->direccion)) !!}</td>
                    <td>{{ $e->telefono }}</td>
                    <td>{{ $e->correo_contacto }}</td>

                    <td class="erp-actions-cell">

                        {{-- EDITAR --}}
                        <a href="{{ route('empresa.editar', $e->idempresa) }}"
                           class="btn-table btn-edit">
                            <i class="fa-solid fa-pen"></i>
                        </a>

                        {{-- BORRAR --}}
                        <form action="{{ route('empresa.eliminar', $e->idempresa) }}"
                              method="POST"
                              style="display:inline;">
                            @csrf
                            @method('DELETE')

                            <button class="btn-table btn-delete"
                                    onclick="return confirm('¿Seguro que deseas eliminar esta empresa?');">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        </form>

                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="no-results">Sin resultados.</td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>

    {{-- PAGINACIÓN --}}
    <div class="erp-pagination">
        {{ $empresas->appends(request()->all())->links() }}
    </div>

</div>

@endsection
