<?php
/** Regression: el texto sobre el color de cada Oferta es blanco o negro y cumple WCAG 2 AA. */
declare(strict_types=1);

define('ABSPATH', __DIR__);

$root = dirname(__DIR__);
require $root . '/inc/color-contrast.php';
$template = file_get_contents($root . '/oferta-academica/page.php');

function check_contrast_ink(bool $ok, string $message): void {
    if (!$ok) {
        fwrite(STDERR, "FAIL: {$message}\n");
        exit(1);
    }
}

$ink = static fn (string $background): array => [
    flacso_child_contrast_ink($background),
    flacso_child_contrast_ratio(flacso_child_contrast_ink($background), $background),
];

check_contrast_ink(abs(flacso_child_contrast_ratio('#ffffff', '#000000') - 21.0) < 0.001, 'blanco sobre negro es 21:1');

// Azul medio de las ofertas de infancias: el azul marino quedaba en 4,7:1 y se leía mal.
[$color, $ratio] = $ink('#489ddd');
check_contrast_ink($color === '#000000' && $ratio >= 7.0, "letra negra sobre #489ddd ({$color}, {$ratio}:1)");

check_contrast_ink($ink('#6b1fad')[0] === '#ffffff', 'letra blanca sobre el violeta de Género');
check_contrast_ink($ink('#0a3470')[0] === '#ffffff', 'letra blanca sobre el azul institucional de respaldo');
check_contrast_ink($ink('#74cc00')[0] === '#000000', 'letra negra sobre verde claro');

// Colores configurados hoy en las Ofertas publicadas.
foreach (['#00c993', '#10bb82', '#1cabb0', '#1dbf66', '#489ddd', '#6b1fad', '#74cc00', '#af2f7e', '#c63535', '#dd793a'] as $offer_color) {
    [$color, $ratio] = $ink($offer_color);
    check_contrast_ink($ratio >= 4.5, "{$offer_color} con letra {$color} cumple AA ({$ratio}:1)");
}

// Cualquier color que se configure en el futuro.
for ($r = 0; $r <= 255; $r += 15) {
    for ($g = 0; $g <= 255; $g += 15) {
        for ($b = 0; $b <= 255; $b += 15) {
            $background = sprintf('#%02x%02x%02x', $r, $g, $b);
            [$color, $ratio] = $ink($background);
            check_contrast_ink($ratio >= 4.5, "{$background} con letra {$color} cumple AA ({$ratio}:1)");
        }
    }
}

check_contrast_ink(strpos($template, '$offer_ink = flacso_child_contrast_ink($offer_color);') !== false, 'la plantilla elige la letra con el helper WCAG');
check_contrast_ink(substr_count($template, 'esc_attr($offer_ink)') === 2, 'botones del encabezado y del formulario usan la misma letra');
check_contrast_ink(strpos($template, "'#082b60' : '#ffffff'") === false && strpos($template, "'#ffffff' : '#082b60'") === false, 'sin la elección anterior con azul marino');

echo "OK offer contrast ink\n";
