<?php
// En la portada se retiran los estilos antiguos del plugin; en el resto del sitio no se toca nada.
$root = dirname(__DIR__);
if (!defined('ABSPATH')) { define('ABSPATH', __DIR__); }
function check($condition, $message) { if (!$condition) { fwrite(STDERR, "FAIL: $message\n"); exit(1); } }
$GLOBALS['is_front'] = true; $GLOBALS['hooks'] = []; $GLOBALS['dequeued'] = []; $GLOBALS['deregistered'] = [];
function add_action($hook, $cb, $priority = 10) { $GLOBALS['hooks'][] = [$hook, $cb, $priority]; }
function is_front_page() { return $GLOBALS['is_front']; }
function wp_dequeue_style($h) { $GLOBALS['dequeued'][] = $h; }
function wp_deregister_style($h) { $GLOBALS['deregistered'][] = $h; }
function __return_true() { return true; }
function add_filter() {}
function get_stylesheet_directory() { return dirname(__DIR__); }
function get_stylesheet_directory_uri() { return ''; }
function wp_register_style() {} function wp_enqueue_style() {} function wp_register_script() {} function wp_enqueue_script() {}
require $root . '/inc/assets.php';

$priority = null;
foreach ($GLOBALS['hooks'] as [$hook, $cb, $p]) { if ($cb === 'flacso_child_dequeue_front_page_legacy_assets') { $priority = $p; } }
check($priority !== null && $priority > 65, 'el retiro corre después de los encolados del plugin (60 y 65)');
flacso_child_dequeue_front_page_legacy_assets();
foreach (['flacso-main-page-base', 'flacso-main-page-mobile-fixes', 'flacso-main-page-fonts', 'flacso-mobile-first', 'flacso-main-page-novedades-45'] as $h) {
    check(in_array($h, $GLOBALS['dequeued'], true) && in_array($h, $GLOBALS['deregistered'], true), "se retira $h en la portada");
}
check(!in_array('bootstrap-css', $GLOBALS['dequeued'], true) && !in_array('bootstrap-icons', $GLOBALS['dequeued'], true), 'Bootstrap del tema se conserva');
$GLOBALS['dequeued'] = []; $GLOBALS['is_front'] = false;
flacso_child_dequeue_front_page_legacy_assets();
check($GLOBALS['dequeued'] === [], 'fuera de la portada no se retira nada');
echo "Portada: estilos antiguos retirados solo en la portada.\n";
