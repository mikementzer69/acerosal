<?php
session_start();
require_once __DIR__ . '/../conexion.php';
require_once __DIR__ . '/../Modelos/ModeloRoles.php';

$cn = conectar(); $modelo = new ModeloRoles($cn);
$id = (int)($_GET['id'] ?? 0);
$rol = $modelo->obtenerPorId($id);
if (!$rol || $rol->num_rows===0){ echo "<p>Rol no encontrado.</p>"; exit; }
$info = $rol->fetch_assoc();
?>
<h2>Editar Rol</h2>
<?php if(!empty($_SESSION['msg'])){ echo "<p>".$_SESSION['msg']."</p>"; unset($_SESSION['msg']); } ?>

<form method="POST" action="../Controladores/ControladorRoles.php?accion=actualizar">
  <input type="hidden" name="idRoles" value="<?= $info['idRoles'] ?>">
  <label>Nombre:</label><br>
  <input type="text" name="Nombre" value="<?= htmlspecialchars($info['Nombre']) ?>" required><br><br>

  <label>Descripción:</label><br>
  <textarea name="Descripcion" rows="3"><?= htmlspecialchars($info['Descripcion']) ?></textarea><br><br>

  <button type="submit">Guardar</button>
  <a href="../gestiones/gestionarRoles.php">Cancelar</a>
</form>
