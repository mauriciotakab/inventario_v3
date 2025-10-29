<?php
require_once __DIR__ . '/../helpers/Session.php';
require_once __DIR__ . '/../models/Usuario.php';
require_once __DIR__ . '/../helpers/ActivityLogger.php';

class AuthController
{
    public function login(): void
    {
        Session::start();
        $error = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Session::checkCsrf($_POST['csrf'] ?? '')) {
                $error = 'La sesion expiro. Intenta nuevamente.';
            } else {
                $username = trim($_POST['username'] ?? '');
                $password = (string) ($_POST['password'] ?? '');
                $user = $username !== '' ? Usuario::findByUsername($username) : null;

                if ($user && password_verify($password, $user['password'])) {
                    Session::setUser($user);
                    Session::regen();
                    ActivityLogger::log('login', 'Inicio de sesion exitoso');
                    header('Location: dashboard.php');
                    exit();
                }

                ActivityLogger::log('login_fallido', 'Intento de inicio de sesion fallido', ['username' => $username]);
                $error = 'Usuario o contrasena incorrectos.';
            }
        }

        include __DIR__ . '/../views/auth/login.php';
    }

    public function logout(): void
    {
        Session::start();
        ActivityLogger::log('logout', 'Cierre de sesion');
        Session::logout();
        header('Location: login.php');
        exit();
    }

    public function forgotPassword(): void
    {
        Session::start();
        $mensaje = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Session::checkCsrf($_POST['csrf'] ?? '')) {
                $mensaje = 'La sesion expiro. Intenta nuevamente.';
            } else {
                $username = trim($_POST['username'] ?? '');
                $user = $username !== '' ? Usuario::findByUsername($username) : null;

                if ($user) {
                    $mensaje = 'Por favor contacta al administrador para restablecer tu contrasena.';
                    ActivityLogger::log('forgot_password', 'Solicitud de recuperacion de contrasena', ['username' => $username]);
                } else {
                    $mensaje = 'Usuario no encontrado.';
                }
            }
        }

        include __DIR__ . '/../views/auth/forgot.php';
    }
}



