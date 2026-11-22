<?php
/**
 * Encabezado de página compartido (componente C1): migas, etiqueta, título, bajada, datos, texto largo desplegable e imagen.
 *
 * Args: breadcrumb [[label,url?]], kicker, title, title_id, lede, facts[], more_label, more_html, image_id, image_size, image_sizes, modifier (p. ej. reading).
 *
 * @package kadence-child-flacso
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$args   = isset( $args ) && is_array( $args ) ? $args : array();
$crumbs = isset( $args['breadcrumb'] ) && is_array( $args['breadcrumb'] ) ? $args['breadcrumb'] : array();
$facts  = isset( $args['facts'] ) && is_array( $args['facts'] ) ? $args['facts'] : array();
$image  = (int) ( $args['image_id'] ?? 0 );
$more   = (string) ( $args['more_html'] ?? '' );
$last   = count( $crumbs ) - 1;
?>
<header class="flacso-page-header<?php echo $image ? ' flacso-page-header--has-media' : ''; ?><?php echo ! empty( $args['modifier'] ) ? ' flacso-page-header--' . esc_attr( $args['modifier'] ) : ''; ?>">
	<div class="flacso-page-header__body">
		<?php if ( $crumbs ) : ?>
			<nav class="flacso-page-header__breadcrumb" aria-label="<?php esc_attr_e( 'Navegación', 'flacso-uruguay' ); ?>">
				<?php foreach ( $crumbs as $i => $crumb ) : ?>
					<?php if ( $i === $last || empty( $crumb['url'] ) ) : ?>
						<span<?php echo $i === $last ? ' aria-current="page"' : ''; ?>><?php echo esc_html( $crumb['label'] ); ?></span>
					<?php else : ?>
						<a href="<?php echo esc_url( $crumb['url'] ); ?>"><?php echo esc_html( $crumb['label'] ); ?></a>
					<?php endif; ?>
					<?php if ( $i < $last ) : ?>
						<span class="flacso-page-header__breadcrumb-sep" aria-hidden="true">/</span>
					<?php endif; ?>
				<?php endforeach; ?>
			</nav>
		<?php endif; ?>

		<?php if ( ! empty( $args['kicker'] ) ) : ?>
			<p class="flacso-page-header__kicker"><?php echo esc_html( $args['kicker'] ); ?></p>
		<?php endif; ?>

		<h1 class="flacso-page-header__title"<?php echo ! empty( $args['title_id'] ) ? ' id="' . esc_attr( $args['title_id'] ) . '"' : ''; ?>><?php echo esc_html( $args['title'] ?? '' ); ?></h1>

		<?php if ( ! empty( $args['lede'] ) ) : ?>
			<p class="flacso-page-header__lede"><?php echo esc_html( $args['lede'] ); ?></p>
		<?php endif; ?>

		<?php if ( $facts ) : ?>
			<ul class="flacso-page-header__facts">
				<?php foreach ( $facts as $fact ) : ?>
					<li><?php echo esc_html( $fact ); ?></li>
				<?php endforeach; ?>
			</ul>
		<?php endif; ?>

		<?php if ( '' !== $more ) : ?>
			<details class="flacso-page-header__more">
				<summary><?php echo esc_html( $args['more_label'] ?? __( 'Más información', 'flacso-uruguay' ) ); ?></summary>
				<div class="flacso-page-header__more-body"><?php echo wp_kses_post( $more ); ?></div>
			</details>
		<?php endif; ?>
	</div>

	<?php if ( $image ) : ?>
		<div class="flacso-page-header__media" aria-hidden="true">
			<?php
			echo wp_get_attachment_image(
				$image,
				(string) ( $args['image_size'] ?? 'flacso-seminario-4x5' ),
				false,
				array(
					'class'         => 'flacso-page-header__image',
					'alt'           => '',
					'loading'       => 'eager',
					'decoding'      => 'async',
					'fetchpriority' => 'high',
					'sizes'         => (string) ( $args['image_sizes'] ?? '(max-width: 767px) 92vw, 420px' ),
				)
			);
			?>
		</div>
	<?php endif; ?>
</header>
