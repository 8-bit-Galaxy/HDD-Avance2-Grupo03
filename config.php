// Configuración de la base de datos
$db_name = "mysql:host=localhost;dbname=shop_db";
$username = "root";
$password = "";

try {
    $conn = new PDO($db_name, $username, $password);
    // Configura el manejo de errores de PDO
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // En caso de error, termina la ejecución e imprime el mensaje
    die('Error de conexión a la base de datos: ' . $e->getMessage());
}
?>