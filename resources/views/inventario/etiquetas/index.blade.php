@extends('layouts.app')

@section('content')
<div class="erp-section">

    {{-- TÍTULO --}}
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 10px;">
        <h2 class="erp-title" style="margin: 0;">
            <i class="fa-solid fa-tags text-warning"></i> Impresión de Etiquetas de Piezas (Viñetas)
        </h2>
        <span class="badge" style="background-color: #1e293b; color: #38bdf8; border: 1px solid #334155; padding: 8px 14px; font-size: 0.85rem;">
            <i class="fa-solid fa-barcode"></i> Identificación Física de Bodega
        </span>
    </div>

    {{-- ALERTAS --}}
    @if(session('success'))
        <div class="form-alert success mb-3">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="form-alert danger mb-3">{{ session('error') }}</div>
    @endif

    {{-- CARD DE FILTROS EN CASCADA --}}
    <div class="card p-3 mb-4" style="background: #1e293b; border: 1px solid #334155; border-radius: 10px;">
        <div style="color: #94a3b8; font-size: 0.85rem; font-weight: 700; text-transform: uppercase; margin-bottom: 15px; letter-spacing: 0.5px;">
            <i class="fa-solid fa-filter me-1 text-primary"></i> Seleccionar Calidad, Medida y Lote
        </div>

        <div class="row g-3">
            {{-- 1. CALIDAD (FAMILIA) --}}
            <div class="col-md-4">
                <label style="color: #38bdf8; font-size: 12px; font-weight: 700; text-transform: uppercase; margin-bottom: 6px; display: block;">
                    1. Calidad (Familia) *
                </label>
                <select id="selectFamilia" class="form-select" style="background: #0f172a; color: #fff; border: 1px solid #475569; border-radius: 6px; padding: 9px 12px; width: 100%;">
                    <option value="">-- Seleccione Calidad --</option>
                    @foreach($familias as $f)
                        <option value="{{ $f->id_familia }}" {{ (string)$f->id_familia === (string)$idFamiliaSeleccionada ? 'selected' : '' }}>
                            {{ $f->nombre }} {{ $f->codigo ? '('.$f->codigo.')' : '' }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- 2. PRODUCTO (MEDIDA / DIÁMETRO) --}}
            <div class="col-md-4">
                <label style="color: #38bdf8; font-size: 12px; font-weight: 700; text-transform: uppercase; margin-bottom: 6px; display: block;">
                    2. Medida / Producto *
                </label>
                <select id="selectProducto" class="form-select" disabled style="background: #0f172a; color: #fff; border: 1px solid #475569; border-radius: 6px; padding: 9px 12px; width: 100%;">
                    <option value="">-- Primero elija Calidad --</option>
                </select>
            </div>

            {{-- 3. LOTE --}}
            <div class="col-md-4">
                <label style="color: #38bdf8; font-size: 12px; font-weight: 700; text-transform: uppercase; margin-bottom: 6px; display: block;">
                    3. Lote *
                </label>
                <select id="selectLote" class="form-select" disabled style="background: #0f172a; color: #fff; border: 1px solid #475569; border-radius: 6px; padding: 9px 12px; width: 100%;">
                    <option value="">-- Primero elija Producto --</option>
                </select>
            </div>
        </div>
    </div>

    {{-- ÁREA DE PIEZAS DEL LOTE --}}
    <div id="seccionPiezas" style="display: none;">
        
        <form id="formImprimirEtiquetas" action="{{ route('inventario.etiquetas.imprimir') }}" method="GET" target="_blank">
            
            {{-- BARRA DE HERRAMIENTAS Y RESUMEN DEL LOTE --}}
            <div style="background: #111827; border: 1px solid #374151; border-radius: 8px; padding: 14px 18px; margin-bottom: 15px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
                <div>
                    <h5 id="lblInfoLote" class="mb-0 fw-bold text-white">Piezas del Lote</h5>
                    <small id="lblSubInfoLote" class="text-muted"></small>
                </div>

                <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
                    {{-- SELECTOR DE FORMATO --}}
                    <div style="display: flex; align-items: center; gap: 6px;">
                        <span style="color: #94a3b8; font-size: 0.8rem;">Formato:</span>
                        <select name="formato" id="formatoImpresion" style="background: #1e293b; color: #fff; border: 1px solid #475569; border-radius: 5px; padding: 5px 8px; font-size: 0.85rem;">
                            <option value="rollo">🏷️ Rollo Térmico / Sticker Individual</option>
                            <option value="cuadricula">📄 Hoja Cuadrícula (Múltiples)</option>
                        </select>
                    </div>

                    {{-- BOTÓN IMPRIMIR TODO EL LOTE --}}
                    <button type="button" id="btnImprimirTodoLote" class="btn btn-sm btn-outline-warning" style="padding: 7px 14px; font-weight: 600;">
                        <i class="fa-solid fa-print me-1"></i> Imprimir Todo el Lote
                    </button>

                    {{-- BOTÓN IMPRIMIR SELECCIONADAS --}}
                    <button type="submit" id="btnImprimirSeleccionadas" class="btn btn-sm btn-success" style="padding: 7px 18px; font-weight: 700; background-color: #10b981; border: none;">
                        <i class="fa-solid fa-tags me-1"></i> Imprimir Seleccionadas (<span id="cantSeleccionadas">0</span>)
                    </button>
                </div>
            </div>

            {{-- TABLA DE PIEZAS --}}
            <div class="erp-table-container">
                <table class="erp-table">
                    <thead>
                        <tr>
                            <th style="width: 45px; text-align: center;">
                                <input type="checkbox" id="checkTodos" title="Marcar/Desmarcar todas" style="cursor: pointer; width: 16px; height: 16px;">
                            </th>
                            <th style="width: 60px; text-align: center;">#</th>
                            <th>Código de Pieza</th>
                            <th style="text-align: right;">Longitud Actual (Mts)</th>
                            <th style="text-align: right;">Peso Actual (Lb)</th>
                            <th style="text-align: center;">Estado</th>
                            <th style="width: 130px; text-align: center;">Acción</th>
                        </tr>
                    </thead>
                    <tbody id="tablaPiezasBody">
                        {{-- Llenado dinámico vía JS --}}
                    </tbody>
                </table>
            </div>

        </form>
    </div>

    {{-- PANTALLA INICIAL VACÍA / INDICADOR --}}
    <div id="pantallaEspera" style="padding: 60px 20px; text-align: center; background: #0f172a; border: 1px dashed #334155; border-radius: 10px; margin-top: 15px;">
        <i class="fa-solid fa-tags" style="font-size: 3.5rem; color: #334155; margin-bottom: 15px; display: block;"></i>
        <h5 style="color: #94a3b8; font-weight: 600;">Seleccione una Calidad, Producto y Lote para cargar las piezas</h5>
        <p style="color: #64748b; font-size: 0.9rem; max-width: 500px; margin: 0 auto;">
            Podrá elegir e imprimir etiquetas adhesivas con código de barras, número de pieza, lote y dimensiones físicas para pegarlas en bodega.
        </p>
    </div>

