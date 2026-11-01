@extends('layouts.app')

@section('content')

<div class="form-container">

    <h2 class="form-title">
        <i class="fa-solid fa-building"></i> Editar Empresa
    </h2>

    @if(session('msg'))
        <div class="form-alert">{{ session('msg') }}</div>
    @endif

    @php
        $logoActual = !empty($empresa->logo) ? $empresa->logo : ($empresa->id_empresa == 2 ? 'images/logo_empresa2.jpg' : 'images/logo.png');
    @endphp

    <form method="POST" action="{{ route('empresa.actualizar', $empresa->id_empresa) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="form-row">
            <div class="form-group" style="max-width: 180px;">
                <label>ID de Empresa</label>
                <input type="number" value="{{ $empresa->id_empresa }}" readonly
                       style="background-color: #334155; color: #38bdf8; font-weight: bold; cursor: not-allowed;">
            </div>

            <div class="form-group" style="flex: 1;">
                <label>Nombre *</label>
                <input type="text" name="nombre" required value="{{ old('nombre', $empresa->nombre) }}">
            </div>
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

        <div class="form-row" style="align-items: center; margin-top: 10px;">
            <div class="form-group">
                <label>Logo Almacenado</label>
                <select name="logo_existente" id="logo_existente" onchange="actualizarPreviewLogo(this.value)">
                    @foreach($logosDisponibles ?? ['images/logo.png', 'images/logo_empresa2.jpg'] as $rutaLogo)
                        <option value="{{ $rutaLogo }}" {{ $logoActual === $rutaLogo ? 'selected' : '' }}>
                            {{ basename($rutaLogo) }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label>Subir / Actualizar Logo (Opcional)</label>
                <input type="file" name="logo_file" accept="image/*" onchange="previewArchivoSubido(this)">
            </div>

            <div class="form-group" style="max-width: 180px; text-align: center;">
                <label>Logo Actual</label>
                <div style="background: #ffffff; padding: 8px; border-radius: 6px; border: 1px solid #475569; display: inline-block;">
                    <img id="preview_logo_img" src="{{ asset($logoActual) }}" alt="Logo actual" style="max-height: 50px; max-width: 150px; object-fit: contain;">
                </div>
            </div>
        </div>

        <div class="form-actions">
            <button class="btn-primary" type="submit">Actualizar</button>
            <a class="btn-secondary" href="{{ route('empresa.lista') }}">Cancelar</a>
        </div>

    </form>

</div>

<script>
function actualizarPreviewLogo(ruta) {
    if (ruta) {
        document.getElementById('preview_logo_img').src = "{{ asset('') }}" + ruta;
    }
}
function previewArchivoSubido(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('preview_logo_img').src = e.target.result;
        };
        reader.readAsDataURL(input.files[0]);
    }
}
</script>

@endsection
