<?php
/**
 * Portada editorial. El plugin aporta el contenido; el tema define el diseño.
 */
if (!defined('ABSPATH')) { exit; }

function flacso_home_v2_get_data(): array {
    $model = class_exists('FLACSO_Home_Campaign') ? FLACSO_Home_Campaign::view_model() : [];
    $campaign = is_array($model['campaign'] ?? null) ? $model['campaign'] : [];
    $custom_campaign = ($campaign['source'] ?? '') === 'campaign';
    $hero_title = $custom_campaign && trim((string) ($campaign['title'] ?? '')) !== ''
        ? trim(wp_strip_all_tags((string) $campaign['title']))
        : 'Formación que conecta, conocimiento que transforma.';
    $hero_kicker = $custom_campaign && trim((string) ($campaign['kicker'] ?? '')) !== ''
        ? trim(wp_strip_all_tags((string) $campaign['kicker']))
        : 'Programa Uruguay';
    $primary = is_array($campaign['cta_primary'] ?? null) ? $campaign['cta_primary'] : [];
    $hero_cta = $custom_campaign && !empty($primary['url']) && !empty($primary['label'])
        ? ['label' => trim(wp_strip_all_tags((string) $primary['label'])), 'url' => (string) $primary['url']]
        : ['label' => 'Explorar oferta académica', 'url' => '#oferta'];
    $image_id = absint($campaign['desktop_image_id'] ?? 0);
    $hero_image = $image_id ? wp_get_attachment_image_url($image_id, 'large') : '';
    $mobile_image_id = absint($campaign['mobile_image_id'] ?? 0);
    $hero_mobile_image = $mobile_image_id ? wp_get_attachment_image_url($mobile_image_id, 'large') : '';
    // Identidad institucional cuando no hay una imagen de campaña activa.
    if (!$hero_image) {
        $hero_image = 'https://flacso.edu.uy/wp-content/uploads/2026/02/cropped-cropped-Logos-FLACSO-Claro.png';
    }

    $defaults = [
        'maestrias' => 'Maestrías',
        'especializaciones' => 'Especializaciones',
        'diplomados' => 'Diplomados',
        'diplomas' => 'Diplomas',
        'seminarios' => 'Seminarios',
    ];
    $cards = [];
    foreach ($defaults as $slug => $label) {
        $cards[$slug] = ['title' => $label, 'url' => home_url('/formacion/' . $slug . '/')];
    }
    $source = function_exists('flacso_section_oferta_educativa_get_cards') ? flacso_section_oferta_educativa_get_cards() : [];
    foreach ((array) $source as $card) {
        $title = trim(wp_strip_all_tags((string) ($card['title'] ?? '')));
        $normalized = strtolower(remove_accents($title));
        foreach ($defaults as $slug => $label) {
            if (strpos($normalized, rtrim($slug, 's')) !== false) {
                $url = trim((string) ($card['url'] ?? ''));
                if ($url !== '' && $url !== '#') { $cards[$slug] = ['title' => $label, 'url' => $url]; }
                break;
            }
        }
    }
    $events = function_exists('flacso_section_eventos_get_items') ? flacso_section_eventos_get_items(3) : [];
    $events = is_array($events) ? array_slice($events, 0, 3) : [];
    $featured = function_exists('flacso_section_novedades_get_sticky_posts') ? flacso_section_novedades_get_sticky_posts(-1) : [];
    $recent_data = function_exists('flacso_section_novedades_get_posts') ? flacso_section_novedades_get_posts(8, 'novedades', 1) : [];
    $recent = is_array($recent_data['posts'] ?? null) ? $recent_data['posts'] : [];
    $featured = is_array($featured) ? array_values($featured) : [];
    $has_pinned_news = !empty($featured);
    if (!$featured && $recent) { $featured[] = array_shift($recent); }
    $extra_pinned = array_slice($featured, 2);
    $excluded = array_map(static fn($post) => (int) $post->ID, $featured);
    $featured = array_slice($featured, 0, 2);
    $recent = array_values(array_filter($recent, static fn($post) => !in_array((int) $post->ID, $excluded, true)));
    $extra_pinned_ids = array_map(static fn($post) => (int) $post->ID, $extra_pinned);
    $institution = class_exists('Flacso_Main_Page_Settings') ? Flacso_Main_Page_Settings::get_section('quienes') : [];
    return [
        'hero_image' => $hero_image,
        'hero_is_logo' => !$image_id,
        'hero_mobile_image' => $hero_mobile_image,
        'hero_image_alt' => $image_id ? 'Imagen de la campaña de FLACSO Uruguay' : 'Logo de FLACSO Uruguay',
        'hero_title' => $hero_title,
        'hero_kicker' => $hero_kicker,
        'hero_cta' => $hero_cta,
        'cards' => $cards,
        'events' => $events,
        'featured_news' => $featured,
        'has_pinned_news' => $has_pinned_news,
        'recent_news' => array_merge($extra_pinned, array_slice($recent, 0, 4)),
        'extra_pinned_ids' => $extra_pinned_ids,
        'institution' => is_array($institution) ? $institution : [],
    ];
}

function flacso_home_v2_part(string $name, array $data): void {
    $path = get_stylesheet_directory() . '/template-parts/home/' . $name . '.php';
    if (is_readable($path)) { include $path; }
}

function flacso_home_v2_render(): string {
    $data = flacso_home_v2_get_data();
    ob_start();
    echo '<main id="main" class="flacso-home-v2" role="main">';
    foreach (['hero', 'offer', 'events', 'news', 'about', 'impact'] as $part) {
        flacso_home_v2_part($part, $data);
    }
    echo '</main>';
    return (string) ob_get_clean();
}

function flacso_home_v2_news_card($post, string $variant, bool $is_pinned = false): void {
    $id = (int) $post->ID;
    $title = wp_strip_all_tags(get_the_title($id));
    $url = get_permalink($id);
    $image_id = get_post_thumbnail_id($id);
    $excerpt = has_excerpt($id) ? get_the_excerpt($id) : wp_trim_words(wp_strip_all_tags((string) get_post_field('post_content', $id)), 22);
    ?>
    <article class="home-news-card home-news-card--<?php echo esc_attr($variant); ?>">
        <a class="home-news-media" href="<?php echo esc_url($url); ?>" aria-label="<?php echo esc_attr('Leer: ' . $title); ?>">
            <?php if ($image_id) : echo wp_get_attachment_image($image_id, 'medium_large', false, ['alt' => '', 'loading' => 'lazy']); else : ?><span class="home-media-empty" aria-hidden="true">FLACSO Uruguay</span><?php endif; ?>
        </a>
        <div class="home-news-copy">
            <?php if ($variant === 'featured' || $is_pinned) : ?><span class="home-news-pin">● <?php echo $is_pinned ? 'Noticia fijada' : 'En portada'; ?></span><?php endif; ?>
            <time datetime="<?php echo esc_attr(get_the_date('c', $id)); ?>"><?php echo esc_html(get_the_date('j \\d\\e F \\d\\e Y', $id)); ?></time>
            <h3><a href="<?php echo esc_url($url); ?>"><?php echo esc_html($title); ?></a></h3>
            <?php if ($variant === 'featured' && trim((string) $excerpt) !== '') : ?><p><?php echo esc_html(wp_strip_all_tags($excerpt)); ?></p><?php endif; ?>
            <a class="home-text-link" href="<?php echo esc_url($url); ?>">Leer noticia <span aria-hidden="true">→</span></a>
        </div>
    </article>
    <?php
}
