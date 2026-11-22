<?php
/**
 * Las tarjetas de los listados sirven una versión WebP de los tamaños del tema (flacso-card 480x600 y los 4x5 de 800x1000)
 * cuando existe un archivo .webp junto al PNG/JPEG. No se usa el filtro global image_editor_output_format porque
 * convertiría todas las imágenes del sitio, incluida la de las vistas previas de enlaces (og_meta_size).
 * Ejecutable sin WordPress: php tests/webp-cards-test.php
 */
$root = dirname(__DIR__);
if (!defined('ABSPATH')) { define('ABSPATH', __DIR__ . '/'); }

function webp_check($ok, $description) {
    if (!$ok) { fwrite(STDERR, "FAIL: " . $description . "\n"); exit(1); }
}

$GLOBALS['hooks'] = [];
$GLOBALS['uploads'] = sys_get_temp_dir() . '/flacso-webp-test-' . getmypid();
@mkdir($GLOBALS['uploads'] . '/2026/05', 0777, true);
function add_action($tag, $cb, $priority = 10, $args = 1) { $GLOBALS['hooks'][$tag][] = [$cb, $priority, $args]; return true; }
function add_filter($tag, $cb, $priority = 10, $args = 1) { return add_action($tag, $cb, $priority, $args); }
function wp_get_upload_dir() { return ['basedir' => $GLOBALS['uploads'], 'baseurl' => 'https://sitio.test/wp-content/uploads']; }
function wp_delete_file($f) { @unlink($f); }
function get_attached_file($id) { return $GLOBALS['uploads'] . '/2026/05/original.png'; }

require $root . '/inc/webp-cards.php';

function touch_file($rel) { $p = $GLOBALS['uploads'] . '/' . $rel; file_put_contents($p, 'x'); return $p; }

webp_check(function_exists('flacso_child_webp_companion_path'), 'existe flacso_child_webp_companion_path()');
webp_check(function_exists('flacso_child_webp_companion_url'), 'existe flacso_child_webp_companion_url()');
webp_check(function_exists('flacso_child_card_srcset_webp'), 'existe el filtro del srcset');
webp_check(function_exists('flacso_child_card_image_src_webp'), 'existe el filtro de la URL principal');
webp_check(function_exists('flacso_child_generate_webp_companions'), 'existe el generador de archivos WebP');

// Ruta del archivo acompañante: solo para los tamaños de tarjeta del tema.
webp_check(flacso_child_webp_companion_path('/u/2026/05/afiche-480x600.png') === '/u/2026/05/afiche-480x600.webp', 'PNG de 480x600 -> .webp');
webp_check(flacso_child_webp_companion_path('/u/2026/05/afiche-800x1000.jpg') === '/u/2026/05/afiche-800x1000.webp', 'JPG de 800x1000 -> .webp');
webp_check(flacso_child_webp_companion_path('/u/2026/05/afiche-800x1000.jpeg') === '/u/2026/05/afiche-800x1000.webp', 'JPEG de 800x1000 -> .webp');
webp_check(flacso_child_webp_companion_path('/u/2026/05/afiche-768x960.png') === '', 'un tamaño que no es de tarjeta no se toca');
webp_check(flacso_child_webp_companion_path('/u/2026/05/afiche.png') === '', 'el original (sin tamaño) no se toca');
webp_check(flacso_child_webp_companion_path('/u/2026/05/og-1200x630.png') === '', 'la imagen de vista previa de enlaces (og) no se toca');
webp_check(flacso_child_webp_companion_path('/u/2026/05/afiche-480x600.webp') === '', 'un archivo que ya es WebP no se toca');
webp_check(flacso_child_webp_companion_path('/u/2026/05/afiche-480x600.gif') === '', 'un GIF no se toca');

// URL: se reemplaza solo si el .webp existe en disco.
$url = 'https://sitio.test/wp-content/uploads/2026/05/afiche-480x600.png';
webp_check(flacso_child_webp_companion_url($url) === $url, 'sin archivo .webp se conserva la URL original');
touch_file('2026/05/afiche-480x600.webp');
webp_check(flacso_child_webp_companion_url($url) === 'https://sitio.test/wp-content/uploads/2026/05/afiche-480x600.webp', 'con el .webp en disco se entrega el .webp');
webp_check(flacso_child_webp_companion_url('https://otro.test/x/afiche-480x600.png') === 'https://otro.test/x/afiche-480x600.png', 'una URL de otro dominio no se toca');
webp_check(flacso_child_webp_companion_url('https://sitio.test/wp-content/uploads/2026/05/afiche-768x960.png') === 'https://sitio.test/wp-content/uploads/2026/05/afiche-768x960.png', 'un tamaño ajeno no se toca');

