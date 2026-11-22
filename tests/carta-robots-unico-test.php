<?php
/**
 * La Carta debe emitir una sola directiva robots (noindex, nofollow), también con un plugin SEO activo.
 * Rank Math/Yoast imprimen su propia etiqueta; el tema debe cambiar la de ellos en vez de sumar otra.
 * Ejecutable sin WordPress: php tests/carta-robots-unico-test.php
 */
$root = dirname(__DIR__);
if (!defined('ABSPATH')) { define('ABSPATH', __DIR__ . '/'); }

function robots_check($ok, $description) {
    if (!$ok) { fwrite(STDERR, "FAIL: " . $description . "\n"); exit(1); }
}

$GLOBALS['hooks'] = [];
$GLOBALS['is_carta'] = true;
function add_action($tag, $cb, $priority = 10, $args = 1) { $GLOBALS['hooks'][$tag][] = $cb; return true; }
function add_filter($tag, $cb, $priority = 10, $args = 1) { return add_action($tag, $cb, $priority, $args); }
function has_action($tag, $cb = false) { return false; }
function apply_filters($tag, $value, ...$rest) {
    foreach ($GLOBALS['hooks'][$tag] ?? [] as $cb) { $value = call_user_func($cb, $value, ...$rest); }
    return $value;
}
function flacso_theme_is_carta() { return $GLOBALS['is_carta']; }

require $root . '/inc/open-graph.php';

// Con Rank Math activo: el tema no imprime su propia etiqueta; cambia la de Rank Math.
define('RANK_MATH_VERSION', '1.0');
ob_start();
flacso_theme_render_carta_robots_meta();
$printed = ob_get_clean();
robots_check($printed === '', 'con un plugin SEO, el tema no imprime una segunda etiqueta robots');

$robots = apply_filters('rank_math/frontend/robots', ['index' => 'index', 'follow' => 'follow', 'max-snippet' => '-1']);
robots_check(($robots['index'] ?? '') === 'noindex' && ($robots['follow'] ?? '') === 'nofollow', 'Rank Math recibe noindex, nofollow en la Carta');

$yoast = apply_filters('wpseo_robots_array', ['index' => 'index', 'follow' => 'follow']);
robots_check(($yoast['index'] ?? '') === 'noindex' && ($yoast['follow'] ?? '') === 'nofollow', 'Yoast recibe noindex, nofollow en la Carta');

$core = apply_filters('wp_robots', ['max-image-preview' => 'large']);
robots_check(!empty($core['noindex']) && !empty($core['nofollow']), 'el núcleo de WordPress recibe noindex, nofollow en la Carta');

// Fuera de la Carta nada cambia.
$GLOBALS['is_carta'] = false;
$normal = apply_filters('rank_math/frontend/robots', ['index' => 'index', 'follow' => 'follow']);
robots_check(($normal['index'] ?? '') === 'index', 'la ficha regular conserva su indexación');
$core_normal = apply_filters('wp_robots', ['max-image-preview' => 'large']);
robots_check(empty($core_normal['noindex']), 'la ficha regular no recibe noindex');

// La plantilla ya no registra su propia versión tardía.
$carta = (string) file_get_contents($root . '/template-parts/oferta/carta.php');
robots_check(strpos($carta, 'noindex') === false, 'carta.php no repite la lógica de robots (ya se emite en open-graph.php)');

echo "OK una sola directiva robots en la Carta\n";
