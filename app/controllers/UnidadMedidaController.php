<?php
require_once __DIR__ . '/../models/UnidadMedida.php';
require_once __DIR__ . '/../helpers/Session.php';

class UnidadMedidaController
{
    public function index(): void
    {
        Session::requireLogin(['Administrador']);
        $unidades = UnidadMedida::all();
        include __DIR__ . '/../views/unidades/index.php';
    }

    public function create(): void
    {
        Session::requireLogin(['Administrador']);
        $error = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (! Session::checkCsrf($_POST['csrf'] ?? '')) {
                $error = 'Token CSRF inválido.';
            } elseif (empty(trim($_POST['nombre'] ?? '')) || empty(trim($_POST['abreviacion'] ?? ''))) {
                $error = 'Todos los campos son obligatorios.';
            } else {
                UnidadMedida::create([
                    'nombre'      => trim($_POST['nombre']),
                    'abreviacion' => trim($_POST['abreviacion']),
                ]);
                header('Location: unidades.php?success=1');
                exit();
            }
        }

        include __DIR__ . '/../views/unidades/create.php';
    }

    public function edit($id): void
    {
        Session::requireLogin(['Administrador']);
        $unidad = UnidadMedida::find($id);
        $error  = '';

        if (! $unidad) {
            die('Unidad no encontrada.');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (! Session::checkCsrf($_POST['csrf'] ?? '')) {
                $error = 'Token CSRF inválido.';
            } elseif (empty(trim($_POST['nombre'] ?? '')) || empty(trim($_POST['abreviacion'] ?? ''))) {
                $error = 'Todos los campos son obligatorios.';
            } else {
                UnidadMedida::update($id, [
                    'nombre'      => trim($_POST['nombre']),
                    'abreviacion' => trim($_POST['abreviacion']),
                ]);
                header('Location: unidades.php?success=2');
                exit();
            }
        }

        include __DIR__ . '/../views/unidades/edit.php';
    }

    public function delete($id): void
    {
        Session::requireLogin(['Administrador']);
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || ! Session::checkCsrf($_POST['csrf'] ?? '')) {
            header('Location: unidades.php?error=csrf');
            exit();
        }

        $unidadId = (int) ($id ?: ($_POST['id'] ?? 0));
        if ($unidadId > 0) {
            UnidadMedida::delete($unidadId);
            header('Location: unidades.php?deleted=1');
            exit();
        }

        header('Location: unidades.php?error=not_found');
        exit();
    }
}
