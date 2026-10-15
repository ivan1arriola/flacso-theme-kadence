<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

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

/**
 * Miniatura editorial 4:5 para seminarios.
 * WordPress recorta desde el centro al generar la miniatura.
 */
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
