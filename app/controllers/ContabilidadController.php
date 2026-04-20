<?php
require_once __DIR__ . '/../helpers/Session.php';
require_once __DIR__ . '/../helpers/Database.php';


class ContabilidadController
{
    public function index(): void
    {
        Session::requireLogin();

$moduleTitle = 'Contabilidad';
$nombre = $_SESSION['nombre'] ?? 'TAKAB';
$role = $_SESSION['role'] ?? '';


        $db = Database::getInstance()->getConnection();

        $datos = [
            'nombre' => $nombre,
            'role' => $role,
            'last_update' => date('d/m/Y, h:i:s a'),
            'alertas' => [],
        ];

        include __DIR__ . '/../views/contabilidad/index.php';
    }
}   
?>