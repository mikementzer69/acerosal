<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../conexion.php';
require_once __DIR__ . '/../Modelos/ModeloRoles.php';
require_once __DIR__ . '/../includes/paginacion.php';

$cn = conectar();
$modelo = new ModeloRoles($cn);

$self = 'gestionarRoles';
$fNombre = trim($_GET['nombre'] ?? '');

$perPage = 10; $page = max(1, (int)($_GET['page'] ?? 1)); $offset = ($page-1)*$perPage;
list($rows, $total) = $modelo->buscarConPaginacion($fNombre, $perPage, $offset);
$totalPages = max(1, (int)ceil($total / $perPage));
$params = ['pagina'=>$self, 'nombre'=>$fNombre];
?>
<h2>Gestión de Roles</h2>
<?php if(!empty($_SESSION['msg'])){ echo "<p>".$_SESSION['msg']."</p>"; unset($_SESSION['msg']); } ?>

<p>
  <a href="<?= BASE_URL ?>index.php?pagina=formularioNuevoRol">➕ Nuevo Rol</a>
</p>

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
      <th>Acciones</th>
    </tr>
  </thead>
  <tbody>
    <?php while($r = $rows->fetch_assoc()): ?>
      <tr>
        <form method="POST" action="<?= BASE_URL ?>controladores/ControladorRoles.php?accion=actualizar">
          <td>
            <input type="hidden" name="idRoles" value="<?= $r['idRoles'] ?>">
            <input type="text" name="Nombre" value="<?= htmlspecialchars($r['Nombre']) ?>" required>
          </td>
          <td><textarea name="Descripcion" rows="2"><?= htmlspecialchars($r['Descripcion']) ?></textarea></td>
          <td>
            <button type="submit">💾 Guardar</button>
            <a href="<?= BASE_URL ?>index.php?pagina=formularioAsignarTareasRol&id=<?= $r['idRoles'] ?>">📌 Tareas</a>
            <a href="<?= BASE_URL ?>controladores/ControladorRoles.php?accion=eliminar&id=<?= $r['idRoles'] ?>"
               onclick="return confirm('¿Eliminar este rol (borrado lógico)?')">🗑️ Eliminar</a>
          </td>
        </form>
      </tr>
    <?php endwhile; if(($rows->num_rows ?? 0)===0): ?>
      <tr><td colspan="3">Sin resultados.</td></tr>
    <?php endif; ?>
  </tbody>
</table>

<?php renderPagination($page, $totalPages, $params); ?>
