<?php
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../Models/UserModel.php';

class UserController extends BaseController {
    
    public function __construct() {
        parent::__construct();
        $this->model = new UserModel();
    }
    
    public function handleRequest() {
        // Obtener los datos del cuerpo de la solicitud (en formato JSON)
        $input = json_decode(file_get_contents('php://input'), true);

        // Verificar si los parámetros 'controller' y 'method' están presentes en el cuerpo de la solicitud
        $controller = $input['controller'] ?? null;
        $method = $input['method'] ?? null;

        // Asegurarse de que los parámetros 'controller' y 'method' estén presentes
        if (!$controller || !$method) {
            http_response_code(400);
            echo json_encode(['error' => 'Controlador o método no especificado en el cuerpo']);
            return;
        }

        // Procesar la solicitud según el método HTTP y el controlador
        switch ($method) {
            case 'POST':
                if ($controller === 'User' && $method === 'create') {
                    $data = $input['data'] ?? null; // Los datos para crear están bajo la clave 'data'
                    if ($data && $this->model->create($data)) {
                        echo json_encode(['success' => true]);
                    } else {
                        http_response_code(400);
                        echo json_encode(['error' => 'Error al crear']);
                    }
                }
                break;

            case 'PUT':
                if ($controller === 'User' && $method === 'update' && isset($input['id'])) {
                    $id = intval($input['id']);
                    $data = $input['data'] ?? null;
                    if ($this->model->update($id, $data)) {
                        echo json_encode(['success' => true]);
                    } else {
                        http_response_code(400);
                        echo json_encode(['error' => 'Error al actualizar']);
                    }
                }
                break;

            case 'DELETE':
                if ($controller === 'User' && $method === 'delete' && isset($input['id'])) {
                    $id = intval($input['id']);
                    if ($this->model->delete($id)) {
                        echo json_encode(['success' => true]);
                    } else {
                        http_response_code(400);
                        echo json_encode(['error' => 'Error al eliminar']);
                    }
                }
                break;

            case 'GET':
                if ($controller === 'User' && $method === 'all') {
                    $data = $this->model->getAll();
                    echo json_encode($data);
                }
                break;

            case 'POST':
                if ($controller === 'User' && $method === 'login') {
                    $this->model->login();
                }
                break;

            default:
                http_response_code(404);
                echo json_encode(['error' => 'Método no válido']);
                break;
        }
    }
}
?>
