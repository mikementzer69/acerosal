<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../conexion.php';
require_once __DIR__ . '/../Modelos/ModeloUsuarios.php';

$cn = conectar();
$modelo = new ModeloUsuarios($cn);
$roles = $modelo->obtenerRolesActivos();
?>
<h2>Nuevo Usuario</h2>
<?php if(!empty($_SESSION['msg'])){ echo "<p>".$_SESSION['msg']."</p>"; unset($_SESSION['msg']); } ?>

<form method="POST" action="<?= BASE_URL ?>Controladores/ControladorUsuarios.php?accion=insertar" onsubmit="return validarPasswords();">
  <fieldset style="border:1px solid #ccc; padding:10px; margin-bottom:10px;">
    <legend>Datos personales</legend>
    <label>Nombre:</label><br>
    <input type="text" name="Nombre"><br><br>

    <label>Apellidos:</label><br>
    <input type="text" name="Apellidos"><br><br>

    <label>Email:</label><br>
    <input type="email" name="Email"><br><br>

    <label>Celular:</label><br>
    <input type="text" name="Celular"><br>
  </fieldset>

  <fieldset style="border:1px solid #ccc; padding:10px; margin-bottom:10px;">
    <legend>Cuenta</legend>
    <label>Usuario:</label><br>
    <input type="text" name="Username" required><br><br>

    <label>Contraseña:</label><br>
    <input type="password" name="Password" required><br><br>

    <label>Repetir Contraseña:</label><br>
    <input type="password" name="Password2" required><br><br>

    <label>Rol:</label><br>
    <select name="id_Roles" required>
      <option value="">-- Seleccione --</option>
      <?php while($r = $roles->fetch_assoc()): ?>
        <option value="<?= $r['idRoles'] ?>"><?= htmlspecialchars($r['Nombre']) ?></option>
      <?php endwhile; ?>
    </select>
  </fieldset>

  <button type="submit">Guardar</button>
  <a href="<?= BASE_URL ?>index.php?pagina=listaUsuarios">Cancelar</a>
</form>

<script>
function validarPasswords(){
  const p1 = document.querySelector('input[name="Password"]').value;
  const p2 = document.querySelector('input[name="Password2"]').value;
  if (p1 !== p2) { alert('Las contraseñas no coinciden.'); return false; }
  return true;
}
</script>
