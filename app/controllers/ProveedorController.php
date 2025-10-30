<?php
require_once __DIR__ . '/../models/Proveedor.php';
require_once __DIR__ . '/../helpers/Session.php';

class ProveedorController
{
    public function index(): void
    {
        Session::requireLogin(['Administrador', 'Almacen']);
        $proveedores = Proveedor::all();
        include __DIR__ . '/../views/proveedores/index.php';
    }

    public function create(): void
    {
        Session::requireLogin(['Administrador', 'Almacen']);
        $errors = [];
        $msg    = '';
        $data   = $this->emptyProveedor();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (! Session::checkCsrf($_POST['csrf'] ?? '')) {
                $errors[] = 'Token CSRF inválido.';
            } else {
                $data = $this->extractProveedor($_POST, $errors);
                if (empty($errors)) {
                    Proveedor::create($data);
                    $msg  = 'Proveedor registrado correctamente.';
                    $data = $this->emptyProveedor();
                }
            }
        }

        include __DIR__ . '/../views/proveedores/create.php';
    }

    public function edit($id): void
    {
        Session::requireLogin(['Administrador', 'Almacen']);
        $proveedor = Proveedor::find($id);
        if (! $proveedor) {
            die('Proveedor no encontrado.');
        }

        $errors = [];
        $msg    = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (! Session::checkCsrf($_POST['csrf'] ?? '')) {
                $errors[] = 'Token CSRF inválido.';
            } else {
                $data = $this->extractProveedor($_POST, $errors);
                if (empty($errors)) {
                    Proveedor::update($id, $data);
                    $msg       = 'Proveedor actualizado correctamente.';
                    $proveedor = Proveedor::find($id);
                }
            }
        }

        include __DIR__ . '/../views/proveedores/edit.php';
    }

    public function delete($id): void
    {
        Session::requireLogin(['Administrador', 'Almacen']);
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || ! Session::checkCsrf($_POST['csrf'] ?? '')) {
            header('Location: proveedores.php?error=csrf');
            exit();
        }

        $proveedorId = (int) ($id ?: ($_POST['id'] ?? 0));
        if ($proveedorId > 0) {
            Proveedor::delete($proveedorId);
            header('Location: proveedores.php?deleted=1');
            exit();
        }

        header('Location: proveedores.php?error=not_found');
        exit();
    }

    private function extractProveedor(array $payload, array &$errors): array
    {
        $nombre      = trim($payload['nombre'] ?? '');
        $contacto    = trim($payload['contacto'] ?? '');
        $telefono    = trim($payload['telefono'] ?? '');
        $email       = trim($payload['email'] ?? '');
        $direccion   = trim($payload['direccion'] ?? '');
        $condiciones = trim($payload['condiciones_pago'] ?? '');

        if ($nombre === '') {
            $errors[] = 'El nombre es obligatorio.';
        }
        if ($contacto === '') {
            $errors[] = 'El contacto es obligatorio.';
        }
        if ($email !== '' && ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'El correo electrónico no es válido.';
        }
        if ($telefono !== '' && ! preg_match('/^[0-9 +()-]{5,20}$/', $telefono)) {
            $errors[] = 'El teléfono contiene caracteres no permitidos.';
        }

        return [
            'nombre'           => $nombre,
            'contacto'         => $contacto,
            'telefono'         => $telefono,
            'email'            => $email,
            'direccion'        => $direccion,
            'condiciones_pago' => $condiciones,
        ];
    }

    private function emptyProveedor(): array
    {
        return [
            'nombre'           => '',
            'contacto'         => '',
            'telefono'         => '',
            'email'            => '',
            'direccion'        => '',
            'condiciones_pago' => '',
        ];
    }
}
