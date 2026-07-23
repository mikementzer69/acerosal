<?php
session_start();
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../conexion.php';
require_once __DIR__ . '/../Modelos/ModeloModulos.php';

$cn = conectar();
$modelo = new ModeloModulos($cn);

function go($rel){ header("Location: " . BASE_URL . ltrim($rel, '/')); exit; }

$accion = $_GET['accion'] ?? '';

switch ($accion) {

  case 'insertar':
    $Nombre = trim($_POST['Nombre'] ?? '');
    $Descripcion = trim($_POST['Descripcion'] ?? '');
    $idPadre = $_POST['id_ModuloPadre'] ?? '';
    $idPadre = ($idPadre === '' ? null : (int)$idPadre);

    if ($Nombre === '') {
      $_SESSION['msg'] = "⚠️ El nombre es obligatorio.";
      go("index.php?pagina=formularioNuevoModulo");
    }

    $ok = $modelo->insertar([
      'Nombre' => $Nombre,
      'Descripcion' => $Descripcion,
      'id_ModuloPadre' => $idPadre
    ]);

    $_SESSION['msg'] = $ok ? "✅ Módulo creado." : "❌ Error al crear el módulo.";
    // ⇨ tras insertar → lista (solo lectura)
    go("index.php?pagina=listaModulos");

  case 'actualizar': // inline
    $id = (int)($_POST['idModulos'] ?? 0);
    $Nombre = trim($_POST['Nombre'] ?? '');
    $Descripcion = trim($_POST['Descripcion'] ?? '');
    $idPadre = $_POST['id_ModuloPadre'] ?? '';
    $idPadre = ($idPadre === '' ? null : (int)$idPadre);

    if ($id<=0 || $Nombre==='') {
      $_SESSION['msg'] = "⚠️ Datos inválidos.";
      go("index.php?pagina=gestionarModulo");
    }

    $_SESSION['msg'] = $modelo->actualizarInline($id, [
      'Nombre' => $Nombre,
      'Descripcion' => $Descripcion,
      'id_ModuloPadre' => $idPadre
    ]) ? "✅ Módulo actualizado." : "❌ No se pudo actualizar.";

    go("index.php?pagina=gestionarModulo");

  case 'eliminar':
    $id = (int)($_GET['id'] ?? 0);
    $_SESSION['msg'] = ($id>0 && $modelo->eliminarLogico($id))
      ? "🗑️ Módulo eliminado (borrado lógico)."
      : "❌ No se pudo eliminar el módulo.";
    go("index.php?pagina=gestionarModulo");
}
