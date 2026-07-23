<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config.php';
?>
<h2>Nuevo Rol</h2>
<?php if(!empty($_SESSION['msg'])){ echo "<p>".$_SESSION['msg']."</p>"; unset($_SESSION['msg']); } ?>

<form method="POST" action="<?= BASE_URL ?>controladores/ControladorRoles.php?accion=insertar">
  <fieldset style="border:1px solid #ccc; padding:10px;">
    <legend>Datos del Rol</legend>
    <label>Nombre:</label><br>
    <input type="text" name="Nombre" required><br><br>

    <label>Descripción:</label><br>
    <textarea name="Descripcion" rows="3"></textarea>
  </fieldset>

  <div style="margin-top:10px;">
    <!-- Botón 1: Guardar → lista -->
    <button type="submit" name="next" value="lista">Guardar</button>
    <!-- Botón 2: Guardar y asignar → paso 2 -->
    <button type="submit" name="next" value="asignar">Guardar y asignar tareas</button>
    <a href="<?= BASE_URL ?>index.php?pagina=listaRoles">Cancelar</a>
  </div>
</form>
