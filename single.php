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



<?php
endwhile;

get_footer();
