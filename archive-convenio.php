<?php
/**
 * Archivo del CPT Convenio.
 *
 * @package kadence-child-flacso
 */

if (!defined('ABSPATH')) {
    exit;
}

get_header();

$convenios = new WP_Query([
    'post_type' => 'convenio',
    'post_status' => 'publish',
    'posts_per_page' => -1,
    'orderby' => 'title',
    'order' => 'ASC',
    'no_found_rows' => true,
]);
$total = (int) $convenios->post_count;
?>
<main class="flacso-convenios-archive" id="main" role="main">
    <header class="flacso-convenios-hero">
        <div class="flacso-convenios-container">
            <nav class="flacso-convenios-breadcrumb" aria-label="<?php esc_attr_e('Navegación', 'kadence-child-flacso'); ?>">
                <a href="<?php echo esc_url(home_url('/')); ?>"><?php esc_html_e('Inicio', 'kadence-child-flacso'); ?></a>
                <span aria-hidden="true">/</span>
                <span><?php esc_html_e('Convenios', 'kadence-child-flacso'); ?></span>
            </nav>
            <span class="flacso-convenios-eyebrow"><?php esc_html_e('Beneficios y cooperación', 'kadence-child-flacso'); ?></span>
            <h1><?php esc_html_e('Convenios', 'kadence-child-flacso'); ?></h1>
            <p><?php esc_html_e('Conocé las instituciones con las que FLACSO Uruguay mantiene acuerdos y beneficios vigentes.', 'kadence-child-flacso'); ?></p>

            <?php if ($total > 0) : ?>
                <div class="flacso-convenios-search">
                    <label for="convenios-search"><?php esc_html_e('Buscar convenio', 'kadence-child-flacso'); ?></label>
                    <div class="flacso-convenios-search__control">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="11" cy="11" r="7"></circle><path d="m20 20-4-4"></path></svg>
                        <input id="convenios-search" type="search" placeholder="<?php esc_attr_e('Buscar por nombre…', 'kadence-child-flacso'); ?>" autocomplete="off" data-convenios-search>
                    </div>
                    <p data-convenios-count aria-live="polite"><?php echo esc_html(sprintf(_n('%d convenio', '%d convenios', $total, 'kadence-child-flacso'), $total)); ?></p>
                </div>
            <?php endif; ?>
        </div>
    </header>

    <section class="flacso-convenios-listing" aria-label="<?php esc_attr_e('Listado de convenios', 'kadence-child-flacso'); ?>">
        <div class="flacso-convenios-container">
            <?php if ($convenios->have_posts()) : ?>
                <div class="flacso-convenios-grid" data-convenios-grid>
                    <?php while ($convenios->have_posts()) : $convenios->the_post(); ?>
                        <?php $search_text = remove_accents(wp_strip_all_tags(get_the_title())); ?>
                        <article class="flacso-convenio-card" data-convenio-card data-search="<?php echo esc_attr(strtolower($search_text)); ?>">
                            <a href="<?php the_permalink(); ?>">
                                <div class="flacso-convenio-card__logo">
                                    <?php the_post_thumbnail('medium_large', ['loading' => 'lazy', 'alt' => get_the_title()]); ?>
                                </div>
                                <h2><?php the_title(); ?></h2>
                                <?php if (has_excerpt()) : ?>
                                    <p><?php echo esc_html(wp_trim_words(get_the_excerpt(), 18, '…')); ?></p>
                                <?php endif; ?>
                                <span><?php esc_html_e('Ver convenio', 'kadence-child-flacso'); ?> <span aria-hidden="true">→</span></span>
                            </a>
                        </article>
                    <?php endwhile; ?>
                </div>
                <div class="flacso-convenios-empty" hidden data-convenios-empty>
                    <h2><?php esc_html_e('No encontramos coincidencias', 'kadence-child-flacso'); ?></h2>
                    <p><?php esc_html_e('Probá con otro nombre o una búsqueda más corta.', 'kadence-child-flacso'); ?></p>
                </div>
                <?php wp_reset_postdata(); ?>
            <?php else : ?>
                <div class="flacso-convenios-empty">
                    <h2><?php esc_html_e('Todavía no hay convenios publicados', 'kadence-child-flacso'); ?></h2>
                    <p><?php esc_html_e('Volvé a consultar próximamente.', 'kadence-child-flacso'); ?></p>
                </div>
            <?php endif; ?>
        </div>
    </section>
</main>
<?php
get_footer();
