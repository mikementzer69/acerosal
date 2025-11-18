<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Datos de conexión (los que aparecen en tu panel de InfinityFree)
$host     = "sql113.infinityfree.com";
$user     = "if0_40026907";
$password = "qjuI7M9posuMw";
$database = "if0_40026907_acerosv11";

// Conexión con mysqli
$conn = mysqli_connect($host, $user, $password, $database);

// Verificar conexión
if (!$conn) {
    die("<h3 style='color:red;'>❌ Error de conexión:</h3> " . mysqli_connect_error());
}

echo "<h3 style='color:green;'>✅ Conexión exitosa a la base de datos</h3>";

// Probar una consulta simple
$sql = "SHOW TABLES";
$result = mysqli_query($conn, $sql);

if ($result) {
    echo "<p>Tablas disponibles en la BD <b>$database</b>:</p><ul>";
    while ($row = mysqli_fetch_row($result)) {
        echo "<li>" . htmlspecialchars($row[0]) . "</li>";
    }
    echo "</ul>";
} else {
    echo "<p style='color:red;'>No se pudieron listar las tablas.</p>";
}

mysqli_close($conn);
?>
