<form id="formInventarioManual" class="form-lote">

    <!-- 🧩 Fila 1: Familia / Producto / Código de producto -->
    <div class="fila-grid tres-columnas">
        <div>
            <label>Familia:</label>
            <select id="selectFamilia">
                <option value="">Seleccione una familia</option>
            </select>
        </div>

        <div>
            <label>Producto:</label>
            <select id="selectProducto" disabled>
                <option value="">Seleccione un producto</option>
            </select>
        </div>

        <div>
            <label>Código de producto:</label>
            <input type="text" id="codigoProducto" readonly placeholder="—">
        </div>
    </div>

    <!-- 🧩 Fila 2: Código lote / Fecha ingreso / Total piezas -->
    <div class="fila-grid tres-columnas">
        <div>
            <label>Código de lote:</label>
            <input type="text" id="codigoLote" placeholder="Ej: L-0001">
        </div>

        <div>
            <label>Fecha ingreso:</label>
            <input type="date" id="fechaIngreso" value="<?php echo date('Y-m-d'); ?>">
        </div>

        <div>
            <label>Total piezas:</label>
            <input type="number" id="totalPiezas" value="0" readonly>
        </div>
    </div>

    <!-- 🧩 Fila 3: Peso total / Cantidad total / Relación lb/m -->
    <div class="fila-grid tres-columnas">
        <div>
            <label>Peso total (lb):</label>
            <input type="number" id="pesoTotal" step="0.01" placeholder="0.00">
        </div>

        <div>
            <label>Cantidad total (m):</label>
            <input type="number" id="cantidadTotal" step="0.01" placeholder="0.00">
        </div>

        <div>
            <label>Relación lb/m:</label>
            <input type="number" id="relacion" step="0.0001" placeholder="0.0000" readonly>
        </div>
    </div>

    <!-- 🧮 Tabla de piezas -->
    <div class="tabla-piezas-container">
        <table id="tablaPiezas" class="tabla-piezas">
            <thead>
                <tr>
                    <th>Código pieza</th>
                    <th>Metros inicial</th>
                    <th>Libras inicial</th>
                    <th>Acción</th>
                </tr>
            </thead>
            <tbody></tbody>
            <tfoot>
                <tr>
                    <th>Total</th>
                    <th id="totalMetros">0.00</th>
                    <th id="totalLibras">0.00</th>
                    <th></th>
                </tr>
            </tfoot>
        </table>
    </div>

    <button type="button" id="btnAgregarPieza" class="btn-agregar">➕ Agregar pieza</button>
    <button type="button" id="btnGuardar" class="btn-guardar">💾 Guardar lote</button>
</form>


