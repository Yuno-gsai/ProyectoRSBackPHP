<?php
require_once  "BaseModel.php";

class PublicacionesModel extends BaseModel{

    public function __construct(){
        parent:: __construct();
        $this->table = 'publicaciones';
    }

    public function create(array $data): bool {
        // Si imagen viene en base64, la convertimos a archivo y guardamos ruta
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
    
    // Función privada para guardar imagen base64 en archivo
    private function guardarImagenBase64(string $base64): string {
        $uploadDir = __DIR__ . '/../uploads/publicaciones/';  // carpeta específica para publicaciones
    
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
    
        preg_match('/^data:image\/(\w+);base64,/', $base64, $type);
        $ext = $type[1]; // extensión como jpg, png
        $base64 = preg_replace('/^data:image\/\w+;base64,/', '', $base64);
        $base64 = str_replace(' ', '+', $base64);
        $data = base64_decode($base64);
    
        $fileName = uniqid('pub_') . '.' . $ext;
        $filePath = $uploadDir . $fileName;
    
        file_put_contents($filePath, $data);
    
        return 'uploads/publicaciones/' . $fileName;
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
    
        // Obtener publicaciones
        $stmt = $pdo->query("SELECT * FROM publicaciones");
        $publicaciones = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
        // Obtener likes con datos de usuario
        $likesStmt = $pdo->query("
            SELECT l.*, u.nombre_usuario, u.foto_perfil 
            FROM likes l
            JOIN usuarios u ON l.usuario_id = u.id
        ");
        $likes = $likesStmt->fetchAll(PDO::FETCH_ASSOC);
    
        // Obtener comentarios con datos de usuario
        $comentariosStmt = $pdo->query("
            SELECT c.*, u.nombre_usuario, u.foto_perfil 
            FROM comentarios c
            JOIN usuarios u ON c.usuario_id = u.id
        ");
        $comentarios = $comentariosStmt->fetchAll(PDO::FETCH_ASSOC);
    
        // Combinar todo por publicación
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
    
        // Obtener publicaciones de amigos
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
    
        // Obtener likes para estas publicaciones
        $publicacionIds = array_column($publicaciones, 'id');
        if (empty($publicacionIds)) {
            return []; // No hay publicaciones de amigos
        }
    
        // Preparar placeholders para IN
        $placeholders = implode(',', array_fill(0, count($publicacionIds), '?'));
    
        // Likes con usuario
        $likesSql = "
            SELECT l.*, u.nombre_usuario, u.foto_perfil
            FROM likes l
            JOIN usuarios u ON l.usuario_id = u.id
            WHERE l.publicacion_id IN ($placeholders)
        ";
        $likesStmt = $pdo->prepare($likesSql);
        $likesStmt->execute($publicacionIds);
        $likes = $likesStmt->fetchAll(PDO::FETCH_ASSOC);
    
        // Comentarios con usuario
        $comentariosSql = "
            SELECT c.*, u.nombre_usuario, u.foto_perfil
            FROM comentarios c
            JOIN usuarios u ON c.usuario_id = u.id
            WHERE c.publicacion_id IN ($placeholders)
        ";
        $comentariosStmt = $pdo->prepare($comentariosSql);
        $comentariosStmt->execute($publicacionIds);
        $comentarios = $comentariosStmt->fetchAll(PDO::FETCH_ASSOC);
    
        // Asociar likes y comentarios a cada publicación
        foreach ($publicaciones as &$pub) {
            $pub['likes'] = array_values(array_filter($likes, fn($like) => $like['publicacion_id'] == $pub['id']));
            $pub['totalLikes'] = count($pub['likes']);
    
            $pub['comentarios'] = array_values(array_filter($comentarios, fn($comentario) => $comentario['publicacion_id'] == $pub['id']));
            $pub['totalComentarios'] = count($pub['comentarios']);
        }
    
        return $publicaciones;
    }
    
    
    
}
?>