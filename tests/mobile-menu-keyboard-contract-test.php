<?php
/**
 * El menú móvil abierto debe llevar el foco adentro y mantenerlo ahí (Tab y Shift+Tab ciclan),
 * y Escape lo cierra devolviendo el foco al botón. El <nav> va antes del botón en el HTML, así
 * que sin esto Tab sale a la página de fondo, que está tapada por el cajón.
 * Verificación de comportamiento real: dev/entorno-local/teclado.mjs (plugin) con Playwright.
 * Ejecutable sin WordPress: php tests/mobile-menu-keyboard-contract-test.php
 */
$root = dirname(__DIR__);

function menu_kbd_check($ok, $description) {
    if (!$ok) { fwrite(STDERR, "FAIL: " . $description . "\n"); exit(1); }
}

$js = str_replace("\r\n", "\n", (string) file_get_contents($root . '/assets/js/site-chrome.js'));

menu_kbd_check(strpos($js, "event.key === 'Tab'") !== false || strpos($js, "event.key !== 'Tab'") !== false, 'el script atiende la tecla Tab para atrapar el foco en el menú abierto');
menu_kbd_check(strpos($js, 'event.shiftKey') !== false, 'el script contempla Shift+Tab (retroceso)');
menu_kbd_check(strpos($js, 'requestAnimationFrame') !== false, 'el foco se mueve al menú después de que el cajón sea visible (visibility: hidden bloquea focus())');
menu_kbd_check(preg_match('/menuClose\s*\)?\s*\.focus\(\)|menuClose\.focus\(\)|focusTarget/', $js) === 1, 'al abrir, el foco va al botón de cerrar del cajón');
menu_kbd_check(strpos($js, "event.key !== 'Escape'") !== false || strpos($js, "event.key === 'Escape'") !== false, 'Escape sigue cerrando el menú');
menu_kbd_check(strpos($js, 'closeMenu(true)') !== false, 'al cerrar con Escape el foco vuelve al botón de abrir');

echo "OK foco del menú móvil\n";
