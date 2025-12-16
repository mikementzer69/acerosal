@extends('layouts.app')

@section('content')

<div class="form-container">

    <h2 class="form-title">
        <i class="fa-solid fa-layer-group"></i> Editar Familia
    </h2>

    <form method="POST" action="{{ route('familia.actualizar', $familia->idfamilia) }}">
        @csrf
        @method('PUT')

        <div class="form-group">
            <label>Nombre *</label>
            <input type="text"
                   name="nombre"
                   value="{{ $familia->nombre }}"
                   required>
        </div>

        <div class="form-group">
            <label>Descripción</label>
            <textarea name="descripcion" rows="3">{{ $familia->descripcion }}</textarea>
        </div>

        <div class="form-actions">
            <button class="btn-primary" type="submit">Guardar Cambios</button>
            <a class="btn-secondary" href="{{ route('familia.lista') }}">Cancelar</a>
        </div>

    </form>

</div>

@endsection
