<?php
/**
 * Código fuente completo de la carta: plantilla, parciales por sección y
 * modelo de vista. Los tests de contrato lo leen como un único texto.
 */

function flacso_carta_template_source(string $root): string {
    $files = array_merge(
        [$root . '/template-parts/oferta/carta.php', $root . '/inc/carta-view-model.php'],
        glob($root . '/template-parts/carta/*.php') ?: []
    );
    sort($files);

    return implode("\n", array_map(static function (string $file): string {
        return (string) file_get_contents($file);
    }, $files));
}
