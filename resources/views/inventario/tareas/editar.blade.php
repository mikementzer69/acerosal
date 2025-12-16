@extends('layouts.app')

@section('content')

<div class="form-container">

    <h2 class="form-title">
        <i class="fa-solid fa-edit"></i> Editar Tarea
    </h2>

    <form method="POST" action="{{ route('tarea.actualizar', $tarea->idtareas) }}">
        @csrf

        <div class="form-group">
            <label>Módulo *</label>
            <select name="id_modulos" required>
                @foreach($modulos as $m)
                    <option value="{{ $m->idmodulos }}"
                        {{ $tarea->id_modulos == $m->idmodulos ? 'selected' : '' }}>
                        {{ $m->nombre }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label>Nombre *</label>
            <input type="text" name="nombre" value="{{ $tarea->nombre }}" required>
        </div>

        <div class="form-group">
            <label>Descripción</label>
            <input type="text" name="descripcion" value="{{ $tarea->descripcion }}">
        </div>

        <div class="form-group">
            <label>Ruta</label>
            <input type="text" name="ruta" value="{{ $tarea->ruta }}">
        </div>

        <div class="form-group">
            <label>Ícono</label>
            <input type="text" name="icono" value="{{ $tarea->icono }}">
        </div>

        <div class="form-group">
            <label>Orden</label>
            <input type="number" name="orden" value="{{ $tarea->orden }}">
        </div>

        <div class="form-group">
            <label>Visible</label>
            <input type="checkbox" name="visible" value="1"
                {{ $tarea->visible ? 'checked' : '' }}>
        </div>

        <div class="form-actions">
            <button class="btn-primary">Actualizar</button>
            <a href="{{ route('tarea.lista') }}" class="btn-secondary">Cancelar</a>
        </div>

    </form>

</div>

@endsection
