<?php
/**
 * La barra "Menú" del cajón móvil y la flecha de submenú son <button>: Kadence los pinta en :hover y :focus (fondo de
 * botón, texto blanco, sombra). Al abrir el menú el foco entra a la barra, así que se veía verde y pegada al borde,
 * y la flecha quedaba blanca sobre fondo claro. Verificación de comportamiento real: cajon-movil.mjs (Playwright).
 * Ejecutable sin WordPress: php tests/mobile-drawer-header-contract-test.php
 */
$root = dirname(__DIR__);

function drawer_check($ok, $description) {
    if (!$ok) { fwrite(STDERR, "FAIL: " . $description . "\n"); exit(1); }
}

$css = str_replace("\r\n", "\n", (string) file_get_contents($root . '/assets/css/site-chrome.css'));

// La regla global `body:not(.wp-admin) :where(button)` (min-height 44px hasta 782px) pesa (0,1,1): se necesitan dos clases.
drawer_check(preg_match('/\.flacso-navbar__navigation\s+\.flacso-navbar__drawer-close\s*\{[^}]*min-height:\s*72px/s', $css) === 1, 'la barra del cajón declara su alto con dos clases (gana a la regla global de botones)');
drawer_check(preg_match('/\.flacso-navbar__navigation\s*\{[^}]*padding:\s*env\(safe-area-inset-top/s', $css) === 1, 'el cajón respeta el área segura superior (env(safe-area-inset-top))');

// Estados que Kadence pisa: la barra conserva fondo, color y sin sombra.
drawer_check(preg_match('/\.flacso-navbar__drawer-close:hover,\s*\.flacso-navbar__drawer-close:focus,\s*\.flacso-navbar__drawer-close:active\s*\{[^}]*background:[^}]*color:[^}]*box-shadow:\s*none/s', $css) === 1, 'la barra conserva fondo, color y sin sombra en hover, focus y active');
drawer_check(preg_match('/\.flacso-navbar__drawer-close:focus-visible\s*\{[^}]*outline:\s*3px solid[^}]*outline-offset:\s*-\d+px/s', $css) === 1, 'la barra muestra el anillo de foco hacia adentro (sin recortarse en el borde)');
drawer_check(preg_match('/\.flacso-navbar__submenu-toggle:hover,\s*\.flacso-navbar__submenu-toggle:focus,\s*\.flacso-navbar__submenu-toggle:active\s*\{[^}]*color:\s*var\(--flacso-chrome-blue\)/s', $css) === 1, 'la flecha del submenú conserva su color en hover, focus y active (no blanco)');

// Aire lateral y vertical en la barra.
drawer_check(preg_match('/\.flacso-navbar__navigation\s+\.flacso-navbar__drawer-close\s*\{[^}]*padding:\s*0\.9rem 0\.6rem/s', $css) === 1, 'la barra tiene relleno vertical y lateral (no pegada a los bordes)');

echo "OK cabecera del cajón móvil\n";
