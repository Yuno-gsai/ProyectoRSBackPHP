<?php
require_once 'BaseController.php';
require_once __DIR__ . '/../Models/ComentsModel.php';
class ComentsController extends BaseController{
    public function __construct(){
        $this->model = new ComentsModel();
    }
}
?>