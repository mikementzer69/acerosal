@extends('layouts.app')

@section('content')

<div class="form-container">

    <h2 class="form-title">
        <i class="fa-solid fa-box"></i> Nuevo Producto
    </h2>

    <form method="POST" action="{{ route('producto.insertar') }}">
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

        {{-- CÓDIGO --}}
        <div class="form-group">
            <label>Código *</label>
            <input type="text" name="codigo" required>
        </div>

        {{-- DESCRIPCIÓN --}}
        <div class="form-group">
            <label>Descripción *</label>
            <input type="text" name="descripcion" required>
        </div>

        {{-- UNIDAD DE MEDIDA --}}
        <div class="form-group">
            <label>Unidad de Medida</label>
            <input type="text" name="unidad_medida">
        </div>

        {{-- MILIMETROS --}}
        <div class="form-group">
            <label>Milímetros</label>
            <input type="number" step="0.01" name="milimetros">
        </div>

        {{-- PULGADAS --}}
        <div class="form-group">
            <label>Pulgadas</label>
            <input type="number" step="0.0001" name="pulgadas">
        </div>

        {{-- TOLERANCIA --}}
        <div class="form-group">
            <label>Tolerancia</label>
            <input type="number" step="0.01" name="tolerancia">
        </div>

        {{-- PESO --}}
        <div class="form-group">
            <label>Peso LB/MTS</label>
            <input type="number" step="0.01" name="peso_lb_mts">
        </div>

        {{-- PRECIO SIN IVA --}}
        <div class="form-group">
            <label>Precio sin IVA</label>
            <input type="number" step="0.01" name="precio_venta_sin_iva">
        </div>

        {{-- PRECIO FIJO --}}
        <div class="form-group">
            <label>Precio Fijo</label>
            <input type="checkbox" name="precio_fijo" value="1">
        </div>

        {{-- BOTONES --}}
        <div class="form-actions">
            <button class="btn-primary" type="submit">Guardar</button>
            <a class="btn-secondary" href="{{ route('producto.lista') }}">Cancelar</a>
        </div>

    </form>

</div>

@endsection
