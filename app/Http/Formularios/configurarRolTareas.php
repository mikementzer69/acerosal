<?php
session_start();
require_once __DIR__ . '/../conexion.php';
require_once __DIR__ . '/../Modelos/ModeloRoles.php';

$cn = conectar(); $modelo = new ModeloRoles($cn);

$idRol = (int)($_GET['idRol'] ?? 0);
$rol = $modelo->obtenerPorId($idRol);
if (!$rol || $rol->num_rows===0){ echo "<p>Rol no encontrado.</p>"; exit; }
$info = $rol->fetch_assoc();

$grupos = $modelo->obtenerTareasActivasAgrupadasPorModulo();
$idsSel = $modelo->obtenerIdsTareasDelRol($idRol);
$sel = array_flip($idsSel);
?>
<h2>Configurar Tareas del Rol (Paso 2)</h2>
<?php if(!empty($_SESSION['msg'])){ echo "<p>".$_SESSION['msg']."</p>"; unset($_SESSION['msg']); } ?>

<p><strong>Rol:</strong> <?= htmlspecialchars($info['Nombre']) ?></p>

<form method="POST" action="../Controladores/ControladorRoles.php?accion=guardar_tareas">
  <input type="hidden" name="idRol" value="<?= $idRol ?>">

  <?php foreach ($grupos as $nombreModulo => $tareas): ?>
    <fieldset style="border:1px solid #ccc; padding:10px; margin-bottom:10px;">
      <legend><?= htmlspecialchars($nombreModulo) ?></legend>
      <?php foreach ($tareas as $t): ?>
        <?php $checked = isset($sel[(int)$t['idTareas']]) ? 'checked' : ''; ?>
        <label style="display:block; margin-bottom:6px;">
          <input type="checkbox" name="tareas[]" value="<?= (int)$t['idTareas'] ?>" <?= $checked ?>>
          <?= htmlspecialchars($t['Titulo']) ?>
        </label>
      <?php endforeach; ?>
    </fieldset>
  <?php endforeach; ?>

  <button type="submit">Guardar Tareas del Rol</button>
  <a href="../gestiones/gestionarRoles.php">Cancelar</a>
</form>
