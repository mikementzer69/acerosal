<?php
    
    if (isset($_GET['ping'])) {
  header('Content-Type: application/json; charset=utf-8');
  echo json_encode(['ok'=>true,'here'=>'ControladorCompras.php']);
  exit;
}
    
require_once __DIR__ . '/../Modelos/ModeloCompras.php';
$modelo = new ModeloCompras();

header('Content-Type: application/json; charset=utf-8');
error_reporting(E_ALL);
ini_set('display_errors', 1);

$resultado = [
    'ok' => false,
    'idCompra' => null,
    'mensajes' => []
];

// ------------------------------------------------------------
// 🔹 PETICIONES GET (AJAX)
// ------------------------------------------------------------
if (isset($_GET['accion'])) {
    switch ($_GET['accion']) {

        case 'productosFamilia':
            $idFamilia = intval($_GET['idFamilia'] ?? 0);
            echo json_encode($modelo->obtenerProductosPorFamilia($idFamilia));
            exit;

        case 'costosActivos':
            echo json_encode($modelo->obtenerCostosActivos());
            exit;
    }
}

// ------------------------------------------------------------
// 💾 GUARDAR COMPRA COMPLETA (POST)
// ------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $accion = $_POST['accion'] ?? 'guardarCompra';
    if ($accion !== 'guardarCompra') {
        echo json_encode(['error' => 'Acción no válida']);
        exit;
    }

    // 🧱 Datos principales
$dataCompra = [
    'id_Empresa'               => intval($_POST['id_Empresa'] ?? 0),
    'Id_Proveedores'           => intval($_POST['id_Proveedores'] ?? 0),
    'Numero_Factura'           => $_POST['Numero_Factura'] ?? '',
    'Fecha_EmisionF'           => $_POST['Fecha_EmisionF'] ?? null,
    'Fecha_Ingreso'            => $_POST['Fecha_Ingreso'] ?? null,
    'Peso_Total_Libras'        => floatval($_POST['Peso_Total_Libras'] ?? 0),
    'Peso_Total_KG'            => floatval($_POST['Peso_Total_KG'] ?? 0),
    'Total_Costos_Adicionales' => floatval($_POST['Total_Costos_Adicionales'] ?? 0),
    'Costos_Adicionales_Libra' => floatval($_POST['Costos_Adicionales_Libra'] ?? 0), // ✅ agregado
    'Importe_Total_Factura'    => floatval($_POST['Importe_Total_Factura'] ?? 0),
    'Total_Factura'            => floatval($_POST['Total_Factura'] ?? 0)
];

    $idCompra = $modelo->insertarCompra($dataCompra);
    if (!$idCompra) {
        $resultado['mensajes'][] = "❌ Error al insertar la compra principal";
        echo json_encode($resultado);
        exit;
    }

    $resultado['idCompra'] = $idCompra;
    $resultado['mensajes'][] = "✅ Compra principal insertada correctamente (ID: $idCompra)";

    // ------------------------------------------------------------
    // 💲 COSTOS ADICIONALES
    // ------------------------------------------------------------
if (!empty($_POST['id_Costos'])) {
    foreach ($_POST['id_Costos'] as $i => $idCosto) {
        if (!$idCosto) continue;
        $modelo->insertarCostoCompra([
            'id_Compras' => $idCompra,            // ✅ plural
            'id_Costos'  => intval($idCosto),     // ✅ plural
            'Valor_USD'  => floatval($_POST['Valor_USD'][$i] ?? 0),
            'Valor_EU'   => floatval($_POST['Valor_EU'][$i] ?? 0)
        ]);
    }
}

    // ------------------------------------------------------------
    // 📦 DETALLE DE PRODUCTOS
    // ------------------------------------------------------------
if (!empty($_POST['id_Producto'])) {
    foreach ($_POST['id_Producto'] as $i => $idProducto) {
        if (!$idProducto) continue;
        $modelo->insertarDetalleCompra([
            'Id_Compras'      => $idCompra,
            'id_Producto'     => intval($idProducto),
            'Cantidad'        => floatval($_POST['Cantidad'][$i] ?? 0),
            'Precio_KG_EU'    => floatval($_POST['Precio_KG_EU'][$i] ?? 0),
            'Precio_KG_USD'   => floatval($_POST['Precio_KG_USD'][$i] ?? 0),
            'Peso_KG'         => floatval($_POST['Peso_KG'][$i] ?? 0),
            'Peso_Libra'      => floatval($_POST['Peso_Libra'][$i] ?? 0),
            'Importe_EU'      => floatval($_POST['Importe_EU'][$i] ?? 0),
            'Importe_dolares' => floatval($_POST['Importe_Dolares'][$i] ?? 0) 
        ]);
    }
}

    // ------------------------------------------------------------
    // 🧩 DETALLE DE FAMILIAS
    // ------------------------------------------------------------
    if (!empty($_POST['id_Familias'])) {
        foreach ($_POST['id_Familias'] as $i => $idFamilia) {
            if (!$idFamilia) continue;
            $ok = $modelo->insertarDetalleFamilia([
                'id_Familias'            => intval($idFamilia),
                'Id_Compras'             => $idCompra,
                'Cantidad_Total'         => floatval($_POST['Cantidad_Total'][$i] ?? 0),
                'Peso_Total_KG'          => floatval($_POST['Peso_Total_KG'][$i] ?? 0),
                'Peso_Total_Libras'      => floatval($_POST['Peso_Total_Libras'][$i] ?? 0),
                'Importe_Total_EU'       => floatval($_POST['Importe_Total_EU'][$i] ?? 0),
                'Importe_Total_Dolares'  => floatval($_POST['Importe_Total_Dolares'][$i] ?? 0),
                'Precio_CIF'             => floatval($_POST['Precio_CIF'][$i] ?? 0),
                'Precio_Unitario_Bodega' => floatval($_POST['Precio_Unitario_Bodega'][$i] ?? 0),
                'Total_Familia'          => floatval($_POST['Total_Familia'][$i] ?? 0)
            ]);
            $resultado['mensajes'][] = $ok
                ? "✅ Detalle familia #$i insertado correctamente"
                : "❌ Error al insertar detalle familia #$i";
        }
    }

    // ------------------------------------------------------------
    $resultado['ok'] = true;
    echo json_encode($resultado, JSON_UNESCAPED_UNICODE);
    exit;
}

// ------------------------------------------------------------
echo json_encode(['error' => 'Acción no válida o sin parámetros.']);
exit;
