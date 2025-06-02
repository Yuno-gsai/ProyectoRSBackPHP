<?php
require_once 'BaseModel.php';

class ComentsModel extends BaseModel{

    public function __construct(){
        parent::__construct();
        $this->table = 'comentarios';
    }

    public function create(array $data): bool {
        $query = "INSERT INTO {$this->table} (publicacion_id, usuario_id, contenido) VALUES (:publicacion_id, :usuario_id, :contenido)";
        $stmt = $this->getConnection()->prepare($query);
        return $stmt->execute([
            'publicacion_id' => $data['publicacion_id'],
            'usuario_id'     => $data['usuario_id'],
            'contenido'      => $data['contenido'],
        ]);
    }
    
    public function update(int $id, array $data): bool {
        $query = "UPDATE {$this->table} SET contenido = :contenido WHERE id = :id";
        $stmt = $this->getConnection()->prepare($query);
        return $stmt->execute([
            'id' => $id,
            'contenido' => $data['contenido']
        ]);
    }
    
    
}
?>