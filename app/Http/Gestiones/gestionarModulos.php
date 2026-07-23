<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../conexion.php';
require_once __DIR__ . '/../Modelos/ModeloModulos.php';
require_once __DIR__ . '/../includes/paginacion.php';

$cn = conectar();
$modelo = new ModeloModulos($cn);

$self = 'gestionarModulo';
$fNombre = trim($_GET['nombre'] ?? '');

$perPage = 10;
$page = max(1, (int)($_GET['page'] ?? 1));
$offset = ($page-1)*$perPage;

list($rows, $total) = $modelo->buscarConPaginacion($fNombre, $perPage, $offset);
$totalPages = max(1, (int)ceil($total / $perPage));
$params = ['pagina'=>$self, 'nombre'=>$fNombre];

/* Para el select de módulo padre */
$modsAct = $modelo->obtenerActivos();
$opts = [];
while($m = $modsAct->fetch_assoc()) $opts[] = $m;
?>
<h2>Gestión de Módulos</h2>
<?php if(!empty($_SESSION['msg'])){ echo "<p>".$_SESSION['msg']."</p>"; unset($_SESSION['msg']); } ?>

<p><a href="<?= BASE_URL ?>index.php?pagina=formularioNuevoModulo">➕ Nuevo Módulo</a></p>

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
      <th>Descripción</th>
      <th>Módulo Padre</th>
      <th>Acciones</th>
    </tr>
  </thead>
  <tbody>
    <?php while($r = $rows->fetch_assoc()): ?>
      <tr>
        <form method="POST" action="<?= BASE_URL ?>controladores/ControladorModulos.php?accion=actualizar">
          <td>
            <input type="hidden" name="idModulos" value="<?= $r['idModulos'] ?>">
            <input type="text" name="Nombre" value="<?= htmlspecialchars($r['Nombre']) ?>" required>
          </td>
          <td><textarea name="Descripcion" rows="2"><?= htmlspecialchars($r['Descripcion']) ?></textarea></td>
          <td>
            <select name="id_ModuloPadre">
              <option value="">— Sin padre —</option>
              <?php foreach($opts as $o): if ((int)$o['idModulos'] === (int)$r['idModulos']) continue; ?>
                <option value="<?= $o['idModulos'] ?>" <?= ((int)$r['id_ModuloPadre']===(int)$o['idModulos']?'selected':'') ?>>
                  <?= htmlspecialchars($o['Nombre']) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </td>
          <td>
            <button type="submit">💾 Guardar</button>
            <a href="<?= BASE_URL ?>controladores/ControladorModulos.php?accion=eliminar&id=<?= $r['idModulos'] ?>"
               onclick="return confirm('¿Eliminar este módulo (borrado lógico)?')">🗑️ Eliminar</a>
          </td>
        </form>
      </tr>
    <?php endwhile; if(($rows->num_rows ?? 0)===0): ?>
      <tr><td colspan="4">Sin resultados.</td></tr>
    <?php endif; ?>
  </tbody>
</table>

<?php renderPagination($page, $totalPages, $params); ?>
