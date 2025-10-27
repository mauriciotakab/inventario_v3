<?php
require_once __DIR__ . '/../models/Cliente.php';
require_once __DIR__ . '/../helpers/Session.php';

class ClienteController
{
    public function index(): void
    {
        Session::requireLogin(['Administrador', 'Almacen']);
        $clientes = Cliente::all();
        include __DIR__ . '/../views/clientes/index.php';
    }

    public function create(): void
    {
        Session::requireLogin(['Administrador', 'Almacen']);
        $errors = [];
        $msg = '';
        $data = [
            'nombre' => '',
            'contacto' => '',
            'telefono' => '',
            'email' => '',
            'direccion' => '',
        ];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Session::checkCsrf($_POST['csrf'] ?? '')) {
                $errors[] = 'Token CSRF inválido.';
            } else {
                $data = $this->extractClienteData($_POST, $errors);

                if (empty($errors)) {
                    Cliente::create($data);
                    $msg = 'Cliente registrado correctamente.';
                    $data = [
                        'nombre' => '',
                        'contacto' => '',
                        'telefono' => '',
                        'email' => '',
                        'direccion' => '',
                    ];
                }
            }
        }

        include __DIR__ . '/../views/clientes/create.php';
    }

    public function edit($id): void
    {
        Session::requireLogin(['Administrador', 'Almacen']);
        $cliente = Cliente::find($id);
        if (!$cliente) {
            die('Cliente no encontrado.');
        }

        $errors = [];
        $msg = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Session::checkCsrf($_POST['csrf'] ?? '')) {
                $errors[] = 'Token CSRF inválido.';
            } else {
                $data = $this->extractClienteData($_POST, $errors);

                if (empty($errors)) {
                    Cliente::update($id, $data);
                    $msg = 'Cliente actualizado correctamente.';
                    $cliente = Cliente::find($id);
                }
            }
        }

        include __DIR__ . '/../views/clientes/edit.php';
    }

    public function delete($id): void
    {
        Session::requireLogin(['Administrador', 'Almacen']);
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !Session::checkCsrf($_POST['csrf'] ?? '')) {
            header('Location: clientes.php?error=csrf');
            exit();
        }

        $clienteId = (int) ($id ?: ($_POST['id'] ?? 0));
        if ($clienteId > 0) {
            Cliente::delete($clienteId);
            header('Location: clientes.php?deleted=1');
            exit();
        }

        header('Location: clientes.php?error=not_found');
        exit();
    }

    private function extractClienteData(array $payload, array &$errors): array
    {
        $nombre = trim($payload['nombre'] ?? '');
        $contacto = trim($payload['contacto'] ?? '');
        $telefono = trim($payload['telefono'] ?? '');
        $email = trim($payload['email'] ?? '');
        $direccion = trim($payload['direccion'] ?? '');

        if ($nombre === '') {
            $errors[] = 'El nombre es obligatorio.';
        }

        if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'El correo electrónico no es válido.';
        }

        if ($telefono !== '' && !preg_match('/^[0-9 +()-]{5,20}$/', $telefono)) {
            $errors[] = 'El teléfono contiene caracteres no permitidos.';
        }

        return [
            'nombre' => $nombre,
            'contacto' => $contacto,
            'telefono' => $telefono,
            'email' => $email,
            'direccion' => $direccion,
        ];
    }
}
