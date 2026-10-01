<?php
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

require_once get_stylesheet_directory() . '/inc/academic-public-model.php';
require_once get_stylesheet_directory() . '/inc/open-graph.php';
require_once get_stylesheet_directory() . '/template-parts/front-page/hero.php';
require_once get_stylesheet_directory() . '/inc/home-v2.php';

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
 * Cabecera y pie institucionales del tema hijo.
 */
function flacso_child_register_navigation_locations() {
    add_theme_support( 'flacso-front-page-layout' );

    register_nav_menus(
        array(
            'header_utility'      => __( 'Cabecera: accesos superiores', 'kadence-child-flacso' ),
            'header_cta'          => __( 'Cabecera: botón destacado', 'kadence-child-flacso' ),
            'footer_formacion'     => __( 'Pie: Formación', 'kadence-child-flacso' ),
            'footer_institucional' => __( 'Pie: Institucional', 'kadence-child-flacso' ),
            'footer_accesos'       => __( 'Pie: Accesos', 'kadence-child-flacso' ),
        )
    );
}
add_action( 'after_setup_theme', 'flacso_child_register_navigation_locations', 20 );

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

    if ( class_exists( 'FLACSO_Home_Campaign' ) && FLACSO_Home_Campaign::is_enabled() ) {
        $path    = 'assets/css/home-v2.css';
        $js_path = 'assets/js/home-v2.js';
        wp_enqueue_style( 'flacso-home-v2', get_stylesheet_directory_uri() . '/' . $path, array( 'flacso-site-chrome' ), flacso_child_asset_version( $path ) );
        wp_enqueue_script( 'flacso-home-v2', get_stylesheet_directory_uri() . '/' . $js_path, array(), flacso_child_asset_version( $js_path ), true );
        return;
    }

    $base_css_path = 'assets/css/front-page-base.css';
    $css_path      = 'assets/css/front-page-hero.css';
    $js_path       = 'assets/js/front-page-hero.js';

    wp_enqueue_style(
        'flacso-front-page-base',
        get_stylesheet_directory_uri() . '/' . $base_css_path,
        array( 'flacso-site-chrome' ),
        flacso_child_asset_version( $base_css_path )
    );
    wp_enqueue_style(
        'flacso-front-page-hero',
        get_stylesheet_directory_uri() . '/' . $css_path,
        array( 'flacso-front-page-base' ),
        flacso_child_asset_version( $css_path )
    );
    wp_enqueue_script(
        'flacso-front-page-hero',
        get_stylesheet_directory_uri() . '/' . $js_path,
        array(),
        flacso_child_asset_version( $js_path ),
        true
    );
}
add_action( 'wp_enqueue_scripts', 'flacso_child_enqueue_front_page_assets', 50 );

function flacso_child_primary_menu_fallback() {
    echo '<ul class="flacso-navbar__menu">';
    wp_list_pages(
        array(
            'title_li' => '',
            'depth'    => 1,
        )
    );
    echo '</ul>';
}

function flacso_child_utility_menu_fallback() {
    $agenda_url = get_theme_mod( 'flacso_contacto_agenda_url', 'https://agenda.flacso.edu.uy/' );
    $links = array(
        'https://virtual.flacso.edu.uy/'       => __( 'Campus Virtual', 'kadence-child-flacso' ),
        $agenda_url                            => __( 'Agenda', 'kadence-child-flacso' ),
        'https://www.instagram.com/flacsouruguay/' => 'Instagram',
    );

    echo '<ul class="flacso-topbar__menu">';
    foreach ( $links as $url => $label ) {
        printf( '<li><a href="%1$s">%2$s</a></li>', esc_url( $url ), esc_html( $label ) );
    }
    echo '</ul>';
}

function flacso_child_get_header_cta() {
    $fallback = array(
        'label'  => __( 'Agenda Web', 'kadence-child-flacso' ),
        'url'    => get_theme_mod( 'flacso_contacto_agenda_url', 'https://agenda.flacso.edu.uy/' ),
        'target' => '',
    );
    $locations = get_nav_menu_locations();

    if ( empty( $locations['header_cta'] ) ) {
        return $fallback;
    }

    $items = wp_get_nav_menu_items( (int) $locations['header_cta'] );
    if ( empty( $items ) || ! is_array( $items ) ) {
        return $fallback;
    }

    $item = reset( $items );
    if ( ! $item instanceof WP_Post || empty( $item->url ) || empty( $item->title ) ) {
        return $fallback;
    }

    return array(
        'label'  => wp_strip_all_tags( $item->title ),
        'url'    => $item->url,
        'target' => '_blank' === $item->target ? '_blank' : '',
    );
}

