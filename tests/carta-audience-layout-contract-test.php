<?php

$root = dirname(__DIR__);
$template = file_get_contents($root . '/template-parts/oferta/carta.php');
$styles = file_get_contents($root . '/assets/css/single-carta-editorial.css');

if ($template === false || $styles === false) {
    fwrite(STDERR, "FAIL: no se pudieron leer los archivos de la carta\n");
    exit(1);
}

if (strpos($template, 'is-image-only') === false) {
    fwrite(STDERR, "FAIL: la carta debe distinguir un panel de audiencia sin resumen\n");
    exit(1);
}

if (strpos($styles, '.fc-audience-panel.is-image-only .fc-audience-panel__image') === false
    || strpos($styles, 'height: 100%;') === false) {
    fwrite(STDERR, "FAIL: un panel de audiencia sólo con imagen debe ocupar toda la altura\n");
    exit(1);
}

echo "OK carta audience layout contract\n";
