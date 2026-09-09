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

        <div style="display: flex; gap: 15px; width: 100%;">
            {{-- PRECIO LISTA --}}
            <div class="form-group" style="flex: 1;">
                <label>Precio de Lista</label>
                <input type="number"
                       step="0.01"
                       min="0"
                       name="precio_lista"
                       value="{{ old('precio_lista', $familia->precio_lista ?? 0) }}">
            </div>

            {{-- PRECIO ESPECIAL --}}
            <div class="form-group" style="flex: 1;">
                <label>Precio Especial</label>
                <input type="number"
                       step="0.01"
                       min="0"
                       name="precio_especial"
                       value="{{ old('precio_especial', $familia->precio_especial ?? 0) }}">
            </div>

            {{-- PRECIO AUTORIZADO --}}
            <div class="form-group" style="flex: 1;">
                <label>¿Requiere Autorización para Precio?</label>
                <select name="precio_autorizado">
                    <option value="N" {{ old('precio_autorizado', $familia->precio_autorizado) == 'N' ? 'selected' : '' }}>No</option>
                    <option value="S" {{ old('precio_autorizado', $familia->precio_autorizado) == 'S' ? 'selected' : '' }}>Sí</option>
                </select>
            </div>
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
