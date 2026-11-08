<?php
$root = dirname(__DIR__);
if (!defined('ABSPATH')) { define('ABSPATH', __DIR__); }
function check($condition, $message) { if (!$condition) { fwrite(STDERR, "FAIL: $message\n"); exit(1); } }
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
function wp_trim_words($value, $count) { return implode(' ', array_slice(explode(' ', $value), 0, $count)); }
function get_post_field($field, $id) { return 'Contenido de prueba'; }
function get_the_title($id) { return 'Noticia ' . $id; }
function get_the_excerpt($id) { return 'Resumen de la noticia'; }
function has_excerpt($id) { return true; }
function get_the_date($format, $id) { return $format === 'c' ? '2026-10-01T00:00:00-03:00' : '1 de octubre de 2026'; }
function get_permalink($id) { return 'https://example.test/noticia-' . $id; }
function get_post_thumbnail_id($id) { return $id === 101 ? 55 : 0; }
function wp_get_attachment_image($id, $size, $icon=false, $attrs=[]) { return '<img src="https://example.test/image.jpg" alt="">'; }
function wp_get_attachment_image_url($id, $size) { return 'https://example.test/hero-' . $id . '.jpg'; }
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
class Flacso_Main_Page_Settings { public static function get_section($key) {
    if ($key === 'quienes') { return ['content'=>$GLOBALS['quienes'] ?? '<p>Texto institucional editable.</p>']; }
    return $GLOBALS['hero_settings'] ?? [];
} }
class Flacso_Home_Content {
    public static function defaults(): array { return [
        'hero'=>['eyebrow'=>'PROGRAMA URUGUAY','title_lead'=>'Formación que conecta,','title_em'=>'conocimiento que transforma.','intro'=>'Formación, investigación y cooperación académica con perspectiva latinoamericana.','cta_label'=>'Explorar oferta académica','cta_url'=>'/formacion/'],
        'campaign'=>['enabled'=>true,'label'=>'INSCRIPCIONES 2027','title'=>'Tu próximo paso empieza acá.','text'=>'Explorá las propuestas con inscripciones abiertas.','cta_label'=>'Conocer la oferta','cta_url'=>'/formacion/'],
        'research'=>['title_lead'=>'Pensar la región.','title_em'=>'Producir conocimiento.','intro'=>'Conocé los proyectos, centros y publicaciones de FLACSO Uruguay.','link_label'=>'Ver investigaciones','link_url'=>'/institucional/investigaciones/','links'=>[
            ['label'=>'Proyectos de investigación','url'=>'/institucional/investigaciones/'],
            ['label'=>'Archivo Histórico IIN–FLACSO','url'=>'https://www.archivoiinflacso.uy/'],
            ['label'=>'Libros y publicaciones','url'=>'/categoria/publicaciones/libros/'],
        ]],
    ]; }
    public static function get(): array { return $GLOBALS['content'] ?? self::defaults(); }
}
require $root . '/inc/home.php';

$data = flacso_home_get_data();
check($data['hero']['title_lead'] === 'Formación que conecta,', 'título del hero desde el contenido editable');
check($data['hero']['title_em'] === 'conocimiento que transforma.', 'énfasis del hero');
check($data['hero']['eyebrow'] === 'PROGRAMA URUGUAY' && $data['hero']['cta']['url'] === '/formacion/', 'eyebrow y CTA por defecto');
check($data['hero']['image'] === 'https://example.test/wp-content/themes/kadence-child-flacso/assets/img/home-hero.jpg', 'sin imagen configurada usa la fotografía por defecto');
check($data['hero']['image_alt'] === 'Personas adultas participando en una instancia de intercambio académico', 'alt descriptivo de la fotografía por defecto');
check(count($data['cards']) === 5 && array_keys($data['cards']) === ['maestrias','especializaciones','diplomados','diplomas','seminarios'], 'cinco niveles en orden');
check(isset($data['cards']['maestrias']['title'], $data['cards']['maestrias']['url'], $data['cards']['maestrias']['open']), 'tarjeta con title, url y open');
check($data['campaign'] !== null && $data['campaign']['cta']['label'] === 'Conocer la oferta', 'campaña por defecto activa');
check($data['event'] === null, 'sin eventos, event es null');
check($data['research']['title_lead'] === 'Pensar la región.' && count($data['research']['links']) === 3, 'investigación desde el contenido editable');
check(strpos($data['institution']['content'], 'Texto institucional editable.') !== false, 'contenido institucional del plugin');
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
unset($GLOBALS['campaign']);

$GLOBALS['hero_settings'] = ['background_image'=>'https://example.test/ajustes.jpg'];
$s = flacso_home_get_data()['hero'];
check($s['image'] === 'https://example.test/ajustes.jpg' && $s['image_alt'] !== '', 'imagen de ajustes antes que la predeterminada');
unset($GLOBALS['hero_settings']);

$GLOBALS['cards'] = [['title'=>'Maestrías','url'=>'#'],['title'=>'Seminarios','url'=>'/formacion/seminarios/']];
$inv = flacso_home_get_data()['cards'];
check(count($inv) === 5 && $inv['maestrias']['url'] === 'https://example.test/formacion/maestrias/', 'categorías sin enlace usan su destino predeterminado');
unset($GLOBALS['cards']);

ob_start(); flacso_home_news_card((object)['ID'=>101], 'featured', true); $card = ob_get_clean();
check(strpos($card, 'home-news-card--featured') !== false && strpos($card, 'Noticia fijada') !== false, 'tarjeta de noticia');
echo "Portada: contrato de datos correcto.\n";
