<?php
// Contrato visual de la carta: una jerarquía editorial, sin numeración decorativa.
// Contrato visual de la carta: jerarquía editorial y ritmo de lectura.
$css = file_get_contents(__DIR__ . '/../assets/css/single-carta-editorial.css');
$checks = [
    'oculta la numeración de las secciones' => '/\.fc-major-heading__kicker\s*\{[^}]*display:\s*none;/s',
    'retira la regla decorativa de los títulos' => '/\.fc-major-heading__rule\s*\{[^}]*display:\s*none;/s',
    'limita el ancho de lectura de la propuesta' => '/\.fc-editorial-intro\s+\.fc-content-panel-body\s*\{[^}]*max-width:\s*72ch;/s',
    'presenta la información práctica como lista' => '/\.fc-info-row\s*\{[^}]*box-shadow:\s*none;/s',
    'presenta la numeración de las secciones como kicker circular' => '/\.fc-major-heading__kicker\s*\{[^}]*display:\s*grid;/s',
    'presenta la regla decorativa de los títulos' => '/\.fc-major-heading__rule\s*\{[^}]*display:\s*block;/s',
    'limita el ancho de lectura de la propuesta' => '/\.fc-editorial-intro\s+\.fc-content-panel-body\s*\{[^}]*max-width:\s*76ch;/s',
    'presenta la información práctica como filas en cuadrícula' => '/\.fc-info-row\s*,\s*[^}]*display:\s*grid;/s',
];
foreach ($checks as $description => $pattern) {
    if (preg_match($pattern, $css) !== 1) {
        fwrite(STDERR, "FAIL: {$description}\n");
        exit(1);
    }
}
echo "OK carta editorial rhythm\n";