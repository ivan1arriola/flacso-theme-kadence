<?php
/**
 * Template individual para el Custom Post Type 'seminario' (Compatibilidad Legacy).
 *
 * Reutiliza el View Model y el partial visual unificado 'template-parts/oferta/seminario.php'.
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
    $context = function_exists('flacso_theme_get_seminar_context')
        ? flacso_theme_get_seminar_context($post_id)
        : [];

    get_template_part('template-parts/oferta/seminario', null, ['context' => $context]);

endwhile;

get_footer();
