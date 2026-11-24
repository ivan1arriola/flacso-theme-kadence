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

function css_rule_body_matching(string $css, string $selector): string {
    if (!preg_match_all('/([^{}]+)\{([^}]*)\}/s', $css, $blocks, PREG_SET_ORDER)) {
        return '';
    }
    $body = '';
    foreach ($blocks as $block) {
        $selectors = array_map('trim', explode(',', preg_replace('/\/\*.*?\*\//s', '', $block[1]) ?? ''));
        if (in_array($selector, $selectors, true)) {
            $body .= "\n" . $block[2];
        }
    }
    return $body;
}

// A9: el buscador de la cabecera conserva el término buscado.
$navbar = str_replace("\r\n", "\n", (string) file_get_contents($root . '/template-parts/header/navbar.php'));
search_focus_check(
    preg_match('/id="flacso-header-search-field"[^>]*value="<\?php echo esc_attr\( get_search_query\(\) \); \?>"/', $navbar) === 1,
    'el campo de búsqueda de la cabecera conserva el término con get_search_query()'
);

// H-A04: el botón que abre el buscador y el formulario mantienen contraste y foco visible (sin estilo verde de Kadence).
$toggleFocusVisible = css_rule_body_matching($css, '.flacso-navbar__icon-button:focus-visible');
search_focus_check(
    preg_match('/outline:\s*[2-9]px\s+solid/', $toggleFocusVisible) === 1,
    'el botón que abre el buscador muestra un contorno de foco visible de al menos 2px'
);
foreach ([':hover', ':focus', ':active'] as $state) {
    $toggleState = css_rule_body_matching($css, '.flacso-navbar__icon-button' . $state);
    search_focus_check(
        strpos($toggleState, 'color: var(--flacso-chrome-blue)') !== false && strpos($toggleState, 'box-shadow: none') !== false,
        'el botón del buscador conserva color y quita box-shadow de Kadence en ' . $state
    );
    $submitState = css_rule_body_matching($css, '.flacso-header-search__form button' . $state);
    search_focus_check(
        strpos($submitState, 'color: #fff') !== false && strpos($submitState, 'box-shadow: none') !== false,
        'el botón Enviar del buscador conserva contraste blanco y quita box-shadow de Kadence en ' . $state
    );
}
$mobileIcon = css_rule_body_matching($css, '.flacso-navbar__icon-button');
search_focus_check(
    preg_match('/@media\s*\(max-width:\s*680px\)\s*\{[^@]*\.flacso-navbar__icon-button,\s*\.flacso-navbar__menu-button\s*\{[^}]*width:\s*44px;\s*height:\s*44px;/s', $css) === 1
        && strpos($mobileIcon, '44px') !== false,
    'el botón del buscador mantiene al menos 44px en móvil'
);

echo "OK foco visible del buscador\n";
