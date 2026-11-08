<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

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
 * Cabecera y pie institucionales del tema hijo.
 */
function flacso_child_enqueue_site_chrome() {
    $css_path = 'assets/css/site-chrome.css';
    $js_path  = 'assets/js/site-chrome.js';

    wp_enqueue_style(
        'flacso-site-chrome',
        get_stylesheet_directory_uri() . '/' . $css_path,
        array( 'chld_thm_cfg_child' ),
        flacso_child_asset_version( $css_path )
    );
    wp_enqueue_script(
        'flacso-site-chrome',
        get_stylesheet_directory_uri() . '/' . $js_path,
        array(),
        flacso_child_asset_version( $js_path ),
        true
    );
}
add_action( 'wp_enqueue_scripts', 'flacso_child_enqueue_site_chrome', 45 );

function flacso_child_enqueue_front_page_assets() {
    if ( ! is_front_page() ) {
        return;
    }

    $css_path = 'assets/css/front-page.css';

    wp_enqueue_style( 'flacso-home-roboto', 'https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;600;700&display=swap', array(), null );
    wp_enqueue_style(
        'flacso-front-page',
        get_stylesheet_directory_uri() . '/' . $css_path,
        array( 'flacso-site-chrome', 'flacso-home-roboto' ),
        flacso_child_asset_version( $css_path )
    );
}
add_action( 'wp_enqueue_scripts', 'flacso_child_enqueue_front_page_assets', 50 );

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

    // Bootstrap forma parte de la base visual del tema FLACSO.
    wp_enqueue_style( 'bootstrap-css' );
    wp_enqueue_style( 'bootstrap-icons' );
    wp_enqueue_script( 'bootstrap-js' );
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

    $needs_jquery_ui = apply_filters( 'flacso_needs_jquery_ui', false );

    if ( $needs_jquery_ui ) {
        wp_enqueue_script( 'jquery-ui-core' );
        wp_enqueue_script( 'jquery-ui-autocomplete' );
        wp_enqueue_style( 'jquery-ui-css' );
    }
}
add_action('wp_enqueue_scripts', 'child_theme_enqueue_jquery_ui', 25);

/**
 * Devuelve una versión de recurso basada en su fecha de modificación.
 * Esto permite cachear indefinidamente y renovar la URL solamente al publicar.
 */
function flacso_child_asset_version( $relative_path ) {
    $absolute_path = get_stylesheet_directory() . '/' . ltrim( $relative_path, '/' );

    return is_readable( $absolute_path ) ? (string) filemtime( $absolute_path ) : wp_get_theme()->get( 'Version' );
}

/**
 * Recursos específicos por plantilla. Evita CSS y JavaScript global innecesario.
 */
