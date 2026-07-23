@extends('layouts.app')

@section('content')

<div class="form-container">

    <h2 class="form-title">
        <i class="fa-solid fa-warehouse"></i> Nueva Ubicación
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
          action="{{ route('inventario.ubicaciones.store') }}"
          class="erp-form">
        @csrf

        {{-- NOMBRE --}}
        <div class="form-group">
            <label>Nombre de Ubicación *</label>
            <input type="text"
                   name="nombre"
                   value="{{ old('nombre') }}"
                   placeholder="Ej: Bodega Central o Estante A1"
                   required
                   autofocus>
        </div>

        {{-- DESCRIPCIÓN --}}
        <div class="form-group">
            <label>Descripción / Observaciones</label>
            <textarea name="descripcion"
                      rows="4"
                      placeholder="Detalles adicionales sobre la ubicación...">{{ old('descripcion') }}</textarea>
        </div>

        {{-- BOTONES --}}
        <div class="erp-actions">
            <button class="btn-primary" type="submit">
                <i class="fa-solid fa-save"></i> Guardar
            </button>

            <a class="btn-secondary" href="{{ route('inventario.ubicaciones.index') }}">
                Cancelar
            </a>
        </div>

    </form>

</div>

@endsection
