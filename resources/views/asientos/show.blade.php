@extends('layouts.app')

@section('content')

<style>
/* --------------------------
   ESTILO PARA PANTALLA
--------------------------- */
.show-container {
    background: #0b1d38;
    padding: 25px;
    border-radius: 12px;
    color: #fff;
    max-width: 900px;
    margin: auto;
}

.show-title {
    font-size: 22px;
    font-weight: bold;
    margin-bottom: 15px;
}

.show-box {
    background: #102544;
    padding: 20px;
    border-radius: 10px;
    margin-bottom: 20px;
}

.show-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 15px;
}

.show-table th {
    background: #17345c;
    padding: 10px;
    border-bottom: 1px solid #2d4f7c;
    text-align: left;
}

.show-table td {
    padding: 10px;
    border-bottom: 1px solid #1b3357;
}

.total-row td {
    font-weight: bold;
    background: #122b4c;
}

/* --------------------------
   ESTILO PARA IMPRESIÓN
--------------------------- */
@media print {

    body, html {
        background: white !important;
        color: black !important;
    }

    .show-container {
        background: white !important;
        color: black !important;
        box-shadow: none !important;
    }

    .show-box {
        background: white !important;
        border: 1px solid #ccc !important;
    }

    .show-table th {
        background: #eaeaea !important;
        color: #000 !important;
        border: 1px solid #999 !important;
    }

    .show-table td {
        border: 1px solid #ccc !important;
    }

    .total-row td {
        background: #f0f0f0 !important;
        font-weight: bold !important;
    }

    .btn, .erp-actions {
        display: none !important;
    }
}

</style>

<div class="show-container">

    <h2 class="show-title">
        📘 Asiento Contable #{{ $asiento->id }}
    </h2>

    <div class="show-box">
        <p><strong>Fecha:</strong> {{ $asiento->fecha }}</p>
        <p><strong>Descripción:</strong> {{ $asiento->descripcion }}</p>
    </div>

    <div class="show-box">
        <h4>Detalle del Asiento</h4>

        <table class="show-table">
            <thead>
                <tr>
                    <th>Cuenta</th>
                    <th>Detalle</th>
                    <th>Debe</th>
                    <th>Haber</th>
                </tr>
            </thead>

            <tbody>
<tbody>
        @foreach($asiento->detalles as $detalle)
            <tr style="
                @if($detalle->debe > 0)
                    background-color: rgba(0, 150, 0, 0.35);
                @elseif($detalle->haber > 0)
                    background-color: rgba(200, 0, 0, 0.35);
                @endif
            ">
                <td>{{ $detalle->cuenta->codigo }} - {{ $detalle->cuenta->nombre }}</td>
                <td>{{ $detalle->descripcion }}</td>
                <td>{{ number_format($detalle->debe, 2) }}</td>
                <td>{{ number_format($detalle->haber, 2) }}</td>
            </tr>
        @endforeach


            <tr class="total-row">
                <td colspan="2" style="text-align:right;">Totales:</td>
                <td>{{ number_format($asiento->total_debe, 2) }}</td>
                <td>{{ number_format($asiento->total_haber, 2) }}</td>
            </tr>
            </tbody>

        </table>
    </div>

    <div style="margin-top:20px;">
        <a href="{{ route('asientos.index') }}" class="btn-secondary">← Volver</a>

        <button onclick="window.print()" class="btn-primary" style="margin-left:10px;">
            🖨 Imprimir
        </button>
    </div>

</div>

@endsection
