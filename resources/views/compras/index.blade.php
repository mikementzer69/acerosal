@extends('layouts.app')

@section('content')

{{-- Contenedor ancho para que la tabla respire --}}
<div class="form-container" style="max-width: 1400px !important;">

    {{-- CABECERA Y BUSCADOR --}}
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 25px;">

        <h2 class="form-title" style="margin: 0;">
            <i class="fa-solid fa-cart-shopping"></i> Gestión de Compras
        </h2>

        {{-- Botón de Nueva Compra --}}
        <a href="{{ route('compras.nueva') }}" class="btn-primary" style="padding: 10px 20px; text-decoration: none; font-size: 0.95rem;">
            <i class="fa-solid fa-plus"></i> Nueva Compra
        </a>
    </div>

    {{-- BARRA DE FILTROS (Visualmente lista para cuando quieras programar búsqueda) --}}
    <div style="background-color: #222; padding: 15px; border-radius: 8px; margin-bottom: 20px; border: 1px solid #444; display: flex; align-items: center; gap: 10px;">
        <i class="fa-solid fa-magnifying-glass" style="color: #666;"></i>
        <input type="text" placeholder="Buscar por número de factura o proveedor..."
               style="background: transparent; border: none; color: #fff; width: 100%; outline: none; font-size: 0.95rem;">
    </div>

    @if(session('success'))
        <div class="form-alert form-success">
            <i class="fa-solid fa-check-circle"></i> {{ session('success') }}
        </div>
    @endif

    {{-- TABLA DE DATOS --}}
{{-- TABLA DE DATOS ACTUALIZADA --}}
<div style="overflow-x: auto; border-radius: 8px; border: 1px solid #333;">
    <table class="erp-table" style="width: 100%; border-collapse: collapse;">
        <thead>
            <tr>
                <th style="color: #fff; padding: 12px 15px; text-align: center; width: 80px;">ID</th>
                <th style="color: #fff; padding: 12px 15px; text-align: left;">Factura</th>
                <th style="color: #fff; padding: 12px 15px; text-align: left;">Proveedor</th>
                <th style="color: #fff; padding: 12px 15px; text-align: center;">Fecha Ingreso</th>
                {{-- ✨ NUEVA COLUMNA DE EUROS ✨ --}}
                <th style="color: #4ade80; padding: 12px 15px; text-align: right;">Total (EUR)</th>
                <th style="color: #60a5fa; padding: 12px 15px; text-align: right;">Total (USD)</th>
                <th style="color: #fff; padding: 12px 15px; text-align: center; width: 120px;">Acciones</th>
            </tr>
        </thead>
        <tbody>
            @forelse($compras as $c)
                <tr style="border-bottom: 1px solid #333; transition: background 0.3s;">
                    <td style="text-align: center; color: #666;">{{ $c->id_compra }}</td>

                    <td style="font-weight: bold; color: #fff;">
                        <i class="fa-solid fa-file-invoice" style="color: #888; margin-right: 5px;"></i>
                        {{ $c->numero_factura }}
                    </td>

                    <td style="color: #ddd;">{{ $c->proveedor->nombre ?? '---' }}</td>

                    <td style="text-align: center; color: #aaa;">
                        <span style="background: #1a1a1a; padding: 4px 10px; border-radius: 15px; border: 1px solid #333; font-size: 0.85em;">
                            {{ $c->fecha_ingreso }}
                        </span>
                    </td>

                    {{-- ✨ MOSTRAR TOTAL EUR ✨ --}}
                    <td style="text-align: right; font-weight: bold; color: #4ade80; font-size: 1.05em;">
                        {{-- Sumamos los importes_eu de la relación compraProductos --}}
                        € {{ number_format($c->compraProductos->sum('importe_eu'), 2) }}
                    </td>

                    {{-- TOTAL USD --}}
                    <td style="text-align: right; font-weight: bold; color: #60a5fa; font-size: 1.05em;">
                        $ {{ number_format($c->total_factura, 2) }}
                    </td>

                    <td style="text-align: center; white-space: nowrap;">
                        <a href="{{ route('compras.show', $c->id_compra) }}" class="btn-secondary" style="padding: 6px 12px; font-size: 0.85rem; text-decoration: none; border: 1px solid #555; margin-right: 5px;" title="Ver Detalle">
                            <i class="fa-regular fa-eye"></i>
                        </a>
                        <a href="{{ route('compras.edit', $c->id_compra) }}" class="btn-primary" style="padding: 6px 12px; font-size: 0.85rem; text-decoration: none; border: 1px solid #0056b3;" title="Editar Compra">
                            <i class="fa-solid fa-pen-to-square"></i>
                        </a>
                    </td>
                </tr>
            @empty
                {{-- ... (tu código de tabla vacía igual) ... --}}
            @endforelse
        </tbody>
    </table>
</div>
    {{-- Paginación (si la tienes implementada) --}}
    @if(method_exists($compras, 'links'))
        <div style="margin-top: 20px;">
            {{ $compras->links() }}
        </div>
    @endif

</div>

{{-- Efecto Hover para las filas --}}
<style>
    .erp-table tbody tr:hover {
        background-color: #1a1a1a;
    }
</style>

@endsection
