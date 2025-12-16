@extends('layouts.app')

@section('content')

<div class="form-container">

    <h2 class="form-title">
        <i class="fa-solid fa-building"></i> Editar Empresa
    </h2>

    @if(session('msg'))
        <div class="form-alert">{{ session('msg') }}</div>
    @endif

    <form method="POST" action="{{ route('empresa.actualizar', $empresa->idempresa) }}">
        @csrf
        @method('PUT')

        <div class="form-group">
            <label>Nombre *</label>
            <input type="text" name="nombre" required value="{{ old('nombre', $empresa->nombre) }}">
        </div>

        <div class="form-row">
            <div class="form-group">
                <label>NIT</label>
                <input type="text" name="nit" value="{{ old('nit', $empresa->nit) }}">
            </div>

            <div class="form-group">
                <label>NRC</label>
                <input type="text" name="nrc" value="{{ old('nrc', $empresa->nrc) }}">
            </div>
        </div>

        <div class="form-group">
            <label>Razón Social</label>
            <input type="text" name="razon_social" value="{{ old('razon_social', $empresa->razon_social) }}">
        </div>

        <div class="form-group">
            <label>Dirección</label>
            <textarea name="direccion" rows="3">{{ old('direccion', $empresa->direccion) }}</textarea>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label>Teléfono</label>
                <input type="text" name="telefono" value="{{ old('telefono', $empresa->telefono) }}">
            </div>

            <div class="form-group">
                <label>Correo de Contacto</label>
                <input type="email" name="correo_contacto" value="{{ old('correo_contacto', $empresa->correo_contacto) }}">
            </div>
        </div>

        <div class="form-actions">
            <button class="btn-primary" type="submit">Actualizar</button>
            <a class="btn-secondary" href="{{ route('empresa.lista') }}">Cancelar</a>
        </div>

    </form>

</div>

@endsection
