<?php

$root = dirname(__DIR__);
require_once __DIR__ . '/helpers/carta-source.php';
$template = flacso_carta_template_source($root);
$styles = file_get_contents($root . '/assets/css/single-carta-editorial.css');

if ($template === false || $styles === false) {
    fwrite(STDERR, "FAIL: no se pudieron leer los archivos de la carta\n");
    exit(1);
}

$overviewStart = strpos($template, 'fc-content-panel fc-content-panel--primary fc-editorial-intro');
$overviewEnd = $overviewStart !== false ? strpos($template, '</article>', $overviewStart) : false;

if ($overviewStart === false || $overviewEnd === false) {
    fwrite(STDERR, "FAIL: no se encontró el panel de información general de la propuesta\n");
    exit(1);
}

$overviewBlock = substr($template, $overviewStart, $overviewEnd - $overviewStart);

if (strpos($overviewBlock, 'fc-content-panel-body') === false) {
    fwrite(STDERR, "FAIL: la propuesta debe estructurar su contenido en fc-content-panel-body\n");
    exit(1);
}

if (strpos($styles, '.fc-editorial-intro') === false
    || strpos($styles, '.fc-content-panel-body') === false) {
    fwrite(STDERR, "FAIL: los estilos deben declarar reglas para .fc-editorial-intro y su contenedor de lectura\n");
    exit(1);
}

echo "OK carta objective layout contract\n";