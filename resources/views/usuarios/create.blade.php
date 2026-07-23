@extends('layouts.app')

@section('content')

<div class="form-container">

    <h2 class="form-title">
        <i class="fa-solid fa-user-plus"></i> Nuevo Usuario
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

    <form method="POST" action="{{ route('usuarios.store') }}">
        @csrf

        <div class="form-group">
            <label>Usuario *</label>
            <input type="text" name="username" value="{{ old('username') }}" required>
        </div>

        <div class="form-group">
            <label>Email *</label>
            <input type="email" name="email" value="{{ old('email') }}" required>
        </div>

        <div class="form-group">
            <label>Nombre *</label>
            <input type="text" name="nombre" value="{{ old('nombre') }}" required>
        </div>

        <div class="form-group">
            <label>Apellidos *</label>
            <input type="text" name="apellidos" value="{{ old('apellidos') }}" required>
        </div>

        <div class="form-group">
            <label>Celular</label>
            <input type="text" name="celular" value="{{ old('celular') }}">
        </div>

        <div class="form-group">
            <label>Password *</label>
            <input type="password" name="password" required>
        </div>
        <select name="rol_nombre" class="form-control" required>
                <option value="">-- Seleccione un Rol --</option>
                @foreach($roles as $rol)
                    <option value="{{ $rol->name }}">{{ $rol->name }}</option>
                @endforeach
            </select>



        <div class="form-actions">
            <button class="btn-primary" type="submit">Guardar</button>
            <a class="btn-secondary" href="{{ route('usuarios.index') }}">Cancelar</a>
        </div>

    </form>

</div>

@endsection
