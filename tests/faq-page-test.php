<?php
/** Contract test for the public FAQ template. */

$root = dirname(__DIR__);
$template = file_get_contents($root . '/page-preguntas-frecuentes.php');
$functions = file_get_contents($root . '/functions.php');
$css = file_get_contents($root . '/assets/css/page-faq.css');
$js = file_get_contents($root . '/assets/js/page-faq.js');

$checks = [
    [$template, "FLACSO_Preguntas_Frecuentes::render", 'La plantilla debe delegar el contenido al plugin.'],
    [$template, "'nivel_titulo' => 'h1'", 'La página debe tener un encabezado principal.'],
    [$functions, "'page-preguntas-frecuentes.php'       => 'page-faq'", 'Los assets FAQ deben cargarse sólo en su plantilla.'],
    [$css, '.flacso-faq__grid', 'Falta la cuadrícula visual de preguntas.'],
    [$js, 'IntersectionObserver', 'Falta la mejora progresiva de animación.'],
];

foreach ($checks as $check) {
    if (strpos($check[0], $check[1]) === false) {
        fwrite(STDERR, $check[2] . PHP_EOL);
        exit(1);
    }
}

echo "Tema FAQ: plantilla, assets y contrato con el plugin correctos.\n";
