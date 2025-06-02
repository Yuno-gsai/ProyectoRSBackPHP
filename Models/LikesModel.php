<?php
require_once 'BaseModel.php';

class LikesModel extends BaseModel{
    
    public function __construct(){
        parent::__construct();
        $this->table = 'likes';
    }

    public function create(array $data):bool{
        $query = "INSERT INTO {$this->table} (publicacion_id,usuario_id) VALUES (:publicacion_id,:usuario_id)";
        $stmt = $this->getConnection()->prepare($query);
        return $stmt->execute([
            'publicacion_id' => $data['publicacion_id'],
            'usuario_id'     => $data['usuario_id']
        ]);
    }

    public function update(int $id, array $data): bool{
        $query = "UPDATE {$this->table} SET
            publicacion_id = :publicacion_id,
            usuario_id     = :usuario_id
            WHERE id = $id";
        $stmt = $this->getConnection()->prepare($query);
        return $stmt->execute([
            'publicacion_id' => $data['publicacion_id'],
            'usuario_id'     => $data['usuario_id']
        ]);
    }

}
?>