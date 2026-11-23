<?php
/**
 * Equipo: buscador accesible (type=search, etiqueta visible, foco visible). Contacto: una sola introducción.
 * Ejecutable sin WordPress: php tests/equipo-contacto-contract-test.php
 */
$root = dirname(__DIR__);
function ec_check($ok, $d) { if (!$ok) { fwrite(STDERR, "FAIL: " . $d . "\n"); exit(1); } }
function ec_read($p) { return str_replace("\r\n", "\n", (string) file_get_contents($p)); }

$php = ec_read($root . '/archive-docente.php');
$css = ec_read($root . '/assets/css/archive-docente.css');
ec_check(preg_match('/<input[^>]*type="search"[^>]*id="archive-search"/', $php) === 1, 'el campo del Equipo es type=search');
ec_check(preg_match('/<label[^>]*for="archive-search"/', $php) === 1, 'el campo del Equipo tiene label for visible');
ec_check(preg_match('/\.search-box input\s*\{[^}]*outline:\s*none/', $css) !== 1, '.search-box input no anula el foco con outline: none');

$contacto = ec_read($root . '/page-contacto.php');
ec_check(preg_match("/fc_render_form\(array\([^)]*'mostrar_encabezado'\s*=>\s*false/", $contacto) === 1, 'page-contacto pasa mostrar_encabezado false al plugin');
echo "OK equipo y contacto\n";
