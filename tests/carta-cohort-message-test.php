<?php
/** Mensaje de la cohorte en la carta: llega desde la cohorte vigente y se muestra justo después de la sección 01. */

define('ABSPATH', __DIR__ . '/../');

function esc_attr_e($s, $d = '') { echo htmlspecialchars((string) $s); }
function wp_strip_all_tags($s) { return trim(strip_tags((string) $s)); }
function wp_kses_post($s) { return (string) $s; }
function wpautop($s) { return (string) $s; }

function cohort_message_assert(bool $condition, string $message): void {
    if (!$condition) {
        fwrite(STDERR, "FAIL: {$message}\n");
        exit(1);
    }
}

$root = dirname(__DIR__);

$render = static function (string $message) use ($root): string {
    $args = ['carta' => ['mensaje_cohorte' => $message]];
    ob_start();
    include $root . '/template-parts/carta/mensaje.php';
    return trim((string) ob_get_clean());
};

cohort_message_assert($render('') === '', 'sin mensaje no se muestra nada');
cohort_message_assert($render('<p> </p>') === '', 'un mensaje sin texto visible no se muestra');
$html = $render('<p>Becas <strong>abiertas</strong></p>');
cohort_message_assert(strpos($html, 'fc-cohort-message') !== false && strpos($html, '<strong>abiertas</strong>') !== false, 'el mensaje se muestra con su formato');

$content = (string) file_get_contents($root . '/template-parts/carta/contenido.php');
$overview = strpos($content, 'fc-major-section--overview');
$message = strpos($content, 'carta/mensaje', (int) $overview);
$experience = strpos($content, 'fc-major-section--experience');
cohort_message_assert($overview !== false && $message > $overview && $message < $experience, 'el mensaje va justo después de la sección 01 y antes de la 02');
cohort_message_assert(substr_count($content, 'carta/mensaje') === 2, 'sin sección 01 el mensaje abre el contenido');

$model = (string) file_get_contents($root . '/inc/academic-public-model.php');
cohort_message_assert(strpos($model, '"carta_mensaje_html"     => (string) ($current_cohort["carta_mensaje_html"] ?? "")') !== false, 'la cohorte vigente aporta el mensaje a la instancia');
$view = (string) file_get_contents($root . '/inc/carta-view-model.php');
cohort_message_assert(strpos($view, "'mensaje_cohorte' => trim((string) (\$instance['carta_mensaje_html'] ?? ''))") !== false, 'el modelo de vista lo entrega a las plantillas');

echo "OK carta cohort message\n";
