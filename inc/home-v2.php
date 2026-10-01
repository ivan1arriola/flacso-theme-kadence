<?php

if (!defined('ABSPATH')) { exit; }

function flacso_home_v2_render(): string {
    $model = class_exists('FLACSO_Home_Campaign') ? FLACSO_Home_Campaign::view_model() : [];
    $campaign = is_array($model['campaign'] ?? null) ? $model['campaign'] : [];
    $image_id = absint($campaign['desktop_image_id'] ?? 0);
    $image = $image_id && function_exists('wp_get_attachment_image_url') ? wp_get_attachment_image_url($image_id, 'full') : '';
    $primary = is_array($campaign['cta_primary'] ?? null) ? $campaign['cta_primary'] : null;
    $secondary = is_array($campaign['cta_secondary'] ?? null) ? $campaign['cta_secondary'] : null;
    ob_start(); ?>
    <main id="main" class="flacso-home-v2" role="main">
        <section class="flacso-home-v2__hero"<?php echo $image ? ' style="--flacso-home-v2-image:url(' . esc_url($image) . ')"' : ''; ?>>
            <div class="flacso-home-v2__inner">
                <?php if (!empty($campaign['kicker'])) : ?><p class="flacso-home-v2__kicker"><?php echo esc_html($campaign['kicker']); ?></p><?php endif; ?>
                <h1><?php echo esc_html($campaign['title'] ?? 'FLACSO Uruguay'); ?></h1>
                <?php if (!empty($campaign['description'])) : ?><p class="flacso-home-v2__description"><?php echo esc_html($campaign['description']); ?></p><?php endif; ?>
                <?php if ($primary || $secondary) : ?><div class="flacso-home-v2__actions">
                    <?php foreach ([$primary, $secondary] as $index => $cta) : if (!$cta) { continue; } ?><a class="flacso-home-v2__cta <?php echo $index ? 'flacso-home-v2__cta--secondary' : ''; ?>" href="<?php echo esc_url($cta['url']); ?>"><?php echo esc_html($cta['label']); ?></a><?php endforeach; ?>
                </div><?php endif; ?>
            </div>
        </section>
        <section class="flacso-home-v2__links" aria-label="Formación FLACSO Uruguay">
            <a href="<?php echo esc_url(home_url('/formacion/')); ?>"><strong>Formación</strong><span>Conocé nuestra propuesta académica.</span></a>
            <a href="<?php echo esc_url(home_url('/formacion/seminarios/')); ?>"><strong>Seminarios</strong><span>Actualización y formación intensiva.</span></a>
            <a href="<?php echo esc_url(home_url('/contactos/')); ?>"><strong>Consultas</strong><span>Estamos para orientarte.</span></a>
        </section>
        <?php echo flacso_home_v2_render_sections(); ?>
    </main>
    <?php return (string) ob_get_clean();
}

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
    $order = array_keys($renderers);
    if (class_exists('Flacso_Main_Page_Settings')) {
        $configured = Flacso_Main_Page_Settings::get_homepage_section_order();
        if (is_array($configured) && $configured) {
            $order = array_values(array_unique(array_merge($configured, $order)));
        }
    }
    ob_start();
    foreach ($order as $key) {
        if (!isset($renderers[$key]) || !function_exists($renderers[$key])) { continue; }
        if (class_exists('Flacso_Main_Page_Settings') && !Flacso_Main_Page_Settings::is_section_visible($key)) { continue; }
        $content = $key === 'novedades'
            ? flacso_home_v2_render_featured_news()
            : call_user_func($renderers[$key]);
        if (!$content) { continue; }
        ?><article class="flacso-home-v2__section flacso-home-v2__section--<?php echo esc_attr($key); ?>" data-section-key="<?php echo esc_attr($key); ?>"><?php echo $content; ?></article><?php
    }
    return (string) ob_get_clean();
}

function flacso_home_v2_render_featured_news(): string {
    if (!function_exists('flacso_section_novedades_get_sticky_posts') || !function_exists('flacso_section_novedades_get_posts')) {
        return '';
    }
    $featured = flacso_section_novedades_get_sticky_posts(6);
    $latest_data = flacso_section_novedades_get_posts(6, 'novedades', 1);
    $latest = is_array($latest_data['posts'] ?? null) ? $latest_data['posts'] : [];
    if (!$featured && !$latest) {
        return '';
    }
    ob_start(); ?>
    <section class="flacso-home-v2__featured-news" aria-labelledby="flacso-home-v2-news-title">
        <div class="flacso-home-v2__section-heading">
            <h2 id="flacso-home-v2-news-title">Novedades</h2>
        </div>
        <?php if ($featured) : ?>
            <div class="flacso-home-v2__carousel-shell">
                <div class="flacso-home-v2__carousel-heading">
                    <h3>Noticias fijadas</h3>
                    <div class="flacso-home-v2__carousel-controls">
                        <button type="button" data-home-news-prev aria-label="Noticia fijada anterior">←</button>
                        <span data-home-news-status aria-live="polite">1 / <?php echo esc_html(count($featured)); ?></span>
                        <button type="button" data-home-news-next aria-label="Siguiente noticia fijada">→</button>
                    </div>
                </div>
                <div class="flacso-home-v2__featured-track" data-home-news-carousel tabindex="0" aria-label="Noticias fijadas">
                    <?php foreach ($featured as $post) : echo flacso_home_v2_render_news_card($post, 'featured'); endforeach; ?>
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
        <a class="flacso-home-v2__all-news" href="<?php echo esc_url(home_url('/categoria/novedades/')); ?>">Ver todas las novedades</a>
    </section>
    <?php return (string) ob_get_clean();
}

function flacso_home_v2_render_news_card($post, string $variant): string {
    $post_id = (int) $post->ID;
    $title = wp_strip_all_tags(get_the_title($post_id));
    $excerpt = has_excerpt($post_id)
        ? get_the_excerpt($post_id)
        : wp_trim_words(wp_strip_all_tags((string) get_post_field('post_content', $post_id)), 20, '…');
    $image_id = get_post_thumbnail_id($post_id);
    ob_start(); ?>
    <article class="flacso-home-v2__news-card flacso-home-v2__news-card--<?php echo esc_attr($variant); ?>">
        <a class="flacso-home-v2__news-image" href="<?php echo esc_url(get_permalink($post_id)); ?>">
            <?php if ($image_id) : echo wp_get_attachment_image($image_id, 'medium_large', false, ['alt' => esc_attr($title), 'loading' => 'lazy']); else : ?><span aria-hidden="true"></span><?php endif; ?>
        </a>
        <div class="flacso-home-v2__news-body">
            <time datetime="<?php echo esc_attr(get_the_date('c', $post_id)); ?>"><?php echo esc_html(get_the_date('d/m/Y', $post_id)); ?></time>
            <h4><a href="<?php echo esc_url(get_permalink($post_id)); ?>"><?php echo esc_html($title); ?></a></h4>
            <p><?php echo esc_html(wp_strip_all_tags((string) $excerpt)); ?></p>
            <a class="flacso-home-v2__news-link" href="<?php echo esc_url(get_permalink($post_id)); ?>">Leer noticia <span aria-hidden="true">→</span></a>
        </div>
    </article>
    <?php return (string) ob_get_clean();
}
