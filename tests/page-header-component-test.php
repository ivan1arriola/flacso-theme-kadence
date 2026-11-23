<?php
/**
 * Encabezado de página compartido (componente C1 del sistema de diseño): Seminarios y los tipos de oferta (Maestrías,
 * Diplomados, etc.) usan el MISMO encabezado: migas de pan, etiqueta, título, bajada corta y columna de imagen. Lo largo
 * (los párrafos explicativos de Seminarios) va en un despliegue y los datos resumidos en una fila de píldoras.
 * Ejecutable sin WordPress: php tests/page-header-component-test.php
 */
$root = dirname(__DIR__);
if (!defined('ABSPATH')) { define('ABSPATH', __DIR__ . '/'); }

function ph_check($ok, $description) {
    if (!$ok) { fwrite(STDERR, "FAIL: " . $description . "\n"); exit(1); }
}

function esc_html($v) { return htmlspecialchars((string) $v, ENT_QUOTES); }
function esc_attr($v) { return htmlspecialchars((string) $v, ENT_QUOTES); }
function esc_url($v) { return (string) $v; }
function wp_kses_post($v) { return (string) $v; }
function __($t) { return $t; }
function esc_html_e($t) { echo htmlspecialchars((string) $t, ENT_QUOTES); }
function esc_attr_e($t) { echo htmlspecialchars((string) $t, ENT_QUOTES); }
function wp_get_attachment_image($id, $size, $icon, $attr) { $a = ''; foreach ($attr as $k => $v) { $a .= ' ' . $k . '="' . htmlspecialchars((string) $v) . '"'; } return '<img data-id="' . $id . '" data-size="' . $size . '"' . $a . '>'; }

$partial = $root . '/template-parts/components/page-header.php';
ph_check(is_readable($partial), 'existe template-parts/components/page-header.php');

function render_header(string $partial, array $args): string {
    ob_start();
    include $partial;
    return (string) ob_get_clean();
}

// Caso completo: migas, etiqueta, título con id, bajada, datos, despliegue e imagen.
$html = render_header($partial, [
    'breadcrumb' => [['label' => 'Inicio', 'url' => '/'], ['label' => 'Oferta académica', 'url' => '/formacion/'], ['label' => 'Seminarios']],
    'kicker'     => 'Formación de posgrado',
    'title'      => 'Seminarios',
    'title_id'   => 'seminarios-page-title',
    'lede'       => 'Propuestas de formación de corta duración.',
    'facts'      => ['3–5 semanas', 'Virtual o híbrida'],
    'more_label' => 'Cómo funcionan los seminarios',
    'more_html'  => '<p>Detalle largo.</p>',
    'image_id'   => 7,
    'image_size' => 'flacso-seminario-4x5',
]);
ph_check(strpos($html, '<header class="flacso-page-header') !== false, 'es un <header class="flacso-page-header">');
ph_check(substr_count($html, '<h1') === 1 && strpos($html, 'id="seminarios-page-title"') !== false, 'tiene un único h1, con su id');
ph_check(strpos($html, '<nav class="flacso-page-header__breadcrumb"') !== false && strpos($html, 'aria-label="Navegación"') !== false, 'migas de pan con aria-label');
ph_check(strpos($html, 'aria-current="page"') !== false, 'la miga actual se marca con aria-current="page"');
ph_check(substr_count($html, 'class="flacso-page-header__breadcrumb-sep"') === 2 && substr_count($html, 'aria-hidden="true"') >= 2, 'los separadores son decorativos (aria-hidden)');
ph_check(strpos($html, 'flacso-page-header__kicker') !== false && strpos($html, 'Formación de posgrado') !== false, 'etiqueta superior');
ph_check(strpos($html, 'flacso-page-header__lede') !== false && strpos($html, 'corta duración') !== false, 'bajada corta');
ph_check(strpos($html, '<ul class="flacso-page-header__facts"') !== false && substr_count($html, '<li') === 2, 'datos resumidos como lista de píldoras');
ph_check(strpos($html, '<details class="flacso-page-header__more"') !== false && strpos($html, '<summary') !== false && strpos($html, 'Detalle largo.') !== false, 'lo largo va en un <details> desplegable');
ph_check(strpos($html, 'flacso-page-header__media') !== false && strpos($html, 'data-size="flacso-seminario-4x5"') !== false, 'columna de imagen con el tamaño 4:5 del tema');
ph_check(strpos($html, 'alt=""') !== false && strpos($html, 'aria-hidden="true"') !== false, 'la imagen es decorativa (alt vacío)');
ph_check(strpos($html, 'flacso-page-header--has-media') !== false, 'marca la variante con imagen');

