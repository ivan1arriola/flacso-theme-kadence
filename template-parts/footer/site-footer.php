<?php
/**
 * Pie institucional.
 *
 * @package kadence-child-flacso
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$phone   = get_theme_mod( 'flacso_contacto_telefono', '2481 7459' );
$email   = get_theme_mod( 'flacso_contacto_email', 'secretaria@flacso.edu.uy' );
$address = get_theme_mod( 'flacso_contacto_direccion', '8 de Octubre 2882, CP 11600, Montevideo' );

$fallback_menus = array(
	'footer_formacion' => array(
		'/formacion/maestrias/'         => __( 'Maestrías', 'kadence-child-flacso' ),
		'/formacion/especializaciones/' => __( 'Especializaciones', 'kadence-child-flacso' ),
		'/formacion/diplomas/'          => __( 'Diplomas', 'kadence-child-flacso' ),
		'/formacion/seminarios/'        => __( 'Seminarios', 'kadence-child-flacso' ),
	),
	'footer_institucional' => array(
		'/sobre-nosotros/'                  => __( 'Quiénes somos', 'kadence-child-flacso' ),
		'/institucional/investigaciones/'   => __( 'Investigaciones', 'kadence-child-flacso' ),
		'/convenios/'                       => __( 'Convenios', 'kadence-child-flacso' ),
		'/contactos/'                       => __( 'Contacto', 'kadence-child-flacso' ),
	),
	'footer_accesos' => array(
		'https://virtual.flacso.edu.uy/'        => __( 'Campus Virtual', 'kadence-child-flacso' ),
		'https://agenda.flacso.edu.uy/'         => __( 'Agenda Web', 'kadence-child-flacso' ),
		'/preguntas-frecuentes/'                => __( 'Preguntas frecuentes', 'kadence-child-flacso' ),
		'https://www.instagram.com/flacsouruguay/' => 'Instagram',
	),
);

$render_footer_menu = static function ( $location ) use ( $fallback_menus ) {
	if ( has_nav_menu( $location ) ) {
		wp_nav_menu(
			array(
				'theme_location' => $location,
				'container'      => false,
				'menu_class'     => 'flacso-footer__menu',
				'depth'          => 1,
				'fallback_cb'    => false,
			)
		);
		return;
	}

	echo '<ul class="flacso-footer__menu">';
	foreach ( $fallback_menus[ $location ] as $url => $label ) {
		$href = 0 === strpos( $url, 'http' ) ? $url : home_url( $url );
		printf( '<li><a href="%1$s">%2$s</a></li>', esc_url( $href ), esc_html( $label ) );
	}
	echo '</ul>';
};
?>
<footer id="colophon" class="flacso-footer">
	<div class="flacso-chrome-container">
		<div class="flacso-footer__grid">
			<div class="flacso-footer__identity">
				<a class="flacso-footer__brand" href="<?php echo esc_url( home_url( '/' ) ); ?>" aria-label="<?php esc_attr_e( 'FLACSO Uruguay — Inicio', 'kadence-child-flacso' ); ?>">
					<?php
					$logo_id = get_theme_mod( 'custom_logo' );
					if ( $logo_id ) {
						echo wp_get_attachment_image( $logo_id, 'medium', false, array( 'alt' => get_bloginfo( 'name' ) ) );
					} else {
						echo '<strong>' . esc_html( get_bloginfo( 'name' ) ) . '</strong>';
					}
					?>
				</a>
				<p><?php esc_html_e( 'Formación, investigación y cooperación académica con perspectiva latinoamericana.', 'kadence-child-flacso' ); ?></p>
				<ul class="flacso-footer__contact">
					<li><a href="mailto:<?php echo esc_attr( sanitize_email( $email ) ); ?>"><?php echo esc_html( antispambot( $email ) ); ?></a></li>
					<li><a href="tel:<?php echo esc_attr( preg_replace( '/[^0-9+]/', '', $phone ) ); ?>"><?php echo esc_html( $phone ); ?></a></li>
					<li><?php echo esc_html( $address ); ?></li>
				</ul>
			</div>

			<div><h2><?php esc_html_e( 'Formación', 'kadence-child-flacso' ); ?></h2><?php $render_footer_menu( 'footer_formacion' ); ?></div>
			<div><h2><?php esc_html_e( 'Institucional', 'kadence-child-flacso' ); ?></h2><?php $render_footer_menu( 'footer_institucional' ); ?></div>
			<div><h2><?php esc_html_e( 'Accesos', 'kadence-child-flacso' ); ?></h2><?php $render_footer_menu( 'footer_accesos' ); ?></div>
		</div>

		<div class="flacso-footer__bottom">
			<p>
				© <?php echo esc_html( wp_date( 'Y' ) ); ?> <?php bloginfo( 'name' ); ?>
				· <?php esc_html_e( 'Sitio desarrollado por', 'kadence-child-flacso' ); ?>
				<a href="https://www.linkedin.com/in/ivan1arriola/" target="_blank" rel="noopener author">Iván Arriola</a>
			</p>
			<nav aria-label="<?php esc_attr_e( 'Enlaces legales', 'kadence-child-flacso' ); ?>">
				<a href="<?php echo esc_url( get_privacy_policy_url() ?: home_url( '/politica-de-privacidad/' ) ); ?>"><?php esc_html_e( 'Privacidad', 'kadence-child-flacso' ); ?></a>
				<a href="<?php echo esc_url( home_url( '/contactos/' ) ); ?>"><?php esc_html_e( 'Contacto', 'kadence-child-flacso' ); ?></a>
			</nav>
		</div>
	</div>
</footer>
