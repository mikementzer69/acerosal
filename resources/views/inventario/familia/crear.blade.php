@extends('layouts.app')

@section('content')

<div class="form-container">

    <h2 class="form-title">
        <i class="fa-solid fa-layer-group"></i> Nueva Calidad
    </h2>
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
          action="{{ route('familia.insertar') }}"
          class="erp-form">
        @csrf

        {{-- CÓDIGO --}}
        <div class="form-group">
            <label>Código *</label>
            <input type="text"
                   name="codigo"
                   value="{{ old('codigo') }}"
                   maxlength="20"
                   required>
        </div>

        {{-- NOMBRE --}}
        <div class="form-group">
            <label>Nombre *</label>
            <input type="text"
                   name="nombre"
                   value="{{ old('nombre') }}"
                   required>
        </div>

        {{-- DESCRIPCIÓN --}}
        <div class="form-group">
            <label>Descripción</label>
            <textarea name="descripcion" rows="3">{{ old('descripcion') }}</textarea>
        </div>

        {{-- COLOR --}}
        <div class="form-group">
            <label>Color de Etiqueta</label>
            <input type="color"
                   name="detalle_color"
                   value="{{ old('detalle_color', '#000000') }}">
        </div>

        {{-- UBICACIÓN --}}



        {{-- BOTONES --}}
        <div class="erp-actions">
            <button class="btn-primary" type="submit">
                Guardar
            </button>

            <a class="btn-secondary" href="{{ route('familia.lista') }}">
                Cancelar
            </a>
        </div>

    </form>

</div>

@endsection
