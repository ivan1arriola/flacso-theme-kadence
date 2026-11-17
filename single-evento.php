<?php
/**
 * Evento individual con la misma estructura visual de posts y formularios.
 *
 * @package kadence-child-flacso
 */

if (!defined('ABSPATH')) {
    exit;
}

get_header();

while (have_posts()) :
    the_post();

    $event_id   = get_the_ID();
    $start_date = (string) get_post_meta($event_id, 'evento_inicio_fecha', true);
    $start_time = (string) get_post_meta($event_id, 'evento_inicio_hora', true);
    $end_date   = (string) get_post_meta($event_id, 'evento_fin_fecha', true);
    $event_date = $start_date !== ''
        ? date_i18n('j \d\e F \d\e Y', strtotime($start_date))
        : get_the_date();

    if ($start_time !== '') {
        $event_date .= ' · ' . $start_time;
    }
    if ($end_date !== '' && $end_date !== $start_date) {
        $event_date .= ' — ' . date_i18n('j \d\e F \d\e Y', strtotime($end_date));
    }

    // La inscripción la gestiona Formularios FLACSO; el evento solo enlaza.
    $form_url   = esc_url((string) get_post_meta($event_id, '_evento_formulario_url', true));
    $last_day   = $end_date !== '' ? $end_date : $start_date;
    $is_past    = $last_day !== '' && $last_day < current_time('Y-m-d');
    $can_signup = $form_url !== '' && !$is_past;
    $short_date = $start_date !== ''
        ? date_i18n('j \d\e F', strtotime($start_date)) . ($start_time !== '' ? ' · ' . $start_time : '')
        : '';
    ?>

    <main class="flacso-single-post flacso-single-evento<?php echo $can_signup ? ' has-signup-bar' : ''; ?>">
        <div class="flacso-single-container">
            <nav class="flacso-breadcrumb" aria-label="<?php esc_attr_e('Navegación', 'kadence-child-flacso'); ?>">
                <a href="<?php echo esc_url(home_url('/')); ?>"><?php esc_html_e('Inicio', 'kadence-child-flacso'); ?></a>
                <span>/</span>
                <span><?php esc_html_e('Eventos', 'kadence-child-flacso'); ?></span>
                <span>/</span>
                <span><?php the_title(); ?></span>
            </nav>

            <header class="flacso-post-hero">
                <div class="flacso-hero-body">
                    <div class="flacso-post-kicker"><?php esc_html_e('Evento', 'kadence-child-flacso'); ?></div>
                    <h1 class="flacso-post-title"><?php the_title(); ?></h1>
                    <div class="flacso-post-date">
                        <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="4.5" width="18" height="16" rx="3"/><path d="M8 2.5v4M16 2.5v4M3 9.5h18"/></svg>
                        <?php echo esc_html($event_date); ?>
                    </div>
                    <?php if ($can_signup) : ?>
                        <a id="flacso-evento-hero-cta" class="flacso-evento-cta__button flacso-evento-hero-cta" href="<?php echo $form_url; ?>" rel="noopener"><?php esc_html_e('Inscribirme', 'kadence-child-flacso'); ?></a>
                    <?php elseif ($is_past) : ?>
                        <p class="flacso-evento-finished"><?php esc_html_e('Este evento ya finalizó.', 'kadence-child-flacso'); ?></p>
                    <?php endif; ?>
                </div>

                <?php if (has_post_thumbnail()) : ?>
                    <figure class="flacso-hero-figure">
                        <?php
                        the_post_thumbnail('large', [
                            'class' => 'flacso-hero-image',
                            'alt'   => esc_attr(get_the_title()),
                        ]);
                        ?>
                    </figure>
                <?php endif; ?>
            </header>

            <div class="flacso-post-columns">
                <article class="flacso-post-content">
                    <?php
                    the_content();

                    get_template_part('template-parts/evento/expositores', null, [
                        'ids' => function_exists('flacso_eventos_get_speaker_ids') ? flacso_eventos_get_speaker_ids($event_id) : [],
                    ]);

                    wp_link_pages([
                        'before' => '<div class="page-links">' . esc_html__('Páginas:', 'kadence-child-flacso'),
                        'after'  => '</div>',
                    ]);
                    ?>
                </article>

                <aside class="flacso-sidebar-news" aria-labelledby="flacso-sidebar-events-title">
                    <h2 id="flacso-sidebar-events-title"><?php esc_html_e('Próximos eventos', 'kadence-child-flacso'); ?></h2>
                    <?php
                    $today = current_time('Y-m-d');
                    $events = new WP_Query([
                        'post_type'      => 'evento',
                        'post_status'    => 'publish',
                        'posts_per_page' => 5,
                        'post__not_in'   => [$event_id],
                        'meta_key'       => 'evento_inicio_fecha',
                        'meta_value'     => $today,
                        'meta_compare'   => '>=',
                        'meta_type'      => 'DATE',
                        'meta_query'      => [
                            [
                                'key'     => '_evento_mostrar_web',
                                'value'   => '1',
                                'compare' => '=',
                            ],
                        ],
                        'orderby'        => 'meta_value',
                        'order'          => 'ASC',
                    ]);

                    if ($events->have_posts()) :
                        echo '<ul class="flacso-sidebar-list">';
                        while ($events->have_posts()) :
                            $events->the_post();
                            $sidebar_date = (string) get_post_meta(get_the_ID(), 'evento_inicio_fecha', true);
                            ?>
                            <li class="flacso-sidebar-item">
                                <a href="<?php the_permalink(); ?>" class="flacso-sidebar-link">
                                    <?php if (has_post_thumbnail()) : ?>
                                        <span class="flacso-sidebar-thumb"><?php the_post_thumbnail('thumbnail'); ?></span>
                                    <?php endif; ?>
                                    <span class="flacso-sidebar-text">
                                        <span class="flacso-sidebar-date">
                                            <?php echo esc_html($sidebar_date !== '' ? date_i18n('j \d\e F', strtotime($sidebar_date)) : get_the_date()); ?>
                                        </span>
                                        <span class="flacso-sidebar-title"><?php the_title(); ?></span>
                                    </span>
                                </a>
                            </li>
                            <?php
                        endwhile;
                        echo '</ul>';
                        wp_reset_postdata();
                    else :
                        echo '<p class="flacso-sidebar-empty">' . esc_html__('No hay otros eventos próximos.', 'kadence-child-flacso') . '</p>';
                    endif;
                    ?>
                </aside>
            </div>
        </div>
    </main>

    <?php if ($can_signup) : ?>
        <div class="flacso-evento-signup" id="flacso-evento-signup" hidden>
            <p class="flacso-evento-signup__info">
                <strong><?php the_title(); ?></strong>
                <?php if ($short_date !== '') : ?><span><?php echo esc_html($short_date); ?></span><?php endif; ?>
            </p>
            <a class="flacso-evento-cta__button" href="<?php echo $form_url; ?>" rel="noopener"><?php esc_html_e('Inscribirme', 'kadence-child-flacso'); ?></a>
        </div>
        <script>
        (function () {
            var bar = document.getElementById('flacso-evento-signup');
            var hero = document.getElementById('flacso-evento-hero-cta');
            if (!bar) return;
            var narrow = window.matchMedia('(max-width: 860px)');
            // En escritorio el botón flota cuando el del hero sale de pantalla; en móvil la barra siempre se ve.
            var heroVisible = true;
            function update() { bar.hidden = !(narrow.matches || !heroVisible); }
            if (!hero || !('IntersectionObserver' in window)) { heroVisible = false; }
            else { new IntersectionObserver(function (entries) { heroVisible = entries[0].isIntersecting; update(); }).observe(hero); }
            narrow.addEventListener('change', update);
            update();
        })();
        </script>
    <?php endif; ?>
    <?php
endwhile;

get_footer();
