@extends('layouts.app')

@section('content')
<style>
    /* Asegurar que el contenedor principal ocupe todo el ancho disponible junto al sidebar */
    main.content {
        flex: 1 !important;
        width: calc(100% - 260px) !important;
        max-width: 100% !important;
        box-sizing: border-box !important;
    }

    .despacho-show-wrapper {
        width: 100%;
        max-width: 1250px;
        margin: 0 auto;
        padding: 10px 15px 30px 15px;
        box-sizing: border-box;
    }

    .despacho-page-title {
        font-size: 22px;
        font-weight: 700;
        color: #0f172a;
        margin: 0 0 20px 0;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    /* Alertas de sesión */
    .despacho-alert {
        padding: 12px 16px;
        border-radius: 8px;
        margin-bottom: 18px;
        font-size: 14px;
        font-weight: 600;
        box-shadow: 0 1px 3px rgba(0,0,0,0.08);
    }
    .despacho-alert-success {
        background-color: #dcfce7;
        color: #166534;
        border-left: 5px solid #22c55e;
    }
    .despacho-alert-danger {
        background-color: #fee2e2;
        color: #991b1b;
        border-left: 5px solid #ef4444;
    }

    /* Banner de estado Cotización */
    .despacho-cotizacion-banner {
        background-color: #fef3c7;
        border-left: 5px solid #f59e0b;
        color: #92400e;
        padding: 14px 18px;
        border-radius: 8px;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 14px;
        box-shadow: 0 2px 5px rgba(0,0,0,0.06);
    }
    .despacho-cotizacion-info {
        display: flex;
        align-items: center;
        gap: 14px;
    }
    .despacho-cotizacion-info h6 {
        margin: 0 0 3px 0;
        font-size: 14px;
        font-weight: 700;
        color: #92400e;
    }
    .despacho-cotizacion-info small {
        font-size: 12.5px;
        color: #78350f;
    }

    /* Tarjetas (Cards) */
    .despacho-card {
        background: #ffffff;
        border-radius: 10px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 2px 6px rgba(15, 23, 42, 0.06);
        margin-bottom: 22px;
        overflow: hidden;
    }
    .despacho-card-header {
        background: #f8fafc;
        padding: 13px 20px;
        border-bottom: 1px solid #e2e8f0;
    }
    .despacho-card-header h5 {
        margin: 0;
        font-size: 13px;
        font-weight: 700;
        color: #475569;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .despacho-card-body {
        padding: 20px;
    }

    /* Grid de Resumen de la Orden */
    .despacho-summary-grid-top {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 18px;
        padding-bottom: 16px;
    }
    .despacho-summary-grid-bottom {
        display: grid;
        grid-template-columns: 1fr 2fr 1.2fr;
        gap: 18px;
        padding-top: 16px;
        border-top: 1px solid #e2e8f0;
    }
    @media (max-width: 900px) {
        .despacho-summary-grid-top {
            grid-template-columns: repeat(2, 1fr);
        }
        .despacho-summary-grid-bottom {
            grid-template-columns: 1fr;
        }
    }
    @media (max-width: 550px) {
        .despacho-summary-grid-top {
            grid-template-columns: 1fr;
        }
    }

    .despacho-field-label {
        display: block;
        font-size: 11.5px;
        font-weight: 700;
        color: #64748b;
        text-transform: uppercase;
        margin-bottom: 6px;
        letter-spacing: 0.3px;
    }
    .despacho-field-value {
        font-size: 15px;
        font-weight: 600;
        color: #0f172a;
        display: block;
        word-break: break-word;
    }
    .despacho-field-order-num {
        font-size: 18px;
        font-weight: 800;
        color: #2563eb;
    }

    /* Badges */
    .despacho-badge {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 5px 12px;
        border-radius: 6px;
        font-size: 12px;
        font-weight: 700;
        line-height: 1.2;
    }
    .despacho-badge-warning {
        background-color: #f59e0b;
        color: #1e293b;
    }
    .despacho-badge-success {
        background-color: #10b981;
        color: #ffffff;
    }
    .despacho-badge-danger {
        background-color: #ef4444;
        color: #ffffff;
    }
    .despacho-badge-secondary {
        background-color: #64748b;
        color: #ffffff;
    }
    .despacho-badge-primary {
        background-color: #2563eb;
        color: #ffffff;
    }
    .despacho-badge-light {
        background-color: #f1f5f9;
        color: #1e293b;
        border: 1px solid #cbd5e1;
    }

    /* Tabla de Detalle de Productos */
    .despacho-table-wrap {
        width: 100%;
        overflow-x: auto;
    }
    .despacho-table {
        width: 100%;
        border-collapse: collapse;
        margin: 0;
        font-size: 13.5px;
    }
    .despacho-table thead th {
        background-color: #f1f5f9;
        color: #334155;
        font-weight: 700;
        font-size: 12.5px;
        text-transform: uppercase;
        padding: 11px 14px;
        border: 1px solid #cbd5e1;
        vertical-align: middle;
    }
    .despacho-table tbody td {
        padding: 11px 14px;
        border: 1px solid #e2e8f0;
        color: #1e293b;
        vertical-align: middle;
    }
    .despacho-table tbody tr:nth-child(even) {
        background-color: #f8fafc;
    }
    .despacho-table tbody tr:hover {
        background-color: #f1f5f9;
    }
    .despacho-table tfoot th {
        background-color: #f1f5f9;
        padding: 12px 14px;
        border: 1px solid #cbd5e1;
        font-size: 14px;
        font-weight: 800;
        color: #0f172a;
    }

    /* Botones de Acción */
    .despacho-actions-bar {
        display: flex;
        justify-content: flex-end;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
        margin-top: 20px;
    }
    .despacho-btn {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: 13px;
        font-weight: 600;
        padding: 8px 16px;
        border-radius: 6px;
        text-decoration: none;
        cursor: pointer;
        border: none;
        box-shadow: 0 1px 3px rgba(0,0,0,0.12);
        transition: opacity 0.15s ease, transform 0.1s ease;
    }
    .despacho-btn:hover {
        opacity: 0.92;
    }
    .despacho-btn-warning {
        background-color: #f59e0b;
        color: #111827;
    }
    .despacho-btn-success {
        background-color: #10b981;
        color: #ffffff;
    }
    .despacho-btn-outline {
        background-color: #ffffff;
        color: #334155;
        border: 1px solid #94a3b8;
    }
    .despacho-btn-secondary {
        background-color: #475569;
        color: #ffffff;
    }
</style>

<div class="despacho-show-wrapper">

    <h3 class="despacho-page-title">
        @if($orden->estado === 'COTIZACION')
            <i class="fa-solid fa-file-lines" style="color: #f59e0b;"></i>
            <span>Cotización #{{ $orden->numero_orden }}</span>
        @else
            <i class="fa-solid fa-file-invoice" style="color: #2563eb;"></i>
            <span>Orden de Despacho #{{ $orden->numero_orden }}</span>
        @endif
    </h3>

    {{-- ALERTAS DE SESIÓN --}}
    @if(session('success'))
        <div class="despacho-alert despacho-alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="despacho-alert despacho-alert-danger">{{ session('error') }}</div>
    @endif

    {{-- BANNER DE ESTADO COTIZACIÓN --}}
    @if(in_array(strtoupper(trim($orden->estado ?? '')), ['COTIZACION', 'BORRADOR']))
        <div class="despacho-cotizacion-banner no-print">
            <div class="despacho-cotizacion-info">
                <i class="fa-solid fa-circle-info" style="font-size: 26px; color: #d97706;"></i>
                <div>
                    <h6>DOCUMENTO EN ESTADO DE COTIZACIÓN</h6>
                    <small>Este documento no ha descontado metros ni libras de inventario, ni generado movimientos en el Kardex.</small>
                </div>
            </div>
            <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                <a href="{{ route('inventario.despacho.imprimir_cotizacion', $orden->id_orden_despacho) }}"
                   target="_blank"
                   class="despacho-btn despacho-btn-warning">
                    <i class="fa-solid fa-file-invoice-dollar"></i> Imprimir Cotización
                </a>
                <form action="{{ route('inventario.despacho.convertir', $orden->id_orden_despacho) }}"
                      method="POST"
                      style="display: inline; margin: 0;"
                      onsubmit="return confirm('¿Confirmas convertir la Cotización #{{ $orden->numero_orden }} a Orden de Despacho y procesar la salida física de inventario?');">
                    @csrf
                    <button type="submit" class="despacho-btn despacho-btn-success">
                        <i class="fa-solid fa-truck-fast"></i> Convertir a Despacho
                    </button>
                </form>
            </div>
        </div>
    @endif

    {{-- ================= ENCABEZADO MEJORADO ================= --}}
    <div class="despacho-card">
        <div class="despacho-card-header">
            <h5>Resumen de la Orden</h5>
        </div>
        <div class="despacho-card-body">
            {{-- Primera Fila --}}
            <div class="despacho-summary-grid-top">
                <div>
                    <span class="despacho-field-label">Número de Orden:</span>
                    <span class="despacho-field-order-num">{{ $orden->numero_orden }}</span>
                </div>

                <div>
                    <span class="despacho-field-label">Fecha de Registro:</span>
                    <span class="despacho-field-value">{{ \Carbon\Carbon::parse($orden->fecha)->format('d/m/Y') }}</span>
                </div>

                <div>
                    <span class="despacho-field-label">Cliente:</span>
                    <span class="despacho-field-value" title="{{ $orden->cliente->nombre ?? '-' }}">
                        {{ $orden->cliente->nombre ?? '-' }}
                    </span>
                </div>

                <div>
                    <span class="despacho-field-label">Vendedor:</span>
                    <span class="despacho-field-value">{{ $orden->vendedor->nombre ?? '' }} {{ $orden->vendedor->apellidos ?? '' }}</span>
                </div>
            </div>

            {{-- Segunda Fila --}}
            <div class="despacho-summary-grid-bottom">
                <div>
                    <span class="despacho-field-label">Estado de Orden:</span>
                    @php
                        $badgeClass = match($orden->estado) {
                            'COTIZACION' => 'despacho-badge-warning',
                            'FINALIZADA', 'DESPACHADA', 'ACTIVA' => 'despacho-badge-success',
                            'ANULADA' => 'despacho-badge-danger',
                            default => 'despacho-badge-secondary'
                        };
                    @endphp
                    <span class="despacho-badge {{ $badgeClass }}">
                        {{ $orden->estado }}
                    </span>
                </div>

                <div>
                    <span class="despacho-field-label">Observaciones de Despacho:</span>
                    <span style="color: #475569; font-size: 14px;">{{ $orden->observaciones ?? '— Sin observaciones registradas —' }}</span>
                </div>

                <div>
                    <span class="despacho-field-label">Tipo de Precio (Justificación):</span>
                    @if($orden->motivo_precio == 1)
                        <span class="despacho-badge despacho-badge-warning">Autorizado por gerencia</span>
                    @elseif($orden->motivo_precio == 2)
                        <span class="despacho-badge despacho-badge-warning">Igual a competencia</span>
                    @else
                        <span class="despacho-badge despacho-badge-secondary">Precio de Lista</span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- ================= DETALLE CON MERMAS ================= --}}
    <div class="despacho-card">
        <div class="despacho-card-header">
            <h5>Detalle de Productos</h5>
        </div>
        <div class="despacho-card-body" style="padding: 0;">
            <div class="despacho-table-wrap">
                <table class="despacho-table">
                    <thead>
                        <tr>
                            <th style="text-align: left;">Calidad</th>
                            <th style="text-align: left;">Producto / Medidas</th>
                            <th style="text-align: center;">Lote</th>
                            <th style="text-align: center;">Pieza</th>
                            <th style="text-align: center;">Ubicación</th>
                            <th style="text-align: center;">Cant. Cortes</th>
                            <th style="text-align: right;">Metros<br><small style="color: #64748b; font-weight: 500; text-transform: none;">(Total Neto)</small></th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $totalMetros = 0;
                            $totalLibras = 0;
                            $totalMermaMts = 0;
                            $totalMermaLbs = 0;

                            // Agrupación automática de cortes repetidos de la misma pieza y medida
                            $detallesAgrupados = $orden->detalles->groupBy(function($item) {
                                $metros = number_format(floatval($item->cantidad_metros), 4, '.', '');
                                $medidaSol = trim($item->medida_solicitada ?? '');
                                $idPieza = $item->id_pieza ?? 0;
                                $idLote = $item->id_lote ?? 0;
                                $idProd = $item->id_producto ?? 0;
                                return "{$idProd}_{$idLote}_{$idPieza}_{$metros}_{$medidaSol}";
                            })->map(function($grupo) {
                                $primer = $grupo->first();
                                return (object)[
                                    'familia'           => $primer->familia,
                                    'producto'          => $primer->producto,
                                    'lote'              => $primer->lote,
                                    'pieza'             => $primer->pieza,
                                    'cantidad_cortes'   => $grupo->count(),
                                    'medida_unitaria'   => floatval($primer->cantidad_metros),
                                    'medida_solicitada' => $primer->medida_solicitada,
                                    'cantidad_metros'   => $grupo->sum(fn($d) => floatval($d->cantidad_metros)),
                                    'cantidad_libras'   => $grupo->sum(fn($d) => floatval($d->cantidad_libras)),
                                    'merma_metros'      => $grupo->sum(fn($d) => floatval($d->merma_metros ?? 0)),
                                    'merma_libras'      => $grupo->sum(fn($d) => floatval($d->merma_libras ?? 0)),
                                    'precio_venta_sin_iva' => floatval($primer->precio_venta_sin_iva),
                                ];
                            });
                        @endphp

                        @foreach($detallesAgrupados as $d)
                            <tr>
                                <td style="text-align: left; color: #475569; font-weight: 600;">
                                    {{ $d->familia->nombre ?? '-' }}
                                </td>

                                <td style="text-align: left;">
                                    {{-- CONCATENACIÓN: FAMILIA - PRODUCTO --}}
                                    <div style="font-weight: 700; font-size: 13.5px; color: #0f172a;">
                                        {{ $d->familia->nombre ?? 'N/A' }} - {{ $d->producto->descripcion ?? 'S/D' }}
                                    </div>

                                    {{-- Medidas en naranja Acerosal --}}
                                    @php
                                        $mm = floatval($d->producto->milimetros ?? 0);
                                        $plg = $d->producto->pulgadas ?? '-';
                                    @endphp
                                    @if($mm > 0 || ($plg && $plg != '-'))
                                        <div style="color: #d97706; font-weight: 700; font-size: 11.5px; margin-top: 2px;">
                                            ({{ $mm > 0 ? $mm.' mm' : '' }}{{ ($mm > 0 && $plg != '-') ? ' / ' : '' }}{{ $plg != '-' ? $plg.' plg' : '' }})
                                        </div>
                                    @endif

                                    @if(!empty($d->medida_solicitada))
                                        <div style="color: #475569; font-size: 11.5px; margin-top: 4px;">
                                            <i class="fa-solid fa-ruler-horizontal" style="margin-right: 4px;"></i>
                                            Medida Solicitada: <strong style="color: #0f172a;">{{ $d->medida_solicitada }}</strong>
                                        </div>
                                    @endif
                                </td>

                                <td style="text-align: center;">{{ $d->lote->codigo ?? '-' }}</td>
                                <td style="text-align: center;">{{ $d->pieza->codigo ?? '-' }}</td>

                                {{-- COLUMNA: UBICACIÓN --}}
                                <td style="text-align: center;">
                                    <span class="despacho-badge despacho-badge-light">
                                        <i class="fa-solid fa-location-dot" style="color: #ef4444; font-size: 10px;"></i>
                                        {{ $d->familia->ubicacion->nombre ?? ($d->producto->ubicacion->nombre ?? 'B-01') }}
                                    </span>
                                </td>

                                {{-- COLUMNA: CANTIDAD / CORTES AGRUPADOS --}}
                                <td style="text-align: center;">
                                    @if($d->cantidad_cortes > 1)
                                        <span class="despacho-badge despacho-badge-primary">
                                            {{ $d->cantidad_cortes }} cortes
                                        </span>
                                        <div style="color: #64748b; font-size: 11px; margin-top: 4px;">
                                            de {{ number_format($d->medida_unitaria, 4) }} m c/u
                                        </div>
                                    @else
                                        <span class="despacho-badge despacho-badge-light">
                                            1 corte
                                        </span>
                                    @endif
                                </td>

                                <td style="text-align: right; font-weight: 700;">{{ number_format($d->cantidad_metros, 4) }}</td>
                            </tr>

                            @php
                                $totalMetros += $d->cantidad_metros;
                                $totalLibras += $d->cantidad_libras;
                                $totalMermaMts += ($d->merma_metros ?? 0);
                                $totalMermaLbs += ($d->merma_libras ?? 0);
                            @endphp
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="6" style="text-align: right;">TOTAL ACUMULADO</th>
                            <th style="text-align: right; color: #2563eb;">{{ number_format($totalMetros, 4) }}</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    {{-- ACCIONES --}}
    <div class="despacho-actions-bar no-print">
        @if(in_array(strtoupper(trim($orden->estado ?? '')), ['COTIZACION', 'BORRADOR']))
            <form action="{{ route('inventario.despacho.convertir', $orden->id_orden_despacho) }}"
                  method="POST"
                  style="display: inline; margin: 0;"
                  onsubmit="return confirm('¿Confirmas convertir la Cotización #{{ $orden->numero_orden }} a Orden de Despacho y procesar la salida física de inventario?');">
                @csrf
                <button type="submit" class="despacho-btn despacho-btn-success">
                    <i class="fa-solid fa-truck-fast"></i> Convertir a Despacho
                </button>
            </form>
        @elseif(strtoupper(trim($orden->estado ?? '')) === 'FINALIZADA')
            <span class="despacho-badge despacho-badge-light" style="color: #15803d; border-color: #86efac; padding: 8px 14px;">
                <i class="fa-solid fa-circle-check" style="color: #16a34a;"></i> Despacho Procesado
            </span>
        @endif

        <a href="{{ route('inventario.despacho.imprimir_cotizacion', $orden->id_orden_despacho) }}"
           target="_blank"
           class="despacho-btn despacho-btn-warning">
            <i class="fa-solid fa-file-invoice-dollar"></i> Imprimir Cotización
        </a>

        <button type="button" onclick="window.print()" class="despacho-btn despacho-btn-outline">
            <i class="fa-solid fa-print"></i> Hoja Despacho
        </button>

        <a href="{{ route('inventario.despacho.index') }}" class="despacho-btn despacho-btn-secondary">
            <i class="fa-solid fa-arrow-left"></i> Volver al Listado
        </a>
    </div>

