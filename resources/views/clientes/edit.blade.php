@extends('layouts.app')

<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<style>
/* Ajuste de Select2 para el tema oscuro */
.select2-container--default .select2-selection--single {
    background-color: #111 !important;
    border: 1px solid #444 !important;
    height: 45px !important;
    color: white !important;
}
.select2-container--default .select2-selection--single .select2-selection__rendered {
    color: white !important;
    line-height: 45px !important;
}
.select2-container--default .select2-selection--single .select2-selection__arrow {
    height: 43px !important;
}
.select2-dropdown {
    background-color: #111 !important;
    border: 1px solid #444 !important;
    color: white !important;
}
.select2-search__field {
    background-color: #222 !important;
    color: white !important;
    border: 1px solid #444 !important;
}
.select2-results__option--highlighted[aria-selected] {
    background-color: #3b82f6 !important;
}
</style>
@section('content')

<div class="form-container">

    <h2 class="form-title">
        <i class="fa-solid fa-user-pen"></i> Editar Cliente
    </h2>

    @if ($errors->any())
        <div class="form-alert form-error">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST"
          action="{{ route('clientes.update', $cliente->id_cliente) }}"
          class="erp-form">

        @csrf
        @method('PUT')

        {{-- TÍTULO 1 --}}
        <h5 style="color: #aaa; border-bottom: 1px solid #444; margin-bottom: 25px; padding-bottom: 5px; font-size: 0.9rem; text-transform: uppercase; letter-spacing: 1px;">
            Datos de Identificación
        </h5>

        {{-- LÍNEA 1: CÓDIGO | TIPO | DOCUMENTO --}}
        {{-- Aumentamos el gap a 30px para mayor separación --}}
        <div style="display: flex; gap: 30px; margin-bottom: 20px; flex-wrap: wrap;">

            {{-- Código (Solo lectura - Gris metálico para resaltar pero oscuro) --}}
            <div style="flex: 1; min-width: 150px;">
                <label style="display:block; margin-bottom: 8px; color: #ddd;">Código</label>
                <input type="text"
                       name="codigo"
                       value="{{ $cliente->codigo }}"
                       style="width: 100%; background-color: #4a5568 !important; color: #fff !important; font-weight: bold; border: 1px solid #2d3748; padding: 10px; border-radius: 4px;">
            </div>

            {{-- Tipo Cliente (Fondo Negro, Letra Blanca) --}}
            <div style="flex: 1; min-width: 150px;">
                <label style="display:block; margin-bottom: 8px; color: #ddd;">Tipo Cliente *</label>
                <select name="tipo_cliente" style="width: 100%; height: 45px; padding: 5px 10px; background-color: #111; color: #fff; border: 1px solid #444; border-radius: 4px;">
                    <option value="NATURAL" {{ $cliente->tipo_cliente == 'NATURAL' ? 'selected' : '' }}>Persona Natural</option>
                    <option value="JURIDICO" {{ $cliente->tipo_cliente == 'JURIDICO' ? 'selected' : '' }}>Jurídico (Empresa)</option>
                    <option value="EXENTO" {{ $cliente->tipo_cliente == 'EXENTO' ? 'selected' : '' }}>Contribuyente Exento</option>
                </select>
            </div>

            {{-- Origen --}}
            <div style="flex: 1; min-width: 120px;">
                <label style="display:block; margin-bottom: 8px; color: #ddd;">Origen *</label>
                <select name="origen" style="width: 100%; height: 45px; padding: 5px 10px; background-color: #111; color: #fff; border: 1px solid #444; border-radius: 4px;">
                    <option value="N" {{ $cliente->origen == 'N' ? 'selected' : '' }}>Nacional</option>
                    <option value="E" {{ $cliente->origen == 'E' ? 'selected' : '' }}>Extranjero</option>
                </select>
            </div>

            {{-- Tipo Contribuyente --}}
            <div style="flex: 1; min-width: 150px;">
                <label style="display:block; margin-bottom: 8px; color: #ddd;">Contribuyente *</label>
                <select name="tipo_contribuyente" style="width: 100%; height: 45px; padding: 5px 10px; background-color: #111; color: #fff; border: 1px solid #444; border-radius: 4px;">
                    <option value="O" {{ $cliente->tipo_contribuyente == 'O' ? 'selected' : '' }}>Otros (O)</option>
                    <option value="P" {{ $cliente->tipo_contribuyente == 'P' ? 'selected' : '' }}>Pequeño (P)</option>
                    <option value="M" {{ $cliente->tipo_contribuyente == 'M' ? 'selected' : '' }}>Mediano (M)</option>
                    <option value="G" {{ $cliente->tipo_contribuyente == 'G' ? 'selected' : '' }}>Grande (G)</option>
                </select>
            </div>

            {{-- Documento --}}
            <div style="flex: 1; min-width: 180px;">
                <label style="display:block; margin-bottom: 8px; color: #ddd;">Documento (DUI)</label>
                <input type="text" name="documento" value="{{ old('documento', $cliente->documento) }}" placeholder="00000000-0"
                       style="width: 100%; padding: 10px; background-color: #111; color: #fff; border: 1px solid #444; border-radius: 4px;">
            </div>
            
            {{-- NIT --}}
            <div style="flex: 1; min-width: 180px;">
                <label style="display:block; margin-bottom: 8px; color: #ddd;">NIT</label>
                <input type="text" name="nit" value="{{ old('nit', $cliente->nit) }}" placeholder="0000-000000-000-0"
                       style="width: 100%; padding: 10px; background-color: #111; color: #fff; border: 1px solid #444; border-radius: 4px;">
            </div>

            {{-- NRC --}}
            <div style="flex: 1; min-width: 150px;">
                <label style="display:block; margin-bottom: 8px; color: #ddd;">NRC</label>
                <input type="text" name="nrc" value="{{ old('nrc', $cliente->nrc) }}" placeholder="123456-7"
                       style="width: 100%; padding: 10px; background-color: #111; color: #fff; border: 1px solid #444; border-radius: 4px;">
            </div>

            {{-- Giro de Negocio --}}
            <div style="flex: 1; min-width: 250px;">
                <label style="display:block; margin-bottom: 8px; color: #ddd;">Giro de Negocio</label>
                <select name="id_giro" style="width: 100%; height: 45px; padding: 5px 10px; background-color: #111; color: #fff; border: 1px solid #444; border-radius: 4px;">
                    <option value="">-- Seleccione --</option>
                    @foreach($giros as $giro)
                        <option value="{{ $giro->id }}" {{ $cliente->id_giro == $giro->id ? 'selected' : '' }}>
                            {{ $giro->codigo }} - {{ $giro->descripcion }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        {{-- LÍNEA 2: NOMBRES --}}
        <div style="display: flex; gap: 30px; margin-bottom: 20px; flex-wrap: wrap;">
            <div style="flex: 1; min-width: 250px;">
                <label style="display:block; margin-bottom: 8px; color: #ddd;">Nombre Fiscal (Razón Social) *</label>
                <input type="text" name="nombre" value="{{ old('nombre', $cliente->nombre) }}" required
                       style="width: 100%; padding: 10px; background-color: #111; color: #fff; border: 1px solid #444; border-radius: 4px;">
            </div>
            <div style="flex: 1; min-width: 250px;">
                <label style="display:block; margin-bottom: 8px; color: #ddd;">Nombre Comercial</label>
                <input type="text" name="nombre_comercial" value="{{ old('nombre_comercial', $cliente->nombre_comercial) }}"
                       style="width: 100%; padding: 10px; background-color: #111; color: #fff; border: 1px solid #444; border-radius: 4px;">
            </div>
        </div>

        {{-- LÍNEA EXTRA: DOCUMENTOS EXTRANJEROS --}}
        <div style="display: flex; gap: 30px; margin-bottom: 20px; flex-wrap: wrap;">
            <div style="flex: 1; min-width: 200px;">
                <label style="display:block; margin-bottom: 8px; color: #ddd;">NIT Extranjero (NITE)</label>
                <input type="text" name="nite" value="{{ old('nite', $cliente->nite) }}" placeholder="Opcional..."
                       style="width: 100%; padding: 10px; background-color: #111; color: #fff; border: 1px solid #444; border-radius: 4px;">
            </div>
            <div style="flex: 1; min-width: 200px;">
                <label style="display:block; margin-bottom: 8px; color: #ddd;">Pasaporte</label>
                <input type="text" name="pasaporte" value="{{ old('pasaporte', $cliente->pasaporte) }}" placeholder="Opcional..."
                       style="width: 100%; padding: 10px; background-color: #111; color: #fff; border: 1px solid #444; border-radius: 4px;">
            </div>
        </div>

        {{-- TÍTULO 2 --}}
        <h5 style="color: #aaa; border-bottom: 1px solid #444; margin: 30px 0 25px 0; padding-bottom: 5px; font-size: 0.9rem; text-transform: uppercase; letter-spacing: 1px;">
            Contacto y Ubicación
        </h5>

        {{-- LÍNEA 3: TELÉFONO | CORREO --}}
        <div style="display: flex; gap: 30px; margin-bottom: 20px; flex-wrap: wrap;">
            <div style="flex: 1; min-width: 200px;">
                <label style="display:block; margin-bottom: 8px; color: #ddd;">Teléfono</label>
                <input type="text" name="telefono" value="{{ old('telefono', $cliente->telefono) }}"
                       style="width: 100%; padding: 10px; background-color: #111; color: #fff; border: 1px solid #444; border-radius: 4px;">
            </div>
            <div style="flex: 1; min-width: 200px;">
                <label style="display:block; margin-bottom: 8px; color: #ddd;">Correo Electrónico</label>
                <input type="email" name="correo" value="{{ old('correo', $cliente->correo) }}"
                       style="width: 100%; padding: 10px; background-color: #111; color: #fff; border: 1px solid #444; border-radius: 4px;">
            </div>
        </div>

        {{-- LÍNEA EXTRA: DIRECCIÓN | CONTACTO --}}
        <div style="display: flex; gap: 30px; margin-bottom: 20px; flex-wrap: wrap;">
             <div style="flex: 1; min-width: 200px;">
                <label style="display:block; margin-bottom: 8px; color: #ddd;">Dirección</label>
                <input type="text" name="direccion" value="{{ old('direccion', $cliente->direccion) }}"
                       style="width: 100%; padding: 10px; background-color: #111; color: #fff; border: 1px solid #444; border-radius: 4px;">
            </div>
            <div style="flex: 1; min-width: 200px;">
                <label style="display:block; margin-bottom: 8px; color: #ddd;">Contacto Principal</label>
                <input type="text" name="contacto_principal" value="{{ old('contacto_principal', $cliente->contacto_principal) }}"
                       style="width: 100%; padding: 10px; background-color: #111; color: #fff; border: 1px solid #444; border-radius: 4px;">
            </div>
        </div>

        {{-- LÍNEA 4: DEPARTAMENTO | CIUDAD --}}
        <div style="display: flex; gap: 30px; margin-bottom: 20px; flex-wrap: wrap;">
            <div style="flex: 1; min-width: 200px;">
                <label style="display:block; margin-bottom: 8px; color: #ddd;">Departamento</label>
                <select name="departamento" style="width: 100%; height: 45px; padding: 5px 10px; background-color: #111; color: #fff; border: 1px solid #444; border-radius: 4px;">
                    @foreach(['San Salvador', 'La Libertad', 'Santa Ana', 'San Miguel', 'Sonsonate', 'Ahuachapán', 'Usulután', 'La Paz', 'La Unión', 'Cuscatlán', 'Chalatenango', 'Morazán', 'San Vicente', 'Cabañas'] as $depto)
                        <option value="{{ $depto }}" {{ $cliente->departamento == $depto ? 'selected' : '' }}>
                            {{ $depto }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div style="flex: 1; min-width: 200px;">
                <label style="display:block; margin-bottom: 8px; color: #ddd;">Ciudad</label>
                <input type="text" name="ciudad" value="{{ old('ciudad', $cliente->ciudad) }}"
                       style="width: 100%; padding: 10px; background-color: #111; color: #fff; border: 1px solid #444; border-radius: 4px;">
            </div>
        </div>

        {{-- TÍTULO 3 --}}
        <h5 style="color: #aaa; border-bottom: 1px solid #444; margin: 30px 0 25px 0; padding-bottom: 5px; font-size: 0.9rem; text-transform: uppercase; letter-spacing: 1px;">
            Crédito y Estado
        </h5>

        {{-- LÍNEA 5: LÍMITE | DÍAS | ESTADO --}}
        <div style="display: flex; gap: 30px; margin-bottom: 20px; flex-wrap: wrap;">
            <div style="flex: 1; min-width: 150px;">
                <label style="display:block; margin-bottom: 8px; color: #ddd;">Límite Crédito ($)</label>
                <input type="number" step="0.01" name="limite_credito" value="{{ old('limite_credito', $cliente->limite_credito) }}"
                       style="width: 100%; padding: 10px; background-color: #111; color: #fff; border: 1px solid #444; border-radius: 4px;">
            </div>
            <div style="flex: 1; min-width: 150px;">
                <label style="display:block; margin-bottom: 8px; color: #ddd;">Días Crédito</label>
                <input type="number" name="dias_credito" value="{{ old('dias_credito', $cliente->dias_credito) }}"
                       style="width: 100%; padding: 10px; background-color: #111; color: #fff; border: 1px solid #444; border-radius: 4px;">
            </div>
            <div style="flex: 1; min-width: 150px;">
                <label style="display:block; margin-bottom: 8px; color: #ddd;">Estado</label>
                <select name="estado" style="width: 100%; height: 45px; padding: 5px 10px; background-color: #111; color: #fff; border: 1px solid #444; border-radius: 4px;">
                    <option value="ACTIVO" {{ $cliente->estado == 'ACTIVO' ? 'selected' : '' }}>ACTIVO</option>
                    <option value="INACTIVO" {{ $cliente->estado == 'INACTIVO' ? 'selected' : '' }}>INACTIVO</option>
                    <option value="BLOQUEADO" {{ $cliente->estado == 'BLOQUEADO' ? 'selected' : '' }}>BLOQUEADO</option>
                </select>
            </div>
            
            <div style="flex: 1; min-width: 150px; display: flex; align-items: center; margin-top: 25px;">
                <input type="checkbox" name="exento" value="1" {{ old('exento', $cliente->exento) ? 'checked' : '' }} id="exento" style="width: 20px; height: 20px; margin-right: 10px; cursor: pointer;">
                <label for="exento" style="color: #ddd; cursor: pointer;">Cliente Exento de Impuestos</label>
            </div>
        </div>

        {{-- BOTONES --}}
        <div class="erp-actions" style="margin-top: 40px; text-align: center;">
            <button class="btn-primary" type="submit" style="padding: 12px 30px; font-size: 1rem; cursor: pointer;">
                <i class="fa-solid fa-rotate"></i> Actualizar
            </button>
            <a class="btn-secondary" href="{{ route('clientes.index') }}" style="padding: 12px 30px; font-size: 1rem; text-decoration: none; margin-left: 15px; cursor: pointer;">
                Cancelar
            </a>
        </div>

    </form>
</div>

@endsection

<script>
$(document).ready(function() {
    $('select[name="id_giro"]').select2({
        placeholder: '-- Escribe para buscar giro --',
        allowClear: true
    });
});

document.addEventListener('DOMContentLoaded', function() {
    const duiInput = document.querySelector('input[name="documento"]');
    const nitInput = document.querySelector('input[name="nit"]');
    const nrcInput = document.querySelector('input[name="nrc"]');

    if (duiInput) {
        duiInput.addEventListener('input', function(e) {
            let x = e.target.value.replace(/\D/g, '').match(/(\d{0,8})(\d{0,1})/);
            e.target.value = !x[2] ? x[1] : x[1] + '-' + x[2];
        });
    }

    if (nitInput) {
        nitInput.addEventListener('input', function(e) {
            let x = e.target.value.replace(/\D/g, '').match(/(\d{0,4})(\d{0,6})(\d{0,3})(\d{0,1})/);
            e.target.value = !x[2] ? x[1] : x[1] + (x[2] ? '-' + x[2] : '') + (x[3] ? '-' + x[3] : '') + (x[4] ? '-' + x[4] : '');
        });
    }

    if (nrcInput) {
        nrcInput.addEventListener('input', function(e) {
            let x = e.target.value.replace(/\D/g, '').match(/(\d{0,7})(\d{0,1})/);
            e.target.value = !x[2] ? x[1] : x[1] + '-' + x[2];
        });
    }
});
</script>
