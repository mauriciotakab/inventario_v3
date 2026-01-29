<?php
require_once __DIR__ . '/../models/Almacen.php';
require_once __DIR__ . '/../models/Usuario.php';
require_once __DIR__ . '/../helpers/Session.php';

class AlmacenController
{
    public function index(): void
    {
        Session::requireLogin(['Administrador', 'Almacen']);
        $almacenes = Almacen::all();
        include __DIR__ . '/../views/almacenes/index.php';
    }

    public function create(): void
    {
        Session::requireLogin(['Administrador', 'Almacen']);
        $usuarios = Usuario::all();
        $error    = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (! Session::checkCsrf($_POST['csrf'] ?? '')) {
                $error = 'Token CSRF inválido.';
            } else {
                Almacen::create($_POST);
                header('Location: almacenes.php?success=1');
                exit();
            }
        }

        include __DIR__ . '/../views/almacenes/create.php';
    }

    public function edit($id): void
    {
        Session::requireLogin(['Administrador', 'Almacen']);
        $almacen  = Almacen::find($id);
        $usuarios = Usuario::all();
        $error    = '';

        if (! $almacen) {
            die('Almacén no encontrado.');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (! Session::checkCsrf($_POST['csrf'] ?? '')) {
                $error = 'Token CSRF inválido.';
            } else {
                Almacen::update($id, $_POST);
                header('Location: almacenes.php?success=2');
                exit();
            }
        }

        include __DIR__ . '/../views/almacenes/edit.php';
    }

    public function delete($id): void
    {
        Session::requireLogin(['Administrador', 'Almacen']);
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || ! Session::checkCsrf($_POST['csrf'] ?? '')) {
            header('Location: almacenes.php?error=csrf');
            exit();
        }

        $almacenId = (int) ($id ?: ($_POST['id'] ?? 0));
        if ($almacenId > 0) {
            Almacen::delete($almacenId);
            header('Location: almacenes.php?deleted=1');
            exit();
        }

        header('Location: almacenes.php?error=not_found');
        exit();
    }
}
