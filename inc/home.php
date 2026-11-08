<?php
/**
 * Portada editorial. El plugin aporta el contenido; el tema define el diseño.
 */
if (!defined('ABSPATH')) { exit; }

/**
 * Párrafos institucionales aprobados; se usan solo si la opción "quienes" está vacía.
 */
function flacso_home_default_institution_content(): string {
    return '<p><strong>FLACSO Uruguay</strong> es la sede uruguaya de la <strong>Facultad Latinoamericana de Ciencias Sociales</strong>, una de las principales <strong>redes académicas</strong> de <strong>América Latina y el Caribe</strong>, con presencia en más de <strong>20 países</strong> de la región. Su objetivo principal es <strong>contribuir al desarrollo de la región</strong> mediante la <strong>formación de profesionales</strong>, la <strong>investigación crítica</strong> y la <strong>transferencia de conocimiento</strong>.</p>'
        . '<p><strong>FLACSO Uruguay</strong> ofrece <strong>programas de posgrado</strong> en diversas áreas de las <strong>ciencias sociales</strong> y desarrolla <strong>proyectos de investigación</strong> en temas como <strong>género</strong>, <strong>política pública</strong>, <strong>medio ambiente</strong>, <strong>desarrollo humano</strong> y <strong>cultura</strong>, con un enfoque <strong>interdisciplinario</strong> y un compromiso con la <strong>transformación social</strong>.</p>';
}

