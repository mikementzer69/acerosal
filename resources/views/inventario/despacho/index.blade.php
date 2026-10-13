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

    {{-- BUSCADOR Y FILTROS --}}
    <form method="GET" action="{{ route('inventario.despacho.index') }}" class="erp-search-form">
        <div class="search-row">
            <div>
                <label>Buscar Orden:</label>
                <input type="text"
                       name="busqueda"
                       value="{{ request('busqueda') }}"
                       class="search-input"
                       placeholder="N° orden, cliente o vendedor...">
            </div>

            <div>
                <label>Estado:</label>
                <select name="estado" class="search-input" style="color: #fff; min-width: 170px;">
                    <option value="">-- Todos los estados --</option>
                    <option value="COTIZACION" {{ request('estado') === 'COTIZACION' ? 'selected' : '' }}>COTIZACION</option>
                    <option value="FINALIZADA" {{ request('estado') === 'FINALIZADA' ? 'selected' : '' }}>FINALIZADA</option>
                    <option value="ANULADA" {{ request('estado') === 'ANULADA' ? 'selected' : '' }}>ANULADA</option>
                </select>
            </div>

            <button class="btn-primary" type="submit">
                <i class="fa-solid fa-magnifying-glass"></i> Buscar
            </button>

            <a class="btn-secondary" href="{{ route('inventario.despacho.index') }}">
                <i class="fa-solid fa-eraser"></i> Limpiar
            </a>
        </div>
    </form>

    {{-- BARRA DE ACCIÓN Y CONTADOR --}}
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; flex-wrap: wrap; gap: 10px;">
        <div class="erp-actions" style="margin: 0;">
            <a class="btn-primary" href="{{ route('inventario.despacho.create') }}">
                <i class="fa-solid fa-plus"></i> Nueva Orden / Cotización
            </a>
        </div>
        <p class="erp-total" style="margin: 0;">
            Total Registros: <strong style="color: #38bdf8;">{{ $ordenes instanceof \Illuminate\Pagination\LengthAwarePaginator ? $ordenes->total() : $ordenes->count() }}</strong>
        </p>
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
                    <th style="width:140px; text-align:center;">Acciones</th>
                </tr>
            </thead>

            <tbody>
                @forelse($ordenes as $o)
                    <tr>
                        <td style="text-align:center;">{{ $o->id_orden_despacho }}</td>
                        <td class="fw-bold">{{ $o->numero_orden }}</td>
                        <td style="text-align:center;">{{ \Carbon\Carbon::parse($o->fecha)->format('d/m/Y') }}</td>
                        <td>{{ $o->cliente->nombre ?? '-' }}</td>
                        <td>{{ $o->vendedor->nombre ?? '-' }} {{ $o->vendedor->apellidos ?? '' }}</td>
                        <td style="text-align:center;">
                            @php
                                $estadoUpper = strtoupper(trim($o->estado ?? ''));
                                $color = match($estadoUpper) {
                                    'COTIZACION' => 'warning text-dark',
                                    'BORRADOR' => 'secondary',
                                    'FINALIZADA', 'DESPACHADA' => 'success',
                                    'ANULADA' => 'danger',
                                    default => 'info'
                                };
                            @endphp
                            <span class="badge bg-{{ $color }}">{{ $o->estado }}</span>
                        </td>

                        <td class="erp-actions-cell" style="text-align:center; white-space: nowrap;">

                            {{-- BOTÓN CONVERTIR A DESPACHO / INDICADOR DE ESTADO --}}
                            @if(in_array(strtoupper(trim($o->estado ?? '')), ['COTIZACION', 'BORRADOR']))
                                <form action="{{ route('inventario.despacho.convertir', $o->id_orden_despacho) }}"
                                      method="POST"
                                      style="display:inline;"
                                      onsubmit="return confirm('¿Confirmas convertir la Cotización #{{ $o->numero_orden }} a Orden de Despacho y descargar inventario?');">
                                    @csrf
                                    <button type="submit" class="btn-table" style="background-color: #10b981; color: #fff;" title="Convertir Cotización a Despacho">
                                        <i class="fa-solid fa-truck-fast"></i>
                                    </button>
                                </form>
                            @elseif(in_array(strtoupper(trim($o->estado ?? '')), ['FINALIZADA', 'DESPACHADA']))
                                <span class="btn-table" style="background-color: #064e3b; color: #34d399; border: 1px solid #059669; cursor: default; display: inline-flex; align-items: center; justify-content: center;" title="Despacho ya Procesado">
                                    <i class="fa-solid fa-circle-check"></i>
                                </span>
                            @elseif(strtoupper(trim($o->estado ?? '')) === 'ANULADA')
                                <span class="btn-table" style="background-color: #450a0a; color: #f87171; border: 1px solid #7f1d1d; cursor: default; display: inline-flex; align-items: center; justify-content: center;" title="Orden Anulada">
                                    <i class="fa-solid fa-ban"></i>
                                </span>
                            @endif

                            {{-- BOTÓN VER DETALLE --}}
                            <a href="{{ route('inventario.despacho.show', $o->id_orden_despacho) }}"
                                class="btn-table btn-edit" title="Ver Detalle">
                                <i class="fa-solid fa-eye"></i>
                            </a>

                            {{-- BOTÓN IMPRIMIR COTIZACIÓN VALORIZADA --}}
                            <a href="{{ route('inventario.despacho.imprimir_cotizacion', $o->id_orden_despacho) }}"
                               target="_blank"
                               class="btn-table"
                               style="background-color: #f59e0b; color: #111827;"
                               title="Imprimir Cotización Valorizada">
                                <i class="fa-solid fa-file-invoice-dollar"></i>
                            </a>

                            {{-- BOTÓN ANULAR / CANCELAR --}}
                            @if($o->estado != 'ANULADA' && ($o->facturado ?? 0) == 0)
                                <form action="{{ route('inventario.despacho.anular', $o->id_orden_despacho) }}"
                                      method="POST"
                                      style="display:inline;"
                                      onsubmit="return confirm('{{ $o->estado === 'COTIZACION' ? '¿Seguro de cancelar esta cotización?' : '¿Seguro de anular esta orden? El stock regresará al inventario.' }}');">
                                    @csrf
                                    <button type="submit" class="btn-table btn-delete" title="{{ $o->estado === 'COTIZACION' ? 'Cancelar Cotización' : 'Anular Orden' }}">
                                        <i class="fa-solid fa-trash-can"></i>
                                    </button>
                                </form>
                            @endif

                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="no-results" style="text-align: center; padding: 25px;">
                            <i class="fa-solid fa-folder-open" style="font-size: 2em; color: #64748b; margin-bottom: 8px; display: block;"></i>
                            No se encontraron órdenes de despacho registradas.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- PAGINACIÓN --}}
    @if($ordenes instanceof \Illuminate\Pagination\LengthAwarePaginator)
        <div class="erp-pagination">
            {{ $ordenes->withQueryString()->onEachSide(1)->links('pagination::bootstrap-5') }}
        </div>
    @endif

