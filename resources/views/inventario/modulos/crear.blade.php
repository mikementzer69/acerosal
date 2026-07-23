@extends('layouts.app')

@section('content')

<div class="form-container">

    <h2 class="form-title">
        <i class="fa-solid fa-layer-group"></i> Nuevo Módulo
    </h2>

    <form method="POST" action="{{ route('modulo.insertar') }}">
        @csrf

        {{-- FAMILIA --}}
        <div class="form-group">
            <label>Familia *</label>
            <select name="id_familia" required>
                <option value="">Seleccione...</option>
                @foreach($familias as $f)
                    <option value="{{ $f->idfamilia }}">{{ $f->nombre }}</option>
                @endforeach
            </select>
        </div>

        {{-- MÓDULO PADRE --}}
        <div class="form-group">
            <label>Módulo Padre</label>
            <select name="id_modulopadre">
                <option value="">(Ninguno)</option>
                @foreach($padres as $p)
                    <option value="{{ $p->id_modulo }}">{{ $p->nombre }}</option>
                @endforeach
            </select>
        </div>

        {{-- NOMBRE --}}
        <div class="form-group">
            <label>Nombre *</label>
            <input type="text" name="nombre" required>
        </div>

        {{-- DESCRIPCIÓN --}}
        <div class="form-group">
            <label>Descripción</label>
            <textarea name="descripcion" rows="3"></textarea>
        </div>

        {{-- BOTONES --}}
        <div class="form-actions">
            <button class="btn-primary" type="submit">Guardar</button>
            <a class="btn-secondary" href="{{ route('modulo.lista') }}">Cancelar</a>
        </div>

    </form>

</div>

@endsection
