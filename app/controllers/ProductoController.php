<?php
require_once __DIR__ . '/../models/Producto.php';
require_once __DIR__ . '/../helpers/Session.php';
require_once __DIR__ . '/../helpers/ActivityLogger.php';

class ProductoController
{
    public function index()
    {
        Session::requireLogin(['Administrador', 'Almacen','Compras']);

        $filtros = [
            'buscar' => trim($_GET['buscar'] ?? ''),
            'nombre' => trim($_GET['nombre'] ?? ''),
            'codigo' => trim($_GET['codigo'] ?? ''),
            'tipo' => $_GET['tipo'] ?? '',
            'categoria_id' => $_GET['categoria_id'] ?? '',
            'almacen_id' => $_GET['almacen_id'] ?? '',
            'proveedor_id' => $_GET['proveedor_id'] ?? '',
            'estado' => $_GET['estado'] ?? '',
            'activo_id' => $_GET['activo_id'] ?? '',
            'stock_flag' => $_GET['stock_flag'] ?? '',
            'unidad_medida_id' => $_GET['unidad_medida_id'] ?? '',
            'tags' => trim($_GET['tags'] ?? ''),
            'fecha_desde' => $_GET['fecha_desde'] ?? '',
            'fecha_hasta' => $_GET['fecha_hasta'] ?? '',
            'valor_min' => $_GET['valor_min'] ?? '',
            'valor_max' => $_GET['valor_max'] ?? '',
        ];

        $productos = Producto::all($filtros);

        $stats = [
            'total' => count($productos),
            'consumibles' => 0,
            'herramientas' => 0,
            'stock_bajo' => 0,
            'sin_stock' => 0,
            'activos' => 0,
            'inactivos' => 0,
            'valor_total' => 0.0,
        ];

        foreach ($productos as $producto) {
            $cantidad = (int) ($producto['stock_actual'] ?? 0);
            $minimo = (int) ($producto['stock_minimo'] ?? 0);
            $valorProducto = (float) ($producto['costo_compra'] ?? 0) * $cantidad;
            $stats['valor_total'] += $valorProducto;

            if (($producto['tipo'] ?? '') === 'Consumible') {
                $stats['consumibles']++;
            } elseif (($producto['tipo'] ?? '') === 'Herramienta') {
                $stats['herramientas']++;
            }

            if ($cantidad < $minimo) {
                $stats['stock_bajo']++;
            }
            if ($cantidad <= 0) {
                $stats['sin_stock']++;
            }
            if ((int) ($producto['activo_id'] ?? 1) === 1) {
                $stats['activos']++;
            } else {
                $stats['inactivos']++;
            }
        }

        $db = Database::getInstance()->getConnection();
        $categorias = $db->query("SELECT id, nombre FROM categorias ORDER BY nombre ASC")->fetchAll();
        $almacenes = $db->query("SELECT id, nombre FROM almacenes ORDER BY nombre ASC")->fetchAll();
        $proveedores = $db->query("SELECT id, nombre FROM proveedores ORDER BY nombre ASC")->fetchAll();
        $unidades = $db->query("SELECT id, nombre, abreviacion FROM unidades_medida ORDER BY nombre ASC")->fetchAll();
        $estadosActivos = Producto::estadosActivos();
        $estadosProducto = Producto::estadosDisponibles();
        $tiposProducto = Producto::tiposDisponibles();

        $hayFiltros = false;
        foreach ($filtros as $valor) {
            if ($valor !== '' && $valor !== null) {
                $hayFiltros = true;
                break;
            }
        }

        $alerta = [
            'success' => $_GET['success'] ?? null,
            'deleted' => $_GET['deleted'] ?? null,
        ];

        $importAlert = $_SESSION['productos_import'] ?? null;
        if (isset($_SESSION['productos_import'])) {
            unset($_SESSION['productos_import']);
        }

        include __DIR__ . '/../views/productos/index.php';
    }

