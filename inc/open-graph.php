<?php

if (!defined('ABSPATH')) {
    exit;
}

add_action('after_setup_theme', 'flacso_theme_register_og_image_size');
add_action('wp_head', 'flacso_theme_render_open_graph_meta', 1);
add_filter('wpseo_opengraph_image', 'flacso_theme_filter_seo_plugin_og_image');
add_filter('wpseo_twitter_image', 'flacso_theme_filter_seo_plugin_og_image');
add_filter('rank_math/opengraph/facebook/image', 'flacso_theme_filter_seo_plugin_og_image');
add_filter('rank_math/opengraph/twitter/image', 'flacso_theme_filter_seo_plugin_og_image');

function flacso_theme_register_og_image_size() {
    add_image_size('og_meta_size', 1200, 630, true);
}

function flacso_theme_render_open_graph_meta() {
    $context = flacso_theme_get_open_graph_context();
    if (empty($context)) {
        return;
    }

    if (flacso_theme_has_external_og_provider() && empty($context['force_render'])) {
        return;
    }

    $title = trim((string) ($context['title'] ?? ''));
    $description = trim((string) ($context['description'] ?? ''));
    $url = esc_url_raw((string) ($context['url'] ?? ''));
    $site_name = trim((string) get_bloginfo('name'));
    $og_type = trim((string) ($context['og_type'] ?? 'article')) ?: 'article';
    $image_url = esc_url_raw((string) ($context['image_url'] ?? ''));
    $image_width = isset($context['image_width']) ? (int) $context['image_width'] : 0;
    $image_height = isset($context['image_height']) ? (int) $context['image_height'] : 0;
    $image_alt = trim((string) ($context['image_alt'] ?? ''));
    $mime_type = trim((string) ($context['mime_type'] ?? ''));

    if ($title === '' || $url === '') {
        return;
    }

    echo "\n";
    echo '<meta property="og:type" content="' . esc_attr($og_type) . '">' . "\n";
    if ($site_name !== '') {
        echo '<meta property="og:site_name" content="' . esc_attr($site_name) . '">' . "\n";
    }
    echo '<meta property="og:title" content="' . esc_attr($title) . '">' . "\n";
    if ($description !== '') {
        echo '<meta property="og:description" content="' . esc_attr($description) . '">' . "\n";
    }
    echo '<meta property="og:url" content="' . esc_url($url) . '">' . "\n";

    if ($image_url !== '') {
        echo '<meta property="og:image" content="' . esc_url($image_url) . '">' . "\n";
        echo '<meta property="og:image:secure_url" content="' . esc_url($image_url) . '">' . "\n";

        if ($image_width > 0) {
            echo '<meta property="og:image:width" content="' . esc_attr((string) $image_width) . '">' . "\n";
        }

        if ($image_height > 0) {
            echo '<meta property="og:image:height" content="' . esc_attr((string) $image_height) . '">' . "\n";
        }

        if ($image_alt !== '') {
            echo '<meta property="og:image:alt" content="' . esc_attr($image_alt) . '">' . "\n";
        }

        if ($mime_type !== '') {
            echo '<meta property="og:image:type" content="' . esc_attr($mime_type) . '">' . "\n";
        }
    }

    echo '<meta name="twitter:card" content="summary_large_image">' . "\n";
    echo '<meta name="twitter:title" content="' . esc_attr($title) . '">' . "\n";
    if ($description !== '') {
        echo '<meta name="twitter:description" content="' . esc_attr($description) . '">' . "\n";
    }
    if ($image_url !== '') {
        echo '<meta name="twitter:image" content="' . esc_url($image_url) . '">' . "\n";
    }
    echo "\n";
}

function flacso_theme_filter_seo_plugin_og_image($image) {
    $context = flacso_theme_get_open_graph_context();
    $image_url = trim((string) ($context['image_url'] ?? ''));

    return $image_url !== '' ? $image_url : $image;
}

function flacso_theme_get_open_graph_context() {
    static $context = null;

    if ($context !== null) {
        return $context;
    }

    $context = flacso_theme_get_taxonomy_og_context();
    if (!empty($context)) {
        return $context;
    }

    $context = flacso_theme_get_singular_og_context();
    if (!empty($context)) {
        return $context;
    }

    $context = [];
    return $context;
}

function flacso_theme_get_taxonomy_og_context() {
    if (!is_tax(array('tipo-oferta-academica', 'area-tematica'))) {
        return [];
    }

    $term = get_queried_object();
    if (!($term instanceof WP_Term)) {
        return [];
    }

    $description = trim(wp_strip_all_tags(term_description($term, $term->taxonomy)));
    if ($description === '') {
        $description = sprintf(
            'Explora la oferta academica de %s en FLACSO Uruguay.',
            $term->name
        );
    }

    $term_link = get_term_link($term);
    if (is_wp_error($term_link)) {
        return [];
    }

    $image_data = [];
    $term_image_id = (int) get_term_meta($term->term_id, 'thumbnail_id', true);
    if ($term_image_id > 0) {
        $image_data = flacso_theme_get_attachment_og_image_data($term_image_id, $term->name);
    }

    return [
        'og_type' => 'website',
        'title' => single_term_title('', false) ?: $term->name,
        'description' => $description,
        'url' => $term_link,
    ] + $image_data;
}

