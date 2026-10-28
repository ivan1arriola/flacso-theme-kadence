<?php
/**
 * Carta · Secciones 01 y 02: propuesta y experiencia académica.
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
    'hay_bloques_detallados' => $hay_bloques_detallados,
    'detalles_academicos' => $detalles_academicos,
    'hay_detalles_academicos' => $hay_detalles_academicos,
    'mensaje_bienvenida' => $mensaje_bienvenida,
    'child_content' => $child_content,
    'fc_audience_summary' => $fc_audience_summary,
    'fc_long_description' => $fc_long_description,
] = $args['carta'];
?>
            <!-- CONTENIDO DETALLADO -->
            <section class="fc-content-sections" aria-label="<?php esc_attr_e('Información detallada del programa', 'flacso-uruguay'); ?>">
                <?php if (!empty($mensaje_bienvenida)) : ?>
                    <div class="fc-message-box fc-message-box-standalone">
                        <?php echo wp_kses_post($mensaje_bienvenida); ?>
                    </div>
                <?php endif; ?>

                <?php if ($hay_bloques_detallados) : ?>
                    <div class="fc-academic-layout">
                        <section class="fc-major-section fc-major-section--overview" aria-labelledby="fc-overview-title">
                            <header class="fc-major-heading">
                                <span class="fc-major-heading__kicker" aria-hidden="true">01</span>
                                <div>
                                    <h2 id="fc-overview-title"><?php esc_html_e('Sobre la propuesta', 'flacso-uruguay'); ?></h2>
                                    <p class="fc-section-eyebrow"><?php esc_html_e('Información general', 'flacso-uruguay'); ?></p>
                                    <span class="fc-major-heading__rule" aria-hidden="true"></span>
                                </div>
                            </header>
                            <?php if (!empty($academic['descripcion_html'])) : ?>
                            <article class="fc-content-panel fc-content-panel--primary fc-editorial-intro" aria-labelledby="fc-panel-descripcion">
                                <div class="fc-general-grid<?php echo $fc_long_description ? ' fc-general-grid--long' : ($fc_audience_summary !== '' ? ' fc-general-grid--with-audience' : ''); ?>">
                                    <div class="fc-general-copy">
                                        <span class="fc-editorial-dots" aria-hidden="true"><i></i><i></i></span>
                                        <h3 id="fc-panel-descripcion"><?php esc_html_e('Información general', 'flacso-uruguay'); ?></h3>
                                        <div class="fc-content-panel-body">
                                            <?php echo wp_kses_post($academic['descripcion_html']); ?>
                                        </div>
                                    </div>

                                    <?php if ($fc_audience_summary !== '') : ?>
                                        <aside class="fc-audience-panel">
                                            <div class="fc-audience-panel__copy">
                                                <span class="fc-audience-panel__icon"><i class="bi bi-people-fill" aria-hidden="true"></i></span>
                                                <div>
                                                    <h3><?php esc_html_e('¿A quiénes está dirigido?', 'flacso-uruguay'); ?></h3>
                                                    <p><?php echo esc_html($fc_audience_summary); ?></p>
                                                </div>
                                            </div>
                                        </aside>
                                    <?php endif; ?>
                                </div>
                            </article>
                            <?php endif; ?>
                        </section>

                        <section class="fc-major-section fc-major-section--experience" aria-labelledby="fc-experience-title">
                            <header class="fc-major-heading">
                                <span class="fc-major-heading__kicker" aria-hidden="true">02</span>
                                <div>
                                    <h2 id="fc-experience-title"><?php esc_html_e('Experiencia académica', 'flacso-uruguay'); ?></h2>
                                    <p class="fc-section-eyebrow"><?php esc_html_e('Contenidos y estructura académica', 'flacso-uruguay'); ?></p>
                                    <span class="fc-major-heading__rule" aria-hidden="true"></span>
                                </div>
                            </header>

                            <div class="fc-experience-grid">
                        <?php if (!empty($academic['modalidad_html'])) : ?>
                            <article class="fc-content-panel fc-content-panel--modality" aria-labelledby="fc-panel-modalidad">
                                <div class="fc-content-panel__icon"><i class="bi bi-laptop" aria-hidden="true"></i></div>
                                <div>
                                    <h2 id="fc-panel-modalidad"><?php esc_html_e('Modalidad de Cursada', 'flacso-uruguay'); ?></h2>
                                    <div class="fc-content-panel-body">
                                        <?php echo wp_kses_post($academic['modalidad_html']); ?>
                                    </div>
                                </div>
                            </article>
                        <?php endif; ?>

                        <?php if ($hay_detalles_academicos) : ?>
                            <div class="fc-academic-details" aria-label="<?php esc_attr_e('Detalles académicos', 'flacso-uruguay'); ?>">
                                <div class="fc-academic-details__heading">
                                    <span class="fc-academic-details__icon"><i class="bi bi-book" aria-hidden="true"></i></span>
                                    <h2><?php esc_html_e('Contenidos y estructura académica', 'flacso-uruguay'); ?></h2>
                                </div>
                                <?php foreach ($detalles_academicos as $fc_detail_key => $fc_detail_label) :
                                    if (empty($academic[$fc_detail_key])) {
                                        continue;
                                    }
                                ?>
                                    <details class="fc-academic-detail">
                                        <summary>
                                            <span><?php echo esc_html($fc_detail_label); ?></span>
                                            <i class="bi bi-plus-lg" aria-hidden="true"></i>
                                        </summary>
                                        <div class="fc-academic-detail__body">
                                            <?php echo wp_kses_post($academic[$fc_detail_key]); ?>
                                        </div>
                                    </details>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                            </div>
                        </section>
                    </div>
                <?php endif; ?>

                <?php if ($child_content !== '') : ?>
                    <article class="fc-content-panel fc-content-wrapper fc-content-wrapper-compact fc-child-content" aria-label="<?php esc_attr_e('Contenido adicional del programa', 'flacso-uruguay'); ?>">
                        <div class="fc-content-panel-body">
                            <?php echo wp_kses_post($child_content); ?>
                        </div>
                    </article>
                <?php endif; ?>
            </section>
