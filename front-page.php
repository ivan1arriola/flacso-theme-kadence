<?php
/**
 * The template for displaying the front page.
 *
 * The header opens #inner-wrap and runs kadence_before_content;
 * the footer runs kadence_after_content and closes #inner-wrap.
 * Do not add another wrapper or duplicate those hooks here.
 *
 * @package kadence-child-flacso
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

echo flacso_home_render(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- las plantillas escapan cada valor.

get_footer();
