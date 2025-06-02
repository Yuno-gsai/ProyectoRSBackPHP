<?php
// Configuración de respuesta por defecto
header('Content-Type: application/json; charset=utf-8');

// Solo manejar CORS si no es una solicitud OPTIONS (ya manejada en .htaccess)
if ($_SERVER['REQUEST_METHOD'] !== 'OPTIONS') {
    // Configuración básica de CORS (los headers principales ya están en .htaccess)
    header('Access-Control-Allow-Credentials: true');
}
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$uri        = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri        = trim($uri, '/');
$segments   = explode('/', $uri);
$controller = $segments[1] ?? null;

if ($controller && file_exists(__DIR__ . "/Routes/{$controller}.php")) {
    require_once __DIR__ . "/Routes/{$controller}.php";
} else {
    http_response_code(404);
    echo json_encode(['error' => 'Ruta no encontrada']);
}

//Para Probar el postman Usar las siguientes URL

//http://localhost/ProyectoDeDaw/Aqui El Nombre De La Ruta/all 

//Rutas: Friends,Coments,Likes,Publications,User

//http://localhost/ProyectoDeDaw/Friends/all Consultar  todos los registros de la base de datos
//http://localhost/ProyectoDeDaw/Friends/create Crear un nuevo registro en la base de datos
//http://localhost/ProyectoDeDaw/Friends/delete?id=1 Eliminar un registro de la base de datos
//http://localhost/ProyectoDeDaw/Friends/update?id=1 Actualizar un registro de la base de datos

?>

