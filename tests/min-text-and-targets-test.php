<?php
/**
 * Piso de legibilidad y áreas táctiles (fase 1 del plan UI/UX): ninguna hoja propia declara texto menor a 12 px y los enlaces
 * del pie y las migas del encabezado compartido miden 44 px de alto en móvil.
 * Ejecutable sin WordPress: php tests/min-text-and-targets-test.php
 */
$root = dirname(__DIR__);
function mt_check($ok, $d) { if (!$ok) { fwrite(STDERR, "FAIL: " . $d . "\n"); exit(1); } }

$files = array_merge(glob($root . '/assets/css/*.css') ?: [], glob($root . '/assets/css/components/*.css') ?: [], [$root . '/oferta-academica/style.css', $root . '/style.css']);
$small = [];
foreach ($files as $file) {
    if (!is_readable($file)) { continue; }
    if (preg_match_all('/font-size\s*:\s*(\d*\.?\d+)(rem|px)\b/', (string) file_get_contents($file), $m, PREG_SET_ORDER)) {
        foreach ($m as $hit) {
            $px = (float) $hit[1] * ($hit[2] === 'rem' ? 16 : 1);
            if ($px < 12) { $small[] = basename($file) . ': ' . $hit[0]; }
        }
    }
}
mt_check($small === [], 'hay font-size menor a 12 px: ' . implode(' | ', array_slice($small, 0, 5)));

$chrome = str_replace("\r\n", "\n", (string) file_get_contents($root . '/assets/css/site-chrome.css'));
mt_check(preg_match('/\.flacso-footer__menu a \{[^}]*min-height: 44px/', $chrome) === 1, 'los enlaces del pie miden 44 px en móvil');
mt_check(preg_match('/div:last-child \.flacso-footer__menu a \{[^}]*min-height: 44px/', $chrome) === 1, 'los accesos en pastilla del pie miden 44 px');
$ph = str_replace("\r\n", "\n", (string) file_get_contents($root . '/assets/css/components/page-header.css'));
mt_check(preg_match('/max-width: 767px\) \{[^@]*\.flacso-page-header__breadcrumb a \{[^}]*min-height: 44px/s', $ph) === 1, 'las migas del encabezado compartido miden 44 px en móvil');
echo "OK texto mínimo de 12 px y áreas táctiles de 44 px\n";
