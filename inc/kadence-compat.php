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

/** Evitar que cadenas arbitrarias activen el cambio. */
function flacso_child_sanitize_kadence_toggle( $value ): bool {
    return in_array( $value, array( true, 1, '1', 'yes', 'on' ), true );
}

function flacso_child_register_kadence_compat( $wp_customize ): void {
    $wp_customize->add_section(
        'flacso_kadence_compat',
        array(
            'title'       => __( 'Integración con Kadence', 'kadence-child-flacso' ),
            'description' => __( 'Los colores, las fuentes y los botones usan las opciones globales de Kadence. Podés activar también los constructores nativos de cabecera y pie.', 'kadence-child-flacso' ),
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
