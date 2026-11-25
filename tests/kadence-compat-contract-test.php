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
function add_action( $hook, $callback, $priority = 10 ) { $GLOBALS['bridge_actions'][ $hook ] = $callback; }
function add_filter( $hook, $callback, $priority = 10 ) { $GLOBALS['bridge_actions'][ $hook ] = $callback; }
function apply_filters( $hook, $value ) { return $value; }
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
bridge_check( ! flacso_child_modular_typography_enabled(), 'la escala nueva es reversible y no cambia el sitio sin activación' );
bridge_check( isset( $customizer->settings['flacso_modular_typography'] ), 'control para la escala modular' );
$font = array( 'size' => array( 'desktop' => 16, 'tablet' => 15, 'mobile' => 14 ), 'family' => 'Roboto', 'color' => 'palette4', 'weight' => '700' );
$heading = flacso_child_typography_font( $font, $font, 1 );
bridge_check( $heading['size']['desktop'] === 48.8281 && $heading['size']['mobile'] === 42.7246, 'H1 usa cinco pasos y respeta base móvil' );
bridge_check( $heading['weight'] === '600' && $heading['variant'] === '600' && $heading['lineHeight']['desktop'] === 1.1, 'H1 semibold, interlineado compacto' );
bridge_check( $heading['family'] === 'Roboto' && $heading['color'] === 'palette4', 'no cambia familia o color' );
$body = flacso_child_typography_font( $font, $font );
bridge_check( $body['weight'] === '400' && $body['lineHeight']['desktop'] === 1.6, 'cuerpo regular' );
bridge_check( flacso_child_typography_font( $font, $font, 6 )['lineHeight']['desktop'] === 1.35, 'interlineado H6' );
$invalid = array( 'size' => array( 'desktop' => 'clamp(1rem, 2vw, 2rem)' ) );
bridge_check( flacso_child_typography_font( $font, $invalid, 1 ) === $font, 'formato no soportado conserva opciones' );
bridge_check( flacso_child_typography_tokens( $invalid ) === '', 'formato no soportado no emite CSS' );
$GLOBALS['bridge_mods']['flacso_modular_typography'] = true;
$GLOBALS['bridge_mods']['base_font'] = $font;
$defaults = flacso_child_typography_defaults( array( 'base_font' => $font, 'h1_font' => $font, 'h6_font' => $font ) );
bridge_check( $defaults['h1_font']['size']['desktop'] === 48.8281 && $defaults['h6_font']['size']['desktop'] === 16.0, 'defaults nativos derivados' );
$filtered = $GLOBALS['bridge_actions']['theme_mod_h2_font']( $font );
bridge_check( $filtered['size']['desktop'] === 39.0625, 'theme_mod de H2 derivado de base guardada' );
bridge_check( $GLOBALS['bridge_mods']['base_font'] === $font, 'no escribe opciones persistidas' );
$tokens = flacso_child_typography_tokens( $font );
bridge_check( strpos( $tokens, '--flacso-type-5:48.8281px' ) !== false && strpos( $tokens, '--flacso-type-small:12.8px' ) !== false, 'tokens de escala' );
bridge_check( strpos( $tokens, '@media(max-width:767px)' ) !== false, 'tokens responsivos' );
bridge_check( in_array( 'flacso-modular-type', flacso_child_typography_body_class( array() ), true ), 'clase solo para modo activo compatible' );
$GLOBALS['bridge_mods']['flacso_modular_typography'] = false;
bridge_check( $GLOBALS['bridge_actions']['theme_mod_h2_font']( $font ) === $font, 'desactivar devuelve configuración nativa' );
bridge_check( flacso_child_typography_body_class( array() ) === array(), 'sin clase en modo nativo' );
echo "Kadence: integración visual, escala modular y cabecera/pie reversibles correctos.\n";
