<?php
require_once __DIR__ . '/../conexion.php';

class ModeloInventario
{
    private mysqli $cn;

    public function __construct()
    {
        // Mantiene el mismo patrón que ModeloCompras
        $this->cn = conectar();
    }
    // ------------------------------------------------------------
    // 1️⃣ VALIDAR CÓDIGO DE LOTE ÚNICO POR PRODUCTO
    // ------------------------------------------------------------
    public function existeCodigoLote(int $idProducto, string $codigoLote): bool {
        $sql = "SELECT COUNT(*) AS total
                FROM Lotes
                WHERE Id_Productos = ? AND Codigo = ? AND Eliminado = 0";
        $stmt = $this->cn->prepare($sql);
        $stmt->bind_param("is", $idProducto, $codigoLote);
        $stmt->execute();
        $rs = $stmt->get_result()->fetch_assoc();
        return $rs['total'] > 0;
    }

    // ------------------------------------------------------------
    // 2️⃣ INSERTAR NUEVO LOTE
    // ------------------------------------------------------------
    public function insertarLote(array $data): int {
        $sql = "INSERT INTO Lotes (
                    Id_Productos,
                    Codigo,
                    Fecha_Ingreso,
                    Peso_Total_Libras,
                    Cantidad_Total_Metros,
                    Relacion_Cantidad_Peso,
                    Total_Piezas,
                    Eliminado
                ) VALUES (?, ?, ?, ?, ?, ?, ?, 0)";

        $stmt = $this->cn->prepare($sql);
        $stmt->bind_param(
            "issdddi",
            $data['Id_Productos'],
            $data['Codigo'],
            $data['Fecha_Ingreso'],
            $data['Peso_Total_Libras'],
            $data['Cantidad_Total_Metros'],
            $data['Relacion_Cantidad_Peso'],
            $data['Total_Piezas']
        );

        if ($stmt->execute()) {
            return $this->cn->insert_id; // devuelve idLote generado
        } else {
            error_log("Error insertando lote: " . $stmt->error);
            return 0;
        }
    }

    // ------------------------------------------------------------
    // 3️⃣ INSERTAR PIEZAS ASOCIADAS A UN LOTE
    // ------------------------------------------------------------
    public function insertarPieza(array $pieza, int $idProducto, int $idLote): bool {
        $sql = "INSERT INTO Piezas (
                    Id_Productos,
                    Id_Lotes,
                    Codigo,
                    Peso_Libras_Inicial,
                    Cantidad_Metros_Inicial,
                    Peso_Libras_Actual,
                    Cantidad_Metros_Actual,
                    Peso_Libras_Recortados,
                    Cantidad_Metros_Recortados,
                    Finalizado,
                    Eliminado
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 0, 0)";

        $stmt = $this->cn->prepare($sql);
        $stmt->bind_param(
            "iissddddd",
            $idProducto,
            $idLote,
            $pieza['Codigo'],
            $pieza['Peso_Libras_Inicial'],
            $pieza['Cantidad_Metros_Inicial'],
            $pieza['Peso_Libras_Actual'],
            $pieza['Cantidad_Metros_Actual'],
            $pieza['Peso_Libras_Recortados'],
            $pieza['Cantidad_Metros_Recortados']
        );

        if (!$stmt->execute()) {
            error_log("Error insertando pieza: " . $stmt->error);
            return false;
        }
        return true;
    }

    // ------------------------------------------------------------
    // 4️⃣ OBTENER LOTES ACTIVOS (para listas)
    // ------------------------------------------------------------
    public function obtenerLotesActivos(): array {
        $sql = "SELECT 
                    l.idLotes,
                    l.Codigo,
                    p.Descripcion AS Producto,
                    l.Fecha_Ingreso,
                    l.Peso_Total_Libras,
                    l.Cantidad_Total_Metros,
                    l.Total_Piezas
                FROM Lotes l
                INNER JOIN Productos p ON l.Id_Productos = p.idProductos
                WHERE l.Eliminado = 0
                ORDER BY l.Fecha_Ingreso DESC";

        $rs = $this->cn->query($sql);
        return $rs ? $rs->fetch_all(MYSQLI_ASSOC) : [];
    }

    // ------------------------------------------------------------
    // 5️⃣ OBTENER PIEZAS DE UN LOTE
    // ------------------------------------------------------------
    public function obtenerPiezasPorLote(int $idLote): array {
        $sql = "SELECT 
                    idPiezas,
                    Codigo,
                    Peso_Libras_Inicial,
                    Cantidad_Metros_Inicial,
                    Peso_Libras_Actual,
                    Cantidad_Metros_Actual,
                    Finalizado
                FROM Piezas
                WHERE Id_Lotes = ? AND Eliminado = 0";

        $stmt = $this->cn->prepare($sql);
        $stmt->bind_param("i", $idLote);
        $stmt->execute();
        $rs = $stmt->get_result();
        return $rs ? $rs->fetch_all(MYSQLI_ASSOC) : [];
    }

    // ------------------------------------------------------------
    // 6️⃣ OBTENER PRODUCTOS POR FAMILIA (para select dinámico)
    // ------------------------------------------------------------
    public function obtenerProductosPorFamilia(int $idFamilia): array {
        $sql = "SELECT idProductos, Descripcion, Codigo
                FROM Productos
                WHERE id_Familia = ? AND Eliminado = 0
                ORDER BY Descripcion";

        $stmt = $this->cn->prepare($sql);
        $stmt->bind_param("i", $idFamilia);
        $stmt->execute();
        $res = $stmt->get_result();
        return $res->fetch_all(MYSQLI_ASSOC);
    }

    // ------------------------------------------------------------
    // 7️⃣ OBTENER FAMILIAS ACTIVAS (para select inicial)
    // ------------------------------------------------------------
    public function obtenerFamiliasActivas(): array {
        $res = $this->cn->query("
            SELECT idFamilia, Nombre
            FROM Familia
            WHERE Activo = 1
            ORDER BY Nombre
        ");
        return $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
    }
    
    // ------------------------------------------------------------
	// 2️⃣ OBTENER DATOS GENERALES DE UNA COMPRA
	// ------------------------------------------------------------
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
            	WHERE c.idCompras = ? AND c.Eliminado = 0";
    
    	$stmt = $this->cn->prepare($sql);
    	$stmt->bind_param("i", $idCompra);
    	$stmt->execute();
    	$res = $stmt->get_result();
    
    	return $res->fetch_assoc() ?: null;
	}


    // ------------------------------------------------------------
