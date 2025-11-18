<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../conexion.php';
require_once __DIR__ . '/../Modelos/ModeloProveedores.php';
require_once __DIR__ . '/../includes/paginacion.php';

$cn = conectar();
$modelo = new ModeloProveedores($cn);

$self = 'listaProveedores';
$fNombre = trim($_GET['nombre'] ?? '');

$perPage = 10;
$page = max(1, (int)($_GET['page'] ?? 1));
$offset = ($page-1)*$perPage;

list($rows, $total) = $modelo->buscarConPaginacion($fNombre, $perPage, $offset);
$totalPages = max(1, (int)ceil($total / $perPage));
$params = ['pagina'=>$self, 'nombre'=>$fNombre];
?>
<h2>Proveedores (solo lectura)</h2>

<form method="GET" action="<?= BASE_URL ?>index.php">
  <input type="hidden" name="pagina" value="<?= $self ?>">
  <label>Nombre:</label>
  <input type="text" name="nombre" value="<?= htmlspecialchars($fNombre) ?>">
  <button type="submit">Buscar</button>
  <a href="<?= BASE_URL ?>index.php?pagina=<?= $self ?>">Limpiar</a>
</form>

<p>Total: <?= $total ?></p>
<table border="1" cellpadding="5">
  <thead>
    <tr>
      <th>Nombre</th>
      <th>Origen</th>
      <th>Dirección</th>
    </tr>
  </thead>
  <tbody>
    <?php while($r = $rows->fetch_assoc()): ?>
      <tr>
        <td><?= htmlspecialchars($r['Nombre']) ?></td>
        <td><?= htmlspecialchars($r['Origen']) ?></td>
        <td><?= nl2br(htmlspecialchars($r['Direccion'])) ?></td>
      </tr>
    <?php endwhile; if(($rows->num_rows ?? 0) === 0): ?>
      <tr><td colspan="3">Sin resultados.</td></tr>
    <?php endif; ?>
  </tbody>
</table>

<?php renderPagination($page, $totalPages, $params); ?>
