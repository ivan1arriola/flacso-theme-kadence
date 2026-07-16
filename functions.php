<?php
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

require_once get_stylesheet_directory() . '/inc/open-graph.php';

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

/**
 * Normaliza la presentación de las imágenes destacadas en una relación 4:5.
 * El script crea el fondo difuminado a partir de la propia imagen.
 */
function flacso_child_enqueue_featured_images() {
    wp_enqueue_script(
        'flacso-child-featured-images',
        trailingslashit( get_stylesheet_directory_uri() ) . 'assets/js/featured-images.js',
        array(),
        wp_get_theme()->get( 'Version' ),
        true
    );
}
add_action( 'wp_enqueue_scripts', 'flacso_child_enqueue_featured_images', 35 );

function flacso_child_enqueue_ga4_events() {
    wp_enqueue_script(
        'flacso-child-ga4-events',
        trailingslashit( get_stylesheet_directory_uri() ) . 'assets/js/ga4-events.js',
        array(),
        wp_get_theme()->get( 'Version' ),
        true
    );
    wp_add_inline_script(
        'flacso-child-ga4-events',
        'window.flacsoGA4Config = window.flacsoGA4Config || ' . wp_json_encode( array( 'siteArea' => 'flacso_theme' ) ) . ';',
        'before'
    );
}
add_action( 'wp_enqueue_scripts', 'flacso_child_enqueue_ga4_events', 5 );

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

/**
 * ===========================================================
 * CONTACT PAGE — CUSTOMIZER SETTINGS
 * ===========================================================
 */
add_action('customize_register', function ($wp_customize) {
    $wp_customize->add_section('flacso_contacto', array(
        'title'    => 'Contacto',
        'priority' => 130,
    ));

    $wp_customize->add_setting('flacso_contacto_telefono', array(
        'default'           => '2481 7459',
        'sanitize_callback' => 'sanitize_text_field',
        'type'              => 'theme_mod',
    ));
    $wp_customize->add_control('flacso_contacto_telefono', array(
        'label'   => 'Teléfono',
        'section' => 'flacso_contacto',
        'type'    => 'text',
    ));

    $wp_customize->add_setting('flacso_contacto_email', array(
        'default'           => 'secretaria@flacso.edu.uy',
        'sanitize_callback' => 'sanitize_email',
        'type'              => 'theme_mod',
    ));
    $wp_customize->add_control('flacso_contacto_email', array(
        'label'   => 'Correo',
        'section' => 'flacso_contacto',
        'type'    => 'email',
    ));

    $wp_customize->add_setting('flacso_contacto_direccion', array(
        'default'           => '8 de Octubre 2882, CP 11600, Montevideo',
        'sanitize_callback' => 'sanitize_text_field',
        'type'              => 'theme_mod',
    ));
    $wp_customize->add_control('flacso_contacto_direccion', array(
        'label'   => 'Dirección',
        'section' => 'flacso_contacto',
        'type'    => 'text',
    ));

    $wp_customize->add_setting('flacso_contacto_maps_url', array(
        'default'           => 'https://maps.google.com/?q=FLACSO+Uruguay+8+de+Octubre+2882+Montevideo',
        'sanitize_callback' => 'esc_url_raw',
        'type'              => 'theme_mod',
    ));
    $wp_customize->add_control('flacso_contacto_maps_url', array(
        'label'   => 'URL de Google Maps',
        'section' => 'flacso_contacto',
        'type'    => 'url',
    ));

    $wp_customize->add_setting('flacso_contacto_embed_url', array(
        'default'           => 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3271.848752430452!2d-56.1514792!3d-34.907644!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x959f8032b3f3b3f3%3A0x3f3f3f3f3f3f3f3f!2sFLACSO+Uruguay!5e0!3m2!1ses!2suy!4v1',
        'sanitize_callback' => 'esc_url_raw',
        'type'              => 'theme_mod',
    ));
    $wp_customize->add_control('flacso_contacto_embed_url', array(
        'label'   => 'URL embed del mapa',
        'section' => 'flacso_contacto',
        'type'    => 'url',
    ));

    $wp_customize->add_setting('flacso_contacto_agenda_url', array(
        'default'           => 'https://agenda.flacso.edu.uy/',
        'sanitize_callback' => 'esc_url_raw',
        'type'              => 'theme_mod',
    ));
    $wp_customize->add_control('flacso_contacto_agenda_url', array(
        'label'   => 'URL de agenda web',
        'section' => 'flacso_contacto',
        'type'    => 'url',
    ));

    $wp_customize->add_setting('flacso_contacto_otros', array(
        'default'           => wp_json_encode(array(
            array('area' => 'Secretaría Académica', 'persona' => '', 'email' => 'secretaria@flacso.edu.uy'),
            array('area' => 'Inscripciones', 'persona' => '', 'email' => 'inscripciones@flacso.edu.uy'),
            array('area' => 'Soporte Web', 'persona' => '', 'email' => 'soporte@flacso.edu.uy'),
        )),
        'sanitize_callback' => 'flacso_sanitize_contactos_json',
        'type'              => 'theme_mod',
    ));
    $wp_customize->add_control('flacso_contacto_otros', array(
        'label'       => 'Otros contactos (JSON)',
        'description' => 'Formato: [{"area":"Área","persona":"Nombre","email":"correo@ejemplo.com"}]',
        'section'     => 'flacso_contacto',
        'type'        => 'textarea',
    ));
});

function flacso_sanitize_contactos_json($value) {
    $decoded = json_decode($value, true);
    if (!is_array($decoded)) {
        return '[]';
    }
    $sanitized = array();
    foreach ($decoded as $item) {
        if (!is_array($item) || empty($item['area']) || empty($item['email'])) {
            continue;
        }
        $sanitized[] = array(
            'area'    => sanitize_text_field($item['area']),
            'persona' => isset($item['persona']) ? sanitize_text_field($item['persona']) : '',
            'email'   => sanitize_email($item['email']),
        );
    }
    return wp_json_encode($sanitized);
}

function flacso_get_contactos() {
    $raw = get_theme_mod('flacso_contacto_otros', '[]');
    $decoded = json_decode($raw, true);
    return is_array($decoded) ? $decoded : array();
}

/**
 * ===========================================================
 * CONTACT PAGE — VIRTUAL PAGE (sin crear página en DB)
 * ===========================================================
 */
add_action('init', function () {
    add_rewrite_rule('^contactos/?$', 'index.php?flacso_contact_page=1', 'top');
});

add_filter('query_vars', function ($vars) {
    $vars[] = 'flacso_contact_page';
    return $vars;
});

add_action('template_include', function ($template) {
    if (get_query_var('flacso_contact_page')) {
        global $wp_query;
        $wp_query->is_404 = false;
        $wp_query->is_page = true;
        status_header(200);
        return get_stylesheet_directory() . '/page-contacto.php';
    }
    return $template;
});

add_filter('pre_get_document_title', function ($title) {
    if (get_query_var('flacso_contact_page')) {
        return 'Contacto – ' . get_bloginfo('name');
    }
    return $title;
});

/**
 * ===========================================================
 * CONTACT PAGE — FORCE BOOTSTRAP
 * ===========================================================
 */
add_filter('flacso_needs_bootstrap', function ($needs) {
    if (is_page('contacto') || get_query_var('flacso_contact_page')) {
        return true;
    }
    return $needs;
});

