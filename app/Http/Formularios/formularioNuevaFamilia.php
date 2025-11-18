<?php
require_once __DIR__ . '/../includes/auth.php';

require_once __DIR__ . '/../config.php';
?>
<h2>Nueva Familia</h2>
<?php if(!empty($_SESSION['msg'])){ echo "<p>".$_SESSION['msg']."</p>"; unset($_SESSION['msg']); } ?>

<form method="POST" action="<?= BASE_URL ?>Controladores/ControladorFamilia.php?accion=insertar">
  <fieldset>
    <legend>Datos de la Familia</legend>

    <label>Nombre:</label><br>
    <input type="text" name="Nombre" required><br><br>

    <label>Descripción:</label><br>
    <textarea name="Descripcion" rows="3"></textarea>
  </fieldset>

  <div style="margin-top:10px;">
    <button type="submit">Guardar</button>
    <a href="<?= BASE_URL ?>index.php?pagina=listaFamilia">Cancelar</a>
  </div>
</form>
