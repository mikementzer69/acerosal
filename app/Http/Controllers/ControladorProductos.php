<?php
session_start();
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../conexion.php';
require_once __DIR__ . '/../Modelos/ModeloProductos.php';

$cn = conectar();
$modelo = new ModeloProductos($cn);

function go($relativePath) {
  header("Location: " . BASE_URL . ltrim($relativePath, '/'));
  exit;
}

$accion = $_GET['accion'] ?? '';

switch ($accion) {

  case 'insertar':
    $d = [
      'id_Familia'            => (int)($_POST['id_Familia'] ?? 0),
      'Codigo'                => trim($_POST['Codigo'] ?? ''),
      'Descripcion'           => trim($_POST['Descripcion'] ?? ''),
      'Unidad_Medida'         => trim($_POST['Unidad_Medida'] ?? ''),
      'Milimetros'            => ($_POST['Milimetros'] ?? '')==='' ? null : (float)$_POST['Milimetros'],
      'Pulgadas'              => ($_POST['Pulgadas'] ?? '')==='' ? null : (float)$_POST['Pulgadas'],
      'Tolerancia'            => ($_POST['Tolerancia'] ?? '')==='' ? null : (float)$_POST['Tolerancia'],
      'Peso_LB_MTS'           => ($_POST['Peso_LB_MTS'] ?? '')==='' ? null : (float)$_POST['Peso_LB_MTS'],
      'Precio_Venta_sin_IVA'  => ($_POST['Precio_Venta_sin_IVA'] ?? '')==='' ? null : (float)$_POST['Precio_Venta_sin_IVA'],
      'Precio_Fijo'           => isset($_POST['Precio_Fijo']) ? 1 : 0,
    ];
    if ($d['id_Familia']<=0 || $d['Descripcion']==='') {
      $_SESSION['msg'] = "⚠️ Familia y Descripción son obligatorios.";
      go("index.php?pagina=formularioNuevoProducto");
    }
    $_SESSION['msg'] = $modelo->insertar($d) ? "✅ Producto creado." : "❌ Error al crear.";
    // ⇨ Después de insertar → ir a la lista (solo lectura)
    go("index.php?pagina=listaProductos");

  case 'actualizar': // edición inline
    $id = (int)($_POST['idProductos'] ?? 0);
    $d = [
      'id_Familia'            => (int)($_POST['id_Familia'] ?? 0),
      'Codigo'                => trim($_POST['Codigo'] ?? ''),
      'Descripcion'           => trim($_POST['Descripcion'] ?? ''),
      'Unidad_Medida'         => trim($_POST['Unidad_Medida'] ?? ''),
      'Milimetros'            => ($_POST['Milimetros'] ?? '')==='' ? null : (float)$_POST['Milimetros'],
      'Pulgadas'              => ($_POST['Pulgadas'] ?? '')==='' ? null : (float)$_POST['Pulgadas'],
      'Tolerancia'            => ($_POST['Tolerancia'] ?? '')==='' ? null : (float)$_POST['Tolerancia'],
      'Peso_LB_MTS'           => ($_POST['Peso_LB_MTS'] ?? '')==='' ? null : (float)$_POST['Peso_LB_MTS'],
      'Precio_Venta_sin_IVA'  => ($_POST['Precio_Venta_sin_IVA'] ?? '')==='' ? null : (float)$_POST['Precio_Venta_sin_IVA'],
      'Precio_Fijo'           => isset($_POST['Precio_Fijo']) ? 1 : 0,
    ];
    if ($id<=0 || $d['id_Familia']<=0 || $d['Descripcion']==='') {
      $_SESSION['msg'] = "⚠️ Datos inválidos.";
      go("index.php?pagina=gestionarProductos");
    }
    $_SESSION['msg'] = $modelo->actualizarInline($id, $d) ? "✅ Producto actualizado." : "❌ No se pudo actualizar.";
    go("index.php?pagina=gestionarProductos");

  case 'eliminar': // soft delete
    $id = (int)($_GET['id'] ?? 0);
    $_SESSION['msg'] = ($id>0 && $modelo->eliminarLogico($id))
      ? "🗑️ Producto eliminado (borrado lógico)."
      : "❌ No se pudo eliminar el producto.";
    go("index.php?pagina=gestionarProductos");
}
