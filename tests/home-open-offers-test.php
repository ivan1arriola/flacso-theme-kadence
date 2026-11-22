<?php
/**
 * Portada pensada para móvil: sección de inscripciones abiertas (datos desde el modelo académico, carrusel táctil).
 * Ejecutable sin WordPress: php tests/home-open-offers-test.php
 */
$root = dirname(__DIR__);
function open_check($ok, $d) { if (!$ok) { fwrite(STDERR, "FAIL: " . $d . "\n"); exit(1); } }

$home = (string) file_get_contents($root . '/inc/home.php');
open_check(strpos($home, 'function flacso_home_open_offers') !== false, 'existe flacso_home_open_offers()');
open_check(preg_match("/\['hero', 'offer', 'open', 'campaign'/", $home) === 1, 'la sección "open" se renderiza justo después de la oferta');
open_check(strpos($home, "'open_offers' => flacso_home_open_offers") !== false, 'los datos de la portada incluyen open_offers');
open_check(strpos($home, 'function_exists(\'flacso_theme_get_offer_context\')') !== false, 'sin el modelo académico la sección se omite sin errores');

$part = (string) file_get_contents($root . '/template-parts/home/open.php');
open_check(strpos($part, 'if (!$open) { return; }') !== false, 'sin propuestas abiertas no se imprime la sección');
open_check(strpos($part, 'aria-labelledby="openTitle"') !== false && strpos($part, '<h2 id="openTitle">') !== false, 'sección con título accesible');
open_check(strpos($part, '<ul class="open-list">') !== false && strpos($part, 'flacso_child_card_image_html') !== false, 'lista de tarjetas con imagen 480x600 optimizada');

$css = (string) file_get_contents($root . '/assets/css/front-page.css');
open_check(strpos($css, 'scroll-snap-type:x mandatory') !== false, 'en móvil las tarjetas se deslizan con imán horizontal');
open_check(strpos($css, '.flacso-home .hero .hero-cta{display:flex;width:100%}') !== false, 'el botón principal ocupa todo el ancho en móvil');
open_check(strpos($css, '.flacso-home .offer-grid>a{min-height:64px}') !== false, 'los accesos por tipo miden al menos 64 px en móvil');
open_check(preg_match('/font-size:\s*(9|10|11)px/', $css) === 0, 'la portada no usa textos de menos de 12 px');
echo "OK portada móvil: inscripciones abiertas\n";
