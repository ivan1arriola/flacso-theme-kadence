<?php
/**
 * WebP acotado a las tarjetas de los listados.
 *
 * Los afiches son PNG de diseño con foto: a 480x600 pesan unos 300 a 520 KB; en WebP (calidad 82) unos 15 a 55 KB. Medido
 * sobre 20 afiches reales de /formacion/: de 6,44 MB a 0,56 MB.
 *
 * No se usa el filtro global `image_editor_output_format`: convertiría todas las imágenes subidas del sitio, incluida la de
 * las vistas previas de enlaces (og_meta_size), y no permite distinguir el tamaño. En su lugar se genera un archivo `.webp`
 * junto al PNG/JPEG solo de los tamaños de tarjeta (480x600 y 800x1000) y se sirve solo en las tarjetas; si el `.webp` no
 * existe se conserva el PNG/JPEG. Los originales y los demás tamaños no se tocan.
 *
 * @package kadence-child-flacso
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** Tamaños de tarjeta (flacso-card y los 4x5 del tema) y calidad del WebP. */
const FLACSO_CHILD_WEBP_CARD_SIZES = array( '480x600', '800x1000' );
const FLACSO_CHILD_WEBP_QUALITY    = 82;

/** Ruta del .webp acompañante de un archivo de tarjeta, o '' si no corresponde (otro tamaño, otro formato, ya WebP). */
function flacso_child_webp_companion_path( string $path ): string {
	$sizes = implode( '|', FLACSO_CHILD_WEBP_CARD_SIZES );
	if ( ! preg_match( '/^(.*-(?:' . $sizes . '))\.(?:png|jpe?g)$/i', $path, $m ) ) {
		return '';
	}
	return $m[1] . '.webp';
}

/** URL del .webp acompañante si existe en disco; si no, la URL recibida. */
function flacso_child_webp_companion_url( string $url ): string {
	$upload = wp_get_upload_dir();
	$base   = isset( $upload['baseurl'] ) ? (string) $upload['baseurl'] : '';
	$dir    = isset( $upload['basedir'] ) ? (string) $upload['basedir'] : '';
	if ( '' === $base || '' === $dir || 0 !== strpos( $url, $base . '/' ) ) {
		return $url;
	}
	$webp_url = flacso_child_webp_companion_path( $url );
	if ( '' === $webp_url ) {
		return $url;
	}
	$relative = substr( $webp_url, strlen( $base ) );
	return file_exists( $dir . $relative ) ? $webp_url : $url;
}

/** Filtro de srcset: entrega el .webp en los candidatos de tarjeta que lo tienen. */
function flacso_child_card_srcset_webp( $sources ) {
	if ( ! is_array( $sources ) ) {
		return $sources;
	}
	foreach ( $sources as $key => $source ) {
		if ( is_array( $source ) && ! empty( $source['url'] ) ) {
			$sources[ $key ]['url'] = flacso_child_webp_companion_url( (string) $source['url'] );
		}
	}
	return $sources;
}
add_filter( 'wp_calculate_image_srcset', 'flacso_child_card_srcset_webp', 20, 1 );

/** Filtro de la URL principal: solo para los tamaños nombrados del tema. */
function flacso_child_card_image_src_webp( $image, $attachment_id = 0, $size = '' ) {
	$card_sizes = array( 'flacso-card', 'flacso-oferta-4x5', 'flacso-seminario-4x5' );
	if ( ! is_array( $image ) || empty( $image[0] ) || ! is_string( $size ) || ! in_array( $size, $card_sizes, true ) ) {
		return $image;
	}
	$image[0] = flacso_child_webp_companion_url( (string) $image[0] );
	return $image;
}
add_filter( 'wp_get_attachment_image_src', 'flacso_child_card_image_src_webp', 20, 3 );

/**
 * Genera los .webp de los tamaños de tarjeta de un adjunto. Devuelve cuántos archivos creó (0 si no hay editor o ya existen).
 */
function flacso_child_generate_webp_companions( int $attachment_id ): int {
	$metadata = wp_get_attachment_metadata( $attachment_id );
	$file     = get_attached_file( $attachment_id );
	if ( ! is_array( $metadata ) || empty( $metadata['sizes'] ) || ! is_string( $file ) || '' === $file ) {
		return 0;
	}
	$created = 0;
	foreach ( (array) $metadata['sizes'] as $size ) {
		if ( empty( $size['file'] ) ) {
			continue;
		}
		$source = dirname( $file ) . '/' . $size['file'];
		$target = flacso_child_webp_companion_path( $source );
		if ( '' === $target || ! file_exists( $source ) || file_exists( $target ) ) {
			continue;
		}
		$editor = wp_get_image_editor( $source );
		if ( is_wp_error( $editor ) ) {
			continue;
		}
		$editor->set_quality( FLACSO_CHILD_WEBP_QUALITY );
		$saved = $editor->save( $target, 'image/webp' );
		if ( ! is_wp_error( $saved ) ) {
			++$created;
		}
	}
	return $created;
}

/** Al subir o regenerar una imagen: crea los .webp de tarjeta. */
function flacso_child_generate_webp_on_metadata( $metadata, $attachment_id ) {
	if ( is_array( $metadata ) && ! empty( $metadata['sizes'] ) ) {
		flacso_child_generate_webp_companions( (int) $attachment_id );
	}
	return $metadata;
}
add_filter( 'wp_generate_attachment_metadata', 'flacso_child_generate_webp_on_metadata', 30, 2 );

/** Al borrar un adjunto: elimina sus .webp de tarjeta (los PNG/JPEG los borra WordPress). */
function flacso_child_delete_webp_companions( $attachment_id ): void {
	$metadata = wp_get_attachment_metadata( (int) $attachment_id );
	$file     = get_attached_file( (int) $attachment_id );
	if ( ! is_array( $metadata ) || empty( $metadata['sizes'] ) || ! is_string( $file ) || '' === $file ) {
		return;
	}
	foreach ( (array) $metadata['sizes'] as $size ) {
		if ( empty( $size['file'] ) ) {
			continue;
		}
		$target = flacso_child_webp_companion_path( dirname( $file ) . '/' . $size['file'] );
		if ( '' !== $target && file_exists( $target ) ) {
			wp_delete_file( $target );
		}
	}
}
add_action( 'delete_attachment', 'flacso_child_delete_webp_companions', 5 );

/** Para regenerar los existentes: wp eval 'flacso_child_backfill_webp();' (devuelve el total creado). */
function flacso_child_backfill_webp( array $attachment_ids = array() ): int {
	if ( ! $attachment_ids ) {
		$attachment_ids = get_posts(
			array(
				'post_type'      => 'attachment',
				'post_mime_type' => array( 'image/png', 'image/jpeg' ),
				'post_status'    => 'inherit',
				'posts_per_page' => -1,
				'fields'         => 'ids',
				'no_found_rows'  => true,
			)
		);
	}
	$total = 0;
	foreach ( $attachment_ids as $id ) {
		$total += flacso_child_generate_webp_companions( (int) $id );
	}
	return $total;
}
