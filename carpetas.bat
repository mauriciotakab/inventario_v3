@echo off
setlocal

:: Ruta base del proyecto
set "BASE=takab-inventario"

:: Crear carpetas principales
mkdir "%BASE%\app\controllers"
mkdir "%BASE%\app\models"
mkdir "%BASE%\app\views\auth"
mkdir "%BASE%\app\views\productos"
mkdir "%BASE%\app\views\solicitudes"
mkdir "%BASE%\app\views\almacenes"
mkdir "%BASE%\app\views\prestamos"
mkdir "%BASE%\app\views\ordenes_compra"
mkdir "%BASE%\app\views\reportes"
mkdir "%BASE%\app\helpers"
mkdir "%BASE%\public\assets\css"
mkdir "%BASE%\public\assets\js"
mkdir "%BASE%\public\assets\images"
mkdir "%BASE%\config"

:: Crear archivos de ejemplo (vacíos)
type nul > "%BASE%\app\controllers\AuthController.php"
type nul > "%BASE%\app\controllers\ProductoController.php"
type nul > "%BASE%\app\controllers\SolicitudController.php"
type nul > "%BASE%\app\controllers\AlmacenController.php"
type nul > "%BASE%\app\controllers\PrestamoController.php"
type nul > "%BASE%\app\controllers\OrdenCompraController.php"
type nul > "%BASE%\app\controllers\ReporteController.php"

type nul > "%BASE%\app\models\Usuario.php"
type nul > "%BASE%\app\models\Producto.php"
type nul > "%BASE%\app\models\Solicitud.php"
type nul > "%BASE%\app\models\Almacen.php"
type nul > "%BASE%\app\models\Prestamo.php"
type nul > "%BASE%\app\models\OrdenCompra.php"
type nul > "%BASE%\app\models\Movimiento.php"

type nul > "%BASE%\app\helpers\Database.php"
type nul > "%BASE%\app\helpers\Session.php"

type nul > "%BASE%\public\index.php"

type nul > "%BASE%\config\config.php"

echo Estructura creada exitosamente en: %BASE%
pause
