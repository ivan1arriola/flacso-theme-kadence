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

    <<?php echo flacso_child_main_tag(); ?> class="flacso-single-post" id="main">
        <?php
        get_template_part(
            'template-parts/components/page-header',
            null,
            [
                'modifier'    => 'reading',
                'breadcrumb'  => [
                    ['label' => __('Inicio', 'flacso-uruguay'), 'url' => home_url('/')],
                    ['label' => __('Noticias', 'flacso-uruguay'), 'url' => $blog_url],
                    ['label' => $primary_category],
                ],
                'kicker'      => $primary_category,
                'title'       => get_the_title(),
                'lede'        => has_excerpt() ? wp_strip_all_tags(get_the_excerpt()) : '',
                'facts'       => [get_the_date()],
                'image_id'    => (int) get_post_thumbnail_id(),
                'image_size'  => 'large',
                'image_sizes' => '(max-width: 767px) 92vw, 360px',
            ]
        );
        ?>

        <div class="flacso-single-container">

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
                        'no_found_rows'       => true,
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
    </<?php echo flacso_child_main_tag(); ?>>



<?php
endwhile;

get_footer();
