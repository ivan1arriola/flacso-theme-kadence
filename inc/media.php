<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function flacso_child_register_seminar_image_sizes() {
    add_image_size( 'flacso-seminario-4x5', 800, 1000, true );
    add_image_size( 'flacso-oferta-4x5', 800, 1000, true );
    // Miniatura de tarjeta de los listados (4:5). El 800x1000 anterior sirve de versión 2x en el srcset.
    add_image_size( 'flacso-card', 480, 600, true );
}
add_action( 'after_setup_theme', 'flacso_child_register_seminar_image_sizes', 25 );

/**
 * Tamaño reducido más grande disponible para una imagen, nunca el original.
 *
 * Las imágenes subidas antes de registrar los tamaños 4x5 del tema no los tienen y WordPress devuelve `full`
 * (3 a 5 MB por tarjeta). Devuelve '' si el tamaño no es del tema o no hay ningún reducido.
 *
 * @param array<string,mixed> $metadata Metadatos del adjunto (wp_get_attachment_metadata).
 */
function flacso_child_fallback_image_size( $metadata, string $requested ): string {
    $order = array(
        'flacso-card'          => array( 'flacso-card', 'flacso-oferta-4x5', 'flacso-seminario-4x5', 'medium_large', 'large', 'medium' ),
        'flacso-oferta-4x5'    => array( 'flacso-oferta-4x5', 'flacso-seminario-4x5', 'medium_large', 'large', 'medium' ),
        'flacso-seminario-4x5' => array( 'flacso-seminario-4x5', 'flacso-oferta-4x5', 'medium_large', 'large', 'medium' ),
    );
    if ( ! isset( $order[ $requested ] ) || ! is_array( $metadata ) || empty( $metadata['sizes'] ) || ! is_array( $metadata['sizes'] ) ) {
        return '';
    }
    foreach ( $order[ $requested ] as $candidate ) {
        if ( isset( $metadata['sizes'][ $candidate ] ) ) {
            return $candidate;
        }
    }
    return '';
}

/** image_downsize: si falta el tamaño de tarjeta pedido, entrega el reducido más grande en vez del original. */
function flacso_child_image_downsize_fallback( $out, $attachment_id, $size ) {
    if ( false !== $out || ! is_string( $size ) ) {
        return $out;
    }
    $metadata = wp_get_attachment_metadata( $attachment_id );
    $fallback = flacso_child_fallback_image_size( $metadata, $size );
    if ( '' === $fallback || $fallback === $size ) {
        return $out;
    }
    return image_downsize( $attachment_id, $fallback );
}
add_filter( 'image_downsize', 'flacso_child_image_downsize_fallback', 10, 3 );

/**
 * <img> de una tarjeta de listado: tamaño `flacso-card` con srcset y sizes, carga diferida y decodificación asíncrona.
 *
 * @param int                  $attachment_id Adjunto (0 si no se conoce: se resuelve por la URL).
 * @param string               $fallback_url  URL de respaldo (imagen externa o sin adjunto).
 * @param array<string,string> $extra         Atributos adicionales (por ejemplo `onerror`).
 */
function flacso_child_card_image_html( int $attachment_id, string $fallback_url, string $alt, string $sizes = '', array $extra = array() ): string {
    if ( $attachment_id <= 0 && '' !== $fallback_url ) {
        $attachment_id = (int) attachment_url_to_postid( $fallback_url );
    }
    $attr = array_merge(
        array(
            'alt'      => $alt,
            'loading'  => 'lazy',
            'decoding' => 'async',
        ),
        '' !== $sizes ? array( 'sizes' => $sizes ) : array(),
        $extra
    );
    if ( $attachment_id > 0 ) {
        $html = wp_get_attachment_image( $attachment_id, 'flacso-card', false, $attr );
        if ( '' !== $html ) {
            return $html;
        }
    }
    if ( '' === $fallback_url ) {
        return '';
    }
    $html = '<img src="' . esc_url( $fallback_url ) . '"';
    foreach ( $attr as $name => $value ) {
        $html .= ' ' . $name . '="' . esc_attr( $value ) . '"';
    }
    return $html . '>';
}

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

    $featured_images_path = 'assets/js/featured-images.js';

    wp_enqueue_script(
        'flacso-child-featured-images',
        trailingslashit( get_stylesheet_directory_uri() ) . $featured_images_path,
        array(),
        flacso_child_asset_version( $featured_images_path ),
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

