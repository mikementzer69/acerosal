@extends('layouts.app')

@section('content')
<div class="container">

    {{-- =========================
            ENCABEZADO
       ========================== --}}

    {{-- 🟢 LÓGICA DE MEDIDAS Y UBICACIÓN --}}
    @php
        // Validamos si existen las columnas en el objeto $producto
        $mm   = floatval($producto->milimetros ?? 0);
        $plg  = $producto->pulgadas ?? null;
        $textoMedidas = "";

        if ($mm > 0 || ($plg && $plg != '-')) {
            $txtMM  = $mm > 0 ? "$mm mm" : '';
            $txtPLG = ($plg && $plg != '-') ? "$plg plg" : '';
            $sep    = ($txtMM && $txtPLG) ? ' / ' : '';

            $textoMedidas = "($txtMM $sep $txtPLG)";
        }

        // ✨ MAGIA: Buscamos el nombre de la ubicación si no viene cargado ✨
        if (!isset($producto->nombre_ubicacion) && isset($producto->id_ubicacion)) {
            $ubiEncontrada = \DB::table('ubicaciones')
                ->where('id_ubicacion', $producto->id_ubicacion)
                ->first();
            $producto->nombre_ubicacion = $ubiEncontrada ? $ubiEncontrada->nombre : 'Sin Ubicación';
        }
    @endphp

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="mb-0">
                {{ $producto->descripcion }}

                {{-- ✨ AQUÍ AGREGAMOS LAS MEDIDAS EN NARANJA ✨ --}}
                @if($textoMedidas)
                    <span style="color: #d97706; font-size: 0.8em; font-weight: bold; margin: 0 5px;">
                        {{ $textoMedidas }}
                    </span>
                @endif

                <small class="text-muted" style="font-size: 0.6em;">({{ $producto->codigo }})</small>
            </h3>

            <span class="badge bg-info text-dark mt-2">
                Total Disponible: {{ number_format($producto->stock_metros, 2) }} Unidades
            </span>
            <span class="badge bg-success mt-2">
                Peso Total: {{ number_format($producto->peso_total_libras, 2) }} Lbs
            </span>
            
            {{-- ✨ UBICACIÓN EN EL ENCABEZADO (CON ICONO) ✨ --}}
            <span class="badge bg-dark mt-2">
                <i class="fa-solid fa-location-dot text-warning"></i> {{ $producto->nombre_ubicacion ?? 'Sin Ubicación' }}
            </span>
        </div>

        <a href="{{ route('inventario.inventario.consulta') }}" class="btn btn-secondary">
            <i class="fa-solid fa-arrow-left"></i> Volver
        </a>
    </div>

    {{-- =========================
            LISTADO DE LOTES
       ========================== --}}
    @php
        // Si el controlador manda un stdClass (DB::table), buscamos los lotes aquí mismo
        $lotesReales = \App\Models\Inventario\Lote::where('id_producto', $producto->id_producto)
                        ->with('piezas') 
                        ->orderBy('fecha_ingreso', 'desc')
                        ->get();
    @endphp
    @forelse ($lotesReales as $lote)
        <div class="card mb-4 shadow-sm">
            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                <div>
                    <strong>Lote:</strong> {{ $lote->codigo }}
                    <span class="mx-2">|</span>
                    <small class="text-muted">Ingreso: {{ \Carbon\Carbon::parse($lote->fecha_ingreso)->format('d/m/Y') }}</small>
                </div>
                <div>
                    <span class="badge bg-secondary">Piezas: {{ $lote->piezas->count() }}</span>
                    <span class="badge bg-secondary">Peso Lote: {{ number_format($lote->peso_total_libras, 2) }} Lbs</span>
                </div>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm table-striped mb-0">
                        <thead class="table-primary">
                            <tr>
                                <th style="width: 20%">Código Pieza</th>
                                <th>Estado</th>
                                <th class="text-end">Longitud Inicial</th>
                                <th class="text-end">Longitud Actual</th>
                                <th class="text-end">Peso Actual</th>
                                <th class="text-center">Ubicación</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($lote->piezas as $pieza)
                                <tr>
                                    <td class="fw-bold">{{ $pieza->codigo }}</td>
                                    <td>
                                        @if($pieza->eliminado)
                                            <span class="badge bg-danger">ANULADA</span>
                                        @elseif($pieza->cantidad_metros_actual == 0)
                                            <span class="badge bg-secondary">AGOTADA</span>
                                        @elseif($pieza->cantidad_metros_actual < $pieza->cantidad_metros_inicial)
                                            <span class="badge bg-warning text-dark">RECORTADA</span>
                                        @else
                                            <span class="badge bg-success">NUEVA</span>
                                        @endif
                                    </td>
                                    <td class="text-end">{{ number_format($pieza->cantidad_metros_inicial, 2) }} m</td>

                                    <td class="text-end {{ $pieza->cantidad_metros_actual < $pieza->cantidad_metros_inicial ? 'text-danger fw-bold' : '' }}">
                                        {{ number_format($pieza->cantidad_metros_actual, 2) }} m
                                    </td>

                                    <td class="text-end">{{ number_format($pieza->peso_libras_actual, 2) }} lb</td>
                                    
                                    {{-- ✨ AQUÍ ESTÁ EL CAMBIO DE VISIBILIDAD MÁGICA ✨ --}}
                                    <td class="text-center">
                                        {{-- Usamos un badge sólido con fondo oscuro y texto blanco para máximo contraste --}}
                                        <span class="badge bg-dark text-white fw-bold" style="font-size: 0.95em; padding: 5px 10px; border-radius: 4px;">
                                            <i class="fa-solid fa-map-pin text-warning me-1"></i> {{ $producto->nombre_ubicacion ?? 'Sin Ubicación' }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-3">
                                        No hay piezas activas en este lote.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer text-muted" style="font-size: 0.8rem;">
                Relación Peso/Longitud: {{ number_format($lote->relacion_cantidad_peso, 4) }}
            </div>
        </div>
    @empty
        <div class="alert alert-warning text-center">
            Este producto no tiene lotes registrados en el historial.
        </div>
    @endforelse

</div>
@endsection