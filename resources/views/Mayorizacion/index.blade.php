@extends('layouts.app')

@section('content')

<div class="erp-section">

    <h2 class="erp-title">
        <i class="fa-solid fa-calculator"></i> Generar Mayorización
    </h2>

    {{-- ALERTA --}}
    @if(session('msg'))
        <div class="form-alert">
            {{ session('msg') }}
        </div>
    @endif

    <div class="mayorizacion-card">

        <form action="{{ route('mayorizacion.generar') }}" method="POST">
            @csrf

            <div class="form-row">
                <label class="form-label">Año:</label>
                <input type="number"
                       name="anio"
                       value="{{ date('Y') }}"
                       class="form-input"
                       required>
            </div>

            <div class="form-row">
                <label class="form-label">Mes:</label>
                <select name="mes" class="form-input" required>
                    @for($i = 1; $i <= 12; $i++)
                        <option value="{{ $i }}">{{ $i }}</option>
                    @endfor
                </select>
            </div>

            <div class="form-actions">
                <button class="btn-primary">
                    <i class="fa-solid fa-play"></i> Generar Mayorización
                </button>
            </div>
        </form>

    </div>

</div>

{{-- ESTILOS PERSONALIZADOS --}}
<style>
    .mayorizacion-card {
        background: #0e1c33;
        border-radius: 12px;
        padding: 30px;
        width: 450px;
        margin-top: 20px;
        box-shadow: 0 0 15px rgba(0,0,0,0.3);
        color: white;
    }

    .form-row {
        margin-bottom: 15px;
    }

    .form-label {
        font-weight: bold;
        display: block;
        margin-bottom: 5px;
    }

    .form-input {
        width: 100%;
        height: 38px;
        border-radius: 6px;
        border: 1px solid #ccc;
        padding: 6px 10px;
        background: #f9f9f9;
        color: #333;
    }

    .form-input:focus {
        outline: none;
        border-color: #4a90e2;
        box-shadow: 0 0 5px rgba(74,144,226,0.7);
    }

    .form-actions {
        margin-top: 20px;
        text-align: right;
    }
</style>

@endsection
