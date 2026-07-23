<?php
session_start();
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../conexion.php';
require_once __DIR__ . '/../Modelos/ModeloRoles.php';

$cn = conectar();
$modelo = new ModeloRoles($cn);

$id = (int)($_GET['id'] ?? 0);
$rol = $modelo->obtenerPorId($id);
if (!$rol) { echo "<p>Rol no encontrado.</p>"; return; }

$ids = $modelo->obtenerTareasDeRol($id);
if (!empty($ids)) {
  $in  = implode(',', array_fill(0, count($ids), '?'));
  $types = str_repeat('i', count($ids));
  // obtener nombres de tareas con módulo
  $sql = "SELECT t.idTareas, t.Nombre AS TareaNombre, m.Nombre AS ModuloNombre
          FROM Tareas t
          INNER JOIN Modulos m ON m.idModulos = t.id_Modulos
          WHERE t.idTareas IN ($in)
          ORDER BY m.Nombre, t.Nombre";
  $st = $cn->prepare($sql);
  $st->bind_param($types, ...$ids);
  $st->execute();
  $res = $st->get_result();
} else {
  $res = false;
}
?>
<h2>Detalle del Rol</h2>
<p><strong>Nombre:</strong> <?= htmlspecialchars($rol['Nombre']) ?></p>
<p><strong>Descripción:</strong> <?= nl2br(htmlspecialchars($rol['Descripcion'])) ?></p>

<h3>Tareas asignadas</h3>
<?php if ($res && $res->num_rows): ?>
  <ul>
    <?php while($r = $res->fetch_assoc()): ?>
      <li><strong><?= htmlspecialchars($r['ModuloNombre']) ?>:</strong> <?= htmlspecialchars($r['TareaNombre']) ?></li>
    <?php endwhile; ?>
  </ul>
<?php else: ?>
  <p>Este rol no tiene tareas asignadas.</p>
<?php endif; ?>

<p style="margin-top:10px;">
  <a href="<?= BASE_URL ?>index.php?pagina=listaRoles">Volver a la lista</a> |
  <a href="<?= BASE_URL ?>index.php?pagina=formularioAsignarTareasRol&id=<?= $id ?>">Editar tareas</a>
</p>
