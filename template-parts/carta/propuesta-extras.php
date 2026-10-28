<?php
/**
 * Carta · Menciones, orientaciones, articulaciones y títulos intermedios.
 *
 * Presenta datos ya resueltos por flacso_theme_get_carta_view().
 *
 * @package kadence-child-flacso
 */

if (!defined('ABSPATH')) {
    exit;
}

[
    'academic' => $academic,
    'arr_menciones' => $arr_menciones,
    'arr_orientaciones' => $arr_orientaciones,
    'arr_titulos_intermedios' => $arr_titulos_intermedios,
] = $args['carta'];
?>
        <?php if (!empty($arr_menciones) || !empty($arr_orientaciones)) : ?>
            <section class="fc-specializations" aria-label="<?php esc_attr_e('Menciones y orientaciones de la propuesta', 'flacso-uruguay'); ?>">
                <div class="fc-specializations__inner">
                    <?php if (!empty($arr_menciones)) : ?>
                        <div class="fc-chip-block">
                            <p><?php esc_html_e('Con mención en:', 'flacso-uruguay'); ?></p>
                            <div class="fc-chip-grid">
                                <?php foreach ($arr_menciones as $mencion) : ?>
                                    <span><?php echo esc_html($mencion); ?></span>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($arr_orientaciones)) : ?>
                        <div class="fc-chip-block">
                            <p><?php esc_html_e('Orientaciones metodológicas:', 'flacso-uruguay'); ?></p>
                            <div class="fc-chip-grid">
                                <?php foreach ($arr_orientaciones as $orientacion) : ?>
                                    <span><?php echo esc_html($orientacion); ?></span>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                </div>
            </section>
        <?php endif; ?>

        <?php if (!empty($academic['articulaciones'])) : ?>
            <div class="fc-page-container">
                <?php get_template_part('template-parts/oferta/articulaciones', null, ['planes' => $academic['articulaciones']]); ?>
            </div>
        <?php endif; ?>

        <!-- TÍTULOS INTERMEDIOS -->
        <?php if (!empty($arr_titulos_intermedios)) : ?>
            <div class="fc-intermediate-strip">
                <div class="fc-intermediate-grid">
                    <?php foreach ($arr_titulos_intermedios as $titulo_intermedio) : ?>
                        <div class="fc-intermediate-card">
                            <div class="fc-intermediate-icon"><i class="bi bi-award-fill" aria-hidden="true"></i></div>
                            <div class="fc-intermediate-content">
                                <div class="fc-intermediate-copy">
                                    <div class="fc-intermediate-kicker"><?php esc_html_e('Título intermedio', 'flacso-uruguay'); ?></div>
                                    <h3><a href="<?php echo esc_url($titulo_intermedio['url']); ?>"><?php echo esc_html($titulo_intermedio['titulo']); ?></a></h3>
                                    <?php if (!empty($titulo_intermedio['menciones'])) : ?>
                                        <p class="fc-intermediate-mentions">
                                            <strong><?php esc_html_e(count($titulo_intermedio['menciones']) === 1 ? 'Mención:' : 'Menciones:', 'flacso-uruguay'); ?></strong>
                                            <?php echo esc_html(implode(' · ', $titulo_intermedio['menciones'])); ?>
                                        </p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
