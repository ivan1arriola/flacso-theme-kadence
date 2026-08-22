<?php
/**
 * Template para la página de Oferta Académica (/formacion/).
 *
 * La presentación y los datos pertenecen al módulo Oferta Académica del
 * plugin. Esta plantilla solamente integra ese renderer con el tema para que
 * /formacion/ y la página que contiene el bloque compartan una única interfaz.
 *
 * @package kadence-child-flacso
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$catalog_markup = class_exists( 'Oferta_Renderer' )
	? Oferta_Renderer::render_oferta_pagina( [
		'heroTitle'   => 'Oferta Académica',
		'heroImageId' => 22981,
	] )
	: '';

get_header();

if ( $catalog_markup !== '' ) {
	echo $catalog_markup; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
} else {
	?>
	<div class="container" style="padding: 60px 0;">
		<p><?php esc_html_e( 'La oferta académica no está disponible en este momento.', 'kadence-child-flacso' ); ?></p>
	</div>
	<?php
}

get_footer();
