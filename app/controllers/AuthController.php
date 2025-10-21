<?php
require_once __DIR__ . '/../models/Usuario.php';
require_once __DIR__ . '/../helpers/ActivityLogger.php';
session_start();

class AuthController
{
    public function login()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = trim($_POST['username'] ?? '');
            $password = $_POST['password'] ?? '';
            $user = Usuario::findByUsername($username);
            if ($user && password_verify($password, $user['password'])) {
                // Guardar datos en sesión
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['nombre'] = $user['nombre_completo'];
                ActivityLogger::log('login', 'Inicio de sesión exitoso');
                header("Location: dashboard.php");
                exit();
            } else {
                ActivityLogger::log('login_fallido', 'Intento de inicio de sesión fallido', ['username' => $username]);
                $error = "Usuario o contraseña incorrectos.";
            }
        }
        include __DIR__ . '/../views/auth/login.php';
    }

    public function logout()
    {
        session_start();
        ActivityLogger::log('logout', 'Cierre de sesión');
        session_unset();
        session_destroy();
        header("Location: login.php");
        exit();
    }
    public function forgotPassword()
{
    $mensaje = '';
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $username = trim($_POST['username'] ?? '');
        $user = Usuario::findByUsername($username);
        if ($user) {
            $mensaje = "Por favor contacta al administrador para restablecer tu contraseña.";
            ActivityLogger::log('forgot_password', 'Solicitud de recuperación de contraseña', ['username' => $username]);
            // O aquí puedes implementar envío de correo o generación de código temporal
        } else {
            $mensaje = "Usuario no encontrado.";
        }
    }
    include __DIR__ . '/../views/auth/forgot.php';
}

}
