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

require_once get_stylesheet_directory() . '/inc/academic-team-context.php';

get_header();

while (have_posts()) :
    the_post();

    $post_id = get_the_ID();
    $context = function_exists('flacso_theme_get_offer_context')
        ? flacso_theme_get_offer_context($post_id)
        : [];

    if (function_exists('flacso_theme_apply_scoped_teams')) {
        $context = flacso_theme_apply_scoped_teams($context, $post_id);
    }

    $is_carta = (bool) get_query_var('es_carta') || (get_query_var('carta') !== false && get_query_var('carta') !== '');
    if (!$is_carta && isset($_SERVER['REQUEST_URI'])) {
        $req_path = trim((string) wp_parse_url((string) $_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
        if (str_ends_with($req_path, '/carta') || str_ends_with($req_path, 'carta')) {
            $is_carta = true;
        }
    }

    if ($is_carta) {
        get_template_part('template-parts/oferta/carta', null, ['context' => $context]);
    } else {
        get_template_part('template-parts/oferta/default', null, ['context' => $context]);
    }

endwhile;

get_footer();
