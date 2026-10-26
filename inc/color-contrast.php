<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/** Luminancia relativa WCAG 2 de un color #rrggbb. */
function flacso_child_relative_luminance( string $hex ): float {
    $channel = static function ( int $value ): float {
        $normalized = $value / 255;

        return $normalized <= 0.04045
            ? $normalized / 12.92
            : pow( ( $normalized + 0.055 ) / 1.055, 2.4 );
    };

    return 0.2126 * $channel( (int) hexdec( substr( $hex, 1, 2 ) ) )
        + 0.7152 * $channel( (int) hexdec( substr( $hex, 3, 2 ) ) )
        + 0.0722 * $channel( (int) hexdec( substr( $hex, 5, 2 ) ) );
}

/** Relación de contraste WCAG 2 entre dos colores #rrggbb, de 1 a 21. */
function flacso_child_contrast_ratio( string $foreground, string $background ): float {
    $first  = flacso_child_relative_luminance( $foreground );
    $second = flacso_child_relative_luminance( $background );

    return ( max( $first, $second ) + 0.05 ) / ( min( $first, $second ) + 0.05 );
}

/**
 * Letra blanca o negra sobre un fondo #rrggbb: la de mayor contraste WCAG 2.
 * Para cualquier fondo, una de las dos supera 4,58:1, por encima del mínimo AA (4,5:1).
 */
function flacso_child_contrast_ink( string $background ): string {
    return flacso_child_contrast_ratio( '#ffffff', $background ) >= flacso_child_contrast_ratio( '#000000', $background )
        ? '#ffffff'
        : '#000000';
}
