<?php
class ModeloUsuarios {
  private mysqli $cn;
  public function __construct(mysqli $cn){ $this->cn = $cn; }

  public function existeUsername(string $u): bool {
    $st = $this->cn->prepare("SELECT 1 FROM Usuarios WHERE Username = ? LIMIT 1");
    $st->bind_param("s", $u);
    $st->execute();
    $st->store_result();
    return $st->num_rows > 0;
  }

  public function insertar(array $d): bool {
    $sql = "INSERT INTO Usuarios
            (Username, Password, Nombre, Apellidos, Email, Celular, id_Roles, Activo)
            VALUES (?,?,?,?,?,?,?,1)";
    $st = $this->cn->prepare($sql);
    $st->bind_param(
      "ssssssi",
      $d['Username'], $d['Password'], $d['Nombre'], $d['Apellidos'],
      $d['Email'], $d['Celular'], $d['id_Roles']
    );
    return $st->execute();
  }

  public function actualizar(int $id, array $d): bool {
    // Construye dinámicamente por si viene Password o no
    $fields = "Nombre = ?, Apellidos = ?, Email = ?, Celular = ?, id_Roles = ?";
    $types = "ssssi";
    $params = [$d['Nombre'], $d['Apellidos'], $d['Email'], $d['Celular'], $d['id_Roles']];

    if (isset($d['Password'])) {
      $fields .= ", Password = ?";
      $types  .= "s";
      $params[] = $d['Password'];
    }

    $sql = "UPDATE Usuarios SET $fields WHERE idUsuarios = ? AND Activo = 1";
    $types .= "i";
    $params[] = $id;

    $st = $this->cn->prepare($sql);
    $st->bind_param($types, ...$params);
    return $st->execute();
  }

  public function eliminarLogico(int $id): bool {
    $st = $this->cn->prepare("UPDATE Usuarios SET Activo = 0 WHERE idUsuarios = ?");
    $st->bind_param("i", $id);
    return $st->execute();
  }

  // Para listas (solo activos) con paginación
  public function buscarConPaginacion(string $username = '', string $email = '', ?int $idRol = null, int $limit = 10, int $offset = 0): array {
    $where = " WHERE u.Activo = 1 ";
    $types = ""; $params = [];

    if ($username !== '') { $where .= " AND u.Username LIKE ? "; $types .= "s"; $params[] = "%$username%"; }
    if ($email !== '')    { $where .= " AND u.Email LIKE ? ";    $types .= "s"; $params[] = "%$email%"; }
    if (!is_null($idRol) && $idRol > 0) { $where .= " AND u.id_Roles = ? "; $types .= "i"; $params[] = $idRol; }

    $sqlCount = "SELECT COUNT(*) AS total
                 FROM Usuarios u
                 INNER JOIN Roles r ON r.idRoles = u.id_Roles
                 $where";
    if ($types) { $stC = $this->cn->prepare($sqlCount); $stC->bind_param($types, ...$params); $stC->execute(); $resC = $stC->get_result(); }
    else { $resC = $this->cn->query($sqlCount); }
    $total = (int)($resC->fetch_assoc()['total'] ?? 0);

    $sql = "SELECT u.*, r.Nombre AS RolNombre
            FROM Usuarios u
            INNER JOIN Roles r ON r.idRoles = u.id_Roles
            $where
            ORDER BY u.Username ASC
            LIMIT ? OFFSET ?";
    $typesData = $types . "ii";
    $paramsData = $params; $paramsData[] = $limit; $paramsData[] = $offset;

    $st = $this->cn->prepare($sql);
    $st->bind_param($typesData, ...$paramsData);
    $st->execute();
    return [$st->get_result(), $total];
  }

  // Para futuras necesidades: ver TODOS, incluidos inactivos
  public function obtenerTodosIncluyendoEliminados(): mysqli_result|false {
    $sql = "SELECT u.*, r.Nombre AS RolNombre
            FROM Usuarios u
            INNER JOIN Roles r ON r.idRoles = u.id_Roles
            ORDER BY u.Activo DESC, u.Username ASC";
    return $this->cn->query($sql);
  }

  public function obtenerRolesActivos(): mysqli_result|false {
    return $this->cn->query("SELECT idRoles, Nombre FROM Roles WHERE Activo = 1 ORDER BY Nombre ASC");
  }

  public function obtenerPorId(int $id): ?array {
    $st = $this->cn->prepare("SELECT * FROM Usuarios WHERE idUsuarios = ?");
    $st->bind_param("i", $id);
    $st->execute();
    $res = $st->get_result();
    return $res->num_rows ? $res->fetch_assoc() : null;
  }
}
