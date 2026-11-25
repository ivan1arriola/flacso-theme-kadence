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
    $wp_customize->add_setting(
        'flacso_modular_typography',
        array(
            'default'           => false,
            'type'              => 'theme_mod',
            'transport'         => 'refresh',
            'sanitize_callback' => 'flacso_child_sanitize_kadence_toggle',
        )
    );
    $wp_customize->add_control(
        'flacso_modular_typography',
        array(
            'label'       => __( 'Escala tipográfica modular FLACSO', 'kadence-child-flacso' ),
            'description' => __( 'Usa el tamaño base de Tipografía de Kadence (px o rem numéricos) y calcula H1–H6 con factor 1,25, pesos 400/600 e interlineados progresivos. No modifica los valores guardados. Desactivá para recuperar los ajustes originales. La portada es la primera plantilla migrada; otras plantillas pueden conservar estilos propios. Recargá el Personalizador después de activar o desactivar.', 'kadence-child-flacso' ),
            'section'     => 'flacso_kadence_compat',
            'type'        => 'checkbox',
        )
    );
}
add_action( 'customize_register', 'flacso_child_register_kadence_compat' );

/** El tema padre mantiene la configuración persistida; el puente solo transforma su lectura. */
function flacso_child_modular_typography_enabled(): bool {
    return flacso_child_sanitize_kadence_toggle( get_theme_mod( 'flacso_modular_typography', false ) )
        && 'theme_mod' === apply_filters( 'kadence_theme_option_type', 'theme_mod' );
}

/** Aceptar únicamente tamaños numéricos positivos y unidades compatibles. */
function flacso_child_typography_sizes( $font ): array {
    if ( ! is_array( $font ) || ! is_array( $font['size'] ?? null ) ) {
        return array();
    }
    $unit = $font['sizeType'] ?? 'px';
    if ( ! in_array( $unit, array( '', 'px', 'rem' ), true ) ) {
        return array();
    }
    $sizes = array();
    foreach ( array( 'desktop', 'tablet', 'mobile' ) as $device ) {
        $value = $font['size'][ $device ] ?? '';
        if ( '' === $value || null === $value ) {
            continue;
        }
        if ( ! is_numeric( $value ) || ! is_finite( (float) $value ) || (float) $value <= 0 || (float) $value > 200 ) {
            return array();
        }
        $sizes[ $device ] = (float) $value;
    }
    if ( ! isset( $sizes['desktop'] ) ) {
        return array();
    }
    return $sizes;
}

/** Base efectiva: no reemplazar preferencias guardadas ni formatos no reconocidos. */
function flacso_child_typography_base(): array {
    $font = get_theme_mod( 'base_font', null );
    if ( null === $font || '' === $font ) {
        return array( 'size' => array( 'desktop' => 16 ), 'sizeType' => 'px' );
    }
    return is_array( $font ) ? $font : array();
}

/** Derivar el tamaño en cada dispositivo sin redondear los pasos intermedios. */
function flacso_child_typography_scale( array $sizes, int $step ): array {
    return array_map(
        static function ( $size ) use ( $step ) {
            return round( (float) $size * pow( 1.25, $step ), 4 );
        },
        $sizes
    );
}

/** Mantiene familia, color y demás propiedades de Kadence; cambia solo las gobernadas. */
function flacso_child_typography_font( array $font, array $base, int $level = 0 ): array {
    $sizes = flacso_child_typography_sizes( $base );
    if ( ! $sizes ) {
        return $font;
    }
    $font['size'] = flacso_child_typography_scale( $sizes, $level ? 6 - $level : 0 );
    $font['sizeType'] = ! empty( $base['sizeType'] ) ? $base['sizeType'] : 'px';
    $font['lineHeight'] = array( 'desktop' => $level ? round( 1.05 + $level * 0.05, 2 ) : 1.6 );
    $font['lineType'] = '';
    $font['weight'] = $level ? '600' : '400';
    $font['variant'] = $level ? '600' : 'regular';
    return $font;
}

