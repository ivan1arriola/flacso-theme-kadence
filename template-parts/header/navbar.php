<?php
/**
 * Navegación institucional.
 *
 * @package kadence-child-flacso
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$header_cta = flacso_child_get_header_cta();
?>
<header id="masthead" class="flacso-site-header" data-flacso-header>
	<div class="flacso-topbar">
		<div class="flacso-chrome-container flacso-topbar__inner">
			<nav class="flacso-topbar__links" aria-label="<?php esc_attr_e( 'Accesos institucionales', 'kadence-child-flacso' ); ?>">
				<?php
				wp_nav_menu(
					array(
						'theme_location' => 'header_utility',
						'container'      => false,
						'menu_class'     => 'flacso-topbar__menu',
						'fallback_cb'    => 'flacso_child_utility_menu_fallback',
						'depth'          => 1,
					)
				);
				?>
			</nav>
		</div>
	</div>

	<div class="flacso-navbar">
		<div class="flacso-chrome-container flacso-navbar__inner">
			<div class="flacso-navbar__brand">
				<?php if ( has_custom_logo() ) : ?>
					<?php the_custom_logo(); ?>
				<?php else : ?>
					<a class="flacso-navbar__site-name" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php bloginfo( 'name' ); ?></a>
				<?php endif; ?>
			</div>

			<nav id="flacso-primary-navigation" class="flacso-navbar__navigation" aria-label="<?php esc_attr_e( 'Navegación principal', 'kadence-child-flacso' ); ?>" data-flacso-menu>
				<button class="flacso-navbar__drawer-close" type="button" data-flacso-menu-close>
					<span><?php esc_html_e( 'Menú', 'kadence-child-flacso' ); ?></span>
					<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6 6l12 12M18 6 6 18"></path></svg>
					<span class="screen-reader-text"><?php esc_html_e( 'Cerrar menú', 'kadence-child-flacso' ); ?></span>
				</button>
				<?php
				wp_nav_menu(
					array(
						'theme_location' => 'primary',
						'container'      => false,
						'menu_class'     => 'flacso-navbar__menu',
						'fallback_cb'    => 'flacso_child_primary_menu_fallback',
						'depth'          => 2,
					)
				);
				?>
				<?php // En móvil el botón de la barra se oculta por espacio; se repite en el menú lateral (ver site-chrome.css). ?>
				<a class="flacso-navbar__cta" href="<?php echo esc_url( $header_cta['url'] ); ?>"<?php echo '_blank' === $header_cta['target'] ? ' target="_blank" rel="noopener noreferrer"' : ''; ?>><?php echo esc_html( $header_cta['label'] ); ?></a>
			</nav>
			<button class="flacso-navbar__overlay" type="button" tabindex="-1" aria-hidden="true" data-flacso-menu-overlay></button>

			<div class="flacso-navbar__actions">
				<button class="flacso-navbar__icon-button" type="button" aria-expanded="false" aria-controls="flacso-header-search" data-flacso-search-toggle>
					<svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="11" cy="11" r="7"></circle><path d="m20 20-4-4"></path></svg>
					<span class="screen-reader-text"><?php esc_html_e( 'Buscar', 'kadence-child-flacso' ); ?></span>
				</button>
				<a class="flacso-navbar__cta" href="<?php echo esc_url( $header_cta['url'] ); ?>"<?php echo '_blank' === $header_cta['target'] ? ' target="_blank" rel="noopener noreferrer"' : ''; ?>><?php echo esc_html( $header_cta['label'] ); ?></a>
				<button class="flacso-navbar__menu-button" type="button" aria-expanded="false" aria-controls="flacso-primary-navigation" data-flacso-menu-toggle>
					<span aria-hidden="true"></span><span aria-hidden="true"></span><span aria-hidden="true"></span>
					<span class="screen-reader-text"><?php esc_html_e( 'Abrir menú', 'kadence-child-flacso' ); ?></span>
				</button>
			</div>
		</div>

		<div id="flacso-header-search" class="flacso-header-search" hidden data-flacso-search>
			<div class="flacso-chrome-container">
				<form role="search" method="get" class="flacso-header-search__form" action="<?php echo esc_url( home_url( '/' ) ); ?>">
					<label class="screen-reader-text" for="flacso-header-search-field"><?php esc_html_e( 'Buscar en FLACSO Uruguay', 'kadence-child-flacso' ); ?></label>
					<svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="11" cy="11" r="7"></circle><path d="m20 20-4-4"></path></svg>
					<input id="flacso-header-search-field" type="search" name="s" value="<?php echo esc_attr( get_search_query() ); ?>" placeholder="<?php esc_attr_e( 'Buscar formación, noticias, eventos…', 'kadence-child-flacso' ); ?>">
					<button type="submit"><?php esc_html_e( 'Buscar', 'kadence-child-flacso' ); ?></button>
				</form>
			</div>
		</div>
	</div>
</header>
