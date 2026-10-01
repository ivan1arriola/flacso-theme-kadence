<?php
$root = dirname(__DIR__);
function home_v2_assert($ok, $message) { if (!$ok) { fwrite(STDERR, "FAIL: {$message}\n"); exit(1); } }
$front = file_get_contents($root . '/front-page.php');
$functions = file_get_contents($root . '/functions.php');
$template = file_get_contents($root . '/inc/home-v2.php');
$css = file_get_contents($root . '/assets/css/home-v2.css');
$js = file_get_contents($root . '/assets/js/home-v2.js');
home_v2_assert(strpos($front, 'FLACSO_Home_Campaign::is_enabled') !== false, 'la plantilla respeta el rollback flag');
home_v2_assert(strpos($functions, 'assets/css/home-v2.css') !== false, 'el tema carga assets v2');
home_v2_assert(strpos($template, "home_url('/formacion/')") !== false, 'conserva la ruta de formación');
home_v2_assert(strpos($template, 'flacso_home_v2_render_sections') !== false, 'la portada v2 conserva las secciones existentes');
home_v2_assert(strpos($template, 'flacso_home_v2_render_featured_news') !== false, 'la portada v2 ofrece formato editorial alternativo');
home_v2_assert(strpos($template, 'flacso-home-v2__featured-news') !== false, 'el formato destacado tiene markup propio');
home_v2_assert(strpos($template, "'instagram' =>") === false, 'Instagram no debe renderizarse en la portada');
home_v2_assert(strpos($template, 'data-home-news-carousel') !== false, 'las noticias fijadas deben usar un carrusel navegable');
home_v2_assert(strpos($template, 'flacso_section_novedades_get_posts(6') !== false, 'la portada debe cargar seis noticias no fijadas');
home_v2_assert(strpos($template, 'flacso-home-v2__latest-news') !== false, 'las noticias no fijadas deben tener un listado propio');
home_v2_assert(strpos($functions, 'assets/js/home-v2.js') !== false, 'el tema debe cargar el controlador del carrusel');
home_v2_assert(strpos($js, "document.readyState === 'loading'") !== false, 'el carrusel debe inicializar aunque el script cargue después del DOM');
home_v2_assert(strpos($js, 'track.scrollTo') !== false, 'los controles deben mover el carrusel a una posición absoluta');
home_v2_assert(strpos($css, '.flacso-home-v2 svg') !== false, 'los iconos quedan dimensionados dentro de la portada v2');
home_v2_assert(strpos($css, '@media (min-width: 761px)') !== false, 'las mejoras de escritorio deben aplicarse desde un breakpoint ascendente');
home_v2_assert(strpos($css, '.flacso-home-v2__latest-grid { display: grid; grid-template-columns: 1fr;') !== false, 'las noticias no fijadas deben comenzar en una columna móvil');
home_v2_assert(strpos($css, 'aspect-ratio: 4 / 5') !== false, 'las portadas de novedades deben conservar relación 4:5');
home_v2_assert(strpos($template, 'flacso-home-v2__hero') !== false && strpos($css, '.flacso-home-v2__hero') !== false, 'hero v2 tiene markup y estilos');
home_v2_assert(strpos($template, 'flacso_section_congreso_render') === false, 'la portada v2 no debe renderizar Congreso');
home_v2_assert(strpos($front, "'congreso'") === false, 'el fallback de portada no debe renderizar Congreso');

function home_v2_quienes_layout(string $css, int $width, int $height): array {
    $chrome = trim((string) shell_exec('command -v google-chrome || command -v chromium || true'));
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
        . 'cardHeight:Math.round(card.getBoundingClientRect().height),brandWidth:Math.round(brand.getBoundingClientRect().width),'
        . 'copyWidth:Math.round(copy.getBoundingClientRect().width)});</script></body></html>';
    $temporary = tempnam(sys_get_temp_dir(), 'flacso-home-v2-quienes-');
    $path = $temporary === false ? false : $temporary . '.html';
    home_v2_assert($path !== false && rename($temporary, $path) && file_put_contents($path, $html) !== false, 'se crea el escenario visual de la sección institucional');
    $command = escapeshellarg($chrome) . ' --headless=new --disable-gpu --no-sandbox --hide-scrollbars'
        . ' --window-size=' . $width . ',' . $height . ' --dump-dom ' . escapeshellarg('file://' . $path) . ' 2>/dev/null';
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
home_v2_assert($quienes_desktop['brandWidth'] < $quienes_desktop['copyWidth'], 'en escritorio el contenido debe tener mayor jerarquía que la marca');
echo "OK home v2 contract\n";