</div>

<style>
/* Estilos para la paginación del ERP en modo oscuro */
.erp-pagination {
    margin-top: 25px;
    display: flex;
    justify-content: center;
}

.erp-pagination nav {
    display: flex;
    flex-direction: column;
    align-items: center;
}

.erp-pagination .pagination {
    display: flex;
    list-style: none;
    gap: 6px;
    padding: 0;
    margin: 0;
    align-items: center;
}

.erp-pagination .page-item {
    display: inline-block;
}

.erp-pagination .page-link {
    background-color: #1e293b !important;
    border: 1px solid #334155 !important;
    color: #e2e8f0 !important;
    padding: 7px 13px;
    border-radius: 6px;
    text-decoration: none;
    font-size: 0.88rem;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s ease;
}

.erp-pagination .page-item.active .page-link {
    background-color: #3b82f6 !important;
    border-color: #3b82f6 !important;
    color: #ffffff !important;
    font-weight: 700;
    box-shadow: 0 0 10px rgba(59, 130, 246, 0.4);
}

.erp-pagination .page-item.disabled .page-link {
    background-color: #0f172a !important;
    border-color: #1e293b !important;
    color: #475569 !important;
    cursor: not-allowed;
    opacity: 0.6;
}

.erp-pagination .page-link:hover:not(.disabled) {
    background-color: #334155 !important;
    color: #38bdf8 !important;
    border-color: #38bdf8 !important;
}

.erp-pagination .text-muted,
.erp-pagination p {
    color: #94a3b8 !important;
    font-size: 0.85em;
    margin-bottom: 12px !important;
}

.erp-pagination svg {
    width: 16px;
    height: 16px;
}
</style>
@endsection
