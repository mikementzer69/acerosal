<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        @page { margin: 0.5cm; }
        body { font-family: 'Helvetica', Arial, sans-serif; font-size: 8px; color: #334155; }

        .header { text-align: center; margin-bottom: 15px; border-bottom: 1px solid #e2e8f0; padding-bottom: 8px; }
        .header h2 { color: #2563eb; margin: 0; font-size: 16px; text-transform: uppercase; }
        .header p { margin: 2px 0; color: #64748b; font-size: 9px; }

        table { width: 100%; border-collapse: collapse; table-layout: fixed; }

        th {
            background-color: #f1f5f9;
            color: #475569;
            padding: 8px 3px;
            text-align: center;
            text-transform: uppercase;
            font-size: 7px;
            border: 1px solid #cbd5e1;
        }

        td { padding: 6px 3px; border: 1px solid #f1f5f9; vertical-align: middle; border: 1px solid #cbd5e1; }

        /* Anchos de columnas para que quepa todo */
        .col-fecha { width: 12%; }
        .col-tipo { width: 12%; }
        .col-cant { width: 9%; text-align: right; }
        .col-merma { width: 9%; text-align: right; color: #b45309; }
        .col-peso { width: 9%; text-align: right; color: #2563eb; font-weight: bold; }
        .col-total { width: 10%; text-align: right; background-color: #f8fafc; font-weight: bold; }
        .col-saldo { width: 11%; text-align: right; font-weight: bold; background-color: #fffbeb; }
        .col-comentario { width: 28%; color: #64748b; font-size: 7px; }

        .text-end { text-align: right; }
        .entrada { color: #16a34a; font-weight: bold; }
        .salida { color: #dc2626; font-weight: bold; }
    </style>
</head>
<body>
    <div class="header">
        <h2>Kardex de Inventario - Acerosal</h2>
        <p><strong>PRODUCTO:</strong>
            @if($producto)
                {{ $producto->codigo }} - {{ $producto->descripcion }} ({{ $producto->milimetros }}mm / {{ $producto->pulgadas }}")
            @else
                --- REPORTE GENERAL ---
            @endif
        </p>
        <p>Generado el {{ now()->format('d/m/Y H:i') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th class="col-fecha">Fecha</th>
                <th class="col-tipo">Movimiento</th>
                <th class="col-cant">Mts (Neto)</th>
                <th class="col-merma">Merma (Mts)</th>
                <th class="col-peso">Peso (Lbs)</th>
                <th class="col-total">Total Salida</th>
                <th class="col-saldo">Saldo Stock</th>
                <th class="col-comentario">Observaciones</th>
            </tr>
        </thead>
        <tbody>
            @if(isset($saldoInicial))
            <tr style="background-color: #f0f7ff; font-weight: bold;">
                <td style="text-align: center;">{{ $fechaInicio ? date('d/m/Y', strtotime($fechaInicio)) : '---' }}</td>
                <td colspan="5" style="text-align: right;">SALDO APERTURA:</td>
                <td class="col-saldo">{{ number_format($saldoInicial, 2) }}</td>
                <td class="col-comentario">Inventario inicial</td>
            </tr>
            @endif

            @foreach($movimientos as $m)
            <tr>
                <td style="text-align: center;">{{ date('d/m/Y H:i', strtotime($m->fecha)) }}</td>
                <td style="text-align: center;">
                    <span class="{{ str_contains(strtolower($m->tipo), 'entrada') ? 'entrada' : 'salida' }}">
                        {{ strtoupper(str_replace('_', ' ', $m->tipo)) }}
                    </span>
                </td>
                <td class="text-end">{{ number_format($m->cantidad, 2) }}</td>
                <td class="text-end" style="color: #b45309;">{{ number_format($m->tolerancia_aplicada ?? 0, 4) }}</td>
                <td class="text-end" style="color: #2563eb; font-weight: bold;">
                    {{ number_format(($m->peso_neto_libras ?? 0) + ($m->merma_libras_grabada ?? 0), 2) }}
                </td>
                <td class="text-end" style="font-weight: bold;">
                    {{ number_format($m->cantidad_total_retirada ?? $m->cantidad, 4) }}
                </td>
                <td class="col-saldo">{{ number_format($m->saldo_dinamico_global, 2) }}</td>
                <td class="col-comentario">{{ $m->origen }} - {{ $m->comentario ?? 'N/A' }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr style="background-color: #f1f5f9; border-top: 2px solid #2563eb;">
                <td colspan="5" style="text-align: right; font-weight: bold; padding: 8px;">RESUMEN DEL PERIODO:</td>
                <td class="text-end" style="padding: 8px;">
                    <span style="color: #16a34a;">+ {{ number_format($totalEntradas, 2) }}</span><br>
                    <span style="color: #dc2626;">- {{ number_format($totalSalidas, 2) }}</span>
                </td>
                <td class="text-end" style="font-weight: bold; background-color: #fffbeb; padding: 8px; font-size: 10px; color: #b45309;">
                    {{ number_format($saldoFinal, 2) }}
                    <div style="font-size: 6px; font-weight: normal; color: #64748b;">STOCK FINAL MTS</div>
                </td>
                <td></td>
            </tr>
        </tfoot>
    </table>
</body>
</html>
