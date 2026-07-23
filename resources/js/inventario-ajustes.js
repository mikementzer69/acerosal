(function () {
  const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
  const cfg = window.INV_AJUSTES;
  if (!cfg) return;

  const $ = (sel, root = document) => root.querySelector(sel);
  const $$ = (sel, root = document) => Array.from(root.querySelectorAll(sel));

  function routeWithId(template, id) {
    // template viene con .../0/...; reemplazamos el último /0
    return template.replace(/\/0(\/|$)/, `/${id}$1`);
  }

  async function postJson(url, data) {
    const resp = await fetch(url, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': csrf,
        'Accept': 'application/json'
      },
      body: JSON.stringify(data || {})
    });
    const json = await resp.json().catch(() => ({}));
    if (!resp.ok) {
      const msg = json?.message || 'Error en la operación.';
      throw new Error(msg);
    }
    return json;
  }

  function num(v) {
    const n = parseFloat(v);
    return isNaN(n) ? 0 : n;
  }

  function setWarnTotals(show) {
    const box = $('#alert_totales');
    if (!box) return;
    box.style.display = show ? 'block' : 'none';
  }

  function calcTotalsInitial() {
    let totMet = 0;
    let totLb = 0;
    $$('#tbody_piezas tr').forEach(tr => {
      totMet += num($('.n-metros-inicial', tr)?.value);
      totLb  += num($('.n-libras-inicial', tr)?.value);
    });

    $('#totMetIni').textContent = totMet.toFixed(2);
    $('#totLbIni').textContent = totLb.toFixed(2);

    const loteMet = num($('#cantidad_total_metros')?.value);
    const loteLb  = num($('#peso_total_libras')?.value);

    const metOk = Math.abs(totMet - loteMet) < 0.01;
    const lbOk  = Math.abs(totLb - loteLb) < 0.01;

    // Advertencia NO bloqueante (según regla)
    setWarnTotals(!(metOk && lbOk));
  }

  function validateRowStrict(tr) {
    // inicial = actual + recortado (metros y libras). Bloqueante para guardar pieza.
    const mi = num($('.n-metros-inicial', tr).value);
    const ma = num($('.n-metros-actual', tr).value);
    const mr = num($('.n-metros-rec', tr).value);

    const li = num($('.n-libras-inicial', tr).value);
    const la = num($('.n-libras-actual', tr).value);
    const lr = num($('.n-libras-rec', tr).value);

    const metOk = Math.abs(mi - (ma + mr)) < 0.01;
    const lbOk  = Math.abs(li - (la + lr)) < 0.01;

    return { metOk, lbOk, ok: metOk && lbOk };
  }

  function attachInputRecalc() {
    $$('#tbody_piezas input').forEach(inp => {
      inp.addEventListener('input', () => {
        calcTotalsInitial();
      });
    });
    ['#cantidad_total_metros', '#peso_total_libras'].forEach(sel => {
      $(sel)?.addEventListener('input', calcTotalsInitial);
    });
  }

  async function onGuardarLote() {
    const payload = {
      codigo: $('#codigo_lote').value.trim(),
      fecha_ingreso: $('#fecha_ingreso').value,
      peso_total_libras: num($('#peso_total_libras').value),
      cantidad_total_metros: num($('#cantidad_total_metros').value),
      relacion_cantidad_peso: num($('#relacion_cantidad_peso').value),
      total_piezas: parseInt($('#total_piezas').value || '0', 10),
      unidad_medida_peso: $('#unidad_medida_peso').value.trim(),
      unidad_medida_longitud: $('#unidad_medida_longitud').value.trim(),
    };

    await postJson(cfg.routes.updateLote, payload);
    calcTotalsInitial();
    alert('Lote actualizado.');
  }

  async function onGuardarPieza(tr) {
    const id = tr.getAttribute('data-id-pieza');
    const codigo = tr.getAttribute('data-codigo');

    const strict = validateRowStrict(tr);
    if (!strict.ok) {
      alert('Validación: inicial debe ser igual a actual + recortado (metros y libras).');
      return;
    }

    const payload = {
      cantidad_metros_inicial: num($('.n-metros-inicial', tr).value),
      peso_libras_inicial: num($('.n-libras-inicial', tr).value),
      cantidad_metros_actual: num($('.n-metros-actual', tr).value),
      peso_libras_actual: num($('.n-libras-actual', tr).value),
      cantidad_metros_recortados: num($('.n-metros-rec', tr).value),
      peso_libras_recortados: num($('.n-libras-rec', tr).value),
    };

    const url = routeWithId(cfg.routes.updatePieza, id);
    await postJson(url, payload);

    calcTotalsInitial();
    alert(`Pieza actualizada: ${codigo}`);
  }

  async function onRetirarPieza(tr) {
    const id = tr.getAttribute('data-id-pieza');
    const codigo = tr.getAttribute('data-codigo');

    const causa = prompt(`Causa del retiro para la pieza ${codigo}:`);
    if (!causa || !causa.trim()) return;

    const url = routeWithId(cfg.routes.retirarPieza, id);
    await postJson(url, { causa: causa.trim() });

    // UI: marcar estado
    $('.col-estado', tr).textContent = 'Retirada';
    alert(`Pieza retirada: ${codigo}`);
  }

  async function onEliminarPieza(tr) {
    const id = tr.getAttribute('data-id-pieza');
    const codigo = tr.getAttribute('data-codigo');

    if (!confirm(`¿Eliminar lógicamente la pieza ${codigo}?`)) return;

    const url = routeWithId(cfg.routes.eliminarPieza, id);
    await postJson(url, {});

    tr.remove();
    calcTotalsInitial();
    alert(`Pieza eliminada: ${codigo}`);
  }

  async function onEliminarLote() {
    if (!confirm('¿Eliminar lógicamente el lote y sus piezas (cascada)?')) return;
    await postJson(cfg.routes.eliminarLote, {});
    alert('Lote eliminado. Regresando a la lista de lotes.');
    window.location.href = '/inventario/ajustes/lotes';
  }

  function bindRowButtons() {
    $$('#tbody_piezas tr').forEach(tr => {
      $('.btnGuardarPieza', tr)?.addEventListener('click', () => onGuardarPieza(tr));
      $('.btnRetirarPieza', tr)?.addEventListener('click', () => onRetirarPieza(tr));
      $('.btnEliminarPieza', tr)?.addEventListener('click', () => onEliminarPieza(tr));
    });
  }

  function initBarcodeCells() {
    // Por ahora: mostramos el código como texto en celda barcode.
    // Si querés SVG en pantalla, lo generamos en backend por endpoint y lo inyectamos.
    $$('.barcode-cell').forEach(cell => {
      const t = cell.getAttribute('data-barcode-text') || '';
      cell.textContent = t;
    });
  }

  // Init
  $('#btnGuardarLote')?.addEventListener('click', onGuardarLote);
  $('#btnEliminarLote')?.addEventListener('click', onEliminarLote);

  initBarcodeCells();
  bindRowButtons();
  attachInputRecalc();
  calcTotalsInitial();
})();
