<?php
session_start();
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../conexion.php';
require_once __DIR__ . '/../Modelos/ModeloUsuarios.php';

$cn = conectar();
$modelo = new ModeloUsuarios($cn);

function go($relative) {
  header("Location: " . BASE_URL . ltrim($relative, '/'));
  exit;
}

$accion = $_GET['accion'] ?? '';

switch ($accion) {

  case 'insertar':
    // Campos
    $Username  = trim($_POST['Username'] ?? '');
    $Password  = $_POST['Password'] ?? '';
    $Password2 = $_POST['Password2'] ?? '';
    $Nombre    = trim($_POST['Nombre'] ?? '');
    $Apellidos = trim($_POST['Apellidos'] ?? '');
    $Email     = trim($_POST['Email'] ?? '');
    $Celular   = trim($_POST['Celular'] ?? '');
    $id_Roles  = (int)($_POST['id_Roles'] ?? 0);

    // Validaciones mínimas
    if ($Username === '' || $Password === '' || $Password2 === '' || $id_Roles <= 0) {
      $_SESSION['msg'] = "⚠️ Usuario, contraseña y rol son obligatorios.";
      go("index.php?pagina=formularioNuevoUsuario");
    }
    if ($Password !== $Password2) {
      $_SESSION['msg'] = "⚠️ Las contraseñas no coinciden.";
      go("index.php?pagina=formularioNuevoUsuario");
    }

    // Validar username único (solo activos o todos, como prefieras: aquí contra todos)
    if ($modelo->existeUsername($Username)) {
      $_SESSION['msg'] = "⚠️ El nombre de usuario ya existe.";
      go("index.php?pagina=formularioNuevoUsuario");
    }

    // Hash de password
    $PasswordHash = password_hash($Password, PASSWORD_BCRYPT);

    // Insertar
    $ok = $modelo->insertar([
      'Username'  => $Username,
      'Password'  => $PasswordHash,
      'Nombre'    => $Nombre,
      'Apellidos' => $Apellidos,
      'Email'     => $Email,
      'Celular'   => $Celular,
      'id_Roles'  => $id_Roles,
    ]);

    $_SESSION['msg'] = $ok ? "✅ Usuario creado." : "❌ Error al crear el usuario.";

    // ⇨ Al terminar inserción → ir a la lista de solo lectura
    go("index.php?pagina=listaUsuarios");

  case 'actualizar':
    // Actualiza desde el formulario de edición
    $idUsuarios = (int)($_POST['idUsuarios'] ?? 0);
    $Nombre     = trim($_POST['Nombre'] ?? '');
    $Apellidos  = trim($_POST['Apellidos'] ?? '');
    $Email      = trim($_POST['Email'] ?? '');
    $Celular    = trim($_POST['Celular'] ?? '');
    $id_Roles   = (int)($_POST['id_Roles'] ?? 0);

    // (opcional) actualizar password si se envía
    $Password   = $_POST['Password'] ?? '';
    $Password2  = $_POST['Password2'] ?? '';

    if ($idUsuarios <= 0 || $id_Roles <= 0) {
      $_SESSION['msg'] = "⚠️ Datos inválidos.";
      go("index.php?pagina=gestionarUsuarios");
    }

    $data = [
      'Nombre'    => $Nombre,
      'Apellidos' => $Apellidos,
      'Email'     => $Email,
      'Celular'   => $Celular,
      'id_Roles'  => $id_Roles,
    ];

    if ($Password !== '' || $Password2 !== '') {
      if ($Password !== $Password2) {
        $_SESSION['msg'] = "⚠️ Las contraseñas no coinciden.";
        go("index.php?pagina=formularioEditarUsuario&id={$idUsuarios}");
      }
      $data['Password'] = password_hash($Password, PASSWORD_BCRYPT);
    }

    $_SESSION['msg'] = $modelo->actualizar($idUsuarios, $data)
      ? "✅ Usuario actualizado."
      : "❌ No se pudo actualizar.";
    // Los updates los mantenemos volviendo a gestionar
    go("index.php?pagina=gestionarUsuarios");

  case 'eliminar': // borrado lógico → Activo = 0
    $id = (int)($_GET['id'] ?? 0);
    $_SESSION['msg'] = ($id > 0 && $modelo->eliminarLogico($id))
      ? "🗑️ Usuario eliminado (borrado lógico)."
      : "❌ No se pudo eliminar el usuario.";
    go("index.php?pagina=gestionarUsuarios");
}
