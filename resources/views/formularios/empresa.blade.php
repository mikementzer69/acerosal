@extends('layouts.app')

@section('content')

<div class="form-container">

    <h2 class="form-title">
        <i class="fa-solid fa-building"></i> Nueva Empresa
    </h2>

    {{-- MENSAJE DE ÉXITO / ERROR --}}
    @if(session('msg'))
        <div class="form-alert">
            {{ session('msg') }}
        </div>
    @endif

    {{-- FORMULARIO --}}
    <form method="POST" action="{{ route('empresa.insertar') }}">
        @csrf

        <div class="form-group">
            <label>Nombre *</label>
            <input type="text" name="Nombre" required>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label>NIT</label>
                <input type="text" name="NIT">
            </div>

            <div class="form-group">
                <label>NRC</label>
                <input type="text" name="NRC">
            </div>
        </div>

        <div class="form-group">
            <label>Razón Social</label>
            <input type="text" name="Razon_Social">
        </div>

        <div class="form-group">
            <label>Dirección</label>
            <textarea name="Direccion" rows="3"></textarea>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label>Teléfono</label>
                <input type="text" name="Telefono">
            </div>

            <div class="form-group">
                <label>Correo de Contacto</label>
                <input type="email" name="Correo_Contacto">
            </div>
        </div>

        <div class="form-actions">
            <button class="btn-primary" type="submit">Guardar</button>
            <a class="btn-secondary" href="{{ route('empresa.lista') }}">Cancelar</a>
        </div>

    </form>

</div>

@endsection