// Filtro del srcset: reescribe solo los candidatos de tarjeta que tienen su .webp.
touch_file('2026/05/afiche-800x1000.webp');
$sources = [
    480 => ['url' => 'https://sitio.test/wp-content/uploads/2026/05/afiche-480x600.png', 'descriptor' => 'w', 'value' => 480],
    768 => ['url' => 'https://sitio.test/wp-content/uploads/2026/05/afiche-768x960.png', 'descriptor' => 'w', 'value' => 768],
    800 => ['url' => 'https://sitio.test/wp-content/uploads/2026/05/afiche-800x1000.png', 'descriptor' => 'w', 'value' => 800],
];
$out = flacso_child_card_srcset_webp($sources, [480, 600], 'x', [], 1);
webp_check(substr($out[480]['url'], -5) === '.webp' && substr($out[800]['url'], -5) === '.webp', 'el srcset entrega .webp en los tamaños de tarjeta');
webp_check(substr($out[768]['url'], -4) === '.png', 'el srcset no toca el tamaño que no es de tarjeta');
webp_check(flacso_child_card_srcset_webp(false, [], '', [], 1) === false, 'el filtro respeta un srcset ausente');

// Filtro de la URL principal: solo para los tamaños del tema.
$img = ['https://sitio.test/wp-content/uploads/2026/05/afiche-480x600.png', 480, 600, true];
webp_check(substr(flacso_child_card_image_src_webp($img, 1, 'flacso-card')[0], -5) === '.webp', 'flacso-card entrega .webp');
webp_check(substr(flacso_child_card_image_src_webp($img, 1, 'large')[0], -4) === '.png', 'otros tamaños nombrados no se tocan');
webp_check(flacso_child_card_image_src_webp(false, 1, 'flacso-card') === false, 'el filtro respeta una imagen ausente');
webp_check(flacso_child_card_image_src_webp($img, 1, [480, 600]) === $img, 'los tamaños por dimensiones no se tocan');

// Ganchos: generación al subir, limpieza al borrar, y NO el filtro global.
webp_check(!empty($GLOBALS['hooks']['wp_generate_attachment_metadata']), 'se genera el .webp al subir una imagen');
webp_check(!empty($GLOBALS['hooks']['delete_attachment']), 'se borran los .webp al eliminar el adjunto');
webp_check(!empty($GLOBALS['hooks']['wp_calculate_image_srcset']) && !empty($GLOBALS['hooks']['wp_get_attachment_image_src']), 'los filtros de srcset y de URL están enganchados');
webp_check(empty($GLOBALS['hooks']['image_editor_output_format']), 'NO se usa el filtro global image_editor_output_format');

// Generador: sin editor disponible no falla ni toca nada.
$GLOBALS['meta'][5] = ['file' => '2026/05/original.png', 'sizes' => ['flacso-card' => ['file' => 'original-480x600.png', 'width' => 480, 'height' => 600]]];
function wp_get_attachment_metadata($id) { return $GLOBALS['meta'][$id] ?? false; }
function wp_get_image_editor($path) { return new WP_Error('sin_editor'); }
function is_wp_error($v) { return $v instanceof WP_Error; }
class WP_Error { public function __construct($c = '') {} }
touch_file('2026/05/original-480x600.png');
webp_check(flacso_child_generate_webp_companions(5) === 0, 'sin editor de imágenes el generador devuelve 0 y no falla');
webp_check(flacso_child_generate_webp_companions(999) === 0, 'un adjunto sin metadatos devuelve 0');

// Limpieza al borrar.
touch_file('2026/05/original-480x600.webp');
flacso_child_delete_webp_companions(5);
webp_check(!file_exists($GLOBALS['uploads'] . '/2026/05/original-480x600.webp'), 'al borrar el adjunto se elimina su .webp');
webp_check(file_exists($GLOBALS['uploads'] . '/2026/05/original-480x600.png'), 'la limpieza no toca el PNG (lo borra WordPress)');

// Las plantillas ya cargan el módulo.
$functions = (string) file_get_contents($root . '/functions.php');
webp_check(strpos($functions, 'inc/webp-cards.php') !== false, 'functions.php carga inc/webp-cards.php');

echo "OK WebP acotado a las tarjetas\n";
