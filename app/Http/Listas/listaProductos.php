<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../conexion.php';
require_once __DIR__ . '/../Modelos/ModeloProductos.php';
require_once __DIR__ . '/../includes/paginacion.php';

$cn = conectar();
$modelo = new ModeloProductos($cn);

$self = 'listaProductos';
$fCodigo = trim($_GET['codigo'] ?? '');
$fTexto  = trim($_GET['texto'] ?? '');
$fFam    = $_GET['idFamilia'] ?? '';
$idFam   = ($fFam === '' ? null : (int)$fFam);

$perPage = 10; $page = max(1, (int)($_GET['page'] ?? 1)); $offset = ($page-1)*$perPage;
list($rows, $total) = $modelo->buscarConPaginacion($fCodigo, $fTexto, $idFam, $perPage, $offset);
$totalPages = max(1, (int)ceil($total / $perPage));
$params = ['pagina'=>$self, 'codigo'=>$fCodigo, 'texto'=>$fTexto, 'idFamilia'=>$fFam];

$fams = $modelo->obtenerFamiliasActivas();
$familias = [];
while($f = $fams->fetch_assoc()){ $familias[] = $f; }
?>
<h2>Productos (solo lectura)</h2>
<?php if(!empty($_SESSION['msg'])){ echo "<p>".$_SESSION['msg']."</p>"; unset($_SESSION['msg']); } ?>

<form method="GET" action="<?= BASE_URL ?>index.php">
  <input type="hidden" name="pagina" value="<?= $self ?>">
  <label>Código:</label>
  <input type="text" name="codigo" value="<?= htmlspecialchars($fCodigo) ?>">

  <label style="margin-left:8px;">Descripción:</label>
  <input type="text" name="texto" value="<?= htmlspecialchars($fTexto) ?>">

  <label style="margin-left:8px;">Familia:</label>
  <select name="idFamilia">
    <option value="">-- Todas --</option>
    <?php foreach($familias as $fa): ?>
      <option value="<?= $fa['idFamilia'] ?>" <?= ($fFam!=='' && (int)$fFam===$fa['idFamilia']?'selected':'') ?>>
        <?= htmlspecialchars($fa['Nombre']) ?>
      </option>
    <?php endforeach; ?>
  </select>

  <button type="submit">Buscar</button>
  <a href="<?= BASE_URL ?>index.php?pagina=<?= $self ?>">Limpiar</a>
</form>

<p>Total: <?= $total ?></p>
<table border="1" cellpadding="5">
  <thead>
    <tr>
      <th>Familia</th>
      <th>Código</th>
      <th>Descripción</th>
      <th>U.M.</th>
      <th>mm</th>
      <th>in</th>
      <th>Tolerancia</th>
      <th>Peso LB/MTS</th>
      <th>Precio s/IVA</th>
      <th>Precio Fijo</th>
    </tr>
  </thead>
  <tbody>
    <?php while($r = $rows->fetch_assoc()): ?>
      <tr>
        <td><?= htmlspecialchars($r['FamiliaNombre']) ?></td>
        <td><?= htmlspecialchars($r['Codigo']) ?></td>
        <td><?= nl2br(htmlspecialchars($r['Descripcion'])) ?></td>
        <td><?= htmlspecialchars($r['Unidad_Medida']) ?></td>
        <td><?= htmlspecialchars($r['Milimetros']) ?></td>
        <td><?= htmlspecialchars($r['Pulgadas']) ?></td>
        <td><?= htmlspecialchars($r['Tolerancia']) ?></td>
        <td><?= htmlspecialchars($r['Peso_LB_MTS']) ?></td>
        <td><?= htmlspecialchars($r['Precio_Venta_sin_IVA']) ?></td>
        <td><?= ((int)$r['Precio_Fijo']===1 ? 'Sí' : 'No') ?></td>
      </tr>
    <?php endwhile; if(($rows->num_rows ?? 0)===0): ?>
      <tr><td colspan="10">Sin resultados.</td></tr>
    <?php endif; ?>
  </tbody>
</table>

<?php renderPagination($page, $totalPages, $params); ?>
