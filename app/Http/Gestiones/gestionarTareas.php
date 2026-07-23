<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../conexion.php';
require_once __DIR__ . '/../Modelos/ModeloTareas.php';
require_once __DIR__ . '/../includes/paginacion.php';

$cn = conectar();
$modelo = new ModeloTareas($cn);

$self = 'gestionarTareas';
$fNombre = trim($_GET['nombre'] ?? '');

$perPage = 10; $page = max(1, (int)($_GET['page'] ?? 1)); $offset = ($page-1)*$perPage;
list($rows, $total) = $modelo->buscarConPaginacion($fNombre, $perPage, $offset);
$totalPages = max(1, (int)ceil($total / $perPage));
$params = ['pagina'=>$self, 'nombre'=>$fNombre];

// Modulos (para selects)
$modsRes = $modelo->obtenerModulosActivos();
$mods = [];
while($m = $modsRes->fetch_assoc()){ $mods[] = $m; }
?>
<h2>Gestión de Tareas</h2>
<?php if(!empty($_SESSION['msg'])){ echo "<p>".$_SESSION['msg']."</p>"; unset($_SESSION['msg']); } ?>

<p><a href="<?= BASE_URL ?>index.php?pagina=formularioNuevaTarea">➕ Nueva Tarea</a></p>

<form method="GET" action="<?= BASE_URL ?>index.php">
  <input type="hidden" name="pagina" value="<?= $self ?>">
  <label>Nombre:</label>
  <input type="text" name="nombre" value="<?= htmlspecialchars($fNombre) ?>">
  <button type="submit">Buscar</button>
  <a href="<?= BASE_URL ?>index.php?pagina=<?= $self ?>">Limpiar</a>
</form>

<table border="1" cellpadding="5">
  <thead>
    <tr>
      <th>Módulo</th>
      <th>Nombre</th>
      <th>Descripción</th>
      <th>Ruta</th>
      <th>Ícono</th>
      <th>Orden</th>
      <th>Visible</th>
      <th>Acciones</th>
    </tr>
  </thead>
  <tbody>
    <?php while($r = $rows->fetch_assoc()): ?>
      <tr>
        <form method="POST" action="<?= BASE_URL ?>controladores/ControladorTareas.php?accion=actualizar">
          <td>
            <input type="hidden" name="idTareas" value="<?= $r['idTareas'] ?>">
            <select name="id_Modulos" required>
              <?php foreach($mods as $m): ?>
                <option value="<?= $m['idModulos'] ?>" <?= ($r['id_Modulos']==$m['idModulos']?'selected':'') ?>>
                  <?= htmlspecialchars($m['Nombre']) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </td>
          <td><input type="text" name="Nombre" value="<?= htmlspecialchars($r['Nombre']) ?>" required></td>
          <td><textarea name="Descripcion" rows="2"><?= htmlspecialchars($r['Descripcion']) ?></textarea></td>
          <td><input type="text" name="Ruta" value="<?= htmlspecialchars($r['Ruta']) ?>"></td>
          <td><input type="text" name="Icono" value="<?= htmlspecialchars($r['Icono']) ?>"></td>
          <td>
            <select name="Orden">
              <?php for($i=1;$i<=10;$i++): ?>
                <option value="<?= $i ?>" <?= ((int)$r['Orden']===$i?'selected':'') ?>><?= $i ?></option>
              <?php endfor; ?>
            </select>
          </td>
          <td><input type="checkbox" name="Visible" <?= ((int)$r['Visible']===1?'checked':'') ?>></td>
          <td>
            <button type="submit">💾 Guardar</button>
            <a href="<?= BASE_URL ?>controladores/ControladorTareas.php?accion=eliminar&id=<?= $r['idTareas'] ?>"
               onclick="return confirm('¿Eliminar esta tarea? Se desactivarán sus asignaciones a roles.')">🗑️ Eliminar</a>
          </td>
        </form>
      </tr>
    <?php endwhile; if(($rows->num_rows ?? 0)===0): ?>
      <tr><td colspan="8">Sin resultados.</td></tr>
    <?php endif; ?>
  </tbody>
</table>

<?php renderPagination($page, $totalPages, $params); ?>
