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

