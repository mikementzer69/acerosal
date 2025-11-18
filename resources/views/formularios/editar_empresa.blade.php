@extends('layouts.app')

@section('content')

<div class="form-container">

    <h2 class="form-title">
        <i class="fa-solid fa-building"></i> Editar Empresa
    </h2>

    @if(session('msg'))
        <div class="form-alert">{{ session('msg') }}</div>
    @endif

    <form method="POST" action="{{ route('empresa.actualizar', $empresa->idEmpresa) }}">
        @csrf
        @method('PUT')

        <div class="form-group">
            <label>Nombre *</label>
            <input type="text" name="Nombre" required value="{{ old('Nombre', $empresa->Nombre) }}">
        </div>

        <div class="form-row">
            <div class="form-group">
                <label>NIT</label>
                <input type="text" name="NIT" value="{{ old('NIT', $empresa->NIT) }}">
            </div>

            <div class="form-group">
                <label>NRC</label>
                <input type="text" name="NRC" value="{{ old('NRC', $empresa->NRC) }}">
            </div>
        </div>

        <div class="form-group">
            <label>Razón Social</label>
            <input type="text" name="Razon_Social" value="{{ old('Razon_Social', $empresa->Razon_Social) }}">
        </div>

        <div class="form-group">
            <label>Dirección</label>
            <textarea name="Direccion" rows="3">{{ old('Direccion', $empresa->Direccion) }}</textarea>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label>Teléfono</label>
                <input type="text" name="Telefono" value="{{ old('Telefono', $empresa->Telefono) }}">
            </div>

            <div class="form-group">
                <label>Correo de Contacto</label>
                <input type="email" name="Correo_Contacto" value="{{ old('Correo_Contacto', $empresa->Correo_Contacto) }}">
            </div>
        </div>

        <div class="form-actions">
            <button class="btn-primary" type="submit">Actualizar</button>
            <a class="btn-secondary" href="{{ route('empresa.lista') }}">Cancelar</a>
        </div>

    </form>

</div>

@endsection
