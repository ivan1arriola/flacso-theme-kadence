<?php
/**
 * Prueba aislada del puente entre el tema hijo y las opciones de Kadence.
 */
$root = dirname( __DIR__ );
if ( ! defined( 'ABSPATH' ) ) { define( 'ABSPATH', __DIR__ ); }
function bridge_check( $ok, $message ) {
    if ( ! $ok ) { fwrite( STDERR, "FAIL: {$message}\n" ); exit( 1 ); }
}
$GLOBALS['bridge_mods'] = array();
$GLOBALS['bridge_actions'] = array();
function get_theme_mod( $key, $default = false ) { return $GLOBALS['bridge_mods'][ $key ] ?? $default; }
function add_action( $hook, $callback ) { $GLOBALS['bridge_actions'][ $hook ] = $callback; }
function __( $text, $domain ) { return $text; }
require $root . '/inc/kadence-compat.php';

bridge_check( ! flacso_child_use_kadence_chrome(), 'por defecto se conserva la cabecera FLACSO' );
$GLOBALS['bridge_mods']['flacso_use_kadence_chrome'] = true;
bridge_check( flacso_child_use_kadence_chrome(), 'modo nativo de Kadence activado' );
foreach ( array( false, 0, '0', 'false', '' ) as $value ) {
    bridge_check( ! flacso_child_sanitize_kadence_toggle( $value ), 'se normalizan los valores falsos' );
}
foreach ( array( true, 1, '1', 'yes', 'on' ) as $value ) {
    bridge_check( flacso_child_sanitize_kadence_toggle( $value ), 'se normalizan los valores verdaderos' );
}

class FakeKadenceCustomizer {
    public $sections = array(), $settings = array(), $controls = array();
    function add_section( $id, $arguments ) { $this->sections[ $id ] = $arguments; }
    function add_setting( $id, $arguments ) { $this->settings[ $id ] = $arguments; }
    function add_control( $id, $arguments ) { $this->controls[ $id ] = $arguments; }
}
bridge_check( isset( $GLOBALS['bridge_actions']['customize_register'] ), 'el ajuste se registra con Customizer' );
$customizer = new FakeKadenceCustomizer();
flacso_child_register_kadence_compat( $customizer );
bridge_check( isset( $customizer->sections['flacso_kadence_compat'] ), 'sección de integración disponible' );
bridge_check( isset( $customizer->controls['flacso_use_kadence_chrome'] ), 'opción reversible disponible' );
bridge_check( $customizer->settings['flacso_use_kadence_chrome']['default'] === false, 'el modo nativo requiere activación' );
$section_help = $customizer->sections['flacso_kadence_compat']['description'];
bridge_check( strpos( $section_help, 'paleta 5' ) !== false && strpos( $section_help, '4,5:1' ) !== false, 'el Customizer advierte el contraste mínimo del texto atenuado' );

$header = file_get_contents( $root . '/header.php' );
$footer = file_get_contents( $root . '/footer.php' );
$bootstrap = file_get_contents( $root . '/functions.php' );
bridge_check( strpos( $bootstrap, "inc/kadence-compat.php" ) !== false, 'el bootstrap carga el puente' );
bridge_check( strpos( $header, "get_template_directory() . '/header.php'" ) !== false, 'la cabecera puede delegar al padre' );
bridge_check( strpos( $footer, "get_template_directory() . '/footer.php'" ) !== false, 'el pie puede delegar al padre' );

$base = file_get_contents( $root . '/style.css' );
$chrome = file_get_contents( $root . '/assets/css/site-chrome.css' );
$home = file_get_contents( $root . '/assets/css/front-page.css' );
bridge_check( strpos( $base, 'var(--global-palette1' ) !== false, 'paleta Kadence aplicada a tokens base' );
bridge_check( strpos( $chrome, 'var(--global-content-width' ) !== false, 'ancho institucional de Kadence' );
// Los CTA de marca (cabecera y hero) siguen la paleta global de Kadence (palette2, amarillo), no el botón de Kadence (verde, de los formularios).
bridge_check( strpos( $chrome, 'var(--global-palette2' ) !== false, 'el CTA de cabecera usa el acento de la paleta Kadence' );
bridge_check( strpos( $chrome, 'palette-btn' ) === false, 'la cabecera no depende del botón de Kadence' );
bridge_check( strpos( $home, 'var(--global-body-font-family' ) !== false, 'la portada usa la fuente base Kadence' );
bridge_check( strpos( $home, 'var(--global-heading-font-family' ) !== false, 'la portada usa títulos Kadence' );
bridge_check( strpos( $home, 'var(--global-palette2' ) !== false, 'el CTA del hero usa el acento de la paleta Kadence' );
bridge_check( strpos( $home, 'palette-btn' ) === false, 'la portada no depende del botón de Kadence' );
echo "Kadence: integración visual y cabecera/pie reversibles correctos.\n";
