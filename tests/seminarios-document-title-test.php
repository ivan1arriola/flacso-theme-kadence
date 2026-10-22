<?php
/**
 * Regresión: el archivo de seminarios no muestra "Seminarios Archive".
 */
declare(strict_types=1);

define('ABSPATH', __DIR__ . '/');
$GLOBALS['flacso_title_test_filters'] = [];
$GLOBALS['flacso_title_test_archive'] = false;

function add_filter($hook, $callback, $priority = 10) {
    $GLOBALS['flacso_title_test_filters'][$hook] = $callback;
}
function is_post_type_archive($post_type) {
    return $post_type === 'seminario' && $GLOBALS['flacso_title_test_archive'];
}
function get_bloginfo($key) {
    return $key === 'name' ? 'FLACSO Uruguay' : '';
}

require dirname(__DIR__) . '/inc/theme-setup.php';

$expected_hooks = ['pre_get_document_title', 'rank_math/frontend/title', 'wpseo_title', 'aioseo_title'];
foreach ($expected_hooks as $hook) {
    if (($GLOBALS['flacso_title_test_filters'][$hook] ?? null) !== 'flacso_theme_seminars_archive_document_title') {
        fwrite(STDERR, "FAIL: falta el filtro {$hook}\n");
        exit(1);
    }
}

$GLOBALS['flacso_title_test_archive'] = true;
foreach ($expected_hooks as $hook) {
    $result = call_user_func($GLOBALS['flacso_title_test_filters'][$hook], 'Seminarios Archive - FLACSO Uruguay');
    if ($result !== 'Seminarios | FLACSO Uruguay') {
        fwrite(STDERR, "FAIL: título de seminarios incorrecto en {$hook}\n");
        exit(1);
    }
}

$GLOBALS['flacso_title_test_archive'] = false;
if (flacso_theme_seminars_archive_document_title('Otro título') !== 'Otro título') {
    fwrite(STDERR, "FAIL: el título de otras páginas fue alterado\n");
    exit(1);
}

echo "OK seminarios-document-title-test\n";
