<?php
/**
 * Contrato: la página pública muestra las orientaciones como bloques debajo del hero,
 * y la modalidad «virtual con encuentros sincrónicos» tiene etiqueta propia.
 */
$root = dirname(__DIR__);
$page = file_get_contents($root . '/oferta-academica/page.php');
$partial = file_get_contents($root . '/template-parts/oferta/orientaciones.php');
$model = file_get_contents($root . '/inc/academic-public-model.php');
$css = file_get_contents($root . '/oferta-academica/style.css');
function verify_orient($condition, $message) {
    if (!$condition) { fwrite(STDERR, "FAIL: {$message}\n"); exit(1); }
}

$hero = strpos($page, 'class="flacso-academic-hero"');
$heroEnd = strpos($page, '</section>', $hero);
$llamada = strpos($page, "'template-parts/oferta/orientaciones'");
$confianza = strpos($page, 'flacso-academic-trust-strip');
verify_orient($hero !== false && $llamada !== false, 'la página invoca el parcial de orientaciones');
verify_orient($llamada > $heroEnd, 'las orientaciones van después del hero');
verify_orient($llamada < $confianza, 'las orientaciones van antes de la franja de reconocimientos');
verify_orient(strpos($page, "\$academic['orientaciones']") !== false, 'usa el meta orientaciones del modelo');

verify_orient(strpos($partial, "\$items === []") !== false && strpos($partial, 'return;') !== false, 'sin orientaciones no dibuja nada');
verify_orient(strpos($partial, 'esc_html($texto)') !== false, 'escapa cada orientación');
verify_orient(strpos($partial, 'is_scalar') !== false, 'ignora valores que no son texto');
verify_orient(strpos($css, '.flacso-academic-orientations__item') !== false, 'tiene estilos de bloque');

// Comportamiento del formateador de modalidad, sin cargar WordPress.
if (!function_exists('__')) { function __($t) { return $t; } }
if (!function_exists('flacso_theme_format_modality')) {
    preg_match('/function flacso_theme_format_modality\(.*?\n}\n/s', $model, $m);
    verify_orient(!empty($m[0]), 'existe el formateador de modalidad');
    eval($m[0]);
}
verify_orient(flacso_theme_format_modality('virtual-sincronica') === 'Virtual con encuentros sincrónicos', 'etiqueta de virtual con encuentros sincrónicos');
verify_orient(flacso_theme_format_modality('virtual') === 'Virtual', 'virtual sigue igual');
verify_orient(flacso_theme_format_modality('semipresencial') === 'Semipresencial', 'semipresencial sigue igual');
verify_orient(flacso_theme_format_modality('hibrida') === 'Híbrida', 'híbrida con tilde');
verify_orient(flacso_theme_format_modality('') === '', 'vacío sigue vacío');
echo "OK academic-orientations-public-test\n";
