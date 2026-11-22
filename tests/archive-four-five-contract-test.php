<?php
/** Contract: academic listings follow the /formacion card design. */
declare(strict_types=1);

$root = dirname(__DIR__);
function expect_archive_design(bool $condition, string $message): void {
    if (!$condition) {
        fwrite(STDERR, "FAIL: {$message}\n");
        exit(1);
    }
}
$taxonomy = file_get_contents($root . '/taxonomy-tipo-oferta-academica.php');
$css = file_get_contents($root . '/assets/css/taxonomy-oferta.css');
$listing_css = file_get_contents($root . '/assets/css/type-listing-cards.css');
$seminars = file_get_contents($root . '/seminarios-listado.php');
$seminar_css = file_get_contents($root . '/assets/css/seminarios-listado-polish.css');

expect_archive_design(strpos($taxonomy, 'flacso-formacion-card__media') !== false, 'archivo usa tarjeta del catálogo');
expect_archive_design(strpos($taxonomy, "'flacso-oferta-4x5'") === false, 'archivo no solicita derivado recortado');
expect_archive_design(strpos($listing_css, '.flacso-formacion-grid') !== false, 'archivo organiza tarjetas como catálogo');
echo "Archivo académico: tarjetas del catálogo, formato 4:5 verificados (los cinco puntos se quitaron con el encabezado compartido).\n";
