@extends('layouts.app')

@section('content')
<div class="form-container">

    <h2 class="form-title">
        <i class="fa-solid fa-calendar-day"></i> Cierre Diario de Inventario
    </h2>

    @if(session('msg'))
        <div class="form-alert">{{ session('msg') }}</div>
    @endif

    @if(session('error'))
        <div class="form-alert alert-error">{{ session('error') }}</div>
    @endif

    <form method="POST" action="{{ route('inventario.cierre.diario.ejecutar') }}">
        @csrf

        <div class="form-group">
            <label>Empresa *</label>
            <input type="number" name="id_empresa" required>
        </div>

        <div class="form-group">
            <label>Fecha *</label>
            <input type="date" name="fecha" value="{{ date('Y-m-d') }}" required>
        </div>

        <div class="form-actions">
            <button class="btn-primary" type="submit">Ejecutar Cierre</button>
        </div>

    </form>

</div>
@endsection
