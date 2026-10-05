<?php

$root = dirname(__DIR__);
$template = file_get_contents($root . '/template-parts/oferta/carta.php');
$styles = file_get_contents($root . '/assets/css/single-carta-editorial.css');

if ($template === false || $styles === false) {
    fwrite(STDERR, "FAIL: no se pudieron leer los archivos de la carta\n");
    exit(1);
}

$objectiveStart = strpos($template, 'fc-content-panel fc-content-panel--objective');
$objectiveEnd = $objectiveStart !== false ? strpos($template, '</article>', $objectiveStart) : false;

if ($objectiveStart === false || $objectiveEnd === false) {
    fwrite(STDERR, "FAIL: no se encontró el panel de objetivo general\n");
    exit(1);
}

$objectiveBlock = substr($template, $objectiveStart, $objectiveEnd - $objectiveStart);

if (strpos($objectiveBlock, 'fc-content-panel__content') === false) {
    fwrite(STDERR, "FAIL: el objetivo debe agrupar título y contenido en la segunda columna del grid\n");
    exit(1);
}

if (strpos($styles, '.fc-content-panel--objective > .fc-content-panel__content') === false
    || strpos($styles, 'min-width: 0;') === false) {
    fwrite(STDERR, "FAIL: el contenido del objetivo debe poder ocupar correctamente la columna flexible\n");
    exit(1);
}

echo "OK carta objective layout contract\n";
