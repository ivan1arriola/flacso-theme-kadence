<?php
$root = dirname(__DIR__);
if (!defined('ABSPATH')) { define('ABSPATH', __DIR__); }
function check($condition, $message) { if (!$condition) { fwrite(STDERR, "FAIL: $message\n"); exit(1); } }
if (!function_exists('__')) { function __($text, $domain = '') { return (string) $text; } }
if (!function_exists('esc_html__')) { function esc_html__($text, $domain = '') { return esc_html($text); } }
function absint($value) { return abs((int) $value); }
function wp_strip_all_tags($value) { return strip_tags((string) $value); }
function remove_accents($value) { return strtr($value, ['í'=>'i','ó'=>'o','á'=>'a','é'=>'e','ú'=>'u']); }
function home_url($path='') { return 'https://example.test' . $path; }
function esc_url_raw($value) { return (string) $value; }
function get_stylesheet_directory() { return dirname(__DIR__); }
function get_stylesheet_directory_uri() { return 'https://example.test/wp-content/themes/kadence-child-flacso'; }
function esc_html($value) { return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8'); }
function esc_attr($value) { return esc_html($value); }
function esc_url($value) { return esc_html($value); }
function wp_kses_post($value) { return (string) $value; }
function wpautop($value) { $parts = array_filter(array_map('trim', preg_split('/
\s*
/', (string) $value))); return implode("
", array_map(static fn($p) => strncmp($p, '<p>', 3) === 0 ? $p : '<p>' . $p . '</p>', $parts)); }
function wp_trim_words($value, $count) { return implode(' ', array_slice(explode(' ', $value), 0, $count)); }
function get_post_field($field, $id) { return 'Contenido de prueba'; }
function get_the_title($id) { return 'Noticia ' . $id; }
function get_the_excerpt($id) { return 'Resumen de la noticia'; }
function has_excerpt($id) { return true; }
function get_the_date($format, $id) { return $format === 'c' ? '2026-10-01T00:00:00-03:00' : '1 de octubre de 2026'; }
function get_permalink($id) { return 'https://example.test/noticia-' . $id; }
function get_post_thumbnail_id($id) { return $id === 101 || $id === ($GLOBALS['thumb_for'] ?? 0) ? 55 : 0; }
function wp_get_attachment_image($id, $size, $icon=false, $attrs=[]) { return '<img src="https://example.test/image.jpg" alt="">'; }
function wp_get_attachment_image_url($id, $size) { return 'https://example.test/hero-' . $id . '.jpg'; }
function wp_get_attachment_image_src($id, $size) {
    $widths = ['1536x1536' => [1536, 865], '2048x2048' => [2048, 1154], 'full' => [2560, 1442]];
    $dims = $widths[$size] ?? [1024, 577];
    $url = $size === 'full' ? 'https://example.test/hero-' . $id . '.jpg' : 'https://example.test/hero-' . $id . '-' . $size . '.jpg';
    return [$url, $dims[0], $dims[1], $size !== 'full'];
}
function flacso_section_oferta_educativa_get_cards() { return $GLOBALS['cards'] ?? [
    ['title'=>'Seminarios','url'=>'/formacion/seminarios/'], ['title'=>'Maestrías','url'=>'/formacion/maestrias/'],
    ['title'=>'Diplomas','url'=>'/formacion/diplomas/'], ['title'=>'Diplomados','url'=>'/formacion/diplomados/'],
    ['title'=>'Especializaciones','url'=>'/formacion/especializaciones/'],
]; }
function flacso_section_eventos_get_items($count) { return $GLOBALS['events'] ?? []; }
function flacso_section_novedades_get_sticky_posts($limit) { return $GLOBALS['pinned'] ?? []; }
function flacso_section_novedades_get_posts($limit,$category,$page) {
    $ids = $GLOBALS['recent'] ?? [101,201,202,203,204];
    return ['posts'=>array_map(static fn($id) => (object)['ID'=>$id], $ids)];
}
class FLACSO_Home_Campaign { public static function view_model() { return ['campaign'=>$GLOBALS['campaign'] ?? ['source'=>'fallback']]; } }
class Flacso_Main_Page_Settings {
    // Réplica del valor predeterminado real del plugin (sin etiquetas <p>), que get_section() fusiona con lo guardado.
    const DEFAULT_QUIENES = '<strong>FLACSO Uruguay</strong> es la sede uruguaya de la <strong>Facultad Latinoamericana de Ciencias Sociales</strong>, una de las principales redes académicas de América Latina y el Caribe. Contribuye al desarrollo de la región mediante formación, investigación crítica y transferencia de conocimiento.';
    const DEFAULT_HERO_IMAGE = 'https://example.test/plugin-default.jpg';
    public static function default_hero_image(): string { return self::DEFAULT_HERO_IMAGE; }
    public static function get_section($key) {
        if ($key === 'quienes') { return ['content'=>$GLOBALS['quienes'] ?? self::DEFAULT_QUIENES]; }
        return $GLOBALS['hero_settings'] ?? ['background_image'=>self::DEFAULT_HERO_IMAGE];
    }
}
class Flacso_Home_Content {
    public static function defaults(): array { return [
        'hero'=>['eyebrow'=>'PROGRAMA URUGUAY','title_lead'=>'Formación que conecta,','title_em'=>'conocimiento que transforma.','intro'=>'Formación, investigación y cooperación académica con perspectiva latinoamericana.','cta_label'=>'Explorar oferta académica','cta_url'=>'/formacion/'],
        'campaign'=>['enabled'=>true,'label'=>'INSCRIPCIONES 2027','title'=>'Tu próximo paso empieza acá.','text'=>'Explorá las propuestas con inscripciones abiertas.','cta_label'=>'Conocer la oferta','cta_url'=>'/formacion/'],
        'research'=>['title_lead'=>'Pensar la región.','title_em'=>'Producir conocimiento.','intro'=>'Conocé los proyectos, centros y publicaciones de FLACSO Uruguay.','link_label'=>'Explorar investigaciones','link_url'=>'/institucional/investigaciones/','links'=>[
            ['label'=>'Proyectos de investigación','url'=>'/institucional/investigaciones/'],
            ['label'=>'Archivo Histórico IIN–FLACSO','url'=>'https://www.archivoiinflacso.uy/'],
            ['label'=>'Libros y publicaciones','url'=>'/categoria/publicaciones/libros/'],
        ]],
    ]; }
    public static function get(): array { return $GLOBALS['content'] ?? self::defaults(); }
}
function flacso_child_main_tag() { return 'main'; }
require $root . '/inc/home.php';

$data = flacso_home_get_data();
check($data['hero']['title_lead'] === 'Formación que conecta,', 'título del hero desde el contenido editable');
check($data['hero']['title_em'] === 'conocimiento que transforma.', 'énfasis del hero');
check($data['hero']['eyebrow'] === 'PROGRAMA URUGUAY' && $data['hero']['cta']['url'] === '/formacion/', 'eyebrow y CTA por defecto');
check($data['hero']['image'] === 'https://example.test/wp-content/themes/kadence-child-flacso/assets/img/home-hero.jpg', 'sin imagen configurada usa la fotografía por defecto');
check($data['hero']['image_alt'] === 'Personas adultas participando en una instancia de intercambio académico', 'alt descriptivo de la fotografía por defecto');
check(count($data['cards']) === 5 && array_keys($data['cards']) === ['maestrias','especializaciones','diplomados','diplomas','seminarios'], 'cinco niveles en orden');
check(isset($data['cards']['maestrias']['title'], $data['cards']['maestrias']['url']) && !array_key_exists('open', $data['cards']['maestrias']), 'tarjeta con title y url, sin conteo de inscripciones');
check($data['campaign'] !== null && $data['campaign']['cta']['label'] === 'Conocer la oferta', 'campaña por defecto activa');
check($data['event'] === null, 'sin eventos, event es null');
check($data['research']['title_lead'] === 'Pensar la región.' && count($data['research']['links']) === 3, 'investigación desde el contenido editable');
check(strpos($data['institution']['content'], 'sede uruguaya') !== false, 'contenido institucional del plugin');
check(count($data['featured_news']) === 1 && count($data['recent_news']) === 4, 'sin fijadas: 1 destacada y 4 recientes');
check((int) $data['featured_news'][0]->ID === 101, 'la destacada es la primera reciente');

$GLOBALS['quienes'] = '';
$fallback = flacso_home_get_data();
check(strpos($fallback['institution']['content'], '<strong>FLACSO Uruguay</strong> es la sede uruguaya') !== false && strpos($fallback['institution']['content'], 'transformación social') !== false, 'contenido institucional vacío usa los párrafos aprobados');
unset($GLOBALS['quienes']);

$GLOBALS['content'] = Flacso_Home_Content::defaults();
$GLOBALS['content']['campaign']['enabled'] = false;
check(flacso_home_get_data()['campaign'] === null, 'campaña apagada es null');
unset($GLOBALS['content']);

$GLOBALS['events'] = [['id'=>0,'title'=>''], ['id'=>301,'link'=>'https://example.test/evento-301','title'=>'Actividad abierta','day'=>'18','month'=>'Octubre'], ['id'=>302,'title'=>'Otra']];
$ev = flacso_home_get_data()['event'];
check(is_array($ev) && (int) $ev['id'] === 301 && $ev['title'] === 'Actividad abierta' && $ev['day'] === '18', 'event es el primer evento válido');
unset($GLOBALS['events']);

$GLOBALS['pinned'] = [(object)['ID'=>101],(object)['ID'=>102],(object)['ID'=>103]];
$three = flacso_home_get_data();
check(count($three['featured_news']) === 2, 'máximo dos destacadas');
check((int) $three['recent_news'][0]->ID === 103 && count($three['recent_news']) === 4, 'la tercera fijada abre las recientes, máx. 4');
$ids = array_map(static fn($p) => (int) $p->ID, array_merge($three['featured_news'], $three['recent_news']));
check($ids === array_unique($ids), 'sin noticias duplicadas');
unset($GLOBALS['pinned']);

$GLOBALS['pinned'] = []; $GLOBALS['recent'] = [];
$none = flacso_home_get_data();
check($none['featured_news'] === [] && $none['recent_news'] === [], 'sin noticias ambos vacíos');
unset($GLOBALS['pinned'], $GLOBALS['recent']);

$GLOBALS['campaign'] = ['source'=>'campaign','title'=>'Convocatoria de prueba','kicker'=>'Inscripciones','cta_primary'=>['label'=>'Postular','url'=>'https://example.test/postular'],'desktop_image_id'=>7,'mobile_image_id'=>8];
$c = flacso_home_get_data()['hero'];
check($c['title_lead'] === 'Convocatoria de prueba' && $c['title_em'] === '', 'campaña CPT reemplaza el título');
check($c['eyebrow'] === 'Inscripciones' && $c['cta'] === ['label'=>'Postular','url'=>'https://example.test/postular'], 'campaña CPT reemplaza kicker y CTA');
check($c['image'] === 'https://example.test/hero-7.jpg' && $c['image_mobile'] === 'https://example.test/hero-8.jpg', 'campaña CPT aporta imágenes');
check(strpos($c['image_srcset'], '1536w') !== false && strpos($c['image_srcset'], '2048w') !== false && strpos($c['image_srcset'], '2560w') !== false, 'campaña CPT arma srcset del hero con 1536w, 2048w y 2560w');
check($c['image_sizes'] === '100vw', 'hero de portada usa sizes="100vw"');
$campaign_html = flacso_home_render();
check(strpos($campaign_html, 'srcset="https://example.test/hero-7-1536x1536.jpg 1536w') !== false && strpos($campaign_html, 'sizes="100vw"') !== false && strpos($campaign_html, 'fetchpriority="high"') !== false, 'hero renderiza srcset, sizes y fetchpriority');
unset($GLOBALS['campaign']);

$GLOBALS['hero_settings'] = ['background_image'=>'https://example.test/ajustes.jpg'];
$s = flacso_home_get_data()['hero'];
check($s['image'] === 'https://example.test/ajustes.jpg' && $s['image_alt'] !== '', 'imagen de ajustes antes que la predeterminada');
$GLOBALS['hero_settings'] = ['background_image'=>Flacso_Main_Page_Settings::DEFAULT_HERO_IMAGE];
$d = flacso_home_get_data()['hero'];
check($d['image'] === 'https://example.test/wp-content/themes/kadence-child-flacso/assets/img/home-hero.jpg', 'la imagen predeterminada del plugin no reemplaza la fotografía del tema');
unset($GLOBALS['hero_settings']);

$GLOBALS['cards'] = [['title'=>'Maestrías','url'=>'#'],['title'=>'Seminarios','url'=>'/formacion/seminarios/']];
$inv = flacso_home_get_data()['cards'];
check(count($inv) === 5 && $inv['maestrias']['url'] === 'https://example.test/formacion/maestrias/', 'categorías sin enlace usan su destino predeterminado');
unset($GLOBALS['cards']);

ob_start(); flacso_home_news_card((object)['ID'=>101], 'featured', true); $card = ob_get_clean();
check(strpos($card, 'class="news-card feature"') !== false && strpos($card, 'NOTICIA FIJADA') !== false && strpos($card, 'class="poster"') !== false && strpos($card, 'Leer noticia') !== false, 'tarjeta destacada en cuadrícula Bento');
ob_start(); flacso_home_news_card((object)['ID'=>201], 'recent', false); $card = ob_get_clean();
check(strpos($card, 'NOTICIA FIJADA') === false && strpos($card, 'class="poster"') === false && strpos($card, 'class="news-meta"') !== false, 'tarjeta sin miniatura no genera enlace visual vacío');
check(strpos($card, 'class="news-card recent news-card--no-image"') !== false, 'tarjeta reciente sin miniatura ocupa ancho completo');
ob_start(); flacso_home_news_card((object)['ID'=>601], 'featured', false); $card = ob_get_clean();
check(strpos($card, 'class="news-card feature news-card--no-image"') !== false && strpos($card, 'class="poster"') === false, 'noticia destacada sin imagen no crea enlace invisible');

// Plantillas
$html = flacso_home_render();
check(strpos($html, '<main id="main" class="flacso-home">') === 0, 'main con id y clase de la portada');
$last = -1;
foreach (['Formación que conecta', 'Encontrá tu próxima formación', 'Tu próximo paso empieza acá.', 'Noticias</h2>', 'Pensar la región.', '¿Qué es FLACSO Uruguay?'] as $heading) {
    $pos = strpos($html, $heading);
    check($pos !== false && $pos > $last, "encabezado en orden: $heading");
    $last = $pos;
}
check(substr_count($html, '<h1') === 1, 'exactamente un h1');
check(strpos($html, 'Próximas actividades') === false, 'sin eventos no hay agenda');
check(substr_count($html, 'class="offer-grid"') === 1 && preg_match_all('#<div class="offer-grid">(.*?)</div>#s', $html, $m) && substr_count($m[1][0], '<a ') === 5, 'cinco enlaces de oferta');
foreach (['01', '02', '03'] as $n) { check(strpos($html, '<span>' . $n . '</span>') !== false, "enlace de investigación $n"); }
check(substr_count($html, 'class="research-links"') === 1 && preg_match('#class="research-links">(.*?)</div>#s', $html, $m) && substr_count($m[1], '<a ') === 3, 'tres enlaces de investigación');
check(strpos($html, 'Conocer las novedades') === false, 'sin texto retirado');
check(strpos($html, 'class="news-bento"') !== false && substr_count($html, 'class="news-card') === 5, 'cinco noticias en una sola cuadrícula');
check(strpos($html, 'class="hero-media"') !== false && strpos($html, 'class="primary hero-cta"') !== false, 'hero con imagen y CTA neumórfico');
check(strpos($html, 'Explorar investigaciones <span') !== false, 'enlace de investigación con la etiqueta del contenido');
check(strpos($html, '<p><strong>FLACSO Uruguay</strong> es la sede uruguaya') !== false, 'texto institucional sin <p> se envuelve en párrafos');
foreach (['hero', 'offer-section', 'campaign', 'news-section', 'research-section', 'about-section'] as $cls) { check(strpos($html, 'class="' . $cls) !== false, "sección $cls"); }
check(strpos($html, 'agenda-section') === false, 'agenda oculta sin eventos');
$blank_attr = 'target="_' . 'blank"';
if (preg_match_all('/<a [^>]*' . preg_quote($blank_attr, '/') . '[^>]*>/', $html, $blank)) { foreach ($blank[0] as $tag) { check(strpos($tag, 'noopener noreferrer') !== false, 'enlace externo con rel'); } }

$GLOBALS['events'] = [['id'=>301,'link'=>'https://example.test/evento-301','title'=>'Actividad abierta','day'=>'18','month'=>'Octubre','hora'=>'18:00','status'=>'Virtual']];
$with = flacso_home_render();
check(strpos($with, 'Próximas actividades') !== false && strpos($with, 'Actividad abierta') !== false && strpos($with, '18:00 · Virtual') !== false, 'agenda con un evento');
unset($GLOBALS['events']);

$GLOBALS['content'] = Flacso_Home_Content::defaults();
$GLOBALS['content']['research']['link_label'] = 'Etiqueta propia';
check(strpos(flacso_home_render(), 'Etiqueta propia <span') !== false, 'la etiqueta editada del enlace se imprime');
unset($GLOBALS['content']);

$GLOBALS['events'] = [['id'=>301,'title'=>'Sin enlace','day'=>'1','month'=>'Mayo']]; // id 301: sin miniatura en el stub
$GLOBALS['thumb_for'] = 301;
$nolink = flacso_home_render();
check(strpos($nolink, 'Sin enlace') !== false && strpos($nolink, 'href=""') === false, 'evento sin enlace no genera href vacío');
unset($GLOBALS['events'], $GLOBALS['thumb_for']);

$GLOBALS['campaign_raw'] = true;
$GLOBALS['content'] = Flacso_Home_Content::defaults();
$GLOBALS['content']['campaign'] = ['enabled'=>true, 'cta_label'=>'', 'cta_url'=>''];
$partial = flacso_home_render();
check(strpos($partial, 'campaign-grid') !== false, 'campaña con claves faltantes se imprime sin avisos');
unset($GLOBALS['content'], $GLOBALS['campaign_raw']);

$css = file_get_contents($root . '/assets/css/front-page.css');
check(strpos($css, '.flacso-home .news-bento .news-card:only-child{grid-column:1/-1') !== false, 'una sola noticia ocupa todo el ancho');
check(strpos($css, '.news-bento .news-card:first-child{grid-column:span 6;') !== false, 'destacada ocupa media cuadrícula');
check(strpos($css, '.news-bento .news-card:nth-child(4):nth-last-child(2)') !== false, 'cinco noticias completan la última fila');
check(strpos($css, '.news-bento .news-card:nth-child(4):last-child{grid-column:1/-1}') !== false, 'cuatro noticias no dejan huecos');
check(strpos($css, '.news-card.news-card--no-image') !== false, 'tarjetas sin imagen no reservan columna');
check(strpos($css, '.news-bento .news-meta{margin:0 0 9px;font-size:12px;color:#53647e}') !== false, 'metadatos de Bento con contraste reforzado');
check(strpos($css, '.flacso-home .hero-media img{transform:translate3d(') !== false, 'parallax actúa sobre la imagen real');
check(strpos($css, '@media(max-width:760px)') !== false && strpos($css, '@media(prefers-reduced-motion:reduce)') !== false, 'adaptación móvil y movimiento reducido');
$js = file_get_contents($root . '/assets/js/front-page.js');
check(strpos($js, 'min-width: 761px') !== false && strpos($js, 'prefers-reduced-motion: reduce') !== false, 'parallax desactivado en móvil o movimiento reducido');
check(preg_match('#\.flacso-home \.container\{[^}]*padding-inline:0#', $css) === 1, 'el contenedor no depende del padding de Bootstrap');

$GLOBALS['recent'] = [601];
$single = flacso_home_render();
check(strpos($single, 'class="news-bento news-bento--compact"') !== false && substr_count($single, 'class="news-card') === 1, 'noticia única mantiene Bento compacto');
$GLOBALS['recent'] = [601, 602];
$pair = flacso_home_render();
check(strpos($pair, 'class="news-bento news-bento--compact"') !== false && substr_count($pair, 'class="news-card') === 2, 'dos noticias mantienen Bento compacto');
$GLOBALS['recent'] = [601, 602, 603];
$triple = flacso_home_render();
check(strpos($triple, 'class="news-bento"') !== false && substr_count($triple, 'class="news-card') === 3, 'tres noticias usan Bento normal');
$GLOBALS['recent'] = [601, 602, 603, 604];
$four = flacso_home_render();
check(strpos($four, 'class="news-bento"') !== false && substr_count($four, 'class="news-card') === 4, 'cuatro noticias usan Bento normal');
$GLOBALS['pinned'] = [(object)['ID'=>101], (object)['ID'=>102]];
$six = flacso_home_render();
check(strpos($six, 'class="news-bento"') !== false && substr_count($six, 'class="news-card') === 6, 'seis noticias usan Bento normal');
unset($GLOBALS['recent'], $GLOBALS['pinned']);

$GLOBALS['content'] = Flacso_Home_Content::defaults();
$GLOBALS['content']['campaign']['enabled'] = false;
$off = flacso_home_render();
check(strpos($off, 'Tu próximo paso empieza acá.') === false && strpos($off, 'class="campaign"') === false, 'campaña apagada oculta');
unset($GLOBALS['content']);

// E2: retiro de fuentes TTF, logo local, no_found_rows, MutationObserver desconectado y cadenas traducibles.
check(strpos($css, '@font-face') === false && strpos($css, '.ttf') === false, 'front-page.css ya no declara @font-face TTF locales');
check(!is_dir($root . '/assets/fonts/roboto'), 'los 4 archivos TTF locales de Roboto se eliminaron');
$about_tpl = (string) file_get_contents($root . '/template-parts/home/about.php');
check(strpos($about_tpl, '/assets/img/logo-flacso-claro.png') !== false && strpos($about_tpl, 'wp-content/uploads') === false, 'about.php carga el logo local del tema');
check(is_file($root . '/assets/img/logo-flacso-claro.png'), 'existe assets/img/logo-flacso-claro.png');
$featured_js = (string) file_get_contents($root . '/assets/js/featured-images.js');
check(strpos($featured_js, 'observer.disconnect()') !== false, 'featured-images.js desconecta el MutationObserver tras load');
check(strpos((string) file_get_contents($root . '/single.php'), "'no_found_rows'") !== false, 'single.php usa no_found_rows en consulta lateral');
check(strpos((string) file_get_contents($root . '/single-evento.php'), "'no_found_rows'") !== false, 'single-evento.php usa no_found_rows en consulta lateral');
$home_php = (string) file_get_contents($root . '/inc/home.php');
check(strpos($home_php, "esc_html__('Leer noticia'") !== false && strpos($home_php, "esc_html__('NOTICIA FIJADA'") !== false, 'inc/home.php internacionaliza los textos de tarjetas de noticias');

echo "Portada: contrato de datos correcto.\n";
