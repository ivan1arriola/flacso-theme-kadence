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
echo "OK home v2 contract\n";
