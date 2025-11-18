<?php
class ModeloTareas {
  private mysqli $cn;
  public function __construct(mysqli $cn){ $this->cn = $cn; }

  /* Utilidades */
  public function obtenerModulosActivos(): mysqli_result|false {
    return $this->cn->query("SELECT idModulos, Nombre FROM Modulos WHERE Activo = 1 ORDER BY Nombre ASC");
  }

  /* Insertar */
  public function insertar(array $d): bool {
    $sql = "INSERT INTO Tareas
            (id_Modulos, Nombre, Descripcion, Ruta, Icono, Orden, Visible, Activo)
            VALUES (?,?,?,?,?,?,?,1)";
    $st = $this->cn->prepare($sql);
    $st->bind_param(
      "issssii",
      $d['id_Modulos'], $d['Nombre'], $d['Descripcion'], $d['Ruta'],
      $d['Icono'], $d['Orden'], $d['Visible']
    );
    return $st->execute();
  }

  /* Actualizar inline (solo activas) */
  public function actualizarInline(int $id, array $d): bool {
    $sql = "UPDATE Tareas SET
              id_Modulos = ?, Nombre = ?, Descripcion = ?, Ruta = ?, Icono = ?,
              Orden = ?, Visible = ?
            WHERE idTareas = ? AND Activo = 1";
    $st = $this->cn->prepare($sql);
    $st->bind_param(
      "issssiii",
      $d['id_Modulos'], $d['Nombre'], $d['Descripcion'], $d['Ruta'], $d['Icono'],
      $d['Orden'], $d['Visible'], $id
    );
    return $st->execute();
  }

  /* Borrado lógico de tareas + cascada lógica a Roles_Tareas */
  public function eliminarLogicoConCascada(int $id): bool {
    $this->cn->begin_transaction();
    try {
      $st1 = $this->cn->prepare("UPDATE Tareas SET Activo = 0 WHERE idTareas = ?");
      $st1->bind_param("i", $id);
      if (!$st1->execute()) throw new Exception("No se pudo desactivar la tarea.");

      $st2 = $this->cn->prepare("UPDATE Roles_Tareas SET Activo = 0 WHERE id_Tareas = ?");
      $st2->bind_param("i", $id);
      if (!$st2->execute()) throw new Exception("No se pudo desactivar Roles_Tareas.");

      $this->cn->commit();
      return true;
    } catch (Exception $e) {
      $this->cn->rollback();
      return false;
    }
  }

  /* Búsqueda con paginación (solo activas) */
  public function buscarConPaginacion(string $nombre='', int $limit=10, int $offset=0): array {
    $where = " WHERE t.Activo = 1 ";
    $types = ""; $params = [];

    if ($nombre !== '') { $where .= " AND t.Nombre LIKE ? "; $types .= "s"; $params[] = "%$nombre%"; }

    $sqlCount = "SELECT COUNT(*) AS total
                 FROM Tareas t INNER JOIN Modulos m ON m.idModulos = t.id_Modulos
                 $where";
    if ($types) { $stC = $this->cn->prepare($sqlCount); $stC->bind_param($types, ...$params); $stC->execute(); $resC = $stC->get_result(); }
    else { $resC = $this->cn->query($sqlCount); }
    $total = (int)($resC->fetch_assoc()['total'] ?? 0);

    $sql = "SELECT t.*, m.Nombre AS ModuloNombre
            FROM Tareas t INNER JOIN Modulos m ON m.idModulos = t.id_Modulos
            $where
            ORDER BY t.Orden ASC, t.Nombre ASC
            LIMIT ? OFFSET ?";
    $typesData = $types . "ii";
    $paramsData = $params; $paramsData[]=$limit; $paramsData[]=$offset;

    $st = $this->cn->prepare($sql);
    $st->bind_param($typesData, ...$paramsData);
    $st->execute();
    return [$st->get_result(), $total];
  }

  /* Ver todos (incluye inactivas) */
  public function obtenerTodosIncluyendoEliminados(): mysqli_result|false {
    $sql = "SELECT t.*, m.Nombre AS ModuloNombre
            FROM Tareas t INNER JOIN Modulos m ON m.idModulos = t.id_Modulos
            ORDER BY t.Activo DESC, t.Orden ASC, t.Nombre ASC";
    return $this->cn->query($sql);
  }
}
