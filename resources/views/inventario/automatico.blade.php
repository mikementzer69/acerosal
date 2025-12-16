@extends('layouts.app')

@section('content')
<div class="inventario-wrapper">

    {{-- LISTBOX DE COMPRAS --}}
    <aside class="listbox-compras">
        <h3>Compras nuevas</h3>

        <select id="listboxCompras" size="12" class="listbox">
            @foreach($comprasNuevas as $compra)
                <option value="{{ $compra->id_compra }}">
                    Factura {{ $compra->numero_factura }} - {{ $compra->fecha_ingreso }}
                </option>
            @endforeach
        </select>
    </aside>

<section class="contenido-inventario">

    <div class="panel-info">
        <h3>Información de la compra</h3>
        <div id="infoCompra" class="grid-info-compra"></div>
    </div>

    <div id="contenedorLotes" class="lotes-area">
        <p class="mensaje-inicial">Seleccione una compra para generar el inventario.</p>
    </div>

    <div class="acciones-final">
        <button type="button" id="btnGuardarInventario" class="btn-guardar">
            💾 Guardar inventario
        </button>
    </div>

</section>


</div>



@endsection
