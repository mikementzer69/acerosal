<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../conexion.php';
require_once __DIR__ . '/../Modelos/ModeloTareas.php';

$cn = conectar();
$modelo = new ModeloTareas($cn);
$mods = $modelo->obtenerModulosActivos();
?>
<h2>Nueva Tarea</h2>
<?php if(!empty($_SESSION['msg'])){ echo "<p>".$_SESSION['msg']."</p>"; unset($_SESSION['msg']); } ?>

<form method="POST" action="<?= BASE_URL ?>Controladores/ControladorTareas.php?accion=insertar">
  <fieldset style="border:1px solid #ccc; padding:10px; margin-bottom:10px;">
    <legend>Datos de la Tarea</legend>

    <label>Módulo:</label><br>
    <select name="id_Modulos" required>
      <option value="">-- Seleccione --</option>
      <?php while($m = $mods->fetch_assoc()): ?>
        <option value="<?= $m['idModulos'] ?>"><?= htmlspecialchars($m['Nombre']) ?></option>
      <?php endwhile; ?>
    </select><br><br>

    <label>Nombre:</label><br>
    <input type="text" name="Nombre" required><br><br>

    <label>Descripción:</label><br>
    <textarea name="Descripcion" rows="3"></textarea><br><br>

    <label>Ruta:</label><br>
    <input type="text" name="Ruta" placeholder="index.php?pagina=..."><br><br>

    <label>Ícono:</label><br>
    <input type="text" name="Icono" placeholder="ej. fa-solid fa-box"><br><br>

    <label>Orden:</label><br>
    <select name="Orden">
      <?php for($i=1;$i<=10;$i++): ?>
        <option value="<?= $i ?>"><?= $i ?></option>
      <?php endfor; ?>
    </select><br><br>

    <label><input type="checkbox" name="Visible" checked> Visible</label>
  </fieldset>

  <button type="submit">Guardar</button>
  <a href="<?= BASE_URL ?>index.php?pagina=listaTareas">Cancelar</a>
</form>
