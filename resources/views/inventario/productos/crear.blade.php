@extends('layouts.app')

@section('content')
<div class="form-container">

    <h2 class="form-title">
        <i class="fa-solid fa-box"></i> Nuevo Producto
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

    <form method="POST" action="{{ route('producto.insertar') }}">
        @csrf

        {{-- FILA 1: FAMILIA, UBICACIÓN Y CÓDIGO --}}
        <div class="form-row">
            <div class="form-group">
                <label>Calidad *</label>
                <select name="id_familia" required>
                    <option value="">Seleccione...</option>
                    @foreach($familias as $f)
                        <option value="{{ $f->id_familia }}" {{ old('id_familia')==$f->id_familia?'selected':'' }}>
                            {{ $f->nombre }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- CAMPO NUEVO: UBICACIÓN --}}
            <div class="form-group">
                <label>Ubicación / Bodega</label>
                <select name="id_ubicacion">
                    <option value="">Seleccione...</option>
                    @foreach($ubicaciones as $u)
                        <option value="{{ $u->id_ubicacion }}" {{ old('id_ubicacion') == $u->id_ubicacion ? 'selected' : '' }}>
                            {{ $u->nombre }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label>Código *</label>
                <input type="text" name="codigo" value="{{ old('codigo') }}" required>
            </div>
        </div>

        {{-- FILA 2: DESCRIPCIÓN (SOLA) --}}
        <div class="form-group">
            <label>Descripción *</label>
            <input type="text" name="descripcion" value="{{ old('descripcion') }}" required>
        </div>

        {{-- FILA 3: UNIDADES + MILÍMETROS --}}
        <div class="form-row">
            <div class="form-group">
                <label>Unidad Medida Longitud</label>
                <input type="text" name="unidad_medida_longitud" value="{{ old('unidad_medida_longitud') }}">
            </div>

            <div class="form-group">
                <label>Unidad Medida Peso</label>
                <input type="text" name="unidad_medida_peso" value="{{ old('unidad_medida_peso') }}">
            </div>

            <div class="form-group">
                <label>Milímetros</label>
                <input type="text" name="milimetros" value="{{ old('milimetros') }}">
            </div>
        </div>

        {{-- FILA 4: PULGADAS Y TOLERANCIA --}}
        <div class="form-row">
            <div class="form-group">
                <label>Pulgadas</label>
                <input type="text" name="pulgadas" value="{{ old('pulgadas') }}">
            </div>

            <div class="form-group">
                <label>Pulgadas Decimal</label>
                <input type="number" step="0.0001" name="pulgadas_decimal" value="{{ old('pulgadas_decimal') }}">
            </div>

            <div class="form-group">
                <label>Tolerancia</label>
                <input type="number" step="0.0001" name="tolerancia" value="{{ old('tolerancia') }}">
            </div>
        </div>

        {{-- FILA 5: PESO, STOCK METROS (NUEVO) Y PRECIO BODEGA --}}
        <div class="form-row">
            <div class="form-group">
                <label>Peso LB/MTS</label>
                <input type="number" step="0.0001" name="peso_lb_mts" value="{{ old('peso_lb_mts') }}">
            </div>

            <div class="form-group">
                <label>Stock Metros (Inicial)</label>
                <input type="number"
                       name="stock_metros"
                       value="0.00"
                       readonly
                       style="background-color: #e9ecef; color: #333333; cursor: not-allowed; font-weight: bold;">
            </div>

            <div class="form-group">
                <label>Precio Unitario Bodega</label>
                <input type="number" step="0.0001" name="precio_unitario_bodega" value="{{ old('precio_unitario_bodega') }}">
            </div>
        </div>

        {{-- FILA 6: PRECIOS DE VENTA --}}
        <div class="form-row">
            <div class="form-group">
                <label>Precio sin IVA</label>
                <input type="number" step="0.01" name="precio_venta_sin_iva" value="{{ old('precio_venta_sin_iva') }}">
            </div>

            <div class="form-group" style="display:flex; align-items:center; gap:10px; padding-top: 25px;">
                <input type="checkbox" id="precio_fijo" name="precio_fijo" value="1" {{ old('precio_fijo') ? 'checked' : '' }}>
                <label for="precio_fijo" style="margin-bottom: 0; cursor:pointer;">Precio Fijo</label>
            </div>
        </div>

        {{-- BOTONES --}}
        <div class="form-actions mt-4">
            <button class="btn-primary" type="submit">Guardar</button>
            <a class="btn-secondary" href="{{ route('producto.lista') }}">Cancelar</a>
        </div>

    </form>

</div>
@endsection
