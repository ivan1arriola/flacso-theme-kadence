<?php
/**
 * Template principal para la página individual de una Oferta Académica.
 *
 * Consume el View Model canónico y despacha a template-parts/oferta/default.
 *
 * @package kadence-child-flacso
 */

if (!defined('ABSPATH')) {
    exit;
}

get_header();

while (have_posts()) :
    the_post();

    $post_id = get_the_ID();
    $context = function_exists('flacso_theme_get_offer_context')
        ? flacso_theme_get_offer_context($post_id)
        : [];

    get_template_part('template-parts/oferta/default', null, ['context' => $context]);

endwhile;

get_footer();
