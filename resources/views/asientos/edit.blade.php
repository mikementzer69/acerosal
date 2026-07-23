@extends('layouts.app')

@section('content')

<div class="erp-section">

    <h2 class="erp-title">
        <i class="fa-solid fa-pen-to-square"></i> Editar Asiento Contable #{{ $asiento->id }}
    </h2>

    <form method="POST" action="{{ route('asientos.update', $asiento->id) }}">
        @csrf
        @method('PUT')

        <div class="asiento-form">

            {{-- FECHA / DESCRIPCIÓN --}}
            <div class="top-box">
                <label>Fecha</label>
                <input type="date" name="fecha" value="{{ $asiento->fecha }}" required>

                <label style="margin-left:20px;">Descripción</label>
                <input type="text" name="descripcion" value="{{ $asiento->descripcion }}" required>
            </div>

            <hr>

            <h3>Detalle del Asiento</h3>

            <table class="erp-table" id="tabla-detalle">
                <thead>
                    <tr>
                        <th>Cuenta</th>
                        <th>Detalle</th>
                        <th>Debe</th>
                        <th>Haber</th>
                        <th></th>
                    </tr>
                </thead>

                <tbody>

                    @foreach($asiento->detalles as $d)
                    <tr>
                        <td>
                            <select name="cuenta_id[]" required>
                                <option value="">-- Seleccione --</option>
                                @foreach($cuentas as $c)
                                    <option value="{{ $c->id }}"
                                        {{ $d->cuenta_id == $c->id ? 'selected' : '' }}>
                                        {{ $c->codigo }} - {{ $c->nombre }}
                                    </option>
                                @endforeach
                            </select>
                        </td>

                        <td>
                            <input type="text" name="detalle[]" value="{{ $d->descripcion }}">
                        </td>

                        <td>
                            <input type="number" name="debe[]" step="0.01" class="input-debe"
                                   value="{{ $d->debe }}">
                        </td>

                        <td>
                            <input type="number" name="haber[]" step="0.01" class="input-haber"
                                   value="{{ $d->haber }}">
                        </td>

                        <td>
                            <button type="button" class="btn-table btn-delete" onclick="eliminarFila(this)">
                                <i class="fa-solid fa-xmark"></i>
                            </button>
                        </td>
                    </tr>
                    @endforeach

                </tbody>

                {{-- Totales --}}
                <tfoot>
                    <tr class="total-row-edit">
                        <td colspan="2" style="text-align:right;">Totales:</td>
                        <td id="total_debe_edit">0.00</td>
                        <td id="total_haber_edit">0.00</td>
                    </tr>
                </tfoot>
            </table>

            <button type="button" class="btn-secondary" onclick="agregarFila()">
                ➕ Agregar línea
            </button>

            <div style="margin-top:20px;">
                <button class="btn-primary">Guardar Cambios</button>
                <a href="{{ route('asientos.index') }}" class="btn-secondary">Cancelar</a>
            </div>

        </div>
    </form>
</div>

{{-- JS Dinámico --}}
<script>

function agregarFila() {
    let tabla = document.querySelector("#tabla-detalle tbody");
    let fila = `
        <tr>
            <td>
                <select name="cuenta_id[]" required>
                    <option value="">-- Seleccione --</option>
                    @foreach($cuentas as $c)
                        <option value="{{ $c->id }}">{{ $c->codigo }} - {{ $c->nombre }}</option>
                    @endforeach
                </select>
            </td>
            <td><input type="text" name="detalle[]"></td>
            <td><input type="number" name="debe[]" step="0.01" class="input-debe" value="0"></td>
            <td><input type="number" name="haber[]" step="0.01" class="input-haber" value="0"></td>
            <td>
                <button type="button" class="btn-table btn-delete" onclick="eliminarFila(this)">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </td>
        </tr>
    `;
    tabla.insertAdjacentHTML('beforeend', fila);
    recalcularTotales();
}

function eliminarFila(btn) {
    btn.closest("tr").remove();
    recalcularTotales();
}

function recalcularTotales() {
    let totalDebe = 0;
    let totalHaber = 0;

    document.querySelectorAll('.input-debe').forEach(e => {
        let val = parseFloat(e.value);
        totalDebe += isNaN(val) ? 0 : val;
    });

    document.querySelectorAll('.input-haber').forEach(e => {
        let val = parseFloat(e.value);
        totalHaber += isNaN(val) ? 0 : val;
    });

    document.getElementById("total_debe_edit").innerText = totalDebe.toFixed(2);
    document.getElementById("total_haber_edit").innerText = totalHaber.toFixed(2);
}

// Recalcular cuando se edita un número
document.addEventListener('input', function(e) {
    if (e.target.classList.contains('input-debe') ||
        e.target.classList.contains('input-haber')) {
        recalcularTotales();
    }
});

// Recalcular al cargar la página
window.onload = recalcularTotales;

</script>

@endsection
