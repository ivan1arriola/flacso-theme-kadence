<?php
$root = dirname(__DIR__);
function home_v2_assert($ok, $message) { if (!$ok) { fwrite(STDERR, "FAIL: {$message}\n"); exit(1); } }
$front = file_get_contents($root . '/front-page.php');
$functions = file_get_contents($root . '/functions.php');
$template = file_get_contents($root . '/inc/home-v2.php');
$css = file_get_contents($root . '/assets/css/home-v2.css');
home_v2_assert(strpos($front, 'FLACSO_Home_Campaign::is_enabled') !== false, 'la plantilla respeta el rollback flag');
home_v2_assert(strpos($functions, 'assets/css/home-v2.css') !== false, 'el tema carga assets v2');
home_v2_assert(strpos($template, "home_url('/formacion/')") !== false, 'conserva la ruta de formación');
home_v2_assert(strpos($template, 'flacso_home_v2_render_sections') !== false, 'la portada v2 conserva las secciones existentes');
home_v2_assert(strpos($template, 'flacso-home-v2__hero') !== false && strpos($css, '.flacso-home-v2__hero') !== false, 'hero v2 tiene markup y estilos');
home_v2_assert(strpos($template, 'flacso_section_congreso_render') === false, 'la portada v2 no debe renderizar Congreso');
home_v2_assert(strpos($front, "'congreso'") === false, 'el fallback de portada no debe renderizar Congreso');
echo "OK home v2 contract\n";
