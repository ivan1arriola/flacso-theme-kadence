<?php
/**
 * Página pública de preguntas frecuentes.
 *
 * El plugin administra el contenido y el tema solamente define su presentación.
 *
 * @package kadence-child-flacso
 */

if (!defined('ABSPATH')) {
    exit;
}

get_header();
?>
<main class="flacso-faq-page">
    <div class="flacso-faq-page__shell">
        <nav class="flacso-faq-page__breadcrumb" aria-label="<?php esc_attr_e('Navegación', 'kadence-child-flacso'); ?>">
            <a href="<?php echo esc_url(home_url('/')); ?>"><?php esc_html_e('Inicio', 'kadence-child-flacso'); ?></a>
            <span aria-hidden="true">/</span>
            <span><?php esc_html_e('Preguntas frecuentes', 'kadence-child-flacso'); ?></span>
        </nav>

        <?php
        if (class_exists('FLACSO_Preguntas_Frecuentes')) {
            echo FLACSO_Preguntas_Frecuentes::render([
                'id'           => 'faq-flacso',
                'titulo'       => __('Preguntas frecuentes', 'kadence-child-flacso'),
                'intro'        => __('Aquí encontrarás respuestas a las preguntas más frecuentes sobre la oferta educativa de la Facultad Latinoamericana de Ciencias Sociales (FLACSO) Uruguay.', 'kadence-child-flacso'),
                'nivel_titulo' => 'h1',
            ]); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        } elseif (current_user_can('manage_options')) {
            echo '<p class="flacso-faq__empty">'
                . esc_html__('El módulo de preguntas frecuentes no está disponible.', 'kadence-child-flacso')
                . '</p>';
        }
        ?>
    </div>
</main>
<?php
get_footer();
