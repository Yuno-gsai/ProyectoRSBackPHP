<?php
require_once __DIR__ . '/../Models/LikesModel.php';
require_once 'BaseController.php';
class LikesController extends BaseController{
    public function __construct(){
        $this->model = new LikesModel();    
    }
}
?>