@extends('layouts.app')

@section('content')

<div class="form-container">

    <h2 class="form-title">
        <i class="fa-solid fa-layer-group"></i> Editar Calidad
    </h2>

    @if ($errors->any())
        <div class="form-alert form-error" style="background-color: #fee2e2; border: 1px solid #ef4444; color: #b91c1c; padding: 12px; border-radius: 6px; margin-bottom: 20px;">
            <strong><i class="fa-solid fa-triangle-exclamation"></i> Por favor corrija los siguientes errores:</strong>
            <ul style="margin: 6px 0 0 20px;">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if(session('error'))
        <div class="form-alert form-error" style="background-color: #fee2e2; border: 1px solid #ef4444; color: #b91c1c; padding: 12px; border-radius: 6px; margin-bottom: 20px;">
            <i class="fa-solid fa-circle-xmark"></i> {{ session('error') }}
        </div>
    @endif

    @if(session('msg'))
        <div class="form-alert" style="background-color: #d1fae5; border: 1px solid #10b981; color: #065f46; padding: 12px; border-radius: 6px; margin-bottom: 20px;">
            <i class="fa-solid fa-circle-check"></i> {{ session('msg') }}
        </div>
    @endif

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
                   maxlength="50"
                   required>
        </div>

        {{-- NOMBRE --}}
        <div class="form-group">
            <label>Nombre *</label>
            <input type="text"
                   name="nombre"
                   value="{{ old('nombre', $familia->nombre) }}"
                   maxlength="255"
                   required>
        </div>

        {{-- DESCRIPCIÓN --}}
        <div class="form-group">
            <label>Descripción</label>
            <textarea name="descripcion" rows="3">{{ old('descripcion', $familia->descripcion) }}</textarea>
        </div>

        {{-- COLOR --}}
        @php
            $rawColor = $familia->detalle_color ?? '#000000';
            $validHex = (str_starts_with($rawColor, '#') && strlen($rawColor) == 7) ? $rawColor : '#000000';
        @endphp
        <div class="form-group">
            <label>Color de Etiqueta</label>
            <input type="color"
                   name="detalle_color"
                   value="{{ old('detalle_color', $validHex) }}">
        </div>

        <div style="display: flex; gap: 15px; width: 100%;">
            {{-- PRECIO LISTA --}}
            <div class="form-group" style="flex: 1;">
                <label>Precio de Lista ($/lb)</label>
                <input type="number"
                       step="any"
                       min="0"
                       name="precio_lista"
                       value="{{ old('precio_lista', $familia->precio_lista ?? 0) }}">
            </div>

            {{-- PRECIO ESPECIAL --}}
            <div class="form-group" style="flex: 1;">
                <label>Precio Especial ($/lb)</label>
                <input type="number"
                       step="any"
                       min="0"
                       name="precio_especial"
                       value="{{ old('precio_especial', $familia->precio_especial ?? 0) }}">
            </div>

            {{-- PRECIO AUTORIZADO --}}
            <div class="form-group" style="flex: 1;">
                <label>¿Requiere Autorización para Precio?</label>
                <select name="precio_autorizado">
                    <option value="N" {{ old('precio_autorizado', $familia->precio_autorizado ?? 'N') == 'N' ? 'selected' : '' }}>No</option>
                    <option value="S" {{ old('precio_autorizado', $familia->precio_autorizado ?? 'N') == 'S' ? 'selected' : '' }}>Sí</option>
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
