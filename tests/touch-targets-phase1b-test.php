<?php
/**
 * Áreas táctiles de 44 px (fase 1b): nav > a, .text-link, enlaces rápidos de contacto, marca del pie y nav de Formación.
 * Ejecutable sin WordPress: php tests/touch-targets-phase1b-test.php
 */
$root = dirname(__DIR__);
function tt_check($ok, $d) { if (!$ok) { fwrite(STDERR, "FAIL: " . $d . "\n"); exit(1); } }
function tt_css($f) { global $root; return str_replace("\r\n", "\n", (string) file_get_contents($root . '/assets/css/' . $f)); }

$chrome = tt_css('site-chrome.css');
tt_check(preg_match('/@media \(max-width: 782px\) \{[^@]*nav > a[^{]*\{[^}]*min-height: 44px/', $chrome) === 1, 'nav > a mide 44 px en móvil');
tt_check(preg_match('/@media \(max-width: 782px\) \{[^@]*\.flacso-footer__brand[^{]*\{[^}]*min-height: 44px/', $chrome) === 1, 'la marca del pie mide 44 px en móvil');
tt_check(preg_match('/max-width:\s*767px\)\s*\{[^@]*\.flacso-home \.text-link[^{]*\{[^}]*min-height:\s*44px/', tt_css('front-page.css')) === 1, '.text-link de portada mide 44 px en móvil');
tt_check(preg_match('/\.flacso-contact-quick__link \{[^}]*min-height: 44px/', tt_css('page-contacto.css')) === 1, 'enlaces rápidos de contacto miden 44 px');
tt_check(preg_match('/max-width: 720px\) \{[^@]*\.flacso-formacion-nav__link[^{]*\{[^}]*min-height: 44px/', tt_css('formacion-page.css')) === 1, 'nav de Formación mide 44 px en móvil');
tt_check(preg_match('/\.flacso-topbar__menu a \{[^}]*min-height: 44px/', tt_css('mobile-ux.css')) === 1, 'los enlaces de la barra superior miden 44 px (mobile-ux.css los dejaba en 40)');
tt_check(preg_match('/\.flacso-footer__contact a \{[^}]*min-height: 44px/', tt_css('mobile-ux.css')) === 1, 'los enlaces de contacto del pie miden 44 px (mobile-ux.css los dejaba en 40)');
tt_check(preg_match('/\.flacso-formacion-group__more \{[^}]*min-height: 44px/', tt_css('formacion-page.css')) === 1, '«Ver todas…» de cada grupo de Formación mide 44 px');
tt_check(preg_match('/\.flacso-contact-data a \{[^}]*min-height: 44px/', tt_css('page-contacto.css')) === 1, 'los datos de contacto (correo, teléfono) miden 44 px');
echo "OK áreas táctiles de 44 px (fase 1b)\n";
