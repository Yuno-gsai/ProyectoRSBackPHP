<?php
require_once __DIR__ . '/../Models/FriendsModel.php';
require_once 'BaseController.php';
class FriendsController extends BaseController {
    public function __construct() {
        $this->model = new FriendsModel();
    }
}
?>
