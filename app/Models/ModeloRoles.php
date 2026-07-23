<?php
class ModeloRoles {
  private mysqli $cn;
  public function __construct(mysqli $cn){ $this->cn = $cn; }

  /* --------- CRUD Roles ---------- */
  public function insertar(string $nombre, string $descripcion): int|false {
    $st = $this->cn->prepare("INSERT INTO Roles (Nombre, Descripcion, Activo) VALUES (?,?,1)");
    $st->bind_param("ss", $nombre, $descripcion);
    if ($st->execute()) return $this->cn->insert_id;
    return false;
  }

  public function actualizarInline(int $id, string $nombre, string $descripcion): bool {
    $st = $this->cn->prepare("UPDATE Roles SET Nombre = ?, Descripcion = ? WHERE idRoles = ? AND Activo = 1");
    $st->bind_param("ssi", $nombre, $descripcion, $id);
    return $st->execute();
  }

  public function eliminarLogico(int $id): bool {
    $st = $this->cn->prepare("UPDATE Roles SET Activo = 0 WHERE idRoles = ?");
    $st->bind_param("i", $id);
    return $st->execute();
  }

  /* --------- Listas ---------- */
  public function buscarConPaginacion(string $nombre='', int $limit=10, int $offset=0): array {
    $where = " WHERE Activo = 1 ";
    $types = ""; $params = [];
    if ($nombre !== '') { $where .= " AND Nombre LIKE ? "; $types .= "s"; $params[] = "%$nombre%"; }

    $sqlCount = "SELECT COUNT(*) AS total FROM Roles $where";
    if ($types) { $stC = $this->cn->prepare($sqlCount); $stC->bind_param($types, ...$params); $stC->execute(); $resC = $stC->get_result(); }
    else { $resC = $this->cn->query($sqlCount); }
    $total = (int)($resC->fetch_assoc()['total'] ?? 0);

    $sql = "SELECT * FROM Roles $where ORDER BY Nombre ASC LIMIT ? OFFSET ?";
    $typesData = $types . "ii";
    $paramsData = $params; $paramsData[]=$limit; $paramsData[]=$offset;
    $st = $this->cn->prepare($sql);
    $st->bind_param($typesData, ...$paramsData);
    $st->execute();
    return [$st->get_result(), $total];
  }

  public function obtenerTodosIncluyendoEliminados(): mysqli_result|false {
    return $this->cn->query("SELECT * FROM Roles ORDER BY Activo DESC, Nombre ASC");
  }

  public function obtenerPorId(int $id): ?array {
    $st = $this->cn->prepare("SELECT * FROM Roles WHERE idRoles = ?");
    $st->bind_param("i", $id);
    $st->execute();
    $res = $st->get_result();
    return $res->num_rows ? $res->fetch_assoc() : null;
  }

  /* --------- Módulos y Tareas (asignación) ---------- */
  public function obtenerModulosActivos(): mysqli_result|false {
    return $this->cn->query("SELECT idModulos, Nombre, Descripcion, id_ModuloPadre FROM Modulos WHERE Activo = 1 ORDER BY Nombre ASC");
  }

  public function obtenerTareasActivas(): mysqli_result|false {
    $sql = "SELECT t.idTareas, t.id_Modulos, t.Nombre, t.Descripcion
            FROM Tareas t
            INNER JOIN Modulos m ON m.idModulos = t.id_Modulos
            WHERE t.Activo = 1 AND m.Activo = 1
            ORDER BY t.Nombre ASC";
    return $this->cn->query($sql);
  }

  public function obtenerTareasDeRol(int $idRol): array {
    $st = $this->cn->prepare("SELECT id_Tareas FROM Roles_Tareas WHERE id_Roles = ? AND Activo = 1");
    $st->bind_param("i", $idRol);
    $st->execute();
    $res = $st->get_result();
    $ids = [];
    while($r = $res->fetch_assoc()) $ids[] = (int)$r['id_Tareas'];
    return $ids;
  }

  /**
   * Sincroniza las tareas de un rol con el arreglo $idsTareasSeleccionadas:
   * - Marca Activo=1 para las seleccionadas (insertando si no existen)
   * - Marca Activo=0 para las no seleccionadas que estuvieran activas
   */
  public function guardarAsignacionTareas(int $idRol, array $idsTareasSeleccionadas): bool {
    $this->cn->begin_transaction();
    try {
      // 1) Desactivar todas las actuales
      $stOff = $this->cn->prepare("UPDATE Roles_Tareas SET Activo = 0 WHERE id_Roles = ?");
      $stOff->bind_param("i", $idRol);
      if (!$stOff->execute()) throw new Exception("No se pudo desactivar tareas previas.");

      if (!empty($idsTareasSeleccionadas)) {
        // 2) Activar/Insertar seleccionadas
        $stUp = $this->cn->prepare("
          INSERT INTO Roles_Tareas (id_Roles, id_Tareas, Activo)
          VALUES (?, ?, 1)
          ON DUPLICATE KEY UPDATE Activo = VALUES(Activo)
        ");
        foreach ($idsTareasSeleccionadas as $idT) {
          $idT = (int)$idT;
          $stUp->bind_param("ii", $idRol, $idT);
          if (!$stUp->execute()) throw new Exception("No se pudo asignar tarea $idT.");
        }
      }

      $this->cn->commit();
      return true;
    } catch (Exception $e) {
      $this->cn->rollback();
      return false;
    }
  }
}
