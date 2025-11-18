<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../conexion.php';
require_once __DIR__ . '/../Modelos/ModeloTareas.php';
require_once __DIR__ . '/../includes/paginacion.php';

$cn = conectar();
$modelo = new ModeloTareas($cn);

$self = 'listaTareas';
$fNombre = trim($_GET['nombre'] ?? '');

$perPage = 10; $page = max(1, (int)($_GET['page'] ?? 1)); $offset = ($page-1)*$perPage;
list($rows, $total) = $modelo->buscarConPaginacion($fNombre, $perPage, $offset);
$totalPages = max(1, (int)ceil($total / $perPage));
$params = ['pagina'=>$self, 'nombre'=>$fNombre];
?>
<h2>Tareas (solo lectura)</h2>
<?php if(!empty($_SESSION['msg'])){ echo "<p>".$_SESSION['msg']."</p>"; unset($_SESSION['msg']); } ?>

<form method="GET" action="<?= BASE_URL ?>index.php">
  <input type="hidden" name="pagina" value="<?= $self ?>">
  <label>Nombre:</label>
  <input type="text" name="nombre" value="<?= htmlspecialchars($fNombre) ?>">
  <button type="submit">Buscar</button>
  <a href="<?= BASE_URL ?>index.php?pagina=<?= $self ?>">Limpiar</a>
</form>

<p><a href="<?= BASE_URL ?>index.php?pagina=formularioNuevaTarea">➕ Nueva Tarea</a></p>

<p>Total: <?= $total ?></p>
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
    </tr>
  </thead>
  <tbody>
    <?php while($r = $rows->fetch_assoc()): ?>
      <tr>
        <td><?= htmlspecialchars($r['ModuloNombre']) ?></td>
        <td><?= htmlspecialchars($r['Nombre']) ?></td>
        <td><?= nl2br(htmlspecialchars($r['Descripcion'])) ?></td>
        <td><?= htmlspecialchars($r['Ruta'] ?? '') ?></td>
        <td><?= htmlspecialchars($r['Icono'] ?? '') ?></td>
        <td><?= (int)$r['Orden'] ?></td>
        <td><?= ((int)$r['Visible']===1 ? 'Sí' : 'No') ?></td>
      </tr>
    <?php endwhile; if(($rows->num_rows ?? 0)===0): ?>
      <tr><td colspan="7">Sin resultados.</td></tr>
    <?php endif; ?>
  </tbody>
</table>

<?php renderPagination($page, $totalPages, $params); ?>