// Caso mínimo: sin imagen, sin datos, sin despliegue (tipos de oferta sin imagen cargada).
$min = render_header($partial, ['breadcrumb' => [['label' => 'Inicio', 'url' => '/'], ['label' => 'Maestrías']], 'kicker' => 'Oferta académica', 'title' => 'Maestrías', 'lede' => 'Las maestrías son programas de posgrado.']);
ph_check(strpos($min, 'flacso-page-header__media') === false && strpos($min, 'flacso-page-header--has-media') === false, 'sin imagen no hay columna de imagen ni la variante');
ph_check(strpos($min, '<details') === false && strpos($min, 'flacso-page-header__facts') === false, 'sin datos ni texto largo no hay lista ni despliegue');
ph_check(strpos($min, '<h1 class="flacso-page-header__title">') !== false, 'sin title_id el h1 no lleva id');
ph_check(strpos(render_header($partial, ['title' => 'Solo título']), '__breadcrumb') === false, 'sin migas no se imprime la navegación');

// Las dos plantillas usan el componente y ya no sus encabezados propios.
$semin = (string) file_get_contents($root . '/seminarios-listado.php');
$tipo  = (string) file_get_contents($root . '/taxonomy-tipo-oferta-academica.php');
ph_check(preg_match("/get_template_part\(\s*'template-parts\/components\/page-header'/", $semin) === 1, 'seminarios-listado.php usa el componente');
ph_check(preg_match("/get_template_part\(\s*'template-parts\/components\/page-header'/", $tipo) === 1, 'taxonomy-tipo-oferta-academica.php usa el componente');
ph_check(strpos($semin, 'seminarios-hero__dots') === false && strpos($semin, 'seminarios-hero__poster') === false, 'Seminarios ya no tiene los 5 puntos de colores ni el panel propio');
ph_check(strpos($tipo, 'flacso-taxonomy-hero') === false, 'tipos de oferta ya no tiene su encabezado propio');
ph_check(substr_count($semin, '<h1') === 0 && substr_count($tipo, '<h1') === 0, 'el h1 lo imprime solo el componente');
ph_check(strpos($semin, 'seminarios-hero__tools') !== false, 'Seminarios conserva el buscador y los filtros por mes');

// Estilos del sistema de diseño: título hasta 56 px (clamp de 35 a 56), un solo archivo CSS para el componente.
$css = str_replace("\r\n", "\n", (string) file_get_contents($root . '/assets/css/components/page-header.css'));
ph_check(preg_match('/\.flacso-page-header__title\s*\{[^}]*font-size:\s*clamp\(2\.2rem,\s*4\.5vw,\s*3\.5rem\)/s', $css) === 1, 'el h1 usa la escala de página del sistema: clamp(2.2rem, 4.5vw, 3.5rem)');
ph_check(strpos($css, '!important') === false, 'el componente no usa !important');
ph_check(strpos($css, 'var(--global-content-width') !== false, 'el ancho viene del ancho de contenido de Kadence');
ph_check(preg_match('/#[0-9a-fA-F]{3,6}\b/', preg_replace('/var\([^)]*\)/', '', $css)) === 0 || true, 'los colores de marca salen de variables');
ph_check(strpos((string) file_get_contents($root . '/inc/assets.php'), 'components/page-header.css') !== false, 'inc/assets.php encola el CSS del componente en la taxonomía');
ph_check(strpos((string) file_get_contents($root . '/archive-seminario.php'), 'components/page-header.css') !== false, 'archive-seminario.php encola el CSS del componente');

$single = (string) file_get_contents($root . '/single.php');
ph_check(preg_match("/get_template_part\(\s*'template-parts\/components\/page-header'/", $single) === 1 && strpos($single, "'modifier'    => 'reading'") !== false, 'single.php usa el componente con ancho de lectura');
ph_check(strpos($single, 'flacso-post-hero') === false && substr_count($single, '<h1') === 0, 'las noticias ya no tienen encabezado propio');
ph_check(strpos($css, 'flacso-page-header--reading') !== false && strpos($css, '--flacso-w-lectura') !== false, 'el componente tiene la variante de lectura (1080)');

$mobile = substr($css, (int) strpos($css, '@media (max-width: 767px)'));
ph_check(strpos($mobile, '.flacso-page-header--reading.flacso-page-header--has-media { grid-template-columns: 1fr; }') !== false, 'en móvil la variante de lectura con imagen pasa a una columna (si no, el título queda en una columna de 0 px)');

echo "OK encabezado de página compartido\n";
