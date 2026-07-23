document.addEventListener("DOMContentLoaded", () => {

    const familiaSelect = document.getElementById("familia_select");
    const productoSelect = document.getElementById("producto_select");
    const tarjetaLote = document.getElementById("tarjeta_lote_manual");

    if (!familiaSelect) return;

    let codigoProducto = "";
    let piezas = [];

    const inputCodigoLote = document.getElementById("codigo_lote");
    const inputPesoTotal = document.getElementById("peso_total");
    const inputMtsTotal = document.getElementById("mts_total");
    const inputRelacion = document.getElementById("relacion");
    const tituloProducto = document.getElementById("titulo_producto");
    const btnGuardar = document.getElementById("btnGuardar");

    // Mensaje de estado
    const mensajeEstado = document.createElement("div");
    mensajeEstado.style.marginTop = "12px";
    mensajeEstado.style.fontWeight = "bold";
    tarjetaLote?.appendChild(mensajeEstado);

    // ====================================================
    // 1. Cargar productos por familia
    // ====================================================
    familiaSelect.addEventListener("change", async () => {
        const id = familiaSelect.value;
        if (!id) return;

        const resp = await fetch(`/api/productos-por-familia/${id}`);
        const data = await resp.json();

        productoSelect.innerHTML = `<option value="">Seleccione un producto</option>`;
        data.forEach(p => {
            productoSelect.innerHTML += `<option value="${p.id_producto}" data-codigo="${p.codigo}">${p.descripcion}</option>`;
        });

        productoSelect.disabled = false;
        productoSelect.selectedIndex = 0;
    });

    // ====================================================
    // 2. Selección del producto → mostrar tarjeta
    // ====================================================
    productoSelect.addEventListener("change", async () => {
        const opt = productoSelect.selectedOptions[0];
        if (!opt) return;

        codigoProducto = opt.dataset.codigo;
        tituloProducto.innerText = `${codigoProducto} - ${opt.text}`;

        tarjetaLote.style.display = "block";
        tarjetaLote.scrollIntoView({ behavior: "smooth" });

        sugerirCodigoLote();
    });

    // ====================================================
    // 3. Sugerir código de lote (NO asignar)
    // ====================================================
    async function sugerirCodigoLote() {
        const resp = await fetch("/api/lotes/ultimo");
        const data = await resp.json();

        let sugerencia = "L00001";

        if (data?.codigo_lote) {
            const n = parseInt(data.codigo_lote.substring(1)) + 1;
            sugerencia = "L" + String(n).padStart(5, "0");
        }

        inputCodigoLote.placeholder = `Ej: ${sugerencia}`;
        validarTotales();
    }

    // ====================================================
    // 4. Relación lb/m
    // ====================================================
    function calcularRelacion() {
        const peso = parseFloat(inputPesoTotal.value) || 0;
        const mts = parseFloat(inputMtsTotal.value) || 0;

        const rel = mts > 0 ? (peso / mts) : 0;
        inputRelacion.value = rel.toFixed(6);

        recalcularLibras();
        validarTotales();
        actualizarTotalesVisuales()
    }

    inputPesoTotal.addEventListener("input", calcularRelacion);
    inputMtsTotal.addEventListener("input", calcularRelacion);

    // ====================================================
    // 5. Agregar pieza
    // ====================================================
    document.getElementById("btnAddPieza").addEventListener("click", () => {

        const lista = document.getElementById("tbody_piezas");

        const div = document.createElement("tr");
        piezas.push(div);

        const index = piezas.length;
        const lote = inputCodigoLote.value.trim() || inputCodigoLote.placeholder.replace("Ej: ", "");

        const codigo = `${codigoProducto}-${lote}-${String(index).padStart(3, "0")}`;

        div.innerHTML = `
            <td><input type="text" class="codigo_pieza form-control" value="${codigo}" readonly></td>

            <td><input type="number" step="0.01" class="mts_pieza form-control"></td>

            <td><input type="number" step="0.01" class="lbs_pieza form-control" readonly></td>

            <td><button class="btn-remove">❌</button></td>
        `;

        lista.appendChild(div);

        // Evento metros
        div.querySelector(".mts_pieza").addEventListener("input", () => {
            calcularLbsPieza(div);
            validarTotales();
            actualizarTotalesVisuales()
        });

        // Eliminar pieza
        div.querySelector(".btn-remove").addEventListener("click", () => {
            div.remove();
            piezas = piezas.filter(p => p !== div);
            recalcularCodigos();
            validarTotales();
            actualizarTotalesVisuales()
        });

        recalcularCodigos();
        validarTotales();
        actualizarTotalesVisuales()
    });

    // ====================================================
    // 6. Recalcular códigos cuando cambia código lote
    // ====================================================
    inputCodigoLote.addEventListener("input", () => {
        recalcularCodigos();
        validarTotales();
        actualizarTotalesVisuales()
    });

    function recalcularCodigos() {
        const lote = inputCodigoLote.value.trim() || inputCodigoLote.placeholder.replace("Ej: ", "");

        piezas.forEach((p, i) => {
            p.querySelector(".codigo_pieza").value =
                `${codigoProducto}-${lote}-${String(i + 1).padStart(3, "0")}`;
        });
    }

    // ====================================================
    // 7. Calcular libras por pieza
    // ====================================================
    function calcularLbsPieza(div) {
        const mts = parseFloat(div.querySelector(".mts_pieza").value) || 0;
        const rel = parseFloat(inputRelacion.value) || 0;

        div.querySelector(".lbs_pieza").value = (mts * rel).toFixed(3);
        actualizarTotalesVisuales()
    }

    function recalcularLibras() {
        piezas.forEach(p => calcularLbsPieza(p));
    }

    // ====================================================
    // 8. Validación total
    // ====================================================
    function validarTotales() {

        const totalMetrosLote = parseFloat(inputMtsTotal.value) || 0;
        const pesoLote = parseFloat(inputPesoTotal.value) || 0;

        let sumaMetros = 0;
        let sumaLbs = 0;

        piezas.forEach(p => {
            sumaMetros += parseFloat(p.querySelector(".mts_pieza").value) || 0;
            sumaLbs += parseFloat(p.querySelector(".lbs_pieza").value) || 0;
        });

        // METROS — sin tolerancia
        if (sumaMetros.toFixed(2) !== totalMetrosLote.toFixed(2)) {
            mostrarEstado(
                `ERROR — Metros deben coincidir EXACTAMENTE. Lote: ${totalMetrosLote}, Piezas: ${sumaMetros.toFixed(2)}`,
                "red"
            );
            btnGuardar.disabled = true;
            return false;
        }

        // LIBRAS — con tolerancia
        const tolerancia = piezas.length * 0.01;
        const diferencia = Math.abs(pesoLote - sumaLbs);

        if (diferencia > tolerancia) {
            mostrarEstado(
                `ERROR — Libras fuera de tolerancia. Dif: ${diferencia.toFixed(3)}, Tol: ${tolerancia.toFixed(3)}`,
                "red"
            );
            btnGuardar.disabled = true;
            return false;
        }

        mostrarEstado("OK — Totales correctos", "green");
        btnGuardar.disabled = false;
        return true;
    }

    function actualizarTotalesVisuales() {

        const totalPiezasInput = document.getElementById("total_piezas");
        const totMet = document.getElementById("totMet");
        const totLb = document.getElementById("totLb");

        let sumaMetros = 0;
        let sumaLibras = 0;

        piezas.forEach(p => {
            sumaMetros += parseFloat(p.querySelector(".mts_pieza").value) || 0;
            sumaLibras += parseFloat(p.querySelector(".lbs_pieza").value) || 0;
        });

        // Total piezas
        totalPiezasInput.value = piezas.length;

        // Totales del footer
        totMet.textContent = sumaMetros.toFixed(2);
        totLb.textContent = sumaLibras.toFixed(2);
    }

// =======================================================
// 9. GUARDAR INVENTARIO MANUAL
// =======================================================

btnGuardar.addEventListener("click", async () => {

    // Validación final
    if (btnGuardar.disabled) {
        alert("No se puede guardar: revise los totales.");
        return;
    }

    // Datos del lote
    const lote = {
        codigo_lote: inputCodigoLote.value.trim(),
        fecha_ingreso: document.getElementById("fecha_ingreso").value,
        total_piezas: piezas.length,
        peso_total: parseFloat(inputPesoTotal.value),
        mts_total: parseFloat(inputMtsTotal.value),
        relacion: parseFloat(inputRelacion.value),
        id_producto: productoSelect.value
    };

    // Datos de las piezas
    const piezasData = piezas.map(p => ({
        codigo: p.querySelector(".codigo_pieza").value,
        mts: parseFloat(p.querySelector(".mts_pieza").value),
        lbs: parseFloat(p.querySelector(".lbs_pieza").value)
    }));

    const payload = {
        lote,
        piezas: piezasData
    };

    // Envío al servidor
    const resp = await fetch("/inventario/manual/guardar", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify(payload)
    });

    const data = await resp.json();

    if (data.ok) {
        alert("Inventario manual guardado correctamente.");
        window.location.reload();
    } else {
        alert("Error al guardar: " + data.error);
    }
});


    function mostrarEstado(msg, color) {
        mensajeEstado.textContent = msg;
        mensajeEstado.style.color = color;
    }
});