// 3️⃣ OBTENER DETALLE DE PRODUCTOS DE UNA COMPRA
// ------------------------------------------------------------
public function obtenerDetalleCompra(int $idCompra): array
{
    $sql = "SELECT 
                d.id_Producto AS idProductos,
                p.Codigo,
                p.Descripcion,
                d.Peso_Libra AS Peso_Total_Libras,
                d.Cantidad AS Cantidad_Total_Metros
            FROM Detalle_Compra d
            INNER JOIN Productos p ON p.idProductos = d.id_Producto
            WHERE d.Id_Compras = ? AND d.Eliminado = 0";

    $stmt = $this->cn->prepare($sql);
    $stmt->bind_param("i", $idCompra);
    $stmt->execute();
    $res = $stmt->get_result();

    return $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
}


        // ==========================================================
// 1️⃣ OBTENER COMPRAS NUEVAS (NUEVA_COMPRA = 1)
// ==========================================================
// ------------------------------------------------------------
// 🔹 OBTENER COMPRAS NUEVAS (PENDIENTES DE INVENTARIO)
// ------------------------------------------------------------
public function obtenerComprasNuevas(): array
{
    $sql = "SELECT 
                c.idCompras,
                c.Numero_Factura,
                c.Fecha_Ingreso,
                p.Nombre AS Proveedor,
                e.Nombre AS Empresa
            FROM Compras c
            INNER JOIN Proveedores p ON c.Id_Proveedores = p.idProveedores
            INNER JOIN Empresa e ON c.id_Empresa = e.idEmpresa
            WHERE c.Nueva_Compra = 1
              AND c.Eliminado = 0
            ORDER BY c.idCompras DESC";

    $res = $this->cn->query($sql);
    return $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
}

// ------------------------------------------------------------
// ✅ MARCAR COMPRA COMO PROCESADA
// ------------------------------------------------------------
public function marcarCompraProcesada(int $idCompra): bool
{
    $sql = "UPDATE Compras SET Nueva_Compra = 0 WHERE idCompras = ?";
    $stmt = $this->cn->prepare($sql);

    if (!$stmt) {
        error_log("Error al preparar marcarCompraProcesada(): " . $this->cn->error);
        return false;
    }

    $stmt->bind_param("i", $idCompra);
    $ok = $stmt->execute();

    if (!$ok) {
        error_log("Error al ejecutar marcarCompraProcesada(): " . $stmt->error);
    }

    $stmt->close();
    return $ok;
}


}
?>

