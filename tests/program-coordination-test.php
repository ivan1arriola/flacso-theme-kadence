<?php
/**
 * La coordinación de un programa puede faltar, venir vacía o venir malformada: las plantillas nunca deben fallar.
 * Antes, `(array) get_post_meta(..., 'coordinacion', true)` daba [''] si faltaba el metadato y el bucle de
 * archive-programa-academico.php fallaba con "Cannot access offset of type string on string" (HTTP 500 en PHP 8).
 * Ejecutable sin WordPress: php tests/program-coordination-test.php
 */
$root = dirname(__DIR__);
if (!defined('ABSPATH')) { define('ABSPATH', __DIR__ . '/'); }

function coord_check($ok, $description) {
    if (!$ok) { fwrite(STDERR, "FAIL: " . $description . "\n"); exit(1); }
}

$GLOBALS['meta'] = [];
function get_post_meta($id, $key = '', $single = false) { return $GLOBALS['meta'][$id][$key] ?? ''; }
function absint($v) { return abs((int) $v); }
function __($t) { return $t; }
function sanitize_text_field($v) { return trim(strip_tags((string) $v)); }

$helper = $root . '/inc/program-coordination.php';
coord_check(is_readable($helper), 'existe inc/program-coordination.php');
require $helper;
coord_check(function_exists('flacso_theme_program_coordination'), 'existe flacso_theme_program_coordination()');

// Metadato ausente (get_post_meta devuelve ''): lista vacía, no [''].
coord_check(flacso_theme_program_coordination(1) === [], 'sin metadato devuelve lista vacía');

// Valores no válidos.
foreach ([false, null, 'texto suelto', 0, 'a:0:{}'] as $i => $malo) {
    $GLOBALS['meta'][2]['coordinacion'] = $malo;
    coord_check(flacso_theme_program_coordination(2) === [], 'valor no válido #' . $i . ' devuelve lista vacía');
}

// Lista vacía.
$GLOBALS['meta'][3]['coordinacion'] = [];
coord_check(flacso_theme_program_coordination(3) === [], 'lista vacía devuelve lista vacía');

// Filas mezcladas: se descartan las que no son arreglos o no tienen ni nombre ni docente.
$GLOBALS['meta'][4]['coordinacion'] = [
    'una cadena',
    ['nombre' => 'Ana Pérez', 'rol' => 'Coordinadora'],
    ['docente_id' => '77'],
    ['nombre' => '', 'docente_id' => 0],
    [],
    ['nombre' => '  <b>Luis</b> ', 'docente_id' => 5, 'rol' => ''],
];
$filas = flacso_theme_program_coordination(4);
coord_check(count($filas) === 3, 'quedan solo las filas con nombre o docente (hay ' . count($filas) . ')');
coord_check($filas[0] === ['nombre' => 'Ana Pérez', 'docente_id' => 0, 'rol' => 'Coordinadora'], 'fila con nombre y rol se normaliza');
coord_check($filas[1] === ['nombre' => '', 'docente_id' => 77, 'rol' => 'Coordinación'], 'fila solo con docente usa el rol por defecto');
coord_check($filas[2]['nombre'] === 'Luis' && $filas[2]['docente_id'] === 5 && $filas[2]['rol'] === 'Coordinación', 'el nombre se limpia y el rol vacío usa el valor por defecto');
foreach ($filas as $fila) {
    coord_check(array_keys($fila) === ['nombre', 'docente_id', 'rol'], 'todas las filas tienen siempre las tres claves');
}

// Las plantillas ya no castean el metadato a mano.
foreach (['archive-programa-academico.php', 'single-programa-academico.php'] as $plantilla) {
    $codigo = (string) file_get_contents($root . '/' . $plantilla);
    coord_check(strpos($codigo, "(array) get_post_meta") === false || strpos($codigo, "'coordinacion'") === false || strpos($codigo, 'flacso_theme_program_coordination') !== false, $plantilla . ' no castea el metadato a mano');
    coord_check(strpos($codigo, 'flacso_theme_program_coordination(') !== false, $plantilla . ' usa flacso_theme_program_coordination()');
    coord_check(!preg_match("/\(array\)\s*get_post_meta\([^)]*'coordinacion'/", $codigo), $plantilla . ' no contiene (array) get_post_meta(..., coordinacion)');
}

echo "OK coordinación de programas\n";
