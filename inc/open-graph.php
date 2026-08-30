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

    $context = flacso_theme_get_virtual_preinscripcion_og_context();
    if (!empty($context)) {
        return $context;
    }

    $context = flacso_theme_get_virtual_seminario_preinscripcion_og_context();
    if (!empty($context)) {
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

function flacso_theme_get_virtual_preinscripcion_og_context() {
    if (!get_query_var('es_preinscripcion') && !get_query_var('es_carta')) {
        return [];
    }

    if (
        !class_exists('FLACSO_Formulario_Preinscripcion_Final')
        || !method_exists('FLACSO_Formulario_Preinscripcion_Final', 'get_instance')
    ) {
        return [];
    }

    $plugin = FLACSO_Formulario_Preinscripcion_Final::get_instance();
    if (!$plugin || !method_exists($plugin, 'get_preinscripcion_template_context')) {
        return [];
    }

    $info = $plugin->get_preinscripcion_template_context();
    if (!is_array($info) || empty($info['titulo_posgrado'])) {
        return [];
    }

    $is_carta = (bool) get_query_var('es_carta');
    $title_prefix = $is_carta ? 'Carta de Presentacion - ' : 'Preinscripcion - ';
    $description_prefix = $is_carta
        ? 'Sube tu carta de presentacion para '
        : 'Completa el formulario de preinscripcion para ';
    $title = $title_prefix . trim((string) $info['titulo_posgrado']);

    return [
        'og_type' => 'website',
        'title' => $title,
        'description' => $description_prefix . trim((string) $info['titulo_posgrado']),
        'url' => flacso_theme_get_current_request_url(),
        'force_render' => true,
    ] + flacso_theme_build_url_image_data(
        isset($info['imagen_destacada']) ? (string) $info['imagen_destacada'] : '',
        $title
    );
}

function flacso_theme_get_virtual_seminario_preinscripcion_og_context() {
    if (!get_query_var('flacso_preinscripcion')) {
        return [];
    }

    $seminario_id = isset($_GET['ID']) ? absint(wp_unslash($_GET['ID'])) : get_queried_object_id();
    if ($seminario_id <= 0 || !function_exists('flacso_theme_is_seminar') || !flacso_theme_is_seminar($seminario_id)) {
        return [];
    }

    $title = 'Preinscripcion - ' . get_the_title($seminario_id);
    $description = get_the_excerpt($seminario_id);
    if ($description === '') {
        $description = 'Completa el formulario de preinscripcion para ' . get_the_title($seminario_id);
    }

    return [
        'og_type' => 'website',
        'title' => $title,
        'description' => $description,
        'url' => flacso_theme_get_current_request_url(),
        'force_render' => true,
    ] + flacso_theme_get_attachment_og_image_data(get_post_thumbnail_id($seminario_id), $title);
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
    if (class_exists('Oferta_Taxonomies') && method_exists('Oferta_Taxonomies', 'get_term_featured_image_data')) {
        $raw_image_data = Oferta_Taxonomies::get_term_featured_image_data($term);
        if (is_array($raw_image_data)) {
            $image_id = isset($raw_image_data['id']) ? (int) $raw_image_data['id'] : 0;
            if ($image_id > 0) {
                $image_data = flacso_theme_get_attachment_og_image_data($image_id, $term->name);
            } else {
                $image_data = flacso_theme_build_url_image_data(
                    (string) ($raw_image_data['large'] ?? $raw_image_data['url'] ?? ''),
                    trim((string) ($raw_image_data['alt'] ?? '')) ?: $term->name,
                    isset($raw_image_data['width']) ? (int) $raw_image_data['width'] : 0,
                    isset($raw_image_data['height']) ? (int) $raw_image_data['height'] : 0
                );
            }
        }
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

function flacso_theme_build_url_image_data($image_url, $fallback_alt = '', $width = 0, $height = 0) {
    $image_url = esc_url_raw((string) $image_url);
    if ($image_url === '') {
        return [];
    }

    return [
        'image_url' => $image_url,
        'image_width' => (int) $width,
        'image_height' => (int) $height,
        'image_alt' => trim((string) $fallback_alt),
        'mime_type' => '',
    ];
}

function flacso_theme_build_post_description($post_id) {
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

function flacso_theme_get_current_request_url() {
    $request_uri = wp_unslash($_SERVER['REQUEST_URI'] ?? '');
    if (!is_string($request_uri) || $request_uri === '') {
        return home_url('/');
    }

    $path = strtok($request_uri, '?');
    if (!is_string($path) || $path === '') {
        return home_url('/');
    }

    return home_url($path);
}

function flacso_theme_has_external_og_provider() {
    return defined('WPSEO_VERSION')
        || defined('RANK_MATH_VERSION')
        || has_action('wp_head', 'jetpack_og_tags');
}
