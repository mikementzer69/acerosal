@extends('layouts.app')

@section('content')

<div class="form-container">

    <h2 class="form-title">
        <i class="fa-solid fa-user-pen"></i> Editar Usuario
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

    {{-- Ajustamos a 'mensaje' para que coincida con el controlador --}}
    @if(session('mensaje'))
        <div class="form-alert">{{ session('mensaje') }}</div>
    @endif

    <form method="POST" action="{{ route('usuarios.update', $usuario->id_usuario) }}">
        @csrf
        @method('PUT')

        <div class="form-group">
            <label>Usuario (No editable)</label>
            {{-- 🛡️ QUITAMOS el atributo 'name'. Al no tener name, el navegador NO lo envía
                 y así es IMPOSIBLE que el controlador lo arruine con un número. --}}
            <input type="text" value="{{ $usuario->username }}" readonly class="form-control-plaintext">
        </div>

        <div class="form-group">
            <label>Email *</label>
            <input type="email" name="email" required value="{{ old('email', $usuario->email) }}">
        </div>

        <div class="form-group">
            <label>Nombre *</label>
            <input type="text" name="nombre" required value="{{ old('nombre', $usuario->nombre) }}">
        </div>

        <div class="form-group">
            <label>Apellidos *</label>
            <input type="text" name="apellidos" required value="{{ old('apellidos', $usuario->apellidos) }}">
        </div>

        <div class="form-group">
            <label>Celular</label>
            <input type="text" name="celular" value="{{ old('celular', $usuario->celular) }}">
        </div>

        <div class="form-group">
            <label>Password (opcional)</label>
            <input type="password" name="password">
        </div>

        <div class="mb-3">
            <label class="form-label">Rol del Sistema <span class="text-danger">*</span></label>
            <select name="id_rol" class="form-control" required>
                <option value="">-- Seleccione un Rol --</option>
                @foreach($roles as $rol)
                    <option value="{{ $rol->id_rol }}" @selected($usuario->id_rol == $rol->id_rol)>
                        {{ strtoupper($rol->nombre ?? $rol->name) }}
                    </option>
                @endforeach
            </select>
            @error('id_rol')
                <small class="text-danger">{{ $message }}</small>
            @enderror
        </div>

        <div class="form-actions">
            <button class="btn-primary" type="submit">Actualizar</button>
            <a class="btn-secondary" href="{{ route('usuarios.index') }}">Cancelar</a>
        </div>

    </form>

</div>

@endsection
