document.addEventListener("DOMContentLoaded", () => {
    const contenedorLotes = document.getElementById("contenedorLotes");
    // Aquí no necesitamos el botón Guardar porque es consulta

    // 1. FUNCIÓN PARA GENERAR TARJETA (VERSIÓN CONSULTA)
    window.generarTarjetaConsulta = function(prod, correlativo, piezasExistentes = []) {
        const codigoLote = prod.Codigo_Lote || ("L" + String(correlativo).padStart(5, "0"));

        const html = `
        <div class="form-lote consulta" data-id="${prod.idProductos}">
            <h4 style="color: #60a5fa;">${prod.Codigo} - ${prod.Descripcion}</h4>

            <div class="grid-lote">
                <div>
                    <label>Código de lote</label>
                    <input type="text" value="${prod.Codigo_Lote}" readonly class="input-readonly">
                </div>
                <div>
                    <label>Metros totales</label>
                    <input type="text" value="${prod.Cantidad_Total_Metros}" readonly>
                </div>
                <div>
                    <label>Total piezas</label>
                    <input type="text" value="${piezasExistentes.length}" readonly>
                </div>
            </div>

            <div class="tabla-piezas-container">
                <h5>Piezas registradas</h5>
                <table class="tabla-piezas">
                    <thead>
                        <tr>
                            <th>Código pieza</th>
                            <th>Metros inicial</th>
                            <th>Libras inicial</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${piezasExistentes.map(p => `
                            <tr>
                                <td style="color: #fbbf24; font-family: monospace;">${p.codigo}</td>
                                <td>${parseFloat(p.cantidad_metros_inicial).toFixed(2)}</td>
                                <td>${parseFloat(p.peso_libras_inicial).toFixed(2)}</td>
                            </tr>
                        `).join('')}
                    </tbody>
                </table>
                </div>
        </div>
        `;

        contenedorLotes.insertAdjacentHTML("beforeend", html);
    }
});
