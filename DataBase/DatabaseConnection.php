<?php
class DatabaseConnection {
    private $conn;

    public function __construct() {
        // Obtener las variables de entorno configuradas en Azure
        $host       = getenv('DB_HOST') ?? 'mysql-server-uno.mysql.database.azure.com';
        $port       = getenv('DB_PORT') ?? 3306;
        $username   = getenv('DB_USERNAME') ?? 'adminmysql';
        $password   = getenv('DB_PASSWORD') ?? '';
        $database   = getenv('DB_DATABASE') ?? 'red_social';
        $sslCaPath  = getenv('DB_SSL_CA_PATH') ?? 'uploads/certificado.pem'; // Asegúrate de que la ruta sea correcta

        try {
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4",
            ];

            // Si tienes el archivo CA para SSL, lo agregas aquí
            if ($sslCaPath && file_exists($sslCaPath)) {
                $options[PDO::MYSQL_ATTR_SSL_CA] = $sslCaPath;
            }

            // Definir el DSN (Data Source Name) para la conexión con la base de datos
            $dsn = "mysql:host=$host;port=$port;charset=utf8mb4";

            // Inicializamos la conexión sin especificar la base de datos para verificar si existe
            $this->conn = new PDO($dsn, $username, $password, $options);

            // Verificar si la base de datos existe
            $stmt = $this->conn->prepare("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = ?");
            $stmt->execute([$database]);
            $exists = $stmt->fetch(PDO::FETCH_ASSOC);

            // Si la base de datos no existe, la creamos
            if (!$exists) {
                $this->createDatabaseAndTables($database);
            }

            // Cambiar a la base de datos especificada
            $this->conn->exec("USE `$database`");

        } catch(PDOException $e) {
            throw new Exception("Error de conexión: " . $e->getMessage());
        }
    }

    private function createDatabaseAndTables(string $database) {
        try {
            // Crear base de datos si no existe
            $this->conn->exec("CREATE DATABASE IF NOT EXISTS `$database` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            $this->conn->exec("USE `$database`");

            // Ejecutar el SQL para crear las tablas (asegúrate de tener el archivo DataBase.sql en la ruta correcta)
            $sql = file_get_contents(__DIR__ . '/DataBase.sql');
            $this->conn->exec($sql);
        } catch(PDOException $e) {
            throw new Exception("Error al crear la base de datos: " . $e->getMessage());
        }
    }

    public function getConnection() {
        return $this->conn;
    }

    public function ExecuteQuery(string $query) {
        try {
            $conn = $this->getConnection();
            $stmt = $conn->prepare($query);
            $stmt->execute();
            return $stmt;
        } catch (PDOException $e) {
            throw new Exception("Error al ejecutar la consulta: " . $e->getMessage());
        }
    }

    public function __destruct() {
        $this->conn = null;
    }
}
?>
