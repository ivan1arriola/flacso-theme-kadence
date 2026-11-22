<?php
/**
 * Los listados no deben servir el archivo original de una imagen destacada (3 a 5 MB cada una) cuando falta el
 * tamaño reducido: las imágenes subidas antes de registrar `flacso-oferta-4x5` y `flacso-seminario-4x5` no lo tienen y
 * WordPress devuelve `full`. El tema elige el mayor tamaño reducido que exista y registra `flacso-card` (480x600).
 * Ejecutable sin WordPress: php tests/card-image-fallback-test.php
 */
$root = dirname(__DIR__);
if (!defined('ABSPATH')) { define('ABSPATH', __DIR__ . '/'); }

function card_img_check($ok, $description) {
    if (!$ok) { fwrite(STDERR, "FAIL: " . $description . "\n"); exit(1); }
}

$GLOBALS['registered'] = [];
$GLOBALS['hooks'] = [];
$GLOBALS['meta'] = [];
function add_action($tag, $cb, $priority = 10, $args = 1) { $GLOBALS['hooks'][$tag][] = [$cb, $priority, $args]; return true; }
function add_filter($tag, $cb, $priority = 10, $args = 1) { return add_action($tag, $cb, $priority, $args); }
function add_image_size($name, $w = 0, $h = 0, $crop = false) { $GLOBALS['registered'][$name] = [$w, $h, $crop]; }
function is_home() { return false; } function is_archive() { return false; } function is_singular($t = '') { return false; } function is_front_page() { return false; }
function wp_get_attachment_metadata($id) { return $GLOBALS['meta'][$id] ?? false; }
// Simula image_downsize() de WordPress para un tamaño que existe en los metadatos.
function image_downsize($id, $size) {
    $m = $GLOBALS['meta'][$id] ?? [];
    if (is_string($size) && isset($m['sizes'][$size])) { return ['http://sitio/' . $m['sizes'][$size]['file'], $m['sizes'][$size]['width'], $m['sizes'][$size]['height'], true]; }
    return ['http://sitio/' . ($m['file'] ?? 'original.png'), $m['width'] ?? 0, $m['height'] ?? 0, false];
}

function esc_url($u) { return $u; }
function esc_attr($v) { return htmlspecialchars((string) $v, ENT_QUOTES); }
function attachment_url_to_postid($url) { return $GLOBALS['url_to_id'][$url] ?? 0; }
function wp_get_attachment_image($id, $size, $icon, $attr) {
    if (!isset($GLOBALS['meta'][$id])) { return ''; }
    $GLOBALS['last_img_call'] = [$id, $size, $attr];
    $d = flacso_child_image_downsize_fallback(false, $id, $size); // WordPress aplica el filtro image_downsize
    if ($d === false) { $d = image_downsize($id, $size); }
    $a = ''; foreach ($attr as $k => $v) { $a .= ' ' . $k . '="' . esc_attr($v) . '"'; }
    return '<img src="' . $d[0] . '" srcset="' . $d[0] . ' 480w"' . $a . '>';
}
require $root . '/inc/media.php';
foreach ($GLOBALS['hooks']['after_setup_theme'] ?? [] as $hook) { call_user_func($hook[0]); } // registra los tamaños

card_img_check(isset($GLOBALS['registered']['flacso-card']) && $GLOBALS['registered']['flacso-card'] === [480, 600, true], 'se registra flacso-card de 480x600 con recorte 4:5');
card_img_check(function_exists('flacso_child_fallback_image_size'), 'existe flacso_child_fallback_image_size()');
card_img_check(function_exists('flacso_child_image_downsize_fallback'), 'existe el filtro flacso_child_image_downsize_fallback()');
card_img_check(!empty($GLOBALS['hooks']['image_downsize']), 'el filtro está enganchado a image_downsize');

$size = function (array $names) { $s = []; foreach ($names as $n) { $s[$n] = ['file' => "img-{$n}.webp", 'width' => 100, 'height' => 100]; } return ['file' => 'original.png', 'sizes' => $s]; };

// El tamaño pedido existe: no se toca.
card_img_check(flacso_child_fallback_image_size($size(['flacso-card', 'large']), 'flacso-card') === 'flacso-card', 'si existe el tamaño pedido se usa');
// Falta flacso-card: se usa el 4:5 grande antes que cualquier otro.
card_img_check(flacso_child_fallback_image_size($size(['flacso-oferta-4x5', 'large', 'medium_large']), 'flacso-card') === 'flacso-oferta-4x5', 'sin flacso-card se usa flacso-oferta-4x5');
// Imágenes anteriores a los tamaños del tema: solo hay los de WordPress.
card_img_check(flacso_child_fallback_image_size($size(['thumbnail', 'medium', 'medium_large', 'large', '1536x1536']), 'flacso-oferta-4x5') === 'medium_large', 'imagen vieja: se usa medium_large (nunca el original)');
card_img_check(flacso_child_fallback_image_size($size(['thumbnail', 'medium']), 'flacso-seminario-4x5') === 'medium', 'solo hay medium: se usa medium');
// Sin ningún tamaño reducido: no hay nada mejor que decidir.
card_img_check(flacso_child_fallback_image_size(['file' => 'original.png', 'sizes' => []], 'flacso-card') === '', 'sin tamaños reducidos devuelve vacío');
card_img_check(flacso_child_fallback_image_size([], 'flacso-card') === '', 'sin metadatos devuelve vacío');
// Tamaños ajenos al tema: no se interviene.
card_img_check(flacso_child_fallback_image_size($size(['thumbnail']), 'full') === '', 'full no se reemplaza (lo pidió una plantilla a propósito)');

