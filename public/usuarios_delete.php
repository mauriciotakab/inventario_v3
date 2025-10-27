<?php
require_once __DIR__ . '/../app/controllers/UsuarioController.php';

$controller = new UsuarioController();
$id = $_POST['id'] ?? 0;
$controller->delete($id);