function flacso_home_get_data(): array {
    $content = class_exists('Flacso_Home_Content') ? Flacso_Home_Content::get() : [];
    $hero_text = is_array($content['hero'] ?? null) ? $content['hero'] : [];
    $campaign_text = is_array($content['campaign'] ?? null) ? $content['campaign'] : [];
    $research_text = is_array($content['research'] ?? null) ? $content['research'] : [];

    $model = class_exists('FLACSO_Home_Campaign') ? FLACSO_Home_Campaign::view_model() : [];
    $campaign = is_array($model['campaign'] ?? null) ? $model['campaign'] : [];
    $custom_campaign = ($campaign['source'] ?? '') === 'campaign';

    $title_lead = (string) ($hero_text['title_lead'] ?? '');
    $title_em = (string) ($hero_text['title_em'] ?? '');
    if ($custom_campaign && trim((string) ($campaign['title'] ?? '')) !== '') {
        $title_lead = trim(wp_strip_all_tags((string) $campaign['title']));
        $title_em = '';
    }
    $eyebrow = $custom_campaign && trim((string) ($campaign['kicker'] ?? '')) !== ''
        ? trim(wp_strip_all_tags((string) $campaign['kicker']))
        : (string) ($hero_text['eyebrow'] ?? '');
    $primary = is_array($campaign['cta_primary'] ?? null) ? $campaign['cta_primary'] : [];
    $hero_cta = $custom_campaign && !empty($primary['url']) && !empty($primary['label'])
        ? ['label' => trim(wp_strip_all_tags((string) $primary['label'])), 'url' => (string) $primary['url']]
        : ['label' => (string) ($hero_text['cta_label'] ?? ''), 'url' => (string) ($hero_text['cta_url'] ?? '')];

    // Imagen: campaña CPT, luego la fotografía configurada en ajustes, luego la predeterminada del tema.
    $image_alt = 'Imagen institucional de FLACSO Uruguay';
    $image_id = $custom_campaign ? absint($campaign['desktop_image_id'] ?? 0) : 0;
    $hero_image = $image_id ? (string) wp_get_attachment_image_url($image_id, 'full') : '';
    if (!$hero_image && class_exists('Flacso_Main_Page_Settings')) {
        $hero_settings = Flacso_Main_Page_Settings::get_section('hero');
        if (is_array($hero_settings) && !empty($hero_settings['background_image'])) {
            $hero_image = esc_url_raw((string) $hero_settings['background_image']);
        }
    }
    if (!$hero_image) {
        $hero_image = get_stylesheet_directory_uri() . '/assets/img/home-hero.jpg';
        $image_alt = 'Personas adultas participando en una instancia de intercambio académico';
    }
    $mobile_image_id = $custom_campaign ? absint($campaign['mobile_image_id'] ?? 0) : 0;
    $hero_mobile_image = $mobile_image_id ? (string) wp_get_attachment_image_url($mobile_image_id, 'large') : '';

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
    // Propuestas con inscripción abierta por tipo, con el mismo criterio del catálogo de Formación.
    $open_counts = array_fill_keys(array_keys($defaults), 0);
    if (function_exists('flacso_theme_get_offer_context')) {
        $type_slugs = ['maestria' => 'maestrias', 'especializacion' => 'especializaciones', 'diplomado' => 'diplomados', 'diploma' => 'diplomas'];
        foreach (get_posts(['post_type' => 'oferta-academica', 'post_status' => 'publish', 'posts_per_page' => -1, 'fields' => 'ids']) as $offer_id) {
            $context = flacso_theme_get_offer_context((int) $offer_id);
            $slug = $type_slugs[(string) ($context['type'] ?? 'maestria') ?: 'maestria'] ?? '';
            if ($slug !== '' && !empty($context['registration']['is_open'])) { $open_counts[$slug]++; }
        }
    }
    if (function_exists('flacso_theme_get_seminars_catalog')) {
        foreach (flacso_theme_get_seminars_catalog() as $seminar) {
            if (!empty($seminar['registration']['is_open'])) { $open_counts['seminarios']++; }
        }
    }
    foreach ($cards as $slug => $card) { $cards[$slug]['open'] = $open_counts[$slug] ?? 0; }

    if (!function_exists('flacso_section_eventos_get_items')) {
        if (class_exists('Flacso_Main_Page_Loader')) {
            Flacso_Main_Page_Loader::load_components();
        }
        if (!function_exists('flacso_section_eventos_get_items') && defined('FLACSO_URUGUAY_PATH')) {
            $eventos_carousel_file = FLACSO_URUGUAY_PATH . 'modules/main-page/sections/eventos-carousel.php';
            if (file_exists($eventos_carousel_file)) {
                require_once $eventos_carousel_file;
            }
        }
    }
    $events = function_exists('flacso_section_eventos_get_items') ? flacso_section_eventos_get_items(3) : [];
    $event = null;
    foreach (is_array($events) ? $events : [] as $candidate) {
        if (is_array($candidate) && !empty($candidate['id']) && trim((string) ($candidate['title'] ?? '')) !== '') {
            $event = $candidate;
            break;
        }
    }

    $featured = function_exists('flacso_section_novedades_get_sticky_posts') ? flacso_section_novedades_get_sticky_posts(-1) : [];
    $recent_data = function_exists('flacso_section_novedades_get_posts') ? flacso_section_novedades_get_posts(8, 'novedades', 1) : [];
    $recent = is_array($recent_data['posts'] ?? null) ? $recent_data['posts'] : [];
    $featured = is_array($featured) ? array_values($featured) : [];
    if (!$featured && $recent) { $featured[] = array_shift($recent); }
    $extra_pinned = array_slice($featured, 2);
    $excluded = array_map(static fn($post) => (int) $post->ID, $featured);
    $featured = array_slice($featured, 0, 2);
    $recent = array_values(array_filter($recent, static fn($post) => !in_array((int) $post->ID, $excluded, true)));
    $recent_news = array_slice(array_merge($extra_pinned, $recent), 0, 4);

    $campaign_block = null;
    if (!empty($campaign_text['enabled'])) {
        $campaign_block = [
            'label' => (string) ($campaign_text['label'] ?? ''),
            'title' => (string) ($campaign_text['title'] ?? ''),
            'text' => (string) ($campaign_text['text'] ?? ''),
            'cta' => ['label' => (string) ($campaign_text['cta_label'] ?? ''), 'url' => (string) ($campaign_text['cta_url'] ?? '')],
        ];
    }

    $institution = class_exists('Flacso_Main_Page_Settings') ? Flacso_Main_Page_Settings::get_section('quienes') : [];
    $institution = is_array($institution) ? $institution : [];
    if (trim(wp_strip_all_tags((string) ($institution['content'] ?? ''))) === '') {
        $institution['content'] = flacso_home_default_institution_content();
    }

    return [
        'hero' => [
            'eyebrow' => $eyebrow,
            'title_lead' => $title_lead,
            'title_em' => $title_em,
            'intro' => (string) ($hero_text['intro'] ?? ''),
            'cta' => $hero_cta,
            'image' => $hero_image,
            'image_mobile' => $hero_mobile_image,
            'image_alt' => $image_alt,
        ],
        'cards' => $cards,
        'campaign' => $campaign_block,
        'event' => $event,
        'featured_news' => $featured,
        'recent_news' => $recent_news,
        'research' => [
            'title_lead' => (string) ($research_text['title_lead'] ?? ''),
            'title_em' => (string) ($research_text['title_em'] ?? ''),
            'intro' => (string) ($research_text['intro'] ?? ''),
            'link' => ['label' => (string) ($research_text['link_label'] ?? ''), 'url' => (string) ($research_text['link_url'] ?? '')],
            'links' => is_array($research_text['links'] ?? null) ? array_values($research_text['links']) : [],
        ],
        'institution' => $institution,
    ];
}

function flacso_home_part(string $name, array $data): void {
    $path = get_stylesheet_directory() . '/template-parts/home/' . $name . '.php';
    if (is_readable($path)) { include $path; }
}

function flacso_home_render(): string {
    $data = flacso_home_get_data();
    ob_start();
    echo '<main id="main" class="flacso-home" role="main">';
    foreach (['hero', 'offer', 'campaign', 'agenda', 'news', 'research', 'about'] as $part) {
        flacso_home_part($part, $data);
    }
    echo '</main>';
    return (string) ob_get_clean();
}

function flacso_home_news_card($post, string $variant, bool $is_pinned = false): void {
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
