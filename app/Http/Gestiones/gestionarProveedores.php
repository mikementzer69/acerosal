<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../conexion.php';
require_once __DIR__ . '/../Modelos/ModeloProveedores.php';
require_once __DIR__ . '/../includes/paginacion.php';
require_once __DIR__ . '/../includes/paises.php';

$cn = conectar();
$modelo = new ModeloProveedores($cn);

$self = 'gestionarProveedores';
$fNombre = trim($_GET['nombre'] ?? '');

$perPage = 10;
$page = max(1, (int)($_GET['page'] ?? 1));
$offset = ($page-1)*$perPage;

list($rows, $total) = $modelo->buscarConPaginacion($fNombre, $perPage, $offset);
$totalPages = max(1, (int)ceil($total / $perPage));
$params = ['pagina'=>$self, 'nombre'=>$fNombre];
?>
<h2>Gestión de Proveedores</h2>
<?php if(!empty($_SESSION['msg'])){ echo "<p>".$_SESSION['msg']."</p>"; unset($_SESSION['msg']); } ?>

<p><a href="<?= BASE_URL ?>index.php?pagina=formularioNuevoProveedor">➕ Nuevo Proveedor</a></p>

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
      <th>Nombre</th>
      <th>Origen</th>
      <th>Dirección</th>
      <th>Acciones</th>
    </tr>
  </thead>
  <tbody>
    <?php while($r = $rows->fetch_assoc()): ?>
      <tr>
        <form method="POST" action="<?= BASE_URL ?>controladores/ControladorProveedores.php?accion=actualizar">
          <td>
            <input type="hidden" name="idProveedores" value="<?= $r['idProveedores'] ?>">
            <input type="text" name="Nombre" value="<?= htmlspecialchars($r['Nombre']) ?>" required>
          </td>
          <td>
            <select name="Origen">
              <option value="">-- Seleccione --</option>
              <?php foreach($PAISES as $p): ?>
                <option value="<?= htmlspecialchars($p) ?>" <?= ($r['Origen']===$p ? 'selected' : '') ?>>
                  <?= htmlspecialchars($p) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </td>
          <td><textarea name="Direccion" rows="2"><?= htmlspecialchars($r['Direccion']) ?></textarea></td>
          <td>
            <button type="submit">💾 Guardar</button>
            <a href="<?= BASE_URL ?>controladores/ControladorProveedores.php?accion=eliminar&id=<?= $r['idProveedores'] ?>"
               onclick="return confirm('¿Eliminar este proveedor (borrado lógico)?')">🗑️ Eliminar</a>
          </td>
        </form>
      </tr>
    <?php endwhile; if(($rows->num_rows ?? 0) === 0): ?>
      <tr><td colspan="4">Sin resultados.</td></tr>
    <?php endif; ?>
  </tbody>
</table>

<?php renderPagination($page, $totalPages, $params); ?>
