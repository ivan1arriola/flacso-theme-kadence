<?php
/**
 * Contrato: la tarjeta «Información de cursada» usa bloques sueltos, sin estilos en línea que
 * la CSS con !important pisa, y con la modalidad larga ocupando dos columnas.
 */
$root = dirname(__DIR__);
$page = file_get_contents($root . '/oferta-academica/page.php');
$css = file_get_contents($root . '/oferta-academica/style.css');
function verify_cohort($condition, $message) {
    if (!$condition) { fwrite(STDERR, "FAIL: {$message}\n"); exit(1); }
}
$ini = strpos($page, 'class="cohort__details"');
$fin = strpos($page, 'cohort__note', $ini);
$tarjeta = substr($page, $ini, $fin - $ini);
verify_cohort($ini !== false && $fin !== false, 'existe la tarjeta de cursada');
verify_cohort(strpos($tarjeta, 'style=') === false, 'sin estilos en línea en los datos de cursada');
verify_cohort(strpos($tarjeta, 'cohort__row--wide') !== false, 'la modalidad larga ocupa dos columnas');
verify_cohort(strpos($tarjeta, 'cohort__row--full') !== false && strpos($tarjeta, 'cohort__value--prose') !== false, 'la descripción de cursada es un bloque de ancho completo');
verify_cohort(strpos($page, 'class="cohort__note-warning"') !== false, 'aviso de presencialidad con clase y sin estilo en línea');

// La última regla de .cohort__row no puede volver a la sangría condicionada por :not(:first-child).
verify_cohort(strpos($css, '.cohort__row:not(:first-child)') === false, 'sin bordes izquierdos condicionados por posición');
verify_cohort(strpos($css, '.cohort__row--wide') !== false && strpos($css, 'grid-column: span 2') !== false, 'estilos de modalidad ancha');
verify_cohort(strpos($css, '.cohort__note-warning') !== false, 'estilos del aviso de presencialidad');
echo "OK cohort-card-layout-test\n";
