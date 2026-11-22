<?php
/**
 * El submenú de escritorio se abre por :hover y :focus-within; debe poder descartarse con Escape (WCAG 1.4.13),
 * devolver el foco al enlace padre y reabrirse con la flecha hacia abajo o al salir y volver a entrar.
 * Verificación de comportamiento real: prueba de teclado con Playwright a 1280 px (dev/entorno-local).
 * Ejecutable sin WordPress: php tests/desktop-submenu-escape-contract-test.php
 */
$root = dirname(__DIR__);

function submenu_check($ok, $description) {
    if (!$ok) { fwrite(STDERR, "FAIL: " . $description . "\n"); exit(1); }
}

$js  = str_replace("\r\n", "\n", (string) file_get_contents($root . '/assets/js/site-chrome.js'));
$css = str_replace("\r\n", "\n", (string) file_get_contents($root . '/assets/css/site-chrome.css'));

submenu_check(strpos($js, 'is-submenu-dismissed') !== false, 'el script marca el submenú descartado con is-submenu-dismissed');
submenu_check(strpos($js, 'dismissDesktopSubmenu') !== false, 'existe la función que descarta el submenú de escritorio');
submenu_check(preg_match("/event\.key\s*===\s*'ArrowDown'/", $js) === 1, 'ArrowDown reabre el submenú');
submenu_check(strpos($js, "'mouseleave'") !== false || strpos($js, "'pointerleave'") !== false, 'al salir el puntero se restablece el submenú');
submenu_check(strpos($js, "'mouseenter'") !== false || strpos($js, "'pointerenter'") !== false, 'al entrar el puntero se restablece el submenú');
submenu_check(strpos($js, "'focusout'") !== false, 'al salir el foco se restablece el submenú');

// La regla de descarte debe ganar a :hover y :focus-within y vivir solo en escritorio.
submenu_check(preg_match('/@media\s*\(min-width:\s*1081px\)\s*\{[^@]*?is-submenu-dismissed[^@]*?visibility:\s*hidden/s', $css) === 1, 'el CSS oculta el submenú descartado, solo en escritorio (min-width: 1081px)');
submenu_check(preg_match('/is-submenu-dismissed\s*>\s*\.sub-menu/', $css) === 1, 'la regla apunta al submenú del ítem descartado');

echo "OK submenú de escritorio descartable con Escape\n";
