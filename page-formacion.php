<?php
/**
 * Template para la página de Oferta Académica (/formacion/).
 *
 * Este template es cargado por el plugin (class-cpt-oferta-academica.php)
 * cuando el usuario visita /formacion/. Los datos se obtienen del plugin
 * a través de Oferta_Renderer::get_oferta_pagina_data().
 *
 * @package kadence-child-flacso
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Obtener datos del plugin
$data = class_exists( 'Oferta_Renderer' )
	? Oferta_Renderer::get_oferta_pagina_data( [
		'heroTitle'   => 'Oferta Académica',
		'heroImageId' => 22981,
	] )
	: [];

if ( empty( $data ) ) {
	// Sin plugin activo — mostrar un mensaje mínimo
	get_header();
	echo '<div class="container" style="padding:60px 0"><p>' . esc_html__( 'La oferta académica no está disponible en este momento.', 'kadence-child-flacso' ) . '</p></div>';
	get_footer();
	return;
}

// Extraer variables para el template
$hero_title      = $data['hero_title'];
$hero_subtitle   = $data['hero_subtitle'];
$hero_image      = $data['hero_image'];
$terms           = $data['terms'];
$link_seminarios = $data['link_seminarios'];
$link_convenios  = $data['link_convenios'];
$sections        = $data['sections'];

get_header();
?>

<!-- Hero: Oferta Académica -->
<section class="flacso-oferta-hero flacso-oferta-hero--full" style="--flacso-oferta-hero-image: <?php echo $hero_image ? 'url(' . esc_url( $hero_image ) . ')' : 'none'; ?>;">
	<div class="container">
		<div class="flacso-oferta-hero__content text-center">
			<h1 class="flacso-oferta-hero__title mb-3"><?php echo esc_html( $hero_title ); ?></h1>
			<p class="flacso-oferta-hero__subtitle mb-4"><?php echo esc_html( $hero_subtitle ); ?></p>
			<div class="flacso-oferta-hero__actions" role="navigation" aria-label="<?php esc_attr_e( 'Navegación de la oferta académica', 'kadence-child-flacso' ); ?>">
				<?php foreach ( $sections as $section_data ) : ?>
					<?php if ( ! empty( $section_data['term_link'] ) ) : ?>
						<a class="flacso-oferta-hero__btn flacso-oferta-hero__btn--solid" href="<?php echo esc_url( $section_data['term_link'] ); ?>">
							<?php echo esc_html( $section_data['term']->name ); ?>
						</a>
					<?php endif; ?>
				<?php endforeach; ?>
				<a class="flacso-oferta-hero__btn flacso-oferta-hero__btn--solid" href="<?php echo esc_url( $link_seminarios ); ?>">
					<?php esc_html_e( 'Seminarios', 'kadence-child-flacso' ); ?>
				</a>
				<a class="flacso-oferta-hero__btn flacso-oferta-hero__btn--convenios" href="<?php echo esc_url( $link_convenios ); ?>">
					<?php esc_html_e( 'Convenios', 'kadence-child-flacso' ); ?>
				</a>
			</div>
		</div>
	</div>
</section>

<!-- Secciones de categorías de oferta -->
<section class="flacso-oferta-body">
	<div class="container">
		<?php
		foreach ( $sections as $section_data ) :
			$term  = $section_data['term'];
			$query = new WP_Query( $section_data['query_args'] );
			?>
			<div class="flacso-oferta-section" id="<?php echo esc_attr( $term->slug ); ?>">
				<div class="d-flex justify-content-between align-items-center mb-3 gap-3">
					<div class="text-center w-100">
						<h2 class="flacso-oferta-section__title mb-0"><?php echo esc_html( $term->name ); ?></h2>
					</div>
				</div>

				<?php if ( $query->have_posts() ) : ?>
					<?php
					$rendered_cards = 0;
					ob_start();
					while ( $query->have_posts() ) {
						$query->the_post();
						if ( Oferta_Renderer::render_program_card( get_the_ID(), $term ) ) {
							$rendered_cards++;
						}
					}
					$cards_markup = ob_get_clean();
					wp_reset_postdata();
					?>
					<?php if ( $rendered_cards > 0 ) : ?>
						<div class="row g-4">
							<?php echo $cards_markup; ?>
						</div>
					<?php else : ?>
						<div class="alert alert-info mb-4">
							<?php esc_html_e( 'No hay programas disponibles en esta categoría.', 'kadence-child-flacso' ); ?>
						</div>
					<?php endif; ?>
				<?php else : ?>
					<div class="alert alert-info mb-4">
						<?php esc_html_e( 'No hay programas disponibles en esta categoría.', 'kadence-child-flacso' ); ?>
					</div>
				<?php endif; ?>
			</div>
		<?php endforeach; ?>

		<!-- Sección: Seminarios -->
		<div class="flacso-oferta-section" id="seminarios">
			<div class="d-flex justify-content-between align-items-center mb-3 gap-3">
				<div class="text-center w-100">
					<h2 class="flacso-oferta-section__title mb-0"><?php esc_html_e( 'Seminarios', 'kadence-child-flacso' ); ?></h2>
				</div>
			</div>
			<?php echo $data['seminarios_html']; ?>
			<div class="flacso-oferta-section__actions">
				<a class="flacso-oferta-section__btn flacso-oferta-section__btn--primary" href="<?php echo esc_url( $link_seminarios ); ?>">
					<?php esc_html_e( 'Ver todos los seminarios abiertos', 'kadence-child-flacso' ); ?>
				</a>
				<a class="flacso-oferta-section__btn flacso-oferta-section__btn--outline" href="https://flacso.edu.uy/preguntas-frecuentes/">
					<?php esc_html_e( 'Preguntas frecuentes', 'kadence-child-flacso' ); ?>
				</a>
			</div>
		</div>
	</div>
</section>

<!-- Formulario flotante de consulta -->
<?php echo $data['floating_form_html']; ?>

<!-- Scripts: smooth scroll, countdowns, Meta Pixel -->

<?php
get_footer();
