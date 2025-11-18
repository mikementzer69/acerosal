<?php
require_once __DIR__ . '/../Modelos/ModeloCompras.php';
$modelo = new ModeloCompras();

$proveedores   = $modelo->obtenerProveedoresActivos();
$familias      = $modelo->obtenerFamiliasActivas();
$costosActivos = $modelo->obtenerCostosActivos();

$empresaNombre = $_SESSION['nombreEmpresa'] ?? '—';
$empresaId     = $_SESSION['idEmpresa'] ?? '';
?>

<!-- ===========================================================
     🧱 FORMULARIO PRINCIPAL DE COMPRAS (con grid funcional)
=========================================================== -->
<form id="formCompra" method="POST" class="grid-compras">

    <!-- 🔔 FILA 0: Mensajes globales -->
    <div id="mensaje" class="mensaje"></div>

    <!-- 🟩 FILA 1: DATOS DE COMPRA -->
    <div class="datos-compra bloque">
        <fieldset>
            <legend>Datos de la Compra</legend>

            <div class="campo">
                <label>Empresa:</label>
                <span><?= htmlspecialchars($empresaNombre) ?></span>
                <input type="hidden" name="id_Empresa" value="<?= htmlspecialchars($empresaId) ?>">
            </div>

            <div class="campo">
                <label for="id_Proveedor">Proveedor:</label>
                <select name="id_Proveedores" id="id_Proveedor" required>
                    <option value="">-- Seleccione --</option>
                    <?php foreach ($proveedores as $p): ?>
                        <option value="<?= $p['idProveedores'] ?>"><?= htmlspecialchars($p['Nombre']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="campo">
                <label for="Numero_Factura">N° Factura:</label>
                <input type="text" name="Numero_Factura" id="Numero_Factura" maxlength="45" required>
            </div>

            <div class="campo">
                <label for="Fecha_EmisionF">Fecha Emisión:</label>
                <input type="date" name="Fecha_EmisionF" id="Fecha_EmisionF" required>
            </div>

            <div class="campo">
                <label for="Fecha_Ingreso">Fecha Ingreso:</label>
                <input type="date" name="Fecha_Ingreso" id="Fecha_Ingreso" required value="<?= date('Y-m-d') ?>">
            </div>

            <div class="campo">
                <label for="tasaCambio">Tasa de Cambio (€ → $):</label>
                <input type="number" id="tasaCambio" name="tasaCambio" step="0.0001" min="0" value="1.0000">
            </div>
        </fieldset>
    </div>

    <!-- 💲 FILA 1B: COSTOS ADICIONALES -->
    <div class="costos-adicionales bloque">
        <fieldset>
            <legend>Costos Adicionales</legend>
            <table>
                <thead>
                    <tr>
                        <th>Tipo Costo</th>
                        <th>Valor (USD)</th>
                        <th>Valor (€)</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody id="tabla-costos"></tbody>
            </table>

            <button type="button" id="btnAgregarCosto" class="btnAgregar">Agregar Costo</button>

            <div class="totales-costos">
                <label>Total Costos Adicionales (USD):</label>
                <input type="number" id="totalCostosAdicionales" name="Total_Costos_Adicionales"
                       readonly step="0.01" value="0.00">
            </div>
        </fieldset>
    </div>

    <!-- 🟦 FILA 2: DETALLE DE COMPRA -->
    <div class="detalle-compra bloque">
        <fieldset>
            <legend>Detalle de Compra</legend>

            <!-- 🔹 Selector de familias -->
            <div class="familia-selector">
                <label for="familiaSelect">Agregar familia:</label>
                <select id="familiaSelect">
                    <option value="">-- Seleccione familia --</option>
                    <?php foreach ($familias as $f): ?>
                        <option value="<?= $f['idFamilia'] ?>"><?= htmlspecialchars($f['Nombre']) ?></option>
                    <?php endforeach; ?>
                </select>
                <button type="button" id="btnAgregarFamilia" class="btnAgregar">Agregar Familia</button>
            </div>

            <!-- 🔹 Contenedor dinámico -->
            <div id="contenedor-familias"></div>

        </fieldset>
    </div>

    <!-- 🟨 FILA 3: RESUMEN -->
    <div class="resumen bloque">
        <fieldset>
            <legend>Resumen</legend>
            <table id="tabla-resumen" class="tabla-resumen">
                <thead>
                    <tr>
                        <th>Familia</th>
                        <th>Cant. Total (m)</th>
                        <th>Peso Total KG</th>
                        <th>Peso Total LB</th>
                        <th>Importe Total (€)</th>
                        <th>Importe Total ($)</th>
                        <th>Precio CIF</th>
                        <th>Precio Unitario Bodega</th>
                        <th>Total Familia ($)</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </fieldset>
    </div>

    <!-- 🟥 FILA 4: TOTALES FACTURA -->
    <div class="totales-factura bloque">
        <fieldset>
            <legend>Totales de Factura</legend>

            <div class="campo">
                <label>Peso Total (KG):</label>
                <input type="number" id="totalKG" name="Peso_Total_KG" readonly value="0.00">
            </div>

            <div class="campo">
                <label>Peso Total (LB):</label>
                <input type="number" id="totalLB" name="Peso_Total_Libras" readonly value="0.00">
            </div>

            <div class="campo">
                <label>Costos Adicionales por Libra ($):</label>
                <input type="number" id="costosPorLibra" name="Costos_Adicionales_Libra" readonly value="0.00">
            </div>

            <div class="campo">
                <label>Importe Total Factura ($):</label>
                <input type="number" id="importeTotalFactura" name="Importe_Total_Factura" readonly value="0.00">
            </div>

            <div class="campo">
                <label>Total Factura ($):</label>
                <input type="number" id="totalFactura" name="Total_Factura" readonly value="0.00">
            </div>

            <div class="fila-boton">
                <button type="submit" id="btnGuardarCompra" class="btn-guardar">💾 Guardar Compra</button>
            </div>
        </fieldset>
    </div>
</form>

