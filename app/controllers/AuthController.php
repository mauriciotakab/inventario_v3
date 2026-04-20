<?php
require_once __DIR__ . '/../models/Usuario.php';
session_start();

class AuthController
{
    public function login()
    {
        $allowedModules = ['nomina', 'rh', 'gestion_usuarios','contabilidad','bancos','compras','inventario','ventas','proyectos'];
        $module = $_GET['module'] ?? $_POST['module'] ?? null;
        if ($module !== null && !in_array($module, $allowedModules, true)) {
            $module = null;
        }

        // Soportar redirección post-login (por ejemplo, cuando Session::requireLogin manda ?next=...)
        $next = $_GET['next'] ?? $_POST['next'] ?? null;
        $next = $this->sanitizeNext($next);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = trim($_POST['username'] ?? '');
            $password = $_POST['password'] ?? '';
            $user = Usuario::findByUsername($username);
            if ($user && password_verify($password, $user['password'])) {
                // Guardar datos en sesión
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['nombre'] = $user['nombre_completo'];
                // Redirección:
                // 1) Si venimos de una página protegida -> volver ahí
                // 2) Si venimos de un deep-link con module -> ir a ese módulo
                // 3) Default -> menú de módulos
                if ($next) {
                    header("Location: {$next}");
                    exit();
                }
                if ($module) {
                    $redirect = $this->routeByModule($module);
                    header("Location: {$redirect}");
                    exit();
                }
                header('Location: menu.php');
                exit();
            } else {
                $error = "Usuario o contraseña incorrectos.";
            }
        }
        include __DIR__ . '/../views/auth/login.php';
    }

    private function routeByModule(string $module): string
    {
        // Mantén rutas locales y controladas (evita open redirect)
        switch ($module) {
            case 'nomina':
                return 'nomina.php';
             case 'rh':
                return 'rh.php';
            case 'gestion_usuarios':
                return 'gestion_usuarios.php';
            case 'contabilidad':
                return 'contabilidad.php';
            case 'bancos':
                return "bancos.php";
            case 'compras':
                return 'compras.php';
            case 'ventas':
                return 'ventas.php';
            case 'proyectos':
                return 'proyectos.php';
            case 'inventario':
            default:
                return 'dashboard.php';
        }
    }

    private function sanitizeNext($next): ?string
    {
        if (!$next || !is_string($next)) return null;
        $next = trim($next);

        // Rechazar intentos de URLs externas
        if (preg_match('/^(https?:)?\/\//i', $next)) return null;

        // Solo permitir archivos .php en el mismo directorio (sin slashes)
        // Soporta querystring, e.g. dashboard.php?x=1
        $parts = explode('?', $next, 2);
        $file = $parts[0] ?? '';
        if (!preg_match('/^[a-zA-Z0-9_-]+\.php$/', $file)) return null;

        // Normalizar: quedarnos con basename
        $file = basename($file);
        if ($file === '') return null;

        return $file . (isset($parts[1]) ? ('?' . $parts[1]) : '');
    }

    public function logout()
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
        session_unset();
        session_destroy();
        header("Location: index.php");
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
            // O aquí puedes implementar envío de correo o generación de código temporal
        } else {
            $mensaje = "Usuario no encontrado.";
        }
    }
    include __DIR__ . '/../views/auth/forgot.php';
}

}
