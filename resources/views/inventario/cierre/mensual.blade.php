@extends('layouts.app')

@section('content')
<div class="form-container">

    <h2 class="form-title">
        <i class="fa-solid fa-calendar"></i> Cierre Mensual de Inventario
    </h2>

    @if(session('msg'))
        <div class="form-alert">{{ session('msg') }}</div>
    @endif

    @if(session('error'))
        <div class="form-alert alert-error">{{ session('error') }}</div>
    @endif

    <form method="POST" action="{{ route('inventario.cierre.mensual.ejecutar') }}">
        @csrf

        <div class="form-group">
            <label>Empresa *</label>
            <input type="number" name="id_empresa" required>
        </div>

        <div class="form-group">
            <label>Mes *</label>
            <input type="number" name="mes" min="1" max="12" required>
        </div>

        <div class="form-group">
            <label>Año *</label>
            <input type="number" name="anio" min="2020" required>
        </div>

        <div class="form-actions">
            <button class="btn-primary">Ejecutar Cierre</button>
        </div>

    </form>

</div>
@endsection
