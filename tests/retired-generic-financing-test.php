<?php
/**
 * Se elimina la financiación genérica de ofertas y cartas.
 * Las tablas de aranceles y las condiciones específicas se conservan.
 */
$root = dirname(__DIR__);
$model = file_get_contents($root . '/inc/academic-public-model.php');
$offer = file_get_contents($root . '/oferta-academica/page.php');
$carta = file_get_contents($root . '/template-parts/oferta/carta.php');
$css = file_get_contents($root . '/oferta-academica/style.css');

function generic_financing_assert(bool $condition, string $message): void {
    if (!$condition) {
        fwrite(STDERR, "FAIL: {$message}\n");
        exit(1);
    }
}

generic_financing_assert(strpos($offer, 'detail-body--financiacion') === false, 'la página no contiene el acordeón genérico');
generic_financing_assert(strpos($offer, "esc_html_e('Financiación',") === false, 'la página no contiene encabezado de acordeón');
generic_financing_assert(strpos($carta, 'carta_mas_info_financiacion') === false, 'la carta no consume la tarjeta de financiación eliminada');
generic_financing_assert(strpos($model, '"financiacion_html"') === false, 'el modelo ya no recupera el texto genérico');
generic_financing_assert(strpos($css, 'detail-body--financiacion') === false, 'no quedan estilos específicos del acordeón eliminado');

// No eliminar precios, planes de cuotas reales, formas de pago ni enlaces.
generic_financing_assert(strpos($model, '"tabla_precio_id"') !== false && strpos($model, '"valor_uyu"') !== false, 'el modelo sigue entregando aranceles de la cohorte');
generic_financing_assert(strpos($carta, 'fc-pricing-section') !== false, 'la carta conserva la sección de costos');
generic_financing_assert(strpos($carta, 'precios_filas') !== false, 'se conservan filas de aranceles');
generic_financing_assert(strpos($carta, 'formas-de-pago/') !== false, 'se conserva el enlace a formas de pago');
generic_financing_assert(strpos($carta, 'carta_mas_info_trayectoria_html') !== false, 'se conserva la información institucional de trayectoria');
generic_financing_assert(strpos($carta, 'carta_mas_info_gestion_html') !== false, 'se conserva la información institucional de gestión');

echo "OK: se retiró financiación genérica sin afectar aranceles ni pagos\n";
