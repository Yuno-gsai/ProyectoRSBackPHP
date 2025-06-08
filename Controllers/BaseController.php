<?php
abstract class BaseController {

    protected $model;

    public function handleRequest() {
        // Encabezados CORS para permitir solicitudes desde un origen específico
        header("Access-Control-Allow-Origin: *");  // Cambiar a tu dominio de frontend en producción
        header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");  // Métodos permitidos
        header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");  // Cabeceras permitidas
        header("Access-Control-Allow-Credentials: true");  // Si es necesario permitir credenciales (cookies, autenticación)

        // Responder a la solicitud OPTIONS (preflight)
        if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
            http_response_code(200);  // Responde con código 200 para la solicitud OPTIONS
            exit;  // Termina aquí la ejecución, ya que solo estamos respondiendo a la pre-solicitud
        }

        // Leer la entrada JSON del cuerpo de la solicitud
        $input = json_decode(file_get_contents('php://input'), true);

        $controller = $input['controller'] ?? null;
        $method = $input['method'] ?? null;

        if (!$controller || !$method) {
            http_response_code(400);
            echo json_encode(['error' => 'Controlador o método no especificado en el cuerpo']);
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $data = $input['data'] ?? null; 
                if ($method === 'create') {
                    if ($data && $this->model->create($data)) {
                        echo json_encode(['success' => true]);
                    } else {
                        http_response_code(400);
                        echo json_encode(['error' => 'Error al crear']);
                    }
                }
                if ($method === 'all') {
                    $data = $this->model->getAll();
                    echo json_encode($data);
                }
                if ($method === 'delete' && isset($data['id'])) {
                    $id = intval($data['id']);
                    if ($this->model->delete($id)) {
                        echo json_encode(['success' => true]);
                    } else {
                        http_response_code(400);
                        echo json_encode(['error' => 'Error al eliminar']);
                    }
                }
                if ($method === 'update' && isset($data['id'])) {
                    $id = intval($data['id']);
                    if ($this->model->update($id, $data)) {
                        echo json_encode(['success' => true]);
                    } else {
                        http_response_code(400);
                        echo json_encode(['error' => 'Error al actualizar']);
                    }
                }
        }
    }
}
?>
