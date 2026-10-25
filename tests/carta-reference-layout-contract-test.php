<?php
/**
 * Contrato visual de la carta contra la maqueta editorial adjunta.
 * El archivo HTML de referencia NO se usa como fuente de datos académicos.
 */
declare(strict_types=1);

$root = dirname(__DIR__);
$template = (string) file_get_contents($root . '/template-parts/oferta/carta.php');
$style = (string) file_get_contents($root . '/assets/css/single-carta-reference.css');

function check_reference_carta(bool $ok, string $label): void {
    if (!$ok) {
        fwrite(STDERR, "FAIL carta de referencia: {$label}\n");
        exit(1);
    }
}

check_reference_carta(
    strpos($template, "'flacso-single-carta-reference'") !== false
        && strpos($template, "['flacso-single-carta-editorial']") !== false,
    'el CSS editorial nuevo debe cargar al final y depender del existente'
);

$heroStart = strpos($template, '<section class="fc-hero"');
$heroEnd = $heroStart !== false ? strpos($template, '</section>', $heroStart) : false;
$facts = strpos($template, 'id="fc-info-clave"');
$photo = strpos($template, 'class="fc-hero-visual"');

check_reference_carta(
    $heroStart !== false && $facts !== false && $photo !== false
        && $heroStart < $facts && $facts < $photo && $heroEnd > $facts,
    'las fechas, la duración, la modalidad y la cohorte deben quedar en el hero'
);

check_reference_carta(
    substr_count($template, 'id="fc-info-clave"') === 1,
    'los datos clave no deben duplicarse'
);

check_reference_carta(
    strpos($template, 'class="fc-specializations"') !== false
        && strpos($template, "if (!empty(\$arr_menciones) || !empty(\$arr_orientaciones))") !== false
        && strpos($template, 'foreach ($arr_menciones as $mencion)') !== false
        && strpos($template, 'foreach ($arr_orientaciones as $orientacion)') !== false,
    'menciones y orientaciones deben seguir siendo dinámicas y opcionales'
);

check_reference_carta(
    strpos($template, 'fc-section-eyebrow') !== false
        && strpos($template, "esc_html_e('Calendario y costos'") !== false,
    'los títulos y subtítulos deben corresponder a la nueva jerarquía'
);

check_reference_carta(
    strpos($template, 'flacso_theme_calendar_and_curriculum_share_document(') !== false
        && strpos($template, '&& !$calendario_y_malla_compartidos') !== false,
    'mantener la regla visual de calendario y malla sin fusionar los registros'
);

check_reference_carta(
    strpos($template, 'wp_kses_post($academic[\'descripcion_html\'])') !== false
        && strpos($template, 'wp_kses_post($precios_nota)') !== false
        && strpos($template, 'foreach ($precios_filas as $row)') !== false,
    'conservar textos académicos y aranceles del sistema'
);

check_reference_carta(
    strpos($style, '.fc-hero .fc-feature-grid') !== false
        && strpos($style, '.fc-specializations') !== false
        && strpos($style, '.fc-pricing-table thead th') !== false
        && strpos($style, '.fc-major-section--contact') !== false,
    'composición del prototipo incompleta'
);

check_reference_carta(
    strpos($style, '@media (max-width: 767px)') !== false
        && strpos($style, 'overflow-x: auto;') !== false
        && strpos($style, 'grid-template-columns: minmax(0,1fr)') !== false,
    'faltan reglas móviles de navegación y lectura'
);

check_reference_carta(
    strpos($style, '--fc-brand') !== false
        && strpos($style, 'var(--fc-ref-yellow)') !== false,
    'conservar acento de cada oferta y CTA institucional'
);

echo "OK carta de referencia: composición, datos, navegación y móvil.\n";
