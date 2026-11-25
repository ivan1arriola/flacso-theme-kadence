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

/**
 * Respaldo si el plugin está desactivado: replica Flacso_Home_Content::defaults().
 */
function flacso_home_default_content(): array {
    return [
        'hero' => ['eyebrow' => 'PROGRAMA URUGUAY', 'title_lead' => 'Formación que conecta,', 'title_em' => 'conocimiento que transforma.', 'intro' => 'Formación, investigación y cooperación académica con perspectiva latinoamericana.', 'cta_label' => 'Explorar oferta académica', 'cta_url' => home_url('/formacion/')],
        'campaign' => ['enabled' => true, 'label' => 'INSCRIPCIONES 2027', 'title' => 'Tu próximo paso empieza acá.', 'text' => 'Explorá las propuestas con inscripciones abiertas.', 'cta_label' => 'Conocer la oferta', 'cta_url' => home_url('/formacion/')],
        'research' => ['title_lead' => 'Pensar la región.', 'title_em' => 'Producir conocimiento.', 'intro' => 'Conocé los proyectos, centros y publicaciones de FLACSO Uruguay.', 'link_label' => 'Explorar investigaciones', 'link_url' => home_url('/institucional/investigaciones/'), 'links' => [
            ['label' => 'Proyectos de investigación', 'url' => home_url('/institucional/investigaciones/')],
            ['label' => 'Archivo Histórico IIN–FLACSO', 'url' => 'https://www.archivoiinflacso.uy/'],
            ['label' => 'Libros y publicaciones', 'url' => home_url('/categoria/publicaciones/libros/')],
        ]],
    ];
}

function flacso_home_get_data(): array {
    $content = class_exists('Flacso_Home_Content') ? Flacso_Home_Content::get() : flacso_home_default_content();
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
        $configured = is_array($hero_settings) ? trim((string) ($hero_settings['background_image'] ?? '')) : '';
        // La foto predeterminada del plugin no cuenta como elección editorial: se usa la de la maqueta.
        $plugin_default = method_exists('Flacso_Main_Page_Settings', 'default_hero_image') ? trim((string) Flacso_Main_Page_Settings::default_hero_image()) : '';
        if ($configured !== '' && $configured !== $plugin_default) {
            $hero_image = esc_url_raw($configured);
            if ($image_id === 0 && function_exists('attachment_url_to_postid')) {
                $image_id = absint(attachment_url_to_postid($hero_image));
            }
        }
    }
    if (!$hero_image) {
        $hero_image = get_stylesheet_directory_uri() . '/assets/img/home-hero.jpg';
        $image_alt = 'Personas adultas participando en una instancia de intercambio académico';
    }
    $hero_srcset = flacso_home_hero_srcset($image_id, $hero_image);
    $hero_sizes = $hero_srcset !== '' ? '100vw' : '';
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
            'image_srcset' => $hero_srcset,
            'image_sizes' => $hero_sizes,
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

function flacso_home_hero_srcset(int $image_id, string $full_url): string {
    if ($image_id <= 0) {
        return '';
    }
    $entries = [];
    foreach (['1536x1536' => 1536, '2048x2048' => 2048, 'full' => 2560] as $size => $fallback_width) {
        $url = '';
        $width = $fallback_width;
        if (function_exists('wp_get_attachment_image_src')) {
            $src = wp_get_attachment_image_src($image_id, $size);
            if (!is_array($src) || empty($src[0])) {
                continue;
            }
            $url = (string) $src[0];
            $reported = (int) ($src[1] ?? 0);
            if ($reported > 0) {
                $width = $reported;
            }
            if ($size !== 'full' && $width < 1536) {
                continue;
            }
        } elseif (function_exists('wp_get_attachment_image_url')) {
            $candidate = (string) wp_get_attachment_image_url($image_id, $size);
            if ($candidate === '') {
                continue;
            }
            $url = $candidate;
        }
        if ($url !== '' && !isset($entries[$url])) {
            $entries[$url] = esc_url($url) . ' ' . $width . 'w';
        }
    }
    if ($full_url !== '' && !isset($entries[$full_url]) && count($entries) >= 1) {
        $entries[$full_url] = esc_url($full_url) . ' 2560w';
    }
    return count($entries) >= 2 ? implode(', ', $entries) : '';
}

function flacso_home_part(string $name, array $data): void {
    $path = get_stylesheet_directory() . '/template-parts/home/' . $name . '.php';
    if (is_readable($path)) { include $path; }
}

function flacso_home_render(): string {
    $data = flacso_home_get_data();
    ob_start();
    echo '<' . flacso_child_main_tag() . ' id="main" class="flacso-home">';
    foreach (['hero', 'agenda', 'offer', 'news', 'about'] as $part) {
        flacso_home_part($part, $data);
    }
    echo '</' . flacso_child_main_tag() . '>';
    return (string) ob_get_clean();
}

function flacso_home_news_card($post, string $variant, bool $is_pinned = false): void {
    $id = (int) $post->ID;
    $title = wp_strip_all_tags(get_the_title($id));
    $url = get_permalink($id);
    $image_id = get_post_thumbnail_id($id);
    $category = '';
    if (function_exists('get_the_category')) {
        $categories = get_the_category($id);
        if (is_array($categories) && !empty($categories[0]->name)) { $category = trim(wp_strip_all_tags((string) $categories[0]->name)); }
    }
    $is_feature = $variant === 'featured' || $variant === 'secondary';
    // Una noticia sin miniatura se presenta a ancho completo, sin enlaces vacíos.
    $poster = $image_id
        ? '<a class="poster" href="' . esc_url($url) . '" aria-label="' . esc_attr(sprintf(__('Leer noticia: %s', 'flacso-uruguay'), $title)) . '">'
            . wp_get_attachment_image($image_id, 'medium_large', false, ['alt' => '', 'loading' => 'lazy'])
            . '</a>'
        : '';
    $visual_class = $image_id ? '' : ' news-card--no-image';
    $meta = '<p class="news-meta">' . ($category !== '' ? esc_html($category) . ' · ' : '')
        . '<time datetime="' . esc_attr(get_the_date('c', $id)) . '">' . esc_html(get_the_date('j M Y', $id)) . '</time></p>';
    $heading = '<h3><a href="' . esc_url($url) . '">' . esc_html($title) . '</a></h3>';
    $link = '<a class="text-link" href="' . esc_url($url) . '">' . esc_html__('Leer noticia', 'flacso-uruguay') . ' <span aria-hidden="true">↗</span></a>';
    if ($is_feature) {
        $pin = $is_pinned ? '<p class="pin-label"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="m14 3 7 7-4 1-4 5-2-2-6 6M4 13l7-7 1-4"/></svg> ' . esc_html__('NOTICIA FIJADA', 'flacso-uruguay') . '</p>' : '';
        echo '<article class="news-card feature' . ($variant === 'secondary' ? ' secondary' : '') . $visual_class . '">' . $poster . '<div>' . $pin . $meta . $heading . $link . '</div></article>';
        return;
    }
    $pin = $is_pinned ? '<p class="pin-label">' . esc_html__('NOTICIA FIJADA', 'flacso-uruguay') . '</p>' : '';
    echo '<article class="news-card recent' . $visual_class . '">' . $poster . '<div>' . $pin . $meta . $heading . $link . '</div></article>';
}
