@extends('layouts.app')

@section('content')

<div class="form-container">

    <h2 class="form-title">
        <i class="fa-solid fa-pen-to-square"></i> Editar Ubicación: {{ $ubicacion->nombre }}
    </h2>

    {{-- ALERTAS DE ERROR --}}
    @if ($errors->any())
        <div class="form-alert form-error">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST"
          action="{{ route('inventario.ubicaciones.update', $ubicacion->id_ubicacion) }}"
          class="erp-form">
        @csrf
        @method('PUT')

        {{-- NOMBRE --}}
        <div class="form-group">
            <label>Nombre de Ubicación *</label>
            <input type="text"
                   name="nombre"
                   value="{{ old('nombre', $ubicacion->nombre) }}"
                   required>
        </div>

        {{-- DESCRIPCIÓN --}}
        <div class="form-group">
            <label>Descripción / Observaciones</label>
            <textarea name="descripcion"
                      rows="4">{{ old('descripcion', $ubicacion->descripcion) }}</textarea>
        </div>

        {{-- ESTADO --}}
        <div class="form-group">
            <label>Estado del Registro</label>
            <select name="inactivo" class="form-input" style="background-color: #111; color: white; border: 1px solid #444;">
                <option value="0" {{ $ubicacion->inactivo == 0 ? 'selected' : '' }}>ACTIVO</option>
                <option value="1" {{ $ubicacion->inactivo == 1 ? 'selected' : '' }}>INACTIVO (Bloqueado)</option>
            </select>
        </div>

        {{-- BOTONES --}}
        <div class="erp-actions">
            <button class="btn-primary" type="submit">
                <i class="fa-solid fa-refresh"></i> Actualizar Cambios
            </button>

            <a class="btn-secondary" href="{{ route('inventario.ubicaciones.index') }}">
                Cancelar
            </a>
        </div>

    </form>

</div>

@endsection
