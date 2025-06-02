<?php
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../Models/UserModel.php';

class UserController extends BaseController {
    
    public function __construct() {
        parent::__construct();
        $this->model = new UserModel();
    }
    
    public function handleRequest() {
        $method = $_SERVER['REQUEST_METHOD'];
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $uri = trim($uri, '/');
        $segments = explode('/', $uri);
        $path = $segments[2] ?? '';
    
        // Configurar encabezados CORS
        header('Access-Control-Allow-Credentials: true');
    
        // Manejar solicitudes OPTIONS (preflight)
        if ($method === 'OPTIONS') {
            http_response_code(200);
            exit();
        }
    
        try {
            switch ($method) {
                case 'POST':
                    if ($path === 'login') {
                        $this->model->login();
                        return;
                    }
                    if ($path === 'create') {
                        // opcional, si quieres manejar create aquí, sino delegar a BaseController
                        parent::handleRequest();
                        return;
                    }
                    break;
    
                case 'PUT':
                    if ($path === 'update' && isset($_GET['id'])) {
                        $id = intval($_GET['id']);
                        $json = file_get_contents('php://input');
                        $data = json_decode($json, true);
    
                        if (json_last_error() !== JSON_ERROR_NONE) {
                            throw new Exception('Formato JSON inválido: ' . json_last_error_msg());
                        }
    
                        if (empty($data)) {
                            throw new Exception('No se recibieron datos para actualizar');
                        }
    
                        if ($this->model->update($id, $data)) {
                            http_response_code(200);
                            echo json_encode([
                                'success' => true,
                                'message' => 'Usuario actualizado correctamente',
                                'data' => $data
                            ]);
                        } else {
                            throw new Exception('No se pudo actualizar el usuario');
                        }
                        return; // Importante: salir después de manejar la solicitud
                    }
                    break;
    
                default:
                    // Dejar que el controlador base maneje otros métodos
                    parent::handleRequest();
                    return;
            }
    
            // Si llegamos aquí, la ruta no fue manejada
            throw new Exception('Ruta no válida');
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
    
}
