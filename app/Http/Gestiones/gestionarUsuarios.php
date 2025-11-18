<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../conexion.php';
require_once __DIR__ . '/../Modelos/ModeloUsuarios.php';
require_once __DIR__ . '/../includes/paginacion.php';

$cn      = conectar();
$modelo  = new ModeloUsuarios($cn);

// --- Filtros ---
$fUsername = trim($_GET['username'] ?? '');
$fEmail    = trim($_GET['email'] ?? '');
$fRol      = ($_GET['id_Roles'] ?? '') !== '' ? intval($_GET['id_Roles']) : null;

// Roles para filtro (solo activos)
$roles = $cn->query("SELECT idRoles, Nombre FROM Roles WHERE Activo = 1 ORDER BY Nombre ASC");

// --- Paginación ---
$perPage = 10;
$page    = max(1, intval($_GET['page'] ?? 1));
$offset  = ($page - 1) * $perPage;

// Obtener datos + total desde el MODELO
list($usuarios, $total) = $modelo->buscarConPaginacion($fUsername, $fEmail, $fRol, $perPage, $offset);
$totalPages = max(1, (int)ceil($total / $perPage));

// Parámetros para el helper (preservar filtros)
$params = [
    'username' => $fUsername,
    'email'    => $fEmail,
    'id_Roles' => $fRol,
];
?>
<h2>Gestión de Usuarios</h2>

<?php if (!empty($_SESSION['msg'])) { echo "<p>".$_SESSION['msg']."</p>"; unset($_SESSION['msg']); } ?>

<form method="GET">
    <label>Username:</label>
    <input type="text" name="username" value="<?= htmlspecialchars($fUsername) ?>">

    <label>Email:</label>
    <input type="text" name="email" value="<?= htmlspecialchars($fEmail) ?>">

    <label>Rol:</label>
    <select name="id_Roles">
        <option value="">-- Todos --</option>
        <?php while ($r = $roles->fetch_assoc()): ?>
            <option value="<?= $r['idRoles'] ?>" <?= ($fRol==$r['idRoles']?'selected':'') ?>>
                <?= htmlspecialchars($r['Nombre']) ?>
            </option>
        <?php endwhile; ?>
    </select>

    <button type="submit">Buscar</button>
    <a href="gestionarUsuarios.php">Limpiar</a>
</form>

<p>
    <a href="../formularios/formularioNuevoUsuario.php">➕ Nuevo Usuario</a>
    &nbsp;|&nbsp; Total activos: <?= $total ?>
</p>

<table border="1" cellpadding="5">
    <thead>
        <tr>
            <th>Username</th>
            <th>Nombre</th>
            <th>Apellidos</th>
            <th>Email</th>
            <th>Celular</th>
            <th>Rol</th>
            <th>Acciones</th>
        </tr>
    </thead>
    <tbody>
        <?php while ($u = $usuarios->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($u['Username']) ?></td>
                <td><?= htmlspecialchars($u['Nombre']) ?></td>
                <td><?= htmlspecialchars($u['Apellidos']) ?></td>
                <td><?= htmlspecialchars($u['Email'] ?? '') ?></td>
                <td><?= htmlspecialchars($u['Celular'] ?? '') ?></td>
                <td><?= htmlspecialchars($u['RolNombre']) ?></td>
                <td>
                    <a href="../formularios/formularioEditarUsuario.php?id=<?= $u['idUsuarios'] ?>">✏️ Editar</a>
                    |
                    <a href="<?= BASE_URL ?>controladores/ControladorUsuarios.php?accion=eliminar&id=<?= $r['idUsuarios'] ?>"
                        onclick="return confirm('¿Eliminar este usuario (borrado lógico)?')">🗑️ Eliminar</a>
                </td>
            </tr>
        <?php endwhile; ?>
        <?php if ($usuarios->num_rows === 0): ?>
            <tr><td colspan="7">Sin resultados.</td></tr>
        <?php endif; ?>
    </tbody>
</table>

<?php renderPagination($page, $totalPages, $params); ?>
