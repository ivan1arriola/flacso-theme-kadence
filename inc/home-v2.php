<?php

if (!defined('ABSPATH')) { exit; }

function flacso_home_v2_render(): string {
    $model = class_exists('FLACSO_Home_Campaign') ? FLACSO_Home_Campaign::view_model() : [];
    $campaign = is_array($model['campaign'] ?? null) ? $model['campaign'] : [];
    $image_id = absint($campaign['desktop_image_id'] ?? 0);
    $image = $image_id && function_exists('wp_get_attachment_image_url') ? wp_get_attachment_image_url($image_id, 'full') : '';
    $primary = is_array($campaign['cta_primary'] ?? null) ? $campaign['cta_primary'] : null;
    $secondary = is_array($campaign['cta_secondary'] ?? null) ? $campaign['cta_secondary'] : null;
    $slogan = flacso_theme_brand_slogan();
    $hero_title = flacso_theme_brand_slogan_replace_legacy($campaign['title'] ?? 'FLACSO Uruguay');
    $hero_description = flacso_theme_brand_slogan_replace_legacy($campaign['description'] ?? '');
    if ($hero_title === $slogan && $hero_description === $slogan) {
        $hero_description = '';
    }
    $show_slogan = $hero_title !== $slogan && $hero_description !== $slogan;
    ob_start(); ?>
    <main id="main" class="flacso-home-v2" role="main">
        <section class="flacso-home-v2__hero"<?php echo $image ? ' style="--flacso-home-v2-image:url(' . esc_url($image) . ')"' : ''; ?>>
            <div class="flacso-home-v2__inner">
                <?php if (!empty($campaign['kicker'])) : ?><p class="flacso-home-v2__kicker"><?php echo esc_html($campaign['kicker']); ?></p><?php endif; ?>
                <?php if ($hero_title === $slogan) : ?>
                    <?php $slogan_lines = explode(', ', $slogan, 2); ?>
                    <h1 class="flacso-home-v2__title--slogan"><span><?php echo esc_html($slogan_lines[0]); ?>,</span> <span><?php echo esc_html($slogan_lines[1] ?? ''); ?></span></h1>
                <?php else : ?>
                    <h1><?php echo esc_html($hero_title); ?></h1>
                <?php endif; ?>
                <?php if ($show_slogan) : ?><p class="flacso-home-v2__slogan"><?php echo esc_html($slogan); ?></p><?php endif; ?>
                <?php if ($hero_description !== '') : ?><p class="flacso-home-v2__description"><?php echo esc_html($hero_description); ?></p><?php endif; ?>
                <?php if ($primary || $secondary) : ?><div class="flacso-home-v2__actions">
                    <?php foreach ([$primary, $secondary] as $index => $cta) : if (!$cta) { continue; } ?><a class="flacso-home-v2__cta <?php echo $index ? 'flacso-home-v2__cta--secondary' : ''; ?>" href="<?php echo esc_url($cta['url']); ?>"><?php echo esc_html($cta['label']); ?></a><?php endforeach; ?>
                </div><?php endif; ?>
            </div>
        </section>
        <nav class="flacso-home-v2__links" aria-label="Accesos rápidos de FLACSO Uruguay">
            <a href="<?php echo esc_url(home_url('/formacion/')); ?>">
                <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="m2 10 10-5 10 5-10 5-10-5Z"/><path d="M6 12v5c3 2.5 9 2.5 12 0v-5"/><path d="M22 10v7"/></svg>
                <strong>Formación</strong><span>Conocé nuestra propuesta académica.</span>
                <span class="flacso-home-v2__link-arrow" aria-hidden="true">→</span>
            </a>
            <a href="<?php echo esc_url(home_url('/formacion/seminarios/')); ?>">
                <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 7v14"/><path d="M3 18V5c3-1.5 6-1.5 9 .5 3-2 6-2 9-.5v13c-3-1.5-6-1.5-9 .5-3-2-6-2-9-.5Z"/></svg>
                <strong>Seminarios</strong><span>Actualización y formación intensiva.</span>
                <span class="flacso-home-v2__link-arrow" aria-hidden="true">→</span>
            </a>
            <a href="<?php echo esc_url(home_url('/contactos/')); ?>">
                <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M20 11.5a8 8 0 0 1-8 8 8.5 8.5 0 0 1-3.5-.9L4 20l1.4-4.1A8 8 0 1 1 20 11.5Z"/><path d="M9 10h6M9 13h4"/></svg>
                <strong>Consultas</strong><span>Estamos para orientarte.</span>
                <span class="flacso-home-v2__link-arrow" aria-hidden="true">→</span>
            </a>
        </nav>
        <?php echo flacso_home_v2_render_sections(); ?>
    </main>
    <?php return (string) ob_get_clean();
}

