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

echo flacso_home_render(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- las plantillas escapan cada valor.
?>
</div><!-- #primary -->

<?php
do_action( 'kadence_after_content' );
get_footer();
