<?php
/**
 * Tarjeta académica compartida (componente C4): Formación (ofertas y seminarios) y los listados por tipo usan la misma
 * anatomía `flacso-formacion-card`. Ejecutable sin WordPress: php tests/academic-card-component-test.php
 */
$root = dirname(__DIR__);
if (!defined('ABSPATH')) { define('ABSPATH', __DIR__ . '/'); }

function ac_check($ok, $description) {
    if (!$ok) { fwrite(STDERR, "FAIL: " . $description . "\n"); exit(1); }
}

function esc_html($v) { return htmlspecialchars((string) $v, ENT_QUOTES); }
function esc_attr($v) { return htmlspecialchars((string) $v, ENT_QUOTES); }
function esc_url($v) { return (string) $v; }
function __($t) { return $t; }
function esc_html_e($t) { echo htmlspecialchars((string) $t, ENT_QUOTES); }
function flacso_child_card_image_html(int $id, string $url, string $alt, string $sizes = '', array $extra = array()): string {
    return '<img data-id="' . $id . '" src="' . $url . '" alt="' . htmlspecialchars($alt, ENT_QUOTES) . '" sizes="' . $sizes . '" class="' . ($extra['class'] ?? '') . '">';
}

$partial = $root . '/template-parts/components/academic-card.php';
ac_check(is_readable($partial), 'existe template-parts/components/academic-card.php');

function render_card(string $partial, array $args): string {
    ob_start();
    include $partial;
    return (string) ob_get_clean();
}

$full = render_card($partial, [
    'title'       => 'Maestría "Género" & más',
    'url'         => 'https://x/maestria',
    'badge'       => 'Maestrías',
    'description' => 'Presentación breve.',
    'image_id'    => 5,
    'image_url'   => 'https://x/a.jpg',
    'image_sizes' => '(max-width: 600px) 40vw, 400px',
    'accent'      => '#0a3470',
    'is_open'     => true,
    'meta'        => [['icon' => 'bi-calendar3', 'text' => '3 mar'], ['icon' => 'bi-clock', 'text' => '120 hs.', 'datetime' => '2026-03-03']],
    'link_attrs'  => ['aria-label' => 'Ver X', 'data-offer-title' => 'T"1'],
]);
ac_check(strpos($full, '<a href="https://x/maestria"') === 0 || strpos($full, '<a href="https://x/maestria"') !== false, 'enlace con la URL');
ac_check(strpos($full, 'class="flacso-formacion-card flacso-formacion-card--accented"') !== false, 'con acento: clase --accented');
ac_check(strpos($full, 'style="--offer-accent: #0a3470;"') !== false, 'con acento: --offer-accent en línea');
ac_check(strpos($full, 'Maestría &quot;Género&quot; &amp; más') !== false && strpos($full, 'Maestría "Género"') === false, 'el título se escapa (comillas y &)');
ac_check(strpos($full, 'aria-label="Ver X"') !== false && strpos($full, 'data-offer-title="T&quot;1"') !== false, 'atributos extra del enlace, escapados');
ac_check(strpos($full, 'data-size') === false && strpos($full, 'sizes="(max-width: 600px) 40vw, 400px"') !== false && strpos($full, 'display:none;') !== false, 'con imagen: sizes y respaldo oculto');
ac_check(strpos($full, '<span class="flacso-formacion-card__badge">Maestrías</span>') !== false, 'etiqueta');
ac_check(strpos($full, 'flacso-formacion-card__excerpt') !== false && strpos($full, 'Presentación breve.') !== false, 'descripción');
ac_check(substr_count($full, 'flacso-formacion-card__meta-item') === 2 && strpos($full, '<time datetime="2026-03-03">120 hs.</time>') !== false, 'datos con icono y <time> cuando hay datetime');
ac_check(strpos($full, 'flacso-formacion-card__status is-open') !== false && strpos($full, 'Inscripciones abiertas') !== false, 'estado abierta');
ac_check(strpos($full, 'Conocer más') !== false, 'acción');

$min = render_card($partial, ['title' => 'Seminario', 'url' => '/s/', 'badge' => 'Seminario', 'is_open' => false]);
ac_check(strpos($min, '--accented') === false && strpos($min, 'style="--offer-accent') === false, 'sin acento: ni clase ni estilo');
ac_check(strpos($min, 'class="flacso-formacion-card"') !== false, 'sin acento: clase base exacta');
ac_check(strpos($min, '<img') === false && strpos($min, 'display:none;') === false && strpos($min, 'flacso-formacion-card__placeholder') !== false, 'sin imagen: solo el respaldo visible');
ac_check(strpos($min, 'flacso-formacion-card__excerpt') === false, 'sin descripción no hay párrafo');
ac_check(strpos($min, 'is-open') === false && strpos($min, 'Próxima apertura') !== false, 'estado próxima apertura');

// Las plantillas usan el componente y ya no repiten la anatomía.
foreach (['formacion-page.php', 'taxonomy-tipo-oferta-academica.php'] as $file) {
    $src = (string) file_get_contents($root . '/' . $file);
    ac_check(preg_match("/get_template_part\(\s*'template-parts\/components\/academic-card'/", $src) === 1, "$file usa el componente");
    ac_check(strpos($src, 'flacso-formacion-card__body') === false, "$file ya no repite el cuerpo de la tarjeta");
}

echo "OK tarjeta académica compartida\n";
