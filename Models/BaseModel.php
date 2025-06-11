<?php
require_once  __DIR__ . '/../DataBase/DatabaseConnection.php';

abstract class BaseModel extends DatabaseConnection{  
    
    protected $table;

    public function __construct() {
        parent::__construct();
    }
    
    public function getAll() {
        $query = "SELECT * FROM {$this->table}";
        return $this->ExecuteQuery($query)->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function get($id) {
        $query = "SELECT * FROM {$this->table} WHERE id = $id";
        return $this->ExecuteQuery($query)->fetch(PDO::FETCH_ASSOC);
    }
    
    public function delete($id) {
        $query = "DELETE FROM $this->table WHERE id = $id";
        return $this->ExecuteQuery($query);
    }
    
}
?>