<?php
$root = dirname(__DIR__);
function home_v2_assert($ok, $message) { if (!$ok) { fwrite(STDERR, "FAIL: {$message}\n"); exit(1); } }
$front = file_get_contents($root . '/front-page.php');
$functions = file_get_contents($root . '/functions.php');
$template = file_get_contents($root . '/inc/home-v2.php');
$css = file_get_contents($root . '/assets/css/home-v2.css');


if (!defined('ABSPATH')) { define('ABSPATH', __DIR__); }
function flacso_section_novedades_get_sticky_posts($limit) {
    $GLOBALS['featured_limit'] = $limit;
    $count = $GLOBALS['featured_count'] ?? 3;
    $posts = $count ? array_map(static fn($id) => (object) ['ID' => $id], range(100, 99 + $count)) : [];
    return $limit === -1 ? $posts : array_slice($posts, 0, $limit);
}
function flacso_section_novedades_get_posts($limit, $category, $page) { return ['posts' => [(object) ['ID' => 101]]]; }
function wp_strip_all_tags($value) { return strip_tags((string) $value); }
function get_the_title($post_id) { return 'Noticia de prueba'; }
function has_excerpt($post_id) { return true; }
function get_the_excerpt($post_id) { return 'Resumen de prueba'; }
function get_post_thumbnail_id($post_id) { return 0; }
function get_permalink($post_id) { return 'https://example.test/noticia'; }
function get_the_date($format, $post_id) { return $format === 'c' ? '2026-10-01T00:00:00-03:00' : '01/10/2026'; }
function esc_attr($value) { return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8'); }
function esc_html($value) { return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8'); }
function esc_url($value) { return (string) $value; }
function home_url($path = '') { return 'https://example.test' . $path; }
function flacso_section_quienes_somos_render() { return '<h2>Institucional</h2>'; }
function flacso_section_oferta_educativa_render() { return '<h2>Oferta educativa</h2>'; }
function flacso_section_contacto_render() { return '<h2>Contacto</h2>'; }
class Flacso_Main_Page_Settings {
    public static function get_homepage_section_order() { return ['novedades', 'quienes', 'posgrados', 'contacto']; }
    public static function is_section_visible($key) { return true; }
}
require_once $root . '/inc/home-v2.php';
$rendered_news = flacso_home_v2_render_featured_news();
$rendered_sections = flacso_home_v2_render_sections();
foreach ([0, 1, 2, 3, 4, 5, 8] as $count) {
    $GLOBALS['featured_count'] = $count;
    $news = flacso_home_v2_render_featured_news();
    home_v2_assert(substr_count($news, 'news-card--featured') === $count, 'renderiza todos los destacados: ' . $count);
    home_v2_assert(strpos($news, 'data-home-news-status') === false, 'la grilla no usa paginación');
}
unset($GLOBALS['featured_count']);

 home_v2_assert($GLOBALS['featured_limit'] === -1 && substr_count($rendered_news, 'news-card--featured') === 3, 'la portada solicita todos los destacados');
 home_v2_assert(strpos($rendered_news, 'data-home-news-next') === false && strpos($rendered_news, 'data-home-news-status') === false, 'el puzzle no muestra flechas ni paginador');

home_v2_assert(strpos($rendered_news, '>Actualidad<') === false, 'la portada renderizada no debe mostrar el supratítulo Actualidad');
home_v2_assert(strpos($rendered_news, '>Novedades</h2>') !== false, 'Novedades debe conservarse como encabezado principal');
home_v2_assert(strpos($rendered_news, '<h3>Destacados</h3>') !== false, 'el carrusel debe identificarse como Destacados');
home_v2_assert(preg_match('/news-card--featured.*?news-badge[^>]*>Destacada<.*?<p>Resumen de prueba<\/p>/s', $rendered_news) === 1, 'cada noticia destacada debe mostrar su sello y resumen');
home_v2_assert(preg_match('/news-card--latest.*?<\/article>/s', $rendered_news, $latest_card) === 1 && strpos($latest_card[0], '<p>') === false, 'las últimas noticias deben ser compactas y no mostrar resumen');
$news_position = strpos($rendered_sections, 'data-section-key="novedades"');
$offer_position = strpos($rendered_sections, 'data-section-key="posgrados"');
$institutional_position = strpos($rendered_sections, 'data-section-key="quienes"');
$contact_position = strpos($rendered_sections, 'data-section-key="contacto"');
home_v2_assert($news_position < $offer_position && $offer_position < $institutional_position && $institutional_position < $contact_position, 'la portada debe ordenar Novedades, Oferta educativa, Institucional y Contacto');
home_v2_assert(strpos($front, 'FLACSO_Home_Campaign::is_enabled') !== false, 'la plantilla respeta el rollback flag');
home_v2_assert(strpos($functions, 'assets/css/home-v2.css') !== false, 'el tema carga assets v2');
home_v2_assert(strpos($template, "home_url('/formacion/')") !== false, 'conserva la ruta de formación');
home_v2_assert(strpos($template, 'flacso_home_v2_render_sections') !== false, 'la portada v2 conserva las secciones existentes');
home_v2_assert(strpos($template, 'flacso_home_v2_render_featured_news') !== false, 'la portada v2 ofrece formato editorial alternativo');
home_v2_assert(strpos($template, 'flacso-home-v2__featured-news') !== false, 'el formato destacado tiene markup propio');
home_v2_assert(strpos($template, "'instagram' =>") === false, 'Instagram no debe renderizarse en la portada');
home_v2_assert(strpos($template, 'flacso-home-v2__bento-grid') !== false, 'las noticias fijadas deben usar el puzzle');
 home_v2_assert(strpos($functions, 'assets/js/home-v2.js') === false, 'la portada no carga JavaScript para los destacados');
home_v2_assert(strpos($template, 'flacso_section_novedades_get_posts(6') !== false, 'la portada debe cargar seis noticias no fijadas');
home_v2_assert(strpos($template, 'flacso-home-v2__latest-news') !== false, 'las noticias no fijadas deben tener un listado propio');
home_v2_assert(strpos($css, '.flacso-home-v2 svg') !== false, 'los iconos quedan dimensionados dentro de la portada v2');
home_v2_assert(strpos($css, '@media (min-width: 761px)') !== false, 'las mejoras de escritorio deben aplicarse desde un breakpoint ascendente');
home_v2_assert(strpos($css, '.flacso-home-v2__latest-grid { display: grid; grid-template-columns: 1fr;') !== false, 'las noticias no fijadas deben comenzar en una columna móvil');
home_v2_assert(strpos($css, 'aspect-ratio: 4 / 5') !== false, 'las portadas de novedades deben conservar relación 4:5');
home_v2_assert(strpos($template, 'flacso-home-v2__hero') !== false && strpos($css, '.flacso-home-v2__hero') !== false, 'hero v2 tiene markup y estilos');
home_v2_assert(strpos($template, 'flacso_section_congreso_render') === false, 'la portada v2 no debe renderizar Congreso');
home_v2_assert(strpos($front, "'congreso'") === false, 'el fallback de portada no debe renderizar Congreso');

function home_v2_exact_viewport(string $html, int $width, int $height): string {
    $html = str_replace('<style>', '<style>body{margin:0}', $html);
    return '<!doctype html><html><meta charset="utf-8"><body style="margin:0"><iframe width="' . $width . '" height="' . $height . '" style="border:0" srcdoc="'
        . htmlspecialchars($html, ENT_QUOTES, 'UTF-8') . '" onload="document.getElementById(\'result\').textContent=this.contentDocument.getElementById(\'result\').textContent"></iframe><pre id="result"></pre></body></html>';
}
function home_v2_news_layout(string $css, string $news, int $width, int $height): array {
    $chrome = getenv('FLACSO_TEST_BROWSER') ?: trim((string) shell_exec('command -v google-chrome || command -v chromium || true'));
    home_v2_assert($chrome !== '', 'la prueba visual requiere Chrome o Chromium');
    $html = '<!doctype html><html><head><meta charset="utf-8"><style>' . $css . '</style></head><body>'
        . '<main class="flacso-home-v2"><article class="flacso-home-v2__section">' . $news . '</article></main>'
        . '<pre id="result"></pre><script>const shell=document.querySelector(".flacso-home-v2__bento-shell");'
        . 'const track=document.querySelector(".flacso-home-v2__bento-grid");const featured=document.querySelector(".flacso-home-v2__news-card--featured");'
        . 'const badge=document.querySelector(".flacso-home-v2__news-badge");const latestGrid=document.querySelector(".flacso-home-v2__latest-grid");'
        . 'const latest=document.querySelector(".flacso-home-v2__news-card--latest");const latestImage=latest.querySelector(".flacso-home-v2__news-image");'
        . 'const featuredImage=featured.querySelector(".flacso-home-v2__news-image");document.querySelector("#result").textContent=JSON.stringify({'
        . 'shellBackground:getComputedStyle(shell).backgroundColor,badgeBackground:getComputedStyle(badge).backgroundColor,'
        . 'latestDisplay:getComputedStyle(latest).display,latestShadow:getComputedStyle(latest).boxShadow,'
        . 'latestColumns:getComputedStyle(latestGrid).gridTemplateColumns.split(" ").length,'
        . 'featuredAspect:getComputedStyle(featuredImage).aspectRatio,latestAspect:getComputedStyle(latestImage).aspectRatio,'
        . 'featuredWidth:Math.round(featured.getBoundingClientRect().width),trackWidth:Math.round(track.getBoundingClientRect().width),'
        . 'display:getComputedStyle(track).display,cards:Array.from(track.children).map(c=>{const r=c.getBoundingClientRect(),i=c.querySelector(".flacso-home-v2__news-image").getBoundingClientRect(),b=c.querySelector(".flacso-home-v2__news-body");return {x:r.x,y:r.y,width:r.width,height:r.height,imageWidth:i.width,imageHeight:i.height,bodyWidth:b.clientWidth,bodyScroll:b.scrollHeight,bodyHeight:b.clientHeight}}),'
        . 'latestWidth:Math.round(latest.getBoundingClientRect().width),latestImageWidth:Math.round(latestImage.getBoundingClientRect().width),'
        . 'latestTitleSize:parseFloat(getComputedStyle(latest.querySelector("h4")).fontSize),'
        . 'overflow:document.documentElement.scrollWidth>document.documentElement.clientWidth});</script></body></html>';
    $html = home_v2_exact_viewport($html, $width, $height);
    $temporary = tempnam(sys_get_temp_dir(), 'flacso-home-v2-news-');
    $path = $temporary === false ? false : $temporary . '.html';
    home_v2_assert($path !== false && rename($temporary, $path) && file_put_contents($path, $html) !== false, 'se crea el escenario visual de novedades');
    $command = escapeshellarg($chrome) . ' --headless=new --disable-gpu --no-sandbox --hide-scrollbars'
        . ' --window-size=' . $width . ',' . $height . ' --dump-dom ' . escapeshellarg('file:///' . str_replace('\\', '/', $path)) . (PHP_OS_FAMILY === 'Windows' ? ' 2>NUL' : ' 2>/dev/null');
    $dump = (string) shell_exec($command);
    unlink($path);
    home_v2_assert(preg_match('/<pre id="result">(.*?)<\/pre>/s', $dump, $match) === 1, 'Chrome devuelve las medidas de novedades');
    $layout = json_decode(html_entity_decode($match[1], ENT_QUOTES | ENT_HTML5), true);
    home_v2_assert(is_array($layout), 'las medidas de novedades son válidas');
    return $layout;
}

$news_mobile = home_v2_news_layout($css, $rendered_news, 390, 844);
home_v2_assert($news_mobile['shellBackground'] === 'rgb(238, 245, 251)', 'el carrusel debe ocupar una franja azul clara');
home_v2_assert($news_mobile['badgeBackground'] === 'rgb(252, 209, 22)', 'la noticia destacada debe llevar un sello amarillo');
home_v2_assert($news_mobile['latestDisplay'] === 'grid' && $news_mobile['latestColumns'] === 1, 'las últimas noticias deben formar un listado horizontal de una columna en móvil');
home_v2_assert($news_mobile['latestShadow'] === 'none', 'el listado cronológico no debe usar sombras de carrusel');
home_v2_assert($news_mobile['featuredAspect'] === '4 / 5' && $news_mobile['latestAspect'] === '4 / 5', 'ambos tipos de portada deben conservar 4:5');
home_v2_assert($news_mobile['latestImageWidth'] < $news_mobile['latestWidth'] * .45, 'la miniatura cronológica debe quedar a la izquierda del texto');
home_v2_assert($news_mobile['overflow'] === false, 'las novedades no deben desbordar en móvil');

$news_desktop = home_v2_news_layout($css, $rendered_news, 1440, 1000);
foreach ([901, 1000, 1150, 1151, 1440, 1920] as $width) {
    $puzzle = home_v2_news_layout($css, $rendered_news, $width, 1000);
    $cards = $puzzle['cards'];

    home_v2_assert(abs(($cards[0]['y'] + $cards[0]['height']) - ($cards[2]['y'] + $cards[2]['height'])) < 1, 'las tres piezas cierran a la misma altura en ' . $width);
    foreach ($cards as $card) {
        home_v2_assert(abs($card['imageWidth'] / $card['imageHeight'] - .8) < .01, 'las imágenes conservan 4:5 en ' . $width);
        home_v2_assert($card['bodyWidth'] >= 140 && $card['bodyScroll'] <= $card['bodyHeight'] + 1, 'el texto cabe dentro de cada pieza en ' . $width);
    }
    home_v2_assert(!$puzzle['overflow'], 'el puzzle no desborda en ' . $width);
}

home_v2_assert($news_desktop['latestColumns'] === 2, 'las últimas noticias deben usar dos columnas en escritorio');
home_v2_assert($news_desktop['display'] === 'grid' && $news_desktop['cards'][0]['height'] > $news_desktop['cards'][1]['height'] * 2, 'la primera pieza ocupa dos filas');
home_v2_assert($news_desktop['latestImageWidth'] >= 124 && $news_desktop['latestTitleSize'] >= 16.5, 'las últimas noticias deben conservar una lectura editorial visible');
home_v2_assert($news_desktop['overflow'] === false, 'las novedades no deben desbordar en escritorio');


foreach ([1, 2, 4, 5, 8] as $count) {
    $GLOBALS['featured_count'] = $count;
    $layout = home_v2_news_layout($css, flacso_home_v2_render_featured_news(), 1440, 1600);
    $cards = $layout['cards'];
    home_v2_assert(count($cards) === $count && !$layout['overflow'], 'la grilla variable conserva todas las piezas: ' . $count);
    foreach ($cards as $card) {
        home_v2_assert(abs($card['imageWidth'] / $card['imageHeight'] - .8) < .01 && $card['bodyScroll'] <= $card['bodyHeight'] + 1, 'imagen y texto correctos con ' . $count . ' destacados');
    }
    if ($count === 2) {
        home_v2_assert(abs($cards[0]['width'] - $cards[1]['width']) < 1 && abs($cards[0]['height'] - $cards[1]['height']) < 1, 'dos destacados tienen el mismo tamaño');
    }
    if ($count > 3 && $count % 2 === 0) {
        home_v2_assert(abs($cards[$count - 1]['width'] - $layout['trackWidth']) < 1, 'la última pieza par cierra toda la fila');
    }
}
unset($GLOBALS['featured_count']);

foreach ([901, 1151, 1440] as $width) {
    $GLOBALS['featured_count'] = 2;
    $pair = home_v2_news_layout($css, flacso_home_v2_render_featured_news(), $width, 1000);
    foreach ($pair['cards'] as $card) {
        home_v2_assert($card['bodyWidth'] >= 160 && $card['bodyScroll'] <= $card['bodyHeight'] + 1, 'dos destacados conservan espacio para los títulos en ' . $width);
    }
}
unset($GLOBALS['featured_count']);

function home_v2_quienes_layout(string $css, int $width, int $height): array {
    $chrome = getenv('FLACSO_TEST_BROWSER') ?: trim((string) shell_exec('command -v google-chrome || command -v chromium || true'));
    home_v2_assert($chrome !== '', 'la prueba visual requiere Chrome o Chromium');
    $html = '<!doctype html><html><head><meta charset="utf-8"><style>' . $css . '</style></head><body>'
        . '<main class="flacso-home-v2"><article class="flacso-home-v2__section flacso-home-v2__section--quienes">'
        . '<section class="flacso-quienes-section"><div class="flacso-content-shell"><div class="flacso-quienes-card">'
        . '<div class="flacso-quienes-brand"><div class="flacso-quienes-brand__logo"><img alt="FLACSO Uruguay"></div>'
        . '<p class="flacso-quienes-brand__name">Facultad Latinoamericana de Ciencias Sociales</p></div>'
        . '<div class="flacso-quienes-copy"><h2 class="flacso-quienes-title">¿Qué es FLACSO Uruguay?</h2>'
        . '<div class="flacso-quienes-text"><p>FLACSO Uruguay es la sede uruguaya de la Facultad Latinoamericana de Ciencias Sociales, una red académica regional.</p>'
        . '<p>Ofrece programas de posgrado y desarrolla proyectos de investigación con compromiso social.</p></div>'
        . '<a class="flacso-quienes-cta" href="#">Conocer más</a></div></div></div></section></article></main>'
        . '<pre id="result"></pre><script>const card=document.querySelector(".flacso-quienes-card");'
        . 'const brand=document.querySelector(".flacso-quienes-brand");const copy=document.querySelector(".flacso-quienes-copy");'
        . 'const text=document.querySelector(".flacso-quienes-text");const logo=document.querySelector(".flacso-quienes-brand__logo img");document.querySelector("#result").textContent=JSON.stringify({'
        . 'cardDisplay:getComputedStyle(card).display,columns:getComputedStyle(card).gridTemplateColumns,'
        . 'brandBackground:getComputedStyle(brand).backgroundColor,textAlign:getComputedStyle(text).textAlign,'
        . 'logoFilter:getComputedStyle(logo).filter,'
        . 'cardHeight:Math.round(card.getBoundingClientRect().height),cardWidth:Math.round(card.getBoundingClientRect().width),'
        . 'sectionWidth:Math.round(document.querySelector(".flacso-home-v2__section").getBoundingClientRect().width),'
        . 'copyFont:parseFloat(getComputedStyle(text).fontSize),brandWidth:Math.round(brand.getBoundingClientRect().width),'
        . 'copyWidth:Math.round(copy.getBoundingClientRect().width)});</script></body></html>';
    $html = home_v2_exact_viewport($html, $width, $height);
    $temporary = tempnam(sys_get_temp_dir(), 'flacso-home-v2-quienes-');
    $path = $temporary === false ? false : $temporary . '.html';
    home_v2_assert($path !== false && rename($temporary, $path) && file_put_contents($path, $html) !== false, 'se crea el escenario visual de la sección institucional');
    $command = escapeshellarg($chrome) . ' --headless=new --disable-gpu --no-sandbox --hide-scrollbars'
        . ' --window-size=' . $width . ',' . $height . ' --dump-dom ' . escapeshellarg('file:///' . str_replace('\\', '/', $path)) . (PHP_OS_FAMILY === 'Windows' ? ' 2>NUL' : ' 2>/dev/null');
    $dump = (string) shell_exec($command);
    unlink($path);
    $matched = preg_match('/<pre id="result">(.*?)<\/pre>/s', $dump, $match);
    if ($matched !== 1) {
        fwrite(STDERR, substr($dump, -1200) . "\n");
    }
    home_v2_assert($matched === 1, 'Chrome devuelve las medidas institucionales');
    $layout = json_decode(html_entity_decode($match[1], ENT_QUOTES | ENT_HTML5), true);
    home_v2_assert(is_array($layout), 'las medidas institucionales son válidas');
    return $layout;
}

$quienes_mobile = home_v2_quienes_layout($css, 390, 844);
home_v2_assert($quienes_mobile['cardDisplay'] === 'grid', 'la sección institucional debe conservar una composición controlada');
home_v2_assert($quienes_mobile['brandBackground'] === 'rgb(23, 54, 93)', 'el logotipo claro debe tener un panel azul visible');
home_v2_assert($quienes_mobile['logoFilter'] !== 'none', 'el logotipo azul debe convertirse a blanco sobre el panel institucional');
home_v2_assert($quienes_mobile['textAlign'] === 'left', 'el texto institucional móvil no debe justificarse');
home_v2_assert($quienes_mobile['cardHeight'] < 700, 'la tarjeta institucional móvil no debe generar espacio muerto');

$quienes_desktop = home_v2_quienes_layout($css, 1440, 1000);
home_v2_assert($quienes_desktop['cardWidth'] <= 1100 && $quienes_desktop['cardWidth'] < $quienes_desktop['sectionWidth'], 'la tarjeta institucional debe ser más contenida que el resto de la retícula');
home_v2_assert($quienes_desktop['brandWidth'] < $quienes_desktop['copyWidth'] * .42, 'el contenido institucional debe tener mayor jerarquía que el panel de marca');
home_v2_assert($quienes_desktop['copyFont'] >= 17, 'el texto institucional debe conservar una lectura cómoda');

function home_v2_closing_sections_layout(string $css, int $width, int $height): array {
    $chrome = getenv('FLACSO_TEST_BROWSER') ?: trim((string) shell_exec('command -v google-chrome || command -v chromium || true'));
    home_v2_assert($chrome !== '', 'la prueba visual requiere Chrome o Chromium');
    $cards = '';
    foreach (['Maestrías', 'Especializaciones', 'Diplomas', 'Diplomados', 'Seminarios'] as $index => $title) {
        $modifier = $index === 0 ? ' flacso-oferta-mosaico__card--featured' : '';
        $cards .= '<a class="flacso-oferta-mosaico__card' . $modifier . '" href="#"><div class="flacso-oferta-mosaico__media"></div>'
            . '<div class="flacso-oferta-mosaico__content"><h3>' . $title . '</h3><div class="flacso-oferta-mosaico__description">Propuesta académica de FLACSO Uruguay.</div>'
            . '<span class="flacso-oferta-mosaico__link">Conocer la propuesta →</span></div></a>';
    }
    $legacy_mobile_css = '.flacso-oferta-mosaico__card,.flacso-oferta-mosaico__card--featured{min-height:200px;aspect-ratio:16/9}';
    $html = '<!doctype html><html><head><meta charset="utf-8"><style>' . $css . '</style><style>' . $legacy_mobile_css . '</style></head><body>'
        . '<main class="flacso-home-v2"><article class="flacso-home-v2__section flacso-home-v2__section--posgrados"><section class="flacso-oferta-mosaico">'
        . '<div class="flacso-content-shell"><header class="flacso-oferta-mosaico__header"><h2>Nuestra oferta educativa</h2>'
        . '<div class="flacso-oferta-mosaico__intro">Formaciones en distintos niveles y áreas de las ciencias sociales.</div></header>'
        . '<div class="flacso-oferta-mosaico__grid">' . $cards . '</div></div></section></article>'
        . '<article class="flacso-home-v2__section flacso-home-v2__section--contacto"><section class="flacso-contacto flacso-contacto-section">'
        . '<div class="flacso-content-shell flacso-contacto-content"><div class="flacso-contacto-card"><span class="flacso-contacto-icon"><svg></svg></span>'
        . '<div class="flacso-contacto-copy"><h2 class="flacso-contacto-heading">Contactanos</h2><p class="flacso-contacto-subtitle">Con gusto responderemos todas tus consultas.</p></div>'
        . '<a class="flacso-btn flacso-contacto-cta" href="#">Solicite información</a></div></div></section></article></main>'
        . '<pre id="result"></pre><script>const offer=document.querySelector(".flacso-oferta-mosaico__grid");'
        . 'const featured=document.querySelector(".flacso-oferta-mosaico__card--featured");const regular=document.querySelectorAll(".flacso-oferta-mosaico__card")[1];'
        . 'const contact=document.querySelector(".flacso-contacto-card");const cta=document.querySelector(".flacso-contacto-cta");'
        . 'const offerHeader=document.querySelector(".flacso-oferta-mosaico__header");const offerStyle=getComputedStyle(offer);const contactStyle=getComputedStyle(contact);document.querySelector("#result").textContent=JSON.stringify({'
        . 'offerDisplay:offerStyle.display,offerColumnCount:offerStyle.gridTemplateColumns.split(" ").length,gridWidth:Math.round(offer.getBoundingClientRect().width),'
        . 'featuredWidth:Math.round(featured.getBoundingClientRect().width),featuredHeight:Math.round(featured.getBoundingClientRect().height),'
        . 'featuredAspect:getComputedStyle(featured).aspectRatio,'
        . 'regularWidth:Math.round(regular.getBoundingClientRect().width),regularHeight:Math.round(regular.getBoundingClientRect().height),'
        . 'contactDisplay:contactStyle.display,contactColumnCount:contactStyle.gridTemplateColumns.split(" ").length,'
        . 'offerHeaderGap:parseFloat(getComputedStyle(offerHeader).columnGap),contactBackground:contactStyle.backgroundColor,'
        . 'ctaWidth:Math.round(cta.getBoundingClientRect().width),ctaHeight:Math.round(cta.getBoundingClientRect().height),'
        . 'contactHeight:Math.round(contact.getBoundingClientRect().height),contactWidth:Math.round(contact.getBoundingClientRect().width),'
        . 'overflow:document.documentElement.scrollWidth>document.documentElement.clientWidth});</script></body></html>';
    $html = home_v2_exact_viewport($html, $width, $height);
    $temporary = tempnam(sys_get_temp_dir(), 'flacso-home-v2-closing-');
    $path = $temporary === false ? false : $temporary . '.html';
    home_v2_assert($path !== false && rename($temporary, $path) && file_put_contents($path, $html) !== false, 'se crea el escenario visual de oferta y contacto');
    $command = escapeshellarg($chrome) . ' --headless=new --disable-gpu --no-sandbox --hide-scrollbars'
        . ' --window-size=' . $width . ',' . $height . ' --dump-dom ' . escapeshellarg('file:///' . str_replace('\\', '/', $path)) . (PHP_OS_FAMILY === 'Windows' ? ' 2>NUL' : ' 2>/dev/null');
    $dump = (string) shell_exec($command);
    unlink($path);
    home_v2_assert(preg_match('/<pre id="result">(.*?)<\/pre>/s', $dump, $match) === 1, 'Chrome devuelve las medidas de oferta y contacto');
    $layout = json_decode(html_entity_decode($match[1], ENT_QUOTES | ENT_HTML5), true);
    home_v2_assert(is_array($layout), 'las medidas de oferta y contacto son válidas');
    return $layout;
}

$closing_mobile = home_v2_closing_sections_layout($css, 390, 844);
home_v2_assert($closing_mobile['offerDisplay'] === 'grid' && $closing_mobile['offerColumnCount'] === 1, 'la oferta debe comenzar en una columna móvil');
home_v2_assert($closing_mobile['contactDisplay'] === 'grid' && $closing_mobile['contactColumnCount'] === 1, 'contacto debe apilar su contenido en móvil');
home_v2_assert($closing_mobile['contactBackground'] === 'rgb(23, 54, 93)', 'contacto debe cerrar la portada con alto contraste');
home_v2_assert($closing_mobile['featuredAspect'] === 'auto', 'la portada nueva debe neutralizar la proporción móvil heredada');
home_v2_assert($closing_mobile['featuredWidth'] <= $closing_mobile['gridWidth'], 'las tarjetas de oferta no deben exceder la columna móvil');
home_v2_assert($closing_mobile['overflow'] === false, 'oferta y contacto no deben desbordar en móvil');

$closing_tablet = home_v2_closing_sections_layout($css, 768, 1000);
home_v2_assert($closing_tablet['offerColumnCount'] === 2, 'la oferta debe usar dos columnas en tablet');
home_v2_assert($closing_tablet['featuredWidth'] > $closing_tablet['regularWidth'] * 1.8, 'la propuesta principal debe ocupar el ancho de la retícula tablet: ' . json_encode($closing_tablet));
home_v2_assert($closing_tablet['contactColumnCount'] === 3, 'contacto debe usar una fila en tablet');
home_v2_assert($closing_tablet['overflow'] === false, 'oferta y contacto no deben desbordar en tablet');

$closing_desktop = home_v2_closing_sections_layout($css, 1440, 1000);
home_v2_assert($closing_desktop['offerColumnCount'] === 3, 'la oferta debe usar tres columnas en escritorio');
home_v2_assert($closing_desktop['featuredHeight'] > $closing_desktop['regularHeight'] * 1.8, 'la propuesta principal debe ocupar dos filas en escritorio');
home_v2_assert($closing_desktop['offerHeaderGap'] <= 28, 'el título y la introducción de oferta deben leerse como una sola cabecera');
home_v2_assert($closing_desktop['contactColumnCount'] === 3 && $closing_desktop['ctaWidth'] < $closing_desktop['contactWidth'], 'contacto debe conservar una llamada a la acción compacta en escritorio');
home_v2_assert($closing_desktop['contactHeight'] >= 140 && $closing_desktop['ctaHeight'] >= 54, 'contacto debe cerrar la portada con mayor presencia');
home_v2_assert($closing_desktop['overflow'] === false, 'oferta y contacto no deben desbordar en escritorio');
echo "OK home v2 contract\n";
