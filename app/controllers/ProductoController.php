<?php
require_once __DIR__ . '/../models/Producto.php';
require_once __DIR__ . '/../helpers/Session.php';
require_once __DIR__ . '/../helpers/ActivityLogger.php';

class ProductoController
{
    public function index()
    {
        Session::requireLogin(['Administrador', 'Almacen', 'Compras']);

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
            $cantidad = (float) ($producto['stock_actual'] ?? 0);
            $minimo = (float) ($producto['stock_minimo'] ?? 0);
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
        $categorias = $db->query('SELECT id, nombre FROM categorias ORDER BY nombre ASC')->fetchAll();
        $almacenes = $db->query('SELECT id, nombre FROM almacenes ORDER BY nombre ASC')->fetchAll();
        $proveedores = $db->query('SELECT id, nombre FROM proveedores ORDER BY nombre ASC')->fetchAll();
        $unidades = $db->query('SELECT id, nombre, abreviacion FROM unidades_medida ORDER BY nombre ASC')->fetchAll();
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
        Session::requireLogin(['Administrador', 'Almacen', 'Compras']);

        $db = Database::getInstance()->getConnection();
        $categorias = $db->query('SELECT id, nombre FROM categorias ORDER BY nombre ASC')->fetchAll();
        $proveedores = $db->query('SELECT id, nombre FROM proveedores ORDER BY nombre ASC')->fetchAll();
        $almacenes = $db->query('SELECT id, nombre FROM almacenes ORDER BY nombre ASC')->fetchAll();
        $unidades = $db->query('SELECT id, nombre, abreviacion FROM unidades_medida ORDER BY nombre ASC')->fetchAll();
        $estadosProducto = Producto::estadosDisponibles();
        $tiposProducto = Producto::tiposDisponibles();

        $errors = [];
        $data = $this->defaultProductoData();
        $error = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Session::checkCsrf($_POST['csrf'] ?? '')) {
                $errors[] = 'Token CSRF invalido.';
            } else {
                $data = $this->collectProductoData($_POST, $errors);

                if (Producto::findByCodigo($data['codigo'])) {
                    $errors[] = 'Ya existe un producto con ese codigo.';
                }

                $nuevaImagen = $this->handleImagenUpload($_FILES['imagen_url'] ?? null, $errors);
                if ($nuevaImagen === false) {
                    $errors[] = 'No fue posible procesar la imagen adjunta.';
                } elseif (is_string($nuevaImagen)) {
                    $data['imagen_url'] = $nuevaImagen;
                }

                if (empty($errors)) {
                    $payload = $data;
                    $payload['last_requested_by_user_id'] = null;
                    $payload['last_request_date'] = null;

                    Producto::create($payload);
                    ActivityLogger::log('producto_creado', 'Se registro el producto ' . $payload['nombre'], [
                        'codigo' => $payload['codigo'],
                    ]);
                    header('Location: productos.php?success=1');
                    exit();
                }
            }
        }

        if (!empty($errors)) {
            $error = implode(PHP_EOL, $errors);
        }

        include __DIR__ . '/../views/productos/create.php';
    }

    public function edit($id)
    {
        Session::requireLogin(['Administrador', 'Almacen', 'Compras']);

        $producto = Producto::find($id);
        if (!$producto) {
            die('Producto no encontrado.');
        }

        $db = Database::getInstance()->getConnection();
        $categorias = $db->query('SELECT id, nombre FROM categorias ORDER BY nombre ASC')->fetchAll();
        $proveedores = $db->query('SELECT id, nombre FROM proveedores ORDER BY nombre ASC')->fetchAll();
        $almacenes = $db->query('SELECT id, nombre FROM almacenes ORDER BY nombre ASC')->fetchAll();
        $unidades = $db->query('SELECT id, nombre, abreviacion FROM unidades_medida ORDER BY nombre ASC')->fetchAll();
        $estadosProducto = Producto::estadosDisponibles();
        $tiposProducto = Producto::tiposDisponibles();

        $errors = [];
        $data = array_merge($this->defaultProductoData(), $producto);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Session::checkCsrf($_POST['csrf'] ?? '')) {
                $errors[] = 'Token CSRF invalido.';
            } else {
                $data = $this->collectProductoData($_POST, $errors, (int) $id);

                $existente = Producto::findByCodigo($data['codigo']);
                if ($existente && (int) $existente['id'] !== (int) $id) {
                    $errors[] = 'Ya existe otro producto con ese codigo.';
                }

                $nuevaImagen = $this->handleImagenUpload($_FILES['imagen_url'] ?? null, $errors, $producto['imagen_url'] ?? null);
                if ($nuevaImagen === false) {
                    $errors[] = 'No fue posible procesar la imagen adjunta.';
                } elseif (is_string($nuevaImagen)) {
                    $data['imagen_url'] = $nuevaImagen;
                } else {
                    $data['imagen_url'] = $producto['imagen_url'];
                }

                if (empty($errors)) {
                    $data['last_requested_by_user_id'] = $producto['last_requested_by_user_id'] ?? null;
                    $data['last_request_date'] = $producto['last_request_date'] ?? null;

                    Producto::update($id, $data);
                    ActivityLogger::log('producto_actualizado', 'Se actualizo el producto ' . $data['nombre'], [
                        'codigo' => $data['codigo'],
                    ]);
                    header('Location: productos.php?success=2');
                    exit();
                }
            }
        }

        $error = empty($errors) ? '' : implode(PHP_EOL, $errors);

        include __DIR__ . '/../views/productos/edit.php';
    }
    public function view($id)
    {
        Session::requireLogin(['Administrador', 'Almacen', 'Compras']);
        $producto = Producto::find($id);
        if (!$producto) {
            die('Producto no encontrado.');
        }
        include __DIR__ . '/../views/productos/view.php';
    }

    public function delete($id)
    {
        Session::requireLogin(['Administrador', 'Almacen', 'Compras']);
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !Session::checkCsrf($_POST['csrf'] ?? '')) {
            header('Location: productos.php?deleted=0&error=csrf');
            exit();
        }

        $producto = Producto::find($id);
        Producto::delete($id);

        if ($producto) {
            ActivityLogger::log('producto_eliminado', 'Se elimino el producto ' . ($producto['nombre'] ?? ''), [
                'codigo' => $producto['codigo'] ?? null,
            ]);
        } else {
            ActivityLogger::log('producto_eliminado', 'Se elimino un producto', ['producto_id' => $id]);
        }

        header('Location: productos.php?deleted=1');
        exit();
    }

    public function setActive($id, $active)
    {
        Session::requireLogin(['Administrador', 'Almacen', 'Compras']);
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !Session::checkCsrf($_POST['csrf'] ?? '')) {
            header('Location: productos.php?error=csrf');
            exit();
        }

        Producto::setActive($id, (int) $active);
        ActivityLogger::log('producto_estado', 'Se cambio la disponibilidad del producto', [
            'producto_id' => (int) $id,
            'activo' => (bool) $active,
        ]);
        header('Location: productos.php');
        exit();
    }

    public function downloadTemplate(): void
    {
        Session::requireLogin(['Administrador', 'Almacen', 'Compras']);

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
            'peso',
            'ancho',
            'alto',
            'profundidad',
            'marca',
            'color',
            'forma',
            'origen',
            'tags',
        ];

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=plantilla_productos_' . date('Ymd_His') . '.csv');

        $output = fopen('php://output', 'w');
        fputs($output, chr(239) . chr(187) . chr(191));
        fputcsv($output, $columns);
        fclose($output);
        ActivityLogger::log('productos_template', 'Descarga de plantilla de productos');
    }

    public function import(): void
    {
        Session::requireLogin(['Administrador', 'Almacen', 'Compras']);

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: productos.php');
            return;
        }

        if (!Session::checkCsrf($_POST['csrf'] ?? '')) {
            $_SESSION['productos_import'] = [
                'processed' => 0,
                'success' => 0,
                'skipped' => 0,
                'errors' => ['Token CSRF invalido.'],
            ];
            header('Location: productos.php');
            return;
        }

        if (empty($_FILES['archivo']['tmp_name']) || $_FILES['archivo']['error'] !== UPLOAD_ERR_OK) {
            $_SESSION['productos_import'] = [
                'processed' => 0,
                'success' => 0,
                'skipped' => 0,
                'errors' => ['Debes seleccionar un archivo CSV valido.'],
            ];
            header('Location: productos.php');
            return;
        }

        $handle = fopen($_FILES['archivo']['tmp_name'], 'r');
        if (!$handle) {
            $_SESSION['productos_import'] = [
                'processed' => 0,
                'success' => 0,
                'skipped' => 0,
                'errors' => ['No fue posible leer el archivo.'],
            ];
            header('Location: productos.php');
            return;
        }

        $columns = fgetcsv($handle);
        if (!$columns) {
            fclose($handle);
            $_SESSION['productos_import'] = [
                'processed' => 0,
                'success' => 0,
                'skipped' => 0,
                'errors' => ['El archivo esta vacio.'],
            ];
            header('Location: productos.php');
            return;
        }

        $columns = array_map('trim', $columns);
        $map = array_flip($columns);

        $result = [
            'processed' => 0,
            'success' => 0,
            'skipped' => 0,
            'errors' => [],
        ];

        $tiposValidos = Producto::tiposDisponibles();
        $estadosValidos = Producto::estadosDisponibles();

        while (($row = fgetcsv($handle)) !== false) {
            $result['processed']++;
            $lineNumber = $result['processed'] + 1;

            $rowAssoc = [];
            foreach ($map as $col => $index) {
                $rowAssoc[$col] = $row[$index] ?? null;
            }

            if ($this->filaVacia($rowAssoc)) {
                $result['skipped']++;
                continue;
            }

            $codigo = trim($rowAssoc['codigo'] ?? '');
            if ($codigo === '') {
                $result['errors'][] = "Fila {$lineNumber}: el campo 'codigo' es obligatorio.";
                continue;
            }

            if (Producto::findByCodigo($codigo)) {
                $result['errors'][] = "Fila {$lineNumber}: el codigo ya existe.";
                continue;
            }

            $nombre = trim($rowAssoc['nombre'] ?? '');
            if ($nombre === '') {
                $result['errors'][] = "Fila {$lineNumber}: el campo 'nombre' es obligatorio.";
                continue;
            }

            $tipo = ucfirst(strtolower(trim($rowAssoc['tipo'] ?? '')));
            if (!in_array($tipo, $tiposValidos, true)) {
                $result['errors'][] = "Fila {$lineNumber}: el tipo '{$rowAssoc['tipo']}' no es valido. Valores permitidos: " . implode(', ', $tiposValidos) . '.';
                continue;
            }

            $estado = ucfirst(strtolower(trim($rowAssoc['estado'] ?? '')));
            if ($estado === '') {
                $estado = 'Nuevo';
            }
            if (!in_array($estado, $estadosValidos, true)) {
                $result['errors'][] = "Fila {$lineNumber}: el estado '{$rowAssoc['estado']}' no es valido. Valores permitidos: " . implode(', ', $estadosValidos) . '.';
                continue;
            }

            $almacenId = (int) ($rowAssoc['almacen_id'] ?? 0);
            if ($almacenId <= 0) {
                $result['errors'][] = "Fila {$lineNumber}: el campo 'almacen_id' debe ser un numero valido.";
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

        ActivityLogger::log('productos_import', 'Importacion de productos finalizada', [
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

    private function defaultProductoData(): array
    {
        return [
            'codigo' => '',
            'nombre' => '',
            'descripcion' => '',
            'proveedor_id' => null,
            'categoria_id' => null,
            'peso' => null,
            'ancho' => null,
            'alto' => null,
            'profundidad' => null,
            'unidad_medida_id' => null,
            'clase_categoria' => '',
            'marca' => '',
            'color' => '',
            'forma' => '',
            'especificaciones_tecnicas' => '',
            'origen' => '',
            'costo_compra' => 0.0,
            'precio_venta' => 0.0,
            'stock_minimo' => 0.0,
            'stock_actual' => 0.0,
            'almacen_id' => null,
            'estado' => 'Nuevo',
            'tipo' => 'Consumible',
            'imagen_url' => null,
            'tags' => '',
            'activo_id' => 1,
        ];
    }

    private function collectProductoData(array $input, array &$errors, ?int $productoId = null): array
    {
        $data = $this->defaultProductoData();

        $data['codigo'] = trim($input['codigo'] ?? '');
        if ($data['codigo'] === '') {
            $errors[] = 'El codigo interno es obligatorio.';
        } elseif (mb_strlen($data['codigo']) > 50) {
            $errors[] = 'El codigo no debe exceder 50 caracteres.';
        }

        $data['nombre'] = trim($input['nombre'] ?? '');
        if ($data['nombre'] === '') {
            $errors[] = 'El nombre del producto es obligatorio.';
        } elseif (mb_strlen($data['nombre']) > 150) {
            $errors[] = 'El nombre no debe exceder 150 caracteres.';
        }

        $data['descripcion'] = trim($input['descripcion'] ?? '');
        if (mb_strlen($data['descripcion']) > 1000) {
            $errors[] = 'La descripcion no debe exceder 1000 caracteres.';
        }

        $data['proveedor_id'] = $this->toNullableInt($input['proveedor_id'] ?? null);
        $data['categoria_id'] = $this->toNullableInt($input['categoria_id'] ?? null);
        $data['unidad_medida_id'] = $this->toNullableInt($input['unidad_medida_id'] ?? null);
        $data['almacen_id'] = $this->toNullableInt($input['almacen_id'] ?? null);
        if (empty($data['almacen_id'])) {
            $errors[] = 'Debes seleccionar un almacen asignado.';
        }

        $data['clase_categoria'] = trim($input['clase_categoria'] ?? '');
        $data['marca'] = trim($input['marca'] ?? '');
        $data['color'] = trim($input['color'] ?? '');
        $data['forma'] = trim($input['forma'] ?? '');
        $data['especificaciones_tecnicas'] = trim($input['especificaciones_tecnicas'] ?? '');
        $data['origen'] = trim($input['origen'] ?? '');
        $data['tags'] = trim($input['tags'] ?? '');

        $data['peso'] = $this->normalizeDecimal($input['peso'] ?? null);
        $data['ancho'] = $this->normalizeDecimal($input['ancho'] ?? null);
        $data['alto'] = $this->normalizeDecimal($input['alto'] ?? null);
        $data['profundidad'] = $this->normalizeDecimal($input['profundidad'] ?? null);

        $data['stock_actual'] = $this->normalizeDecimal($input['stock_actual'] ?? 0);
        if ($data['stock_actual'] === null || $data['stock_actual'] < 0) {
            $errors[] = 'El stock actual debe ser un numero mayor o igual a cero.';
        }

        $data['stock_minimo'] = $this->normalizeDecimal($input['stock_minimo'] ?? 0);
        if ($data['stock_minimo'] === null || $data['stock_minimo'] < 0) {
            $errors[] = 'El stock minimo debe ser un numero mayor o igual a cero.';
        }

        $data['costo_compra'] = $this->normalizeDecimal($input['costo_compra'] ?? 0);
        if ($data['costo_compra'] === null || $data['costo_compra'] < 0) {
            $errors[] = 'El costo de compra debe ser un numero mayor o igual a cero.';
        }

        $data['precio_venta'] = $this->normalizeDecimal($input['precio_venta'] ?? 0);
        if ($data['precio_venta'] === null || $data['precio_venta'] < 0) {
            $errors[] = 'El precio de venta debe ser un numero mayor o igual a cero.';
        }

        $data['tipo'] = $input['tipo'] ?? 'Consumible';
        if (!in_array($data['tipo'], Producto::tiposDisponibles(), true)) {
            $errors[] = 'El tipo seleccionado no es valido.';
        }

        $data['estado'] = $input['estado'] ?? 'Nuevo';
        if (!in_array($data['estado'], Producto::estadosDisponibles(), true)) {
            $errors[] = 'El estado seleccionado no es valido.';
        }

        return $data;
    }

    private function normalizeDecimal($value): ?float
    {
        if ($value === null || $value === '') {
            return 0.0;
        }
        $normalized = str_replace(',', '.', (string) $value);
        return is_numeric($normalized) ? round((float) $normalized, 2) : null;
    }

    private function handleImagenUpload(?array $file, array &$errors, ?string $existingPath = null)
    {
        if (!$file || ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            return $existingPath;
        }

        if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
            $errors[] = 'Error al cargar la imagen (codigo ' . ($file['error'] ?? 'desconocido') . ').';
            return false;
        }

        if (($file['size'] ?? 0) > 2 * 1024 * 1024) {
            $errors[] = 'La imagen no debe superar los 2 MB.';
            return false;
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = $finfo ? finfo_file($finfo, $file['tmp_name']) : null;
        if ($finfo) {
            finfo_close($finfo);
        }

        $allowed = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/gif' => 'gif',
            'image/webp' => 'webp',
        ];

        if (!$mime || !isset($allowed[$mime])) {
            $errors[] = 'El formato de imagen no es valido.';
            return false;
        }

        $uploadDir = dirname(__DIR__, 2) . '/public/assets/images/';
        if (!is_dir($uploadDir) && !mkdir($uploadDir, 0775, true) && !is_dir($uploadDir)) {
            $errors[] = 'No fue posible preparar el directorio de imagenes.';
            return false;
        }

        $filename = uniqid('prod_', true) . '.' . $allowed[$mime];
        $destination = rtrim($uploadDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $filename;

        if (!move_uploaded_file($file['tmp_name'], $destination)) {
            $errors[] = 'No fue posible guardar la imagen subida.';
            return false;
        }

        return 'assets/images/' . $filename;
    }
}










