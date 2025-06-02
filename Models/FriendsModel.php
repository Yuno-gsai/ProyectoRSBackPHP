<?php
require_once 'BaseModel.php';

class FriendsModel extends BaseModel{
    
    public function __construct(){
        parent::__construct();
        $this->table = 'amigos';
    }

    function create(array $data): bool {
        $query = "INSERT INTO {$this->table} (usuario1_id,usuario2_id) VALUES (:usuario1_id,:usuario2_id)";
        $stmt = $this->getConnection()->prepare($query);
        return $stmt->execute([
            "usuario1_id" => $data["usuario1_id"],
            "usuario2_id" => $data["usuario2_id"]
        ]);
    }

    function update(int $id,array $data): bool {
        $query = "UPDATE {$this->table} SET
            usuario1_id=:usuario1_id,
            usuario2_id=:usuario2_id
            WHERE id = $id";
        $stmt = $this->getConnection()->prepare($query);
        return $stmt->execute([
            "usuario1_id" => $data["usuario1_id"],
            "usuario2_id" => $data["usuario2_id"]
        ]);
    }

    function getAll() {
        $query = "
        SELECT
            a.id AS amistad_id,
            u1.id AS usuario1_id,
            u1.nombre_usuario AS usuario1_nombre,
            u1.correo AS usuario1_correo,
            u1.foto_perfil AS usuario1_foto,
            u1.biografia AS usuario1_biografia,
            u1.creado_en AS usuario1_creado_en,
            u2.id AS usuario2_id,
            u2.nombre_usuario AS usuario2_nombre,
            u2.correo AS usuario2_correo,
            u2.foto_perfil AS usuario2_foto,
            u2.biografia AS usuario2_biografia,
            u2.creado_en AS usuario2_creado_en,
            a.creado_en AS amistad_creado_en
        FROM amigos a
        JOIN usuarios u1 ON a.usuario1_id = u1.id
        JOIN usuarios u2 ON a.usuario2_id = u2.id
        ORDER BY a.creado_en DESC;
        ";
        $stmt = $this->getConnection()->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
}
?>