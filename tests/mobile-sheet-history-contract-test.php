<?php
/**
 * El panel "Más información" de la ficha de oferta en móvil se comporta como una pantalla: el gesto "atrás" del teléfono
 * debe cerrarlo y no sacar a la persona de la ficha. Al abrir se agrega una entrada al historial; "atrás" la consume y
 * cierra el panel; cerrarlo por la ✕, la sombra o el deslizamiento la retira para no acumular entradas.
 * Verificación de comportamiento real: panel-atras.mjs (Playwright, teléfono emulado).
 * Ejecutable sin WordPress: php tests/mobile-sheet-history-contract-test.php
 */
$root = dirname(__DIR__);

function sheet_check($ok, $description) {
    if (!$ok) { fwrite(STDERR, "FAIL: " . $description . "\n"); exit(1); }
}

$js = str_replace("\r\n", "\n", (string) file_get_contents($root . '/oferta-academica/script.js'));

sheet_check(strpos($js, 'history.pushState') !== false, 'al abrir el panel se agrega una entrada al historial');
sheet_check(strpos($js, 'flacsoInfoSheet') !== false, 'la entrada se marca para reconocerla');
sheet_check(strpos($js, "addEventListener('popstate'") !== false, 'el gesto atrás (popstate) cierra el panel');
sheet_check(strpos($js, 'history.back()') !== false, 'cerrar el panel por otra vía retira la entrada del historial');
sheet_check(strpos($js, 'history.replaceState') !== false, 'al recargar o al cerrar por un ancla se limpia la marca sin retroceder');

// Las vías de cierre existentes siguen presentes.
sheet_check(strpos($js, "closeButton.addEventListener('click'") !== false, 'la ✕ sigue cerrando el panel');
sheet_check(strpos($js, "overlay.addEventListener('click'") !== false, 'la sombra sigue cerrando el panel');

echo "OK atrás cierra el panel de información en móvil\n";
