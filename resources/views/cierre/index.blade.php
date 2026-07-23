@extends('layouts.app')

@section('content')

<div class="erp-section">

    <h2 class="erp-title">
        <i class="fa-solid fa-lock"></i> Cierre Contable
    </h2>

    {{-- Mensajes --}}
    @if(session('msg'))
        <div class="form-alert">
            {{ session('msg') }}
        </div>
    @endif

    <div class="erp-card" style="max-width: 500px; padding: 20px; margin-top:20px;">

        <form method="POST" action="{{ route('cierre.generar') }}">
            @csrf

            <div class="form-group" style="margin-bottom: 20px;">
                <label class="form-label">Año:</label>
                <input type="number" name="anio" class="form-input"
                       value="{{ date('Y') }}" required>
            </div>

            <div class="form-group" style="margin-bottom: 20px;">
                <label class="form-label">Mes:</label>
                <select name="mes" class="form-input" required>
                    @for($m=1; $m<=12; $m++)
                        <option value="{{ $m }}">
                            {{ $m }} - {{ \Carbon\Carbon::create()->month($m)->locale('es')->monthName }}
                        </option>
                    @endfor
                </select>
            </div>

            <div style="margin-top:25px;">
                <button class="btn-primary w-full" style="width:100%;">
                    🔒 Cerrar Mes Contable
                </button>
            </div>

        </form>
    </div>

</div>

@endsection
