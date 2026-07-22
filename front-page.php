<?php
/**
 * The template for displaying the front page.
 *
 * @package kadence-child-flacso
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

get_header();

/**
 * Hook for displaying content before the main area.
 */
do_action( 'kadence_before_content' );
?>

	<?php if ( function_exists( 'flacso_homepage_builder_render' ) ) : ?>
		<?php echo flacso_homepage_builder_render(); ?>
	<?php else : ?>
	<main id="main" class="site-main flacso-home-layout" role="main">
		<div class="flacso-main-page flacso-homepage-completa">
			<?php
			/**
			 * FLACSO Homepage Sections
			 * Las secciones se cargan directamente desde el plugin.
			 * Para cambiar el orden, simplemente mueve estas líneas.
			 */

			$renderers = [
				'hero'               => 'flacso_section_hero_render',
				'festejos'           => 'flacso_section_festejos_render',
				'eventos'            => 'flacso_section_eventos_render',
				'seminarios'         => 'flacso_section_seminarios_proximos_render',
				// 'novedades_destacadas' (si existía) solía combinarse con novedades. Ahora se carga secuencial.
				'novedades'          => 'flacso_section_novedades_render',
				'novedades_busqueda' => 'flacso_section_novedades_busqueda_render',
				'quienes'            => 'flacso_section_quienes_somos_render',
				'instagram'          => 'flacso_section_instagram_render',
				'posgrados'          => 'flacso_section_oferta_educativa_render',
				'mailing'            => 'flacso_section_mailing_render',
				'congreso'           => 'flacso_section_congreso_render',
				'contacto'           => 'flacso_section_contacto_render',
			];

			foreach ( $renderers as $key => $function_name ) {
				if ( function_exists( $function_name ) ) {
					$content = call_user_func( $function_name );
					if ( ! empty( $content ) ) {
						$is_bleed_surface = $key === 'hero';
						$surface_variant  = $is_bleed_surface ? 'flacso-home-block__surface--bleed' : 'flacso-home-block__surface--card';
						?>
						<article class="flacso-home-block flacso-home-block--<?php echo esc_attr( $key ); ?>" data-section-key="<?php echo esc_attr( $key ); ?>">
							<div class="flacso-home-block__surface <?php echo esc_attr( $surface_variant ); ?> flacso-home-block__surface--<?php echo esc_attr( $key ); ?>">
								<?php echo $content; ?>
							</div>
						</article>
						<?php
					}
				}
			}
			?>
		</div>
	</main><!-- #main -->
	<?php endif; ?>
</div><!-- #primary -->

<?php
/**
 * Hook for displaying content after the main area.
 */
do_action( 'kadence_after_content' );

get_footer();
