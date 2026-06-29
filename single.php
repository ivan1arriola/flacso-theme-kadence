<?php
/**
 * Template para entradas individuales
 *
 * @package kadence-child-flacso
 */

if (!defined('ABSPATH')) {
    exit;
}

get_header();

while (have_posts()) :
    the_post();

    $post_categories  = get_the_category();
    $primary_category = !empty($post_categories) ? $post_categories[0]->name : 'Novedad';

    $blog_page_id = get_option('page_for_posts');
    $blog_url     = $blog_page_id ? get_permalink($blog_page_id) : home_url('/categoria/novedades/');
    ?>

    <main class="flacso-single-post">
        <div class="flacso-single-container">

            <nav class="flacso-breadcrumb" aria-label="Navegación">
                <a href="<?php echo esc_url(home_url('/')); ?>">Inicio</a>
                <span>/</span>
                <a href="<?php echo esc_url($blog_url); ?>">Noticias</a>
                <span>/</span>
                <span><?php echo esc_html($primary_category); ?></span>
            </nav>

            <header class="flacso-post-hero">
                <div class="flacso-hero-body">
                    <div class="flacso-post-kicker">
                        <?php echo esc_html($primary_category); ?>
                    </div>

                    <h1 class="flacso-post-title">
                        <?php the_title(); ?>
                    </h1>

                    <div class="flacso-post-date">
                        <?php echo esc_html(get_the_date()); ?>
                    </div>
                </div>

                <?php if (has_post_thumbnail()) : ?>
                    <figure class="flacso-hero-figure">
                        <?php
                        the_post_thumbnail('large', array(
                            'class' => 'flacso-hero-image',
                            'alt'   => esc_attr(get_the_title()),
                        ));
                        ?>
                    </figure>
                <?php endif; ?>
            </header>

            <div class="flacso-post-columns">
                <article class="flacso-post-content">
                    <?php
                    the_content();

                    wp_link_pages(array(
                        'before' => '<div class="page-links">' . esc_html__('Páginas:', 'kadence-child-flacso'),
                        'after'  => '</div>',
                    ));
                    ?>
                </article>

                <aside class="flacso-sidebar-news" aria-labelledby="flacso-sidebar-news-title">
                    <h2 id="flacso-sidebar-news-title">Ver más novedades</h2>

                    <?php
                    $recent_posts = new WP_Query(array(
                        'post_type'           => 'post',
                        'posts_per_page'      => 5,
                        'post__not_in'        => array(get_the_ID()),
                        'ignore_sticky_posts' => true,
                    ));

                    if ($recent_posts->have_posts()) :
                        echo '<ul class="flacso-sidebar-list">';
                        while ($recent_posts->have_posts()) :
                            $recent_posts->the_post();
                            ?>
                            <li class="flacso-sidebar-item">
                                <a href="<?php the_permalink(); ?>" class="flacso-sidebar-link">
                                    <?php if (has_post_thumbnail()) : ?>
                                        <span class="flacso-sidebar-thumb">
                                            <?php the_post_thumbnail('thumbnail'); ?>
                                        </span>
                                    <?php endif; ?>
                                    <span class="flacso-sidebar-text">
                                        <span class="flacso-sidebar-date"><?php echo esc_html(get_the_date()); ?></span>
                                        <span class="flacso-sidebar-title"><?php the_title(); ?></span>
                                    </span>
                                </a>
                            </li>
                            <?php
                        endwhile;
                        echo '</ul>';
                        wp_reset_postdata();
                    else :
                        echo '<p class="flacso-sidebar-empty">No hay otras novedades disponibles.</p>';
                    endif;
                    ?>

                    <a href="<?php echo esc_url($blog_url); ?>" class="flacso-sidebar-cta">Todas las novedades</a>
                </aside>
            </div>

        </div>
    </main>

    <style>
        .flacso-single-post {
            --flacso-blue: #1d3a72;
            --flacso-blue-dark: #0f1a2d;
            --flacso-yellow: #fed222;
            --flacso-text: #1f2937;
            --flacso-muted: #64748b;
            --flacso-border: #e2e8f0;
            --flacso-bg: #f8fafc;

            background: var(--flacso-bg);
            color: var(--flacso-text);
            padding: 0 0 4.5rem;
        }

        .flacso-single-container {
            width: min(100% - 2rem, 1080px);
            margin: 0 auto;
        }

        .flacso-breadcrumb {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 0.45rem;
            padding: 1.75rem 0 1.25rem;
            font-size: 0.9rem;
            color: var(--flacso-muted);
        }

        .flacso-breadcrumb a {
            color: var(--flacso-blue);
            text-decoration: none;
            font-weight: 500;
        }

        .flacso-breadcrumb a:hover {
            text-decoration: underline;
        }

        /* ── Hero ── */
        .flacso-post-hero {
            background:
                radial-gradient(circle at top right, rgba(254, 210, 34, 0.16), transparent 34%),
                linear-gradient(135deg, var(--flacso-blue-dark), var(--flacso-blue));
            color: #ffffff;
            border-radius: 22px;
            padding: clamp(2rem, 4vw, 3.25rem);
            margin-bottom: 2rem;
            box-shadow: 0 18px 45px rgba(15, 26, 45, 0.14);
            display: flex;
            gap: 2rem;
            align-items: center;
        }

        .flacso-hero-body {
            flex: 1 1 0;
            min-width: 0;
        }

        .flacso-post-kicker {
            display: inline-flex;
            align-items: center;
            width: fit-content;
            background: var(--flacso-yellow);
            color: var(--flacso-blue-dark);
            border-radius: 999px;
            padding: 0.45rem 0.85rem;
            font-size: 0.78rem;
            font-weight: 800;
            line-height: 1;
            text-transform: uppercase;
            letter-spacing: 0.02em;
            margin-bottom: 1rem;
        }

        .flacso-post-title {
            color: #ffffff;
            font-size: clamp(2rem, 4.2vw, 3.4rem);
            line-height: 1.08;
            letter-spacing: -0.035em;
            margin: 0;
            max-width: 920px;
            font-weight: 800;
        }

        .flacso-post-date {
            margin-top: 1.2rem;
            font-size: 1rem;
            color: rgba(255, 255, 255, 0.82);
            font-weight: 500;
        }

        .flacso-hero-figure {
            flex: 0 0 140px;
            margin: 0;
            border-radius: 14px;
            overflow: hidden;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.2);
            aspect-ratio: 1 / 1;
            background: rgba(255, 255, 255, 0.08);
        }

        .flacso-hero-image {
            width: 100%;
            height: 100%;
            display: block;
            object-fit: cover;
        }

        /* ── Columnas: contenido + sidebar ── */
        .flacso-post-columns {
            display: flex;
            gap: 2.25rem;
            align-items: flex-start;
        }

        .flacso-post-content {
            flex: 1 1 0;
            min-width: 0;
            background: #ffffff;
            border: 1px solid var(--flacso-border);
            border-radius: 22px;
            padding: clamp(1.5rem, 4vw, 3rem);
            font-size: 1.08rem;
            line-height: 1.78;
            color: #334155;
        }

        .flacso-post-content p {
            margin: 0 0 1.35rem;
        }

        .flacso-post-content h2,
        .flacso-post-content h3,
        .flacso-post-content h4 {
            color: var(--flacso-blue);
            line-height: 1.25;
            margin: 2rem 0 1rem;
            font-weight: 800;
        }

        .flacso-post-content a {
            color: var(--flacso-blue);
            font-weight: 600;
            text-decoration-thickness: 1px;
            text-underline-offset: 3px;
        }

        .flacso-post-content img {
            max-width: 100%;
            height: auto;
            border-radius: 12px;
        }

        .flacso-post-content ul,
        .flacso-post-content ol {
            padding-left: 1.4rem;
            margin-bottom: 1.4rem;
        }

        /* ── Sidebar "Ver más novedades" ── */
        .flacso-sidebar-news {
            flex: 0 0 300px;
            position: sticky;
            top: 2rem;
            background: #ffffff;
            border: 1px solid var(--flacso-border);
            border-radius: 20px;
            padding: 1.5rem;
            box-shadow: 0 4px 18px rgba(15, 26, 45, 0.04);
        }

        .flacso-sidebar-news h2 {
            color: var(--flacso-blue);
            font-size: 1.1rem;
            font-weight: 800;
            margin: 0 0 1.25rem;
            padding-bottom: 0.75rem;
            border-bottom: 2px solid var(--flacso-yellow);
        }

        .flacso-sidebar-list {
            list-style: none;
            margin: 0;
            padding: 0;
        }

        .flacso-sidebar-item + .flacso-sidebar-item {
            margin-top: 0.75rem;
            padding-top: 0.75rem;
            border-top: 1px solid var(--flacso-border);
        }

        .flacso-sidebar-link {
            display: flex;
            gap: 0.75rem;
            color: inherit;
            text-decoration: none;
        }

        .flacso-sidebar-link:hover .flacso-sidebar-title {
            color: var(--flacso-blue);
        }

        .flacso-sidebar-thumb {
            flex: 0 0 56px;
            width: 56px;
            height: 56px;
            border-radius: 10px;
            overflow: hidden;
            background: #eef2f7;
        }

        .flacso-sidebar-thumb img {
            width: 100%;
            height: 100%;
            display: block;
            object-fit: cover;
        }

        .flacso-sidebar-text {
            flex: 1 1 0;
            min-width: 0;
            display: flex;
            flex-direction: column;
            gap: 0.15rem;
        }

        .flacso-sidebar-date {
            font-size: 0.78rem;
            color: var(--flacso-muted);
        }

        .flacso-sidebar-title {
            font-size: 0.9rem;
            font-weight: 700;
            line-height: 1.3;
            color: var(--flacso-blue-dark);
            transition: color 0.15s;
        }

        .flacso-sidebar-empty {
            color: var(--flacso-muted);
            font-size: 0.9rem;
        }

        .flacso-sidebar-cta {
            display: block;
            margin-top: 1.25rem;
            padding-top: 0.75rem;
            border-top: 1px solid var(--flacso-border);
            color: var(--flacso-blue);
            font-size: 0.9rem;
            font-weight: 700;
            text-decoration: none;
            text-align: center;
        }

        .flacso-sidebar-cta:hover {
            text-decoration: underline;
        }

        /* ── Responsive ── */
        @media (max-width: 860px) {
            .flacso-single-container {
                width: min(100% - 1.25rem, 1080px);
            }

            .flacso-post-hero {
                flex-direction: column-reverse;
                border-radius: 18px;
                gap: 1.25rem;
            }

            .flacso-hero-figure {
                flex: 0 0 auto;
                width: min(100%, 280px);
                aspect-ratio: 16 / 10;
            }

            .flacso-post-title {
                font-size: clamp(1.85rem, 9vw, 2.6rem);
            }

            .flacso-post-columns {
                flex-direction: column;
            }

            .flacso-post-content {
                border-radius: 18px;
                font-size: 1rem;
                line-height: 1.7;
            }

            .flacso-sidebar-news {
                flex: none;
                width: 100%;
                position: static;
            }

            .flacso-sidebar-news h2 {
                text-align: center;
            }

            .flacso-sidebar-list {
                display: grid;
                grid-template-columns: repeat(2, 1fr);
                gap: 0.75rem;
            }

            .flacso-sidebar-item + .flacso-sidebar-item {
                margin-top: 0;
                padding-top: 0;
                border-top: none;
            }

            .flacso-sidebar-link {
                flex-direction: column;
            }

            .flacso-sidebar-thumb {
                flex: none;
                width: 100%;
                height: auto;
                aspect-ratio: 16 / 9;
                border-radius: 10px;
            }
        }

        @media (max-width: 480px) {
            .flacso-sidebar-list {
                grid-template-columns: 1fr;
            }
        }
    </style>

<?php
endwhile;

get_footer();
