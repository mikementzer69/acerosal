<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../conexion.php';
require_once __DIR__ . '/../Modelos/ModeloProductos.php';

$cn = conectar();
$modelo = new ModeloProductos($cn);
$fams = $modelo->obtenerFamiliasActivas();
?>
<h2>Nuevo Producto</h2>
<?php if(!empty($_SESSION['msg'])){ echo "<p>".$_SESSION['msg']."</p>"; unset($_SESSION['msg']); } ?>

<form method="POST" action="<?= BASE_URL ?>Controladores/ControladorProductos.php?accion=insertar">
  <fieldset style="border:1px solid #ccc; padding:10px;">
    <legend>Datos del Producto</legend>

    <label>Familia:</label><br>
    <select name="id_Familia" required>
      <option value="">-- Seleccione --</option>
      <?php while($f = $fams->fetch_assoc()): ?>
        <option value="<?= $f['idFamilia'] ?>"><?= htmlspecialchars($f['Nombre']) ?></option>
      <?php endwhile; ?>
    </select><br><br>

    <label>Código:</label><br>
    <input type="text" name="Codigo"><br><br>

    <label>Descripción:</label><br>
    <textarea name="Descripcion" rows="3" required></textarea><br><br>

    <label>Unidad de Medida:</label><br>
    <input type="text" name="Unidad_Medida" placeholder="kg, lb, pza, etc."><br><br>

    <label>Milímetros:</label><br>
    <input type="number" step="0.0001" name="Milimetros"><br><br>

    <label>Pulgadas:</label><br>
    <input type="number" step="0.0001" name="Pulgadas"><br><br>

    <label>Tolerancia:</label><br>
    <input type="number" step="0.0001" name="Tolerancia"><br><br>

    <label>Peso LB/MTS:</label><br>
    <input type="number" step="0.0001" name="Peso_LB_MTS"><br><br>

    <label>Precio Venta sin IVA (USD):</label><br>
    <input type="number" step="0.01" name="Precio_Venta_sin_IVA"><br><br>

    <label><input type="checkbox" name="Precio_Fijo" checked> Precio Fijo</label>
  </fieldset>

  <div style="margin-top:10px;">
    <button type="submit">Guardar</button>
    <a href="<?= BASE_URL ?>index.php?pagina=listaProductos">Cancelar</a>
  </div>
</form>
