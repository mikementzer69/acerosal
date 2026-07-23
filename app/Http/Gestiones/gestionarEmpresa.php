<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../conexion.php';
require_once __DIR__ . '/../Modelos/ModeloEmpresa.php';
require_once __DIR__ . '/../includes/paginacion.php';

$cn = conectar();
$modelo = new ModeloEmpresa($cn);

$self = 'gestionarEmpresa';
$fNombre = trim($_GET['nombre'] ?? '');

$perPage = 10;
$page = max(1, (int)($_GET['page'] ?? 1));
$offset = ($page-1)*$perPage;

list($rows, $total) = $modelo->buscarConPaginacion($fNombre, $perPage, $offset);
$totalPages = max(1, (int)ceil($total / $perPage));
$params = ['pagina'=>$self, 'nombre'=>$fNombre];
?>

<h2>Gestión de Empresas</h2>
<?php if(!empty($_SESSION['msg'])){ echo "<p>".$_SESSION['msg']."</p>"; unset($_SESSION['msg']); } ?>

<p><a href="<?= BASE_URL ?>index.php?pagina=formularioNuevaEmpresa">➕ Nueva Empresa</a></p>

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
      <th>NIT</th>
      <th>NRC</th>
      <th>Razón Social</th>
      <th>Dirección</th>
      <th>Teléfono</th>
      <th>Correo</th>
      <th>Acciones</th>
    </tr>
  </thead>
  <tbody>
    <?php while($r = $rows->fetch_assoc()): ?>
      <tr>
        <form method="POST" action="<?= BASE_URL ?>controladores/ControladorEmpresa.php?accion=actualizar">
          <td>
            <input type="hidden" name="idEmpresa" value="<?= $r['idEmpresa'] ?>">
            <input type="text" name="Nombre" value="<?= htmlspecialchars($r['Nombre']) ?>" required>
          </td>
          <td><input type="text" name="NIT" value="<?= htmlspecialchars($r['NIT']) ?>"></td>
          <td><input type="text" name="NRC" value="<?= htmlspecialchars($r['NRC']) ?>"></td>
          <td><input type="text" name="Razon_Social" value="<?= htmlspecialchars($r['Razon_Social']) ?>"></td>
          <td><textarea name="Direccion" rows="2"><?= htmlspecialchars($r['Direccion']) ?></textarea></td>
          <td><input type="text" name="Telefono" value="<?= htmlspecialchars($r['Telefono']) ?>"></td>
          <td><input type="email" name="Correo_Contacto" value="<?= htmlspecialchars($r['Correo_Contacto']) ?>"></td>
          <td>
            <button type="submit">💾 Guardar</button>
            <a href="<?= BASE_URL ?>controladores/ControladorEmpresa.php?accion=eliminar&id=<?= $r['idEmpresa'] ?>"
               onclick="return confirm('¿Eliminar esta empresa (borrado lógico)?')">🗑️ Eliminar</a>
          </td>
        </form>
      </tr>
    <?php endwhile; if(($rows->num_rows ?? 0)===0): ?>
      <tr><td colspan="8">Sin resultados.</td></tr>
    <?php endif; ?>
  </tbody>
</table>

<?php renderPagination($page, $totalPages, $params); ?>
