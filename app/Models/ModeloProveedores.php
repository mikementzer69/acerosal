<?php
class ModeloProveedores {
  private mysqli $cn;
  public function __construct(mysqli $cn){ $this->cn = $cn; }

  /* Insertar */
  public function insertar(array $d): bool {
    $sql = "INSERT INTO Proveedores (Nombre, Origen, Direccion, Eliminado)
            VALUES (?,?,?,0)";
    $st = $this->cn->prepare($sql);
    $st->bind_param("sss", $d['Nombre'], $d['Origen'], $d['Direccion']);
    return $st->execute();
  }

  /* Actualizar inline solo si no está eliminado */
  public function actualizarInline(int $id, array $d): bool {
    $sql = "UPDATE Proveedores
            SET Nombre = ?, Origen = ?, Direccion = ?
            WHERE idProveedores = ? AND Eliminado = 0";
    $st = $this->cn->prepare($sql);
    $st->bind_param("sssi", $d['Nombre'], $d['Origen'], $d['Direccion'], $id);
    return $st->execute();
  }

  /* Borrado lógico */
  public function eliminarLogico(int $id): bool {
    $st = $this->cn->prepare("UPDATE Proveedores SET Eliminado = 1 WHERE idProveedores = ?");
    $st->bind_param("i", $id);
    return $st->execute();
  }

  /* Listar activos con filtros y paginación */
  public function buscarConPaginacion(string $nombre = '', int $limit = 10, int $offset = 0): array {
    $where = " WHERE Eliminado = 0 ";
    $types = ""; $params = [];

    if ($nombre !== '') { $where .= " AND Nombre LIKE ? "; $types .= "s"; $params[] = "%$nombre%"; }

    $sqlCount = "SELECT COUNT(*) AS total FROM Proveedores $where";
    if ($types) { $stC = $this->cn->prepare($sqlCount); $stC->bind_param($types, ...$params); $stC->execute(); $resC = $stC->get_result(); }
    else { $resC = $this->cn->query($sqlCount); }
    $total = (int)($resC->fetch_assoc()['total'] ?? 0);

    $sql = "SELECT * FROM Proveedores $where ORDER BY Nombre ASC LIMIT ? OFFSET ?";
    $typesData = $types . "ii";
    $paramsData = $params; $paramsData[] = $limit; $paramsData[] = $offset;

    $st = $this->cn->prepare($sql);
    $st->bind_param($typesData, ...$paramsData);
    $st->execute();
    return [$st->get_result(), $total];
  }

    public function catalogoSelect(): mysqli_result|false {
    // Notas: según tu modelo actual, la tabla usa campos: idProveedores, Nombre
    // y manejas Eliminado=0 como activos.
    $sql = "SELECT idProveedores AS id, Nombre AS nombre
            FROM Proveedores
            WHERE Eliminado = 0
            ORDER BY Nombre ASC";
    return $this->cn->query($sql);
  }

  /* Ver todos incluyendo eliminados (para futuros reportes) */
  public function obtenerTodosIncluyendoEliminados(): mysqli_result|false {
    return $this->cn->query("SELECT * FROM Proveedores ORDER BY Eliminado ASC, Nombre ASC");
  }
}
