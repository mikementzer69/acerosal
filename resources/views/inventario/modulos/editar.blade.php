@extends('layouts.app')

@section('content')

<div class="form-container">

    <h2 class="form-title">
        <i class="fa-solid fa-diagram-project"></i> Editar Módulo
    </h2>

    <form method="POST" action="{{ route('modulo.actualizar', $modulo->idmodulos) }}">
        @csrf
        @method('PUT')

        <div class="form-group">
            <label>Nombre *</label>
            <input type="text" name="nombre" value="{{ $modulo->nombre }}" required>
        </div>

        <div class="form-group">
            <label>Descripción</label>
            <textarea name="descripcion" rows="3">{{ $modulo->descripcion }}</textarea>
        </div>

        <div class="form-group">
            <label>Módulo Padre</label>
            <select name="id_modulopadre">
                <option value="">— Ninguno —</option>
                @foreach($padres as $p)
                    <option value="{{ $p->idmodulos }}"
                        {{ $modulo->id_modulopadre == $p->idmodulos ? 'selected' : '' }}>
                        {{ $p->nombre }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="form-actions">
            <button class="btn-primary" type="submit">Guardar Cambios</button>
            <a class="btn-secondary" href="{{ route('modulo.lista') }}">Cancelar</a>
        </div>

    </form>

</div>

@endsection

