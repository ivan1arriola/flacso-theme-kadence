<?php
/** Contrato de titulos intermedios vinculados de la carta. */
if (!defined('ABSPATH')) define('ABSPATH', dirname(__DIR__) . '/');
$GLOBALS['offers'] = [
    10 => (object) ['ID' => 10, 'post_type' => 'oferta-academica', 'post_status' => 'publish', 'post_title' => 'Maestría en Educación'],
    11 => (object) ['ID' => 11, 'post_type' => 'oferta-academica', 'post_status' => 'publish', 'post_title' => 'Especialización en Educación'],
    12 => (object) ['ID' => 12, 'post_type' => 'oferta-academica', 'post_status' => 'publish', 'post_title' => 'Diplomado en Educación'],
    13 => (object) ['ID' => 13, 'post_type' => 'oferta-academica', 'post_status' => 'publish', 'post_title' => 'Diploma en Educación'],
    14 => (object) ['ID' => 14, 'post_type' => 'oferta-academica', 'post_status' => 'publish', 'post_title' => 'Maestría en Género'],
    15 => (object) ['ID' => 15, 'post_type' => 'oferta-academica', 'post_status' => 'draft', 'post_title' => 'Diploma borrador'],
    20 => (object) ['ID' => 20, 'post_type' => 'seminario', 'post_status' => 'publish', 'post_title' => 'Seminario'],
];
$GLOBALS['offerTypes'] = [10=>'maestria', 11=>'especializacion', 12=>'diplomado', 13=>'diploma', 14=>'maestria', 15=>'diploma'];
$GLOBALS['offersMeta'] = [
    11 => ['menciones'=>['Investigación','Gestión']],
    12 => ['menciones'=>['Educación']],
];
function absint($value) { return abs((int)$value); }
function get_post($id) { return $GLOBALS['offers'][$id] ?? null; }
function get_post_meta($id, $key, $single=true) { return $GLOBALS['offersMeta'][$id][$key] ?? []; }
function get_permalink($id) { return 'https://flacso.edu.uy/formacion/' . $id . '/'; }
function wp_get_object_terms($id, $taxonomy) {
    $slug = $GLOBALS['offerTypes'][$id] ?? '';
    return $slug !== '' ? [(object)['slug' => $slug]] : [];
}
function is_wp_error($obj) { return false; }
function sanitize_key($text) { return strtolower(trim((string)$text)); }
require_once dirname(__DIR__) . '/inc/academic-public-model.php';
function check_intermediate(bool $condition, string $reason): void {
    if (!$condition) { fwrite(STDERR, "FAIL: {$reason}\n"); exit(1); }
}
$raw = [
    ['oferta_id'=>11, 'menciones'=>['Gestión','No existe','Gestión']],
    ['oferta_id'=>12, 'menciones'=>[]],
    ['oferta_id'=>13],
    ['oferta_id'=>14],
    ['oferta_id'=>10],
    ['oferta_id'=>11],
    ['oferta_id'=>15],
    ['oferta_id'=>20],
];
$result = flacso_theme_resolve_intermediate_titles(10, $raw);
check_intermediate(count($result) === 3, 'unicamente titulos publicados de menor nivel');
check_intermediate($result[0] === [
    'oferta_id'=>11, 'titulo'=>'Especialización en Educación',
    'url'=>'https://flacso.edu.uy/formacion/11/', 'menciones'=>['Gestión'],
], 'titulo y URL de la oferta, con menciones validas');
check_intermediate($result[1]['menciones'] === [], 'titulo sin menciones es valido');
check_intermediate($result[2]['titulo'] === 'Diploma en Educación', 'titulo oficial');
check_intermediate(flacso_theme_resolve_intermediate_titles(13, $raw) === [], 'diploma no admite titulos intermedios');
check_intermediate(flacso_theme_resolve_intermediate_titles(10, ['texto libre']) === [], 'texto libre no aceptado');

$template = file_get_contents(dirname(__DIR__) . '/template-parts/oferta/carta.php');
$stylesheet = file_get_contents(dirname(__DIR__) . '/assets/css/single-carta-editorial.css');
check_intermediate(strpos($template, "\$titulo_intermedio['titulo']") !== false, 'el titulo se obtiene de la oferta');
check_intermediate(strpos($template, "\$titulo_intermedio['url']") !== false, 'se enlaza a su pagina publica');
check_intermediate(strpos($template, "\$titulo_intermedio['menciones']") !== false, 'menciones opcionales en carta');
check_intermediate(strpos($stylesheet, 'fc-intermediate-mentions') !== false, 'estilos de menciones');
echo "OK carta intermediate titles and optional mentions\n";

