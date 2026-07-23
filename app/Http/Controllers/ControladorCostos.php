<?php
session_start();
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../conexion.php';
require_once __DIR__ . '/../Modelos/ModeloCostos.php';

$cn = conectar();
$modelo = new ModeloCostos($cn);

function go($rel){ header("Location: " . BASE_URL . ltrim($rel, '/')); exit; }

$accion = $_GET['accion'] ?? '';

switch ($accion) {

  case 'insertar':
    $Nombre = trim($_POST['Nombre'] ?? '');
    $Descripcion = trim($_POST['Descripcion'] ?? '');
    if ($Nombre === '') {
      $_SESSION['msg'] = "⚠️ El nombre es obligatorio.";
      go("index.php?pagina=formularioNuevoCosto");
    }
    $_SESSION['msg'] = $modelo->insertar(['Nombre'=>$Nombre,'Descripcion'=>$Descripcion])
      ? "✅ Costo creado."
      : "❌ Error al crear el costo.";
    // ⇨ tras insertar → lista (solo lectura)
    go("index.php?pagina=listaCostos");

  case 'actualizar': // inline
    $id = (int)($_POST['idCostos'] ?? 0);
    $Nombre = trim($_POST['Nombre'] ?? '');
    $Descripcion = trim($_POST['Descripcion'] ?? '');
    if ($id<=0 || $Nombre==='') {
      $_SESSION['msg'] = "⚠️ Datos inválidos.";
      go("index.php?pagina=gestionarCostos");
    }
    $_SESSION['msg'] = $modelo->actualizarInline($id, ['Nombre'=>$Nombre,'Descripcion'=>$Descripcion])
      ? "✅ Costo actualizado."
      : "❌ No se pudo actualizar.";
    go("index.php?pagina=gestionarCostos");

  case 'eliminar': // borrado lógico
    $id = (int)($_GET['id'] ?? 0);
    $_SESSION['msg'] = ($id>0 && $modelo->eliminarLogico($id))
      ? "🗑️ Costo eliminado (borrado lógico)."
      : "❌ No se pudo eliminar el costo.";
    go("index.php?pagina=gestionarCostos");
}
