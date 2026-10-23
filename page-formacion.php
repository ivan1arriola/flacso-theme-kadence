<?php
/**
 * Compatibilidad con la jerarquía de WordPress y la ruta virtual del plugin.
 *
 * Toda la presentación del catálogo está en formacion-page.php y
 * sus estilos en assets/css/formacion-page.css.
 *
 * @package kadence-child-flacso
 */

if (!defined('ABSPATH')) {
    exit;
}

require get_stylesheet_directory() . '/formacion-page.php';
