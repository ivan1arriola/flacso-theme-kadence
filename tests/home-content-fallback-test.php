<?php
// Sin la clase Flacso_Home_Content (plugin desactivado) la portada debe seguir mostrando los textos aprobados.
$root = dirname(__DIR__);
if (!defined('ABSPATH')) { define('ABSPATH', __DIR__); }
function check($condition, $message) { if (!$condition) { fwrite(STDERR, "FAIL: $message\n"); exit(1); } }
function absint($value) { return abs((int) $value); }
function wp_strip_all_tags($value) { return strip_tags((string) $value); }
function remove_accents($value) { return $value; }
function home_url($path='') { return 'https://example.test' . $path; }
function esc_url_raw($value) { return (string) $value; }
function get_stylesheet_directory() { return dirname(__DIR__); }
function get_stylesheet_directory_uri() { return 'https://example.test/theme'; }
check(!class_exists('Flacso_Home_Content'), 'la clase del plugin no está definida en esta prueba');
require $root . '/inc/home.php';

$data = flacso_home_get_data();
check($data['hero']['title_lead'] === 'Formación que conecta,', 'título del hero por defecto sin plugin');
check($data['hero']['title_em'] === 'conocimiento que transforma.' && $data['hero']['eyebrow'] === 'PROGRAMA URUGUAY', 'énfasis y eyebrow por defecto');
check($data['hero']['intro'] !== '' && $data['hero']['cta']['label'] === 'Explorar oferta académica' && $data['hero']['cta']['url'] === 'https://example.test/formacion/', 'bajada y CTA por defecto');
check($data['campaign'] !== null && $data['campaign']['title'] === 'Tu próximo paso empieza acá.' && $data['campaign']['cta']['url'] !== '', 'campaña por defecto activa');
check(count($data['research']['links']) === 3 && $data['research']['title_lead'] === 'Pensar la región.' && $data['research']['link']['url'] !== '', 'investigación por defecto con tres enlaces');
check($data['research']['links'][1]['url'] === 'https://www.archivoiinflacso.uy/', 'enlace externo intacto');
check($data['research']['link']['label'] === 'Explorar investigaciones', 'etiqueta por defecto del enlace de investigación');
echo "Portada: respaldo sin plugin correcto.\n";
