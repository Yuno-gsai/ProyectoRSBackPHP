    <?php
    class DatabaseConnection {
        private $conn;
        public function __construct() {
            $envPath    = __DIR__ . '/../.env';
            $env        = parse_ini_file($envPath);
            $host       = $env['DB_HOST'];
            $username   = $env['DB_USER'];
            $password   = $env['DB_PASS'];
            $database   = $env['DB_NAME'];
            
            try {
                $this->conn = new PDO("mysql:host=$host;dbname=$database", $username, $password);
                $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $stmt = $this->conn->prepare("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = ?");
                $stmt->execute([$database]);
                $exists = $stmt->fetch(PDO::FETCH_ASSOC);
                    
                if (!$exists) {
                    $this->createDatabaseAndTables();
                }    
                $this->conn->exec("USE $database");
            } catch(PDOException $e) {
                throw new Exception("Error de conexión: " . $e->getMessage());
            }
    }

        private function createDatabaseAndTables() {
            try {
                $sql = file_get_contents(__DIR__ . '/DataBase.sql');
                $this->conn->exec($sql);
            } catch(PDOException $e) {
                throw new Exception("Error al crear la base de datos: " . $e->getMessage());
            }
        }

        public function getConnection() {
            return $this->conn;
        }

        public function ExecuteQuery($query) {
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