@extends('layouts.app')

@section('content')

<div class="erp-section">

    <h2 class="erp-title">
        <i class="fa-solid fa-book-open"></i> Nuevo Asiento Contable
    </h2>

    {{-- ALERTAS --}}
    @if ($errors->has('msg'))
        <div class="form-alert" style="background:#ffdddd; color:#a20000;">
            {{ $errors->first('msg') }}
        </div>
    @endif

    @if ($errors->any() && !$errors->has('msg'))
        <div class="form-alert" style="background:#ffdddd; color:#a20000;">
            <strong>Errores detectados:</strong>
            <ul style="margin:0; padding-left:18px;">
                @foreach ($errors->all() as $e)
                    <li>{{ $e }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="erp-table-container" style="padding:20px;">

        <form method="POST" action="{{ route('asientos.store') }}" id="form-asiento">
            @csrf

            {{-- CABECERA DEL ASIENTO --}}
            <table class="erp-table" style="border:none; max-width:600px; margin:auto;">
                <tr>
                    <td style="width:150px;"><strong>Fecha</strong></td>
                    <td>
                        <input type="date" name="fecha"
                               value="{{ old('fecha', date('Y-m-d')) }}"
                               class="search-input" required>
                    </td>
                </tr>
                <tr>
                    <td><strong>Descripción</strong></td>
                    <td>
                        <input type="text" name="descripcion"
                               value="{{ old('descripcion') }}"
                               class="search-input">
                    </td>
                </tr>
            </table>

            {{-- DETALLE DEL ASIENTO --}}
            <h3 class="erp-title" style="font-size:20px; margin-top:25px;">
                Detalle del Asiento
            </h3>

            <div class="erp-table-container">
                <table class="erp-table" id="tabla-detalle">
                    <thead>
                        <tr>
                            <th style="width:260px;">Cuenta</th>
                            <th>Detalle</th>
                            <th style="width:110px;">Debe</th>
                            <th style="width:110px;">Haber</th>
                            <th style="width:60px; text-align:center;">X</th>
                        </tr>
                    </thead>
                    <tbody>
                        {{-- fila plantilla inicial --}}
                        @php
                            $oldCuentas = old('cuenta_id', [null]);
                            $oldDetalles = old('detalle', ['']);
                            $oldDebe = old('debe', ['0.00']);
                            $oldHaber = old('haber', ['0.00']);
                        @endphp

                        @foreach($oldCuentas as $i => $oldCuenta)
                        <tr>
                            <td>
                                <select name="cuenta_id[]" class="search-input" required>
                                    <option value="">-- Seleccione --</option>
                                    @foreach($cuentas as $c)
                                        <option value="{{ $c->id }}"
                                            {{ $oldCuenta == $c->id ? 'selected' : '' }}>
                                            {{ $c->codigo }} — {{ $c->nombre }}
                                        </option>
                                    @endforeach
                                </select>
                            </td>
                            <td>
                                <input type="text" name="detalle[]" class="search-input"
                                       value="{{ $oldDetalles[$i] ?? '' }}">
                            </td>
                            <td>
                                <input type="number" step="0.01" min="0"
                                       name="debe[]" class="search-input input-debe"
                                       value="{{ $oldDebe[$i] ?? '0.00' }}">
                            </td>
                            <td>
                                <input type="number" step="0.01" min="0"
                                       name="haber[]" class="search-input input-haber"
                                       value="{{ $oldHaber[$i] ?? '0.00' }}">
                            </td>
                            <td style="text-align:center;">
                                <button type="button" class="btn-table btn-delete btn-remove-row">
                                    <i class="fa-solid fa-xmark"></i>
                                </button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>

                    <tfoot>
                        <tr>
                            <td colspan="5" style="text-align:left;">
                                <button type="button" id="btn-add-row"
                                        class="btn-secondary" style="padding:6px 12px;">
                                    ➕ Agregar línea
                                </button>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="2" style="text-align:right;"><strong>Total:</strong></td>
                            <td>
                                <input type="text" id="total-debe" class="search-input" readonly>
                            </td>
                            <td>
                                <input type="text" id="total-haber" class="search-input" readonly>
                            </td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            {{-- BOTONES --}}
            <div class="erp-actions" style="text-align:center; margin-top:20px;">
                <button type="submit" class="btn-primary" style="padding:8px 18px;">
                    GUARDAR ASIENTO
                </button>
                <a href="{{ route('asientos.index') }}"
                   class="btn-secondary" style="margin-left:8px;">
                    Cancelar
                </a>
            </div>

        </form>

    </div>

</div>

{{-- JS SIMPLE PARA MANEJAR LÍNEAS Y TOTALES --}}
<script>
document.addEventListener('DOMContentLoaded', function () {
    const tabla = document.querySelector('#tabla-detalle tbody');
    const btnAddRow = document.querySelector('#btn-add-row');
    const totalDebeInput  = document.querySelector('#total-debe');
    const totalHaberInput = document.querySelector('#total-haber');

    function recalcularTotales() {
        let totalDebe = 0;
        let totalHaber = 0;

        document.querySelectorAll('.input-debe').forEach(i => {
            totalDebe += parseFloat(i.value || 0);
        });

        document.querySelectorAll('.input-haber').forEach(i => {
            totalHaber += parseFloat(i.value || 0);
        });

        totalDebeInput.value  = totalDebe.toFixed(2);
        totalHaberInput.value = totalHaber.toFixed(2);
    }

    function agregarEventosFila(tr) {
        tr.querySelectorAll('.input-debe, .input-haber').forEach(inp => {
            inp.addEventListener('input', recalcularTotales);
        });

        const btnRemove = tr.querySelector('.btn-remove-row');
        btnRemove.addEventListener('click', () => {
            const filas = tabla.querySelectorAll('tr');
            if (filas.length > 1) {
                tr.remove();
                recalcularTotales();
            }
        });
    }

    // Agregar evento a filas existentes
    tabla.querySelectorAll('tr').forEach(agregarEventosFila);
    recalcularTotales();

    btnAddRow.addEventListener('click', function () {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td>
                <select name="cuenta_id[]" class="search-input" required>
                    <option value="">-- Seleccione --</option>
                    @foreach($cuentas as $c)
                        <option value="{{ $c->id }}">
                            {{ $c->codigo }} — {{ $c->nombre }}
                        </option>
                    @endforeach
                </select>
            </td>
            <td>
                <input type="text" name="detalle[]" class="search-input">
            </td>
            <td>
                <input type="number" step="0.01" min="0"
                       name="debe[]" class="search-input input-debe" value="0.00">
            </td>
            <td>
                <input type="number" step="0.01" min="0"
                       name="haber[]" class="search-input input-haber" value="0.00">
            </td>
            <td style="text-align:center;">
                <button type="button" class="btn-table btn-delete btn-remove-row">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </td>
        `;
        tabla.appendChild(tr);
        agregarEventosFila(tr);
        recalcularTotales();
    });
});
</script>

@endsection
