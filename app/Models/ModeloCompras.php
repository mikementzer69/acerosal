
<?php
// ============================================================
// MODELO: COMPRAS
// Maneja todas las operaciones CRUD relacionadas con Compras,
// Detalle_Compras, Costos_Adicionales y Detalle_Compras_Familias
// ============================================================

require_once __DIR__ . '/../conexion.php';

class ModeloCompras
{
    private mysqli $conexion;

    // ------------------------------------------------------------
    // 1️⃣ CONSTRUCTOR: Conecta automáticamente a la BD
    // ------------------------------------------------------------
    public function __construct()
    {
        $this->conexion = conectar();
    }


// ------------------------------------------------------------
// 2️⃣ REGISTRAR NUEVA COMPRA
// ------------------------------------------------------------
public function insertarCompra(array $data): ?int
{
    $sql = "INSERT INTO Compras (
                Id_Proveedores,
                id_Empresa,
                Numero_Factura,
                Fecha_EmisionF,
                Fecha_Ingreso,
                Peso_Total_Libras,
                Peso_Total_KG,
                Total_Costos_Adicionales,
                Costos_Adicionales_Libra,
                Importe_Total_Factura,
                Total_Factura,
                Nueva_Compra,
                Eliminado
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1, 0)";

    $stmt = $this->conexion->prepare($sql);
    $stmt->bind_param(
        "iisssdddddd",
        $data['Id_Proveedores'],
        $data['id_Empresa'],
        $data['Numero_Factura'],
        $data['Fecha_EmisionF'],
        $data['Fecha_Ingreso'],
        $data['Peso_Total_Libras'],
        $data['Peso_Total_KG'],
        $data['Total_Costos_Adicionales'],
        $data['Costos_Adicionales_Libra'],  // ✅ agregado
        $data['Importe_Total_Factura'],
        $data['Total_Factura']
    );

    if ($stmt->execute()) {
        return $this->conexion->insert_id;
    }

    error_log("❌ Error insertarCompra: " . $stmt->error);
    return null;
}

// ------------------------------------------------------------
// 3️⃣ INSERTAR COSTO ADICIONAL DE COMPRA (TABLA Compras_OtrosCostos)
// ------------------------------------------------------------
public function insertarCostoCompra(array $data): bool
{
    $sql = "INSERT INTO Compras_OtrosCostos (
                id_Compras,
                id_Costos,
                Valor_USD,
                Valor_EU,
                Eliminado
            ) VALUES (?, ?, ?, ?, 0)";

    $stmt = $this->conexion->prepare($sql);


    $stmt->bind_param(
        "iidd",
        $data['id_Compras'],
        $data['id_Costos'],
        $data['Valor_USD'],
        $data['Valor_EU']
    );

    return $stmt->execute();
}


// ------------------------------------------------------------
// 4️⃣ INSERTAR DETALLE DE COMPRA 
// ------------------------------------------------------------
public function insertarDetalleCompra(array $data): bool
{
    $sql = "INSERT INTO Detalle_Compra (
                Id_Compras,
                id_Producto,
                Cantidad,
                Precio_KG_EU,
                Precio_KG_USD,
                Peso_KG,
                Peso_Libra,
                Importe_EU,
                Importe_dolares,
                Eliminado
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 0)";

    $stmt = $this->conexion->prepare($sql);
    $stmt->bind_param(
        "iiddddddd",
        $data['Id_Compras'],
        $data['id_Producto'],
        $data['Cantidad'],
        $data['Precio_KG_EU'],
        $data['Precio_KG_USD'],
        $data['Peso_KG'],
        $data['Peso_Libra'],
        $data['Importe_EU'],
        $data['Importe_dolares']   // ✅ corregido (minúscula)
    );

    return $stmt->execute();
}


