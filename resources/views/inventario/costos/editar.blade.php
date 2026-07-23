@extends('layouts.app')

@section('content')

<div class="form-container">

    <h2 class="form-title">
        <i class="fa-solid fa-money-bill"></i> Editar Costo
    </h2>

    <form method="POST" action="{{ route('costo.actualizar', $costo->id_costo) }}">
        @csrf
        @method('PUT')

        <div class="form-group">
            <label>Nombre *</label>
            <input type="text"
                   name="nombre"
                   value="{{ $costo->nombre }}"
                   required>
        </div>

        <div class="form-group">
            <label>Descripción</label>
            <textarea name="descripcion" rows="3">{{ $costo->descripcion }}</textarea>
        </div>

        <div class="form-actions">
            <button class="btn-primary" type="submit">Guardar Cambios</button>
            <a class="btn-secondary" href="{{ route('costo.lista') }}">Cancelar</a>
        </div>

    </form>

</div>

@endsection

