<?php
require_once __DIR__ . '/../app/controllers/UsuarioController.php';

$controller = new UsuarioController();
$id = $_POST['id'] ?? 0;
$active = $_POST['active'] ?? 0;
$controller->setActive($id, $active);
