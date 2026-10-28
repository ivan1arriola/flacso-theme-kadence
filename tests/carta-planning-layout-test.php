<?php
/** Regression: a single calendar resource must not inherit the generic multi-column grid. */
declare(strict_types=1);

$root = dirname(__DIR__);
$styles = (string) file_get_contents($root . '/assets/css/single-carta-editorial.css');
require_once __DIR__ . '/helpers/carta-source.php';
$template = (string) flacso_carta_template_source($root);

function check_carta_planning(bool $ok, string $message): void {
    if (!$ok) {
        fwrite(STDERR, "FAIL: {$message}\n");
        exit(1);
    }
}

check_carta_planning(
    strpos($styles, '.fc-section:not(.fc-documents-section) > .fc-more-grid') !== false,
    'rejillas genéricas no comprimen el calendario'
);
check_carta_planning(
    strpos($styles, '.fc-documents-section .fc-more-grid {') !== false
        && strpos($styles, 'grid-template-columns: minmax(0, 1fr);') !== false,
    'calendario conserva una columna flexible'
);
check_carta_planning(
    preg_match('/\.fc-major-section--planning \.fc-resource-grid\s*\{\s*grid-template-columns:\s*minmax\(0,\s*1fr\);/', $styles) === 1,
    'calendario y precios se apilan para evitar paneles vacíos'
);
check_carta_planning(
    strpos($template, "esc_html(\$modalidad)") !== false
        && strpos($template, "esc_html_e('Formación 100% a distancia'") === false,
    'el CTA final no contradice la modalidad publicada'
);

echo "Carta: calendario legible y modalidad coherente.\n";