// Filtro: devuelve el reducido en lugar del original.
$GLOBALS['meta'][10] = $size(['medium_large', 'large']);
$out = flacso_child_image_downsize_fallback(false, 10, 'flacso-oferta-4x5');
card_img_check(is_array($out) && strpos($out[0], 'medium_large') !== false, 'imagen vieja: el filtro entrega medium_large');
$GLOBALS['meta'][11] = $size(['flacso-oferta-4x5']);
card_img_check(flacso_child_image_downsize_fallback(false, 11, 'flacso-oferta-4x5') === false, 'si el tamaño existe el filtro no interviene');
card_img_check(flacso_child_image_downsize_fallback(false, 10, 'full') === false, 'el filtro no interviene en full');
card_img_check(flacso_child_image_downsize_fallback(false, 10, [300, 300]) === false, 'el filtro no interviene en tamaños por dimensiones');
card_img_check(flacso_child_image_downsize_fallback(false, 99, 'flacso-card') === false, 'sin metadatos el filtro no interviene');
card_img_check(flacso_child_image_downsize_fallback(['x'], 10, 'flacso-card') === ['x'], 'respeta lo que ya resolvió otro filtro');

// Helper de plantilla: <img> con srcset, sizes y carga diferida, resolviendo el adjunto.
card_img_check(function_exists('flacso_child_card_image_html'), 'existe flacso_child_card_image_html()');
$GLOBALS['meta'][20] = $size(['medium_large', 'large']);
$html = flacso_child_card_image_html(20, 'http://sitio/original-grande.png', 'Maestría', '(max-width: 767px) 92vw, 380px', ['onerror' => "this.style.display='none'"]);
card_img_check(strpos($html, 'medium_large') !== false && strpos($html, 'original-grande.png') === false, 'con adjunto, la etiqueta apunta al tamaño reducido y no al original');
card_img_check(strpos($html, 'srcset=') !== false && strpos($html, 'sizes="(max-width: 767px) 92vw, 380px"') !== false, 'incluye srcset y sizes');
card_img_check(strpos($html, 'loading="lazy"') !== false && strpos($html, 'decoding="async"') !== false, 'carga diferida y decodificación asíncrona');
card_img_check(strpos($html, 'alt="Maestría"') !== false && strpos($html, 'onerror=') !== false, 'conserva el alt y los atributos extra (onerror del marcador)');
card_img_check($GLOBALS['last_img_call'][1] === 'flacso-card', 'pide el tamaño flacso-card');
// Sin id de adjunto pero con la URL del adjunto: se resuelve por URL.
$GLOBALS['url_to_id']['http://sitio/otra.png'] = 20;
card_img_check(strpos(flacso_child_card_image_html(0, 'http://sitio/otra.png', 'X', ''), 'medium_large') !== false, 'sin id, resuelve el adjunto por la URL');
// Sin adjunto (imagen externa): cae a <img src> con carga diferida.
$ext = flacso_child_card_image_html(0, 'https://cdn.ejemplo/foto.jpg', 'Externa', '');
card_img_check(strpos($ext, 'src="https://cdn.ejemplo/foto.jpg"') !== false && strpos($ext, 'loading="lazy"') !== false, 'imagen externa: <img src> con carga diferida');
card_img_check(flacso_child_card_image_html(0, '', 'Nada', '') === '', 'sin imagen devuelve cadena vacía');

// Plantillas de listado: usan el helper y no imprimen la URL original en un <img src>.
$formacion = (string) file_get_contents($root . '/formacion-page.php');
card_img_check(substr_count($formacion, 'flacso_child_card_image_html(') === 2, 'formacion-page.php usa el helper en las dos tarjetas (ofertas y seminarios)');
card_img_check(strpos($formacion, '<img src="<?php echo esc_url($item[\'thumbnail_url\'])') === false && strpos($formacion, '<img src="<?php echo esc_url($sem[\'thumbnail_url\'])') === false, 'formacion-page.php no imprime thumbnail_url en un <img src>');
// El atributo sizes debe reflejar el ancho real de la tarjeta (medido: 154-159px en 412px, 346px en 768px, 307px en 1024px,
// 392px en 1280px). Con 92vw un teléfono retina elegía la variante de 800px (1,2 MB) en vez de la de 480px.
card_img_check(strpos($formacion, '92vw') === false, 'formacion-page.php no declara sizes de 92vw: la tarjeta ocupa un 40 % del ancho en móvil');
card_img_check(substr_count($formacion, '(max-width: 600px) 40vw, (max-width: 900px) 46vw, (max-width: 1100px) 31vw, 400px') === 2, 'las dos tarjetas declaran los sizes medidos');
$taxonomia = (string) file_get_contents($root . '/taxonomy-tipo-oferta-academica.php');
card_img_check(strpos($taxonomia, "'flacso-card'") !== false && strpos($taxonomia, "'large'") === false, 'el listado por tipo pide flacso-card y no large');

echo "OK tamaños de imagen de tarjeta sin servir el original\n";
