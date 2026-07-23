@extends('layouts.app')

@section('content')

<div class="form-container" style="max-width: 1200px !important;">

    {{-- CABECERA --}}
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
        <h2 class="form-title" style="margin: 0;">
            <i class="fa-solid fa-file-invoice-dollar"></i> Detalle de Compra
            <span style="font-size: 0.6em; color: #888; font-weight: normal; margin-left: 10px;">
                @if($compra->numero_factura === 'FACTURAS MÚLTIPLES')
                    <span style="color: #60a5fa; border: 1px solid #3b82f6; padding: 2px 10px; border-radius: 15px; font-size: 0.8em;">FACTURACIÓN MÚLTIPLE</span>
                @else
                    #{{ $compra->numero_factura }}
                @endif
            </span>
        </h2>

        <div style="background: #2d3748; color: #fff; padding: 8px 20px; border-radius: 30px; font-size: 0.9rem; border: 1px solid #4a5568;">
            <i class="fa-regular fa-calendar"></i> Ingreso: {{ $compra->fecha_ingreso }}
        </div>
    </div>

    {{-- SECCIÓN 1: INFORMACIÓN GENERAL --}}
    <h5 style="color: #aaa; border-bottom: 1px solid #444; margin-bottom: 20px; padding-bottom: 5px; font-size: 0.9rem; text-transform: uppercase; letter-spacing: 1px;">
        Información General
    </h5>

    <div style="display: flex; gap: 20px; flex-wrap: wrap; margin-bottom: 40px;">
        {{-- Tarjeta Proveedor --}}
        <div style="flex: 2; min-width: 300px;">
            <label style="display:block; margin-bottom: 5px; color: #aaa; font-size: 0.85em;">Proveedor</label>
            <div style="background: #111; color: #fff; padding: 15px; border: 1px solid #444; border-radius: 6px; font-size: 1.1em; display: flex; align-items: center;">
                <i class="fa-solid fa-truck-field" style="color: #60a5fa; margin-right: 12px; font-size: 1.3em;"></i>
                <span style="font-weight: bold;">{{ $compra->proveedor->nombre }}</span>
            </div>
        </div>

        {{-- Tarjeta Factura (Dinámica) --}}
        <div style="flex: 1; min-width: 180px;">
            <label style="display:block; margin-bottom: 5px; color: #aaa; font-size: 0.85em;">N° Factura General</label>
            <div style="background: #111; color: #ddd; padding: 15px; border: 1px solid #444; border-radius: 6px; text-align: center; font-weight: bold;">
                @if($compra->tipo_facturacion === 'multiple')
                    <span style="color: #60a5fa;">MÚLTIPLES</span>
                @else
                    {{ $compra->numero_factura }}
                @endif
            </div>
        </div>

        {{-- Tarjeta Fecha Emisión --}}
        <div style="flex: 1; min-width: 180px;">
            <label style="display:block; margin-bottom: 5px; color: #aaa; font-size: 0.85em;">Fecha Emisión Factura</label>
            <div style="background: #111; color: #ddd; padding: 15px; border: 1px solid #444; border-radius: 6px; text-align: center;">
                {{ $compra->fecha_emision_factura }}
            </div>
        </div>

        {{-- Tarjeta Tasa de Cambio --}}
        <div style="flex: 1; min-width: 150px;">
            <label style="display:block; margin-bottom: 5px; color: #aaa; font-size: 0.85em;">Tasa de Cambio</label>
            <div style="background: #111; color: #fbbf24; padding: 15px; border: 1px solid #444; border-radius: 6px; text-align: right; font-weight: bold; font-size: 1.1em;">
                {{ number_format($compra->tasa_cambio, 4) }}
            </div>
        </div>
    </div>

 {{-- ============================================================
         SECCIÓN 2: RESUMEN POR CALIDAD
        ============================================================ --}}
    <h5 style="color: #aaa; border-bottom: 1px solid #444; margin: 30px 0 20px 0; padding-bottom: 5px; font-size: 0.9rem; text-transform: uppercase; letter-spacing: 1px;">
        Resumen por Calidad
    </h5>

    {{-- ✨ Definimos la lógica una sola vez para que no se corran las columnas --}}
    @php
        $esMultiple = ($compra->numero_factura === 'FACTURAS MÚLTIPLES');
    @endphp

    <div style="overflow-x: auto; margin-bottom: 30px; border-radius: 8px; border: 1px solid #333;">
        <table class="erp-table">
            <thead>
                <tr>
                    <th style="width: 25%;">Calidad</th>

                    @if($esMultiple)
                        <th style="color: #60a5fa; width: 20%;">N° Factura</th>
                    @endif

                    <th style="text-align: right;">Peso Total (LB)</th>
                    <th style="text-align: right; color: #4ade80;">Total Calidad (EUR)</th>
                    <th style="text-align: right;">Total Calidad (USD)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($compra->compraFamilias as $fam)
                <tr>
                    <td style="font-weight: bold;">{{ $fam->familia->nombre }}</td>

                    {{-- ✨ Si la cabecera existe, pintamos la celda SÍ O SÍ --}}
                    @if($esMultiple)
                        <td style="color: #60a5fa; font-weight: bold;">
                            <span style="background: rgba(59, 130, 246, 0.1); padding: 4px 10px; border-radius: 4px; border: 1px solid rgba(59, 130, 246, 0.3);">
                                {{ $fam->numero_factura ?? 'No asignada' }}
                            </span>
                        </td>
                    @endif

                    <td class="text-right">{{ number_format($fam->peso_total_libras, 4) }}</td>
                    <td class="text-right" style="color: #4ade80;">€ {{ number_format($fam->importe_total_eu, 2) }}</td>
                    <td class="text-right" style="font-weight: bold;">$ {{ number_format($fam->total_familia, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
   </div>

    {{-- SECCIÓN 3: PRODUCTOS DETALLADOS --}}
    <h5 style="color: #aaa; border-bottom: 1px solid #444; margin: 30px 0 20px 0; padding-bottom: 5px; font-size: 0.9rem; text-transform: uppercase; letter-spacing: 1px;">
        Detalle de Productos
    </h5>

    <div style="overflow-x: auto; margin-bottom: 30px; border-radius: 8px; border: 1px solid #333;">
        <table class="erp-table">
            <thead>
                <tr>
                    <th>Producto</th>
                    <th style="text-align: center;">MM</th>
                    <th style="text-align: center;">PULG</th>
                    <th style="text-align: center;">Cant.</th>
                    <th style="text-align: right;">Peso KG</th>
                    <th style="text-align: right;">Peso LB</th>
                    <th style="text-align: right; color: #4ade80;">Precio EUR</th>
                    <th style="text-align: right; color: #4ade80;">Importe EUR</th>
                    <th style="text-align: right;">Precio USD</th>
                    <th style="text-align: right;">Importe USD</th>
                </tr>
            </thead>
            <tbody>
                @foreach($compra->compraProductos as $detalle)
                <tr>
                    <td>{{ $detalle->producto->descripcion }}</td>
                    <td class="text-center" style="color: #60a5fa;">{{ $detalle->producto->milimetros ?? '-' }}</td>
                    <td class="text-center" style="color: #fbbf24;">{{ $detalle->producto->pulgadas ?? '-' }}</td>
                    <td class="text-center">{{ $detalle->cantidad }}</td>
                    <td class="text-right">{{ number_format($detalle->peso_kg, 4) }}</td>
                    <td class="text-right">{{ number_format($detalle->peso_libra, 4) }}</td>
                    <td class="text-right" style="color: #4ade80;">€ {{ number_format($detalle->precio_kg_eu, 2) }}</td>
                    <td class="text-right" style="color: #4ade80;">€ {{ number_format($detalle->importe_eu, 2) }}</td>
                    <td class="text-right">$ {{ number_format($detalle->precio_kg_usd, 4) }}</td>
                    <td class="text-right">$ {{ number_format($detalle->importe_dolares, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- SECCIÓN 4: COSTOS ADICIONALES --}}
    <h5 style="color: #aaa; border-bottom: 1px solid #444; margin: 30px 0 20px 0; padding-bottom: 5px; font-size: 0.9rem; text-transform: uppercase; letter-spacing: 1px;">
        Costos Adicionales
    </h5>

    @if($compra->compraCostos->count() > 0)
        <div style="overflow-x: auto; margin-bottom: 30px; border-radius: 8px; border: 1px solid #333; max-width: 800px;">
            <table class="erp-table" style="width: 100%;">
                <thead>
                    <tr>
                        <th style="color:#fff; width: 50%;">Descripción</th>
                        <th style="color:#fff; text-align: right; width: 25%;">Valor EUR</th>
                        <th style="color:#fff; text-align: right; width: 25%;">Valor USD</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($compra->compraCostos as $c)
                    <tr>
                        <td>{{ $c->costo->nombre }}</td>
                        <td style="text-align: right;">€ {{ number_format($c->valor_eu, 2) }}</td>
                        <td style="text-align: right; color: #f87171; font-weight: bold;">$ {{ number_format($c->valor_usd, 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div style="padding: 15px; background: #222; border-radius: 6px; color: #888; border: 1px dashed #444; margin-bottom: 30px; text-align: center;">
            <i class="fa-solid fa-circle-info"></i> No se registraron costos adicionales.
        </div>
    @endif

    {{-- SECCIÓN 5: TOTALES GENERALES --}}
    <div style="background-color: #222; padding: 30px; border-radius: 10px; border: 1px solid #444; margin-top: 40px; box-shadow: 0 10px 25px rgba(0,0,0,0.3);">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 30px;">
            <div>
                <label style="color: #888; font-size: 0.8rem; text-transform: uppercase; letter-spacing: 1px; display: block; margin-bottom: 5px;">Peso Total (KG)</label>
                <div style="color: #fff; font-size: 1.5rem; font-weight: bold;">{{ number_format($compra->peso_total_kg, 4) }}</div>
            </div>
            <div>
                <label style="color: #888; font-size: 0.8rem; text-transform: uppercase; letter-spacing: 1px; display: block; margin-bottom: 5px;">Peso Total (LB)</label>
                <div style="color: #fff; font-size: 1.5rem; font-weight: bold;">{{ number_format($compra->peso_total_libras, 4) }}</div>
            </div>
            <div>
                <label style="color: #888; font-size: 0.8rem; text-transform: uppercase; letter-spacing: 1px; display: block; margin-bottom: 5px;">Total Costos (USD)</label>
                <div style="color: #f87171; font-size: 1.5rem; font-weight: bold;">$ {{ number_format($compra->total_costos_adicionales, 2) }}</div>
            </div>
            <div>
                <label style="color: #888; font-size: 0.8rem; text-transform: uppercase; letter-spacing: 1px; display: block; margin-bottom: 5px;">TOTAL FACTURA (USD)</label>
                <div style="color: #60a5fa; font-size: 2rem; font-weight: 800;">
                    $ {{ number_format($compra->total_factura, 2) }}
                </div>
            </div>
        </div>
    </div>

    {{-- BOTONES ACCIÓN --}}
    <div class="erp-actions" style="margin-top: 40px; text-align: center; display: flex; justify-content: center; gap: 15px;">
        <a href="{{ route('compras.index') }}" class="btn-secondary" style="padding: 12px 30px; text-decoration: none;">
            <i class="fa-solid fa-arrow-left"></i> Volver
        </a>
        <a href="{{ route('compras.edit', $compra->id_compra) }}" class="btn-primary" style="padding: 12px 30px; text-decoration: none; background: #2563eb;">
            <i class="fa-solid fa-pen"></i> Editar esta Compra
        </a>
    </div>

</div>

@endsection
