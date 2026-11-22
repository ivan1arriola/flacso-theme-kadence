<?php
/**
 * La barra fija inferior de la ficha de oferta en móvil ("Más Información" y "Preinscribirme") es la acción
 * principal de la página: debe separarse del contenido que pasa por debajo y tener botones grandes y legibles.
 * Verificación de comportamiento real: barra-acciones.mjs (Playwright, 412 y 360 px).
 * Ejecutable sin WordPress: php tests/mobile-actions-bar-contract-test.php
 */
$root = dirname(__DIR__);

function bar_check($ok, $description) {
    if (!$ok) { fwrite(STDERR, "FAIL: " . $description . "\n"); exit(1); }
}

$css = str_replace("\r\n", "\n", (string) file_get_contents($root . '/oferta-academica/style.css'));

function bar_rule(string $css, string $selector): string {
    return preg_match('/' . preg_quote($selector, '/') . '\s*\{([^}]*)\}/', $css, $m) ? $m[1] : '';
}

$bar = bar_rule($css, '.flacso-oferta-responsive.flacso-mobile-sheet-ready .flacso-mobile-actions');
bar_check($bar !== '', 'existe la regla de la barra fija');
bar_check(preg_match('/border-top:\s*[3-9]px solid var\(--oa-brand\)/', $bar) === 1, 'el borde superior es grueso y usa el color de la oferta');
bar_check(preg_match('/background:\s*#fff\s*;/', $bar) === 1, 'el fondo es sólido (sin transparencia)');
bar_check(strpos($bar, 'backdrop-filter') === false, 'no usa desenfoque de fondo (la barra no se confunde con el contenido)');
bar_check(preg_match('/box-shadow:\s*0 -(1[2-9]|[2-9]\d)px/', $bar) === 1, 'la sombra superior es marcada');
bar_check(preg_match('/align-items:\s*stretch/', $bar) === 1, 'los dos botones tienen el mismo alto');

$botones = bar_rule($css, ".flacso-oferta-responsive .flacso-mobile-actions__info,\n    .flacso-oferta-responsive .flacso-mobile-actions__register");
bar_check(preg_match('/min-height:\s*(5[6-9]|[6-9]\d)px/', $botones) === 1, 'los botones miden al menos 56px');
bar_check(preg_match('/font-size:\s*clamp\(1rem,/', $botones) === 1, 'el texto de los botones es de al menos 1rem (16px)');

// Ninguna regla para pantallas angostas debe volver a achicar el texto por debajo de 16px.
bar_check(!preg_match('/@media \(max-width: 385px\)\s*\{[^@]*?flacso-mobile-actions[^}]*font-size/s', $css), 'ningún bloque de pantallas angostas fija un tamaño de texto menor');

echo "OK barra fija inferior en móvil\n";
