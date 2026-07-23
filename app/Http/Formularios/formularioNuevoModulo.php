<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../conexion.php';
require_once __DIR__ . '/../Modelos/ModeloModulos.php';

$cn = conectar();
$modelo = new ModeloModulos($cn);
$mods = $modelo->obtenerActivos();
$lista = [];
while($m = $mods->fetch_assoc()) $lista[] = $m;
?>
<h2>Nuevo Módulo</h2>
<?php if(!empty($_SESSION['msg'])){ echo "<p>".$_SESSION['msg']."</p>"; unset($_SESSION['msg']); } ?>

<form method="POST" action="<?= BASE_URL ?>Controladores/ControladorModulos.php?accion=insertar">
  <fieldset style="border:1px solid #ccc; padding:10px;">
    <legend>Datos del Módulo</legend>

    <label>Nombre:</label><br>
    <input type="text" name="Nombre" required><br><br>

    <label>Descripción:</label><br>
    <textarea name="Descripcion" rows="3"></textarea><br><br>

    <label>Módulo padre (opcional):</label><br>
    <select name="id_ModuloPadre">
      <option value="">— Sin padre —</option>
      <?php foreach($lista as $m): ?>
        <option value="<?= $m['idModulos'] ?>"><?= htmlspecialchars($m['Nombre']) ?></option>
      <?php endforeach; ?>
    </select>
  </fieldset>

  <div style="margin-top:10px;">
    <button type="submit">Guardar</button>
    <a href="<?= BASE_URL ?>index.php?pagina=listaModulo">Cancelar</a>
  </div>
</form>
