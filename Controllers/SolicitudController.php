<?php
require_once __DIR__ . '/../Models/SolicitudModel.php';
require_once 'BaseController.php';
class SolicitudController extends BaseController {
    public function __construct() {
        $this->model = new SolicitudModel();    
    }
}