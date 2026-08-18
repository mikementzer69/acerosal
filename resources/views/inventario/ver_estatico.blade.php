@extends('layouts.app')

@section('content')
<div style="background: #111; color: #eee; padding: 20px; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;">

    {{-- 1. INFORMACIÓN GENERAL (Estilo Caja Negra) --}}
    <div style="background: #000; border: 1px solid #333; padding: 15px; border-radius: 5px; margin-bottom: 20px;">
        <h4 style="margin-top: 0; color: #fff; border-bottom: 1px solid #222; padding-bottom: 8px;">Información General</h4>
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; font-size: 0.9rem;">
            <div><strong>Factura:</strong> {{ $compra->numero_factura }}</div>
            <div><strong>Empresa:</strong> {{ $compra->empresa->nombre ?? 'N/A' }}</div>
            <div><strong>Proveedor:</strong> {{ $compra->proveedor->nombre }}</div>
            <div><strong>Emisión:</strong> {{ $compra->fecha_emision_factura ?? $compra->fecha_emision }}</div>
            <div><strong>Ingreso:</strong> {{ $compra->fecha_ingreso }}</div>
        </div>
    </div>

    {{-- 2. LISTADO DE LOTES --}}
@foreach($compra->lotes as $lote)

    {{-- 🟢 LÓGICA DE MEDIDAS (Igual que en el JS pero en PHP) --}}
    @php
        $prod = $lote->producto;
        $mm   = floatval($prod->milimetros);
        $plg  = $prod->pulgadas;
        $textoMedidas = "";

        if ($mm > 0 || ($plg && $plg != '-')) {
            $txtMM  = $mm > 0 ? "$mm mm" : '';
            $txtPLG = ($plg && $plg != '-') ? "$plg plg" : '';
            $sep    = ($txtMM && $txtPLG) ? ' / ' : '';

            $textoMedidas = "($txtMM $sep $txtPLG)";
        }
    @endphp

    {{-- 🔵 ENCABEZADO AZUL MODIFICADO --}}
    <div style="background-color: #1e3a8a; color: white; padding: 10px; margin-top: 20px; border-radius: 5px 5px 0 0; display: flex; justify-content: space-between; align-items: center;">

        <div style="font-size: 1.1em;">
            <strong>LOTE: {{ $lote->codigo }}</strong>
            <span style="color: #93c5fd; margin: 0 10px;">|</span>

            {{-- Aquí va el nombre y las medidas --}}
            <span style="font-weight: normal; color: #bfdbfe;">
                {{ $prod->descripcion }}
                <span style="font-size: 0.9em; color: #fbbf24; font-weight: bold;">
                    {{ $textoMedidas }}
                </span>
            </span>
        </div>

        <div style="font-weight: bold;">
            Total: {{ number_format($lote->cantidad_total_metros, 2) }} m
        </div>
    </div>

            {{-- Grid de Datos Técnicos (Como en tu imagen de ingreso) --}}
            <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 1px; background: #333; border-bottom: 1px solid #333;">
                <div style="background: #1a1a1a; padding: 10px;">
                    <label style="display: block; font-size: 0.7rem; color: #888;">Fecha ingreso</label>
                    <span>{{ $lote->fecha_ingreso }}</span>
                </div>
                <div style="background: #1a1a1a; padding: 10px;">
                    <label style="display: block; font-size: 0.7rem; color: #888;">Total piezas</label>
                    <span style="color: #fbbf24;">{{ $lote->piezas->count() }}</span>
                </div>
                <div style="background: #1a1a1a; padding: 10px;">
                    <label style="display: block; font-size: 0.7rem; color: #888;">Peso total (lb)</label>
                    <span style="color: #fbbf24;">{{ number_format($lote->peso_total_libras, 4) }}</span>
                </div>
                <div style="background: #1a1a1a; padding: 10px;">
                    <label style="display: block; font-size: 0.7rem; color: #888;">Relación lb/m</label>
                    <span style="color: #fbbf24;">{{ number_format($lote->relacion_cantidad_peso, 6) }}</span>
                </div>
            </div>

            {{-- Tabla de Piezas --}}
        <table style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr style="background: #111; color: #666; font-size: 0.75rem; text-transform: uppercase;">
                    <th style="padding: 12px; text-align: left; border-bottom: 1px solid #333;">Código Pieza</th>
                    <th style="padding: 12px; text-align: right; border-bottom: 1px solid #333;">Metros Iniciales</th>
                </tr>
            </thead>
        <tbody>
            @foreach($lote->piezas as $pieza)
                <tr style="border-bottom: 1px solid #222;">
                    {{-- El código sigue en amarillo --}}
                    <td style="padding: 10px 15px; font-family: monospace; color: #fbbf24; font-weight: bold;">
                        {{ $pieza->codigo }}
                    </td>

                    {{-- 🪄 CAMBIO AQUÍ: Regresamos al verde brillante y negrita para los metros --}}
                    <td style="padding: 10px 15px; text-align: right; color: #4ade80; font-weight: bold; font-size: 1.1rem;">
                        {{ number_format($pieza->cantidad_metros_inicial, 2) }}
                    </td>
                </tr>
            @endforeach
        </tbody>

    {{-- 🪄 FILA DE TOTALES --}}
    <tfoot style="background: rgba(74, 222, 128, 0.05);">
        <tr>
            <td style="padding: 12px 15px; text-align: right; color: #888; font-size: 0.8rem; text-transform: uppercase;">
                Suma Total del Lote:
            </td>
            <td style="padding: 12px 15px; text-align: right; color: #4ade80; font-size: 1rem;">
                @php
                    $sumaMetros = $lote->piezas->sum('cantidad_metros_inicial');
                @endphp
                <strong>{{ number_format($sumaMetros, 2) }} m</strong>

                {{-- Validación visual: Si coincide con el total del lote, ponemos un check --}}
                @if(abs($sumaMetros - $lote->cantidad_total_metros) < 0.01)
                    <i class="fa-solid fa-circle-check" style="margin-left: 5px;" title="Cuadre Perfecto"></i>
                @endif
            </td>
        </tr>
    </tfoot>
</table>
        </div>
    @endforeach

    <div style="margin-top: 30px; display: flex; gap: 15px;">
        <a href="{{ url()->previous() }}" style="color: #60a5fa; text-decoration: none; font-size: 0.9rem;">
            ← Volver al listado
        </a>
        <button onclick="window.print()" style="background: #333; color: #fff; border: 1px solid #444; padding: 5px 15px; border-radius: 4px; cursor: pointer;">
            <i class="fa-solid fa-print"></i> Imprimir Reporte
        </button>
    </div>
</div>
@endsection