</div>

{{-- SCRIPT PARA CASCADA AJAX --}}
<script>
document.addEventListener('DOMContentLoaded', function() {
    const selFam  = document.getElementById('selectFamilia');
    const selProd = document.getElementById('selectProducto');
    const selLote = document.getElementById('selectLote');
    
    const seccionPiezas  = document.getElementById('seccionPiezas');
    const pantallaEspera = document.getElementById('pantallaEspera');
    const tbody          = document.getElementById('tablaPiezasBody');
    const checkTodos     = document.getElementById('checkTodos');
    const cantBadge      = document.getElementById('cantSeleccionadas');
    const btnImprimirSel = document.getElementById('btnImprimirSeleccionadas');
    const btnImprimirTodo= document.getElementById('btnImprimirTodoLote');

    // Valores iniciales si vienen por URL
    let famInit  = "{{ $idFamiliaSeleccionada ?? '' }}";
    let prodInit = "{{ $idProductoSeleccionado ?? '' }}";
    let loteInit = "{{ $idLoteSeleccionado ?? '' }}";

    // 1. CAMBIO DE FAMILIA (CALIDAD)
    selFam.addEventListener('change', function() {
        cargarProductos(this.value);
    });

    function cargarProductos(idFamilia, preselectProd = null) {
        selProd.innerHTML = '<option value="">⏳ Cargando medidas...</option>';
        selProd.disabled = true;
        selLote.innerHTML = '<option value="">-- Primero elija Producto --</option>';
        selLote.disabled = true;
        ocultarPiezas();

        if (!idFamilia) {
            selProd.innerHTML = '<option value="">-- Primero elija Calidad --</option>';
            return;
        }

        fetch(`{{ url('/inventario/etiquetas/ajax/productos') }}/${idFamilia}`)
            .then(res => res.json())
            .then(data => {
                selProd.innerHTML = '<option value="">-- Seleccione Medida / Producto --</option>';
                data.forEach(p => {
                    const mm = p.milimetros ? `${p.milimetros} mm` : '';
                    const plg = p.pulgadas && p.pulgadas !== '-' ? `${p.pulgadas}"` : '';
                    const sep = (mm && plg) ? ' / ' : '';
                    const medidas = (mm || plg) ? ` (${mm}${sep}${plg})` : '';

                    const opt = document.createElement('option');
                    opt.value = p.id_producto;
                    opt.textContent = `${p.descripcion}${medidas} [${p.codigo}]`;
                    if (preselectProd && String(p.id_producto) === String(preselectProd)) {
                        opt.selected = true;
                    }
                    selProd.appendChild(opt);
                });
                selProd.disabled = false;

                if (preselectProd) {
                    cargarLotes(preselectProd, loteInit);
                }
            })
            .catch(err => {
                console.error(err);
                selProd.innerHTML = '<option value="">Error al cargar productos</option>';
            });
    }

    // 2. CAMBIO DE PRODUCTO
    selProd.addEventListener('change', function() {
        cargarLotes(this.value);
    });

    function cargarLotes(idProducto, preselectLote = null) {
        selLote.innerHTML = '<option value="">⏳ Cargando lotes...</option>';
        selLote.disabled = true;
        ocultarPiezas();

        if (!idProducto) {
            selLote.innerHTML = '<option value="">-- Primero elija Producto --</option>';
            return;
        }

        fetch(`{{ url('/inventario/etiquetas/ajax/lotes') }}/${idProducto}`)
            .then(res => res.json())
            .then(data => {
                selLote.innerHTML = '<option value="">-- Seleccione Lote --</option>';
                data.forEach(l => {
                    const fecha = l.fecha_ingreso ? l.fecha_ingreso.split('T')[0] : '';
                    const opt = document.createElement('option');
                    opt.value = l.id_lote;
                    opt.textContent = `Lote #${l.codigo} | ${parseFloat(l.cantidad_total_metros).toFixed(2)} mts (${l.total_piezas} piezas) [${fecha}]`;
                    if (preselectLote && String(l.id_lote) === String(preselectLote)) {
                        opt.selected = true;
                    }
                    selLote.appendChild(opt);
                });
                selLote.disabled = false;

                if (preselectLote) {
                    cargarPiezas(preselectLote);
                }
            })
            .catch(err => {
                console.error(err);
                selLote.innerHTML = '<option value="">Error al cargar lotes</option>';
            });
    }

    // 3. CAMBIO DE LOTE
    selLote.addEventListener('change', function() {
        cargarPiezas(this.value);
    });

    function cargarPiezas(idLote) {
        if (!idLote) {
            ocultarPiezas();
            return;
        }

        tbody.innerHTML = '<tr><td colspan="7" class="text-center py-4 text-warning"><i class="fa-solid fa-spinner fa-spin me-2"></i> Cargando piezas físicas...</td></tr>';
        seccionPiezas.style.display = 'block';
        pantallaEspera.style.display = 'none';

        const loteTexto = selLote.options[selLote.selectedIndex].text;
        document.getElementById('lblInfoLote').textContent = loteTexto;
        document.getElementById('lblSubInfoLote').textContent = selProd.options[selProd.selectedIndex].text;

        fetch(`{{ url('/inventario/etiquetas/ajax/piezas') }}/${idLote}`)
            .then(res => res.json())
            .then(piezas => {
                tbody.innerHTML = '';
                if (piezas.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="7" class="text-center py-4 text-muted">No hay piezas activas registradas en este lote.</td></tr>';
                    actualizarContador();
                    return;
                }

                piezas.forEach((pz, idx) => {
                    const tr = document.createElement('tr');
                    const mts = parseFloat(pz.cantidad_metros_actual).toFixed(4);
                    const lbs = parseFloat(pz.peso_libras_actual).toFixed(2);
                    const estado = pz.estado || 'ACTIVA';

                    tr.innerHTML = `
                        <td style="text-align: center;">
                            <input type="checkbox" name="piezas[]" value="${pz.id_pieza}" class="check-pieza" checked style="cursor: pointer; width: 16px; height: 16px;">
                        </td>
                        <td style="text-align: center; color: #94a3b8;">${idx + 1}</td>
                        <td class="fw-bold" style="color: #38bdf8;">
                            <i class="fa-solid fa-tag me-1 text-warning"></i> ${pz.codigo}
                        </td>
                        <td style="text-align: right; font-weight: 600; color: #fff;">${mts}</td>
                        <td style="text-align: right; color: #4ade80;">${lbs}</td>
                        <td style="text-align: center;">
                            <span class="badge bg-success">${estado}</span>
                        </td>
                        <td style="text-align: center;">
                            <a href="{{ route('inventario.etiquetas.imprimir') }}?piezas=${pz.id_pieza}&formato=${document.getElementById('formatoImpresion').value}" target="_blank" class="btn btn-sm" style="background-color: #8b5cf6; color: #fff; font-size: 11px; padding: 3px 8px;" title="Imprimir solo esta viñeta">
                                <i class="fa-solid fa-print"></i> Esta pieza
                            </a>
                        </td>
                    `;
                    tbody.appendChild(tr);
                });

                // Check todos activos
                checkTodos.checked = true;
                actualizarContador();
                asignarEventosChecks();
            })
            .catch(err => {
                console.error(err);
                tbody.innerHTML = '<tr><td colspan="7" class="text-center py-4 text-danger">Error al cargar piezas del lote.</td></tr>';
            });
    }

    function ocultarPiezas() {
        seccionPiezas.style.display = 'none';
        pantallaEspera.style.display = 'block';
        tbody.innerHTML = '';
        actualizarContador();
    }

    // CHECKBOXES
    checkTodos.addEventListener('change', function() {
        const checks = document.querySelectorAll('.check-pieza');
        checks.forEach(c => c.checked = checkTodos.checked);
        actualizarContador();
    });

    function asignarEventosChecks() {
        const checks = document.querySelectorAll('.check-pieza');
        checks.forEach(c => {
            c.addEventListener('change', function() {
                const total = checks.length;
                const marcados = document.querySelectorAll('.check-pieza:checked').length;
                checkTodos.checked = (total === marcados);
                actualizarContador();
            });
        });
    }

    function actualizarContador() {
        const marcados = document.querySelectorAll('.check-pieza:checked').length;
        cantBadge.textContent = marcados;
        btnImprimirSel.disabled = (marcados === 0);
    }

    // BOTÓN IMPRIMIR TODO EL LOTE
    btnImprimirTodo.addEventListener('click', function() {
        const idLote = selLote.value;
        const fmt = document.getElementById('formatoImpresion').value;
        if (!idLote) return;
        window.open(`{{ route('inventario.etiquetas.imprimir') }}?id_lote=${idLote}&formato=${fmt}`, '_blank');
    });

    // AUTO-TRIGGER SI VIENEN POR URL
    if (famInit) {
        cargarProductos(famInit, prodInit);
    }
});
</script>
@endsection
