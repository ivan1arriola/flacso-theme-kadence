<?php
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * ===========================================================
 * ENQUEUE STYLES Y SCRIPTS
 * ===========================================================
 */

// Limpieza de cabecera (Performance)
add_action('init', function() {
    // Eliminar emojis (carga scripts y estilos extras)
    remove_action('wp_head', 'print_emoji_detection_script', 7);
    remove_action('admin_print_scripts', 'print_emoji_detection_script');
    remove_action('wp_print_styles', 'print_emoji_styles');
    remove_action('admin_print_styles', 'print_emoji_styles'); 
    remove_filter('the_content_feed', 'wp_staticize_emoji');
    remove_filter('comment_text_rss', 'wp_staticize_emoji'); 
    remove_filter('wp_mail', 'wp_staticize_emoji_for_email');
    
    // Eliminar meta tags innecesarios
    remove_action('wp_head', 'wp_generator'); // Versión WP
    remove_action('wp_head', 'wlwmanifest_link'); // Windows Live Writer
    remove_action('wp_head', 'rsd_link'); // EditURI link
});

// Carga el CSS del child theme
if ( !function_exists( 'child_theme_configurator_css' ) ):
function child_theme_configurator_css() {
    wp_enqueue_style(
        'chld_thm_cfg_child',
        trailingslashit( get_stylesheet_directory_uri() ) . 'style.css',
        array( 'kadence-global','kadence-header','kadence-content','kadence-woocommerce','kadence-footer' ),
        wp_get_theme()->get('Version')
    );
}
endif;
add_action( 'wp_enqueue_scripts', 'child_theme_configurator_css', 30 ); // prioridad 30 para cargar después de Bootstrap

// Carga Bootstrap 5 y Bootstrap Icons
/**
 * Determina si la página actual requiere Bootstrap y jQuery UI.
 */
function child_theme_needs_bootstrap() {
    if ( ! function_exists( 'is_singular' ) ) {
        return false;
    }

    // 1. Singular y archivos de los CPTs del plugin
    $cpts = array( 'seminario', 'oferta-academica', 'docente', 'evento' );
    if ( is_singular( $cpts ) || is_post_type_archive( $cpts ) ) {
        return true;
    }

    // 2. Taxonomías personalizadas del plugin
    $taxonomies = array( 'tipo-oferta-academica', 'area-tematica', 'tipo-docente' );
    if ( is_tax( $taxonomies ) ) {
        return true;
    }

    // 3. Páginas conocidas por usar formularios o listados
    if ( is_page( array( 'seminarios', 'contactar-seminario', 'preinscripciones' ) ) ) {
        return true;
    }

    // 4. Ruta del endpoint de preinscripción
    if ( get_query_var( 'flacso_preinscripcion' ) ) {
        return true;
    }

    return false;
}

// Carga Bootstrap 5 y Bootstrap Icons
function child_theme_enqueue_bootstrap() {
    // Registrar CSS Bootstrap
    wp_register_style(
        'bootstrap-css',
        'https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css',
        array(),
        '5.3.8',
        'all'
    );

    // Registrar CSS Bootstrap Icons
    wp_register_style(
        'bootstrap-icons',
        'https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.css',
        array(),
        '1.13.1',
        'all'
    );

    // Registrar JS Bootstrap Bundle (incluye Popper)
    wp_register_script(
        'bootstrap-js',
        'https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js',
        array(),
        '5.3.8',
        true
    );

    // Permitir a plugins o plantillas forzar la carga de Bootstrap
    $needs_bootstrap = apply_filters( 'flacso_needs_bootstrap', child_theme_needs_bootstrap() );

    if ( $needs_bootstrap ) {
        wp_enqueue_style( 'bootstrap-css' );
        wp_enqueue_style( 'bootstrap-icons' );
        wp_enqueue_script( 'bootstrap-js' );
    }
}
add_action('wp_enqueue_scripts', 'child_theme_enqueue_bootstrap', 20); // prioridad 20, antes del child CSS

// Carga jQuery UI y estilos base
function child_theme_enqueue_jquery_ui() {
    wp_register_style(
        'jquery-ui-css',
        'https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css',
        array(),
        '1.13.2'
    );

    $needs_jquery_ui = apply_filters( 'flacso_needs_bootstrap', child_theme_needs_bootstrap() );

    if ( $needs_jquery_ui ) {
        wp_enqueue_script( 'jquery-ui-core' );
        wp_enqueue_script( 'jquery-ui-autocomplete' );
        wp_enqueue_style( 'jquery-ui-css' );
    }
}
add_action('wp_enqueue_scripts', 'child_theme_enqueue_jquery_ui', 25);



