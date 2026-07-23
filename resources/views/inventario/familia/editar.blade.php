@extends('layouts.app')

@section('content')

<div class="form-container">

    <h2 class="form-title">
        <i class="fa-solid fa-layer-group"></i> Editar Calidad
    </h2>

    <form method="POST"
          action="{{ route('familia.actualizar', $familia->id_familia) }}"
          class="erp-form">
        @csrf
        @method('PUT')

        {{-- CÓDIGO --}}
        <div class="form-group">
            <label>Código *</label>
            <input type="text"
                   name="codigo"
                   value="{{ old('codigo', $familia->codigo) }}"
                   required>
        </div>

        {{-- NOMBRE --}}
        <div class="form-group">
            <label>Nombre *</label>
            <input type="text"
                   name="nombre"
                   value="{{ old('nombre', $familia->nombre) }}"
                   required>
        </div>

        {{-- DESCRIPCIÓN --}}
        <div class="form-group">
            <label>Descripción</label>
            <textarea name="descripcion" rows="3">{{ old('descripcion', $familia->descripcion) }}</textarea>
        </div>

        {{-- COLOR --}}
        <div class="form-group">
            <label>Color de Etiqueta</label>
            <input type="color"
                   name="detalle_color"
                   value="{{ old('detalle_color', $familia->detalle_color ?? '#000000') }}">
        </div>



        {{-- BOTONES --}}
        <div class="erp-actions">
            <button class="btn-primary" type="submit">
                Guardar Cambios
            </button>

            <a class="btn-secondary" href="{{ route('familia.lista') }}">
                Cancelar
            </a>
        </div>

    </form>

</div>

@endsection
