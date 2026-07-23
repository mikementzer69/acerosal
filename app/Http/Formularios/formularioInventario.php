<aside class="listbox-compras">
    <h3>Compras nuevas</h3>
    <select id="listboxCompras" size="10">
        <?php foreach ($comprasNuevas as $compra): ?>
            <option value="<?= $compra['idCompras'] ?>">
                  Factura <?= htmlspecialchars($compra['Numero_Factura'] ?? '—') ?> - <?= htmlspecialchars($compra['Fecha_Ingreso'] ?? '') ?>
            </option>
        <?php endforeach; ?>
    </select>
</aside>

<section class="contenido-inventario">
    <div id="infoCompra" class="grid-info-compra"></div>
    <div id="contenedorLotes"></div>

    <div class="acciones-final">
        <button type="button" id="btnGuardarInventario" class="btn-guardar">
            💾 Guardar inventario
        </button>
    </div>
</section>

<script src="../js/inventario.js?v=<?php echo time(); ?>"></script>
