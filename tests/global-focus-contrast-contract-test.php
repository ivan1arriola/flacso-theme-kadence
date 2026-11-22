<?php
/**
 * El anillo de foco global debe verse sobre fondo claro y oscuro. El amarillo de la paleta sobre blanco da 1,47:1
 * (WCAG pide 3:1), así que el anillo es de dos tonos: contorno del azul institucional (editable desde Kadence,
 * palette1) con una franja blanca entre el elemento y el contorno, que destaca sobre fondos oscuros.
 * Ejecutable sin WordPress: php tests/global-focus-contrast-contract-test.php
 */
$root = dirname(__DIR__);

function focus_check($ok, $description) {
    if (!$ok) { fwrite(STDERR, "FAIL: " . $description . "\n"); exit(1); }
}

$css = str_replace("\r\n", "\n", (string) file_get_contents($root . '/style.css'));
focus_check(preg_match('/:where\(a, button, input, select, textarea, \[tabindex\]\):focus-visible\s*\{([^}]*)\}/', $css, $m) === 1, 'existe la regla de foco global');
$rule = $m[1];

focus_check(preg_match('/outline:\s*3px solid var\(--flacso-color-primary\)/', $rule) === 1, 'el contorno usa el azul institucional (palette1), no el amarillo');
focus_check(strpos($rule, '--flacso-color-accent') === false, 'el anillo global ya no depende del amarillo (1,47:1 sobre blanco)');
focus_check(preg_match('/box-shadow:\s*0 0 0 \d+px #fff/i', $rule) === 1, 'una franja blanca separa el contorno del elemento para fondos oscuros');
focus_check(preg_match('/outline-offset:\s*([2-9])px/', $rule) === 1, 'el contorno deja un margen para que la franja se vea');
focus_check(strpos($rule, 'outline: 0') === false && strpos($rule, 'outline: none') === false, 'no se anula el contorno');

// El azul institucional por defecto debe pasar 3:1 sobre blanco.
function lum($hex) { $hex = ltrim($hex, '#'); $c = [0, 2, 4]; $v = array_map(function ($i) use ($hex) { $x = hexdec(substr($hex, $i, 2)) / 255; return $x <= 0.03928 ? $x / 12.92 : pow(($x + 0.055) / 1.055, 2.4); }, $c); return 0.2126 * $v[0] + 0.7152 * $v[1] + 0.0722 * $v[2]; }
preg_match('/--flacso-color-primary:\s*var\(--global-palette1,\s*(#[0-9a-fA-F]{6})\)/', $css, $p);
focus_check(!empty($p[1]), 'se declara el azul por defecto de reserva');
$ratio = (lum('#ffffff') + 0.05) / (lum($p[1]) + 0.05);
focus_check($ratio >= 3, sprintf('el azul por defecto contrasta %.2f:1 sobre blanco (mínimo 3:1)', $ratio));

echo "OK foco global con contraste\n";
