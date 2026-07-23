@extends('layouts.app')

@section('content')
<div class="erp-section">

    {{-- TÍTULO CON ESTILO ERP --}}
    <h2 class="erp-title">
        <i class="fa-solid fa-file-invoice"></i> Órdenes de Despacho
    </h2>

    {{-- ALERTA DE MENSAJES --}}
    @if(session('success'))
        <div class="form-alert success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="form-alert danger">{{ session('error') }}</div>
    @endif

    {{-- BOTÓN NUEVA ORDEN --}}
    <div class="erp-actions">
        <a class="btn-primary" href="{{ route('inventario.despacho.create') }}">
            <i class="fa-solid fa-plus"></i> Nueva Orden
        </a>
    </div>

    {{-- TABLA ESTILO ERP --}}
    <div class="erp-table-container">
        <table class="erp-table">
            <thead>
                <tr>
                    <th style="width: 50px; text-align:center;">#</th>
                    <th>Número orden</th>
                    <th style="text-align:center;">Fecha</th>
                    <th>Cliente</th>
                    <th>Vendedor</th>
                    <th style="text-align:center;">Estado</th>
                    <th style="width:120px; text-align:center;">Acciones</th>
                </tr>
            </thead>

            <tbody>
                @forelse($ordenes as $o)
                    <tr>
                        <td style="text-align:center;">{{ $o->id_orden_despacho }}</td>
                        <td class="fw-bold">{{ $o->numero_orden }}</td>
                        <td style="text-align:center;">{{ \Carbon\Carbon::parse($o->fecha)->format('d/m/Y') }}</td>
                        <td>{{ $o->cliente->nombre ?? '-' }}</td>
                        <td>{{ $o->vendedor->name ?? '-' }}</td>
                        <td style="text-align:center;">
                            @php
                                $color = match($o->estado) {
                                    'BORRADOR' => 'secondary',
                                    'FINALIZADA', 'DESPACHADA' => 'success',
                                    'ANULADA' => 'danger',
                                    default => 'info'
                                };
                            @endphp
                            <span class="badge bg-{{ $color }}">{{ $o->estado }}</span>
                        </td>

                        <td class="erp-actions-cell" style="text-align:center;">

                            {{-- BOTÓN VER (ESTILO PRODUCTOS) --}}
                            <a href="{{ route('inventario.despacho.show', $o->id_orden_despacho) }}"
                                class="btn-table btn-edit" title="Ver Detalle">
                                <i class="fa-solid fa-eye"></i>
                            </a>

                            {{-- BOTÓN ANULAR (ESTILO PRODUCTOS) --}}
                            @if($o->estado != 'ANULADA' && ($o->facturado ?? 0) == 0)
                                <form action="{{ route('inventario.despacho.anular', $o->id_orden_despacho) }}"
                                      method="POST"
                                      style="display:inline;"
                                      onsubmit="return confirm('¿Mago, seguro de anular esta orden? El stock regresará al inventario.');">
                                    @csrf
                                    <button type="submit" class="btn-table btn-delete" title="Anular Orden">
                                        <i class="fa-solid fa-trash-can"></i>
                                    </button>
                                </form>
                            @endif

                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="no-results">No se encontraron órdenes de despacho.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

</div>
@endsection
