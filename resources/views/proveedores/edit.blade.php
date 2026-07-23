@extends('layouts.app')

@section('content')

<div class="form-container">

    <h2 class="form-title">
        <i class="fa-solid fa-truck"></i> Editar Proveedor
    </h2>

    <form method="POST" action="{{ route('proveedores.update', $proveedor->id_proveedor) }}">
        @csrf
        @method('PUT')

        {{-- NOMBRE --}}
        <div class="form-group">
            <label>Nombre *</label>
            <input
                type="text"
                name="nombre"
                value="{{ old('nombre', $proveedor->nombre) }}"
                required>
        </div>

        {{-- ORIGEN --}}
        <div class="form-group">
            <label>Origen</label>
            <input
                type="text"
                name="origen"
                value="{{ old('origen', $proveedor->origen) }}">
        </div>

        {{-- DIRECCIÓN --}}
        <div class="form-group">
            <label>Dirección</label>
            <textarea
                name="direccion"
                rows="3">{{ old('direccion', $proveedor->direccion) }}</textarea>
        </div>

        {{-- BOTONES --}}
        <div class="form-actions">
            <button class="btn-primary" type="submit">
                Actualizar
            </button>

            <a class="btn-secondary" href="{{ route('proveedores.index') }}">
                Cancelar
            </a>
        </div>

    </form>

</div>

@endsection
