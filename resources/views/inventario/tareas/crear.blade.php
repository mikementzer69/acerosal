@extends('layouts.app')

@section('content')

<div class="form-container">

    <h2 class="form-title">
        <i class="fa-solid fa-list-check"></i> Nueva Tarea
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

    <form method="POST" action="{{ route('tarea.insertar') }}">
        @csrf

        {{-- MÓDULO --}}
        <div class="form-group">
            <label>Módulo *</label>
            <select name="id_modulo" required>
                <option value="">Seleccione...</option>
                @foreach($modulos as $m)
                    <option value="{{ $m->id_modulo }}">{{ $m->nombre }}</option>
                @endforeach
            </select>
        </div>

        {{-- NOMBRE --}}
        <div class="form-group">
            <label>Nombre *</label>
            <input type="text" name="nombre" required>
        </div>

        {{-- DESCRIPCION --}}
        <div class="form-group">
            <label>Descripción</label>
            <input type="text" name="descripcion">
        </div>

        {{-- RUTA --}}
        <div class="form-group">
            <label>Ruta</label>
            <input type="text" name="ruta">
        </div>

        {{-- ICONO --}}
        <div class="form-group">
            <label>Ícono</label>
            <input type="text" name="icono">
        </div>

        {{-- ORDEN --}}
        <div class="form-group">
            <label>Orden</label>
            <input type="number" name="orden" value="0">
        </div>

        {{-- VISIBLE --}}
        <div class="form-group">
            <label>Visible</label>
            <input type="checkbox" name="visible" value="1" checked>
        </div>

        {{-- BOTONES --}}
        <div class="form-actions">
            <button type="submit" class="btn-primary">Guardar</button>
            <a href="{{ route('tarea.lista') }}" class="btn-secondary">Cancelar</a>
        </div>

    </form>

</div>

@endsection
