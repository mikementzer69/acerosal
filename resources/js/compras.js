document.addEventListener('DOMContentLoaded', function () {

    const form = document.getElementById('formCompra');
    if (!form) return;

    // --- Referencias generales ---
    const radiosMoneda = document.querySelectorAll('input[name="moneda"]');
    const tasaInput = document.getElementById('tasa_cambio');

    const moneda = document.querySelector('input[name="moneda"]:checked').value;
    const inputs = document.querySelectorAll(moneda === 'EUR' ? '.input-precio-eur' : '.input-precio-usd');

    // ✨ MAGO: ESCUCHADOR PARA MOSTRAR/OCULTAR FACTURAS MÚLTIPLES ✨
    document.addEventListener('tipoFacturaCambiado', (e) => {
        const tipo = e.detail;
        const contenedoresFactura = document.querySelectorAll('.contenedor-factura-multiple');
        const inputsFactura = document.querySelectorAll('.input-factura-familia');

        if (tipo === 'multiple') {
            contenedoresFactura.forEach(c => c.style.display = 'flex');
        } else {
            contenedoresFactura.forEach(c => c.style.display = 'none');
            inputsFactura.forEach(i => i.value = ''); // Limpiamos para que no mande basura al backend
        }
    });

    radiosMoneda.forEach(radio => {
        radio.addEventListener('change', (e) => {
            const moneda = e.target.value;

            if (moneda === 'EUR') {
                tasaInput.readOnly = false;
                tasaInput.style.backgroundColor = "#2d3748"; // Color editable
                tasaInput.style.color = "#4ade80"; // Resalta en verde
                tasaInput.focus();
            } else {
                tasaInput.readOnly = true;
                tasaInput.value = "1.0000";
                tasaInput.style.backgroundColor = "#111";
                tasaInput.style.color = "#aaa";
            }

            // Cada vez que cambies de moneda, actualizamos la visibilidad y los cálculos
            gestionarColumnasVisibles(moneda);
            recalcularTotales();
        });
    });

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
    // COSTOS ADICIONALES (MODIFICADO: SOLO USD)
    // ==========================================================

    function agregarFilaCosto(dataRestauracion = null) {
        const tr = document.createElement('tr');

        tr.innerHTML = `
            <td>
                <select name="id_costo[]" class="form-control">
                    <option value="">Seleccione costo</option>
                    ${window.costosOptions || ''}
                </select>
            </td>

            <td>
                <input type="number" step="0.01" name="valor_usd[]" class="form-control campo-costo-usd" placeholder="$ 0.00">
            </td>

            <td style="text-align: center;">
                <button type="button" class="btn-eliminar-fila btn btn-danger btn-sm">X</button>
            </td>
        `;

        tablaCostosBody.appendChild(tr);

        const selectCosto = tr.querySelector('select[name="id_costo[]"]');
        const inputUsd = tr.querySelector('.campo-costo-usd');
        const btnEliminar = tr.querySelector('.btn-eliminar-fila');

        if(dataRestauracion) {
            selectCosto.value = dataRestauracion.id_costo;
            inputUsd.value = dataRestauracion.valor_usd;
        }

        inputUsd.addEventListener('input', recalcularTotales);

        btnEliminar.addEventListener('click', () => {
            tr.remove();
            recalcularTotales();
        });
    }

    if (btnAgregarCosto) {
        btnAgregarCosto.addEventListener('click', () => agregarFilaCosto());
    }

    // Familias...

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

        // ✨ MAGO: LEYENDO ESTADO ACTUAL DEL SWITCH PARA SABER SI DEBE NACER OCULTO O VISIBLE
        const radioFacturacion = document.querySelector('input[name="tipo_facturacion"]:checked');
        const tipoFacturacionActual = radioFacturacion ? radioFacturacion.value : 'unica';
        const displayFactura = tipoFacturacionActual === 'multiple' ? 'flex' : 'none';

        // ✨ MAGO: MODIFICAMOS LA CABECERA PARA INYECTAR EL INPUT DE LA FACTURA
// ✨ MAGO: CABECERA BLINDADA CON COLORES OSCUROS
        bloque.innerHTML = `
        <div class="bloque-familia-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; margin-bottom: 15px; padding-bottom: 10px; border-bottom: 2px solid #e5e7eb;">
            <div style="display: flex; align-items: center; gap: 20px;">

                <strong style="font-size: 1.2em; color: #000000 !important; text-transform: uppercase;">
                    <i class="fa-solid fa-tag"></i> Calidad: ${nombre}
                </strong>

                <div class="contenedor-factura-multiple" style="display: ${displayFactura}; align-items: center; gap: 10px; background: #f3f4f6; padding: 6px 12px; border-radius: 6px; border: 1px solid #d1d5db;">
                    <label style="color: #000000 !important; font-size: 1em; margin: 0; font-weight: 900;">No. Factura:</label>
                    <input type="text" name="factura_familia[${idFam}]" class="input-factura-familia form-control" style="width: 140px; padding: 5px 10px; font-size: 0.95em; background: #1f2937 !important; border: 1px solid #3b82f6 !important; color: #ffffff !important; font-weight: bold;" placeholder="Ej: F-1234">
                </div>

            </div>

            <div>
                <button type="button" class="btn-agregar btn-agregar-producto">+ Producto</button>
                <button type="button" class="btn-eliminar-familia">Eliminar Calidad</button>
            </div>
        </div>

        <input type="hidden" name="familias_seleccionadas[]" value="${idFam}">
        
        <table class="tabla-datos tabla-productos-familia">
            <thead>
                <tr style="background: #f4f4f4; color: #333; text-align: center; font-size: 0.85em; font-weight: bold;">
                    <th style="padding: 10px; border-bottom: 2px solid #ddd;">Producto</th>
                    <th style="padding: 10px; width: 60px; border-bottom: 2px solid #ddd;">Cant.</th>
                    <th style="padding: 10px; width: 85px; border-bottom: 2px solid #ddd;">KG</th>
                    <th style="padding: 10px; width: 85px; border-bottom: 2px solid #ddd;">LB</th>
                    <th style="padding: 10px; width: 100px; border-bottom: 2px solid #ddd; color: #059669;">Precio EUR/kg</th>
                    <th style="padding: 10px; width: 100px; border-bottom: 2px solid #ddd;">Precio USD/kg</th>
                    <th style="padding: 10px; width: 95px; border-bottom: 2px solid #ddd; color: #059669;">Importe EUR</th>
                    <th style="padding: 10px; width: 95px; border-bottom: 2px solid #ddd;">Importe USD</th>
                    <th style="padding: 10px; width: 40px; border-bottom: 2px solid #ddd;">Acción</th>
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

    function agregarFilaProducto(bloque, idFam, dataRestauracion = null) {
        return new Promise((resolve) => {
            const tbody = bloque.querySelector('tbody');
            const tr = document.createElement('tr');
            const monedaActiva = document.querySelector('input[name="moneda"]:checked').value;

        tr.innerHTML = `
            <td><select name="id_producto[]" class="producto-select" style="width: 100%; font-size: 11px;"></select></td>
            <td><input type="number" step="0.0001" name="cantidad[]" class="campo-prod input-cantidad" style="width: 100%; text-align: center;"></td>
            <td><input type="number" step="0.0001" name="peso_kg[]" class="campo-prod input-peso-kg" style="width: 100%; text-align: right;"></td>
            <td><input type="text" name="peso_lb[]" readonly style="width: 100%; text-align: right; background-color: #2d3748; color: #fff; border:none;"></td>

            <td><input type="number" step="0.0001" name="precio_kg_eur[]" class="campo-prod input-precio-eur"
                ${monedaActiva === 'USD' ? 'readonly' : ''}
                style="width: 100%; text-align: right; ${monedaActiva === 'EUR' ? 'background: #fff9db; color: #000;' : 'background: #2d3748; color: #fff;'}"></td>

            <td><input type="number" step="0.0001" name="precio_kg_usd[]" class="campo-prod input-precio-usd"
                ${monedaActiva === 'EUR' ? 'readonly' : ''}
                style="width: 100%; text-align: right; ${monedaActiva === 'USD' ? 'background: #fff9db; color: #000;' : 'background: #2d3748; color: #fff;'}"></td>

            <td><input type="text" name="importe_eur[]" readonly class="input-importe-eur" style="width: 100%; text-align: right; background: #2d3748; color: #fff; border:none;"></td>

            <td><input type="text" name="importe_usd[]" readonly class="input-importe-usd" style="width: 100%; text-align: right; background: #2d3748; color: #fff; border:none;"></td>

            <td style="text-align: center;">
                <input type="hidden" name="familia_producto[]" value="${idFam}">
                <button type="button" class="btn-eliminar-fila btn btn-danger btn-sm">X</button>
            </td>
        `;

        tbody.appendChild(tr);

        const selectProducto = tr.querySelector('.producto-select');
        const inputCant      = tr.querySelector('.input-cantidad');
        const inputsCalc     = tr.querySelectorAll('.campo-prod');
        const btnEliminar    = tr.querySelector('.btn-eliminar-fila');

        inputCant.addEventListener('input', (e) => {
            if (e.inputType) {
                inputCant.classList.remove('is-auto');
                inputCant.style.color = "#000";
            }
        });

        inputsCalc.forEach(input => {
            input.addEventListener('input', () => {
                recalcularTotales();
            });
        });

        btnEliminar.addEventListener('click', () => {
            tr.remove();
            recalcularTotales();
        });

        selectProducto.addEventListener('change', () => {
            recalcularTotales();
        });

            fetch(`/productos/por-familia/${encodeURIComponent(idFam)}`)
                .then(r => {
                    if (!r.ok) throw new Error('HTTP ' + r.status);
                    return r.json();
                })
                .then(data => {
                    let html = '<option value="">Seleccione producto</option>';
                    data.forEach(p => {
                        const mm  = parseFloat(p.milimetros || 0);
                        const plg = p.pulgadas || '';
                        let detalles = "";
                        if (mm > 0 || (plg !== '' && plg !== '-')) {
                            const txtMM = mm > 0 ? `${mm} mm` : '';
                            const txtPLG = (plg !== '' && plg !== '-') ? `${plg} plg` : '';
                            const separador = (txtMM && txtPLG) ? ' / ' : '';
                            detalles = ` (${txtMM}${separador}${txtPLG})`;
                        }
                        const texto = `${p.descripcion}${detalles}`;
                        html += `<option value="${p.id_producto}" data-relacion="${p.peso_lb_mts || 0}">${texto}</option>`;
                    });
                    selectProducto.innerHTML = html;
                    
                    if (dataRestauracion) {
                        selectProducto.value = dataRestauracion.id_producto;
                        inputCant.value = dataRestauracion.cantidad;
                        tr.querySelector('.input-peso-kg').value = dataRestauracion.peso_kg;
                        tr.querySelector('[name="peso_lb[]"]').value = dataRestauracion.peso_lb;
                        tr.querySelector('.input-precio-eur').value = dataRestauracion.precio_kg_eur;
                        tr.querySelector('.input-precio-usd').value = dataRestauracion.precio_kg_usd;
                        tr.querySelector('.input-importe-eur').value = dataRestauracion.importe_eur;
                        tr.querySelector('.input-importe-usd').value = dataRestauracion.importe_usd;
                        
                        if(dataRestauracion.is_auto) {
                            inputCant.classList.add('is-auto');
                            inputCant.style.color = "#60a5fa";
                        }
                    }
                    resolve();
                })
                .catch(error => {
                    console.error("Error cargando productos:", error);
                    selectProducto.innerHTML = '<option value="">Error al cargar</option>';
                    resolve();
                });
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
            <td class="res-kg" style="text-align: right;">0.0000</td>
            <td class="res-lb" style="text-align: right;">0.0000</td>
            <td class="res-eur" style="text-align: right; color: #4ade80;">0.00</td>
            <td class="res-usd" style="text-align: right;">0.00</td>
            <td class="res-cif" style="text-align: right;">0.0000</td>
            <td class="res-bodega" style="text-align: right;">0.0000</td>
            <td class="res-total" style="text-align: right; font-weight: bold; color: #60a5fa;">0.00</td>
        `;

        resumenBody.appendChild(tr);
    }

    // ==========================================================
    // RECÁLCULO GENERAL
    // ==========================================================

    function gestionarColumnasVisibles(moneda) {
        document.querySelectorAll('.input-precio-eur').forEach(i => i.readOnly = (moneda === 'USD'));
        document.querySelectorAll('.input-precio-usd').forEach(i => i.readOnly = (moneda === 'EUR'));

        document.querySelectorAll('.input-precio-eur, .input-precio-usd').forEach(i => {
            i.style.backgroundColor = i.readOnly ? "#2d3748" : "#fff9db";
            if (!i.readOnly) i.style.color = "#000";
        });
    }

    function recalcularTotales() {
        const tasa = parseFloat(tasaInput.value || 1);
        const monedaActiva = document.querySelector('input[name="moneda"]:checked').value;

        let totalKG = 0, totalLB = 0, totalUSD = 0, totalEUR = 0, totalCostosUSD = 0;
        const resumenPorFamilia = {};

        contenedorFamilias.querySelectorAll('.bloque-familia').forEach(b => {
            const idFam = b.dataset.idFamilia;
            const nombreFam = b.dataset.nombreFamilia;

            resumenPorFamilia[idFam] = { nombre: nombreFam, kg: 0, lb: 0, eur: 0, usd: 0 };

            b.querySelectorAll('tbody tr').forEach(tr => {
                const inputCant = tr.querySelector('[name="cantidad[]"]');
                const inputKG   = tr.querySelector('[name="peso_kg[]"]');
                const selectP   = tr.querySelector('.producto-select');

                let cantMetros = parseFloat(inputCant.value) || 0;
                let pesoKG     = parseFloat(inputKG.value) || 0;

                const esAuto = inputCant.classList.contains('is-auto');
                const opt = selectP.options[selectP.selectedIndex];
                const relacionLbM = opt ? parseFloat(opt.getAttribute('data-relacion') || 0) : 0;

                if (pesoKG === 0 && esAuto) {
                    inputCant.value = "";
                    inputCant.classList.remove('is-auto');
                }
                else if ((cantMetros === 0 || esAuto) && pesoKG > 0 && relacionLbM > 0) {
                    const pesoLBAux = pesoKG * 2.20462262;
                    cantMetros = pesoLBAux / relacionLbM;

                    inputCant.value = round4(cantMetros);
                    inputCant.classList.add('is-auto');
                    inputCant.style.color = "#60a5fa";
                }
                else if (cantMetros > 0 && !esAuto) {
                    inputCant.style.color = "#000";
                }

                const pesoLB = pesoKG * 2.20462262;
                let precioEUR = 0, precioUSD = 0;

                if (monedaActiva === 'EUR') {
                    precioEUR = parseFloat(tr.querySelector('[name="precio_kg_eur[]"]').value || 0);
                    precioUSD = precioEUR * tasa;
                    tr.querySelector('[name="precio_kg_usd[]"]').value = round4(precioUSD);
                } else {
                    precioUSD = parseFloat(tr.querySelector('[name="precio_kg_usd[]"]').value || 0);
                    precioEUR = 0;
                }

                const importeEUR = pesoKG * precioEUR;
                const importeUSD = pesoKG * precioUSD;

                tr.querySelector('[name="peso_lb[]"]').value = round4(pesoLB);
                tr.querySelector('[name="importe_eur[]"]').value = round2(importeEUR);
                tr.querySelector('[name="importe_usd[]"]').value = round2(importeUSD);

                totalKG += pesoKG; totalLB += pesoLB; totalEUR += importeEUR; totalUSD += importeUSD;

                resumenPorFamilia[idFam].kg += pesoKG;
                resumenPorFamilia[idFam].lb += pesoLB;
                resumenPorFamilia[idFam].eur += importeEUR;
                resumenPorFamilia[idFam].usd += importeUSD;
            });
        });

        tablaCostosBody.querySelectorAll('tr').forEach(tr => {
            const usd = parseFloat(tr.querySelector('[name="valor_usd[]"]').value || 0);
            totalCostosUSD += usd;
        });

        const totalFactura = totalUSD + totalCostosUSD;
        const costoPorLibraAdic = totalLB > 0 ? totalCostosUSD / totalLB : 0;

        lblTotalKG.textContent = round4(totalKG);
        lblTotalLB.textContent = round4(totalLB);
        lblTotalUSD.textContent = round2(totalUSD);
        lblCostosUSD.textContent = round2(totalCostosUSD);
        lblCostoPorLibra.textContent = round4(costoPorLibraAdic);
        lblTotalFactura.textContent = round2(totalFactura);

        Object.keys(resumenPorFamilia).forEach(id => {
            const f = resumenPorFamilia[id];
            const tr = resumenBody.querySelector(`tr[data-familia="${id}"]`);
            if (!tr) return;

            const precioCIF = f.lb > 0 ? f.usd / f.lb : 0;
            const precioBodega = precioCIF + costoPorLibraAdic;
            const totalF = precioBodega * f.lb;

            tr.querySelector('.res-kg').textContent = round4(f.kg);
            tr.querySelector('.res-lb').textContent = round4(f.lb);
            tr.querySelector('.res-eur').textContent = round2(f.eur);
            tr.querySelector('.res-usd').textContent = round2(f.usd);
            tr.querySelector('.res-cif').textContent = round4(precioCIF);
            tr.querySelector('.res-bodega').textContent = round4(precioBodega);
            tr.querySelector('.res-total').textContent = round2(totalF);
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

    // ==========================================================
    // SISTEMA DE BORRADOR (SERVIDOR)
    // ==========================================================

    const btnGuardarBorrador = document.getElementById('btnGuardarBorrador');
    const btnRestaurarBorrador = document.getElementById('btnRestaurarBorrador');

    if (btnGuardarBorrador) {
        btnGuardarBorrador.addEventListener('click', function() {
            const formData = {
                tipo_facturacion: document.querySelector('input[name="tipo_facturacion"]:checked')?.value || 'unica',
                id_proveedor: document.getElementById('id_proveedor').value,
                numero_factura: document.getElementById('numero_factura').value,
                fecha_ingreso: document.getElementById('fecha_ingreso').value,
                fecha_emision_factura: document.getElementById('fecha_emision_factura').value,
                moneda: document.querySelector('input[name="moneda"]:checked')?.value || 'USD',
                tasa_cambio: document.getElementById('tasa_cambio').value,
                costos: [],
                familias: []
            };

            // Recopilar costos
            tablaCostosBody.querySelectorAll('tr').forEach(tr => {
                formData.costos.push({
                    id_costo: tr.querySelector('select[name="id_costo[]"]').value,
                    valor_usd: tr.querySelector('input[name="valor_usd[]"]').value
                });
            });

            // Recopilar familias y productos
            contenedorFamilias.querySelectorAll('.bloque-familia').forEach(b => {
                const idFam = b.dataset.idFamilia;
                const nombreFam = b.dataset.nombreFamilia;
                const factura = b.querySelector('.input-factura-familia')?.value || '';
                
                const familiaData = {
                    id_familia: idFam,
                    nombre: nombreFam,
                    factura: factura,
                    productos: []
                };

                b.querySelectorAll('tbody tr').forEach(tr => {
                    const cantInput = tr.querySelector('[name="cantidad[]"]');
                    familiaData.productos.push({
                        id_producto: tr.querySelector('.producto-select').value,
                        cantidad: cantInput.value,
                        peso_kg: tr.querySelector('[name="peso_kg[]"]').value,
                        peso_lb: tr.querySelector('[name="peso_lb[]"]').value,
                        precio_kg_eur: tr.querySelector('[name="precio_kg_eur[]"]').value,
                        precio_kg_usd: tr.querySelector('[name="precio_kg_usd[]"]').value,
                        importe_eur: tr.querySelector('[name="importe_eur[]"]').value,
                        importe_usd: tr.querySelector('[name="importe_usd[]"]').value,
                        is_auto: cantInput.classList.contains('is-auto')
                    });
                });

                formData.familias.push(familiaData);
            });

            // Guardar en el servidor
            fetch('/compras/borrador/guardar', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': window.csrfToken || document.querySelector('input[name="_token"]').value
                },
                body: JSON.stringify({ datos: formData })
            }).then(r => r.json()).then(res => {
                if (res.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Borrador Guardado',
                        text: 'El progreso de la compra se ha guardado de forma segura en la nube.',
                        timer: 2000,
                        showConfirmButton: false,
                        background: '#1f2937', color: '#fff'
                    });
                    
                    window.serverDraftJson = formData;
                    if(btnRestaurarBorrador) btnRestaurarBorrador.style.display = 'inline-block';
                } else {
                    throw new Error(res.message || 'Error guardando borrador');
                }
            }).catch(e => {
                console.error(e);
                Swal.fire('Error', 'No se pudo guardar el borrador.', 'error');
            });
        });
    }

    if (btnRestaurarBorrador) {
        btnRestaurarBorrador.addEventListener('click', async function() {
            if(!window.serverDraftJson) return;
            
            const formData = window.serverDraftJson;

            // 1. Restaurar datos generales
            if(formData.tipo_facturacion) {
                const radio = document.querySelector(`input[name="tipo_facturacion"][value="${formData.tipo_facturacion}"]`);
                if(radio) {
                    radio.checked = true;
                    gestionarTipoFactura(formData.tipo_facturacion);
                }
            }
            if(formData.id_proveedor) document.getElementById('id_proveedor').value = formData.id_proveedor;
            if(formData.numero_factura) document.getElementById('numero_factura').value = formData.numero_factura;
            if(formData.fecha_ingreso) document.getElementById('fecha_ingreso').value = formData.fecha_ingreso;
            if(formData.fecha_emision_factura) document.getElementById('fecha_emision_factura').value = formData.fecha_emision_factura;
            if(formData.moneda) {
                const radioMoneda = document.querySelector(`input[name="moneda"][value="${formData.moneda}"]`);
                if(radioMoneda) {
                    radioMoneda.checked = true;
                    gestionarMoneda(formData.moneda);
                }
            }
            if(formData.tasa_cambio) document.getElementById('tasa_cambio').value = formData.tasa_cambio;

            // Limpiar todo antes de restaurar
            tablaCostosBody.innerHTML = '';
            contenedorFamilias.innerHTML = '';
            resumenBody.innerHTML = '';
            for (let prop in familiasActivas) delete familiasActivas[prop];

            // 2. Restaurar costos
            if (formData.costos) {
                formData.costos.forEach(costo => {
                    agregarFilaCosto(costo);
                });
            }

            // 3. Restaurar familias y productos
            // Deshabilitamos temporalmente botones mientras carga
            btnRestaurarBorrador.disabled = true;
            const textOrg = btnRestaurarBorrador.innerHTML;
            btnRestaurarBorrador.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Restaurando...';

            if (formData.familias) {
                for (const fam of formData.familias) {
                    familiasActivas[fam.id_familia] = true;
                    const opt = selectFamilia.querySelector(`option[value="${fam.id_familia}"]`);
                    if (opt) opt.disabled = true;

                    crearBloqueFamilia(fam.id_familia, fam.nombre);
                    const bloque = contenedorFamilias.querySelector(`.bloque-familia[data-id-familia="${fam.id_familia}"]`);
                    
                    if(fam.factura) {
                        const inputFac = bloque.querySelector('.input-factura-familia');
                        if(inputFac) inputFac.value = fam.factura;
                    }

                    if (fam.productos) {
                        for (const prod of fam.productos) {
                            await agregarFilaProducto(bloque, fam.id_familia, prod);
                        }
                    }
                }
            }
            
            recalcularTotales();
            
            btnRestaurarBorrador.innerHTML = textOrg;
            btnRestaurarBorrador.disabled = false;
            
            Swal.fire({
                icon: 'success',
                title: 'Borrador Restaurado',
                text: 'Se han cargado los datos del borrador.',
                timer: 2000,
                showConfirmButton: false,
                background: '#1f2937', color: '#fff'
            });
        });
    }

    // Comprobar borrador al cargar la página
    if (window.serverDraftJson) {
        if(btnRestaurarBorrador) btnRestaurarBorrador.style.display = 'inline-block';
        
        Swal.fire({
            title: 'Borrador Encontrado en la Nube',
            text: 'Tienes una compra guardada en borrador. ¿Deseas continuarla ahora?',
            icon: 'info',
            showCancelButton: true,
            confirmButtonText: 'Sí, restaurar',
            cancelButtonText: 'No, ignorar',
            confirmButtonColor: '#0ea5e9',
            background: '#1f2937', color: '#fff'
        }).then((result) => {
            if (result.isConfirmed && btnRestaurarBorrador) {
                btnRestaurarBorrador.click();
            }
        });
    }

});
