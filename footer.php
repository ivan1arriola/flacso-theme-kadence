<?php
/**
 * The template for displaying the footer
 *
 * Contains the closing of the #content div and all content after.
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package kadence
 */

namespace Kadence;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Con el modo nativo, Kadence utiliza su constructor de pie de página.
if ( function_exists( 'flacso_child_use_kadence_chrome' ) && flacso_child_use_kadence_chrome() ) {
    $kadence_footer = get_template_directory() . '/footer.php';
    if ( is_readable( $kadence_footer ) ) {
        require $kadence_footer;
        return;
    }
}

/**
 * Hook for bottom of inner wrap.
 */
do_action( 'kadence_after_content' );
?>
	</div><!-- #inner-wrap -->
	<?php
	do_action( 'kadence_before_footer' );
	get_template_part( 'template-parts/footer/site-footer' );

	do_action( 'kadence_after_footer' );
	?>
</div><!-- #wrapper -->
<?php do_action( 'kadence_after_wrapper' ); ?>

<?php wp_footer(); ?>
</body>
</html>
