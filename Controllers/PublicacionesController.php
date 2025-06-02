<?php
require_once "BaseController.php";
require_once __DIR__ . '/../Models/PublicacionesModel.php';
class PublicacionesController extends BaseController {
    public function __construct() {
        $this->model = new PublicacionesModel();
    }

    public function handleRequest() {
        $method = $_SERVER['REQUEST_METHOD'];
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $uri = trim($uri, '/');
        $segments = explode('/', $uri);
        $path = $segments[2] ?? ($_GET['path'] ?? '');

        if ($method === 'GET' && $path === 'amigos') {
            // Aquí obtienes el usuario actual, por ejemplo, por sesión o token
            // Para demo, supongamos que $usuarioId viene de algún lado
            $usuarioId = $_GET['usuario_id'] ?? null; // O de sesión

            if ($usuarioId) {
                $data = $this->model->getPublicacionesDeAmigos($usuarioId);
                echo json_encode($data);
            } else {
                http_response_code(400);
                echo json_encode(['error' => 'Falta usuario_id']);
            }
            return; // Salimos para no caer en la base
        }

        // Llama al handleRequest de la clase padre para el resto
        parent::handleRequest();
    }
}

?>