<?php
/**
 * Contrato: la página pública muestra las menciones como bloques debajo del hero,
 * y la modalidad «virtual con encuentros sincrónicos» tiene etiqueta propia.
 */
$root = dirname(__DIR__);
$page = file_get_contents($root . '/oferta-academica/page.php');
$partial = file_get_contents($root . '/template-parts/oferta/menciones.php');
$model = file_get_contents($root . '/inc/academic-public-model.php');
$css = file_get_contents($root . '/oferta-academica/style.css');
function verify_menc($condition, $message) {
    if (!$condition) { fwrite(STDERR, "FAIL: {$message}\n"); exit(1); }
}

$hero = strpos($page, 'class="flacso-academic-hero"');
$heroEnd = strpos($page, '</section>', $hero);
$llamada = strpos($page, "'template-parts/oferta/menciones'");
$confianza = strpos($page, 'flacso-academic-trust-strip');
verify_menc($hero !== false && $llamada !== false, 'la página invoca el parcial de menciones');
verify_menc($llamada > $heroEnd, 'las menciones van después del hero');
verify_menc($llamada < $confianza, 'las menciones van antes de la franja de reconocimientos');
verify_menc(strpos($page, "\$academic['menciones']") !== false, 'usa el meta menciones del modelo');

verify_menc(strpos($partial, "\$items === []") !== false && strpos($partial, 'return;') !== false, 'sin menciones no dibuja nada');
verify_menc(strpos($partial, 'esc_html($texto)') !== false, 'escapa cada mención');
verify_menc(strpos($partial, 'is_scalar') !== false, 'ignora valores que no son texto');
verify_menc(strpos($css, '.flacso-academic-menciones__item') !== false, 'tiene estilos de bloque');

// Comportamiento del formateador de modalidad, sin cargar WordPress.
if (!function_exists('__')) { function __($t) { return $t; } }
if (!function_exists('flacso_theme_format_modality')) {
    preg_match('/function flacso_theme_format_modality\(.*?\n}\n/s', $model, $m);
    verify_menc(!empty($m[0]), 'existe el formateador de modalidad');
    eval($m[0]);
}
verify_menc(flacso_theme_format_modality('virtual-sincronica') === 'Virtual con encuentros sincrónicos', 'etiqueta de virtual con encuentros sincrónicos');
verify_menc(flacso_theme_format_modality('virtual') === 'Virtual', 'virtual sigue igual');
verify_menc(flacso_theme_format_modality('semipresencial') === 'Semipresencial', 'semipresencial sigue igual');
verify_menc(flacso_theme_format_modality('hibrida') === 'Híbrida', 'híbrida con tilde');
verify_menc(flacso_theme_format_modality('') === '', 'vacío sigue vacío');
echo "OK academic-menciones-public-test\n";
