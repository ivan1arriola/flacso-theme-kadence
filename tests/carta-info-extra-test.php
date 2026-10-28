<?php
/** «Información extra para Carta»: llega desde la cohorte vigente y se muestra justo después de la sección 01. */

define('ABSPATH', __DIR__ . '/../');

function esc_attr_e($s, $d = '') { echo htmlspecialchars((string) $s); }
function wp_strip_all_tags($s) { return trim(strip_tags((string) $s)); }
function wp_kses_post($s) { return (string) $s; }
function wpautop($s) { return (string) $s; }

function carta_extra_assert(bool $condition, string $message): void {
    if (!$condition) {
        fwrite(STDERR, "FAIL: {$message}\n");
        exit(1);
    }
}

$root = dirname(__DIR__);

$render = static function (string $text) use ($root): string {
    $args = ['carta' => ['info_extra' => $text]];
    ob_start();
    include $root . '/template-parts/carta/info-extra.php';
    return trim((string) ob_get_clean());
};

carta_extra_assert($render('') === '', 'sin información extra no se muestra nada');
carta_extra_assert($render('<p> </p>') === '', 'un texto sin contenido visible no se muestra');
$html = $render('<h3>Becas</h3><p>Hasta <strong>80%</strong></p>');
carta_extra_assert(strpos($html, 'fc-extra-info') !== false && strpos($html, '<strong>80%</strong>') !== false, 'se muestra con su formato');

$content = (string) file_get_contents($root . '/template-parts/carta/contenido.php');
$overview = strpos($content, 'fc-major-section--overview');
$part = strpos($content, 'carta/info-extra', (int) $overview);
$experience = strpos($content, 'fc-major-section--experience');
carta_extra_assert($overview !== false && $part > $overview && $part < $experience, 'va justo después de la sección 01 y antes de la 02');
carta_extra_assert(substr_count($content, 'carta/info-extra') === 2, 'sin sección 01 abre el contenido');
carta_extra_assert(strpos($content, 'child_content') === false && strpos($content, 'carta_presentacion') === false, 'no quedan nombres antiguos en la plantilla');

$model = (string) file_get_contents($root . '/inc/academic-public-model.php');
carta_extra_assert(strpos($model, '"carta_info_extra_html"  => (string) ($current_cohort["carta_info_extra_html"] ?? "")') !== false, 'la cohorte vigente aporta la información extra a la instancia');
carta_extra_assert(strpos($model, 'carta_presentacion_html') === false, 'la oferta ya no aporta este campo');
$view = (string) file_get_contents($root . '/inc/carta-view-model.php');
carta_extra_assert(strpos($view, "'info_extra' => trim((string) (\$instance['carta_info_extra_html'] ?? ''))") !== false, 'el modelo de vista la entrega a las plantillas');
carta_extra_assert(strpos((string) file_get_contents($root . '/assets/css/single-carta-editorial.css'), 'fc-extra-info') === false, 'no quedan estilos del bloque anterior que choquen');

echo "OK carta info extra\n";
