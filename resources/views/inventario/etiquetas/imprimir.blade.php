<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Viñetas de Piezas - {{ $empresa->nombre ?? 'Grupo Acerosal' }}</title>
    
    {{-- FontAwesome para iconos en toolbar --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    {{-- JsBarcode para códigos de barras nítidos en SVG --}}
    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>

    <style>
        /* ========================================================
           ESTILOS GENERALES DE PANTALLA
           ======================================================== */
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            background-color: #0f172a;
            color: #1e293b;
            padding: 20px;
        }

        /* BARRA SUPERIOR DE ACCIONES (PANTALLA) */
        .toolbar {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            background: #1e293b;
            border-bottom: 2px solid #334155;
            padding: 12px 25px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            z-index: 9999;
            box-shadow: 0 4px 15px rgba(0,0,0,0.4);
        }

        .toolbar-info {
            color: #f8fafc;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .toolbar-btn {
            background: #10b981;
            color: #fff;
            border: none;
            padding: 8px 18px;
            border-radius: 6px;
            font-weight: 700;
            font-size: 13px;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: background 0.2s;
        }
        .toolbar-btn:hover {
            background: #059669;
        }

        .toolbar-btn-sec {
            background: #475569;
            color: #fff;
            border: none;
            padding: 8px 14px;
            border-radius: 6px;
            font-size: 13px;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .toolbar-btn-sec:hover {
            background: #334155;
        }

        .content-area {
            margin-top: 60px;
            display: flex;
            justify-content: center;
        }

        /* ========================================================
           CONTENEDOR DE ETIQUETAS (MODO ROLLO / INDIVIDUAL)
           ======================================================== */
        .etiquetas-container.rollo {
            display: flex;
            flex-direction: column;
            gap: 20px;
            align-items: center;
        }

        /* MODO CUADRÍCULA (HOJA CARTA / MÚLTIPLES) */
        .etiquetas-container.cuadricula {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(95mm, 1fr));
            gap: 15px;
            max-width: 220mm;
            margin: 0 auto;
        }

        /* ========================================================
           DISEÑO DE LA VIÑETA / STICKER INDIVIDUAL (100mm x 55mm)
           ======================================================== */
        .etiqueta-card {
            width: 100mm;
            min-height: 55mm;
            max-height: 58mm;
            background: #ffffff;
            color: #000000;
            border: 2px solid #000000;
            border-radius: 4px;
            padding: 3mm 4mm;
            position: relative;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            overflow: hidden;
            box-shadow: 0 4px 10px rgba(0,0,0,0.3);
        }

        /* 1. ENCABEZADO DE LA ETIQUETA */
        .etiqueta-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1.5px solid #000;
            padding-bottom: 1.5mm;
            margin-bottom: 1.5mm;
        }

        .empresa-nombre {
            font-size: 9pt;
            font-weight: 900;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }

        .etiqueta-fecha {
            font-size: 7.5pt;
            font-weight: 700;
            color: #222;
        }

        /* 2. CALIDAD Y MEDIDA (MUY VISIBLE) */
        .calidad-badge-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1mm;
        }

        .calidad-nombre {
            font-size: 11pt;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .calidad-color-dot {
            width: 9px;
            height: 9px;
            border-radius: 50%;
            border: 1px solid #000;
            display: inline-block;
        }

        .lote-badge {
            font-size: 8.5pt;
            font-weight: 800;
            background: #000;
            color: #fff;
            padding: 1px 5px;
            border-radius: 2px;
        }

        .producto-desc {
            font-size: 8.5pt;
            font-weight: 700;
            line-height: 1.15;
            color: #111;
            margin-bottom: 1.5mm;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .dimensiones-destacadas {
            font-size: 9.5pt;
            font-weight: 900;
            color: #000;
            margin-bottom: 1mm;
        }

        /* 3. CÓDIGO DE BARRAS Y CÓDIGO DE PIEZA */
        .barcode-section {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            margin: 0.5mm 0;
        }

        .barcode-svg {
            max-width: 90mm;
            height: 13mm !important;
        }

        .codigo-pieza-texto {
            font-family: 'Courier New', Courier, monospace;
            font-size: 10.5pt;
            font-weight: 900;
            letter-spacing: 1.5px;
            margin-top: -1mm;
            color: #000;
        }

        /* 4. PIE DE ETIQUETA (MEDIDAS FÍSICAS EN BODEGA) */
        .etiqueta-footer {
            border-top: 1.5px solid #000;
            padding-top: 1.5mm;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 8pt;
            font-weight: 800;
        }

        .medida-item {
            display: flex;
            gap: 3px;
        }

        .medida-val {
            font-size: 9pt;
            font-weight: 900;
        }

        .ubicacion-tag {
            font-size: 7pt;
            font-weight: 700;
            background: #e2e8f0;
            padding: 1px 4px;
            border-radius: 2px;
            text-transform: uppercase;
        }

        /* ========================================================
           REGLAS DE IMPRESIÓN (@MEDIA PRINT)
           ======================================================== */
        @media print {
            body {
                background: #ffffff !important;
                padding: 0 !important;
                margin: 0 !important;
            }

            .toolbar {
                display: none !important;
            }

            .content-area {
                margin: 0 !important;
                padding: 0 !important;
                display: block !important;
            }

            /* MODO ROLLO TÉRMICO (1 ETIQUETA POR HOJA / AVANCE) */
            .etiquetas-container.rollo {
                display: block !important;
                gap: 0 !important;
            }

            .etiquetas-container.rollo .etiqueta-card {
                box-shadow: none !important;
                border: 1.5px solid #000 !important;
                margin: 0 auto !important;
                page-break-after: always !important;
                break-after: page !important;
            }

            /* MODO CUADRÍCULA */
            .etiquetas-container.cuadricula {
                display: grid !important;
                grid-template-columns: repeat(2, 98mm) !important;
                gap: 4mm !important;
                margin: 0 !important;
                padding: 3mm !important;
            }

            .etiquetas-container.cuadricula .etiqueta-card {
                box-shadow: none !important;
                border: 1px solid #000 !important;
                page-break-inside: avoid !important;
                break-inside: avoid !important;
            }

            @page {
                size: auto;
                margin: 2mm;
            }
        }
    </style>
</head>
<body>

    {{-- BARRA DE CONTROL (VISIBLE SOLO EN PANTALLA) --}}
    <div class="toolbar">
        <div class="toolbar-info">
            <span style="font-weight: 700; font-size: 15px;">
                <i class="fa-solid fa-tags text-warning me-2"></i> Viñetas de Piezas
            </span>
            <span style="background: #334155; padding: 3px 10px; border-radius: 12px; font-size: 12px;">
                Total: <strong>{{ count($piezas) }}</strong> etiquetas
            </span>
        </div>

        <div style="display: flex; gap: 10px; align-items: center;">
            {{-- SELECTOR DE MODO --}}
            <label style="color: #94a3b8; font-size: 12px; margin-right: 5px;">Vista:</label>
            <select id="cambioVista" onchange="cambiarModoVista(this.value)" style="background: #0f172a; color: #fff; border: 1px solid #475569; border-radius: 5px; padding: 6px 10px; font-size: 12px; cursor: pointer;">
                <option value="rollo" {{ $formato === 'rollo' ? 'selected' : '' }}>🏷️ Rollo / Individual</option>
                <option value="cuadricula" {{ $formato === 'cuadricula' ? 'selected' : '' }}>📄 Cuadrícula (Múltiples)</option>
            </select>

            <button onclick="window.print()" class="toolbar-btn">
                <i class="fa-solid fa-print"></i> Imprimir Viñetas
            </button>

            <button onclick="window.close()" class="toolbar-btn-sec">
                <i class="fa-solid fa-xmark"></i> Cerrar
            </button>
        </div>
    </div>

    {{-- CONTENEDOR DE TODAS LAS ETIQUETAS --}}
    <div class="content-area">
        <div class="etiquetas-container {{ $formato }}" id="contenedorEtiquetas">
            @foreach($piezas as $idx => $p)
                @php
                    $prod = $p->producto;
                    $fam  = $prod->familia ?? null;
                    $lote = $p->lote ?? null;
                    $mm   = floatval($prod->milimetros ?? 0);
                    $plg  = $prod->pulgadas ?? '';

                    $txtMM  = $mm > 0 ? "$mm mm" : '';
                    $txtPLG = ($plg && $plg != '-') ? "$plg\"" : '';
                    $sep    = ($txtMM && $txtPLG) ? ' / ' : '';
                    $textoDimension = ($txtMM || $txtPLG) ? "($txtMM$sep$txtPLG)" : '';

                    $fechaIngreso = $lote && $lote->fecha_ingreso ? \Carbon\Carbon::parse($lote->fecha_ingreso)->format('d/m/Y') : date('d/m/Y');
                    $barcodeId = 'barcode_' . $p->id_pieza . '_' . $idx;
                @endphp

                <div class="etiqueta-card">
                    
                    {{-- 1. ENCABEZADO --}}
                    <div class="etiqueta-header">
                        <span class="empresa-nombre">{{ $empresa->nombre ?? 'GRUPO ACEROSAL' }}</span>
                        <span class="etiqueta-fecha"><i class="fa-regular fa-calendar"></i> {{ $fechaIngreso }}</span>
                    </div>

                    {{-- 2. CALIDAD & LOTE --}}
                    <div class="calidad-badge-row">
                        <span class="calidad-nombre">
                            @if($fam && $fam->detalle_color)
                                <span class="calidad-color-dot" style="background-color: {{ $fam->detalle_color }};"></span>
                            @endif
                            {{ $fam->nombre ?? 'CALIDAD ACERO' }}
                        </span>
                        <span class="lote-badge">LOTE: {{ $lote->codigo ?? 'S/L' }}</span>
                    </div>

                    {{-- 3. PRODUCTO Y DIMENSIONES --}}
                    <div class="producto-desc" title="{{ $prod->descripcion ?? '' }}">
                        {{ $prod->descripcion ?? '-' }}
                    </div>

                    @if($textoDimension)
                        <div class="dimensiones-destacadas">
                            MEDIDA: {{ $textoDimension }}
                        </div>
                    @endif

                    {{-- 4. CÓDIGO DE BARRAS & CÓDIGO DE PIEZA --}}
                    <div class="barcode-section">
                        <svg id="{{ $barcodeId }}" class="barcode-svg"></svg>
                        <span class="codigo-pieza-texto">{{ $p->codigo }}</span>
                    </div>

                    {{-- 5. MEDIDAS FÍSICAS ACTUALES --}}
                    <div class="etiqueta-footer">
                        <div class="medida-item">
                            <span>LONG:</span>
                            <span class="medida-val">{{ number_format($p->cantidad_metros_actual, 4) }} m</span>
                        </div>

                        <div class="medida-item">
                            <span>PESO:</span>
                            <span class="medida-val">{{ number_format($p->peso_libras_actual, 2) }} lb</span>
                        </div>

                        @if($prod && $prod->ubicacion)
                            <span class="ubicacion-tag">
                                {{ $prod->ubicacion->nombre }}
                            </span>
                        @endif
                    </div>

                </div>
            @endforeach
        </div>
    </div>

    {{-- GENERACIÓN DE CÓDIGOS DE BARRAS --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            @foreach($piezas as $idx => $p)
                try {
                    JsBarcode("#barcode_{{ $p->id_pieza }}_{{ $idx }}", "{{ $p->codigo }}", {
                        format: "CODE128",
                        lineColor: "#000000",
                        width: 1.5,
                        height: 38,
                        displayValue: false,
                        margin: 0
                    });
                } catch(e) {
                    console.error("Error barcode pieza {{ $p->codigo }}", e);
                }
            @endforeach
        });

        function cambiarModoVista(modo) {
            const cont = document.getElementById('contenedorEtiquetas');
            cont.className = 'etiquetas-container ' + modo;
        }
    </script>

</body>
</html>
