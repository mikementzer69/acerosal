@extends('layouts.app')

@section('content')

<div class="form-container">

    <h2 class="form-title">
        <i class="fa-solid fa-edit"></i> Editar Tarea
    </h2>

    @if ($errors->any())
        <div class="form-alert">
            <ul>
                @foreach ($errors->all() as $e)
                    <li>{{ $e }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('tarea.actualizar', $tarea->id_tarea) }}">
        @csrf
        @method('PUT')

        {{-- MÓDULO --}}
        <div class="form-group">
            <label>Módulo *</label>
            <select name="id_modulo" required>
                @foreach($modulos as $m)
                    <option value="{{ $m->id_modulo }}"
                        {{ $tarea->id_modulo == $m->id_modulo ? 'selected' : '' }}>
                        {{ $m->nombre }}
                    </option>
                @endforeach
            </select>
        </div>

        {{-- NOMBRE --}}
        <div class="form-group">
            <label>Nombre *</label>
            <input type="text" name="nombre" value="{{ $tarea->nombre }}" required>
        </div>

        {{-- DESCRIPCIÓN --}}
        <div class="form-group">
            <label>Descripción</label>
            <input type="text" name="descripcion" value="{{ $tarea->descripcion }}">
        </div>

        {{-- RUTA --}}
        <div class="form-group">
            <label>Ruta</label>
            <input type="text" name="ruta" value="{{ $tarea->ruta }}">
        </div>

        {{-- ICONO --}}
        <div class="form-group">
            <label>Ícono</label>
            <input type="text" name="icono" value="{{ $tarea->icono }}">
        </div>

        {{-- ORDEN --}}
        <div class="form-group">
            <label>Orden</label>
            <input type="number" name="orden" value="{{ $tarea->orden }}">
        </div>

        {{-- VISIBLE --}}
        <div class="form-group">
            <label>Visible</label>
            <input type="checkbox" name="visible" value="1"
                {{ $tarea->visible ? 'checked' : '' }}>
        </div>

        {{-- BOTONES --}}
        <div class="form-actions">
            <button class="btn-primary">Actualizar</button>
            <a href="{{ route('tarea.lista') }}" class="btn-secondary">Cancelar</a>
        </div>

    </form>

</div>

@endsection
