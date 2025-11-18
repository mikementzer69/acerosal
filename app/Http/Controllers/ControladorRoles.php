<?php
session_start();
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../conexion.php';
require_once __DIR__ . '/../Modelos/ModeloRoles.php';

$cn = conectar();
$modelo = new ModeloRoles($cn);

function go($rel){ header("Location: " . BASE_URL . ltrim($rel, '/')); exit; }

$accion = $_GET['accion'] ?? '';

switch ($accion) {

  case 'insertar':
    $Nombre = trim($_POST['Nombre'] ?? '');
    $Descripcion = trim($_POST['Descripcion'] ?? '');
    $next = $_POST['next'] ?? 'lista'; // 'lista' | 'asignar'

    if ($Nombre === '') {
      $_SESSION['msg'] = "⚠️ El nombre del rol es obligatorio.";
      go("index.php?pagina=formularioNuevoRol");
    }

    $idRol = $modelo->insertar($Nombre, $Descripcion);
    if ($idRol === false) {
      $_SESSION['msg'] = "❌ No se pudo crear el rol.";
      go("index.php?pagina=formularioNuevoRol");
    }

    $_SESSION['msg'] = "✅ Rol creado.";
    if ($next === 'asignar') {
      go("index.php?pagina=formularioAsignarTareasRol&id={$idRol}");
    } else {
      go("index.php?pagina=listaRoles");
    }

  case 'actualizar': // inline desde gestionar
    $id = (int)($_POST['idRoles'] ?? 0);
    $Nombre = trim($_POST['Nombre'] ?? '');
    $Descripcion = trim($_POST['Descripcion'] ?? '');
    if ($id<=0 || $Nombre==='') {
      $_SESSION['msg'] = "⚠️ Datos inválidos.";
      go("index.php?pagina=gestionarRoles");
    }
    $_SESSION['msg'] = $modelo->actualizarInline($id, $Nombre, $Descripcion)
      ? "✅ Rol actualizado."
      : "❌ No se pudo actualizar.";
    go("index.php?pagina=gestionarRoles");

  case 'eliminar': // borrado lógico
    $id = (int)($_GET['id'] ?? 0);
    $_SESSION['msg'] = ($id>0 && $modelo->eliminarLogico($id))
      ? "🗑️ Rol eliminado (borrado lógico)."
      : "❌ No se pudo eliminar el rol.";
    go("index.php?pagina=gestionarRoles");

  case 'guardar_asignacion':
    $idRol = (int)($_POST['idRol'] ?? 0);
    $tareas = $_POST['tareas'] ?? []; // array de ids
    if ($idRol <= 0) {
      $_SESSION['msg'] = "⚠️ Rol inválido.";
      go("index.php?pagina=listaRoles");
    }
    $ok = $modelo->guardarAsignacionTareas($idRol, array_map('intval', $tareas));
    $_SESSION['msg'] = $ok ? "✅ Asignación actualizada." : "❌ No se pudo actualizar la asignación.";
    go("index.php?pagina=listaRoles");

  case 'ver_detalle':
    // opcional: manejado por vista (no se requiere lógica aquí)
    go("index.php?pagina=verDetalleRol&id=".(int)($_GET['id'] ?? 0));
}
