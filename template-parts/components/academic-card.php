<?php
/**
 * Tarjeta académica compartida (componente C4): oferta o seminario en los listados de Formación y por tipo.
 *
 * Args: title, url, badge, description, image_id, image_url, image_sizes, accent (#rrggbb, opcional), is_open,
 * meta [[icon, text, datetime?]], link_attrs [atributo => valor] (aria-label, data-*), icon_style.
 *
 * @package kadence-child-flacso
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$args      = isset( $args ) && is_array( $args ) ? $args : array();
$title     = (string) ( $args['title'] ?? '' );
$accent    = (string) ( $args['accent'] ?? '' );
$is_open   = ! empty( $args['is_open'] );
$image_url = (string) ( $args['image_url'] ?? '' );
$meta      = isset( $args['meta'] ) && is_array( $args['meta'] ) ? $args['meta'] : array();
$attrs     = isset( $args['link_attrs'] ) && is_array( $args['link_attrs'] ) ? $args['link_attrs'] : array();
$icon_attr = ! empty( $args['icon_style'] ) ? ' style="' . esc_attr( $args['icon_style'] ) . '"' : ' aria-hidden="true"';
?>
<a href="<?php echo esc_url( $args['url'] ?? '' ); ?>"
	class="flacso-formacion-card<?php echo '' !== $accent ? ' flacso-formacion-card--accented' : ''; ?>"
	<?php if ( '' !== $accent ) : ?>style="--offer-accent: <?php echo esc_attr( $accent ); ?>;"<?php endif; ?>
	<?php foreach ( $attrs as $name => $value ) : ?>
		<?php echo esc_attr( $name ); ?>="<?php echo esc_attr( $value ); ?>"
	<?php endforeach; ?>>
	<div class="flacso-formacion-card__media">
		<?php if ( '' !== $image_url ) : ?>
			<?php echo flacso_child_card_image_html( (int) ( $args['image_id'] ?? 0 ), $image_url, $title, (string) ( $args['image_sizes'] ?? '' ), array( 'class' => 'flacso-formacion-card__image', 'onerror' => "this.style.display='none'; if(this.nextElementSibling) this.nextElementSibling.style.display='flex';" ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- helper escapa. ?>
			<div class="flacso-formacion-card__placeholder" style="display:none;">
				<i class="bi bi-mortarboard"<?php echo $icon_attr; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escapado arriba. ?>></i>
			</div>
		<?php else : ?>
			<div class="flacso-formacion-card__placeholder">
				<i class="bi bi-mortarboard"<?php echo $icon_attr; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escapado arriba. ?>></i>
			</div>
		<?php endif; ?>
		<span class="flacso-formacion-card__badge"><?php echo esc_html( $args['badge'] ?? '' ); ?></span>
	</div>
	<div class="flacso-formacion-card__body">
		<h3 class="flacso-formacion-card__title"><?php echo esc_html( $title ); ?></h3>
		<?php if ( ! empty( $args['description'] ) ) : ?>
			<p class="flacso-formacion-card__excerpt"><?php echo esc_html( $args['description'] ); ?></p>
		<?php endif; ?>
		<div class="flacso-formacion-card__meta">
			<?php foreach ( $meta as $item ) : ?>
				<span class="flacso-formacion-card__meta-item">
					<i class="bi <?php echo esc_attr( $item['icon'] ?? '' ); ?>" aria-hidden="true"></i>
					<?php if ( ! empty( $item['datetime'] ) ) : ?>
						<time datetime="<?php echo esc_attr( $item['datetime'] ); ?>"><?php echo esc_html( $item['text'] ?? '' ); ?></time>
					<?php else : ?>
						<?php echo esc_html( $item['text'] ?? '' ); ?>
					<?php endif; ?>
				</span>
			<?php endforeach; ?>
		</div>
		<div class="flacso-formacion-card__footer">
			<span class="flacso-formacion-card__status<?php echo $is_open ? ' is-open' : ''; ?>">
				<?php echo esc_html( $is_open ? __( 'Inscripciones abiertas', 'flacso-uruguay' ) : __( 'Próxima apertura', 'flacso-uruguay' ) ); ?>
			</span>
			<span class="flacso-formacion-card__action">
				<?php esc_html_e( 'Conocer más', 'flacso-uruguay' ); ?> →
			</span>
		</div>
	</div>
</a>