/**
 * Jerarquía editorial de la portada v2. El orden administrativo sigue siendo
 * fuente de verdad para las secciones opcionales y para su visibilidad.
 * El render de eventos devuelve '' cuando no hay eventos futuros.
 */
function flacso_home_v2_render_sections(): string {
    $renderers = [
        'eventos' => 'flacso_section_eventos_render',
        'seminarios' => 'flacso_section_seminarios_proximos_render',
        'novedades' => 'flacso_section_novedades_render',
        'quienes' => 'flacso_section_quienes_somos_render',
        'posgrados' => 'flacso_section_oferta_educativa_render',
        'mailing' => 'flacso_section_mailing_render',
        'contacto' => 'flacso_section_contacto_render',
    ];
    $configured_order = array_keys($renderers);
    if (class_exists('Flacso_Main_Page_Settings')) {
        $configured = Flacso_Main_Page_Settings::get_homepage_section_order();
        if (is_array($configured) && $configured) {
            $configured_order = array_values(array_unique(array_merge($configured, $configured_order)));
        }
    }

    // Noticias, eventos (solo si existen), oferta e institución tienen una
    // posición consistente. Las otras secciones conservan el orden configurado.
    $editorial_order = ['novedades', 'eventos', 'posgrados', 'quienes'];
    $optional_order = array_values(array_filter(
        $configured_order,
        static fn($key) => isset($renderers[$key]) && !in_array($key, array_merge($editorial_order, ['contacto']), true)
    ));
    $order = array_merge($editorial_order, $optional_order, ['contacto']);

    ob_start();
    foreach ($order as $key) {
        if (!isset($renderers[$key])) { continue; }
        if ($key !== 'posgrados' && !function_exists($renderers[$key])) { continue; }
        if (
            $key !== 'posgrados'
            && class_exists('Flacso_Main_Page_Settings')
            && !Flacso_Main_Page_Settings::is_section_visible($key)
        ) { continue; }
        if ($key === 'novedades') {
            $content = flacso_home_v2_render_featured_news();
        } elseif ($key === 'posgrados') {
            $content = flacso_home_v2_render_academic_offer();
        } else {
            $content = call_user_func($renderers[$key]);
        }
        if (!$content) { continue; }
        ?><article class="flacso-home-v2__section flacso-home-v2__section--<?php echo esc_attr($key); ?>" data-section-key="<?php echo esc_attr($key); ?>"><?php echo $content; ?></article><?php
    }
    return (string) ob_get_clean();
}

