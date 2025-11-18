<?php
session_start();
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../conexion.php';
require_once __DIR__ . '/../Modelos/ModeloFamilia.php';

$cn = conectar();
$modelo = new ModeloFamilia($cn);

function go($rel){ header("Location: " . BASE_URL . ltrim($rel, '/')); exit; }

$accion = $_GET['accion'] ?? '';

switch ($accion) {

  case 'insertar':
    $Nombre = trim($_POST['Nombre'] ?? '');
    $Descripcion = trim($_POST['Descripcion'] ?? '');
    if ($Nombre === '') {
      $_SESSION['msg'] = "⚠️ El nombre es obligatorio.";
      go("index.php?pagina=formularioNuevaFamilia");
    }
    $_SESSION['msg'] = $modelo->insertar(['Nombre'=>$Nombre, 'Descripcion'=>$Descripcion])
      ? "✅ Familia creada."
      : "❌ Error al crear la familia.";
    // ⇨ tras insertar → lista (solo lectura)
    go("index.php?pagina=listaFamilia");

  case 'actualizar': // inline
    $id = (int)($_POST['idFamilia'] ?? 0);
    $Nombre = trim($_POST['Nombre'] ?? '');
    $Descripcion = trim($_POST['Descripcion'] ?? '');
    if ($id<=0 || $Nombre==='') {
      $_SESSION['msg'] = "⚠️ Datos inválidos.";
      go("index.php?pagina=gestionarFamilia");
    }
    $_SESSION['msg'] = $modelo->actualizarInline($id, ['Nombre'=>$Nombre, 'Descripcion'=>$Descripcion])
      ? "✅ Familia actualizada."
      : "❌ No se pudo actualizar.";
    go("index.php?pagina=gestionarFamilia");

  case 'eliminar': // borrado lógico
    $id = (int)($_GET['id'] ?? 0);
    $_SESSION['msg'] = ($id>0 && $modelo->eliminarLogico($id))
      ? "🗑️ Familia eliminada (borrado lógico)."
      : "❌ No se pudo eliminar la familia.";
    go("index.php?pagina=gestionarFamilia");
}
