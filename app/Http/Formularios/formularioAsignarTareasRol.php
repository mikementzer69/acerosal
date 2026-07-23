<?php
session_start();
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../conexion.php';
require_once __DIR__ . '/../Modelos/ModeloRoles.php';

$cn = conectar();
$modelo = new ModeloRoles($cn);

$idRol = (int)($_GET['id'] ?? 0);
$rol = $modelo->obtenerPorId($idRol);
if (!$rol) { echo "<p>Rol no encontrado.</p>"; return; }

$modsRes = $modelo->obtenerModulosActivos();
$tasksRes = $modelo->obtenerTareasActivas();
$seleccionadas = $modelo->obtenerTareasDeRol($idRol);

/* Construir árbol sencillo: modulos[id] = {..., hijos[], tareas[]} */
$modulos = [];
while($m = $modsRes->fetch_assoc()){
  $m['hijos'] = [];
  $m['tareas'] = [];
  $modulos[(int)$m['idModulos']] = $m;
}
foreach ($modulos as $id=>$m) {
  $padre = (int)($m['id_ModuloPadre'] ?? 0);
  if ($padre && isset($modulos[$padre])) {
    $modulos[$padre]['hijos'][] = &$modulos[$id];
  }
}
$roots = [];
foreach ($modulos as $id=>$m) {
  if (empty($m['id_ModuloPadre'])) $roots[] = &$modulos[$id];
}
while($t = $tasksRes->fetch_assoc()){
  $mid = (int)$t['id_Modulos'];
  if (isset($modulos[$mid])) $modulos[$mid]['tareas'][] = $t;
}

function renderModulo($m, $seleccionadas, $nivel=0){
  $pad = 20 * $nivel;
  $mid = (int)$m['idModulos'];
  ?>
  <div style="margin-left: <?= $pad ?>px; border-left: 2px solid #eee; padding-left:8px; margin-top:8px;">
    <label>
      <input type="checkbox" class="chk-mod" data-mod="<?= $mid ?>">
      <strong><?= htmlspecialchars($m['Nombre']) ?></strong>
    </label>
    <?php if (!empty($m['tareas'])): ?>
      <div style="margin-left:14px;">
        <?php foreach($m['tareas'] as $t): $tid=(int)$t['idTareas']; ?>
          <div>
            <label>
              <input type="checkbox" name="tareas[]" value="<?= $tid ?>" class="chk-task" data-mod="<?= $mid ?>"
                <?= in_array($tid, $seleccionadas, true) ? 'checked' : '' ?>>
              <?= htmlspecialchars($t['Nombre']) ?>
            </label>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
    <?php if (!empty($m['hijos'])): ?>
      <?php foreach($m['hijos'] as $h) renderModulo($h, $seleccionadas, $nivel+1); ?>
    <?php endif; ?>
  </div>
  <?php
}
?>
<h2>Asignar Tareas al Rol: <?= htmlspecialchars($rol['Nombre']) ?></h2>
<?php if(!empty($_SESSION['msg'])){ echo "<p>".$_SESSION['msg']."</p>"; unset($_SESSION['msg']); } ?>

<form method="POST" action="<?= BASE_URL ?>controladores/ControladorRoles.php?accion=guardar_asignacion" id="formAsignar">
  <input type="hidden" name="idRol" value="<?= $idRol ?>">

  <?php foreach($roots as $r) renderModulo($r, $seleccionadas, 0); ?>

  <div style="margin-top:12px;">
    <button type="submit">Guardar asignación</button>
    <a href="<?= BASE_URL ?>index.php?pagina=listaRoles">Volver</a>
  </div>
</form>

<script>
  // Al marcar un módulo, marca/desmarca todas sus tareas descendientes visibles
  document.querySelectorAll('.chk-mod').forEach(chk => {
    chk.addEventListener('change', e => {
      const mod = e.target.getAttribute('data-mod');
      const checked = e.target.checked;
      document.querySelectorAll('.chk-task[data-mod="'+mod+'"]').forEach(t => t.checked = checked);
      // Si hay submódulos anidados, propagar (marcamos sus .chk-mod también)
      document.querySelectorAll('.chk-mod[data-mod]').forEach(cm => {
        // no tenemos data de jerarquía en DOM, así que asumimos que el usuario marcará submódulos manualmente si los hay
      });
    });
  });
</script>