function flacso_home_v2_render_academic_offer(): string {
    $source_cards = function_exists('flacso_section_oferta_educativa_get_cards')
        ? flacso_section_oferta_educativa_get_cards()
        : [];
    if (!$source_cards) {
        $source_cards = [
            ['title' => 'Maestrías', 'url' => home_url('/formacion/maestrias/')],
            ['title' => 'Especializaciones', 'url' => home_url('/formacion/especializaciones/')],
            ['title' => 'Diplomados', 'url' => home_url('/formacion/diplomados/')],
            ['title' => 'Diplomas', 'url' => home_url('/formacion/diplomas/')],
            ['title' => 'Seminarios', 'url' => home_url('/formacion/seminarios/')],
        ];
    }

    $cards_by_type = [];
    foreach ($source_cards as $card) {
        $title = trim(wp_strip_all_tags((string) ($card['title'] ?? '')));
        $normalized_title = strtolower(remove_accents($title));
        $slug = '';
        if (strpos($normalized_title, 'maestr') !== false) {
            $slug = 'maestrias';
        } elseif (strpos($normalized_title, 'especial') !== false) {
            $slug = 'especializaciones';
        } elseif (strpos($normalized_title, 'diplomado') !== false) {
            $slug = 'diplomados';
        } elseif (strpos($normalized_title, 'diploma') !== false) {
            $slug = 'diplomas';
        } elseif (strpos($normalized_title, 'seminar') !== false) {
            $slug = 'seminarios';
        }
        if ($slug !== '') {
            $cards_by_type[$slug] = $card;
        }
    }

    $categories = [
        'maestrias' => ['label' => 'Posgrado', 'title' => 'Maestrías', 'icon' => '<path d="M3 5.5c3.2-1.6 6.2-1.1 9 1.2v13c-2.8-2.3-5.8-2.8-9-1.2z"/><path d="M21 5.5c-3.2-1.6-6.2-1.1-9 1.2v13c2.8-2.3 5.8-2.8 9-1.2z"/><path class="accent" d="M6 9h3M15 9h3"/>'],
        'especializaciones' => ['label' => 'Posgrado', 'title' => 'Especializaciones', 'icon' => '<circle cx="10.5" cy="10.5" r="6.5"/><path d="m15.5 15.5 5 5"/><path class="accent" d="M10.5 7.5v6M7.5 10.5h6"/>'],
        'diplomados' => ['label' => 'Posgrado', 'title' => 'Diplomados', 'icon' => '<path d="m2 9 10-5 10 5-10 5z"/><path d="M6 11v5c3.4 2.6 8.6 2.6 12 0v-5"/><path d="M22 9v6"/><circle class="accent-fill" cx="21" cy="17" r="1.5"/>'],
        'diplomas' => ['label' => 'Posgrado', 'title' => 'Diplomas', 'icon' => '<rect x="4" y="3" width="16" height="19" rx="1"/><path d="M8 8h8M8 12h8M8 16h4"/><circle class="accent" cx="16" cy="17" r="3"/><path class="accent" d="m14.5 19.5-.5 3 2-1 2 1-.5-3"/>'],
        'seminarios' => ['label' => 'Formación', 'title' => 'Seminarios', 'icon' => '<path d="M3 4h14a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2h-7l-5 4v-4H5a2 2 0 0 1-2-2z"/><path d="M19 9h2a2 2 0 0 1 2 2v8l-4-3"/><path class="accent" d="M7 9h7M7 12h5"/>'],
    ];

    ob_start(); ?>
    <section class="flacso-home-v2__academic-offer" aria-labelledby="flacso-home-v2-academic-title">
        <header class="flacso-home-v2__academic-heading">
            <div>
                <p class="flacso-home-v2__academic-kicker">Oferta académica</p>
                <h2 id="flacso-home-v2-academic-title">Encontrá la formación que buscás</h2>
            </div>
            <p class="flacso-home-v2__academic-intro">Conocé las propuestas de formación de posgrado de FLACSO Uruguay.</p>
        </header>
        <nav class="flacso-home-v2__academic-grid" aria-label="Categorías de formación académica">
            <?php $index = 0; foreach ($categories as $slug => $category) : if (empty($cards_by_type[$slug])) { continue; } $card = $cards_by_type[$slug]; $index++; ?>
                <a class="flacso-home-v2__academic-card" href="<?php echo esc_url($card['url'] ?? '#'); ?>">
                    <span class="flacso-home-v2__academic-label"><?php echo esc_html(sprintf('%02d / %s', $index, $category['label'])); ?></span>
                    <h3><?php echo esc_html($category['title']); ?></h3>
                    <span class="flacso-home-v2__academic-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><?php echo $category['icon']; ?></svg></span>
                    <span class="flacso-home-v2__academic-arrow" aria-hidden="true">→</span>
                </a>
            <?php endforeach; ?>
        </nav>
    </section>
    <?php return (string) ob_get_clean();
}

/**
 * No duplicar noticias: el plugin excluye posts fijados del listado cronológico.
 * Si no hay fijados, se promociona la primera noticia reciente a la cabecera.
 */
