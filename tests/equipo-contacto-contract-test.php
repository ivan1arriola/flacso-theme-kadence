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

ec_check(preg_match('/\.search-box:focus-within\s*\{[^}]*outline:\s*3px solid/', $css) === 1, 'la caja del buscador muestra un anillo al recibir foco (Kadence anula el outline del input)');

$contacto = ec_read($root . '/page-contacto.php');
ec_check(preg_match("/fc_render_form\(array\([^)]*'mostrar_encabezado'\s*=>\s*false/", $contacto) === 1, 'page-contacto pasa mostrar_encabezado false al plugin');
// L17 / PC12: correo y teléfono una sola vez y un solo título sobre el formulario.
ec_check(substr_count($contacto, 'mailto:<?php echo esc_attr($correo)') === 1, 'Contacto muestra el correo de Secretaría una sola vez');
ec_check(substr_count($contacto, 'tel:<?php echo esc_attr($telefono_tel)') === 1, 'Contacto muestra el teléfono una sola vez');
$form_card = (string) strstr((string) strstr($contacto, 'flacso-contact-form-card'), 'fc_render_form', true);
ec_check(substr_count($form_card, '<h2') === 1 && strpos($form_card, 'section-kicker') === false, 'el formulario de Contacto tiene un solo título');
echo "OK equipo y contacto\n";