    public function create()
    {
        Session::requireLogin(['Administrador', 'Almacen','Compras']);
        $db = Database::getInstance()->getConnection();
        $categorias = $db->query("SELECT id, nombre FROM categorias ORDER BY nombre ASC")->fetchAll();
        $proveedores = $db->query("SELECT id, nombre FROM proveedores ORDER BY nombre ASC")->fetchAll();
        $almacenes = $db->query("SELECT id, nombre FROM almacenes ORDER BY nombre ASC")->fetchAll();
        $unidades = $db->query("SELECT id, nombre, abreviacion FROM unidades_medida ORDER BY nombre ASC")->fetchAll();
        $estadosProducto = Producto::estadosDisponibles();
        $tiposProducto = Producto::tiposDisponibles();
        $error = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (Producto::findByCodigo($_POST['codigo'])) {
                $error = "Ya existe un producto con ese código.";
            } else {
                $imagen_path = null;
                if (!empty($_FILES['imagen_url']['name'])) {
                    $upload_dir = __DIR__ . '/..//assets/images/';
                    if (!is_dir($upload_dir)) {
                        mkdir($upload_dir, 0777, true);
                    }
                    $nombreArchivo = uniqid() . '_' . basename($_FILES['imagen_url']['name']);
                    $target_file = $upload_dir . $nombreArchivo;
                    $tipoArchivo = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
                    $check = @getimagesize($_FILES['imagen_url']['tmp_name']);
                    if ($check === false) {
                        $error = "El archivo no es una imagen válida.";
                    } elseif ($_FILES['imagen_url']['size'] > 2 * 1024 * 1024) {
                        $error = "La imagen es demasiado grande (máx. 2MB).";
                    } elseif (!in_array($tipoArchivo, ['jpg', 'jpeg', 'png', 'gif', 'webp'], true)) {
                        $error = "Formato de imagen no permitido.";
                    } elseif (!move_uploaded_file($_FILES['imagen_url']['tmp_name'], $target_file)) {
                        $error = "Error al subir la imagen.";
                    } else {
                        $imagen_path = 'assets/images/' . $nombreArchivo;
                    }
                }
                if (empty($error)) {
                    $data = $_POST;
                    $data['imagen_url'] = $imagen_path;
                    $data['last_requested_by_user_id'] = null;
                    $data['last_request_date'] = null;
                    Producto::create($data);
                    ActivityLogger::log('producto_creado', 'Se registró el producto ' . $data['nombre'], [
                        'codigo' => $data['codigo'] ?? null,
                    ]);
                    header('Location: productos.php?success=1');
                    exit();
                }
            }
        }

