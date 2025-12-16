document.addEventListener('DOMContentLoaded', function () {

    const form = document.getElementById('formCompra');
    if (!form) return;

    // --- Referencias generales ---
    const tasaInput          = document.getElementById('tasa_cambio');
    const tablaCostosBody    = document.querySelector('#tablaCostos tbody');
    const btnAgregarCosto    = document.getElementById('btnAgregarCosto');

    const selectFamilia      = document.getElementById('selectFamilia');
    const btnAgregarFamilia  = document.getElementById('btnAgregarFamilia');
    const contenedorFamilias = document.getElementById('contenedorFamilias');

    const resumenBody        = document.querySelector('#tablaResumenFamilias tbody');

    const lblTotalKG         = document.getElementById('lblTotalKG');
    const lblTotalLB         = document.getElementById('lblTotalLB');
    const lblTotalUSD        = document.getElementById('lblTotalUSD');
    const lblCostosUSD       = document.getElementById('lblCostosUSD');
    const lblCostoPorLibra   = document.getElementById('lblCostoPorLibra');
    const lblTotalFactura    = document.getElementById('lblTotalFactura');

    function round2(v) { return parseFloat(v || 0).toFixed(2); }
    function round4(v) { return parseFloat(v || 0).toFixed(4); }

    const familiasActivas = {};

    // ==========================================================
    // COSTOS ADICIONALES
    // ==========================================================

    if (btnAgregarCosto) {
        btnAgregarCosto.addEventListener('click', () => {

            const tr = document.createElement('tr');

            tr.innerHTML = `
                <td>
                    <select name="id_costo[]">
                        <option value="">Seleccione costo</option>
                        ${window.costosOptions || ''}
                    </select>
                </td>
                <td>
                    <input type="number" step="0.01" name="valor_eu[]" class="campo-costo-eu">
                </td>
                <td>
                    <input type="number" step="0.01" name="valor_usd[]" class="campo-costo-usd">
                </td>
                <td>
                    <button type="button" class="btn-eliminar-fila">X</button>
                </td>
            `;

            tablaCostosBody.appendChild(tr);

            const inputEu  = tr.querySelector('.campo-costo-eu');
            const inputUsd = tr.querySelector('.campo-costo-usd');
            const btnEliminar = tr.querySelector('.btn-eliminar-fila');

            inputEu.addEventListener('input', recalcularTotales);
            inputUsd.addEventListener('input', recalcularTotales);

            btnEliminar.addEventListener('click', () => {
                tr.remove();
                recalcularTotales();
            });
        });
    }

    // ==========================================================
    // FAMILIAS Y TABLAS DE PRODUCTOS
    // ==========================================================

    if (btnAgregarFamilia) {
        btnAgregarFamilia.addEventListener('click', () => {

            const idFam = selectFamilia.value;
            const nombreFam = selectFamilia.options[selectFamilia.selectedIndex]?.text || '';

            if (!idFam) {
                alert('Seleccione una familia.');
                return;
            }
            if (familiasActivas[idFam]) {
                alert('Esta familia ya fue agregada.');
                return;
            }

            familiasActivas[idFam] = true;

            const opt = selectFamilia.querySelector(`option[value="${idFam}"]`);
            if (opt) opt.disabled = true;

            selectFamilia.value = '';

            crearBloqueFamilia(idFam, nombreFam);
        });
    }

    function crearBloqueFamilia(idFam, nombre) {

        const bloque = document.createElement('div');
        bloque.classList.add('bloque-familia');
        bloque.dataset.idFamilia = idFam;
        bloque.dataset.nombreFamilia = nombre;

        bloque.innerHTML = `
            <div class="bloque-familia-header">
                <strong>Familia: ${nombre}</strong>
                <div>
                    <button type="button" class="btn-agregar btn-agregar-producto">+ Producto</button>
                    <button type="button" class="btn-eliminar-familia">Eliminar familia</button>
                </div>
            </div>

            <input type="hidden" name="familias_seleccionadas[]" value="${idFam}">

            <table class="tabla-datos tabla-productos-familia">
                <thead>
                    <tr>
                        <th>Producto</th>
                        <th>Cantidad</th>
                        <th>Peso KG</th>
                        <th>Peso LB</th>
                        <th>Precio EUR/kg</th>
                        <th>Precio USD/kg</th>
                        <th>Importe EUR</th>
                        <th>Importe USD</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        `;

        contenedorFamilias.appendChild(bloque);

        crearFilaResumenFamilia(idFam, nombre);

        const btnAgregarProducto = bloque.querySelector('.btn-agregar-producto');
        const btnEliminarFamilia = bloque.querySelector('.btn-eliminar-familia');

        btnAgregarProducto.addEventListener('click', () => {
            agregarFilaProducto(bloque, idFam);
        });

        btnEliminarFamilia.addEventListener('click', () => {

            if (!confirm(`¿Eliminar completamente la familia "${nombre}"?`)) return;

            bloque.remove();
            delete familiasActivas[idFam];

            const opt = selectFamilia.querySelector(`option[value="${idFam}"]`);
            if (opt) opt.disabled = false;

            const filaResumen = resumenBody.querySelector(`tr[data-familia="${idFam}"]`);
            if (filaResumen) filaResumen.remove();

            recalcularTotales();
        });
    }

    function agregarFilaProducto(bloque, idFam) {

        const tbody = bloque.querySelector('tbody');
        const tr = document.createElement('tr');

        tr.innerHTML = `
            <td><select name="id_producto[]" class="producto-select"><option>Cargando...</option></select></td>
            <td><input type="number" step="0.0001" name="cantidad[]"   class="campo-prod"></td>
            <td><input type="number" step="0.0001" name="peso_kg[]"    class="campo-prod"></td>
            <td><input type="text"                   name="peso_lb[]"   readonly></td>
            <td><input type="number" step="0.0001" name="precio_kg_eu[]" class="campo-prod"></td>
            <td><input type="text"                   name="precio_kg_usd[]" readonly></td>
            <td><input type="text"                   name="importe_eu[]" readonly></td>
            <td><input type="text"                   name="importe_usd[]" readonly></td>
            <td>
                <input type="hidden" name="familia_producto[]" value="${idFam}">
                <button type="button" class="btn-eliminar-fila">X</button>
            </td>
        `;

        tbody.appendChild(tr);

        const selectProducto = tr.querySelector('.producto-select');
        const inputsCalc     = tr.querySelectorAll('.campo-prod');
        const btnEliminar    = tr.querySelector('.btn-eliminar-fila');

        // Ruta correcta según tu aclaración
        fetch(`/productos/familia/${encodeURIComponent(idFam)}`)
            .then(r => r.json())
            .then(data => {
                let html = '<option value="">Seleccione producto</option>';
                data.forEach(p => html += `<option value="${p.id_producto}">${p.descripcion}</option>`);
                selectProducto.innerHTML = html;
            })
            .catch(() => selectProducto.innerHTML = '<option>Error al cargar</option>');

        inputsCalc.forEach(i => i.addEventListener('input', recalcularTotales));

        btnEliminar.addEventListener('click', () => {
            tr.remove();
            recalcularTotales();
        });
    }

    // ==========================================================
    // Fila resumen
    // ==========================================================

    function crearFilaResumenFamilia(idFam, nombre) {
        if (resumenBody.querySelector(`tr[data-familia="${idFam}"]`)) return;

        const tr = document.createElement('tr');
        tr.dataset.familia = idFam;

        tr.innerHTML = `
            <td>${nombre}</td>
            <td class="kg">0.0000</td>
            <td class="lb">0.0000</td>
            <td class="eur">0.00</td>
            <td class="usd">0.00</td>
            <td class="cif">0.0000</td>
            <td class="bodega">0.0000</td>
            <td class="total">0.00</td>
        `;

        resumenBody.appendChild(tr);
    }

    // ==========================================================
    // RECÁLCULO GENERAL
    // ==========================================================

    function recalcularTotales() {

        const tasa = parseFloat(tasaInput.value || 1);

        let totalKG = 0, totalLB = 0, totalUSD = 0, totalEU = 0, totalCostosUSD = 0;

        const resumenPorFamilia = {};

        contenedorFamilias.querySelectorAll('.bloque-familia').forEach(b => {

            const idFam = b.dataset.idFamilia;
            const nombre = b.dataset.nombreFamilia;

            resumenPorFamilia[idFam] = { nombre, kg: 0, lb: 0, eur: 0, usd: 0 };

            b.querySelectorAll('tbody tr').forEach(tr => {

                const pesoKG   = parseFloat(tr.querySelector('[name="peso_kg[]"]').value || 0);
                const precioEU = parseFloat(tr.querySelector('[name="precio_kg_eu[]"]').value || 0);

                const pesoLB   = pesoKG * 2.20462;
                const precioUSD = precioEU * tasa;

                const importeEU  = pesoKG * precioEU;
                const importeUSD = pesoKG * precioUSD;

                tr.querySelector('[name="peso_lb[]"]').value       = round4(pesoLB);
                tr.querySelector('[name="precio_kg_usd[]"]').value = round4(precioUSD);
                tr.querySelector('[name="importe_eu[]"]').value    = round2(importeEU);
                tr.querySelector('[name="importe_usd[]"]').value   = round2(importeUSD);

                totalKG  += pesoKG;
                totalLB  += pesoLB;
                totalEU  += importeEU;
                totalUSD += importeUSD;

                resumenPorFamilia[idFam].kg  += pesoKG;
                resumenPorFamilia[idFam].lb  += pesoLB;
                resumenPorFamilia[idFam].eur += importeEU;
                resumenPorFamilia[idFam].usd += importeUSD;
            });
        });

        tablaCostosBody.querySelectorAll('tr').forEach(tr => {
            const usd = parseFloat(tr.querySelector('[name="valor_usd[]"]').value || 0);
            totalCostosUSD += usd;
        });

        const totalFactura = totalUSD + totalCostosUSD;
        const costoPorLibra = totalLB > 0 ? totalCostosUSD / totalLB : 0;

        lblTotalKG.textContent       = round4(totalKG);
        lblTotalLB.textContent       = round4(totalLB);
        lblTotalUSD.textContent      = round2(totalUSD);
        lblCostosUSD.textContent     = round2(totalCostosUSD);
        lblCostoPorLibra.textContent = round4(costoPorLibra);
        lblTotalFactura.textContent  = round2(totalFactura);

        Object.keys(resumenPorFamilia).forEach(id => {
            const f = resumenPorFamilia[id];
            const tr = resumenBody.querySelector(`tr[data-familia="${id}"]`);
            if (!tr) return;

            const precioCIF    = f.lb > 0 ? f.usd / f.lb : 0;
            const precioBodega = precioCIF + costoPorLibra;
            const totalFam     = precioBodega * f.lb;

            tr.querySelector('.kg').textContent     = round4(f.kg);
            tr.querySelector('.lb').textContent     = round4(f.lb);
            tr.querySelector('.eur').textContent    = round2(f.eur);
            tr.querySelector('.usd').textContent    = round2(f.usd);
            tr.querySelector('.cif').textContent    = round4(precioCIF);
            tr.querySelector('.bodega').textContent = round4(precioBodega);
            tr.querySelector('.total').textContent  = round2(totalFam);
        });
    }

    if (tasaInput) tasaInput.addEventListener('input', recalcularTotales);

    form.addEventListener('submit', e => {

        const bloques = contenedorFamilias.querySelectorAll('.bloque-familia');

        if (bloques.length === 0) {
            alert('Debe agregar al menos una familia con productos.');
            e.preventDefault();
            return;
        }

        for (const b of bloques) {
            if (b.querySelectorAll('tbody tr').length === 0) {
                alert(`La familia "${b.dataset.nombreFamilia}" no tiene productos.`);
                e.preventDefault();
                return;
            }
        }

        recalcularTotales();
    });

});