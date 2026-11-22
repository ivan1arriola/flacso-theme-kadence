<?php
/**
 * Un documento debe tener un solo landmark <main>. En modo nativo, el tema padre ya abre
 * <main id="inner-wrap">, así que las plantillas del hijo deben abrir un <div> en su lugar.
 * Todas pasan por flacso_child_main_tag(); ninguna escribe <main> a mano.
 * Ejecutable sin WordPress: php tests/single-main-landmark-contract-test.php
 */
$root = dirname(__DIR__);
if (!defined('ABSPATH')) { define('ABSPATH', __DIR__ . '/'); }

function main_check($ok, $description) {
    if (!$ok) { fwrite(STDERR, "FAIL: " . $description . "\n"); exit(1); }
}

$GLOBALS['mods'] = [];
function get_theme_mod($key, $default = false) { return $GLOBALS['mods'][$key] ?? $default; }
function add_action() {}
function add_filter() {}
function __($t) { return $t; }
function esc_html__($t) { return $t; }
function esc_html_e($t) { echo $t; }
function sanitize_key($k) { return $k; }
require $root . '/inc/kadence-compat.php';

main_check(function_exists('flacso_child_main_tag'), 'existe flacso_child_main_tag()');
$GLOBALS['mods']['flacso_use_kadence_chrome'] = false;
main_check(flacso_child_main_tag() === 'main', 'con la cabecera propia el contenedor es <main>');
$GLOBALS['mods']['flacso_use_kadence_chrome'] = true;
main_check(flacso_child_main_tag() === 'div', 'con Kadence nativo el contenedor es <div> (el padre ya abre <main>)');

// Ninguna plantilla ni parcial escribe <main> / </main> a mano.
$offenders = [];
$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS));
foreach ($iterator as $file) {
    $path = str_replace(DIRECTORY_SEPARATOR, '/', $file->getPathname());
    if ($file->getExtension() !== 'php' || preg_match('#/(tests|vendor|\.worktrees|node_modules|\.git)/#', $path)) { continue; }
    $code = (string) file_get_contents($path);
    if (preg_match('#<main[\s>]|</main>#', $code) && substr($path, -strlen('inc/kadence-compat.php')) !== 'inc/kadence-compat.php') {
        $offenders[] = str_replace($root . '/', '', $path);
    }
}
main_check($offenders === [], '<main> escrito a mano en: ' . implode(', ', $offenders));

echo "OK un solo landmark main\n";
