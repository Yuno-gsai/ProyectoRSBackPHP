<?php
abstract class BaseController {
    
    protected $model;
    
    public function __construct() {
        // Constructor vacío
    }

    public function handleRequest() {
        // Obtener los datos del cuerpo de la solicitud
        $input = json_decode(file_get_contents('php://input'), true);

        // Verificar si los parámetros 'controller' y 'method' están en el cuerpo de la solicitud
        $controller = $input['controller'] ?? null;
        $method = $input['method'] ?? null;

        // Asegurarse de que los parámetros 'controller' y 'method' estén presentes
        if (!$controller || !$method) {
            http_response_code(400);
            echo json_encode(['error' => 'Controlador o método no especificado en el cuerpo']);
            return;
        }

        // Procesar la solicitud según el método HTTP y el controlador
        switch ($_SERVER['REQUEST_METHOD']) {
            case 'POST':
                if ($method === 'create') {
                    $data = $input['data'] ?? null; // Asumimos que los datos para crear están bajo la clave 'data'
                    if ($data && $this->model->create($data)) {
                        echo json_encode(['success' => true]);
                    } else {
                        http_response_code(400);
                        echo json_encode(['error' => 'Error al crear']);
                    }
                }
                break;

            case 'PUT':
                if ($method === 'update' && isset($input['id'])) {
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
                if ($method === 'delete' && isset($input['id'])) {
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
                if ($method === 'all') {
                    $data = $this->model->getAll();
                    echo json_encode($data);
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