/**
 * Normaliza la presentación de las imágenes destacadas en una relación 4:5.
 * El script crea el fondo difuminado a partir de la propia imagen.
 */
function flacso_child_needs_featured_images() {
    return is_home()
        || is_archive()
        || is_singular( array( 'post', 'docente', 'seminario', 'oferta-academica', 'evento' ) )
        || is_front_page();
}

function flacso_child_enqueue_featured_images() {
    if ( ! flacso_child_needs_featured_images() ) {
        return;
    }

    wp_enqueue_script(
        'flacso-child-featured-images',
        trailingslashit( get_stylesheet_directory_uri() ) . 'assets/js/featured-images.js',
        array(),
        wp_get_theme()->get( 'Version' ),
        true
    );
}
add_action( 'wp_enqueue_scripts', 'flacso_child_enqueue_featured_images', 35 );

/** Evita reutilizar una miniatura anterior tras editarla en WordPress. */
function flacso_child_version_thumbnail_url( $url, $attachment_id ) {
    $modified = get_post_modified_time( 'U', true, $attachment_id );

    return $modified ? add_query_arg( 'flacso_img_v', $modified, $url ) : $url;
}

function flacso_child_version_post_thumbnail_html( $html, $post_id, $attachment_id ) {
    if ( ! $html || ! $attachment_id ) {
        return $html;
    }

    return preg_replace_callback(
        '/\b(src|srcset)=("|\')(.*?)\2/i',
        static function ( $matches ) use ( $attachment_id ) {
            $value = html_entity_decode( $matches[3], ENT_QUOTES, 'UTF-8' );

            if ( 'srcset' === strtolower( $matches[1] ) ) {
                $candidates = array_map( 'trim', explode( ',', $value ) );
                foreach ( $candidates as &$candidate ) {
                    $parts     = preg_split( '/\s+/', $candidate, 2 );
                    $parts[0]  = flacso_child_version_thumbnail_url( $parts[0], $attachment_id );
                    $candidate = implode( ' ', $parts );
                }
                unset( $candidate );
                $value = implode( ', ', $candidates );
            } else {
                $value = flacso_child_version_thumbnail_url( $value, $attachment_id );
            }

            return $matches[1] . '=' . $matches[2] . esc_attr( $value ) . $matches[2];
        },
        $html
    );
}
add_filter( 'post_thumbnail_html', 'flacso_child_version_post_thumbnail_html', 20, 3 );

function flacso_child_flush_posts_using_attachment( $metadata, $attachment_id ) {
    $post_ids = get_posts( array(
        'post_type'      => 'any',
        'post_status'    => 'any',
        'posts_per_page' => -1,
        'fields'         => 'ids',
        'meta_key'       => '_thumbnail_id',
        'meta_value'     => (int) $attachment_id,
        'no_found_rows'  => true,
    ) );

    foreach ( $post_ids as $post_id ) {
        clean_post_cache( $post_id );
    }

    return $metadata;
}
add_filter( 'wp_update_attachment_metadata', 'flacso_child_flush_posts_using_attachment', 20, 2 );

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
        'page-formacion.php'                  => 'page-formacion',
        'seminarios-listado.php'              => 'seminarios-listado',
    );

    $active_template = is_string( $template ) ? basename( $template ) : '';

    // /formacion/ es una ruta virtual resuelta por el plugin antes de que
    // WordPress establezca el template global. Detectarla explícitamente
    // permite cargar los recursos de la plantilla incluida por esa ruta.
    if ( '' === $active_template ) {
        $request_uri  = sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ?? '' ) );
        $request_path = trim( (string) wp_parse_url( $request_uri, PHP_URL_PATH ), '/' );
        if ( 'formacion' === $request_path ) {
            $active_template = 'page-formacion.php';
        }
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

        $css_path = 'assets/css/' . $asset . '.css';
        $js_path  = 'assets/js/' . $asset . '.js';

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
