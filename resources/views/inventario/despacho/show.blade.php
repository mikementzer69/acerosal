@extends('layouts.app')

@section('content')
<div class="container py-3">

    <h3 class="fw-bold text-dark mb-4">Orden de Despacho</h3>

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
                    <span class="badge {{ $orden->estado === 'ACTIVA' ? 'bg-success' : 'bg-info text-dark' }} px-3 py-2 shadow-sm">
                        {{ $orden->estado }}
                    </span>
                </div>

                <div class="col-md-9 mt-3 border-top pt-3">
                    <strong class="text-dark small d-block mb-1 text-uppercase">Observaciones de Despacho:</strong>
                    <span class="text-secondary">{{ $orden->observaciones ?? '— Sin observaciones registradas —' }}</span>
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
                                        {{ $d->familia->ubicacion ?? ($d->producto->ubicacion ?? 'B-01') }}
                                    </span>
                                </td>

                                <td class="text-end fw-bold">{{ number_format($d->cantidad_metros, 2) }}</td>

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
                            <th class="text-end text-primary">{{ number_format($totalMetros, 2) }}</th>

                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    {{-- ACCIONES --}}
    <div class="mt-4 text-end">
        @if($orden->estado === 'BORRADOR')
            <a href="#" class="btn btn-success px-4 shadow-sm">
                Despachar orden
            </a>
        @endif
        <a href="{{ route('inventario.despacho.index') }}" class="btn btn-secondary px-4 shadow-sm ms-2">
            Volver al Listado
        </a>
    </div>

</div>
@endsection
