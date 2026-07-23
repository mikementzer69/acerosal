@extends('layouts.app')

@section('content')

<div class="erp-section">

    <h2 class="erp-title">
        <i class="fa-solid fa-book"></i> Asientos Contables
    </h2>

    {{-- ALERTA --}}
    @if(session('msg'))
        <div class="form-alert">
            {{ session('msg') }}
        </div>
    @endif

    {{-- BOTÓN NUEVO --}}
    <div class="erp-actions">
        <a class="btn-primary" href="{{ route('asientos.create') }}">
            ➕ Nuevo Asiento
        </a>
    </div>

    <p class="erp-total">Resultados: {{ $asientos->total() }}</p>

    {{-- TABLA --}}
    <div class="erp-table-container">
        <table class="erp-table">
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Descripción</th>
                    <th>Total Debe</th>
                    <th>Total Haber</th>
                    <th>Activo</th>
                    <th style="width:150px; text-align:center;">Acciones</th>
                </tr>
            </thead>

            <tbody>
            @forelse($asientos as $a)
                <tr>
                    <td>{{ $a->fecha }}</td>
                    <td>{{ $a->descripcion }}</td>
                    <td>${{ number_format($a->total_debe, 2) }}</td>
                    <td>${{ number_format($a->total_haber, 2) }}</td>
                    <td>{{ $a->activo ? 'Sí' : 'No' }}</td>

                    <td class="erp-actions-cell">

                        {{-- VER --}}
                        <a href="{{ route('asientos.show', $a->id) }}"
                           class="btn-table btn-edit"
                           title="Ver">
                            <i class="fa-solid fa-eye"></i>
                        </a>

                        {{-- EDITAR --}}
                        <a href="{{ route('asientos.edit', $a->id) }}"
                           class="btn-table btn-edit"
                           title="Editar">
                            <i class="fa-solid fa-pen-to-square"></i>
                        </a>

                        {{-- ELIMINAR --}}
                        <form action="{{ route('asientos.destroy', $a->id) }}"
                              method="POST"
                              style="display:inline;">
                            @csrf
                            @method('DELETE')

                            <button class="btn-table btn-delete"
                                    title="Eliminar"
                                    onclick="return confirm('¿Seguro que deseas eliminar este asiento contable?');">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        </form>

                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="no-results">
                        No hay asientos registrados.
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>

    {{-- PAGINACIÓN --}}
    <div class="erp-pagination">
        {{ $asientos->links() }}
    </div>

</div>

@endsection
