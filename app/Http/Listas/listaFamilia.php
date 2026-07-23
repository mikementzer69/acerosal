<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../conexion.php';
require_once __DIR__ . '/../Modelos/ModeloFamilia.php';
require_once __DIR__ . '/../includes/paginacion.php';

$cn = conectar();
$modelo = new ModeloFamilia($cn);

$self = 'listaFamilia';
$fNombre = trim($_GET['nombre'] ?? '');

$perPage = 10;
$page = max(1, (int)($_GET['page'] ?? 1));
$offset = ($page-1)*$perPage;

list($rows, $total) = $modelo->buscarConPaginacion($fNombre, $perPage, $offset);
$totalPages = max(1, (int)ceil($total / $perPage));
$params = ['pagina'=>$self, 'nombre'=>$fNombre];
?>
<h2>Familia (solo lectura)</h2>
<?php if(!empty($_SESSION['msg'])){ echo "<p>".$_SESSION['msg']."</p>"; unset($_SESSION['msg']); } ?>

<form method="GET" action="<?= BASE_URL ?>index.php">
  <input type="hidden" name="pagina" value="<?= $self ?>">
  <label>Nombre:</label>
  <input type="text" name="nombre" value="<?= htmlspecialchars($fNombre) ?>">
  <button type="submit">Buscar</button>
  <a href="<?= BASE_URL ?>index.php?pagina=<?= $self ?>">Limpiar</a>
</form>

<p><a href="<?= BASE_URL ?>index.php?pagina=formularioNuevaFamilia">➕ Nueva Familia</a></p>

<p>Total: <?= $total ?></p>
<table border="1" cellpadding="5">
  <thead>
    <tr>
      <th>Nombre</th>
      <th>Descripción</th>
    </tr>
  </thead>
  <tbody>
    <?php while($r = $rows->fetch_assoc()): ?>
      <tr>
        <td><?= htmlspecialchars($r['Nombre']) ?></td>
        <td><?= nl2br(htmlspecialchars($r['Descripcion'])) ?></td>
      </tr>
    <?php endwhile; if(($rows->num_rows ?? 0)===0): ?>
      <tr><td colspan="2">Sin resultados.</td></tr>
    <?php endif; ?>
  </tbody>
</table>

<?php renderPagination($page, $totalPages, $params); ?>
