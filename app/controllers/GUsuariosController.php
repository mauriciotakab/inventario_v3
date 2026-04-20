<?php
require_once __DIR__ . '/../helpers/Session.php';
require_once __DIR__ . '/../helpers/Database.php';


class GUsuariosController
{
    public function index(): void
    {
        Session::requireLogin();

$moduleTitle = 'Gestión de Usuarios';
$nombre = $_SESSION['nombre'] ?? 'TAKAB';
$role = $_SESSION['role'] ?? '';


        $db = Database::getInstance()->getConnection();

        $datos = [
            'nombre' => $nombre,
            'role' => $role,
            'last_update' => date('d/m/Y, h:i:s a'),
            'alertas' => [],
        ];

        include __DIR__ . '/../views/gestion_usuarios/index.php';
    }
}   
?>