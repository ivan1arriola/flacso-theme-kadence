<?php
/**
 * The template for displaying all single posts
 *
 * @package kadence-child-flacso
 */

if (!defined('ABSPATH')) {
    exit;
}

get_header();

while (have_posts()) :
    the_post();

    // Color institucional FLACSO con gradiente moderno
    $hero_gradient = 'linear-gradient(135deg, #0f172a 0%, #1e293b 100%)';
    $post_categories = get_the_category();
    $primary_category = !empty($post_categories) ? $post_categories[0]->name : 'Novedad';
    ?>

    <div class="flacso-post-page">
        <div class="post-container">
            
            <!-- Breadcrumb Moderno -->
            <nav class="post-breadcrumb" aria-label="Navegación">
                <a href="<?php echo esc_url(home_url('/')); ?>">Inicio</a>
                <span class="sep">/</span>
                <a href="<?php echo esc_url(get_permalink(get_option('page_for_posts'))); ?>">Noticias</a>
                <span class="sep">/</span>
                <span class="current"><?php echo esc_html($primary_category); ?></span>
            </nav>

            <!-- Hero Section Premium -->
            <header class="post-hero">
                <div class="hero-bg" style="background: <?php echo esc_attr($hero_gradient); ?>;"></div>
                <div class="hero-content">
                    <div class="hero-main">
                        <div class="hero-text">
                            <span class="hero-badge"><?php echo esc_html($primary_category); ?></span>
                            <h1 class="hero-name"><?php the_title(); ?></h1>
                            <div class="hero-meta">
                                <span class="meta-date">📅 <?php echo get_the_date(); ?></span>
                                <span class="meta-author">✍️ <?php the_author(); ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <div class="post-grid">
                <!-- Columna Principal: Contenido del Post -->
                <main class="post-main">
                    <article class="post-card content-section">
                        <?php if (has_post_thumbnail()) : ?>
                            <div class="post-featured-image">
                                <?php the_post_thumbnail('large', ['class' => 'featured-img']); ?>
                            </div>
                        <?php endif; ?>
                        
                        <div class="post-body">
                            <?php 
                            the_content(); 
                            
                            wp_link_pages(array(
                                'before' => '<div class="page-links">' . esc_html__('Páginas:', 'kadence-child-flacso'),
                                'after'  => '</div>',
                            ));
                            ?>
                        </div>
                    </article>
                </main>

                <!-- Sidebar: Novedades Recientes -->
                <aside class="post-sidebar">
                    <section class="post-card recent-posts-section">
                        <div class="card-header">
                            <h3>Últimas Novedades</h3>
                        </div>
                        
                        <div class="recent-posts-list">
                            <?php
                            $recent_posts = new WP_Query(array(
                                'post_type' => 'post',
                                'posts_per_page' => 4,
                                'post__not_in' => array(get_the_ID()),
                            ));

                            if ($recent_posts->have_posts()) :
                                while ($recent_posts->have_posts()) : $recent_posts->the_post();
                                ?>
                                    <div class="recent-item">
                                        <a href="<?php the_permalink(); ?>" class="recent-title"><?php the_title(); ?></a>
                                        <span class="recent-date"><?php echo get_the_date(); ?></span>
                                    </div>
                                <?php
                                endwhile;
                                wp_reset_postdata();
                            else :
                                echo '<p class="empty-compact">No hay otras noticias.</p>';
                            endif;
                            ?>
                        </div>
                    </section>
                </aside>
            </div>
        </div>
    </div>

    <style>
        .flacso-post-page {
            --slate-50: #f8fafc;
            --slate-100: #f1f5f9;
            --slate-200: #e2e4e7;
            --slate-300: #cbd5e1;
            --slate-400: #94a3b8;
            --slate-600: #475569;
            --slate-700: #334155;
            --slate-800: #1e293b;
            --slate-900: #0f172a;
            --primary: #1d3a72;
            --accent: #fed222;
            
            background-color: var(--slate-50);
            min-height: 100vh;
            padding-bottom: 5rem;
            font-family: 'Inter', -apple-system, sans-serif;
            color: var(--slate-800);
        }

        .post-container {
            max-width: 1140px;
            margin: 0 auto;
            padding: 0 1.5rem;
        }

        /* Breadcrumb */
        .post-breadcrumb {
            padding: 1.5rem 0;
            font-size: 0.85rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: var(--slate-400);
        }
        .post-breadcrumb a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 500;
        }
        .post-breadcrumb .current { color: var(--slate-600); }

        /* Hero Section */
        .post-hero {
            position: relative;
            border-radius: 24px;
            overflow: hidden;
            margin-bottom: 2rem;
            box-shadow: 0 20px 50px rgba(15, 23, 42, 0.15);
        }
        .hero-bg {
            position: absolute;
            inset: 0;
            z-index: 1;
        }
        .hero-content {
            position: relative;
            z-index: 2;
            padding: 4rem 3rem;
        }
        .hero-badge {
            display: inline-block;
            padding: 0.35rem 1rem;
            background: var(--accent);
            color: var(--slate-900);
            font-size: 0.75rem;
            font-weight: 800;
            text-transform: uppercase;
            border-radius: 6px;
            margin-bottom: 1.5rem;
        }
        .hero-name {
            color: #fff;
            font-size: clamp(2rem, 4vw, 3.5rem);
            margin: 0 0 1.5rem 0;
            font-weight: 800;
            line-height: 1.2;
        }
        .hero-meta {
            display: flex;
            gap: 1.5rem;
            color: var(--slate-300);
            font-size: 0.95rem;
            font-weight: 500;
        }

        /* Grid */
        .post-grid {
            display: grid;
            grid-template-columns: 1fr 340px;
            gap: 2rem;
        }

        /* Cards */
        .post-card {
            background: #fff;
            border-radius: 20px;
            border: 1px solid var(--slate-200);
            box-shadow: 0 4px 20px rgba(15, 23, 42, 0.04);
            overflow: hidden;
        }
        .post-featured-image {
            width: 100%;
            height: auto;
            max-height: 450px;
            overflow: hidden;
        }
        .featured-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }
        .post-body {
            padding: 2.5rem;
            font-size: 1.15rem;
            line-height: 1.8;
            color: var(--slate-700);
        }
        
        .post-body p { margin-bottom: 1.5rem; }
        .post-body h2, .post-body h3 {
            color: var(--primary);
            font-weight: 700;
            margin-top: 2rem;
            margin-bottom: 1rem;
        }
        .post-body img {
            max-width: 100%;
            height: auto;
            border-radius: 12px;
            margin: 1.5rem 0;
        }

        .card-header {
            background: var(--slate-50);
            padding: 1.5rem;
            border-bottom: 1px solid var(--slate-100);
        }
        .card-header h3 { margin: 0; color: var(--primary); font-size: 1.1rem; font-weight: 700; }

        /* Sidebar */
        .post-sidebar { display: flex; flex-direction: column; gap: 1.5rem; }
        
        .recent-posts-list { padding: 1.5rem; display: flex; flex-direction: column; gap: 1.25rem; }
        .recent-item {
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
            padding-bottom: 1.25rem;
            border-bottom: 1px solid var(--slate-100);
        }
        .recent-item:last-child { border-bottom: none; padding-bottom: 0; }
        .recent-title {
            color: var(--slate-800);
            text-decoration: none;
            font-weight: 600;
            line-height: 1.4;
            transition: color 0.2s ease;
        }
        .recent-title:hover { color: var(--primary); text-decoration: underline; }
        .recent-date { font-size: 0.8rem; color: var(--slate-400); }

        /* Responsive */
        @media (max-width: 992px) {
            .post-grid { grid-template-columns: 1fr; }
            .hero-content { padding: 2.5rem 1.5rem; }
            .post-body { padding: 1.5rem; }
        }
    </style>

<?php
endwhile;
get_footer();
