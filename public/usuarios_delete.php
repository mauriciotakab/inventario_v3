<?php
require_once __DIR__ . '/../app/controllers/UsuarioController.php';

$controller = new UsuarioController();
$controller->delete($_GET['id'] ?? 0);
