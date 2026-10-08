<?php
/**
 * Hero de la portada.
 *
 * La presentación pertenece al tema. El plugin aporta únicamente los datos
 * editables cuando su módulo de portada está disponible.
 *
 * @package kadence-child-flacso
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'flacso_section_hero_render' ) ) {
	function flacso_section_hero_render() {
		$fallback = array(
			'kicker'          => __( 'FLACSO Uruguay', 'kadence-child-flacso' ),
			'title'           => flacso_theme_brand_slogan(),
			'subtitle'        => __( 'Posgrados, diplomas y seminarios con perspectiva latinoamericana.', 'kadence-child-flacso' ),
			'background_image' => '',
			'show_buttons'    => true,
			'buttons'         => array(),
		);
		$settings = class_exists( 'Flacso_Main_Page_Settings' )
			? Flacso_Main_Page_Settings::get_section( 'hero' )
			: array();
		$settings = wp_parse_args( is_array( $settings ) ? $settings : array(), $fallback );

		$title    = flacso_theme_brand_slogan_replace_legacy( $settings['title'] );
		$subtitle = flacso_theme_brand_slogan_replace_legacy( $settings['subtitle'] );
		$slogan   = flacso_theme_brand_slogan();
		if ( $title === $slogan && $subtitle === $slogan ) {
			$subtitle = '';
		}
		$show_slogan = $title !== $slogan && $subtitle !== $slogan;
		$kicker   = trim( wp_strip_all_tags( (string) $settings['kicker'] ) );
		$image    = esc_url( (string) $settings['background_image'] );
		$buttons  = array();

		if ( ! empty( $settings['buttons'] ) && is_array( $settings['buttons'] ) ) {
			foreach ( $settings['buttons'] as $button ) {
				$label = trim( wp_strip_all_tags( (string) ( $button['label'] ?? '' ) ) );
				$url   = class_exists( 'Flacso_Main_Page_Settings' )
					? Flacso_Main_Page_Settings::normalize_url_output( (string) ( $button['url'] ?? '' ) )
					: esc_url( (string) ( $button['url'] ?? '' ) );
				$style = sanitize_key( (string) ( $button['style'] ?? 'primary' ) );

				if ( empty( $button['enabled'] ) || '' === $label || '' === $url ) {
					continue;
				}
				$buttons[] = array(
					'label' => $label,
					'url'   => $url,
					'style' => in_array( $style, array( 'primary', 'outline', 'light', 'ghost' ), true ) ? $style : 'primary',
				);
			}
		}

		$buttons = array_slice( $buttons, 0, 3 );
		$floating = array();
		foreach ( array( 'primary', 'secondary' ) as $bubble_key ) {
			$label = trim( wp_strip_all_tags( (string) ( $settings[ 'bubble_' . $bubble_key . '_label' ] ?? '' ) ) );
			$url   = class_exists( 'Flacso_Main_Page_Settings' )
				? Flacso_Main_Page_Settings::normalize_url_output( (string) ( $settings[ 'bubble_' . $bubble_key . '_url' ] ?? '' ) )
				: esc_url( (string) ( $settings[ 'bubble_' . $bubble_key . '_url' ] ?? '' ) );
			$style_key = sanitize_key( (string) ( $settings[ 'bubble_' . $bubble_key . '_style' ] ?? $bubble_key ) );

			if ( ! empty( $settings[ 'bubble_' . $bubble_key . '_enabled' ] ) && $label && $url ) {
				$floating[] = array(
					'label' => $label,
					'url'   => $url,
					'style' => in_array( $style_key, array( 'primary', 'outline', 'light', 'ghost' ), true ) ? $style_key : 'outline',
				);
			}
		}
		$year    = '';
		if ( preg_match( '/\b(20\d{2})\b/', $title, $matches ) ) {
			$year = $matches[1];
		}
		$title_html = esc_html( $title );
		if ( $year ) {
			$title_html = preg_replace(
				'/\b' . preg_quote( $year, '/' ) . '\b/',
				'<span class="flacso-front-hero__accent">$0</span>',
				$title_html,
				1
			);
		}

		$style = $image ? '--flacso-front-hero-image:url(' . esc_url( $image ) . ');' : '';

		ob_start();
		?>
		<section class="flacso-front-hero" style="<?php echo esc_attr( $style ); ?>" data-flacso-front-hero>
			<div class="flacso-content-shell flacso-front-hero__grid">
				<div class="flacso-front-hero__content">
					<?php if ( $kicker ) : ?>
						<p class="flacso-front-hero__kicker"><?php echo esc_html( $kicker ); ?></p>
					<?php endif; ?>
					<h1 class="flacso-front-hero__title"><?php echo wp_kses( $title_html, array( 'span' => array( 'class' => true ) ) ); ?></h1>
					<?php if ( $show_slogan ) : ?>
						<p class="flacso-front-hero__slogan"><?php echo esc_html( $slogan ); ?></p>
					<?php endif; ?>
					<?php if ( $subtitle ) : ?>
						<p class="flacso-front-hero__subtitle"><?php echo esc_html( $subtitle ); ?></p>
					<?php endif; ?>
					<?php if ( ! empty( $settings['show_buttons'] ) && $buttons ) : ?>
						<div class="flacso-front-hero__actions">
							<?php foreach ( $buttons as $button ) : ?>
								<a class="flacso-front-hero__button flacso-front-hero__button--<?php echo esc_attr( $button['style'] ); ?>" href="<?php echo esc_url( $button['url'] ); ?>">
									<span><?php echo esc_html( $button['label'] ); ?></span>
									<svg viewBox="0 0 24 24" aria-hidden="true"><path d="m9 18 6-6-6-6"></path></svg>
								</a>
							<?php endforeach; ?>
						</div>
					<?php endif; ?>
				</div>

				<div class="flacso-front-hero__art" aria-hidden="true">
					<div class="flacso-front-hero__orbit"></div>
					<div class="flacso-front-hero__globe">
						<img class="flacso-front-hero__world" src="<?php echo esc_url( get_stylesheet_directory_uri() . '/assets/img/flacso-world-uruguay.svg' ); ?>" alt="" width="1000" height="1000">
					</div>
					<?php if ( $year ) : ?>
						<div class="flacso-front-hero__year"><strong><?php echo esc_html( $year ); ?></strong><span><?php esc_html_e( 'Inscripciones', 'kadence-child-flacso' ); ?></span></div>
					<?php endif; ?>
				</div>
			</div>
		</section>
		<?php if ( $floating ) : ?>
			<aside class="flacso-front-hero-fab" data-flacso-front-hero-fab aria-label="<?php esc_attr_e( 'Accesos rápidos', 'kadence-child-flacso' ); ?>">
				<?php foreach ( $floating as $floating_link ) : ?>
					<a class="flacso-front-hero-fab__link flacso-front-hero-fab__link--<?php echo esc_attr( $floating_link['style'] ); ?>" href="<?php echo esc_url( $floating_link['url'] ); ?>">
						<span class="flacso-front-hero-fab__icon" aria-hidden="true">
							<?php if ( 'primary' === $floating_link['style'] ) : ?>
								<svg viewBox="0 0 24 24"><path d="M3 7.5 12 3l9 4.5-9 4.5-9-4.5Z"></path><path d="M6.5 9.25V14c0 1.65 2.46 3 5.5 3s5.5-1.35 5.5-3V9.25"></path><path d="M21 8v6"></path></svg>
							<?php else : ?>
								<svg viewBox="0 0 24 24"><path d="M20 11.5a7.5 7.5 0 0 1-8 7.48 8.5 8.5 0 0 1-3.5-.98L4 20l1.4-4.2A7.5 7.5 0 1 1 20 11.5Z"></path><path d="M9 9.5a3 3 0 0 1 6 0c0 2-3 2-3 4"></path><path d="M12 16h.01"></path></svg>
							<?php endif; ?>
						</span>
						<span><?php echo esc_html( $floating_link['label'] ); ?></span>
						<svg class="flacso-front-hero-fab__arrow" viewBox="0 0 24 24" aria-hidden="true"><path d="m9 18 6-6-6-6"></path></svg>
					</a>
				<?php endforeach; ?>
			</aside>
		<?php endif; ?>
		<?php
		return ob_get_clean();
	}
}
