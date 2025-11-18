<?php
class ModeloProductos {
  private mysqli $cn;
  public function __construct(mysqli $cn){ $this->cn = $cn; }

  /* Utilidades para selects */
  public function obtenerFamiliasActivas(): mysqli_result|false {
    return $this->cn->query("SELECT idFamilia, Nombre FROM Familia WHERE Activo = 1 ORDER BY Nombre ASC");
  }

  /* Insertar */
public function insertar(array $d): bool {
    $sql = "INSERT INTO Productos
        (id_Familia, Codigo, Descripcion, Unidad_Medida, Milimetros, Pulgadas, Tolerancia, Peso_LB_MTS, Precio_Venta_sin_IVA, Precio_Fijo, Eliminado)
        VALUES (?,?,?,?,?,?,?,?,?,?,0)";

    $st = $this->cn->prepare($sql);
    if (!$st) {
        throw new RuntimeException("Prepare insertar: " . $this->cn->error);
    }

    // Casts defensivos
    $idFamilia   = (int)($d['id_Familia'] ?? 0);
    $codigo      = (string)($d['Codigo'] ?? '');
    $desc        = (string)($d['Descripcion'] ?? '');
    $unidad      = (string)($d['Unidad_Medida'] ?? '');
    $mm          = (float)($d['Milimetros'] ?? 0);
    $inches      = (float)($d['Pulgadas'] ?? 0);
    $tol         = (float)($d['Tolerancia'] ?? 0);
    $pesoLbMts   = (float)($d['Peso_LB_MTS'] ?? 0);
    $precioSinIva= (float)($d['Precio_Venta_sin_IVA'] ?? 0);
    $precioFijo  = (int)  ($d['Precio_Fijo'] ?? 0); // 0/1

    // Tipos: i s s s d d d d d i  => 10 parámetros
    if (!$st->bind_param(
        "isssdddddi",
        $idFamilia, $codigo, $desc, $unidad,
        $mm, $inches, $tol,
        $pesoLbMts, $precioSinIva, $precioFijo
    )) {
        throw new RuntimeException("bind_param insertar: " . $st->error);
    }

    $ok = $st->execute();
    if (!$ok) {
        throw new RuntimeException("execute insertar: " . $st->error);
    }
    $st->close();
    return true;
}


  /* Actualizar inline (solo activos) */
  public function actualizarInline(int $id, array $d): bool {
    $sql = "UPDATE Productos SET
      id_Familia=?, Codigo=?, Descripcion=?, Unidad_Medida=?, Milimetros=?, Pulgadas=?, Tolerancia=?, Peso_LB_MTS=?, Precio_Venta_sin_IVA=?, Precio_Fijo=?
      WHERE idProductos=? AND Eliminado = 0";
    $st = $this->cn->prepare($sql);
    $st->bind_param(
      "isssddd d d ii",
      $d['id_Familia'], $d['Codigo'], $d['Descripcion'], $d['Unidad_Medida'],
      $d['Milimetros'], $d['Pulgadas'], $d['Tolerancia'],
      $d['Peso_LB_MTS'], $d['Precio_Venta_sin_IVA'], $d['Precio_Fijo'],
      $id
    );
    return $st->execute();
  }

  /* Borrado lógico */
  public function eliminarLogico(int $id): bool {
    $st = $this->cn->prepare("UPDATE Productos SET Eliminado = 1 WHERE idProductos = ?");
    $st->bind_param("i", $id);
    return $st->execute();
  }

  /* Listar activos con filtros y paginación */
  public function buscarConPaginacion(
    string $codigo = '',
    string $texto = '',
    ?int $idFamilia = null,
    int $limit = 10,
    int $offset = 0
  ): array {
    $where = " WHERE p.Eliminado = 0 ";
    $types = ""; $params = [];

    if ($codigo !== '')   { $where .= " AND p.Codigo LIKE ? ";       $types.="s"; $params[]="%$codigo%"; }
    if ($texto !== '')    { $where .= " AND p.Descripcion LIKE ? ";  $types.="s"; $params[]="%$texto%"; }
    if (!is_null($idFamilia) && $idFamilia>0) {
      $where .= " AND p.id_Familia = ? "; $types.="i"; $params[] = $idFamilia;
    }

    $sqlCount = "SELECT COUNT(*) AS total
                 FROM Productos p
                 INNER JOIN Familia f ON f.idFamilia = p.id_Familia
                 $where";
    if ($types) { $stC=$this->cn->prepare($sqlCount); $stC->bind_param($types, ...$params); $stC->execute(); $resC=$stC->get_result(); }
    else { $resC=$this->cn->query($sqlCount); }
    $total = (int)($resC->fetch_assoc()['total'] ?? 0);

    $sql = "SELECT p.*, f.Nombre AS FamiliaNombre
            FROM Productos p
            INNER JOIN Familia f ON f.idFamilia = p.id_Familia
            $where
            ORDER BY p.Codigo ASC, p.Descripcion ASC
            LIMIT ? OFFSET ?";
    $typesData = $types . "ii";
    $paramsData = $params; $paramsData[]=$limit; $paramsData[]=$offset;

    $st = $this->cn->prepare($sql);
    $st->bind_param($typesData, ...$paramsData);
    $st->execute();
    return [$st->get_result(), $total];
  }

  public function catalogoSelect(): mysqli_result|false {
    // En tu modelo de productos manejas Eliminado=0 como activos visibles.
    $sql = "SELECT idProductos AS id, Descripcion AS nombre
            FROM Productos
            WHERE Eliminado = 0
            ORDER BY Codigo ASC, Descripcion ASC";
    return $this->cn->query($sql);
  }

  /* Ver todos (incluye eliminados) */
  public function obtenerTodosIncluyendoEliminados(): mysqli_result|false {
    $sql = "SELECT p.*, f.Nombre AS FamiliaNombre
            FROM Productos p INNER JOIN Familia f ON f.idFamilia = p.id_Familia
            ORDER BY p.Eliminado ASC, p.Codigo ASC, p.Descripcion ASC";
    return $this->cn->query($sql);
  }
}
