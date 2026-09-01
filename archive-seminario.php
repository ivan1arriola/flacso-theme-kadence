<?php
/**
 * Archivo público del CPT Seminario.
 *
 * La URL canónica /formacion/seminarios/ usa el mismo catálogo visual
 * que la plantilla histórica seminarios-listado.php, pero ahora entra
 * por la jerarquía nativa de templates de WordPress.
 *
 * @package kadence-child-flacso
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$css_path = 'assets/css/seminarios-listado.css';
$js_path  = 'assets/js/seminarios-listado.js';

wp_enqueue_style(
    'flacso-seminarios-listado',
    get_stylesheet_directory_uri() . '/' . $css_path,
    array( 'flacso-site-chrome' ),
    function_exists( 'flacso_child_asset_version' ) ? flacso_child_asset_version( $css_path ) : wp_get_theme()->get( 'Version' )
);

wp_enqueue_script(
    'flacso-seminarios-listado',
    get_stylesheet_directory_uri() . '/' . $js_path,
    array(),
    function_exists( 'flacso_child_asset_version' ) ? flacso_child_asset_version( $js_path ) : wp_get_theme()->get( 'Version' ),
    true
);

require get_stylesheet_directory() . '/seminarios-listado.php';
