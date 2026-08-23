<?php
/**
 * The template for displaying the front page.
 *
 * @package kadence-child-flacso
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

do_action( 'kadence_before_content' );
?>

	<?php if ( function_exists( 'flacso_homepage_builder_render' ) ) : ?>
		<?php echo flacso_homepage_builder_render(); ?>
	<?php else : ?>
	<main id="main" class="site-main flacso-home-layout" role="main">
		<div class="flacso-main-page flacso-homepage-completa">
			<?php
			/**
			 * Fallback de portada.
			 *
			 * El plugin es la fuente de verdad para orden y visibilidad. Este mapa
			 * únicamente relaciona las claves canónicas con sus renderers para que
			 * el tema pueda degradar de forma segura si el builder completo no está
			 * disponible.
			 */
			$renderers = [
				'hero'       => 'flacso_section_hero_render',
				'festejos'   => 'flacso_section_festejos_render',
				'eventos'    => 'flacso_section_eventos_render',
				'seminarios' => 'flacso_section_seminarios_proximos_render',
				'novedades'  => 'flacso_section_novedades_render',
				'quienes'    => 'flacso_section_quienes_somos_render',
				'instagram'  => 'flacso_section_instagram_render',
				'posgrados'  => 'flacso_section_oferta_educativa_render',
				'mailing'    => 'flacso_section_mailing_render',
				'congreso'   => 'flacso_section_congreso_render',
				'contacto'   => 'flacso_section_contacto_render',
			];

			$order = array_keys( $renderers );
			if ( class_exists( 'Flacso_Main_Page_Settings' ) && method_exists( 'Flacso_Main_Page_Settings', 'get_homepage_section_order' ) ) {
				$configured_order = Flacso_Main_Page_Settings::get_homepage_section_order();
				if ( is_array( $configured_order ) && ! empty( $configured_order ) ) {
					$order = array_values(
						array_unique(
							array_merge( $configured_order, array_keys( $renderers ) )
						)
					);
				}
			}

			foreach ( $order as $key ) {
				if ( ! isset( $renderers[ $key ] ) ) {
					continue;
				}

				if (
					class_exists( 'Flacso_Main_Page_Settings' )
					&& method_exists( 'Flacso_Main_Page_Settings', 'is_section_visible' )
					&& ! Flacso_Main_Page_Settings::is_section_visible( $key )
				) {
					continue;
				}

				$function_name = $renderers[ $key ];
				if ( ! function_exists( $function_name ) ) {
					continue;
				}

				$content = call_user_func( $function_name );
				if ( empty( $content ) ) {
					continue;
				}

				$is_bleed_surface = 'hero' === $key;
				$surface_variant  = $is_bleed_surface
					? 'flacso-home-block__surface--bleed'
					: 'flacso-home-block__surface--card';
				?>
				<article class="flacso-home-block flacso-home-block--<?php echo esc_attr( $key ); ?>" data-section-key="<?php echo esc_attr( $key ); ?>">
					<div class="flacso-home-block__surface <?php echo esc_attr( $surface_variant ); ?> flacso-home-block__surface--<?php echo esc_attr( $key ); ?>">
						<?php echo $content; ?>
					</div>
				</article>
				<?php
			}
			?>
		</div>
	</main><!-- #main -->
	<?php endif; ?>
</div><!-- #primary -->

<?php
do_action( 'kadence_after_content' );
get_footer();
