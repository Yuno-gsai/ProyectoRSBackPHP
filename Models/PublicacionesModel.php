<?php
require_once "BaseModel.php";

class PublicacionesModel extends BaseModel {

    private $storageAccount;
    private $containerName;
    private $sasToken;

    public function __construct() {
        parent::__construct();
        $this->table = 'publicaciones';

        $this->storageAccount = getenv('AZURE_STORAGE_ACCOUNT') ?? '';
        $this->containerName = getenv('AZURE_STORAGE_CONTAINER') ?? '';
        $this->sasToken = getenv('AZURE_STORAGE_SAS_TOKEN') ?? '';
    }

    public function create(array $data): bool {
        if (isset($data['imagen']) && str_starts_with($data['imagen'], 'data:image/')) {
            $data['imagen'] = $this->guardarImagenBase64($data['imagen']);
        }

        $query = "INSERT INTO {$this->table} (usuario_id, contenido, imagen) VALUES (:usuario_id, :contenido, :imagen)";
        $stmt = $this->getConnection()->prepare($query);

        return $stmt->execute([
            'usuario_id' => $data['usuario_id'],
            'contenido'  => $data['contenido'],
            'imagen'     => $data['imagen'] ?? null
        ]);
    }

    private function guardarImagenBase64(string $base64): string {
        preg_match('/^data:image\/(\w+);base64,/', $base64, $type);
        $ext = $type[1];
        $base64 = preg_replace('/^data:image\/\w+;base64,/', '', $base64);
        $base64 = str_replace(' ', '+', $base64);
        $data = base64_decode($base64);

        $tempFile = sys_get_temp_dir() . '/' . uniqid('pub_') . '.' . $ext;
        file_put_contents($tempFile, $data);

        $blobName = 'publicaciones/' . uniqid() . '.' . $ext;

        $url = $this->uploadToAzureBlob($this->storageAccount, $this->containerName, $blobName, $tempFile, $this->sasToken);

        unlink($tempFile);

        return $url;
    }

    private function uploadToAzureBlob($storageAccount, $containerName, $blobName, $filePath, $sasToken) {
        $url = "https://{$storageAccount}.blob.core.windows.net/{$containerName}/{$blobName}?{$sasToken}";

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
        curl_close($ch);
        fclose($fileHandle);

        if ($statusCode == 201) {
            return "https://{$storageAccount}.blob.core.windows.net/{$containerName}/{$blobName}";
        } else {
            throw new Exception("Error subiendo archivo a Azure Blob: HTTP $statusCode");
        }
    }

    public function update(int $id, array $data): bool {
        $query = "UPDATE {$this->table} SET 
            usuario_id = :usuario_id,
            contenido  = :contenido,
            imagen     = :imagen 
            WHERE id = $id";
        $stmt = $this->getConnection()->prepare($query);
        return $stmt->execute([
            'usuario_id' => $data['usuario_id'],
            'contenido'  => $data['contenido'],
            'imagen'     => $data['imagen']
        ]);
    }

    public function getAll() {
        $pdo = $this->getConnection();

        $stmt = $pdo->query("SELECT * FROM publicaciones");
        $publicaciones = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $likesStmt = $pdo->query("
            SELECT l.*, u.nombre_usuario, u.foto_perfil 
            FROM likes l
            JOIN usuarios u ON l.usuario_id = u.id
        ");
        $likes = $likesStmt->fetchAll(PDO::FETCH_ASSOC);

        $comentariosStmt = $pdo->query("
            SELECT c.*, u.nombre_usuario, u.foto_perfil 
            FROM comentarios c
            JOIN usuarios u ON c.usuario_id = u.id
        ");
        $comentarios = $comentariosStmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($publicaciones as &$pub) {
            $pub['likes'] = array_values(array_filter($likes, function ($like) use ($pub) {
                return $like['publicacion_id'] == $pub['id'];
            }));
            $pub['totalLikes'] = count($pub['likes']);

            $pub['comentarios'] = array_values(array_filter($comentarios, function ($comentario) use ($pub) {
                return $comentario['publicacion_id'] == $pub['id'];
            }));
            $pub['totalComentarios'] = count($pub['comentarios']);
        }

        return $publicaciones;
    }

    public function getPublicacionesDeAmigos($usuarioId) {
        $pdo = $this->getConnection();

        $sql = "
            SELECT p.*, u.nombre_usuario, u.foto_perfil
            FROM publicaciones p
            JOIN usuarios u ON p.usuario_id = u.id
            WHERE p.usuario_id IN (
                SELECT 
                    CASE
                        WHEN usuario1_id = :usuarioId THEN usuario2_id
                        ELSE usuario1_id
                    END AS amigo_id
                FROM amigos
                WHERE usuario1_id = :usuarioId OR usuario2_id = :usuarioId
            )
            ORDER BY p.creado_en DESC
        ";

        $stmt = $pdo->prepare($sql);
        $stmt->execute(['usuarioId' => $usuarioId]);
        $publicaciones = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $publicacionIds = array_column($publicaciones, 'id');
        if (empty($publicacionIds)) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($publicacionIds), '?'));

        $likesSql = "
            SELECT l.*, u.nombre_usuario, u.foto_perfil
            FROM likes l
            JOIN usuarios u ON l.usuario_id = u.id
            WHERE l.publicacion_id IN ($placeholders)
        ";
        $likesStmt = $pdo->prepare($likesSql);
        $likesStmt->execute($publicacionIds);
        $likes = $likesStmt->fetchAll(PDO::FETCH_ASSOC);

        $comentariosSql = "
            SELECT c.*, u.nombre_usuario, u.foto_perfil
            FROM comentarios c
            JOIN usuarios u ON c.usuario_id = u.id
            WHERE c.publicacion_id IN ($placeholders)
        ";
        $comentariosStmt = $pdo->prepare($comentariosSql);
        $comentariosStmt->execute($publicacionIds);
        $comentarios = $comentariosStmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($publicaciones as &$pub) {
            $pub['likes'] = array_values(array_filter($likes, fn($like) => $like['publicacion_id'] == $pub['id']));
            $pub['totalLikes'] = count($pub['likes']);

            $pub['comentarios'] = array_values(array_filter($comentarios, fn($comentario) => $comentario['publicacion_id'] == $pub['id']));
            $pub['totalComentarios'] = count($pub['comentarios']);
        }

        return $publicaciones;
    }

    public function getUserPublications($userID) {
        $pdo = $this->getConnection();
        $stmt = $pdo->prepare("SELECT * FROM publicaciones WHERE usuario_id = :userID");
        $stmt->execute(['userID' => $userID]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

}
?>