</div>

<style media="print">
    @page {
        size: letter;
        margin: 1cm;
    }
    html, body {
        height: auto !important;
        min-height: 0 !important;
        margin: 0 !important;
        padding: 0 !important;
        overflow: visible !important;
        background: #ffffff !important;
    }
    .erp-container {
        display: block !important;
        width: 100% !important;
        height: auto !important;
        margin: 0 !important;
        padding: 0 !important;
    }
    .no-print,
    .despacho-btn,
    .despacho-alert,
    .despacho-cotizacion-banner,
    .despacho-actions-bar,
    .sidebar-toggle,
    header,
    .app-header,
    nav,
    .sidebar,
    #sidebar,
    footer,
    .app-footer {
        display: none !important;
    }
    main.content,
    .content {
        display: block !important;
        margin: 0 !important;
        padding: 0 !important;
        width: 100% !important;
        max-width: 100% !important;
        height: auto !important;
        overflow: visible !important;
    }
    .despacho-show-wrapper {
        max-width: 100% !important;
        width: 100% !important;
        margin: 0 !important;
        padding: 0 !important;
        page-break-after: avoid !important;
        break-after: avoid !important;
    }
    .despacho-card {
        border: 1px solid #cbd5e1 !important;
        box-shadow: none !important;
        margin-bottom: 14px !important;
        page-break-inside: avoid;
        break-inside: avoid;
    }
</style>
@endsection
