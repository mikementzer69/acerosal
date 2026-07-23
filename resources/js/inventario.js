document.addEventListener("DOMContentLoaded", () => {

    const listbox = document.getElementById("listboxCompras");
    const infoCompra = document.getElementById("infoCompra");
    const contenedorLotes = document.getElementById("contenedorLotes");
    const btnGuardar = document.getElementById("btnGuardarInventario");

    let currentCompraId = null;
    let lotes = {};
    let piezasVisuales = {}; // Aquí guardamos los "grupos" para la tabla visual

    // ============================
    // 1. SELECCIONAR COMPRA
    // ============================
    // ✨ MAGO: Chaleco antibalas activado. Solo escucha si "listbox" existe en la pantalla.
if (listbox) {
    listbox.addEventListener("change", async () => {
        currentCompraId = listbox.value;
        if (!currentCompraId) return;

        contenedorLotes.innerHTML = "<p>Cargando detalle...</p>";

        const res = await fetch(`/inventario/compra/${currentCompraId}/detalle`);
        const data = await res.json();

        if (!data.success) {
            alert(data.message);
            return;
        }

        // mostrar info compra
        infoCompra.innerHTML = `
            <div><strong>Factura:</strong> ${data.compra.Numero_Factura}</div>
            <div><strong>Proveedor:</strong> ${data.compra.Proveedor}</div>
            <div><strong>Empresa:</strong> ${data.compra.Empresa}</div>
            <div><strong>Emisión:</strong> ${data.compra.Fecha_EmisionF}</div>
            <div><strong>Ingreso:</strong> ${data.compra.Fecha_Ingreso}</div>
        `;

        // reset estructura
        lotes = {};
        piezasVisuales = {};
        contenedorLotes.innerHTML = "";

        // generar tarjetas JS
        data.detalle.forEach((prod, index) => {
            generarTarjeta(prod, index + 1);
        });
    });
}

    // ============================
    // 2. GENERAR TARJETA LOTES
    // ============================
    function generarTarjeta(prod, correlativo) {

        const codigoLote = "L" + String(correlativo).padStart(5, "0");

        piezasVisuales[prod.idProductos] = [];

        lotes[prod.idProductos] = {
            Id_Productos: prod.idProductos,
            Codigo_Producto: prod.Codigo,
            Codigo: codigoLote,
            Fecha_Ingreso: new Date().toISOString().substring(0, 10),
            Peso_Total_Libras: prod.Peso_Total_Libras,
            Cantidad_Total_Metros: prod.Cantidad_Total_Metros,
            Relacion_Cantidad_Peso: prod.Peso_Total_Libras / prod.Cantidad_Total_Metros,
            Total_Piezas: 0
        };

    const html = `
<div class="form-lote" data-id="${prod.idProductos}">
    <h4 style="display: flex; align-items: center; gap: 10px; margin: 0 0 15px 0; color: #fff;">
        ${prod.Codigo} - ${prod.Descripcion}

        <span style="display: inline-flex; gap: 5px; margin-left: 10px;">
            <span style="background: #1e3a8a; color: #60a5fa; padding: 2px 8px; border-radius: 4px; font-size: 0.75em; border: 1px solid #1e40af;">
                ${prod.Milimetros} MM
            </span>
            <span style="background: #78350f; color: #fbbf24; padding: 2px 8px; border-radius: 4px; font-size: 0.75em; border: 1px solid #92400e;">
                ${prod.Pulgadas} PULG
            </span>
        </span>
    </h4>
            </div>

            <div class="grid-lote">
                <div>
                    <label>Código de lote</label>
                    <input type="text" class="codigo-lote" id="codigoLote_${prod.idProductos}" value="${codigoLote}">
                </div>
                <div>
                    <label>Fecha ingreso</label>
                    <input type="date" value="${lotes[prod.idProductos].Fecha_Ingreso}" readonly>
                </div>
                <div>
                    <label>Total piezas</label>
                    <input type="text" id="total_${prod.idProductos}" value="0" readonly>
                </div>
                <div>
                    <label>Peso total (lb)</label>
                    <input type="text" value="${prod.Peso_Total_Libras}" readonly>
                </div>
                <div>
                    <label>Metros totales</label>
                    <input type="text" value="${prod.Cantidad_Total_Metros}" readonly>
                </div>
                <div>
                    <label>Relación lb/m</label>
                    <input type="text" value="${(prod.Peso_Total_Libras / prod.Cantidad_Total_Metros).toFixed(6)}" readonly>
                </div>
            </div>

            <div class="tabla-piezas-container">
                <h5>Piezas del lote</h5>
                <table class="tabla-piezas">
                    <thead>
                        <tr>
                            <th width="10%">Cant.</th>
                            <th width="20%">Rango Códigos</th>
                            <th width="20%">Longitud (m)</th>
                            <th width="20%">Peso Total (lb)</th>
                            <th width="10%">Acción</th>
                        </tr>
                    </thead>
                    <tbody id="tbody_${prod.idProductos}"></tbody>
                    <tfoot>
                        <tr>
                            <th colspan="2">Total</th>
                            <th id="totMet_${prod.idProductos}">0.00</th>
                            <th id="totLb_${prod.idProductos}">0.00</th>
                            <th></th>
                        </tr>
                    </tfoot>
                </table>
                <button class="btn-agregar-pieza" data-id="${prod.idProductos}">
                    ➕ Agregar grupo de piezas
                </button>
            </div>
        </div>
        `;

        // Recuperar consecutivo real del servidor
        fetch(`/inventario/producto/${prod.idProductos}/siguiente-lote`)
            .then(res => res.json())
            .then(data => {
                const inputReal = document.getElementById(`codigoLote_${prod.idProductos}`);
                if (inputReal && data.codigo) {
                    inputReal.value = data.codigo;
                    lotes[prod.idProductos].Codigo = data.codigo;
                }
            })
            .catch(err => console.log("..."));

        contenedorLotes.insertAdjacentHTML("beforeend", html);
    }

    // ============================
    // 3. AGREGAR PIEZA (INTERACTIVO)
    // ============================
    document.addEventListener("click", (e) => {
        if (e.target.classList.contains("btn-agregar-pieza")) {
            const idProd = e.target.dataset.id;
            agregarPieza(idProd);
        }

        if (e.target.classList.contains("btn-eliminar-pieza")) {
            eliminarPieza(e.target.dataset.prod, e.target.dataset.index);
        }
    });

    function agregarPieza(idProd) {
        // 1. Preguntamos Cantidad
        const cantidadStr = prompt("¿Cuántas piezas de esta medida?", "1");
        if (!cantidadStr) return;

        const cantidad = parseInt(cantidadStr);
        if (isNaN(cantidad) || cantidad <= 0) {
            alert("La cantidad debe ser mayor a 0");
            return;
        }

        // 2. Preguntamos Longitud
        const metrosStr = prompt(`Ingrese la longitud (metros) para estas ${cantidad} piezas:`);
        if (!metrosStr || isNaN(metrosStr)) return;

        const metrosUnitarios = parseFloat(metrosStr);
        const relacion = lotes[idProd].Relacion_Cantidad_Peso;

        // Calculamos peso total del grupo
        const pesoTotalGrupo = (relacion * metrosUnitarios) * cantidad;

        // Calculamos rangos visuales
        // Contamos cuántas piezas llevamos acumuladas en TOTAL para saber el correlativo
        let conteoPrevio = 0;
        piezasVisuales[idProd].forEach(p => conteoPrevio += p.Cantidad);

        const inicio = conteoPrevio + 1;
        const fin = conteoPrevio + cantidad;

        const codigoBase = `${lotes[idProd].Codigo_Producto}-${lotes[idProd].Codigo}`;
        let codigoVisual = "";

        if (cantidad === 1) {
            codigoVisual = `${String(inicio).padStart(3, '0')}`;
        } else {
            codigoVisual = `${String(inicio).padStart(3, '0')} - ${String(fin).padStart(3, '0')}`;
        }

        // Guardamos en el array visual
        piezasVisuales[idProd].push({
            CodigoVisual: codigoVisual,
            Cantidad: cantidad,
            MetrosUnitario: metrosUnitarios,
            PesoTotal: pesoTotalGrupo,
            PesoUnitario: (relacion * metrosUnitarios) // Guardamos el unitario para la explosión final
        });

        actualizarTabla(idProd);
    }

    function eliminarPieza(idProd, index) {
        piezasVisuales[idProd].splice(index, 1);
        actualizarTabla(idProd);
    }

    function actualizarTabla(idProd) {
        const tbody = document.getElementById(`tbody_${idProd}`);
        tbody.innerHTML = "";

        let totalMetGlobal = 0;
        let totalLbGlobal = 0;
        let conteoPiezasFisicas = 0;

        piezasVisuales[idProd].forEach((p, i) => {
            const metrosTotalesFila = p.MetrosUnitario * p.Cantidad;

            totalMetGlobal += metrosTotalesFila;
            totalLbGlobal += p.PesoTotal;
            conteoPiezasFisicas += p.Cantidad;

            tbody.insertAdjacentHTML("beforeend", `
                <tr>
                    <td style="font-weight:bold; color:#4ade80;">${p.Cantidad}</td>
                    <td>${p.CodigoVisual}</td>
                    <td>${p.MetrosUnitario.toFixed(2)}</td>
                    <td>${p.PesoTotal.toFixed(2)}</td>
                    <td>
                        <button class="btn-eliminar-pieza" data-prod="${idProd}" data-index="${i}">❌</button>
                    </td>
                </tr>
            `);
        });

        // Totales visuales
        const celdaMetros = document.getElementById(`totMet_${idProd}`);
        document.getElementById(`totLb_${idProd}`).textContent = totalLbGlobal.toFixed(2);

        document.getElementById(`total_${idProd}`).value = conteoPiezasFisicas;
        lotes[idProd].Total_Piezas = conteoPiezasFisicas;

        // Validación (Rojo/Verde)
        const maxMetros = lotes[idProd].Cantidad_Total_Metros;

        if (Math.abs(totalMetGlobal - maxMetros) > 0.05) {
            celdaMetros.style.color = "#ef4444"; // Rojo
            celdaMetros.style.fontWeight = "bold";
            celdaMetros.textContent = `${totalMetGlobal.toFixed(2)} (Faltan/Sobran)`;
            lotes[idProd].tieneError = true;
        } else {
            celdaMetros.style.color = "#4ade80"; // Verde
            celdaMetros.style.fontWeight = "bold";
            celdaMetros.textContent = totalMetGlobal.toFixed(2) + " ✔️";
            lotes[idProd].tieneError = false;
        }
    }

    // ============================
    // 4. GUARDAR INVENTARIO (EL TRUCO MAGISTRAL) 🎩🐇
    // ============================

   // ✨ MAGO: Chaleco antibalas para el botón de Guardar
    if (btnGuardar) {
        btnGuardar.addEventListener("click", async () => {

            if (!currentCompraId) {
                alert("Seleccione una compra.");
                return;
            }

            // 1. Validaciones
            const hayErrores = Object.values(lotes).some(l => l.tieneError === true);
            if (hayErrores) {
                alert("⚠️ Hay lotes que no cuadran con el total de metros. Verifique los números en rojo.");
                return;
            }
            const lotesVacios = Object.values(lotes).some(l => l.Total_Piezas === 0);
            if (lotesVacios) {
                alert("⚠️ Todos los lotes deben tener piezas asignadas.");
                return;
            }

            // 2. PREPARACIÓN DE DATOS (EL TRUCO)
            let piezasParaEnviar = {};

            for (const [idProd, grupos] of Object.entries(piezasVisuales)) {
                piezasParaEnviar[idProd] = [];
                let contadorCorrelativo = 1;

                grupos.forEach(grupo => {
                    for (let i = 0; i < grupo.Cantidad; i++) {
                        const codigoCompleto = `${lotes[idProd].Codigo_Producto}-${lotes[idProd].Codigo}-${String(contadorCorrelativo).padStart(3, '0')}`;

                        piezasParaEnviar[idProd].push({
                            Codigo: codigoCompleto,
                            Cantidad_Metros_Inicial: grupo.MetrosUnitario,
                            Peso_Libras_Inicial: grupo.PesoUnitario,
                        });

                        contadorCorrelativo++;
                    }
                });
            }

            // 3. Enviamos los datos "explotados"
            const formData = new FormData();
            formData.append("id_compra", currentCompraId);
            formData.append("lotes", JSON.stringify(Object.values(lotes)));
            formData.append("piezas", JSON.stringify(piezasParaEnviar));

            try {
                const res = await fetch("/inventario/automatico/guardar", {
                    method: "POST",
                    headers: {
                        "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: formData
                });

                const data = await res.json();

                if (data.success) {
                    if (typeof window.procesarCostosFinancieros === 'function') {
                        window.procesarCostosFinancieros();
                    } else {
                        alert(data.message);
                        location.reload();
                    }
                } else {
                    alert(data.message || "Error al guardar");
                }
            } catch (error) {
                console.error(error);
                alert("Error de conexión al guardar.");
            }
        });
    }
}); // Fin del DOMContentLoaded
