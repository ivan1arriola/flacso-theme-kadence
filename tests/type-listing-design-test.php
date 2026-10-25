<?php
/** Regression: every academic type listing uses the /formacion card language. */
declare(strict_types=1);

$root = dirname(__DIR__);
$taxonomy = (string) file_get_contents($root . '/taxonomy-tipo-oferta-academica.php');
$seminars = (string) file_get_contents($root . '/seminarios-listado.php');
$archive = (string) file_get_contents($root . '/archive-seminario.php');

function check_type_listing(bool $ok, string $message): void {
    if (!$ok) {
        fwrite(STDERR, "FAIL: {$message}\n");
        exit(1);
    }
}

foreach (['flacso-formacion-card', 'flacso-formacion-card__media', 'flacso-formacion-card__title', 'flacso-formacion-card__footer'] as $class) {
    check_type_listing(strpos($taxonomy, $class) !== false, "ofertas: {$class}");
    check_type_listing(strpos($seminars, $class) !== false, "seminarios: {$class}");
}
check_type_listing(strpos($taxonomy, 'assets/css/formacion-page.css') !== false, 'ofertas cargan estilo compartido');
check_type_listing(strpos($archive, 'assets/css/formacion-page.css') !== false, 'seminarios cargan estilo compartido');
check_type_listing(strpos($seminars, 'data-seminar-card') !== false, 'búsqueda de seminarios conservada');
check_type_listing(strpos($seminars, 'data-month-group') !== false, 'filtros por mes conservados');

echo "Listados por tipo: tarjetas del catálogo compartidas.\n";
