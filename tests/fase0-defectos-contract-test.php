<?php
/**
 * Fase 0 (defectos reproducidos en producción el 2026-11-23):
 *  1) /equipo/ renderizaba 159 perfiles de una vez (84.828 px en 360 px): ahora se muestran por tandas con "Mostrar más".
 *  2) El evento tenía dos <h1> (el del tema y uno repetido en el contenido): el contenido degrada su h1 a h2.
 *  3) En fichas con barra fija inferior en móvil, la barra tapaba el final del pie: el espacio se reserva en <body>.
 * Ejecutable sin WordPress: php tests/fase0-defectos-contract-test.php
 */
$root = dirname(__DIR__);
function f0_check($ok, $d) { if (!$ok) { fwrite(STDERR, "FAIL: " . $d . "\n"); exit(1); } }

// 1) Equipo por tandas.
$js = str_replace("\r\n", "\n", (string) file_get_contents($root . '/assets/js/archive-docente.js'));
f0_check(strpos($js, 'BATCH') !== false && preg_match('/BATCH\s*=\s*24/', $js) === 1, 'el directorio muestra 24 perfiles por tanda');
f0_check(strpos($js, 'archive-more') !== false && strpos($js, 'Mostrar más') !== false, 'hay botón "Mostrar más"');
f0_check(strpos($js, 'normalize(\'NFD\')') !== false, 'la búsqueda ignora tildes');
f0_check(strpos($js, 'aria-live') !== false || strpos(file_get_contents($root . '/archive-docente.php'), 'aria-live') !== false, 'el contador se anuncia a lectores de pantalla');
f0_check(strpos($js, 'empty') !== false, 'hay estado vacío cuando la búsqueda no encuentra perfiles');

// 2) Un solo h1.
$compat = (string) file_get_contents($root . '/inc/kadence-compat.php');
f0_check(strpos($compat, 'flacso_child_demote_content_h1') !== false, 'existe el filtro que degrada h1 del contenido');
f0_check(strpos($compat, "add_filter( 'the_content', 'flacso_child_demote_content_h1'") !== false, 'el filtro se registra en the_content');
if (!defined('ABSPATH')) { define('ABSPATH', __DIR__); }
function add_filter() {} function add_action() {} function is_singular($t = '') { return true; } function in_the_loop() { return true; } function is_main_query() { return true; }
require_once $root . '/inc/kadence-compat.php';
f0_check(function_exists('flacso_child_demote_content_h1'), 'la función se carga sin WordPress');
$out = flacso_child_demote_content_h1('<h1 class="wp-block-heading">Título</h1><p>x</p><H1>Otro</H1>');
f0_check($out === '<h2 class="wp-block-heading">Título</h2><p>x</p><h2>Otro</h2>', 'h1 -> h2 conservando atributos: ' . $out);
f0_check(flacso_child_demote_content_h1('<h2>A</h2><h3>B</h3>') === '<h2>A</h2><h3>B</h3>', 'no toca otros niveles');

// 3) Espacio para la barra fija.
$css = str_replace("\r\n", "\n", (string) file_get_contents($root . '/oferta-academica/style.css'));
f0_check(preg_match('/body:has\(\.flacso-mobile-sheet-ready\)\s*\{[^}]*padding-bottom:\s*calc\(/', $css) === 1, 'el espacio de la barra fija se reserva en body (el pie no queda tapado)');
$post_css = str_replace("
", "
", (string) file_get_contents($root . '/assets/css/single-post.css'));
f0_check(preg_match('/body:has\(\.flacso-evento-signup:not\(\[hidden\]\)\)\s*\{[^}]*padding-bottom:\s*calc\(/', $post_css) === 1, 'la barra fija del evento reserva su espacio en body (el pie no queda tapado)');
f0_check(strpos((string) file_get_contents($root . '/oferta-academica/page.php'), 'wp_get_attachment_image($hero_thumb_id, \'large\'') !== false, 'la imagen de la ficha de oferta usa tamaño large con srcset, no la placa original');
echo "OK fase 0: equipo por tandas, un solo h1 y pie visible con barra fija\n";
