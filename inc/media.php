<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function flacso_child_register_seminar_image_sizes() {
    add_image_size( 'flacso-seminario-4x5', 800, 1000, true );
    add_image_size( 'flacso-oferta-4x5', 800, 1000, true );
}
add_action( 'after_setup_theme', 'flacso_child_register_seminar_image_sizes', 25 );

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

