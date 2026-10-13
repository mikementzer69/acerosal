@extends('layouts.app')

@section('content')
<div class="container py-3">

    <h3 class="fw-bold text-dark mb-4">
        @if($orden->estado === 'COTIZACION')
            <i class="fa-solid fa-file-lines text-warning me-2"></i> Cotización #{{ $orden->numero_orden }}
        @else
            <i class="fa-solid fa-file-invoice text-primary me-2"></i> Orden de Despacho #{{ $orden->numero_orden }}
        @endif
    </h3>

    {{-- ALERTAS DE SESIÓN --}}
    @if(session('success'))
        <div class="alert alert-success shadow-sm mb-4">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger shadow-sm mb-4">{{ session('error') }}</div>
    @endif

    {{-- BANNER DE ESTADO COTIZACIÓN --}}
    @if($orden->estado === 'COTIZACION')
        <div class="alert alert-warning d-flex align-items-center justify-content-between p-3 mb-4 shadow-sm border-0" style="background-color: #fef3c7; border-left: 5px solid #f59e0b !important; color: #92400e;">
            <div class="d-flex align-items-center">
                <i class="fa-solid fa-circle-info fs-2 me-3" style="color: #d97706;"></i>
                <div>
                    <h6 class="mb-0 fw-bold">DOCUMENTO EN ESTADO DE COTIZACIÓN</h6>
                    <small>Este documento no ha descontado metros ni libras de inventario, ni generado movimientos en el Kardex.</small>
                </div>
            </div>
            <div class="d-flex align-items-center gap-2">
                <a href="{{ route('inventario.despacho.imprimir_cotizacion', $orden->id_orden_despacho) }}"
                   target="_blank"
                   class="btn btn-warning fw-bold px-3 shadow-sm text-dark"
                   style="background-color: #f59e0b; border: none;">
                    <i class="fa-solid fa-file-invoice-dollar me-1"></i> Imprimir Cotización
                </a>
                <form action="{{ route('inventario.despacho.convertir', $orden->id_orden_despacho) }}"
                      method="POST"
                      onsubmit="return confirm('¿Confirmas convertir la Cotización #{{ $orden->numero_orden }} a Orden de Despacho y procesar la salida física de inventario?');">
                    @csrf
                    <button type="submit" class="btn btn-success fw-bold px-3 shadow-sm" style="background-color: #10b981; border: none;">
                        <i class="fa-solid fa-truck-fast me-1"></i> Convertir a Despacho
                    </button>
                </form>
            </div>
        </div>
    @endif

    {{-- ================= ENCABEZADO MEJORADO ================= --}}
    <div class="card mb-4 border-0 shadow-sm">
        <div class="card-header bg-white py-3 border-bottom">
            <h5 class="mb-0 fw-bold text-secondary small text-uppercase">Resumen de la Orden</h5>
        </div>
        <div class="card-body bg-light-subtle">
            <div class="row g-4">

                <div class="col-md-3">
                    <strong class="text-dark small d-block mb-1 text-uppercase">Número de Orden:</strong>
                    <span class="fs-5 fw-bold text-primary">{{ $orden->numero_orden }}</span>
                </div>

                <div class="col-md-3">
                    <strong class="text-dark small d-block mb-1 text-uppercase">Fecha de Registro:</strong>
                    <span class="fs-6 fw-semibold text-dark">{{ \Carbon\Carbon::parse($orden->fecha)->format('d/m/Y') }}</span>
                </div>

                <div class="col-md-3">
                    <strong class="text-dark small d-block mb-1 text-uppercase">Cliente:</strong>
                    <span class="fs-6 fw-semibold text-dark text-truncate d-block" title="{{ $orden->cliente->nombre ?? '-' }}">
                        {{ $orden->cliente->nombre ?? '-' }}
                    </span>
                </div>

                <div class="col-md-3">
                    <strong class="text-dark small d-block mb-1 text-uppercase">Vendedor:</strong>
                    <span class="fs-6 fw-semibold text-dark">{{ $orden->vendedor->nombre }} {{ $orden->vendedor->apellidos ?? '' }}</span>
                </div>

                {{-- Segunda Fila --}}
                <div class="col-md-3 mt-3 border-top pt-3">
                    <strong class="text-dark small d-block mb-1 text-uppercase">Estado de Orden:</strong>
                    @php
                        $badgeClass = match($orden->estado) {
                            'COTIZACION' => 'bg-warning text-dark',
                            'FINALIZADA', 'DESPACHADA', 'ACTIVA' => 'bg-success text-white',
                            'ANULADA' => 'bg-danger text-white',
                            default => 'bg-secondary text-white'
                        };
                    @endphp
                    <span class="badge {{ $badgeClass }} px-3 py-2 shadow-sm">
                        {{ $orden->estado }}
                    </span>
                </div>

                <div class="col-md-5 mt-3 border-top pt-3">
                    <strong class="text-dark small d-block mb-1 text-uppercase">Observaciones de Despacho:</strong>
                    <span class="text-secondary">{{ $orden->observaciones ?? '— Sin observaciones registradas —' }}</span>
                </div>

                <div class="col-md-4 mt-3 border-top pt-3">
                    <strong class="text-dark small d-block mb-1 text-uppercase">Tipo de Precio (Justificación):</strong>
                    @if($orden->motivo_precio == 1)
                        <span class="badge bg-warning text-dark px-3 py-2 shadow-sm">Autorizado por gerencia</span>
                    @elseif($orden->motivo_precio == 2)
                        <span class="badge bg-warning text-dark px-3 py-2 shadow-sm">Igual a competencia</span>
                    @else
                        <span class="badge bg-secondary px-3 py-2 shadow-sm">Precio de Lista</span>
                    @endif
                </div>

            </div>
        </div>
    </div>

    {{-- ================= DETALLE CON MERMAS ================= --}}
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0 fw-bold text-secondary small text-uppercase">Detalle de Productos</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered table-sm mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3 align-middle">Calidad</th>
                            <th class="align-middle">Producto / Medidas</th>
                            <th class="align-middle">Lote</th>
                            <th class="align-middle">Pieza</th>
                            {{-- 🚩 NUEVA COLUMNA: UBICACIÓN --}}
                            <th class="text-center align-middle">Ubicación</th>
                            <th class="text-end align-middle">Metros<br><small class="text-muted">(Neto)</small></th>
                        </tr>
                    </thead>
                    <tbody>
                        {{-- ✨ PASO 1: INICIALIZAR VARIABLES (Esto evita el error Undefined) ✨ --}}
                        @php
                            $totalMetros = 0;
                            $totalLibras = 0;
                            $totalMermaMts = 0;
                            $totalMermaLbs = 0;
                        @endphp

                        @foreach($orden->detalles as $d)
                            <tr>
                                <td class="ps-3 small text-secondary">
                                    {{ $d->familia->nombre ?? '-' }}
                                </td>

                                <td>
                                    {{-- ✨ CONCATENACIÓN: FAMILIA - PRODUCTO ✨ --}}
                                    <div class="fw-bold" style="font-size: 13px;">
                                        {{ $d->familia->nombre ?? 'N/A' }} - {{ $d->producto->descripcion ?? 'S/D' }}
                                    </div>

                                    {{-- Medidas en naranja Acerosal --}}
                                    @php
                                        $mm = floatval($d->producto->milimetros ?? 0);
                                        $plg = $d->producto->pulgadas ?? '-';
                                    @endphp
                                    @if($mm > 0 || ($plg && $plg != '-'))
                                        <span style="color: #d97706; font-weight: bold; font-size: 11px;">
                                            ({{ $mm > 0 ? $mm.' mm' : '' }}
                                            {{ ($mm > 0 && $plg != '-') ? ' / ' : '' }}
                                            {{ $plg != '-' ? $plg.' plg' : '' }})
                                        </span>
                                    @endif

                                    @if(!empty($d->medida_solicitada))
                                        <div class="text-secondary mt-1" style="font-size: 11px;">
                                            <i class="fa-solid fa-ruler-horizontal me-1"></i>
                                            Medida Solicitada: <strong class="text-dark">{{ $d->medida_solicitada }}</strong>
                                        </div>
                                    @endif
                                </td>

                                <td class="small">{{ $d->lote->codigo ?? '-' }}</td>
                                <td class="small">{{ $d->pieza->codigo ?? '-' }}</td>

                                {{-- 🚩 COLUMNA: UBICACIÓN (Buscamos en pieza o producto) --}}
                                <td class="text-center">
                                    <span class="badge bg-light text-dark border small">
                                        <i class="fa-solid fa-location-dot text-danger me-1" style="font-size: 9px;"></i>
                                        {{ $d->familia->ubicacion->nombre ?? ($d->producto->ubicacion->nombre ?? 'B-01') }}
                                    </span>
                                </td>

                                <td class="text-end fw-bold">{{ number_format($d->cantidad_metros, 4) }}</td>

                            </tr>

                            {{-- ✨ PASO 2: SUMAR VALORES ✨ --}}
                            @php
                                $totalMetros += $d->cantidad_metros;
                                $totalLibras += $d->cantidad_libras;
                                $totalMermaMts += ($d->merma_metros ?? 0);
                                $totalMermaLbs += ($d->merma_libras ?? 0);
                            @endphp
                        @endforeach
                    </tbody>
                    <tfoot class="table-light fw-bold">
                        <tr>
                            {{-- Colspan ajustado a 5 por la nueva columna de ubicación --}}
                            <th colspan="5" class="text-end py-2">TOTAL ACUMULADO</th>
                            <th class="text-end text-primary">{{ number_format($totalMetros, 4) }}</th>

                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    {{-- ACCIONES --}}
    <div class="mt-4 text-end">
        @if($orden->estado === 'COTIZACION')
            <form action="{{ route('inventario.despacho.convertir', $orden->id_orden_despacho) }}"
                  method="POST"
                  style="display: inline-block;"
                  onsubmit="return confirm('¿Confirmas convertir la Cotización #{{ $orden->numero_orden }} a Orden de Despacho y procesar la salida física de inventario?');">
                @csrf
                <button type="submit" class="btn btn-success px-4 shadow-sm fw-bold">
                    <i class="fa-solid fa-truck-fast me-1"></i> Convertir a Despacho
                </button>
            </form>
        @endif
        <a href="{{ route('inventario.despacho.imprimir_cotizacion', $orden->id_orden_despacho) }}"
           target="_blank"
           class="btn btn-warning px-4 shadow-sm fw-bold ms-2 text-dark"
           style="background-color: #f59e0b; border: none;">
            <i class="fa-solid fa-file-invoice-dollar me-1"></i> Imprimir Cotización Valorizada
        </a>
        <button type="button" onclick="window.print()" class="btn btn-outline-dark px-4 shadow-sm ms-2">
            <i class="fa-solid fa-print me-1"></i> Imprimir Hoja Despacho
        </button>
        <a href="{{ route('inventario.despacho.index') }}" class="btn btn-secondary px-4 shadow-sm ms-2">
            Volver al Listado
        </a>
    </div>

</div>

<style>
@media print {
    .btn, .alert, header, nav, .sidebar, footer {
        display: none !important;
    }
    .card {
        border: 1px solid #ddd !important;
        box-shadow: none !important;
    }
    .container {
        max-width: 100% !important;
        width: 100% !important;
        padding: 0 !important;
    }
}
</style>
@endsection
