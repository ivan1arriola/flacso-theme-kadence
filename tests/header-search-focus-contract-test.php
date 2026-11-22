<?php
/**
 * El campo y el botón del buscador del header deben mostrar foco visible.
 * El campo no tiene borde propio: sin un contorno en :focus-visible, la persona que navega con teclado no ve dónde está.
 * Ejecutable sin WordPress: php tests/header-search-focus-contract-test.php
 */
$root = dirname(__DIR__);

function search_focus_check($ok, $description) {
    if (!$ok) { fwrite(STDERR, "FAIL: " . $description . "\n"); exit(1); }
}

$css = str_replace("\r\n", "\n", (string) file_get_contents($root . '/assets/css/site-chrome.css'));

function css_rule_body(string $css, string $selector): string {
    if (!preg_match('/' . preg_quote($selector, '/') . '\s*\{([^}]*)\}/', $css, $m)) { return ''; }
    return $m[1];
}

search_focus_check(css_rule_body($css, '.flacso-header-search__form input:focus') === '' || !preg_match('/outline:\s*(0|none)/', css_rule_body($css, '.flacso-header-search__form input:focus')), 'el campo no anula el contorno de foco');

$input = css_rule_body($css, '.flacso-header-search__form input:focus-visible');
search_focus_check($input !== '', 'el campo define un estilo de foco visible');
search_focus_check(preg_match('/outline:\s*[2-9]px\s+solid/', $input) === 1, 'el campo usa un contorno de al menos 2px');
search_focus_check(!preg_match('/outline:\s*(0|none)/', $input), 'el contorno del campo no se anula');

$button = css_rule_body($css, '.flacso-header-search__form button:focus-visible');
search_focus_check(preg_match('/outline:\s*[2-9]px\s+solid/', $button) === 1, 'el botón de búsqueda también muestra foco visible');

echo "OK foco visible del buscador\n";