function flacso_theme_get_singular_og_context() {
    if (!is_singular()) {
        return [];
    }

    $post_id = get_queried_object_id();
    if ($post_id <= 0) {
        return [];
    }

    $post_type = get_post_type($post_id);
    if (!$post_type) {
        return [];
    }

    $title = get_the_title($post_id);
    $url = get_permalink($post_id);
    if ($title === '' || !$url) {
        return [];
    }

    return [
        'og_type' => 'article',
        'title' => $title,
        'description' => flacso_theme_build_post_description($post_id),
        'url' => $url,
    ] + flacso_theme_get_attachment_og_image_data(get_post_thumbnail_id($post_id), $title);
}

function flacso_theme_get_attachment_og_image_data($attachment_id, $fallback_alt = '') {
    $attachment_id = (int) $attachment_id;
    if ($attachment_id <= 0) {
        return [];
    }

    $image = wp_get_attachment_image_src($attachment_id, 'og_meta_size');
    if (!$image || empty($image[0])) {
        $image = wp_get_attachment_image_src($attachment_id, 'large');
    }

    if (!$image || empty($image[0])) {
        $image = wp_get_attachment_image_src($attachment_id, 'full');
    }

    if (!$image || empty($image[0])) {
        return [];
    }

    $alt = trim((string) get_post_meta($attachment_id, '_wp_attachment_image_alt', true));
    if ($alt === '') {
        $alt = trim((string) $fallback_alt);
    }

    return [
        'image_url' => (string) $image[0],
        'image_width' => !empty($image[1]) ? (int) $image[1] : 0,
        'image_height' => !empty($image[2]) ? (int) $image[2] : 0,
        'image_alt' => $alt,
        'mime_type' => (string) get_post_mime_type($attachment_id),
    ];
}

function flacso_theme_build_post_description($post_id) {
    $post_type = get_post_type($post_id);
    if ($post_type === 'oferta-academica' && function_exists('flacso_theme_get_offer_context')) {
        $context = flacso_theme_get_offer_context($post_id);
        $resumen = trim((string) ($context['academic']['resumen'] ?? ''));
        if ($resumen !== '') {
            return $resumen;
        }
        $presentacion = trim((string) ($context['academic']['presentacion'] ?? ''));
        if ($presentacion !== '') {
            return wp_trim_words(wp_strip_all_tags($presentacion), 28, '...');
        }
    } elseif ($post_type === 'seminario' && function_exists('flacso_theme_get_seminar_context')) {
        $context = flacso_theme_get_seminar_context($post_id);
        $resumen = trim((string) ($context['academic']['resumen'] ?? ''));
        if ($resumen !== '') {
            return $resumen;
        }
        $presentacion = trim((string) ($context['academic']['presentacion'] ?? ''));
        if ($presentacion !== '') {
            return wp_trim_words(wp_strip_all_tags($presentacion), 28, '...');
        }
    }

    $description = trim((string) get_the_excerpt($post_id));
    if ($description !== '') {
        return $description;
    }

    $post_content = get_post_field('post_content', $post_id);
    $description = trim((string) wp_trim_words(wp_strip_all_tags((string) $post_content), 28, '...'));
    if ($description !== '') {
        return $description;
    }

    return trim((string) get_bloginfo('description'));
}

function flacso_theme_has_external_og_provider() {
    return defined('WPSEO_VERSION')
        || defined('RANK_MATH_VERSION')
        || has_action('wp_head', 'jetpack_og_tags');
}

/**
 * Cartas de presentación: noindex, nofollow en el encabezado HTTP y en una sola etiqueta robots.
 * Con un plugin SEO activo, su propia etiqueta se corrige por filtro en vez de sumar otra.
 */
function flacso_theme_render_carta_robots_meta() {
    if ( ! function_exists( 'flacso_theme_is_carta' ) || ! flacso_theme_is_carta() ) {
        return;
    }
    if ( ! headers_sent() ) {
        header( 'X-Robots-Tag: noindex, nofollow', true );
    }
    if ( ! flacso_theme_has_external_og_provider() && ! function_exists( 'wp_robots' ) ) {
        echo '<meta name="robots" content="noindex, nofollow">' . "\n";
    }
}
add_action( 'wp_head', 'flacso_theme_render_carta_robots_meta', 1 );

/** Directivas robots de la Carta para Rank Math y Yoast (arreglos asociativos). */
function flacso_theme_filter_carta_robots_array( $robots ) {
    if ( is_array( $robots ) && function_exists( 'flacso_theme_is_carta' ) && flacso_theme_is_carta() ) {
        $robots['index']  = 'noindex';
        $robots['follow'] = 'nofollow';
    }
    return $robots;
}
add_filter( 'rank_math/frontend/robots', 'flacso_theme_filter_carta_robots_array' );
add_filter( 'wpseo_robots_array', 'flacso_theme_filter_carta_robots_array' );

/** Etiqueta robots nativa de WordPress (5.7+). */
function flacso_theme_filter_carta_wp_robots( $robots ) {
    if ( is_array( $robots ) && function_exists( 'flacso_theme_is_carta' ) && flacso_theme_is_carta() ) {
        unset( $robots['index'], $robots['follow'] );
        $robots['noindex']  = true;
        $robots['nofollow'] = true;
    }
    return $robots;
}
add_filter( 'wp_robots', 'flacso_theme_filter_carta_wp_robots' );

