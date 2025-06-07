<?php
// Permitir solicitudes desde un origen específico (cambiar según sea necesario)
header("Access-Control-Allow-Origin: http://localhost:5173");  // Reemplazar con tu frontend en producción
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");  // Métodos permitidos
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");  // Cabeceras permitidas
header("Access-Control-Allow-Credentials: true");  // Si es necesario permitir credenciales (cookies, autenticación)

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    // Responde a las solicitudes OPTIONS (preflight) de manera rápida
    http_response_code(200);
    exit;
}

// Configuración de errores para desarrollo
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Leer la entrada JSON del cuerpo de la solicitud
$input = json_decode(file_get_contents('php://input'), true);

// Obtener el controlador y el método desde el cuerpo
$controller = $input['controller'] ?? null;
$method = $input['method'] ?? null;

if ($controller && $method) {
    // Verificar si el archivo del controlador existe
    if (file_exists(__DIR__ . "/Routes/{$controller}.php")) {
        require_once __DIR__ . "/Routes/{$controller}.php";  // Incluir el archivo del controlador
    } else {
        // Si el controlador no se encuentra, devolver un error 404
        http_response_code(404);
        echo json_encode(['error' => 'Ruta no encontrada']);
    }
} else {
    // Si no se proporcionan controlador o método, devolver un error 400
    http_response_code(400);
    echo json_encode(['error' => 'Controlador o método no especificado en el cuerpo']);
}
?>
