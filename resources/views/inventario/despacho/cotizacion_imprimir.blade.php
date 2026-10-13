<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cotización {{ $orden->numero_orden }} - Acerosal</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #1e3a8a;
            --primary-dark: #0f172a;
            --accent: #f59e0b;
            --text-dark: #0f172a;
            --text-muted: #64748b;
            --border: #e2e8f0;
            --bg-light: #f8fafc;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
        }

        body {
            background-color: #f1f5f9;
            color: var(--text-dark);
            font-size: 13px;
            line-height: 1.5;
        }

        /* BARRA SUPERIOR DE ACCIONES (SOLO PANTALLA) */
        .actions-bar {
            position: sticky;
            top: 0;
            z-index: 100;
            background: #0f172a;
            color: white;
            padding: 12px 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }

        .actions-bar .title {
            font-size: 15px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .actions-bar .buttons {
            display: flex;
            gap: 10px;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 18px;
            font-size: 13px;
            font-weight: 600;
            border-radius: 6px;
            border: none;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.2s;
        }

        .btn-print {
            background-color: #10b981;
            color: white;
        }
        .btn-print:hover { background-color: #059669; }

        .btn-close {
            background-color: #475569;
            color: white;
        }
        .btn-close:hover { background-color: #334155; }

        /* HOJA DE COTIZACIÓN */
        .page-container {
            max-width: 900px;
            margin: 25px auto;
            background: white;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05);
            border: 1px solid var(--border);
        }

        /* HEADER DEL DOCUMENTO */
        .doc-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            padding-bottom: 24px;
            border-bottom: 2px solid var(--primary);
            margin-bottom: 24px;
        }

        .company-info {
            flex: 1;
        }

        .company-brand {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 6px;
        }

        .company-logo-text {
            font-size: 26px;
            font-weight: 900;
            letter-spacing: -0.5px;
            color: var(--primary);
            text-transform: uppercase;
        }

        .company-tagline {
            font-size: 11px;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 1px;
            font-weight: 600;
        }

        .company-details {
            font-size: 11px;
            color: var(--text-muted);
            line-height: 1.6;
            margin-top: 6px;
        }

        .quote-box {
            text-align: right;
            min-width: 260px;
        }

        .quote-title {
            font-size: 24px;
            font-weight: 900;
            color: var(--primary-dark);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .quote-number {
            font-size: 16px;
            font-weight: 700;
            color: #d97706;
            margin-top: 2px;
        }

        .quote-meta {
            margin-top: 10px;
            font-size: 12px;
            color: var(--text-dark);
            background: var(--bg-light);
            padding: 8px 12px;
            border-radius: 6px;
            border: 1px solid var(--border);
            display: inline-block;
            text-align: right;
        }

        .quote-meta div {
            margin-bottom: 3px;
        }

        /* TARJETAS DE CLIENTE Y VENDEDOR */
        .info-grid {
            display: grid;
            grid-template-columns: 1.4fr 1fr;
            gap: 20px;
            margin-bottom: 25px;
        }

        .info-card {
            background: var(--bg-light);
            border: 1px solid var(--border);
            border-radius: 6px;
            padding: 14px 16px;
        }

        .info-card-title {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: var(--primary);
            border-bottom: 1px solid var(--border);
            padding-bottom: 6px;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .info-row {
            display: flex;
            font-size: 12px;
            margin-bottom: 5px;
        }

        .info-row .label {
            width: 100px;
            color: var(--text-muted);
            font-weight: 600;
            flex-shrink: 0;
        }

        .info-row .value {
            color: var(--text-dark);
            font-weight: 500;
            word-break: break-word;
        }

        /* TABLA DE PRODUCTOS */
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            font-size: 12px;
        }

        .items-table th {
            background-color: var(--primary);
            color: white;
            font-weight: 700;
            text-transform: uppercase;
            font-size: 11px;
            letter-spacing: 0.5px;
            padding: 10px 8px;
            border: 1px solid var(--primary);
        }

        .items-table td {
            padding: 9px 8px;
            border: 1px solid var(--border);
            vertical-align: middle;
        }

        .items-table tbody tr:nth-child(even) {
            background-color: #fafbfd;
        }

        .items-table .text-center { text-align: center; }
        .items-table .text-end { text-align: right; }
        .items-table .text-start { text-align: left; }

        .prod-title {
            font-weight: 700;
            color: var(--primary-dark);
            font-size: 12px;
        }

        .prod-specs {
            color: #d97706;
            font-size: 11px;
            font-weight: 600;
        }

        .prod-sub {
            font-size: 11px;
            color: var(--text-muted);
            margin-top: 2px;
        }

        /* RESUMEN Y TOTALES */
        .summary-grid {
            display: grid;
            grid-template-columns: 1.2fr 1fr;
            gap: 25px;
            margin-top: 15px;
        }

        .terms-box {
            font-size: 11px;
            color: var(--text-muted);
            line-height: 1.6;
            background: #fffdf5;
            border: 1px solid #fef3c7;
            border-radius: 6px;
            padding: 14px 16px;
        }

        .terms-box h6 {
            color: #92400e;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            margin-bottom: 6px;
        }

        .terms-box ul {
            padding-left: 18px;
        }

        .totals-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
        }

        .totals-table td {
            padding: 8px 12px;
            border-bottom: 1px solid var(--border);
        }

        .totals-table .label {
            color: var(--text-muted);
            font-weight: 600;
            text-align: right;
        }

        .totals-table .value {
            text-align: right;
            font-weight: 700;
            color: var(--text-dark);
            width: 130px;
        }

        .totals-table .grand-total td {
            background-color: #f8fafc;
            border-top: 2px solid var(--primary);
            border-bottom: 2px solid var(--primary);
            font-size: 16px;
            font-weight: 900;
            color: var(--primary);
            padding: 12px;
        }

        /* SECCIÓN DE FIRMAS */
        .signatures {
            margin-top: 50px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 60px;
            text-align: center;
        }

        .sign-line {
            border-top: 1px solid #94a3b8;
            padding-top: 8px;
            font-size: 11px;
            font-weight: 600;
            color: var(--text-muted);
            text-transform: uppercase;
        }

        /* PIE DE PÁGINA */
        .doc-footer {
            margin-top: 35px;
            padding-top: 15px;
            border-top: 1px solid var(--border);
            text-align: center;
            font-size: 10px;
            color: var(--text-muted);
        }

        /* REGLAS DE IMPRESIÓN */
        @media print {
            body {
                background: white !important;
                color: black !important;
                font-size: 11px;
            }

            .actions-bar {
                display: none !important;
            }

            .page-container {
                max-width: 100% !important;
                margin: 0 !important;
                padding: 0 !important;
                border: none !important;
                box-shadow: none !important;
                border-radius: 0 !important;
            }

            .items-table th {
                background-color: #f1f5f9 !important;
                color: #0f172a !important;
                border: 1px solid #cbd5e1 !important;
            }

            .items-table td {
                border: 1px solid #cbd5e1 !important;
            }

            .items-table tbody tr:nth-child(even) {
                background-color: transparent !important;
            }

            .totals-table .grand-total td {
                background-color: #f1f5f9 !important;
                color: #000 !important;
            }

            .terms-box {
                background: transparent !important;
                border: 1px solid #cbd5e1 !important;
                color: #334155 !important;
            }

            @page {
                size: letter;
                margin: 1.2cm;
            }
        }
    </style>
</head>
<body>

    {{-- BARRA SUPERIOR (SOLO PANTALLA) --}}
    <div class="actions-bar">
        <div class="title">
            <i class="fa-solid fa-file-invoice-dollar" style="color: #f59e0b;"></i>
            <span>Cotización Valorizada #{{ $orden->numero_orden }}</span>
        </div>
        <div class="buttons">
            <button onclick="window.print()" class="btn btn-print">
                <i class="fa-solid fa-print"></i> Imprimir Cotización
            </button>
            <button onclick="window.close()" class="btn btn-close">
                <i class="fa-solid fa-xmark"></i> Cerrar
            </button>
        </div>
    </div>

    {{-- CONTENEDOR DE LA PÁGINA IMPRIMIBLE --}}
    <div class="page-container">

        {{-- ENCABEZADO --}}
        <div class="doc-header">
            <div class="company-info">
                <div class="company-brand">
                    <span class="company-logo-text">{{ $empresa->nombre ?? 'ACEROSAL' }}</span>
                </div>
                <div class="company-tagline">Distribución y Corte de Aceros Especiales</div>
                <div class="company-details">
                    <strong>{{ $empresa->razon_social ?? 'ACEROSAL S.A. DE C.V.' }}</strong><br>
                    @if(!empty($empresa->nit)) NIT: {{ $empresa->nit }} &nbsp;|&nbsp; @endif
                    @if(!empty($empresa->nrc)) NRC: {{ $empresa->nrc }}<br>@endif
                    @if(!empty($empresa->direccion)) {{ $empresa->direccion }}<br>@endif
                    @if(!empty($empresa->telefono)) Tel: {{ $empresa->telefono }} &nbsp;|&nbsp; @endif
                    @if(!empty($empresa->correo_contacto)) Correo: {{ $empresa->correo_contacto }} @endif
                </div>
            </div>

            <div class="quote-box">
                <div class="quote-title">COTIZACIÓN</div>
                <div class="quote-number"># {{ $orden->numero_orden }}</div>
                <div class="quote-meta">
                    <div><strong>Fecha:</strong> {{ \Carbon\Carbon::parse($orden->fecha)->format('d/m/Y') }}</div>
                    <div><strong>Vigencia:</strong> 15 Días</div>
                    <div><strong>Moneda:</strong> Dólares (USD $)</div>
                </div>
            </div>
        </div>

        {{-- DATOS DEL CLIENTE Y ASESOR --}}
        <div class="info-grid">
            <div class="info-card">
                <div class="info-card-title">
                    <i class="fa-solid fa-user"></i> Datos del Cliente
                </div>
                <div class="info-row">
                    <span class="label">Cliente:</span>
                    <span class="value"><strong>{{ $orden->cliente->nombre ?? 'Consumidor Final' }}</strong></span>
                </div>
                @if(!empty($orden->cliente->nombre_comercial))
                    <div class="info-row">
                        <span class="label">Comercial:</span>
                        <span class="value">{{ $orden->cliente->nombre_comercial }}</span>
                    </div>
                @endif
                @if(!empty($orden->cliente->documento) || !empty($orden->cliente->nit))
                    <div class="info-row">
                        <span class="label">Doc / NIT:</span>
                        <span class="value">{{ $orden->cliente->documento ?? $orden->cliente->nit }}</span>
                    </div>
                @endif
                @if(!empty($orden->cliente->telefono))
                    <div class="info-row">
                        <span class="label">Teléfono:</span>
                        <span class="value">{{ $orden->cliente->telefono }}</span>
                    </div>
                @endif
                @if(!empty($orden->cliente->correo))
                    <div class="info-row">
                        <span class="label">Correo:</span>
                        <span class="value">{{ $orden->cliente->correo }}</span>
                    </div>
                @endif
                @if(!empty($orden->cliente->direccion))
                    <div class="info-row">
                        <span class="label">Dirección:</span>
                        <span class="value">{{ $orden->cliente->direccion }}</span>
                    </div>
                @endif
            </div>

            <div class="info-card">
                <div class="info-card-title">
                    <i class="fa-solid fa-handshake"></i> Datos de la Oferta
                </div>
                <div class="info-row">
                    <span class="label">Asesor Comercial:</span>
                    <span class="value">{{ $orden->vendedor->nombre ?? 'Ventas' }} {{ $orden->vendedor->apellidos ?? '' }}</span>
                </div>
                <div class="info-row">
                    <span class="label">Tipo de Precio:</span>
                    <span class="value">
                        @if($orden->motivo_precio == 1)
                            Especial (Autorizado)
                        @elseif($orden->motivo_precio == 2)
                            Especial (Competencia)
                        @else
                            Precio de Lista
                        @endif
                    </span>
                </div>
                <div class="info-row">
                    <span class="label">Condición:</span>
                    <span class="value">Precios sin IVA incluido</span>
                </div>
                <div class="info-row">
                    <span class="label">Estado:</span>
                    <span class="value"><strong>{{ $orden->estado }}</strong></span>
                </div>
            </div>
        </div>

        {{-- TABLA DE PRODUCTOS / ÍTEMS --}}
        <table class="items-table">
            <thead>
                <tr>
                    <th style="width: 35px;" class="text-center">#</th>
                    <th style="width: 110px;" class="text-start">Calidad</th>
                    <th class="text-start">Descripción / Dimensiones</th>
                    <th style="width: 100px;" class="text-center">Medida Sol.</th>
                    <th style="width: 75px;" class="text-end">Metros</th>
                    <th style="width: 85px;" class="text-end">Peso (lb)</th>
                    <th style="width: 90px;" class="text-end">Precio ($/lb)</th>
                    <th style="width: 100px;" class="text-end">Subtotal ($)</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $totalMetros = 0;
                    $totalLibras = 0;
                    $totalSubtotal = 0;
                @endphp

                @foreach($orden->detalles as $idx => $d)
                    @php
                        $metros = floatval($d->cantidad_metros);
                        $libras = floatval($d->cantidad_libras);
                        $precioSinIva = floatval($d->precio_venta_sin_iva);
                        // Fórmula confirmada por el usuario: Subtotal = Libras * Precio de Venta
                        $subtotalFila = $libras * $precioSinIva;

                        $totalMetros += $metros;
                        $totalLibras += $libras;
                        $totalSubtotal += $subtotalFila;

                        $mm = floatval($d->producto->milimetros ?? 0);
                        $plg = $d->producto->pulgadas ?? '-';
                    @endphp
                    <tr>
                        <td class="text-center">{{ $idx + 1 }}</td>
                        <td>
                            <strong>{{ $d->familia->nombre ?? 'N/A' }}</strong>
                        </td>
                        <td>
                            <div class="prod-title">{{ $d->producto->descripcion ?? 'Producto' }}</div>
                            @if($mm > 0 || ($plg && $plg != '-'))
                                <span class="prod-specs">
                                    ({{ $mm > 0 ? $mm.' mm' : '' }}
                                    {{ ($mm > 0 && $plg != '-') ? ' / ' : '' }}
                                    {{ $plg != '-' ? $plg.' plg' : '' }})
                                </span>
                            @endif
                            @if(!empty($d->lote->codigo) || !empty($d->pieza->codigo))
                                <div class="prod-sub">
                                    Pieza: {{ $d->pieza->codigo ?? '-' }} | Lote: {{ $d->lote->codigo ?? '-' }}
                                </div>
                            @endif
                        </td>
                        <td class="text-center">
                            {{ $d->medida_solicitada ? $d->medida_solicitada : number_format($metros, 2).' m' }}
                        </td>
                        <td class="text-end fw-bold">{{ number_format($metros, 4) }}</td>
                        <td class="text-end fw-bold">{{ number_format($libras, 2) }}</td>
                        <td class="text-end">${{ number_format($precioSinIva, 2) }}</td>
                        <td class="text-end"><strong>${{ number_format($subtotalFila, 2) }}</strong></td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        {{-- RESUMEN Y TOTALES --}}
        @php
            $iva = $totalSubtotal * 0.13;
            $granTotal = $totalSubtotal + $iva;
        @endphp

        <div class="summary-grid">
            <div class="terms-box">
                <h6><i class="fa-solid fa-circle-info me-1"></i> Términos y Condiciones Comerciales</h6>
                <ul>
                    <li>Precios expresados en <strong>Dólares de los Estados Unidos de América (USD)</strong>.</li>
                    <li>Validez de la oferta: <strong>15 días calendario</strong> a partir de la fecha de emisión.</li>
                    <li>Disponibilidad de material y retazos sujeta a confirmación al momento de la orden.</li>
                    @if(!empty($orden->observaciones))
                        <li style="margin-top: 6px;"><strong>Observaciones:</strong> {{ $orden->observaciones }}</li>
                    @endif
                </ul>
            </div>

            <div>
                <table class="totals-table">
                    <tr>
                        <td class="label">Total Metros:</td>
                        <td class="value">{{ number_format($totalMetros, 4) }} m</td>
                    </tr>
                    <tr>
                        <td class="label">Total Peso (Libras):</td>
                        <td class="value">{{ number_format($totalLibras, 2) }} lb</td>
                    </tr>
                    <tr>
                        <td class="label">Subtotal (Sin IVA):</td>
                        <td class="value">${{ number_format($totalSubtotal, 2) }}</td>
                    </tr>
                    <tr>
                        <td class="label">IVA (13%):</td>
                        <td class="value">${{ number_format($iva, 2) }}</td>
                    </tr>
                    <tr class="grand-total">
                        <td class="label" style="text-align: right;">TOTAL COTIZADO:</td>
                        <td class="value">${{ number_format($granTotal, 2) }}</td>
                    </tr>
                </table>
            </div>
        </div>

        {{-- FIRMAS --}}
        <div class="signatures">
            <div>
                <div class="sign-line">
                    {{ $orden->vendedor->nombre ?? 'Asesor Comercial' }} {{ $orden->vendedor->apellidos ?? '' }}<br>
                    <span style="font-size: 9px; font-weight: normal; text-transform: none; color: #64748b;">Elaborado por (Acerosal)</span>
                </div>
            </div>
            <div>
                <div class="sign-line">
                    Aceptado por el Cliente<br>
                    <span style="font-size: 9px; font-weight: normal; text-transform: none; color: #64748b;">Firma y Sello de Conformidad</span>
                </div>
            </div>
        </div>

        {{-- PIE DE PÁGINA --}}
        <div class="doc-footer">
            Documento generado electrónicamente el {{ now()->format('d/m/Y H:i') }} — Acerosal ERP
        </div>

    </div>

</body>
</html>
