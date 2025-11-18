<?php
session_start();
require_once __DIR__ . '/../conexion.php';
require_once __DIR__ . '/../Modelos/ModeloUsuarios.php';

$conexion = conectar();
require_once __DIR__ . '/../includes/paginacion.php'; // (no se usa aquí, pero si lo usas en más vistas)

class ModeloUsuarios {
    private $conexion;
    public function __construct($c){ $this->conexion = $c; }
    public function obtenerPorId($id){
        $stmt = $this->conexion->prepare("SELECT * FROM Usuarios WHERE idUsuarios = ? LIMIT 1");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result();
    }
}

$modelo = new ModeloUsuarios($conexion);

$id = intval($_GET['id'] ?? 0);
$u  = $modelo->obtenerPorId($id);
if ($u->num_rows === 0) { echo "<p>Usuario no encontrado.</p>"; exit; }
$usuario = $u->fetch_assoc();

$roles = $conexion->query("SELECT idRoles, Nombre FROM Roles WHERE Activo = 1 ORDER BY Nombre ASC");
?>
<h2>Editar Usuario</h2>

<?php if (!empty($_SESSION['msg'])) { echo "<p>".$_SESSION['msg']."</p>"; unset($_SESSION['msg']); } ?>

<form action="../Controladores/ControladorUsuarios.php?accion=actualizar" method="POST" onsubmit="return validarPasswordsOpcional()">
    <input type="hidden" name="idUsuarios" value="<?= $usuario['idUsuarios'] ?>">

    <fieldset>
        <legend>Datos de Acceso</legend>
        <div>
            <label for="Username">Username:</label>
            <input type="text" name="Username" id="Username" value="<?= htmlspecialchars($usuario['Username']) ?>" required>
        </div>
        <div>
            <label for="Password">Nueva Contraseña (opcional):</label>
            <input type="password" name="Password" id="Password">
        </div>
        <div>
            <label for="Password2">Repetir Contraseña:</label>
            <input type="password" name="Password2" id="Password2">
        </div>
    </fieldset>

    <fieldset>
        <legend>Datos Personales</legend>
        <div>
            <label for="Nombre">Nombre:</label>
            <input type="text" name="Nombre" id="Nombre" value="<?= htmlspecialchars($usuario['Nombre']) ?>">
        </div>
        <div>
            <label for="Apellidos">Apellidos:</label>
            <input type="text" name="Apellidos" id="Apellidos" value="<?= htmlspecialchars($usuario['Apellidos']) ?>">
        </div>
        <div>
            <label for="Email">Email:</label>
            <input type="email" name="Email" id="Email" value="<?= htmlspecialchars($usuario['Email']) ?>">
        </div>
        <div>
            <label for="Celular">Celular:</label>
            <input type="text" name="Celular" id="Celular" value="<?= htmlspecialchars($usuario['Celular']) ?>">
        </div>
        <div>
            <label for="id_Roles">Rol:</label>
            <select name="id_Roles" id="id_Roles" required>
                <?php while ($r = $roles->fetch_assoc()): ?>
                    <option value="<?= $r['idRoles'] ?>" <?= ($usuario['id_Roles']==$r['idRoles']?'selected':'') ?>>
                        <?= htmlspecialchars($r['Nombre']) ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>
    </fieldset>

    <button type="submit">Actualizar Usuario</button>
    <a href="../gestiones/gestionarUsuarios.php">Cancelar</a>
</form>

<script>
function validarPasswordsOpcional() {
    const p1 = document.getElementById('Password').value;
    const p2 = document.getElementById('Password2').value;
    if ((p1 || p2) && p1 !== p2) {
        alert('Las contraseñas no coinciden');
        return false;
    }
    return true;
}
</script>
