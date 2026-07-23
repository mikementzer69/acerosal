<?php
class ModeloCostos {
  private mysqli $cn;
  public function __construct(mysqli $cn){ $this->cn = $cn; }

  /* Insertar */
  public function insertar(array $d): bool {
    $st = $this->cn->prepare("INSERT INTO Costos (Nombre, Descripcion, Activo) VALUES (?, ?, 1)");
    $st->bind_param("ss", $d['Nombre'], $d['Descripcion']);
    return $st->execute();
  }

  /* Actualizar inline (solo activos) */
  public function actualizarInline(int $id, array $d): bool {
    $st = $this->cn->prepare("UPDATE Costos SET Nombre = ?, Descripcion = ? WHERE idCostos = ? AND Activo = 1");
    $st->bind_param("ssi", $d['Nombre'], $d['Descripcion'], $id);
    return $st->execute();
  }

  /* Borrado lógico */
  public function eliminarLogico(int $id): bool {
    $st = $this->cn->prepare("UPDATE Costos SET Activo = 0 WHERE idCostos = ?");
    $st->bind_param("i", $id);
    return $st->execute();
  }

  /* Lista activos con filtro + paginación */
  public function buscarConPaginacion(string $nombre = '', int $limit = 10, int $offset = 0): array {
    $where = " WHERE Activo = 1 ";
    $types = ""; $params = [];

    if ($nombre !== '') { $where .= " AND Nombre LIKE ? "; $types .= "s"; $params[] = "%$nombre%"; }

    $sqlCount = "SELECT COUNT(*) AS total FROM Costos $where";
    if ($types) { $stC = $this->cn->prepare($sqlCount); $stC->bind_param($types, ...$params); $stC->execute(); $resC = $stC->get_result(); }
    else { $resC = $this->cn->query($sqlCount); }
    $total = (int)($resC->fetch_assoc()['total'] ?? 0);

    $sql = "SELECT * FROM Costos $where ORDER BY Nombre ASC LIMIT ? OFFSET ?";
    $typesData = $types . "ii";
    $paramsData = $params; $paramsData[] = $limit; $paramsData[] = $offset;

    $st = $this->cn->prepare($sql);
    $st->bind_param($typesData, ...$paramsData);
    $st->execute();
    return [$st->get_result(), $total];
  }

  public function catalogoSelect(): mysqli_result|false {
    // En tu modelo, la tabla usa: idCostos, Nombre, Activo (1 = activo).
    $sql = "SELECT idCostos AS id, Nombre AS nombre
            FROM Costos
            WHERE Activo = 1
            ORDER BY Nombre ASC";
    return $this->cn->query($sql);
  }

  /* Ver todos incluyendo eliminados (para reportes) */
  public function obtenerTodosIncluyendoEliminados(): mysqli_result|false {
    return $this->cn->query("SELECT * FROM Costos ORDER BY Activo DESC, Nombre ASC");
  }
}
