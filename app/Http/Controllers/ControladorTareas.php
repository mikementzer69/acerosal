<?php
session_start();
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../conexion.php';
require_once __DIR__ . '/../Modelos/ModeloTareas.php';

$cn = conectar();
$modelo = new ModeloTareas($cn);

function go($rel){ header("Location: " . BASE_URL . ltrim($rel, '/')); exit; }

$accion = $_GET['accion'] ?? '';

switch ($accion) {

  case 'insertar':
    $d = [
      'id_Modulos'  => (int)($_POST['id_Modulos'] ?? 0),
      'Nombre'      => trim($_POST['Nombre'] ?? ''),
      'Descripcion' => trim($_POST['Descripcion'] ?? ''),
      'Ruta'        => trim($_POST['Ruta'] ?? ''),
      'Icono'       => trim($_POST['Icono'] ?? ''),
      'Orden'       => (int)($_POST['Orden'] ?? 1),
      'Visible'     => isset($_POST['Visible']) ? 1 : 0,
    ];
    if ($d['id_Modulos']<=0 || $d['Nombre']==='') {
      $_SESSION['msg'] = "⚠️ Módulo y Nombre son obligatorios.";
      go("index.php?pagina=formularioNuevaTarea");
    }
    $_SESSION['msg'] = $modelo->insertar($d) ? "✅ Tarea creada." : "❌ Error al crear la tarea.";
    // ⇨ tras insertar → lista (solo lectura)
    go("index.php?pagina=listaTareas");

  case 'actualizar': // inline
    $id = (int)($_POST['idTareas'] ?? 0);
    $d = [
      'id_Modulos'  => (int)($_POST['id_Modulos'] ?? 0),
      'Nombre'      => trim($_POST['Nombre'] ?? ''),
      'Descripcion' => trim($_POST['Descripcion'] ?? ''),
      'Ruta'        => trim($_POST['Ruta'] ?? ''),
      'Icono'       => trim($_POST['Icono'] ?? ''),
      'Orden'       => (int)($_POST['Orden'] ?? 1),
      'Visible'     => isset($_POST['Visible']) ? 1 : 0,
    ];
    if ($id<=0 || $d['id_Modulos']<=0 || $d['Nombre']==='') {
      $_SESSION['msg'] = "⚠️ Datos inválidos.";
      go("index.php?pagina=gestionarTareas");
    }
    $_SESSION['msg'] = $modelo->actualizarInline($id, $d) ? "✅ Tarea actualizada." : "❌ No se pudo actualizar.";
    go("index.php?pagina=gestionarTareas");

  case 'eliminar': // borrado lógico + cascada a Roles_Tareas
    $id = (int)($_GET['id'] ?? 0);
    $_SESSION['msg'] = ($id>0 && $modelo->eliminarLogicoConCascada($id))
      ? "🗑️ Tarea eliminada (borrado lógico y asignaciones desactivadas)."
      : "❌ No se pudo eliminar la tarea.";
    go("index.php?pagina=gestionarTareas");
}
