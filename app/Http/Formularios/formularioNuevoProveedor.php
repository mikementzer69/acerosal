<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../conexion.php';
require_once __DIR__ . '/../Modelos/ModeloProveedores.php';
require_once __DIR__ . '/../includes/paises.php';

$cn = conectar();
$modelo = new ModeloProveedores($cn);
?>
<h2>Nuevo Proveedor</h2>
<?php if(!empty($_SESSION['msg'])){ echo "<p>".$_SESSION['msg']."</p>"; unset($_SESSION['msg']); } ?>

<form method="POST" action="<?= BASE_URL ?>Controladores/ControladorProveedores.php?accion=insertar">
  <fieldset style="border:1px solid #ccc; padding:10px; margin-bottom:10px;">
    <legend>Datos del Proveedor</legend>

    <label>Nombre:</label><br>
    <input type="text" name="Nombre" required><br><br>

    <label>Origen (País):</label><br>
    <select name="Origen">
      <option value="">-- Seleccione --</option>
      <?php foreach($PAISES as $p): ?>
        <option value="<?= htmlspecialchars($p) ?>"><?= htmlspecialchars($p) ?></option>
      <?php endforeach; ?>
    </select><br><br>

    <label>Dirección:</label><br>
    <textarea name="Direccion" rows="3"></textarea><br><br>
  </fieldset>
<br>
  <button type="submit">Guardar</button>
  <a href="<?= BASE_URL ?>index.php?pagina=listaProveedores">Cancelar</a>
</form>
