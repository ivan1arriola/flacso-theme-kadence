<?php
/**
 * Página individual del CPT Convenio.
 *
 * @package kadence-child-flacso
 */

if (!defined('ABSPATH')) {
    exit;
}

get_header();

while (have_posts()) :
    the_post();
    $external_url = (string) get_post_meta(get_the_ID(), '_flacso_convenio_external_url', true);
    $content = trim((string) apply_filters('the_content', get_the_content()));
    ?>
    <main class="flacso-convenio-single" id="main" role="main">
        <div class="flacso-convenios-container">
            <nav class="flacso-convenios-breadcrumb" aria-label="<?php esc_attr_e('Navegación', 'kadence-child-flacso'); ?>">
                <a href="<?php echo esc_url(home_url('/')); ?>"><?php esc_html_e('Inicio', 'kadence-child-flacso'); ?></a>
                <span aria-hidden="true">/</span>
                <a href="<?php echo esc_url(get_post_type_archive_link('convenio')); ?>"><?php esc_html_e('Convenios', 'kadence-child-flacso'); ?></a>
                <span aria-hidden="true">/</span>
                <span><?php the_title(); ?></span>
            </nav>

            <article class="flacso-convenio-single__card">
                <div class="flacso-convenio-single__logo">
                    <?php the_post_thumbnail('large', ['alt' => get_the_title()]); ?>
                </div>
                <div class="flacso-convenio-single__content">
                    <span class="flacso-convenios-eyebrow"><?php esc_html_e('Convenio institucional', 'kadence-child-flacso'); ?></span>
                    <h1><?php the_title(); ?></h1>
                    <?php if ($content !== '') : ?>
                        <div class="flacso-convenio-single__description"><?php echo $content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
                    <?php endif; ?>
                    <div class="flacso-convenio-single__actions">
                        <?php if ($external_url !== '') : ?>
                            <a class="flacso-convenio-button flacso-convenio-button--primary" href="<?php echo esc_url($external_url); ?>" target="_blank" rel="noopener noreferrer">
                                <?php esc_html_e('Visitar sitio web', 'kadence-child-flacso'); ?>
                                <span aria-hidden="true">↗</span>
                            </a>
                        <?php endif; ?>
                        <a class="flacso-convenio-button" href="<?php echo esc_url(get_post_type_archive_link('convenio')); ?>">
                            <span aria-hidden="true">←</span> <?php esc_html_e('Ver todos los convenios', 'kadence-child-flacso'); ?>
                        </a>
                    </div>
                </div>
            </article>
        </div>
    </main>
    <?php
endwhile;

get_footer();
