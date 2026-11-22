<?php
/**
 * Coordinación de un programa académico, siempre con filas bien formadas.
 *
 * El metadato `coordinacion` puede faltar, venir vacío o con filas malformadas. Las plantillas lo recorrían tal cual y,
 * con el metadato ausente, `(array) ''` daba `['']` y el archivo de Programas fallaba con HTTP 500 en PHP 8.
 *
 * @package kadence-child-flacso
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @return array<int, array{nombre: string, docente_id: int, rol: string}> Filas con nombre o docente; vacío si no hay.
 */
function flacso_theme_program_coordination( int $program_id ): array {
	$meta = get_post_meta( $program_id, 'coordinacion', true );
	if ( ! is_array( $meta ) ) {
		return array();
	}

	$rows = array();
	foreach ( $meta as $row ) {
		if ( ! is_array( $row ) ) {
			continue;
		}
		$name       = sanitize_text_field( (string) ( $row['nombre'] ?? '' ) );
		$teacher_id = absint( $row['docente_id'] ?? 0 );
		if ( '' === $name && 0 === $teacher_id ) {
			continue;
		}
		$role   = sanitize_text_field( (string) ( $row['rol'] ?? '' ) );
		$rows[] = array(
			'nombre'     => $name,
			'docente_id' => $teacher_id,
			'rol'        => '' !== $role ? $role : __( 'Coordinación', 'flacso-uruguay' ),
		);
	}

	return $rows;
}
