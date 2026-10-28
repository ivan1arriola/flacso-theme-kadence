<?php

$root = dirname(__DIR__);
require_once __DIR__ . '/helpers/carta-source.php';
$template = flacso_carta_template_source($root);
$styles = file_get_contents($root . '/assets/css/single-carta-editorial.css');

if ($template === false || $styles === false) {
    fwrite(STDERR, "FAIL: no se pudieron leer los archivos de la carta\n");
    exit(1);
}

$audienceStart = strpos($template, '<aside class="fc-audience-panel">');
$audienceEnd = $audienceStart !== false ? strpos($template, '</aside>', $audienceStart) : false;

if ($audienceStart === false || $audienceEnd === false) {
    fwrite(STDERR, "FAIL: no se encontró el bloque ¿A quiénes está dirigido?\n");
    exit(1);
}

$audienceBlock = substr($template, $audienceStart, $audienceEnd - $audienceStart);

if (strpos($audienceBlock, '<img') !== false || strpos($audienceBlock, 'fc-audience-panel__image') !== false) {
    fwrite(STDERR, "FAIL: la imagen ilustrativa debe aparecer solamente en el hero\n");
    exit(1);
}

if (strpos($styles, '.fc-audience-panel__copy') === false
    || strpos($styles, 'grid-template-columns: 42px minmax(0,1fr);') === false) {
    fwrite(STDERR, "FAIL: el bloque de audiencia debe conservar su composición editorial\n");
    exit(1);
}

echo "OK carta audience layout contract\n";
