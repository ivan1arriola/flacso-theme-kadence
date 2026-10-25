<?php
/**
 * Contrato visual de la carta: índice con destinos reales y composición editorial.
 */
declare(strict_types=1);

$root = dirname(__DIR__);
$template = (string) file_get_contents($root . '/template-parts/oferta/carta.php');
$styles = (string) file_get_contents($root . '/assets/css/single-carta-editorial.css');

function assert_carta_editorial(bool $ok, string $message): void {
    if (!$ok) {
        fwrite(STDERR, "FAIL carta editorial: {$message}\n");
        exit(1);
    }
}

assert_carta_editorial(strpos($template, 'class="fc-jump-nav"') !== false, 'falta el índice de la carta');
assert_carta_editorial(strpos($template, 'if ($hay_bloques_detallados)') !== false, 'los vínculos a secciones opcionales deben ser condicionales');

foreach ([
    'fc-overview-title', 'fc-experience-title', 'fc-planificacion', 'fc-practica', 'fc-trust', 'fc-contact-title',
] as $anchor) {
    assert_carta_editorial(strpos($template, 'href="#' . $anchor . '"') !== false, "falta el enlace {$anchor}");
    assert_carta_editorial(strpos($template, 'id="' . $anchor . '"') !== false, "falta el destino {$anchor}");
}

assert_carta_editorial(strpos($template, '<div class="fc-reading-layout">') !== false, 'falta la estructura de lectura en escritorio');
assert_carta_editorial(strpos($template, '</div><!-- /.fc-reading-layout -->') !== false, 'falta cerrar la estructura editorial');
assert_carta_editorial(strpos($template, 'fc-reading-sidebar') !== false, 'falta índice lateral');
assert_carta_editorial(strpos($template, 'fc-general-grid--long') !== false, 'falta la variante editorial para descripciones extensas');
assert_carta_editorial(strpos($template, '$fc_long_description = mb_strlen(') !== false, 'la variante de dos columnas debe depender de la extensión del texto');
assert_carta_editorial(strpos($template, "aria-current', 'location'") !== false, 'falta el estado de la sección activa');
assert_carta_editorial(strpos($styles, '.fc-reading-layout {') !== false && strpos($styles, 'position: sticky;') !== false, 'falta la lectura guiada en escritorio');
assert_carta_editorial(strpos($styles, 'column-count: 2;') !== false, 'falta aprovechar el ancho de las descripciones largas');
assert_carta_editorial(strpos($styles, '.fc-major-section--trust > .fc-more-grid') !== false, 'las tarjetas de trayectoria deben ocupar dos columnas');
assert_carta_editorial(strpos($template, 'fc-general-grid--with-audience') !== false, 'falta la clase condicional del panel de público');
assert_carta_editorial(strpos($template, '<h3 id="fc-panel-descripcion">') !== false, 'la descripción debe respetar la jerarquía de encabezados');
assert_carta_editorial(strpos($styles, '.fc-general-grid--with-audience {') !== false, 'falta la composición en columnas');
assert_carta_editorial(strpos($styles, '.fc-jump-nav__links') !== false && strpos($styles, 'overflow-x: auto;') !== false, 'falta la navegación móvil desplazable');
assert_carta_editorial(strpos($styles, '.fc-pricing-table tbody td:first-child') !== false, 'faltan los estilos de concepto en precios');

echo "OK carta editorial V2: índice lateral, lectura adaptable y navegación móvil.\n";