        include __DIR__ . '/../views/productos/create.php';
    }

    public function edit($id)
    {
        Session::requireLogin(['Administrador', 'Almacen','Compras']);
        $producto = Producto::find($id);
        if (!$producto) {
            die('Producto no encontrado.');
        }

        $db = Database::getInstance()->getConnection();
        $categorias = $db->query("SELECT id, nombre FROM categorias ORDER BY nombre ASC")->fetchAll();
        $proveedores = $db->query("SELECT id, nombre FROM proveedores ORDER BY nombre ASC")->fetchAll();
        $almacenes = $db->query("SELECT id, nombre FROM almacenes ORDER BY nombre ASC")->fetchAll();
        $unidades = $db->query("SELECT id, nombre, abreviacion FROM unidades_medida ORDER BY nombre ASC")->fetchAll();
        $estadosProducto = Producto::estadosDisponibles();
        $tiposProducto = Producto::tiposDisponibles();
        $error = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (Producto::existsCodigoExcept($_POST['codigo'], $id)) {
                $error = "Ya existe otro producto con ese código.";
            } else {
                $imagen_path = $producto['imagen_url'];
                if (!empty($_FILES['imagen_url']['name'])) {
                    $upload_dir = __DIR__ . '/..//assets/images/';
                    if (!is_dir($upload_dir)) {
                        mkdir($upload_dir, 0777, true);
                    }
                    $nombreArchivo = uniqid() . '_' . basename($_FILES['imagen_url']['name']);
                    $target_file = $upload_dir . $nombreArchivo;
                    $tipoArchivo = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
                    $check = @getimagesize($_FILES['imagen_url']['tmp_name']);
                    if ($check === false) {
                        $error = "El archivo no es una imagen válida.";
                    } elseif ($_FILES['imagen_url']['size'] > 2 * 1024 * 1024) {
                        $error = "La imagen es demasiado grande (máx. 2MB).";
                    } elseif (!in_array($tipoArchivo, ['jpg', 'jpeg', 'png', 'gif', 'webp'], true)) {
                        $error = "Formato de imagen no permitido.";
                    } elseif (!move_uploaded_file($_FILES['imagen_url']['tmp_name'], $target_file)) {
                        $error = "Error al subir la imagen.";
                    } else {
                        $imagen_path = 'assets/images/' . $nombreArchivo;
                    }
                }
                if (empty($error)) {
                    $data = $_POST;
                    $data['imagen_url'] = $imagen_path;
                    $data['last_requested_by_user_id'] = $producto['last_requested_by_user_id'];
                    $data['last_request_date'] = $producto['last_request_date'];
                    Producto::update($id, $data);
                    ActivityLogger::log('producto_actualizado', 'Se actualizó el producto ' . $data['nombre'], [
                        'codigo' => $data['codigo'] ?? null,
                    ]);
                    header('Location: productos.php?success=2');
                    exit();
                }
            }
        }

        include __DIR__ . '/../views/productos/edit.php';
    }

    public function view($id)
    {
        Session::requireLogin(['Administrador', 'Almacen','Compras']);
        $producto = Producto::find($id);
        if (!$producto) {
            die('Producto no encontrado.');
        }
        include __DIR__ . '/../views/productos/view.php';
    }

    public function delete($id)
    {
        Session::requireLogin(['Administrador', 'Almacen','Compras']);
        $producto = Producto::find($id);
        Producto::delete($id);
        if ($producto) {
            ActivityLogger::log('producto_eliminado', 'Se eliminó el producto ' . ($producto['nombre'] ?? ''), [
                'codigo' => $producto['codigo'] ?? null,
            ]);
        } else {
            ActivityLogger::log('producto_eliminado', 'Se eliminó un producto', ['producto_id' => $id]);
        }
        header('Location: productos.php?deleted=1');
        exit();
    }

    public function setActive($id, $active)
    {
        Session::requireLogin(['Administrador', 'Almacen']);
        Producto::setActive($id, $active);
        ActivityLogger::log('producto_estado', 'Se cambió la disponibilidad del producto', [
            'producto_id' => (int) $id,
            'activo' => (bool) $active,
        ]);
        header('Location: productos.php');
        exit();
    }

    public function downloadTemplate(): void
    {
        Session::requireLogin(['Administrador', 'Almacen']);

        $columns = [
            'codigo',
            'nombre',
            'descripcion',
            'tipo',
            'estado',
            'categoria_id',
            'proveedor_id',
            'almacen_id',
            'unidad_medida_id',
            'stock_actual',
            'stock_minimo',
            'costo_compra',
            'precio_venta',
            'marca',
            'color',
            'forma',
            'origen',
            'tags'
        ];

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=plantilla_productos_' . date('Ymd_His') . '.csv');

        $output = fopen('php://output', 'w');
        fputs($output, chr(239) . chr(187) . chr(191)); // BOM UTF-8
        fputcsv($output, $columns);
        fputcsv($output, [
            'HERR-001',
            'Taladro percutor',
            'Taladro 1/2" con velocidad variable.',
            'Herramienta',
            'Nuevo',
            '1',
            '1',
            '1',
            '1',
            '5',
            '2',
            '1500',
            '2200',
            'Bosch',
            'Azul',
            'Compacto',
            'Alemania',
            'herramientas,taladro'
        ]);
        fputcsv($output, [
            'CON-200',
            'Cinta aislante',
            'Cinta aislante 19mm negra.',
            'Consumible',
            'Nuevo',
            '2',
            '',
            '1',
            '2',
            '120',
            '20',
            '12',
            '20',
            '3M',
            'Negro',
            '',
            'México',
            'material-electrico'
        ]);
        fclose($output);
        ActivityLogger::log('plantilla_productos', 'Descarga de plantilla de productos');
        exit();
    }

    public function import(): void
    {
        Session::requireLogin(['Administrador', 'Almacen']);

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: productos.php');
            exit();
        }

        $result = [
            'success' => 0,
            'errors' => [],
            'skipped' => 0,
            'processed' => 0,
        ];

        if (empty($_FILES['productos_archivo']) || $_FILES['productos_archivo']['error'] !== UPLOAD_ERR_OK) {
            $result['errors'][] = 'No se pudo leer el archivo subido.';
            $_SESSION['productos_import'] = $result;
            header('Location: productos.php');
            exit();
        }

        $tmpPath = $_FILES['productos_archivo']['tmp_name'];
        $handle = fopen($tmpPath, 'r');
        if ($handle === false) {
            $result['errors'][] = 'No se pudo abrir el archivo para lectura.';
            $_SESSION['productos_import'] = $result;
            header('Location: productos.php');
            exit();
        }

        $header = fgetcsv($handle, 0, ',');
        if ($header === false) {
            $result['errors'][] = 'El archivo está vacío.';
            fclose($handle);
            $_SESSION['productos_import'] = $result;
            header('Location: productos.php');
            exit();
        }

        if (!empty($header)) {
            $header[0] = preg_replace('/^\xEF\xBB\xBF/', '', $header[0]);
        }

        $header = array_map(static function ($value) {
            return strtolower(trim($value ?? ''));
        }, $header);

        $required = ['codigo', 'nombre', 'tipo', 'almacen_id', 'stock_actual', 'stock_minimo'];
        $missing = array_diff($required, $header);
        if (!empty($missing)) {
            $result['errors'][] = 'Faltan columnas obligatorias en la cabecera: ' . implode(', ', $missing);
            fclose($handle);
            $_SESSION['productos_import'] = $result;
            header('Location: productos.php');
            exit();
        }

        $tiposValidos = Producto::tiposDisponibles();
        $estadosValidos = Producto::estadosDisponibles();

        $lineNumber = 1;
        while (($row = fgetcsv($handle, 0, ',')) !== false) {
            $lineNumber++;
            if ($this->filaVacia($row)) {
                continue;
            }
            $result['processed']++;

            $rowAssoc = [];
            foreach ($header as $index => $column) {
                $rowAssoc[$column] = $row[$index] ?? '';
            }

            $codigo = trim($rowAssoc['codigo'] ?? '');
            if ($codigo === '') {
                $result['errors'][] = "Fila {$lineNumber}: el campo 'codigo' es obligatorio.";
                continue;
            }

            if (Producto::findByCodigo($codigo)) {
                $result['errors'][] = "Fila {$lineNumber}: ya existe un producto con el código {$codigo}.";
                $result['skipped']++;
                continue;
            }

            $nombre = trim($rowAssoc['nombre'] ?? '');
            if ($nombre === '') {
                $result['errors'][] = "Fila {$lineNumber}: el campo 'nombre' es obligatorio.";
                continue;
            }

            $tipo = ucfirst(strtolower(trim($rowAssoc['tipo'] ?? '')));
            if (!in_array($tipo, $tiposValidos, true)) {
                $result['errors'][] = "Fila {$lineNumber}: el tipo '{$rowAssoc['tipo']}' no es válido. Valores permitidos: " . implode(', ', $tiposValidos) . '.';
                continue;
            }

            $estado = ucfirst(strtolower(trim($rowAssoc['estado'] ?? '')));
            if ($estado === '') {
                $estado = 'Nuevo';
            }
            if (!in_array($estado, $estadosValidos, true)) {
                $result['errors'][] = "Fila {$lineNumber}: el estado '{$rowAssoc['estado']}' no es válido. Valores permitidos: " . implode(', ', $estadosValidos) . '.';
                continue;
            }

            $almacenId = (int) ($rowAssoc['almacen_id'] ?? 0);
            if ($almacenId <= 0) {
                $result['errors'][] = "Fila {$lineNumber}: el campo 'almacen_id' debe ser un número válido.";
                continue;
            }

            $stockActual = (float) str_replace(',', '.', $rowAssoc['stock_actual'] ?? 0);
            $stockMinimo = (float) str_replace(',', '.', $rowAssoc['stock_minimo'] ?? 0);
            if ($stockActual < 0 || $stockMinimo < 0) {
                $result['errors'][] = "Fila {$lineNumber}: el stock no puede ser negativo.";
                continue;
            }

            $costoCompra = (float) str_replace(',', '.', $rowAssoc['costo_compra'] ?? 0);
            $precioVenta = (float) str_replace(',', '.', $rowAssoc['precio_venta'] ?? 0);

            $payload = [
                'codigo' => $codigo,
                'nombre' => $nombre,
                'descripcion' => trim($rowAssoc['descripcion'] ?? ''),
                'proveedor_id' => $this->toNullableInt($rowAssoc['proveedor_id'] ?? null),
                'categoria_id' => $this->toNullableInt($rowAssoc['categoria_id'] ?? null),
                'peso' => $this->toNullableFloat($rowAssoc['peso'] ?? null),
                'ancho' => $this->toNullableFloat($rowAssoc['ancho'] ?? null),
                'alto' => $this->toNullableFloat($rowAssoc['alto'] ?? null),
                'profundidad' => $this->toNullableFloat($rowAssoc['profundidad'] ?? null),
                'unidad_medida_id' => $this->toNullableInt($rowAssoc['unidad_medida_id'] ?? null),
                'clase_categoria' => trim($rowAssoc['clase_categoria'] ?? ''),
                'marca' => trim($rowAssoc['marca'] ?? ''),
                'color' => trim($rowAssoc['color'] ?? ''),
                'forma' => trim($rowAssoc['forma'] ?? ''),
                'especificaciones_tecnicas' => trim($rowAssoc['especificaciones_tecnicas'] ?? ''),
                'origen' => trim($rowAssoc['origen'] ?? ''),
                'costo_compra' => $costoCompra,
                'precio_venta' => $precioVenta,
                'stock_minimo' => $stockMinimo,
                'stock_actual' => $stockActual,
                'almacen_id' => $almacenId,
                'estado' => $estado,
                'tipo' => $tipo,
                'imagen_url' => null,
                'last_requested_by_user_id' => null,
                'last_request_date' => null,
                'tags' => trim($rowAssoc['tags'] ?? ''),
                'activo_id' => 1,
            ];

            try {
                Producto::create($payload);
                $result['success']++;
            } catch (\Throwable $e) {
                $result['errors'][] = "Fila {$lineNumber}: error al registrar el producto ({$e->getMessage()}).";
            }
        }

        fclose($handle);

        ActivityLogger::log('productos_import', 'Importación de productos finalizada', [
            'exitosos' => $result['success'],
            'procesados' => $result['processed'],
            'omitidos' => $result['skipped'],
        ]);

        $_SESSION['productos_import'] = $result;
        header('Location: productos.php');
        exit();
    }

    private function filaVacia(array $values): bool
    {
        foreach ($values as $value) {
            if (trim((string) $value) !== '') {
                return false;
            }
        }
        return true;
    }

    private function toNullableInt($value): ?int
    {
        if ($value === null || trim((string) $value) === '') {
            return null;
        }
        return (int) $value ?: null;
    }

    private function toNullableFloat($value): ?float
    {
        if ($value === null || trim((string) $value) === '') {
            return null;
        }
        $normalized = str_replace(',', '.', (string) $value);
        return is_numeric($normalized) ? (float) $normalized : null;
    }
}