function flacso_child_enqueue_template_assets() {
    global $template;

    $template_assets = array(
        'single-convenio.php'                 => 'convenio',
        'archive-convenio.php'                => 'convenio',
        'single-oferta-academica.php'         => 'single-oferta-academica',
        'single-seminario.php'                => 'single-seminario',
        'category-novedades.php'              => 'category-novedades',
        'single.php'                          => 'single-post',
        'single-evento.php'                   => 'single-post',
        'single-docente.php'                  => 'single-docente',
        'archive-docente.php'                 => 'archive-docente',
        'taxonomy-tipo-oferta-academica.php'  => 'taxonomy-oferta',
        'page-contacto.php'                   => 'page-contacto',
        'page-preguntas-frecuentes.php'       => 'page-faq',
        'consulta-seminario.php'              => 'consulta-seminario',
        'page-formacion.php'                  => 'formacion-page',
        'formacion-page.php'                  => 'formacion-page',
        'seminarios-listado.php'              => 'seminarios-listado',
    );

    $active_template = is_string( $template ) ? basename( $template ) : '';

    // /formacion/ es una ruta virtual servida por el plugin mediante
    // page-formacion.php. WordPress puede tener otra plantilla global aquí,
    // así que esta ruta debe prevalecer para cargar formacion-page.css.
    $request_uri  = sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ?? '' ) );
    $request_path = trim( (string) wp_parse_url( $request_uri, PHP_URL_PATH ), '/' );
    if ( 'formacion' === $request_path ) {
        $active_template = 'page-formacion.php';
    }

    foreach ( $template_assets as $template_file => $asset ) {
        if ( $active_template !== $template_file ) {
            continue;
        }

        // Los seminarios unificados usan single-oferta-academica.php, pero
        // conservan los recursos visuales de la ficha pública de seminario.
        if ( 'single-oferta-academica' === $asset && is_singular( 'oferta-academica' ) ) {
            $offer_id = (int) get_queried_object_id();
            if ( function_exists( 'flacso_theme_is_seminar' ) && flacso_theme_is_seminar( $offer_id ) ) {
                $asset = 'single-seminario';
            }
        }

        $css_path = 'single-oferta-academica' === $asset ? 'oferta-academica/style.css' : 'assets/css/' . $asset . '.css';
        $js_path  = 'single-oferta-academica' === $asset ? 'oferta-academica/script.js' : 'assets/js/' . $asset . '.js';

        if ( is_readable( get_stylesheet_directory() . '/' . $css_path ) ) {
            wp_enqueue_style(
                'flacso-' . $asset,
                get_stylesheet_directory_uri() . '/' . $css_path,
                array( 'chld_thm_cfg_child' ),
                flacso_child_asset_version( $css_path )
            );
        }

        if ( is_readable( get_stylesheet_directory() . '/' . $js_path ) ) {
            wp_enqueue_script(
                'flacso-' . $asset,
                get_stylesheet_directory_uri() . '/' . $js_path,
                array(),
                flacso_child_asset_version( $js_path ),
                true
            );
        }

        if ( 'consulta-seminario' === $asset ) {
            wp_enqueue_script( 'jquery' );
            wp_enqueue_style( 'intl-tel-input-css', 'https://cdn.jsdelivr.net/npm/intl-tel-input@24.2.0/build/css/intlTelInput.css', array(), '24.2.0' );
            wp_enqueue_style( 'country-select-css', 'https://cdn.jsdelivr.net/npm/country-select-js@2.0.1/build/css/countrySelect.min.css', array(), '2.0.1' );
            wp_enqueue_script( 'intl-tel-input-js', 'https://cdn.jsdelivr.net/npm/intl-tel-input@24.2.0/build/js/intlTelInput.min.js', array(), '24.2.0', true );
            wp_enqueue_script( 'country-select-js', 'https://cdn.jsdelivr.net/npm/country-select-js@2.0.1/build/js/countrySelect.min.js', array( 'jquery' ), '2.0.1', true );
        }

        break;
    }
}
add_action( 'wp_enqueue_scripts', 'flacso_child_enqueue_template_assets', 40 );

/**
 * Estilos de articulación compartidos por carta y página pública.
 * Sin impacto en archivos, home ni en preinscripciones.
 */
function flacso_child_enqueue_articulation_styles() {
    if (!is_singular('oferta-academica')) {
        return;
    }
    $path = 'assets/css/academic-articulations.css';
    wp_enqueue_style(
        'flacso-academic-articulations',
        get_stylesheet_directory_uri() . '/' . $path,
        [],
        flacso_child_asset_version($path)
    );
}
add_action('wp_enqueue_scripts', 'flacso_child_enqueue_articulation_styles', 50);

/**
 * Determina si la consulta actual corresponde a una carta de presentación académica.
 */
function flacso_theme_is_carta(): bool {
    if ( ! function_exists( 'is_singular' ) || ! is_singular( 'oferta-academica' ) ) {
        return false;
    }
    if ( (bool) get_query_var( 'es_carta' ) || ( get_query_var( 'carta' ) !== false && get_query_var( 'carta' ) !== '' ) ) {
        return true;
    }
    if ( isset( $_SERVER['REQUEST_URI'] ) ) {
        $req_path = trim( (string) wp_parse_url( (string) $_SERVER['REQUEST_URI'], PHP_URL_PATH ), '/' );
        if ( str_ends_with( $req_path, '/carta' ) || str_ends_with( $req_path, 'carta' ) ) {
            return true;
        }
    }
    return false;
}

/**
 * Encola estilos de la Carta de Presentación en wp_enqueue_scripts (antes de wp_head).
 */
function flacso_child_enqueue_carta_assets() {
    if ( ! flacso_theme_is_carta() ) {
        return;
    }

    wp_enqueue_style(
        'flacso-single-carta',
        get_stylesheet_directory_uri() . '/assets/css/single-carta.css',
        array( 'chld_thm_cfg_child' ),
        flacso_child_asset_version( 'assets/css/single-carta.css' )
    );

    wp_enqueue_style(
        'flacso-single-carta-editorial',
        get_stylesheet_directory_uri() . '/assets/css/single-carta-editorial.css',
        array( 'flacso-single-carta' ),
        flacso_child_asset_version( 'assets/css/single-carta-editorial.css' )
    );

    wp_enqueue_style(
        'flacso-single-carta-reference',
        get_stylesheet_directory_uri() . '/assets/css/single-carta-reference.css',
        array( 'flacso-single-carta-editorial' ),
        flacso_child_asset_version( 'assets/css/single-carta-reference.css' )
    );
}
add_action( 'wp_enqueue_scripts', 'flacso_child_enqueue_carta_assets', 42 );

