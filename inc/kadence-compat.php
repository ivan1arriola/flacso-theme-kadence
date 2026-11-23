<?php
/**
 * Compatibilidad reversible con los constructores nativos de Kadence.
 *
 * @package kadence-child-flacso
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/** Mantener por defecto la cabecera y el pie institucionales existentes. */
function flacso_child_use_kadence_chrome(): bool {
    return (bool) get_theme_mod( 'flacso_use_kadence_chrome', false );
}

/**
 * Etiqueta del contenedor principal de cada plantilla. Con Kadence nativo el tema padre ya abre
 * <main id="inner-wrap">, y un documento debe tener un solo landmark principal.
 */
function flacso_child_main_tag(): string {
    return flacso_child_use_kadence_chrome() ? 'div' : 'main';
}

/** Evitar que cadenas arbitrarias activen el cambio. */
function flacso_child_sanitize_kadence_toggle( $value ): bool {
    return in_array( $value, array( true, 1, '1', 'yes', 'on' ), true );
}

function flacso_child_register_kadence_compat( $wp_customize ): void {
    $wp_customize->add_section(
        'flacso_kadence_compat',
        array(
            'title'       => __( 'Integración con Kadence', 'kadence-child-flacso' ),
            'description' => __( 'Los colores, las fuentes y los botones usan las opciones globales de Kadence. Podés activar también los constructores nativos de cabecera y pie. Aviso de accesibilidad: el texto atenuado usa la paleta 5 de Kadence; mantené al menos 4,5:1 de contraste sobre blanco (#7a8696 da 3,7:1 y no alcanza; #64748b da 4,8:1).', 'kadence-child-flacso' ),
            'priority'    => 32,
        )
    );

    $wp_customize->add_setting(
        'flacso_use_kadence_chrome',
        array(
            'default'           => false,
            'type'              => 'theme_mod',
            'transport'         => 'refresh',
            'sanitize_callback' => 'flacso_child_sanitize_kadence_toggle',
        )
    );
    $wp_customize->add_control(
        'flacso_use_kadence_chrome',
        array(
            'label'       => __( 'Usar cabecera y pie nativos de Kadence', 'kadence-child-flacso' ),
            'description' => __( 'Reemplaza temporalmente la cabecera y el pie de FLACSO por los constructores de Kadence. Comprobá menús y widgets en la vista previa antes de publicar.', 'kadence-child-flacso' ),
            'section'     => 'flacso_kadence_compat',
            'type'        => 'checkbox',
        )
    );
}
add_action( 'customize_register', 'flacso_child_register_kadence_compat' );

/**
 * Un solo <h1> por página: el tema imprime el título de noticias y eventos, así que un <h1> dentro del contenido
 * (p. ej. el título repetido por quien edita) pasa a <h2> conservando sus atributos.
 */
function flacso_child_demote_content_h1( $content ) {
	if ( ! is_singular( array( 'post', 'evento' ) ) || ! in_the_loop() || ! is_main_query() ) {
		return $content;
	}
	return preg_replace( array( '/<h1(?=[\s>])/i', '/<\/h1>/i' ), array( '<h2', '</h2>' ), (string) $content );
}
add_filter( 'the_content', 'flacso_child_demote_content_h1', 20 );
