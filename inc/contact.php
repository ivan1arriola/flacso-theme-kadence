<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

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
