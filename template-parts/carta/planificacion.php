<?php
/**
 * Carta · Sección 03: calendario, documentos, requisitos y costos.
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
    'url_inscripcion' => $url_inscripcion,
    'inscripciones_abiertas' => $inscripciones_abiertas,
    'calendario_url' => $calendario_url,
    'malla_curricular_document' => $malla_curricular_document,
    'calendario_y_malla_compartidos' => $calendario_y_malla_compartidos,
    'precios_filas' => $precios_filas,
    'precios_nota' => $precios_nota,
    'mostrar_precios_dolares' => $mostrar_precios_dolares,
    'precios_columnas' => $precios_columnas,
    'textos' => $textos,
] = $args['carta'];
?>
            <section id="fc-planificacion" class="fc-major-section fc-major-section--planning" aria-labelledby="fc-planning-title">
                <header class="fc-major-heading">
                    <span class="fc-major-heading__kicker" aria-hidden="true">03</span>
                    <div>
                        <h2 id="fc-planning-title"><?php esc_html_e('Calendario y costos', 'flacso-uruguay'); ?></h2>
                        <p class="fc-section-eyebrow"><?php esc_html_e('Planificación e inversión', 'flacso-uruguay'); ?></p>
                        <span class="fc-major-heading__rule" aria-hidden="true"></span>
                    </div>
                </header>

                <div class="fc-resource-grid">

            <!-- Dos datos independientes; solo se agrupa su representación visual si coinciden. -->
            <?php if ($calendario_url !== '' || $malla_curricular_document !== '') : ?>
                <section class="fc-section fc-documents-section">
                    <h2 class="fc-section-title">
                        <?php
                        if ($calendario_y_malla_compartidos) {
                            esc_html_e('Calendario y malla académica', 'flacso-uruguay');
                        } elseif ($calendario_url !== '' && $malla_curricular_document !== '') {
                            esc_html_e('Documentos académicos', 'flacso-uruguay');
                        } elseif ($calendario_url !== '') {
                            esc_html_e('Calendario académico', 'flacso-uruguay');
                        } else {
                            esc_html_e('Malla académica', 'flacso-uruguay');
                        }
                        ?>
                    </h2>
                    <div class="fc-more-grid">
                        <?php if ($calendario_url !== '') : ?>
                            <article class="fc-more-card">
                                <div class="fc-more-icon"><i class="bi bi-calendar2-check" aria-hidden="true"></i></div>
                                <h3>
                                    <?php echo esc_html($calendario_y_malla_compartidos
                                        ? __('Calendario y malla académica', 'flacso-uruguay')
                                        : __('Cronograma de cursada', 'flacso-uruguay')); ?>
                                </h3>
                                <p>
                                    <?php echo esc_html($calendario_y_malla_compartidos
                                        ? __('Fechas de cursada y estructura académica en un único documento.', 'flacso-uruguay')
                                        : __('Fechas clave, inicios y recesos de la cursada.', 'flacso-uruguay')); ?>
                                </p>
                                <a href="<?php echo esc_url($calendario_url); ?>" target="_blank" rel="noopener noreferrer" class="fc-card-button">
                                    <i class="bi bi-file-earmark-pdf" aria-hidden="true"></i>
                                    <?php echo esc_html($calendario_y_malla_compartidos
                                        ? __('Ver calendario y malla académica (PDF)', 'flacso-uruguay')
                                        : __('Ver calendario (PDF)', 'flacso-uruguay')); ?>
                                </a>
                            </article>
                        <?php endif; ?>
                        <?php if ($malla_curricular_document !== '' && !$calendario_y_malla_compartidos) : ?>
                            <article class="fc-more-card">
                                <div class="fc-more-icon"><i class="bi bi-diagram-3" aria-hidden="true"></i></div>
                                <h3><?php esc_html_e('Malla académica', 'flacso-uruguay'); ?></h3>
                                <p><?php esc_html_e('Organización de contenidos y trayectoria curricular.', 'flacso-uruguay'); ?></p>
                                <a href="<?php echo esc_url($malla_curricular_document); ?>" target="_blank" rel="noopener noreferrer" class="fc-card-button">
                                    <i class="bi bi-file-earmark-pdf" aria-hidden="true"></i>
                                    <?php esc_html_e('Ver malla académica (PDF)', 'flacso-uruguay'); ?>
                                </a>
                            </article>
                        <?php endif; ?>
                    </div>
                </section>
            <?php endif; ?>

            <!-- REQUISITOS DE POSTULACIÓN: fallback solo cuando no hay requisitos cargados -->
            <?php if (empty($academic['requisitos_ingreso_html'])) : ?>
                <section class="fc-section">
                    <h2 class="fc-section-title"><?php esc_html_e('Requisitos de Postulación y Admisión', 'flacso-uruguay'); ?></h2>
                    
                    <div class="fc-requirements-grid">
                        <div class="fc-requirement">
                            <div class="fc-requirement-icon"><i class="bi bi-mortarboard" aria-hidden="true"></i></div>
                            <div class="fc-requirement-content">
                                <strong><?php esc_html_e('Título universitario o terciario', 'flacso-uruguay'); ?></strong>
                                <p><?php esc_html_e('Poseer título de grado o formación terciaria equivalente en áreas afines.', 'flacso-uruguay'); ?></p>
                            </div>
                        </div>

                        <div class="fc-requirement">
                            <div class="fc-requirement-icon"><i class="bi bi-file-earmark-person" aria-hidden="true"></i></div>
                            <div class="fc-requirement-content">
                                <strong><?php esc_html_e('Currículum Vitae', 'flacso-uruguay'); ?></strong>
                                <p><?php esc_html_e('CV actualizado detallando trayectoria académica y profesional.', 'flacso-uruguay'); ?></p>
                            </div>
                        </div>

                        <div class="fc-requirement">
                            <div class="fc-requirement-icon"><i class="bi bi-person-vcard" aria-hidden="true"></i></div>
                            <div class="fc-requirement-content">
                                <strong><?php esc_html_e('Documento de Identidad', 'flacso-uruguay'); ?></strong>
                                <p><?php esc_html_e('Copia de cédula de identidad o pasaporte vigente.', 'flacso-uruguay'); ?></p>
                            </div>
                        </div>

                        <div class="fc-requirement">
                            <div class="fc-requirement-icon"><i class="bi bi-chat-square-quote" aria-hidden="true"></i></div>
                            <div class="fc-requirement-content">
                                <strong><?php esc_html_e('Carta de motivación', 'flacso-uruguay'); ?></strong>
                                <p><?php esc_html_e('Exposición de motivos e interés en cursar la propuesta.', 'flacso-uruguay'); ?></p>
                            </div>
                        </div>
                    </div>

                </section>
            <?php endif; ?>

            <!-- TABLA DE PRECIOS E INVERSIÓN -->
            <section class="fc-section fc-pricing-section">
                <h2 class="fc-section-title"><?php esc_html_e('Costos y Financiación', 'flacso-uruguay'); ?></h2>

                <?php if (!empty($precios_filas)) : ?>
                    <div class="fc-pricing-wrapper">
                        <div class="fc-table-wrap">
                            <table class="fc-pricing-table">
                                <thead>
                                    <tr>
                                        <th scope="col"><?php esc_html_e('Concepto', 'flacso-uruguay'); ?></th>
                                        <th scope="col">
                                            <?php esc_html_e('Valor en $', 'flacso-uruguay'); ?>
                                            <span><?php esc_html_e('residentes en Uruguay', 'flacso-uruguay'); ?></span>
                                        </th>
                                        <?php if ($mostrar_precios_dolares) : ?>
                                            <th scope="col">
                                                <?php esc_html_e('Valor en U$S', 'flacso-uruguay'); ?>
                                                <span><?php esc_html_e('residentes en el exterior', 'flacso-uruguay'); ?></span>
                                            </th>
                                        <?php endif; ?>
                                        <?php foreach ($precios_columnas as $columna) : ?>
                                            <th scope="col"><?php echo esc_html((string) $columna['titulo']); ?></th>
                                        <?php endforeach; ?>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($precios_filas as $row) :
                                        if (!is_array($row)) continue;
                                        $concepto_row = $row['concepto'] ?? ($row['concept'] ?? '');
                                        $uyu_row      = $row['uyu'] ?? ($row['uy'] ?? '');
                                        $usd_row      = $row['usd'] ?? ($row['us'] ?? '');
                                        $detalle_row  = (string) ($row['detalle'] ?? '');
                                        $extras_row   = is_array($row['extras'] ?? null) ? $row['extras'] : [];
                                        $is_destacada = !empty($row['destacada']) || !empty($row['highlight']);
                                    ?>
                                        <tr class="<?php echo $is_destacada ? 'is-highlighted' : ''; ?>">
                                            <td data-label="<?php esc_attr_e('Concepto', 'flacso-uruguay'); ?>"><span><?php echo wp_kses_post((string) $concepto_row); ?><?php if ($detalle_row !== '') : ?><small class="fc-pricing-detail"><?php echo esc_html($detalle_row); ?></small><?php endif; ?></span></td>
                                            <td data-label="<?php esc_attr_e('Valor en pesos', 'flacso-uruguay'); ?>"><?php echo wp_kses_post((string) $uyu_row); ?></td>
                                            <?php if ($mostrar_precios_dolares) : ?>
                                                <td data-label="<?php esc_attr_e('Valor en dólares', 'flacso-uruguay'); ?>"><?php echo wp_kses_post((string) $usd_row); ?></td>
                                            <?php endif; ?>
                                            <?php foreach ($precios_columnas as $columna) : ?>
                                                <td data-label="<?php echo esc_attr((string) $columna['titulo']); ?>"><?php echo esc_html((string) ($extras_row[$columna['id']] ?? '')); ?></td>
                                            <?php endforeach; ?>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <?php if (!empty($precios_nota)) : ?>
                            <div class="fc-pricing-note">
                                <i class="bi bi-info-circle me-1" aria-hidden="true"></i> <?php echo wp_kses_post($precios_nota); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php else : ?>
                    <div class="fc-empty-pricing">
                        <i class="bi bi-currency-dollar" aria-hidden="true"></i>
                        <p>
                            <?php
                            printf(
                                /* translators: %s: email link */
                                esc_html__('Para consultar los costos, planes de financiación y políticas de becas, ponte en contacto con %s.', 'flacso-uruguay'),
                                '<a href="mailto:' . esc_attr($textos['correo_inscripciones']) . '">' . esc_html($textos['correo_inscripciones']) . '</a>'
                            );
                            ?>
                        </p>
                    </div>
                <?php endif; ?>

                <?php if ($inscripciones_abiertas) : ?>
                    <div class="fc-pricing-cta">
                        <div>
                            <span><?php esc_html_e('¿Querés avanzar?', 'flacso-uruguay'); ?></span>
                            <strong><?php esc_html_e('Podés iniciar tu preinscripción ahora.', 'flacso-uruguay'); ?></strong>
                        </div>
                        <a data-flacso-preinscription-cta="1" href="<?php echo esc_url($url_inscripcion); ?>">
                            <i class="bi bi-pencil-square" aria-hidden="true"></i>
                            <?php esc_html_e('Preinscribirme', 'flacso-uruguay'); ?>
                        </a>
                    </div>
                <?php endif; ?>
            </section>

                </div><!-- /.fc-resource-grid -->
            </section>
