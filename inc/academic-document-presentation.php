<?php
/**
 * Presentación de documentos académicos del tema FLACSO.
 *
 * Calendario de la cohorte y malla de la oferta mantienen sus fuentes,
 * registros y semánticas independientes en el backend. Únicamente la vista
 * puede representarlos como un mismo enlace si sus URL apuntan al mismo PDF.
 *
 * @package kadence-child-flacso
 */

if (!defined('ABSPATH')) {
    exit;
}

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
 * Devuelve true únicamente si ambas fuentes existen y corresponden al mismo PDF.
 * Se usa para decidir una etiqueta y evitar botones redundantes en las vistas.
 */
function flacso_theme_calendar_and_curriculum_share_document( string $calendario, string $malla ): bool {
    $calendario = flacso_theme_academic_document_source( $calendario );
    $malla      = flacso_theme_academic_document_source( $malla );

    return $calendario !== '' && $malla !== '' && $calendario === $malla;
}