// ------------------------------------------------------------
// 5️⃣ INSERTAR DETALLE POR FAMILIA (NUEVA TABLA)
// ------------------------------------------------------------
public function insertarDetalleFamilia(array $data): void {
    $sql = "INSERT INTO Detalle_Compras_Familias
        (id_Familias, Id_Compras, Cantidad_Total, Peso_Total_KG, Peso_Total_Libras,
         Importe_Total_EU, Importe_Total_Dolares, Precio_CIF, Precio_Unitario_Bodega,
         Total_Familia, Eliminado)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 0)";

    $stmt = $this->conexion->prepare($sql);

    // ✅ Cadena de tipos correcta: sin espacios, 2 enteros y 8 decimales
    $stmt->bind_param(
        "iidddddddd",
        $data['id_Familias'],            // i
        $data['Id_Compras'],             // i
        $data['Cantidad_Total'],         // d
        $data['Peso_Total_KG'],          // d
        $data['Peso_Total_Libras'],      // d
        $data['Importe_Total_EU'],       // d
        $data['Importe_Total_Dolares'],  // d
        $data['Precio_CIF'],             // d
        $data['Precio_Unitario_Bodega'], // d
        $data['Total_Familia']           // d
    );

    $stmt->execute();
}
    
    // ------------------------------------------------------------
    // 6️⃣ OBTENER PRODUCTOS POR FAMILIA (USADO POR AJAX)
    // ------------------------------------------------------------
    public function obtenerProductosPorFamilia(int $idFamilia): array
    {
        $sql = "SELECT idProductos, Descripcion
                FROM Productos
                WHERE id_Familia = ? AND Eliminado = 0
                ORDER BY Descripcion";

        $stmt = $this->conexion->prepare($sql);
        $stmt->bind_param("i", $idFamilia);
        $stmt->execute();
        $res = $stmt->get_result();

        return $res->fetch_all(MYSQLI_ASSOC);
    }

    // ------------------------------------------------------------
    // 7️⃣ OBTENER PROVEEDORES ACTIVOS
    // ------------------------------------------------------------
    public function obtenerProveedoresActivos(): array
    {
        $res = $this->conexion->query("
            SELECT idProveedores, Nombre
            FROM Proveedores
            WHERE Eliminado = 0
            ORDER BY Nombre
        ");
        
       if (!$res) {
        die("<b>Error SQL obtenerProveedoresActivos:</b> " . $this->conexion->error);
    } 
        
        return $res->fetch_all(MYSQLI_ASSOC);
    }

    // ------------------------------------------------------------
    // 8️⃣ OBTENER FAMILIAS ACTIVAS
    // ------------------------------------------------------------
    public function obtenerFamiliasActivas(): array
    {
        $res = $this->conexion->query("
            SELECT idFamilia, Nombre
            FROM Familia
            WHERE Activo = 1
            ORDER BY Nombre
        ");
        return $res->fetch_all(MYSQLI_ASSOC);
    }

    // ------------------------------------------------------------
    // 9️⃣ OBTENER COSTOS ACTIVOS
    // ------------------------------------------------------------
    public function obtenerCostosActivos(): array
    {
        $res = $this->conexion->query("
            SELECT idCostos, Nombre
            FROM Costos
            WHERE Activo = 1
            ORDER BY Nombre
        ");
        return $res->fetch_all(MYSQLI_ASSOC);
    }

    // ------------------------------------------------------------
    // 🔟 OBTENER EMPRESAS ACTIVAS (para pruebas o fallback)
    // ------------------------------------------------------------
    public function obtenerEmpresasActivas(): array
    {
        $res = $this->conexion->query("
            SELECT idEmpresa, Nombre
            FROM Empresa
            WHERE Activo = 1
            ORDER BY Nombre
        ");
        return $res->fetch_all(MYSQLI_ASSOC);
    }
    
    // ==========================================================
// 1️⃣ OBTENER COMPRAS NUEVAS (NUEVA_COMPRA = 1)
// ==========================================================
public function obtenerComprasNuevas(): array
{
    $sql = "SELECT idCompras, Numero_Factura, Fecha_Ingreso 
            FROM Compras 
            WHERE Nueva_Compra = 1 AND Eliminado = 0
            ORDER BY Fecha_Ingreso DESC";

    $resultado = $this->conexion->query($sql);
    return $resultado ? $resultado->fetch_all(MYSQLI_ASSOC) : [];
}

    // ==========================================================
// 2️⃣ OBTENER DATOS COMPLETOS DE UNA COMPRA
// ==========================================================
public function obtenerCompraPorId(int $idCompra): ?array
{
    $sql = "SELECT 
                c.idCompras,
                c.Numero_Factura,
                c.Fecha_Ingreso,
                c.Fecha_EmisionF,
                p.Nombre AS Proveedor,
                e.Nombre AS Empresa
            FROM Compras c
            INNER JOIN Proveedores p ON c.Id_Proveedores = p.idProveedores
            INNER JOIN Empresa e ON c.id_Empresa = e.idEmpresa
            WHERE c.idCompras = ? AND c.Eliminado = 0
            LIMIT 1";

    $stmt = $this->conexion->prepare($sql);
    $stmt->bind_param("i", $idCompra);
    $stmt->execute();
    $resultado = $stmt->get_result();

    return $resultado->num_rows > 0 ? $resultado->fetch_assoc() : null;
}

    // ==========================================================
// 3️⃣ OBTENER DETALLE DE PRODUCTOS DE UNA COMPRA
// ==========================================================
public function obtenerDetalleCompra(int $idCompra): array
{
    $sql = "SELECT 
                d.id_Producto AS idProductos,
                p.Descripcion,
                p.Codigo,
                d.Cantidad AS Cantidad_Total_Metros,
                d.Peso_Libra AS Peso_Total_Libras
            FROM Detalle_Compra d
            INNER JOIN Productos p ON d.id_Producto = p.idProductos
            WHERE d.Id_Compras = ? AND d.Eliminado = 0";

    $stmt = $this->conexion->prepare($sql);
    $stmt->bind_param("i", $idCompra);
    $stmt->execute();
    $resultado = $stmt->get_result();

    return $resultado->fetch_all(MYSQLI_ASSOC);
}

    // ==========================================================
// 4️⃣ MARCAR COMPRA COMO PROCESADA (NUEVA_COMPRA = 0)
// ==========================================================
public function marcarCompraProcesada(int $idCompra): bool
{
    $sql = "UPDATE Compras SET Nueva_Compra = 0 WHERE idCompras = ?";
    $stmt = $this->conexion->prepare($sql);
    $stmt->bind_param("i", $idCompra);
    return $stmt->execute();
}

}
