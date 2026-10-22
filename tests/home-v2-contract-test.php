<?php
$root = dirname(__DIR__);
if (!defined('ABSPATH')) { define('ABSPATH', __DIR__); }
function check($condition, $message) { if (!$condition) { fwrite(STDERR, "FAIL: $message\n"); exit(1); } }
function absint($value) { return abs((int) $value); }
function wp_strip_all_tags($value) { return strip_tags((string) $value); }
function remove_accents($value) { return strtr($value, ['í'=>'i','ó'=>'o','á'=>'a','é'=>'e','ú'=>'u']); }
function home_url($path='') { return 'https://example.test' . $path; }
function get_stylesheet_directory() { return dirname(__DIR__); }
function esc_html($value) { return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8'); }
function esc_attr($value) { return esc_html($value); }
function esc_url($value) { return esc_html($value); }
function wp_kses_post($value) { return strip_tags((string) $value, '<p><strong><em><a>'); }
function wp_trim_words($value, $count) { return implode(' ', array_slice(explode(' ', $value), 0, $count)); }
function get_post_field($field, $id) { return 'Contenido de prueba'; }
function get_the_title($id) { return 'Noticia ' . $id; }
function get_the_excerpt($id) { return 'Resumen de la noticia'; }
function has_excerpt($id) { return true; }
function get_the_date($format, $id) { return $format === 'c' ? '2026-10-01T00:00:00-03:00' : '1 de octubre de 2026'; }
function get_permalink($id) { return 'https://example.test/noticia-' . $id; }
function get_post_thumbnail_id($id) { return $id === 101 ? 55 : 0; }
function wp_get_attachment_image($id, $size, $icon=false, $attrs=[]) { return '<img src="https://example.test/image.jpg" alt="">'; }
function wp_get_attachment_image_url($id, $size) { return 'https://example.test/hero.jpg'; }
function flacso_section_oferta_educativa_get_cards() { return $GLOBALS['cards'] ?? [
    ['title'=>'Seminarios','url'=>'/formacion/seminarios/'], ['title'=>'Maestrías','url'=>'/formacion/maestrias/'],
    ['title'=>'Diplomas','url'=>'/formacion/diplomas/'], ['title'=>'Diplomados','url'=>'/formacion/diplomados/'],
    ['title'=>'Especializaciones','url'=>'/formacion/especializaciones/'],
]; }
function flacso_section_eventos_get_items($count) { return $GLOBALS['events'] ?? []; }
function flacso_section_novedades_get_sticky_posts($limit) { return $GLOBALS['pinned'] ?? [(object)['ID'=>101],(object)['ID'=>102]]; }
function flacso_section_novedades_get_posts($limit,$category,$page) { return ['posts'=>[(object)['ID'=>101],(object)['ID'=>201],(object)['ID'=>202],(object)['ID'=>203],(object)['ID'=>204]]]; }
class FLACSO_Home_Campaign { public static function view_model() { return ['campaign'=>$GLOBALS['campaign'] ?? ['source'=>'fallback']]; } }
class Flacso_Main_Page_Settings { public static function get_section($key) { return ['content'=>'<p>Texto institucional editable.</p>']; } }
require $root . '/inc/home-v2.php';

$html = flacso_home_v2_render();
$headings = ['Formación que','Encontrá tu próxima formación','Próximas actividades','Noticias','¿Qué es FLACSO Uruguay?','+20 países'];
$positions = array_map(static fn($text) => strpos($html, $text), $headings);
check(!in_array(false, $positions, true) && $positions === array_values(array_filter($positions, static fn($pos) => true)), 'están todas las secciones');
$sorted = $positions; sort($sorted);
check($positions === $sorted, 'orden de secciones hero, oferta, actividades, noticias, institución e impacto');
check(substr_count($html, '<h1') === 1, 'un solo H1');
check(strpos($html, 'No hay actividades próximas para mostrar.') !== false, 'actividades visibles vacías');
check(strpos($html, 'agenda.flacso.edu.uy') === false, 'actividades no enlazan reservas');
check(substr_count($html, 'home-offer-card') === 5, 'cinco categorías de oferta');
check(substr_count($html, 'home-news-card--featured') === 2, 'dos noticias destacadas');
check(substr_count($html, 'home-news-card--recent') === 4, 'cuatro noticias recientes');
preg_match('/<div class="home-recent-grid">(.*?)<\/div><\/div><\/section>/s', $html, $recent_markup);
check(strpos($recent_markup[1] ?? '', 'noticia-101') === false, 'destacada no se duplica en recientes');
check(strpos($html, 'Texto institucional editable.') !== false, 'contenido institucional del plugin');
check(strpos($html, 'Conocer las novedades') === false, 'sin CTA redundante');
check(strpos($html, 'cropped-cropped-Logos-FLACSO-Claro.png') !== false && strpos($html, 'admin-ajax.jpeg') === false, 'hero usa el logo oficial');
check(strpos($html, 'Formación de posgrado, investigación rigurosa') === false, 'sin bajada retirada');

$GLOBALS['events'] = [['id'=>301,'link'=>'https://example.test/evento-301','title'=>'Actividad abierta','day'=>'18','month'=>'Octubre']];
$with_event = flacso_home_v2_render();
check(strpos($with_event, 'Actividad abierta') !== false && strpos($with_event, 'evento-301') !== false, 'actividad dinámica enlaza detalle');
check(strpos($with_event, 'No hay actividades próximas para mostrar.') === false, 'sin estado vacío si hay actividad');
check(strpos($with_event, 'home-event-date-art') !== false, 'actividad sin imagen tiene respaldo');

$GLOBALS['pinned'] = [(object)['ID'=>101],(object)['ID'=>102],(object)['ID'=>103]];
$with_extra_pinned = flacso_home_v2_render();
check(strpos($with_extra_pinned, 'Noticia 103') !== false, 'fijadas adicionales permanecen visibles');
unset($GLOBALS['pinned']);
$GLOBALS['campaign'] = ['source'=>'campaign','title'=>'Convocatoria de prueba','kicker'=>'Inscripciones','cta_primary'=>['label'=>'Postular','url'=>'https://example.test/postular']];
$with_campaign = flacso_home_v2_render();
check(strpos($with_campaign, 'Convocatoria de prueba') !== false && strpos($with_campaign, 'Postular') !== false && strpos($with_campaign, 'https://example.test/postular') !== false, 'campaña editable preserva título y acción');
unset($GLOBALS['campaign']);
$GLOBALS['cards'] = [['title'=>'Maestrías','url'=>'#'],['title'=>'Seminarios','url'=>'/formacion/seminarios/']];
$with_invalid_card = flacso_home_v2_render();
check(substr_count($with_invalid_card, 'home-offer-card') === 5, 'las categorías sin enlace usan su destino predeterminado');
check(strpos($with_invalid_card, 'https://example.test/formacion/maestrias/') !== false, 'maestrías conserva su ruta predeterminada');
unset($GLOBALS['cards']);

$front = file_get_contents($root . '/front-page.php');
$css = file_get_contents($root . '/assets/css/home-v2.css');
check(strpos($front, 'FLACSO_Home_Campaign::is_enabled()') !== false, 'conserva bandera de reversión');
check(strpos($front, "return;") !== false, 'CSS antiguo no se carga en portada nueva');
check(strpos($css, 'aspect-ratio:4/5') !== false, 'imágenes de actividades y noticias 4:5');
check(strpos($css, 'object-position:center center !important') !== false, 'hero logo centrado verticalmente');
check(strpos($css, '@media(max-width:760px)') !== false, 'diseño móvil');
check(strpos($css, 'grid-template-columns: minmax(0, 52fr) minmax(0, 48fr)') !== false, 'hero editorial mantiene distribución en escritorio');
check(strpos($css, '.home-hero > .home-hero-layout.home-container') !== false, 'hero aplica ancho Kadence sin alterar otros contenedores');
check(strpos($css, 'clamp(18px, 1.8vw, 28px)') !== false, 'hero reserva margen izquierdo para contenido');
check(strpos($css, 'width: clamp(120px, 12vw, 200px)') !== false, 'gradiente horizontal en fotografía');
check(strpos($css, 'background: linear-gradient(180deg, transparent 0%') !== false, 'gradiente móvil vertical');
check(strpos($css, 'clip-path: polygon(10%') === false, 'fotografía no usa corte diagonal');
check(strpos($css, 'clip-path: none') !== false, 'móvil elimina el corte diagonal');
check(strpos($css, 'prefers-reduced-motion: reduce') !== false, 'hero respeta reducción de movimiento');
$hero_template = file_get_contents($root . '/template-parts/home/hero.php');
check(strpos($hero_template, '<picture>') !== false && strpos($hero_template, 'hero_mobile_image') !== false, 'fotografía responsive editable desde campaña');
check(strpos($hero_template, 'home-hero-title') !== false && strpos($hero_template, 'home-button') !== false, 'título y CTA continúan siendo HTML accesible');
echo "Portada v2: contrato de contenido y estructura correcto.\n";
