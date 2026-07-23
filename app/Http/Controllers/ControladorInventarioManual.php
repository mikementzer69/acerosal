<?php
require_once __DIR__ . '/../Modelos/ModeloInventario.php';
$modeloInv = new ModeloInventario();

// ============================================================
// 1️⃣ VALIDAR PETICIONES AJAX O ACCIONES ESPECÍFICAS
// ============================================================

if (isset($_GET['accion'])) {
    switch ($_GET['accion']) {

        // ------------------------------------------------------------
        // 1️⃣ OBTENER FAMILIAS ACTIVAS
        // ------------------------------------------------------------
        case 'obtenerFamilias':
            $familias = $modeloInv->obtenerFamiliasActivas();
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode($familias);
            exit;

        // ------------------------------------------------------------
        // 2️⃣ OBTENER PRODUCTOS POR FAMILIA
        // ------------------------------------------------------------
        case 'obtenerProductosPorFamilia':
            $idFam = intval($_GET['id']);
            $productos = $modeloInv->obtenerProductosPorFamilia($idFam);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode($productos);
            exit;

        // ------------------------------------------------------------
        // 3️⃣ VALIDAR CÓDIGO DE LOTE ÚNICO
        // ------------------------------------------------------------
        case 'validarCodigoLote':
            $idProd = intval($_GET['idProducto']);
            $codigo = trim($_GET['codigo']);
            $existe = $modeloInv->existeCodigoLote($idProd, $codigo);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['existe' => $existe]);
            exit;

        // ------------------------------------------------------------
        // 4️⃣ GUARDAR LOTE MANUAL (Lote + Piezas)
        // ------------------------------------------------------------
        case 'guardarManual':
            $data = $_POST;

            // 🧩 Validación: código de lote no vacío
            if (empty($data['codigoLote'])) {
                echo "<script>alert('⚠️ El código de lote no puede estar vacío'); history.back();</script>";
                exit;
            }

            // 🧩 Validación: código de lote único por producto
            if ($modeloInv->existeCodigoLote($data['idProducto'], $data['codigoLote'])) {
                echo "<script>alert('⚠️ El código de lote ya existe para este producto'); history.back();</script>";
                exit;
            }

            // 🧱 Insertar Lote Manual
            $idLote = $modeloInv->insertarLoteManual($data);
            if (!$idLote) {
                echo "<script>alert('❌ Error al guardar el lote'); history.back();</script>";
                exit;
            }

            // 🧩 Insertar las piezas asociadas
            if (!empty($_POST['codigoPieza'])) {
                $total = count($_POST['codigoPieza']);
                for ($i = 0; $i < $total; $i++) {
                    $modeloInv->insertarPiezaManual([
                        'Id_Productos' => $data['idProducto'],
                        'Id_Lotes' => $idLote,
                        'Codigo' => $_POST['codigoPieza'][$i],
                        'Peso_Libras_Inicial' => $_POST['librasInicial'][$i],
                        'Cantidad_Metros_Inicial' => $_POST['metrosInicial'][$i],
                    ]);
                }
            }

            echo "<script>alert('✅ Lote manual guardado correctamente'); window.location='index.php?pagina=listaLotes';</script>";
            exit;
    }
}

// ============================================================
// 2️⃣ MOSTRAR FORMULARIO DE INVENTARIO MANUAL (POR DEFECTO)
// ============================================================

require_once __DIR__ . '/../Formularios/formularioInventarioManual.php';
