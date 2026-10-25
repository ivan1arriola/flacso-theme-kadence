<?php
/**
 * Contrato de consumo: articulación exclusivamente editorial para web/carta.
 */
$root = dirname(__DIR__);
$model = file_get_contents($root . '/inc/academic-public-model.php');
$default = file_get_contents($root . '/oferta-academica/page.php');
$carta = file_get_contents($root . '/template-parts/oferta/carta.php');
$partial = file_get_contents($root . '/template-parts/oferta/articulaciones.php');
$assets = file_get_contents($root . '/inc/assets.php');
$styles = file_get_contents($root . '/assets/css/academic-articulations.css');
function verify_art_view($condition, $message) {
    if (!$condition) { fwrite(STDERR, "FAIL: {$message}\n"); exit(1); }
}
verify_art_view(strpos($model, "FLACSO_Academic_Articulations::for_offer(\$offer_id)") !== false, 'modelo usa fuente canónica del plugin');
verify_art_view(strpos($model, "class_exists('FLACSO_Academic_Articulations')") !== false, 'página funciona si el plugin está en despliegue anterior');
verify_art_view(strpos($default, "'template-parts/oferta/articulaciones'") !== false, 'página pública muestra articulación');
verify_art_view(strpos($carta, "'template-parts/oferta/articulaciones'") !== false, 'carta muestra articulación');
verify_art_view(strpos($partial, "['oferta_titulo']") !== false, 'títulos reales del catálogo');
verify_art_view(strpos($partial, "['calendario_url']") !== false, 'un calendario por etapa');
verify_art_view(strpos($partial, "['mencion']") !== false, 'menciones habilitantes');
verify_art_view(strpos($partial, 'Requisitos de continuidad') !== false, 'explica requisitos de aprobación');
verify_art_view(strpos($partial, "['introduccion']") !== false, 'texto de ingreso por oferta');
verify_art_view(strpos($partial, "['continuidad']") !== false, 'condiciones de contratación por oferta');
verify_art_view(strpos($partial, 'wp_kses_post') !== false, 'presentación HTML sanitizada');
verify_art_view(strpos($partial, 'esc_url') !== false && strpos($partial, 'esc_html') !== false, 'escape de enlaces y contenido');
verify_art_view(strpos($assets, "is_singular('oferta-academica')") !== false, 'CSS exclusivamente en Ofertas');
verify_art_view(strpos($styles, '@media(max-width:782px)') !== false, 'responsive móvil');
verify_art_view(strpos($partial, 'flacso_articulacion_nonce') === false, 'sin gestión administrativa en tema');
echo "OK articulation carta and public page contract\n";
