<?php
$plantillaPath = __DIR__ . '/../../../plantillas/Salida_Almacen.html';
$plantilla = file_get_contents($plantillaPath);
if ($plantilla === false) {
    http_response_code(500);
    echo 'No se pudo cargar el formato de salida.';
    return;
}

header('Content-Type: text/html; charset=utf-8');

$escape = function ($value) {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
};

$trunc = function ($value, $max) {
    $value = trim((string) $value);
    if ($value === '') {
        return '';
    }
    if (function_exists('mb_strlen') && function_exists('mb_substr')) {
        if (mb_strlen($value, 'UTF-8') > $max) {
            return mb_substr($value, 0, $max, 'UTF-8');
        }
        return $value;
    }
    if (strlen($value) > $max) {
        return substr($value, 0, $max);
    }
    return $value;
};

$formatCantidad = function ($cantidad) {
    $num = (float) $cantidad;
    $texto = number_format($num, 2, '.', '');
    $texto = rtrim(rtrim($texto, '0'), '.');
    return $texto === '' ? '0' : $texto;
};

$overlayText = function ($left, $top, $text) use ($escape) {
    if ($text === '') {
        return '';
    }
    return '<div class="pdf24_01" style="left:' . $left . 'em;top:' . $top . 'em;z-index:1000;">'
        . '<span class="pdf24_24 pdf24_14 pdf24_37">' . $escape($text) . '</span></div>';
};

$buildRows = function (array $items, array $tops) use ($overlayText, $formatCantidad, $trunc) {
    $leftCantidad = 6.6;
    $leftUnidad = 10.6;
    $leftDescripcion = 17.0;
    $salida = '';
    $max = min(count($items), count($tops));

    for ($i = 0; $i < $max; $i++) {
        $item = $items[$i];
        $top = $tops[$i];
        $cantidad = $formatCantidad($item['cantidad'] ?? 0);
        $unidad = $trunc($item['unidad'] ?? '', 10);
        $descripcion = $trunc($item['descripcion'] ?? '', 45);

        $salida .= $overlayText($leftCantidad, $top, $cantidad);
        $salida .= $overlayText($leftUnidad, $top, $unidad);
        $salida .= $overlayText($leftDescripcion, $top, $descripcion);
    }

    return $salida;
};

$receptor = $usuarioNombre ?: ('Empleado #' . ($solicitud['usuario_id'] ?? ''));
$dependencia = $solicitud['comentario'] ?? '';
$servicio = $solicitud['observacion'] ?? '';

$overlayPage1 = '';
$overlayPage1 .= $overlayText(44.2, 10.9568, $formatoFecha ?? date('d/m/Y'));
$overlayPage1 .= $overlayText(14.2, 12.4868, $trunc($receptor, 60));
$overlayPage1 .= $overlayText(11.2, 14.0168, $trunc($dependencia, 70));
$overlayPage1 .= $overlayText(16.2, 15.5468, $trunc($servicio, 70));

$materialTops = [
    21.8938, 23.8338, 25.7638, 27.6938, 29.6355,
    31.5655, 33.4955, 35.4355, 37.3655, 39.2955,
    41.238, 43.168, 45.098, 47.038, 48.968,
    50.898, 52.838, 54.7697, 56.6997, 58.6397,
];
$overlayPage1 .= $buildRows($consumibles ?? [], $materialTops);

$herramientaTops = [
    31.0855, 32.7755, 34.4755, 36.1655, 37.8655,
    39.5655, 41.258, 42.958, 44.648, 46.348,
];
$overlayPage2 = $buildRows($herramientas ?? [], $herramientaTops);

$needle = '<div class="pdf24_05 pdf24_06">';
$pos = strpos($plantilla, $needle);
if ($pos !== false) {
    $insertAt = $pos + strlen($needle);
    $plantilla = substr_replace($plantilla, $overlayPage1, $insertAt, 0);

    $offset = $insertAt + strlen($overlayPage1);
    $pos2 = strpos($plantilla, $needle, $offset);
    if ($pos2 !== false) {
        $insertAt2 = $pos2 + strlen($needle);
        $plantilla = substr_replace($plantilla, $overlayPage2, $insertAt2, 0);
    }
}

echo $plantilla;
?>