/** Los valores predeterminados también pasan por el sistema cuando no hay un theme_mod guardado. */
function flacso_child_typography_defaults( array $defaults ): array {
    if ( ! flacso_child_modular_typography_enabled() ) {
        return $defaults;
    }
    $base = flacso_child_typography_base();
    if ( ! flacso_child_typography_sizes( $base ) ) {
        return $defaults;
    }
    if ( isset( $defaults['base_font'] ) && is_array( $defaults['base_font'] ) ) {
        $defaults['base_font'] = flacso_child_typography_font( $defaults['base_font'], $base );
    }
    for ( $level = 1; $level <= 6; $level++ ) {
        $key = 'h' . $level . '_font';
        if ( isset( $defaults[ $key ] ) && is_array( $defaults[ $key ] ) ) {
            $defaults[ $key ] = flacso_child_typography_font( $defaults[ $key ], $base, $level );
        }
    }
    return $defaults;
}
add_filter( 'kadence_theme_options_defaults', 'flacso_child_typography_defaults', 20 );

function flacso_child_typography_base_mod( $font ) {
    if ( ! flacso_child_modular_typography_enabled() || ! is_array( $font ) ) {
        return $font;
    }
    return flacso_child_typography_font( $font, $font );
}
add_filter( 'theme_mod_base_font', 'flacso_child_typography_base_mod', 20 );

for ( $flacso_level = 1; $flacso_level <= 6; $flacso_level++ ) {
    add_filter(
        'theme_mod_h' . $flacso_level . '_font',
        static function ( $font ) use ( $flacso_level ) {
            if ( ! flacso_child_modular_typography_enabled() || ! is_array( $font ) ) {
                return $font;
            }
            return flacso_child_typography_font( $font, flacso_child_typography_base(), $flacso_level );
        },
        20
    );
}
unset( $flacso_level );

/** La vista previa debe recalcular la escala en el servidor, no solo en el CSS del navegador. */
function flacso_child_typography_customize( $wp_customize ): void {
    if ( ! flacso_child_modular_typography_enabled() || ! flacso_child_typography_sizes( flacso_child_typography_base() ) ) {
        return;
    }
    $base = $wp_customize->get_setting( 'base_font' );
    if ( $base ) {
        $base->transport = 'refresh';
    }
    for ( $level = 1; $level <= 6; $level++ ) {
        $key = 'h' . $level . '_font';
        // El control compuesto no permite bloquear únicamente tamaño, peso e interlineado.
        // Los colores y familias ya guardados se preservan y vuelven a editarse al desactivar.
        $wp_customize->remove_control( $key );
    }
}
add_action( 'customize_register', 'flacso_child_typography_customize', 100 );

/** Tokens compartidos con las plantillas migradas; valores exclusivamente numéricos y unidades conocidas. */
function flacso_child_typography_tokens( array $font ): string {
    $sizes = flacso_child_typography_sizes( $font );
    if ( ! $sizes ) {
        return '';
    }
    $unit = ! empty( $font['sizeType'] ) ? $font['sizeType'] : 'px';
    $css = '';
    foreach ( array( 'desktop' => '', 'tablet' => '(max-width:1024px)', 'mobile' => '(max-width:767px)' ) as $device => $query ) {
        if ( ! isset( $sizes[ $device ] ) ) {
            continue;
        }
        $rule = 'body.flacso-modular-type{';
        for ( $step = -1; $step <= 5; $step++ ) {
            $key = -1 === $step ? 'small' : (string) $step;
            $rule .= '--flacso-type-' . $key . ':' . round( $sizes[ $device ] * pow( 1.25, $step ), 4 ) . $unit . ';';
        }
        $rule .= '}';
        $css .= $query ? '@media' . $query . '{' . $rule . '}' : $rule;
    }
    return $css;
}

function flacso_child_typography_body_class( array $classes ): array {
    if ( flacso_child_modular_typography_enabled() && flacso_child_typography_sizes( flacso_child_typography_base() ) ) {
        $classes[] = 'flacso-modular-type';
    }
    return $classes;
}
add_filter( 'body_class', 'flacso_child_typography_body_class' );

function flacso_child_typography_enqueue(): void {
    if ( flacso_child_modular_typography_enabled() ) {
        $css = flacso_child_typography_tokens( flacso_child_typography_base() );
        if ( '' !== $css ) {
            wp_add_inline_style( 'chld_thm_cfg_child', $css );
        }
    }
}
add_action( 'wp_enqueue_scripts', 'flacso_child_typography_enqueue', 60 );

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
