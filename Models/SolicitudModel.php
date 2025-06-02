<?php
require_once "baseModel.php";

class SolicitudModel extends BaseModel{
    
    public function __construct(){
        parent::__construct();
        $this->table = 'solicitudes_amistad';
    }
    

    public function create(array $data): bool {
        $query = "INSERT INTO {$this->table} (solicitante_id, solicitado_id, estado) VALUES (:solicitante_id, :solicitado_id, :estado)";
        $stmt = $this->getConnection()->prepare($query);
        return $stmt->execute([
            'solicitante_id' => $data['solicitante_id'],
            'solicitado_id' => $data['solicitado_id'],
            'estado' => $data['estado'],
        ]);
    }
    public function update(int $id, array $data): bool {
        $query = "UPDATE {$this->table} 
        SET estado = :estado 
        WHERE id = :id";
        $stmt = $this->getConnection()->prepare($query);
        return $stmt->execute([
            'id' => $id,
            'estado' => $data['estado']
        ]);
    }
}
?>