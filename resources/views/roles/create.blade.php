@extends('layouts.app')

@section('content')

<div class="form-container">

    <h2 class="form-title">
        <i class="fa-solid fa-user-shield"></i> Nuevo Rol
    </h2>

    {{-- ERRORES --}}
    @if ($errors->any())
        <div class="form-alert">
            <ul>
                @foreach ($errors->all() as $e)
                    <li>{{ $e }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- MENSAJE --}}
    @if(session('msg'))
        <div class="form-alert">{{ session('msg') }}</div>
    @endif

    <form method="POST" action="{{ route('roles.store') }}">
        @csrf

        {{-- NOMBRE --}}
        <div class="form-group">
            <label>Nombre *</label>
            <input type="text"
                   name="name"
                   value="{{ old('name') }}"
                   required>
        </div>

        {{-- DESCRIPCIÓN --}}
        <div class="form-group">
            <label>Descripción</label>
            <textarea name="guard_name"
                      rows="3">{{ old('guard_name') }}</textarea>
        </div>

        {{-- BOTONES --}}
        <div class="form-actions">
            <button class="btn-primary" type="submit">Guardar</button>
            <a class="btn-secondary" href="{{ route('roles.index') }}">Cancelar</a>
        </div>

    </form>

</div>

@endsection
