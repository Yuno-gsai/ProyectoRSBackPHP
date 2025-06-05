<?php
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'OPTIONS') {
    
    header('Access-Control-Allow-Credentials: true');
}

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$input = json_decode(file_get_contents('php://input'), true);

$controller = $input['controller'] ?? null;
$method = $input['method'] ?? null;

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


