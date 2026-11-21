<?php
/**
 * Contrato de estructura y alineación horizontal de la portada.
 * Ejecutable sin WordPress: php tests/front-page-layout-contract-test.php
 */
$root = dirname(__DIR__);
if (!defined('ABSPATH')) { define('ABSPATH', __DIR__); }

function layout_check($ok, $description) {
    if (!$ok) { fwrite(STDERR, "FAIL: " . $description . "\n"); exit(1); }
}

$GLOBALS['render_order'] = [];
function get_header() { $GLOBALS['render_order'][] = 'header'; }
function flacso_home_render() {
    $GLOBALS['render_order'][] = 'home';
    return '<main id="main" class="flacso-home"></main>';
}
function get_footer() { $GLOBALS['render_order'][] = 'footer'; }

ob_start();
require $root . '/front-page.php';
$html = ob_get_clean();
layout_check($GLOBALS['render_order'] === ['header', 'home', 'footer'], 'la portada utiliza header, main y footer una sola vez');
layout_check(trim($html) === '<main id="main" class="flacso-home"></main>', 'la portada no genera cierres de contenedores sueltos');

$header = file_get_contents($root . '/header.php');
$footer = file_get_contents($root . '/footer.php');
$front = file_get_contents($root . '/front-page.php');
layout_check(strpos($header, 'id="inner-wrap"') !== false, 'header abre #inner-wrap');
layout_check(strpos($footer, '</div><!-- #inner-wrap -->') !== false, 'footer cierra #inner-wrap');
layout_check(strpos($header, "kadence_before_content") !== false, 'header ejecuta hook before_content');
layout_check(strpos($footer, "kadence_after_content") !== false, 'footer ejecuta hook after_content');
layout_check(strpos($front, "kadence_before_content") === false && strpos($front, "kadence_after_content") === false, 'front-page no duplica hooks');

$chrome = file_get_contents($root . '/assets/css/site-chrome.css');
$home = file_get_contents($root . '/assets/css/front-page.css');
$mobile = file_get_contents($root . '/assets/css/mobile-ux.css');
layout_check(strpos($chrome, '--flacso-chrome-width: 1180px;') !== false, 'ancho máximo compartido de 1180px');
layout_check(strpos($chrome, '--flacso-content-inset: 40px;') !== false, 'margen compartido escritorio');
layout_check(strpos($chrome, '--flacso-content-inset: 24px;') !== false, 'margen compartido tablet');
layout_check(strpos($chrome, '--flacso-content-inset: 20px;') !== false, 'margen compartido móvil');
$sharedWidth = 'width: min(var(--flacso-chrome-width), calc(100% - var(--flacso-content-inset)))';
$homeWidth = 'width:min(var(--flacso-chrome-width),calc(100% - var(--flacso-content-inset)))';
layout_check(strpos($chrome, $sharedWidth) !== false, 'header y footer consumen el ancho compartido');
layout_check(strpos($home, $homeWidth) !== false && strpos($home, 'max-width:none') !== false, 'contenedor de portada consume el mismo ancho sin límite antiguo');
layout_check(substr_count($home, '.flacso-home .container{') === 1, 'no hay excepciones locales para el ancho de portada');
layout_check(strpos($mobile, '.flacso-chrome-container {') === false, 'mobile-ux no redefine el ancho del encabezado');
layout_check(strpos($chrome, '.flacso-footer .flacso-chrome-container {') === false, 'footer no redefine el ancho institucional');
layout_check(substr_count($chrome, '.flacso-chrome-container {') === 1, 'contenedor global definido una sola vez');

echo "Portada: estructura HTML y anchos unificados correctos.\n";
