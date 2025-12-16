@extends('layouts.app')

@section('content')

<div class="form-container">

    <h2 class="form-title">
        <i class="fa-solid fa-money-bill"></i> Nuevo Costo
    </h2>

    <form method="POST" action="{{ route('costo.insertar') }}">
        @csrf

        <div class="form-group">
            <label>Nombre *</label>
            <input type="text" name="nombre" required>
        </div>

        <div class="form-group">
            <label>Descripción</label>
            <textarea name="descripcion" rows="3"></textarea>
        </div>

        <div class="form-actions">
            <button class="btn-primary" type="submit">Guardar</button>
            <a class="btn-secondary" href="{{ route('costo.lista') }}">Cancelar</a>
        </div>

    </form>

</div>

@endsection
