<?php
class ModeloModulos {
  private mysqli $cn;
  public function __construct(mysqli $cn){ $this->cn = $cn; }

  /* Insertar */
  public function insertar(array $d): bool {
    $sql = "INSERT INTO Modulos (Nombre, Descripcion, Activo, id_ModuloPadre)
            VALUES (?, ?, 1, ?)";
    $st = $this->cn->prepare($sql);
    // id_ModuloPadre puede ser null
    if ($d['id_ModuloPadre'] === null) {
      $null = null;
      $st->bind_param("ssi", $d['Nombre'], $d['Descripcion'], $null);
    } else {
      $st->bind_param("ssi", $d['Nombre'], $d['Descripcion'], $d['id_ModuloPadre']);
    }
    return $st->execute();
  }

  /* Actualizar inline (solo activos) */
  public function actualizarInline(int $id, array $d): bool {
    $sql = "UPDATE Modulos
            SET Nombre = ?, Descripcion = ?, id_ModuloPadre = ?
            WHERE idModulos = ? AND Activo = 1";
    $st = $this->cn->prepare($sql);
    if ($d['id_ModuloPadre'] === null) {
      $null = null;
      $st->bind_param("ssii", $d['Nombre'], $d['Descripcion'], $null, $id);
    } else {
      $st->bind_param("ssii", $d['Nombre'], $d['Descripcion'], $d['id_ModuloPadre'], $id);
    }
    return $st->execute();
  }

  /* Borrado lógico */
  public function eliminarLogico(int $id): bool {
    $st = $this->cn->prepare("UPDATE Modulos SET Activo = 0 WHERE idModulos = ?");
    $st->bind_param("i", $id);
    return $st->execute();
  }

  /* Listar activos con filtro+pag */
  public function buscarConPaginacion(string $nombre='', int $limit=10, int $offset=0): array {
    $where = " WHERE Activo = 1 ";
    $types = ""; $params = [];

    if ($nombre !== '') { $where .= " AND Nombre LIKE ? "; $types .= "s"; $params[] = "%$nombre%"; }

    $sqlCount = "SELECT COUNT(*) AS total FROM Modulos $where";
    if ($types) { $stC=$this->cn->prepare($sqlCount); $stC->bind_param($types, ...$params); $stC->execute(); $resC=$stC->get_result(); }
    else { $resC = $this->cn->query($sqlCount); }
    $total = (int)($resC->fetch_assoc()['total'] ?? 0);

    $sql = "SELECT * FROM Modulos $where ORDER BY Nombre ASC LIMIT ? OFFSET ?";
    $typesData = $types . "ii";
    $paramsData = $params; $paramsData[]=$limit; $paramsData[]=$offset;

    $st = $this->cn->prepare($sql);
    $st->bind_param($typesData, ...$paramsData);
    $st->execute();
    return [$st->get_result(), $total];
  }

  /* Ver TODOS (incluye inactivos) */
  public function obtenerTodosIncluyendoEliminados(): mysqli_result|false {
    return $this->cn->query("SELECT * FROM Modulos ORDER BY Activo DESC, Nombre ASC");
  }

  /* Para selects de módulo padre (solo activos) */
  public function obtenerActivos(): mysqli_result|false {
    return $this->cn->query("SELECT idModulos, Nombre FROM Modulos WHERE Activo = 1 ORDER BY Nombre ASC");
  }
}
