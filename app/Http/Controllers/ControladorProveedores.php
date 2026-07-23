<?php
session_start();
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../conexion.php';
require_once __DIR__ . '/../Modelos/ModeloProveedores.php';

$cn = conectar();
$modelo = new ModeloProveedores($cn);

function go($relative) {
  header("Location: " . BASE_URL . ltrim($relative, '/'));
  exit;
}

$accion = $_GET['accion'] ?? '';

switch ($accion) {

  case 'insertar':
    $Nombre    = trim($_POST['Nombre'] ?? '');
    $Origen    = trim($_POST['Origen'] ?? '');
    $Direccion = trim($_POST['Direccion'] ?? '');

    if ($Nombre === '') {
      $_SESSION['msg'] = "⚠️ El nombre es obligatorio.";
      go("index.php?pagina=formularioNuevoProveedor");
    }

    $ok = $modelo->insertar([
      'Nombre'    => $Nombre,
      'Origen'    => $Origen,
      'Direccion' => $Direccion,
    ]);

    $_SESSION['msg'] = $ok ? "✅ Proveedor creado." : "❌ Error al crear el proveedor.";
    // ⇨ Ir a lista de solo lectura
    go("index.php?pagina=listaProveedores");

  case 'actualizar': // inline desde gestionar
    $id         = (int)($_POST['idProveedores'] ?? 0);
    $Nombre     = trim($_POST['Nombre'] ?? '');
    $Origen     = trim($_POST['Origen'] ?? '');
    $Direccion  = trim($_POST['Direccion'] ?? '');

    if ($id <= 0 || $Nombre === '') {
      $_SESSION['msg'] = "⚠️ Datos inválidos.";
      go("index.php?pagina=gestionarProveedores");
    }

    $_SESSION['msg'] = $modelo->actualizarInline($id, [
      'Nombre'    => $Nombre,
      'Origen'    => $Origen,
      'Direccion' => $Direccion,
    ]) ? "✅ Proveedor actualizado." : "❌ No se pudo actualizar.";

    go("index.php?pagina=gestionarProveedores");

  case 'eliminar':
    $id = (int)($_GET['id'] ?? 0);
    $_SESSION['msg'] = ($id>0 && $modelo->eliminarLogico($id))
      ? "🗑️ Proveedor eliminado (borrado lógico)."
      : "❌ No se pudo eliminar el proveedor.";
    go("index.php?pagina=gestionarProveedores");
}
