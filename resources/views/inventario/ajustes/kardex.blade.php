@extends('layouts.app')

@section('content')
<style>
    /* Contenedor Principal */
    .kardex-master-panel {
        background: #fdfdfd;
        padding: 20px;
        border-radius: 10px;
        border: 1px solid #e0e6ed;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        margin-bottom: 20px;
    }

    /* Distribución en Fila para todo el panel */
    .kardex-layout {
        display: flex;
        flex-wrap: wrap;
        gap: 20px;
        align-items: flex-end;
    }

    /* Contenedor de Filtros: Ocupa el espacio restante */
    .kardex-filters {
        display: flex;
        gap: 15px;
        flex: 3; /* Le damos peso para que crezca */
        min-width: 600px;
    }

    .kardex-input-group { flex: 1; }

    .lbl-kardex {
        font-size: 11px;
        font-weight: 800;
        color: #475569;
        text-transform: uppercase;
        margin-bottom: 6px;
        display: block;
    }

    /* Selectores Limpios */
    .select-acerosal {
        width: 100%;
        height: 40px;
        padding: 0 10px;
        border: 1px solid #cbd5e1;
        border-radius: 6px;
        font-size: 13px;
        background-color: #fff;
    }

    /* Botones Proporcionales (Sin estirarse al infinito) */
    .kardex-actions {
        display: flex;
        gap: 10px;
        flex: 1; /* Los botones ocupan menos espacio que los filtros */
        min-width: 250px;
    }

    .btn-acerosal {
        flex: 1;
        height: 40px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 6px;
        font-weight: 700;
        font-size: 12px;
        text-decoration: none !important; /* Mata subrayado */
        border: none;
        color: white !important;
        cursor: pointer;
        transition: transform 0.1s;
    }

    .btn-blue { background: #0d6efd; }
    .btn-blue:hover { background: #0b5ed7; }

    .btn-red { background: #dc3545; }
    .btn-red:hover { background: #bb2d3b; }

    .btn-acerosal:active { transform: scale(0.96); }
</style>

<div class="kardex-master-panel">
    <form action="{{ route('inventario.ajuste.kardex') }}" method="GET">
        <div class="kardex-layout">
            <div class="kardex-filters">
                <div class="kardex-input-group">
                        <label class="lbl-kardex">3. Desde</label>
                        <input type="date" name="fecha_inicio" class="select-acerosal" value="{{ request('fecha_inicio') }}">
                    </div>

                    <div class="kardex-input-group">
                        <label class="lbl-kardex">4. Hasta</label>
                        <input type="date" name="fecha_fin" class="select-acerosal" value="{{ request('fecha_fin') }}">
                    </div>
                <div class="kardex-input-group">
                    <label class="lbl-kardex">1. Calidad</label>
                    <select id="sel-familia-kardex" name="id_familia" class="select-acerosal">
                        <option value="">-- Seleccione --</option>
                        @foreach($familias as $f)
                            <option value="{{ $f->id_familia }}" {{ request('id_familia') == $f->id_familia ? 'selected' : '' }}>{{ $f->nombre }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="kardex-input-group">
                    <label class="lbl-kardex">2. Producto</label>
                    <select id="sel-producto-kardex" name="id_producto" class="select-acerosal">
                        <option value="">-- Seleccione Producto --</option>
                        @foreach($productos as $p)
                            @php
                                // Creamos la "Ficha Técnica" solo si hay datos
                                $ficha = ($p->milimetros || $p->pulgadas)
                                        ? " ({$p->milimetros}mm / {$p->pulgadas}\")"
                                        : "";
                            @endphp
                            <option value="{{ $p->id_producto }}" {{ request('id_producto') == $p->id_producto ? 'selected' : '' }}>
                                {{ $p->codigo }} - {{ $p->descripcion }}{{ $ficha }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="kardex-input-group">
                    <label class="lbl-kardex">3. Pieza</label>
                    <select id="sel-pieza-kardex" name="id_pieza" class="select-acerosal">
                        <option value="">-- Todas --</option>
                    </select>
                </div>
            </div>

            <div class="kardex-actions">
                <button type="submit" class="btn-acerosal btn-blue">
                    <i class="fas fa-search"></i> CONSULTAR
                </button>
                <a href="{{ route('inventario.ajuste.exportar_pdf', request()->all()) }}" class="btn-acerosal btn-red">
                    <i class="fas fa-file-pdf"></i> PDF
                </a>
            </div>
        </div>
    </form>
</div>

    <div class="card-detalle-pieza">
        <h4 style="color: #60a5fa;"><i class="fa-solid fa-history"></i> Movimientos Registrados</h4>
        <table class="table table-dark table-hover mb-0">
        <thead>
            <tr class="text-uppercase small" style="letter-spacing: 1px;">
                <th class="py-3">Fecha / Hora</th>
                <th class="py-3">Descripcion</th>
                <th class="py-3">Tipo / Origen</th>
                <th class="py-3 text-end">Metros (Neto)</th>
                <th class="py-3 text-end text-warning">Tolerancia (mts)</th>
                <th class="py-3 text-end text-info">Peso (lbs)</th>
                <th class="py-3 text-end">Total Salida (mts)</th>
                <th class="py-3 text-end">Saldo Global</th>
                <th>Observaciones</th>
            </tr>
        </thead>
    <tbody>
        @foreach($movimientos as $m)
        <tr>
            <td class="text-muted small">
                {{ date('d/m/Y H:i', strtotime($m->fecha)) }}
            </td>
<td>
            <div class="fw-bold text-dark" style="font-size: 0.9rem;">
                {{ $m->familia_nombre }} - {{ $m->producto_nombre }}
            </div>

            {{-- Lógica de medidas en naranja --}}
            @php
                $mm = floatval($m->milimetros ?? 0);
                $plg = $m->pulgadas ?? '-';
            @endphp
            @if($mm > 0 || ($plg && $plg != '-'))
                <span style="color: #d97706; font-weight: bold; font-size: 0.85em;">
                    ({{ $mm > 0 ? $mm.' mm' : '' }} {{ ($mm > 0 && $plg != '-') ? '/' : '' }} {{ $plg != '-' ? $plg.' plg' : '' }})
                </span>
            @endif
        </td>

            <td>
                <span class="badge {{ str_contains(strtolower($m->tipo), 'entrada') ? 'bg-success' : 'bg-danger' }} py-1 px-2 mb-1" style="font-size: 9px;">
                    {{ strtoupper(str_replace('_', ' ', $m->tipo)) }}
                </span><br>
                <small class="fw-bold text-uppercase text-secondary">{{ $m->origen ?? 'N/A' }}</small>
            </td>

            {{-- Metros Netos (Lo que pidió el cliente) --}}
<td class="text-end fw-bold">
    @php
        // 🔮 MAGIA: Si la retirada tiene datos (>0), la usamos.
        // Si es 0, usamos la cantidad original.
        $valorReal = ($m->cantidad_total_retirada > 0)
            ? $m->cantidad_total_retirada
            : $m->cantidad;
    @endphp
    {{ number_format($valorReal, 2) }}
</td>

            {{-- Merma en Metros (Tolerancia) --}}
            <td class="text-end text-warning">
                {{ number_format($m->tolerancia_aplicada ?? 0, 4) }}
            </td>

            {{-- Peso Neto en Libras (El 15.60 o 10.40) --}}
            <td class="text-end text-info fw-bold">
            {{ number_format(($m->peso_neto_libras ?? 0) + ($m->merma_libras_grabada ?? 0), 2) }}
            </td>

            {{-- Total Retirado (Metros + Merma) --}}
{{-- Columna Total Salida --}}
        <td class="text-end">
            {{-- 🔮 MAGIA: Sin condiciones. Si en la DB hay 300, aquí pone 300. --}}
            {{ number_format($m->cantidad_total_retirada, 2) }}
        </td>

            {{-- Saldo Dinámico --}}
            <td class="text-end" style="color: #fbbf24; font-weight: bold;">
                {{ number_format($m->saldo_dinamico_global, 2) }} mts
            </td>

                <td style="max-width: 200px; font-size: 0.85rem;">
        {{ $m->comentario }}
    </td>
            </tr>
        @endforeach
    </tbody>



<tfoot style="border-top: 3px solid #3b82f6; background-color: rgba(15, 23, 42, 0.8);">
    <tr class="fw-bold text-white">
        <td colspan="5" class="text-end py-3 text-uppercase small">Resumen del Periodo (Mts):</td>
        <td class="text-end py-2">
            <div class="text-success small">
                <i class="fas fa-arrow-up"></i> +{{ number_format($totalEntradas, 2) }}
            </div>
            <div class="text-danger small">
                <i class="fas fa-arrow-down"></i> -{{ number_format($totalSalidas, 2) }}
            </div>
        </td>
        <td class="text-end py-3" style="background-color: rgba(251, 191, 36, 0.1);">
            <span class="text-warning h5 mb-0">{{ number_format($saldoFinal, 2) }} mts</span>
        </td>
    </tr>
</tfoot>
</table>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const selFam = document.getElementById('sel-familia-kardex');
    const selProd = document.getElementById('sel-producto-kardex');
    const selPieza = document.getElementById('sel-pieza-kardex');

    // NIVEL 1: Familia -> Producto
    selFam.addEventListener('change', function() {
        let familiaId = this.value;
        if(!familiaId) {
            selProd.innerHTML = '<option value="">-- Seleccione Producto --</option>';
            return;
        }

        selProd.innerHTML = '<option value="">-- Cargando productos... --</option>';

        fetch(`/productos/por-familia/${familiaId}`)
            .then(res => res.json())
            .then(data => {
                selProd.innerHTML = '<option value="">-- Seleccione Producto --</option>';
                data.forEach(p => {
                    // Sincronizamos con el formato de mm y pulgadas
                    let mm = p.milimetros || '0';
                    let plg = p.pulgadas || '0';
                    let ficha = ` (${mm}mm / ${plg}")`;
                    selProd.innerHTML += `<option value="${p.id_producto}">${p.codigo} - ${p.descripcion}${ficha}</option>`;
                });
            });
    });

    // NIVEL 2: Producto -> Piezas (¡Aquí estaba el error!)
    selProd.addEventListener('change', function() {
        if(!this.value) {
            selPieza.innerHTML = '<option value="">-- Todas las piezas --</option>';
            return;
        }

        selPieza.innerHTML = '<option value="">-- Cargando piezas... --</option>';

        fetch(`/inventario/ajustes/lotes/${this.value}`)
            .then(res => res.json())
            .then(lotes => {
                selPieza.innerHTML = '<option value="">-- Todas las piezas --</option>';
                lotes.forEach(lote => {
                    fetch(`/inventario/ajustes/piezas/${lote.id_lote}`)
                        .then(r => r.json())
                        .then(piezas => {
                            piezas.forEach(pieza => {
                                // CORRECCIÓN: Usamos cantidad_metros_actual en lugar de cantidad_total_metros
                                let mts = pieza.cantidad_metros_actual || 0;
                                let cod = pieza.codigo || pieza.id_pieza;
                                selPieza.innerHTML += `<option value="${pieza.id_pieza}">Pieza: ${cod} (${mts} mts)</option>`;
                            });
                        });
                });
            });
    });
});
</script>

@endsection
