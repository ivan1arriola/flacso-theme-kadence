<?php
/**
 * Template takeover para la categoría Novedades.
 *
 * @package kadence-child-flacso
 */

if (!defined('ABSPATH')) {
    exit;
}

get_header();

$category     = get_queried_object();
$current_slug = $category ? $category->slug : '';
$current_name = $category ? $category->name : 'Novedades';

$description = category_description();
?>

<main class="flacso-archive-novedades" role="main">
    <div class="flacso-archive-container">

        <header class="flacso-archive-hero">
            <div class="flacso-archive-hero-body">
                <nav class="flacso-breadcrumb" aria-label="Navegación">
                    <a href="<?php echo esc_url(home_url('/')); ?>">Inicio</a>
                    <span>/</span>
                    <span><?php echo esc_html($current_name); ?></span>
                </nav>

                <h1 class="flacso-archive-title"><?php echo esc_html($current_name); ?></h1>

                <?php if ($description) : ?>
                    <p class="flacso-archive-description"><?php echo wp_kses_post($description); ?></p>
                <?php else : ?>
                    <p class="flacso-archive-description">
                        Enterate de las últimas noticias, comunicados y artículos de interés de FLACSO Uruguay.
                    </p>
                <?php endif; ?>
            </div>
        </header>

        <?php if (have_posts()) : ?>
            <div class="flacso-archive-grid">
                <?php while (have_posts()) : the_post(); ?>
                    <article class="flacso-archive-card">
                        <a href="<?php the_permalink(); ?>" class="flacso-archive-card-link">
                            <?php if (has_post_thumbnail()) : ?>
                                <div class="flacso-archive-card-thumb">
                                    <?php the_post_thumbnail('medium_large'); ?>
                                </div>
                            <?php endif; ?>

                            <div class="flacso-archive-card-body">
                                <span class="flacso-archive-card-date"><?php echo esc_html(get_the_date()); ?></span>
                                <h2 class="flacso-archive-card-title"><?php the_title(); ?></h2>
                                <p class="flacso-archive-card-excerpt"><?php echo esc_html(wp_trim_words(get_the_excerpt(), 22, '…')); ?></p>
                            </div>
                        </a>
                    </article>
                <?php endwhile; ?>
            </div>

            <div class="flacso-archive-pagination">
                <?php
                the_posts_pagination(array(
                    'mid_size'  => 2,
                    'prev_text' => '&larr; Anterior',
                    'next_text' => 'Siguiente &rarr;',
                ));
                ?>
            </div>

        <?php else : ?>
            <div class="flacso-archive-empty">
                <p>No se encontraron novedades.</p>
            </div>
        <?php endif; ?>

    </div>
</main>


<?php
get_footer();
