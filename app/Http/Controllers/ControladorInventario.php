<?php
require_once __DIR__ . '/../Modelos/ModeloInventario.php';
$modeloInv = new ModeloInventario();

// ============================================================
// 1️⃣ OBTENER DETALLE DE COMPRA (AJAX)
// ============================================================
if (isset($_GET['accion']) && $_GET['accion'] === 'obtenerDetalleCompra') {
    $idCompra = intval($_GET['id']);
    if (!$idCompra) {
        echo json_encode(['error' => 'ID de compra no válido']);
        exit;
    }

    $compra = $modeloInv->obtenerCompraPorId($idCompra);
    $detalle = $modeloInv->obtenerDetalleCompra($idCompra);

    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['compra' => $compra, 'detalle' => $detalle]);
    exit;
}

// ============================================================
// 2️⃣ VALIDACIÓN AJAX DE CÓDIGO DE LOTE
// ============================================================
if (isset($_GET['accion']) && $_GET['accion'] === 'verificarCodigoLote') {
    $idProd = intval($_GET['idProducto']);
    $codigo = trim($_GET['codigo'] ?? '');

    if (!$idProd || $codigo === '') {
        echo json_encode(['existe' => false]);
        exit;
    }

    $existe = $modeloInv->existeCodigoLote($idProd, $codigo);
    echo json_encode(['existe' => $existe]);
    exit;
}

// ============================================================
// 3️⃣ GUARDAR INVENTARIO (POST)
// ============================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json; charset=utf-8');

    try {
        $idCompra = intval($_POST['idCompra'] ?? 0);
        $lotes = json_decode($_POST['lotes'] ?? '[]', true);
        $piezas = json_decode($_POST['piezas'] ?? '[]', true);

        // --------------------------------------------
        // 🔸 Validaciones básicas
        // --------------------------------------------
        if ($idCompra <= 0) {
            echo json_encode(['success' => false, 'message' => '❌ Falta el ID de la compra.']);
            exit;
        }
        if (empty($lotes)) {
            echo json_encode(['success' => false, 'message' => '❌ No se recibieron lotes.']);
            exit;
        }

        // --------------------------------------------
        // 🔸 Recorrer cada lote y procesarlo
        // --------------------------------------------
        foreach ($lotes as $lote) {
            $idProd = intval($lote['Id_Productos']);
            $codigoLote = trim($lote['Codigo']);

            // ✅ Validar que el código no exista ya para ese producto
            if ($modeloInv->existeCodigoLote($idProd, $codigoLote)) {
                echo json_encode([
                    'success' => false,
                    'message' => "⚠️ Ya existe un lote con el código '{$codigoLote}' para este producto."
                ]);
                exit;
            }

            // ✅ Insertar lote
            $idLote = $modeloInv->insertarLote([
                'Id_Productos' => $idProd,
                'Codigo' => $codigoLote,
                'Fecha_Ingreso' => $lote['Fecha_Ingreso'],
                'Peso_Total_Libras' => $lote['Peso_Total_Libras'],
                'Cantidad_Total_Metros' => $lote['Cantidad_Total_Metros'],
                'Relacion_Cantidad_Peso' => $lote['Relacion_Cantidad_Peso'],
                'Total_Piezas' => $lote['Total_Piezas']
            ]);

            if ($idLote <= 0) {
                echo json_encode(['success' => false, 'message' => "❌ Error al insertar el lote '{$codigoLote}'"]);
                exit;
            }

            // ✅ Insertar piezas del lote
            if (!empty($piezas[$idProd])) {
                foreach ($piezas[$idProd] as $pieza) {
                    $ok = $modeloInv->insertarPieza($pieza, $idProd, $idLote);
                    if (!$ok) {
                        echo json_encode(['success' => false, 'message' => "❌ Error al insertar pieza en el lote '{$codigoLote}'"]);
                        exit;
                    }
                }
            }
        }

        // ✅ Marcar compra procesada
        $modeloInv->marcarCompraProcesada($idCompra);

        echo json_encode(['success' => true, 'message' => '✅ Inventario guardado correctamente.']);

    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => '❌ Error inesperado: ' . $e->getMessage()]);
    }
    exit;
}

// ============================================================
// 4️⃣ MOSTRAR FORMULARIO DE INVENTARIO (si no hay GET ni POST)
// ============================================================
$comprasNuevas = $modeloInv->obtenerComprasNuevas();
require_once __DIR__ . '/../Formularios/formularioInventario.php';
