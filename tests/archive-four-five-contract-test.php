<?php
/** Contract: academic posters stay 4:5, with no image crop or scale. */
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
$seminars = file_get_contents($root . '/seminarios-listado.php');
$seminar_css = file_get_contents($root . '/assets/css/seminarios-listado-polish.css');

expect_archive_design(strpos($taxonomy, "'large',") !== false, 'archivo usa imagen sin recorte 4:5 generado');
expect_archive_design(strpos($taxonomy, "'flacso-oferta-4x5'") === false, 'archivo no solicita derivado recortado');
expect_archive_design(strpos($css, 'aspect-ratio: 4 / 5 !important;') !== false, 'ratio 4:5 obligatorio');
expect_archive_design(strpos($css, 'object-fit: contain !important;') !== false, 'fotografia completa visible');
expect_archive_design(strpos($css, 'grid-template-columns: minmax(0, 42%) minmax(0, 58%)') !== false, 'columna de foto mayor en movil');
expect_archive_design(strpos($css, '90px 0 0 #e4a629') !== false && strpos($css, '120px 0 0 #5083bf') !== false, 'cinco puntos en cabecera academica');
expect_archive_design(strpos($seminars, '<span></span><span></span><span></span><span></span><span></span>') !== false, 'cinco puntos en Seminarios');
expect_archive_design(strpos($seminar_css, 'span:nth-child(5)') !== false, 'color del quinto punto');
echo "Archivo académico: imágenes completas 4:5 y cinco puntos verificados.\n";
