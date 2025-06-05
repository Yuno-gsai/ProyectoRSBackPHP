<?php
require_once "BaseController.php";
require_once __DIR__ . '/../Models/PublicacionesModel.php';

class PublicacionesController extends BaseController {

    public function __construct() {
        $this->model = new PublicacionesModel();
    }

    public function handleRequest() {
        $input = json_decode(file_get_contents('php://input'), true);

        $controller = $input['controller'] ?? null;
        $method = $input['method'] ?? null;

        if (!$controller || !$method) {
            http_response_code(400);
            echo json_encode(['error' => 'Controlador o método no especificado en el cuerpo']);
            return;
        }

        if ($controller !== 'Publicaciones') {
            http_response_code(400);
            echo json_encode(['error' => 'Controlador no válido']);
            return;
        }

        switch ($_SERVER['REQUEST_METHOD']) {
            case 'POST':
                if ($method === 'create') {
                    $data = $input['data'] ?? null; 
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

                if ($method === 'amigos' && isset($input['usuario_id'])) {
                    $usuarioId = $input['usuario_id']; 
                    $data = $this->model->getPublicacionesDeAmigos($usuarioId);
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
