<?php
/**
 * Seminarios vinculados a una oferta académica.
 *
 * @package kadence-child-flacso
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$post_id    = isset( $args['post_id'] ) ? absint( $args['post_id'] ) : 0;
$seminarios = isset( $args['seminarios'] ) && is_array( $args['seminarios'] ) ? $args['seminarios'] : array();

if ( ! $post_id || empty( $seminarios ) ) {
	return;
}
?>
<section class="flacso-oa-seminarios-section" aria-labelledby="flacso-seminarios-vinculados-title">
	<div class="flacso-oa-container">
		<div class="flacso-oa-seminarios-heading">
			<h2 id="flacso-seminarios-vinculados-title"><?php esc_html_e( 'Seminarios vinculados', 'flacso-uruguay' ); ?></h2>
			<a href="<?php echo esc_url( trailingslashit( get_permalink( $post_id ) ) . 'seminarios/' ); ?>" class="flacso-oa-link-btn">
				<?php esc_html_e( 'Ver todos', 'flacso-uruguay' ); ?>
			</a>
		</div>

		<div class="flacso-oa-seminarios-grid">
			<?php foreach ( array_slice( $seminarios, 0, 3 ) as $seminario ) : ?>
				<?php
				$seminario_url    = ! empty( $seminario['permalink'] ) ? $seminario['permalink'] : '#';
				$seminario_titulo = ! empty( $seminario['titulo'] ) ? $seminario['titulo'] : __( 'Seminario', 'flacso-uruguay' );
				$fecha_inicio     = ! empty( $seminario['fecha_inicio'] ) ? $seminario['fecha_inicio'] : '';
				$fecha_legible    = $fecha_inicio ? date_i18n( get_option( 'date_format' ), strtotime( $fecha_inicio ) ) : '';
				?>
				<article class="flacso-oa-seminario-card">
					<h3><a href="<?php echo esc_url( $seminario_url ); ?>"><?php echo esc_html( $seminario_titulo ); ?></a></h3>
					<?php if ( $fecha_legible ) : ?>
						<p class="flacso-oa-seminario-card__date"><?php echo esc_html( $fecha_legible ); ?></p>
					<?php endif; ?>
				</article>
			<?php endforeach; ?>
		</div>
	</div>
</section>
