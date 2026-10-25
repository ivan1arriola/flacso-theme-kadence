<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Eslogan institucional FLACSO Uruguay 2026-2027.
 * Una única fuente para la portada y las cartas de las ofertas.
 */
function flacso_theme_brand_slogan(): string {
    return __( 'Formación que conecta, conocimiento que transforma.', 'kadence-child-flacso' );
}

/**
 * Sustituye eslóganes anteriores en contenido guardado del hero sin alterar
 * títulos de campañas, convocatorias ni descripciones editoriales.
 */
function flacso_theme_brand_slogan_replace_legacy( $value ): string {
    $text = trim( wp_strip_all_tags( (string) $value ) );
    $legacy = array(
        'Formación para comprender y transformar',
        'Formación que transforma',
        'Formación de excelencia, estés donde estés',
    );

    return in_array( rtrim( $text, '. ' ), $legacy, true )
        ? flacso_theme_brand_slogan()
        : $text;
}

/**
 * Título público del archivo de seminarios (pestaña y SEO).
 *
 * El archivo del CPT no debe heredar el valor predeterminado "Seminarios Archive".
 * Cubrimos el título nativo de WordPress y los plugins SEO más habituales.
 */
function flacso_theme_seminars_archive_document_title( $title ) {
    if ( ! is_post_type_archive( 'seminario' ) ) {
        return $title;
    }

    return 'Seminarios | ' . get_bloginfo( 'name' );
}

add_filter( 'pre_get_document_title', 'flacso_theme_seminars_archive_document_title', 99 );
add_filter( 'rank_math/frontend/title', 'flacso_theme_seminars_archive_document_title', 99 );
add_filter( 'wpseo_title', 'flacso_theme_seminars_archive_document_title', 99 );
add_filter( 'aioseo_title', 'flacso_theme_seminars_archive_document_title', 99 );

/**
 * Resuelve la URL del documento real para comparar malla y calendario.
 *
 * El proxy flacso_view_pdf agrega un nombre (fn) que puede cambiar entre
 * enlaces, aunque su argumento src apunte al mismo archivo. No se hacen
 * solicitudes remotas ni se altera la URL que se publica.
 */
function flacso_theme_academic_document_source( string $url ): string {
    $url = trim( html_entity_decode( $url, ENT_QUOTES | ENT_HTML5, 'UTF-8' ) );

    if ( $url === '' ) {
        return '';
    }

    $parsed = parse_url( $url );
    if ( ! is_array( $parsed ) || empty( $parsed['query'] ) ) {
        return $url;
    }

    parse_str( $parsed['query'], $params );
    if ( ( $params['action'] ?? '' ) !== 'flacso_view_pdf' || ! is_string( $params['src'] ?? null ) ) {
        return $url;
    }

    $decoded = base64_decode( $params['src'], true );
    if ( ! is_string( $decoded ) || ! preg_match( '~^https?://~i', $decoded ) ) {
        return $url;
    }

    return trim( html_entity_decode( $decoded, ENT_QUOTES | ENT_HTML5, 'UTF-8' ) );
}

/**
 * Unifica ambos documentos únicamente si existen y tienen la misma fuente.
 */
function flacso_theme_calendar_and_curriculum_share_document( string $calendario, string $malla ): bool {
    $calendario = flacso_theme_academic_document_source( $calendario );
    $malla      = flacso_theme_academic_document_source( $malla );

    return $calendario !== '' && $malla !== '' && $calendario === $malla;
}
