<?php
/**
 * Carta · Sección 04: información práctica (pagos, título, envíos, financiación).
 *
 * Presenta datos ya resueltos por flacso_theme_get_carta_view().
 *
 * @package kadence-child-flacso
 */

if (!defined('ABSPATH')) {
    exit;
}

[
    'textos' => $textos,
] = $args['carta'];
?>
            <!-- INFORMACIÓN PRÁCTICA -->
            <section id="fc-practica" class="fc-section fc-info-important fc-major-section fc-major-section--practical">
                <header class="fc-major-heading">
                    <span class="fc-major-heading__kicker" aria-hidden="true">04</span>
                    <div>
                        <h2><?php esc_html_e('Información práctica', 'flacso-uruguay'); ?></h2>
                        <span class="fc-major-heading__rule" aria-hidden="true"></span>
                    </div>
                </header>

                <?php
                // Cada texto sale de los ajustes del plugin y ya viene resuelto según su regla
                // (FLACSO → Textos de la carta): si la regla no se cumple, o está vacío, no se muestra.
                $filas_informativas = [
                    ['icono' => 'bi-currency-dollar', 'texto' => $textos['pago_dolares']],
                    ['icono' => 'bi-currency-exchange', 'texto' => $textos['pago_pesos']],
                    ['icono' => 'bi-calendar-check', 'texto' => $textos['vencimiento_cuotas']],
                    ['icono' => 'bi-award', 'texto' => $textos['titulo_exterior']],
                    ['icono' => 'bi-award', 'texto' => $textos['titulo_sin_costo']],
                    ['icono' => 'bi-truck', 'texto' => $textos['envio_titulos']],
                ];
                ?>
                <div class="fc-info-list">
                    <?php foreach ($filas_informativas as $fila) : ?>
                        <?php if ($fila['texto'] === '') { continue; } ?>
                        <div class="fc-info-row">
                            <div class="fc-info-icon"><i class="bi <?php echo esc_attr($fila['icono']); ?>" aria-hidden="true"></i></div>
                            <div><?php echo esc_html($fila['texto']); ?></div>
                        </div>
                    <?php endforeach; ?>

                    <?php if ($textos['convenio_iin_texto'] !== '') : ?>
                        <div class="fc-info-row">
                            <div class="fc-info-icon"><i class="bi bi-globe" aria-hidden="true"></i></div>
                            <div><?php if ($textos['convenio_iin_titulo'] !== '') : ?><strong><?php echo esc_html($textos['convenio_iin_titulo']); ?></strong> <?php endif; ?><?php echo esc_html($textos['convenio_iin_texto']); ?></div>
                        </div>
                    <?php endif; ?>

                    <?php if ($textos['financiacion_label'] !== '' || $textos['correo_inscripciones'] !== '') : ?>
                        <div class="fc-info-row">
                            <div class="fc-info-icon"><i class="bi bi-envelope" aria-hidden="true"></i></div>
                            <div>
                                <strong><?php echo esc_html($textos['financiacion_label']); ?></strong>
                                <a href="mailto:<?php echo esc_attr($textos['correo_inscripciones']); ?>"><?php echo esc_html($textos['correo_inscripciones']); ?></a>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="fc-action-buttons">
                    <a href="https://flacso.edu.uy/convenios/" target="_blank" rel="noopener" aria-label="<?php esc_attr_e('Ver convenios de FLACSO Uruguay', 'flacso-uruguay'); ?>">
                        <i class="bi bi-file-earmark-text" aria-hidden="true"></i>
                        <?php esc_html_e('Ver convenios', 'flacso-uruguay'); ?>
                    </a>

                    <a href="https://flacso.edu.uy/formas-de-pago/" target="_blank" rel="noopener" aria-label="<?php esc_attr_e('Ver formas de pago de FLACSO Uruguay', 'flacso-uruguay'); ?>">
                        <i class="bi bi-credit-card" aria-hidden="true"></i>
                        <?php esc_html_e('Ver formas de pago', 'flacso-uruguay'); ?>
                    </a>
                </div>
            </section>
