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

// Roles para filtro
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
<h2>Usuarios (solo lectura)</h2>

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
    <a href="listaUsuarios.php">Limpiar</a>
</form>

<p>Total: <?= $total ?> registro(s)</p>

<table border="1" cellpadding="5">
    <thead>
        <tr>
            <th>Username</th>
            <th>Nombre</th>
            <th>Apellidos</th>
            <th>Email</th>
            <th>Celular</th>
            <th>Rol</th>
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
            </tr>
        <?php endwhile; ?>
        <?php if ($usuarios->num_rows === 0): ?>
            <tr><td colspan="6">Sin resultados.</td></tr>
        <?php endif; ?>
    </tbody>
</table>

<?php renderPagination($page, $totalPages, $params); ?>
