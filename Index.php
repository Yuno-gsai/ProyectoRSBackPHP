<?php
// Configuración de respuesta por defecto
header('Content-Type: application/json; charset=utf-8');

// Solo manejar CORS si no es una solicitud OPTIONS (ya manejada en .htaccess)
if ($_SERVER['REQUEST_METHOD'] !== 'OPTIONS') {
    // Configuración básica de CORS (los headers principales ya están en .htaccess)
    header('Access-Control-Allow-Credentials: true');
}

// Habilitar errores de PHP para depuración
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Obtener los datos del cuerpo de la solicitud
$input = json_decode(file_get_contents('php://input'), true);

// Verificar si los datos fueron enviados en el body
$controller = $input['controller'] ?? null;
$method = $input['method'] ?? null;

// Asegurarse de que tanto el controlador como el método estén especificados en el cuerpo de la solicitud
if ($controller && $method) {
    if (file_exists(__DIR__ . "/Routes/{$controller}.php")) {
        require_once __DIR__ . "/Routes/{$controller}.php";
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Ruta no encontrada']);
    }
} else {
    http_response_code(400);
    echo json_encode(['error' => 'Controlador o método no especificado en el cuerpo']);
}
?>


