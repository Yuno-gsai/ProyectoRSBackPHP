<?php
abstract class BaseController {
    
    protected $model;
    
    public function __construct() {
        // Constructor vacío
    }
    public function handleRequest() {
        $method     = $_SERVER['REQUEST_METHOD'];
        $uri        = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $uri        = trim($uri, '/');
        $segments   = explode('/', $uri);
        $path       = $segments[2] ?? ($_GET['path'] ?? '');

        switch ($method) {
            case 'POST':
                if ($path === 'create') {
                    $data = json_decode(file_get_contents('php://input'), true);
                    if ($this->model->create($data)) {
                        echo json_encode(['success' => true]);
                    } else {
                        http_response_code(400);
                        echo json_encode(['error' => 'Error al crear']);
                    }
                }
                break;

            case 'PUT':
                if ($path === 'update' && isset($_GET['id'])) {
                    $id = intval($_GET['id']);
                    $data = json_decode(file_get_contents('php://input'), true);
                    if ($this->model->update($id, $data)) {
                        echo json_encode(['success' => true]);
                    } else {
                        http_response_code(400);
                        echo json_encode(['error' => 'Error al actualizar']);
                    }
                }
                break;

            case 'DELETE':
                if ($path === 'delete' && isset($_GET['id'])) {
                    $id = intval($_GET['id']);
                    if ($this->model->delete($id)) {
                        echo json_encode(['success' => true]);
                    } else {
                        http_response_code(400);
                        echo json_encode(['error' => 'Error al eliminar']);
                    }
                }
                break;

            case 'GET':
                if ($path === 'all') {
                    $data = $this->model->getAll();
                    echo json_encode($data);
                }
                break;

            default:
                http_response_code(404);
                echo json_encode(['error' => 'Ruta no válida']);
                break;
        }
    }
}
?>