<?php
/** «Información extra» de la oferta en la carta: nombre, ubicación y compatibilidad. */

$root = dirname(__DIR__);

function carta_extra_assert(bool $condition, string $message): void {
    if (!$condition) {
        fwrite(STDERR, "FAIL: {$message}\n");
        exit(1);
    }
}

$model = (string) file_get_contents($root . '/inc/academic-public-model.php');
$view = (string) file_get_contents($root . '/inc/carta-view-model.php');
$content = (string) file_get_contents($root . '/template-parts/carta/contenido.php');

carta_extra_assert(strpos($model, '"carta_info_extra_html"') !== false, 'el modelo público usa carta_info_extra_html');
carta_extra_assert(strpos($model, 'get_post_meta($offer_id, "carta_presentacion_html", true)') !== false, 'se lee el nombre anterior como respaldo');
carta_extra_assert(strpos($view, "'info_extra' => trim((string) (\$academic['carta_info_extra_html'] ?? ''))") !== false, 'el modelo de vista entrega info_extra');
carta_extra_assert(strpos($content, "esc_attr_e('Información extra'") !== false && strpos($content, 'fc-extra-info') !== false, 'la plantilla lo presenta como «Información extra»');
carta_extra_assert(strpos($content, 'child_content') === false && strpos($content, 'carta_presentacion') === false, 'no quedan nombres de «presentación» en la plantilla');
carta_extra_assert(strpos((string) file_get_contents($root . '/assets/css/single-carta-editorial.css'), 'fc-child-content') === false, 'el CSS usa la clase nueva');

echo "OK carta info extra\n";