function flacso_home_v2_render_featured_news(): string {
    if (!function_exists('flacso_section_novedades_get_sticky_posts') || !function_exists('flacso_section_novedades_get_posts')) {
        return '';
    }
    $featured = flacso_section_novedades_get_sticky_posts(-1);
    $latest_data = flacso_section_novedades_get_posts(6, 'novedades', 1);
    $latest = is_array($latest_data['posts'] ?? null) ? $latest_data['posts'] : [];
    $has_pinned = !empty($featured);
    if (!$has_pinned && $latest) {
        $featured = [array_shift($latest)];
    }
    if (!$featured && !$latest) {
        return '';
    }
    ob_start(); ?>
    <section class="flacso-home-v2__featured-news" aria-labelledby="flacso-home-v2-news-title">
        <div class="flacso-home-v2__section-heading">
            <h2 id="flacso-home-v2-news-title">Novedades</h2>
        </div>
        <?php if ($featured) : ?>
            <div class="flacso-home-v2__bento-shell">
                <div class="flacso-home-v2__bento-heading">
                    <h3><?php echo $has_pinned ? 'Destacados' : 'En portada'; ?></h3>
                </div>
                <div class="flacso-home-v2__bento-grid">
                    <?php foreach ($featured as $index => $post) : ?>
                        <?php echo flacso_home_v2_render_news_card($post, $has_pinned ? 'featured' : 'spotlight', $index === 0); ?>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
        <?php if ($latest) : ?>
            <div class="flacso-home-v2__latest-news">
                <h3>Últimas noticias</h3>
                <div class="flacso-home-v2__latest-grid">
                    <?php foreach ($latest as $post) : echo flacso_home_v2_render_news_card($post, 'latest'); endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
        <a class="flacso-home-v2__all-news" href="<?php echo esc_url(home_url('/categoria/novedades/')); ?>">Ver todas las novedades <span aria-hidden="true">→</span></a>
    </section>
    <?php return (string) ob_get_clean();
}

function flacso_home_v2_render_news_card($post, string $variant, bool $is_lead = false): string {
    $post_id = (int) $post->ID;
    $title = wp_strip_all_tags(get_the_title($post_id));
    $excerpt = has_excerpt($post_id)
        ? get_the_excerpt($post_id)
        : wp_trim_words(wp_strip_all_tags((string) get_post_field('post_content', $post_id)), 20, '…');
    $image_id = get_post_thumbnail_id($post_id);
    $pinned = $variant === 'featured';
    ob_start(); ?>
    <article class="flacso-home-v2__news-card flacso-home-v2__news-card--<?php echo esc_attr($variant); ?><?php echo $is_lead ? ' flacso-home-v2__news-card--lead' : ''; ?>">
        <a class="flacso-home-v2__news-image" href="<?php echo esc_url(get_permalink($post_id)); ?>" aria-label="<?php echo esc_attr('Leer: ' . $title); ?>">
            <?php if ($image_id) : echo wp_get_attachment_image($image_id, $is_lead ? 'large' : 'medium_large', false, ['alt' => '', 'loading' => 'lazy']); else : ?><span aria-hidden="true"></span><?php endif; ?>
        </a>
        <div class="flacso-home-v2__news-body">
            <?php if ($pinned || $is_lead) : ?><span class="flacso-home-v2__news-badge"><?php echo $pinned ? ($is_lead ? 'Noticia fijada' : 'Fijada') : 'Novedad'; ?></span><?php endif; ?>
            <time datetime="<?php echo esc_attr(get_the_date('c', $post_id)); ?>"><?php echo esc_html(get_the_date('d/m/Y', $post_id)); ?></time>
            <h4><a href="<?php echo esc_url(get_permalink($post_id)); ?>"><?php echo esc_html($title); ?></a></h4>
            <?php if ($is_lead && trim((string) $excerpt) !== '') : ?><p><?php echo esc_html(wp_strip_all_tags((string) $excerpt)); ?></p><?php endif; ?>
            <a class="flacso-home-v2__news-link" href="<?php echo esc_url(get_permalink($post_id)); ?>">Leer noticia <span aria-hidden="true">→</span></a>
        </div>
    </article>
    <?php return (string) ob_get_clean();
}
