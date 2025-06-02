<?php
require_once 'BaseModel.php';

class UserModel extends BaseModel {

    public function __construct() {
        parent::__construct();
        $this->table = 'usuarios';
    }

    public function create(array $data): bool {
        if (isset($data['contrasena'])) {
            $data['contrasena'] = password_hash($data['contrasena'], PASSWORD_DEFAULT);
        }
    
        $query = "INSERT INTO {$this->table} (nombre_usuario, correo, contrasena) VALUES (:nombre_usuario, :correo, :contrasena)";
        $stmt = $this->getConnection()->prepare($query);
        return $stmt->execute($data);
    }
    

    public function update(int $id, array $data) {
        $data['id'] = $id;
    
        // Hashear contraseña si viene en el array de datos
        if (isset($data['contrasena'])) {
            $data['contrasena'] = password_hash($data['contrasena'], PASSWORD_DEFAULT);
        }
    
        // Si foto_perfil viene como base64, la convertimos en archivo
        if (isset($data['foto_perfil']) && str_starts_with($data['foto_perfil'], 'data:image/')) {
            $data['foto_perfil'] = $this->guardarImagenBase64($data['foto_perfil']);
        }
    
        $allowedFields = ['nombre_usuario', 'correo', 'contrasena', 'foto_perfil', 'biografia'];
        $setClauses = [];
    
        foreach ($allowedFields as $field) {
            if (isset($data[$field])) {
                $setClauses[] = "$field = :$field";
            }
        }
    
        if (empty($setClauses)) {
            return false;
        }
    
        $query = "UPDATE {$this->table} SET " . implode(', ', $setClauses) . " WHERE id = :id";
    
        try {
            $stmt = $this->getConnection()->prepare($query);
            return $stmt->execute($data);
        } catch (PDOException $e) {
            error_log("Error en UserModel->update: " . $e->getMessage());
            return false;
        }
    }
    

    private function guardarImagenBase64(string $base64): string {
        $uploadDir = __DIR__ . '/../uploads/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        preg_match('/^data:image\/(\w+);base64,/', $base64, $type);
        $ext = $type[1]; // jpg, png, etc.
        $base64 = preg_replace('/^data:image\/\w+;base64,/', '', $base64);
        $base64 = str_replace(' ', '+', $base64);
        $data = base64_decode($base64);

        $fileName = uniqid('img_') . '.' . $ext;
        $filePath = $uploadDir . $fileName;

        file_put_contents($filePath, $data);

        return 'uploads/' . $fileName;
    }

    public function getByEmail(string $email) {
        $query = "SELECT * FROM {$this->table} WHERE correo = :correo LIMIT 1";
        $stmt = $this->getConnection()->prepare($query);
        $stmt->execute(['correo' => $email]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    

    public function login() {
        // Espera POST con JSON { correo, contrasena }
        $json = file_get_contents('php://input');
        $data = json_decode($json, true);
    
        if (json_last_error() !== JSON_ERROR_NONE) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'JSON inválido'
            ]);
            return;
        }
    
        if (empty($data['correo']) || empty($data['contrasena'])) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'Correo y contraseña requeridos'
            ]);
            return;
        }
    
        $user = $this->getByEmail($data['correo']);
        if (!$user) {
            http_response_code(401);
            echo json_encode([
                'success' => false,
                'message' => 'Usuario no encontrado'
            ]);
            return;
        }
    
        if (password_verify($data['contrasena'], $user['contrasena'])) {
            // No enviar contraseña en la respuesta
            unset($user['contrasena']);
            echo json_encode([
                'success' => true,
                'user' => $user
            ]);
        } else {
            http_response_code(401);
            echo json_encode([
                'success' => false,
                'message' => 'Contraseña incorrecta'
            ]);
        }
    }
    
}
