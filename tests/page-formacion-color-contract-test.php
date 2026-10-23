<?php
/** Regression: /formacion/ must use each offer's configured palette safely. */
declare(strict_types=1);

$root = dirname(__DIR__);
$template = file_get_contents($root . '/page-formacion.php');
$styles = file_get_contents($root . '/assets/css/page-formacion.css');

function check_formacion_color(bool $ok, string $message): void {
    if (!$ok) {
        fwrite(STDERR, "FAIL: {$message}\n");
        exit(1);
    }
}

check_formacion_color(strpos($template, "['color_principal']") !== false, 'color principal del View Model');
check_formacion_color(strpos($template, "['colores_presentacion']['principal']") !== false, 'respaldo de metadatos de la oferta');
check_formacion_color(strpos($template, "preg_match('/^#[0-9a-fA-F]{6}$/',") !== false, 'validacion de hexadecimal completo');
check_formacion_color(strpos($template, 'esc_attr($accent)') !== false, 'CSS variable escapada al imprimir');
check_formacion_color(strpos($template, 'flacso-formacion-card--accented') !== false, 'acento independiente por tarjeta');
check_formacion_color(strpos($template, 'flacso-formacion-group__palette') !== false, 'paleta de la sección derivada de sus ofertas');
check_formacion_color(strpos($template, 'flacso-formacion-hero__palette') !== false, 'paleta de cabecera derivada de ofertas publicadas');
check_formacion_color(strpos($template, 'array_unique') === false || strpos($template, 'in_array($color, $group_palette, true)') !== false, 'se evita duplicar colores');
check_formacion_color(substr_count($template, 'class="flacso-formacion-card"') === 1, 'seminarios sin falso color institucional de oferta');
check_formacion_color(strpos($styles, 'var(--offer-accent)') !== false, 'CSS toma el color de cada oferta');
check_formacion_color(strpos($styles, '.flacso-formacion-card:focus-visible') !== false, 'foco accesible');
check_formacion_color(strpos($styles, 'grid-template-columns: minmax(0, 1fr);') !== false, 'rejilla móvil sin desborde');
check_formacion_color(strpos($styles, 'prefers-reduced-motion: reduce') !== false, 'respeto por movimiento reducido');

check_formacion_color(strpos($template, 'flacso-formacion-hero__intro') !== false, 'hero editorial adaptativo');
check_formacion_color(strpos($template, 'flacso-formacion-nav__count') !== false, 'navegación con cantidad de propuestas');
check_formacion_color(strpos($template, 'flacso-formacion-group__description') !== false, 'descripción académica por grupo');
check_formacion_color(strpos($template, '(float) $carga_horaria > 0') !== false, 'no presenta horas en cero');
check_formacion_color(strpos($styles, 'aspect-ratio: 4 / 5;') !== false, 'imágenes académicas 4:5');
check_formacion_color(strpos($styles, 'object-fit: contain;') !== false, 'imágenes sin recortes');
check_formacion_color(strpos($styles, 'repeat(2, minmax(0, 1fr))') !== false, 'mosaico en escritorio');
check_formacion_color(strpos($styles, 'grid-template-columns: minmax(0, 42%) minmax(0, 58%)') !== false, 'tarjetas móviles con imagen y texto');

echo "Formación: acentos dinámicos por oferta, accesibilidad y mobile verificados.\n";
