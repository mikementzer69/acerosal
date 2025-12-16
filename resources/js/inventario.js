document.addEventListener("DOMContentLoaded", () => {

    const listbox = document.getElementById("listboxCompras");
    const infoCompra = document.getElementById("infoCompra");
    const contenedorLotes = document.getElementById("contenedorLotes");
    const btnGuardar = document.getElementById("btnGuardarInventario");

    let currentCompraId = null;
    let lotes = {};
    let piezas = {};

    // ============================
    // 1. SELECCIONAR COMPRA
    // ============================
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
        piezas = {};
        contenedorLotes.innerHTML = "";

        // generar tarjetas JS
        data.detalle.forEach((prod, index) => {
            generarTarjeta(prod, index + 1);
        }); 
    });

    // ============================
    // 2. GENERAR TARJETA LOTES
    // ============================
    function generarTarjeta(prod, correlativo) {

        const codigoLote = "L" + String(correlativo).padStart(5, "0");

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

        piezas[prod.idProductos] = [];

        const html = `
        <div class="form-lote" data-id="${prod.idProductos}">
            <h4>${prod.Codigo} - ${prod.Descripcion}</h4>

            <div class="grid-lote">
                <div>
                    <label>Código de lote</label>
                    <input type="text" class="codigo-lote"
                        id="codigoLote_${prod.idProductos}"
                        value="${codigoLote}">
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
                    <input type="text"
                        value="${(prod.Peso_Total_Libras / prod.Cantidad_Total_Metros).toFixed(6)}"
                        readonly>
                </div>
            </div>

            <div class="tabla-piezas-container">
                <h5>Piezas del lote</h5>
                <table class="tabla-piezas">
                    <thead>
                        <tr>
                            <th>Código pieza</th>
                            <th>Metros inicial</th>
                            <th>Libras inicial</th>
                            <th>Acción</th>
                        </tr>
                    </thead>

                    <tbody id="tbody_${prod.idProductos}">
                    </tbody>

                    <tfoot>
                        <tr>
                            <th>Total</th>
                            <th id="totMet_${prod.idProductos}">0.00</th>
                            <th id="totLb_${prod.idProductos}">0.00</th>
                            <th></th>
                        </tr>
                    </tfoot>
                </table>

                <button class="btn-agregar-pieza" data-id="${prod.idProductos}">
                    ➕ Agregar pieza
                </button>
            </div>
        </div>
        `;

        contenedorLotes.insertAdjacentHTML("beforeend", html);
    }

    // ============================
    // 3. AGREGAR PIEZA
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
        const metros = prompt("Metros inicial de la pieza:");
        if (!metros || isNaN(metros)) return;

        const relacion = lotes[idProd].Relacion_Cantidad_Peso;
        const peso = relacion * parseFloat(metros);

        const index = piezas[idProd].length + 1;

        const codigo = `${lotes[idProd].Codigo_Producto}-${lotes[idProd].Codigo}-${String(index).padStart(3, '0')}`;

        piezas[idProd].push({
            Codigo: codigo,
            Cantidad_Metros_Inicial: parseFloat(metros),
            Peso_Libras_Inicial: peso,
        });

        actualizarTabla(idProd);
    }

    function eliminarPieza(idProd, index) {
        piezas[idProd].splice(index, 1);
        actualizarTabla(idProd);
    }

    function actualizarTabla(idProd) {

        const tbody = document.getElementById(`tbody_${idProd}`);
        tbody.innerHTML = "";

        let totalMet = 0;
        let totalLb = 0;

        piezas[idProd].forEach((p, i) => {
            totalMet += p.Cantidad_Metros_Inicial;
            totalLb += p.Peso_Libras_Inicial;

            tbody.insertAdjacentHTML("beforeend", `
                <tr>
                    <td>${p.Codigo}</td>
                    <td>${p.Cantidad_Metros_Inicial.toFixed(2)}</td>
                    <td>${p.Peso_Libras_Inicial.toFixed(2)}</td>
                    <td>
                        <button class="btn-eliminar-pieza"
                            data-prod="${idProd}"
                            data-index="${i}">
                            ❌
                        </button>
                    </td>
                </tr>
            `);
        });

        document.getElementById(`totMet_${idProd}`).textContent = totalMet.toFixed(2);
        document.getElementById(`totLb_${idProd}`).textContent = totalLb.toFixed(2);
        document.getElementById(`total_${idProd}`).value = piezas[idProd].length;

        lotes[idProd].Total_Piezas = piezas[idProd].length;
    }

    // ============================
    // 4. GUARDAR INVENTARIO
    // ============================
    btnGuardar.addEventListener("click", async () => {

        if (!currentCompraId) {
            alert("Seleccione una compra.");
            return;
        }

        const formData = new FormData();
        formData.append("idCompra", currentCompraId);
        formData.append("lotes", JSON.stringify(Object.values(lotes)));
        formData.append("piezas", JSON.stringify(piezas));

        const res = await fetch("/inventario/automatico/guardar", {
            method: "POST",
            headers: {
                "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content,
            },
            body: formData
        });

        const data = await res.json();

        alert(data.message);

        if (data.success) {
            location.reload();
        }
    });
});