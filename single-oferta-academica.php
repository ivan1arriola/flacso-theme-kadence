<?php
/**
 * Template principal para la página individual de una Oferta Académica.
 *
 * Despacha de forma transparente hacia la vista especializada de seminarios
 * o la vista general de programas (maestrías, diplomas, doctorados, etc.),
 * garantizando compatibilidad antes y después de la migración académica.
 *
 * @package kadence-child-flacso
 */

if (!defined('ABSPATH')) {
    exit;
}

get_header();

if (class_exists('Oferta_Renderer')) {
    Oferta_Renderer::enqueue_styles();
}

while (have_posts()) :
    the_post();

    $post_id = get_the_ID();
    $context = function_exists('flacso_theme_get_offer_context')
        ? flacso_theme_get_offer_context($post_id)
        : [];

    if (!empty($context['is_seminar'])) {
        get_template_part('template-parts/oferta/seminario', null, ['context' => $context]);
    } else {
        get_template_part('template-parts/oferta/default', null, ['context' => $context]);
    }

endwhile;

get_footer();
