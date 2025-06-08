<?php
require_once 'BaseModel.php';

class UserModel extends BaseModel {

    private $storageAccount;
    private $containerName;
    private $sasToken;

    public function __construct() {
        parent::__construct();
        $this->table = 'usuarios';

        // Carga variables Azure Storage desde .env
        $this->storageAccount = getenv('AZURE_STORAGE_ACCOUNT') ?? '';
        $this->containerName = getenv('AZURE_STORAGE_CONTAINER') ?? '';
        $this->sasToken = getenv('AZURE_STORAGE_SAS_TOKEN') ?? '';
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
    
        if (isset($data['contrasena'])) {
            $data['contrasena'] = password_hash($data['contrasena'], PASSWORD_DEFAULT);
        }
    
        if (isset($data['foto_perfil']) && str_starts_with($data['foto_perfil'], 'data:image/')) {
            // Guardar la imagen de perfil si se pasa en base64
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
        preg_match('/^data:image\/(\w+);base64,/', $base64, $type);
        $ext = $type[1]; 
        $base64 = preg_replace('/^data:image\/\w+;base64,/', '', $base64);
        $base64 = str_replace(' ', '+', $base64);
        $data = base64_decode($base64);
    
        $tempFile = sys_get_temp_dir() . '/' . uniqid('img_') . '.' . $ext;
        file_put_contents($tempFile, $data);
    
        $blobName = 'usuarios/' . uniqid() . '.' . $ext;
    
        // Depura la URL y el SAS Token
        error_log("Intentando subir la imagen con el siguiente SAS Token: " . $this->sasToken);
    
        $url = $this->uploadToAzureBlob($this->storageAccount, $this->containerName, $blobName, $tempFile, $this->sasToken);
    
        unlink($tempFile);
    
        return $url;
    }
    
    private function uploadToAzureBlob($storageAccount, $containerName, $blobName, $filePath, $sasToken) {
        $url = "https://{$storageAccount}.blob.core.windows.net/{$containerName}/{$blobName}?{$sasToken}";
        error_log("URL generada: " . $url);  // Depura la URL generada para ver si está correcta

        $fileSize = filesize($filePath);
        $fileHandle = fopen($filePath, 'r');
    
        $headers = [
            'x-ms-blob-type: BlockBlob',
            'Content-Length: ' . $fileSize,
            'x-ms-version: 2020-10-02',
            'x-ms-date: ' . gmdate('D, d M Y H:i:s T')
        ];
    
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_PUT, true);
        curl_setopt($ch, CURLOPT_INFILE, $fileHandle);
        curl_setopt($ch, CURLOPT_INFILESIZE, $fileSize);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
        $response = curl_exec($ch);
        $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
        // Depura la respuesta y el código de estado
        error_log("Respuesta de Azure: " . $response);  // Depura la respuesta de Azure
        error_log("Código de estado de Azure: " . $statusCode);  // Depura el código de estado HTTP
    
        curl_close($ch);
        fclose($fileHandle);
    
        if ($statusCode == 201) {
            return "https://{$storageAccount}.blob.core.windows.net/{$containerName}/{$blobName}";
        } else {
            throw new Exception("Error subiendo archivo a Azure Blob: HTTP $statusCode");
        }
    }

    public function getByEmail(string $email) {
        $query = "SELECT * FROM {$this->table} WHERE correo = :correo LIMIT 1";
        $stmt = $this->getConnection()->prepare($query);
        $stmt->execute(['correo' => $email]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function login($data) {
    
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
