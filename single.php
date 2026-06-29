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
    $blog_url     = $blog_page_id ? get_permalink($blog_page_id) : home_url('/noticias/');
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
                <div class="flacso-post-kicker">
                    <?php echo esc_html($primary_category); ?>
                </div>

                <h1 class="flacso-post-title">
                    <?php the_title(); ?>
                </h1>

                <div class="flacso-post-date">
                    <?php echo esc_html(get_the_date()); ?>
                </div>
            </header>

            <article class="flacso-post-layout">

                <?php if (has_post_thumbnail()) : ?>
                    <figure class="flacso-featured-wrap">
                        <?php
                        the_post_thumbnail('large', array(
                            'class' => 'flacso-featured-image',
                            'alt'   => esc_attr(get_the_title()),
                        ));
                        ?>
                    </figure>
                <?php endif; ?>

                <div class="flacso-post-content">
                    <?php
                    the_content();

                    wp_link_pages(array(
                        'before' => '<div class="page-links">' . esc_html__('Páginas:', 'kadence-child-flacso'),
                        'after'  => '</div>',
                    ));
                    ?>
                </div>

            </article>

            <section class="flacso-more-news" aria-labelledby="flacso-more-news-title">
                <div class="flacso-more-news-header">
                    <h2 id="flacso-more-news-title">Últimas novedades</h2>
                    <a href="<?php echo esc_url($blog_url); ?>">Ver todas</a>
                </div>

                <div class="flacso-more-news-grid">
                    <?php
                    $recent_posts = new WP_Query(array(
                        'post_type'           => 'post',
                        'posts_per_page'      => 3,
                        'post__not_in'        => array(get_the_ID()),
                        'ignore_sticky_posts' => true,
                    ));

                    if ($recent_posts->have_posts()) :
                        while ($recent_posts->have_posts()) :
                            $recent_posts->the_post();
                            ?>
                            <article class="flacso-news-card">
                                <a href="<?php the_permalink(); ?>" class="flacso-news-card-link">
                                    <?php if (has_post_thumbnail()) : ?>
                                        <div class="flacso-news-thumb">
                                            <?php the_post_thumbnail('medium_large'); ?>
                                        </div>
                                    <?php endif; ?>

                                    <div class="flacso-news-card-body">
                                        <span class="flacso-news-date"><?php echo esc_html(get_the_date()); ?></span>
                                        <h3><?php the_title(); ?></h3>
                                    </div>
                                </a>
                            </article>
                            <?php
                        endwhile;
                        wp_reset_postdata();
                    else :
                        ?>
                        <p>No hay otras novedades disponibles.</p>
                        <?php
                    endif;
                    ?>
                </div>
            </section>

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

        .flacso-post-hero {
            background:
                radial-gradient(circle at top right, rgba(254, 210, 34, 0.16), transparent 34%),
                linear-gradient(135deg, var(--flacso-blue-dark), var(--flacso-blue));
            color: #ffffff;
            border-radius: 22px;
            padding: clamp(2rem, 4vw, 3.25rem);
            margin-bottom: 2rem;
            box-shadow: 0 18px 45px rgba(15, 26, 45, 0.14);
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

        .flacso-post-layout {
            background: #ffffff;
            border: 1px solid var(--flacso-border);
            border-radius: 22px;
            overflow: hidden;
            box-shadow: 0 8px 28px rgba(15, 26, 45, 0.05);
        }

        .flacso-featured-wrap {
            margin: 0;
            padding: clamp(1rem, 3vw, 2rem);
            background: #ffffff;
            border-bottom: 1px solid var(--flacso-border);
            display: flex;
            justify-content: center;
        }

        .flacso-featured-image {
            width: min(100%, 720px);
            height: auto;
            max-height: none;
            display: block;
            object-fit: contain;
            border-radius: 16px;
        }

        .flacso-post-content {
            max-width: 780px;
            margin: 0 auto;
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

        .flacso-more-news {
            margin-top: 2.25rem;
        }

        .flacso-more-news-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .flacso-more-news-header h2 {
            color: var(--flacso-blue);
            font-size: 1.35rem;
            margin: 0;
            font-weight: 800;
        }

        .flacso-more-news-header a {
            color: var(--flacso-blue);
            font-size: 0.95rem;
            font-weight: 700;
            text-decoration: none;
        }

        .flacso-more-news-header a:hover {
            text-decoration: underline;
        }

        .flacso-more-news-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 1rem;
        }

        .flacso-news-card {
            background: #ffffff;
            border: 1px solid var(--flacso-border);
            border-radius: 18px;
            overflow: hidden;
            transition: transform 0.18s ease, box-shadow 0.18s ease;
        }

        .flacso-news-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 26px rgba(15, 26, 45, 0.08);
        }

        .flacso-news-card-link {
            display: block;
            height: 100%;
            color: inherit;
            text-decoration: none;
        }

        .flacso-news-thumb {
            aspect-ratio: 1 / 1;
            background: #eef2f7;
            overflow: hidden;
        }

        .flacso-news-thumb img {
            width: 100%;
            height: 100%;
            display: block;
            object-fit: cover;
        }

        .flacso-news-card-body {
            padding: 1rem;
        }

        .flacso-news-date {
            display: block;
            color: var(--flacso-muted);
            font-size: 0.82rem;
            margin-bottom: 0.45rem;
        }

        .flacso-news-card h3 {
            color: var(--flacso-blue-dark);
            font-size: 1rem;
            line-height: 1.35;
            margin: 0;
            font-weight: 800;
        }

        @media (max-width: 780px) {
            .flacso-single-container {
                width: min(100% - 1.25rem, 1080px);
            }

            .flacso-post-hero {
                border-radius: 18px;
            }

            .flacso-post-title {
                font-size: clamp(1.85rem, 9vw, 2.6rem);
            }

            .flacso-post-layout {
                border-radius: 18px;
            }

            .flacso-featured-wrap {
                padding: 0.85rem;
            }

            .flacso-featured-image {
                border-radius: 12px;
            }

            .flacso-post-content {
                font-size: 1rem;
                line-height: 1.7;
            }

            .flacso-more-news-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>

<?php
endwhile;

get_footer();