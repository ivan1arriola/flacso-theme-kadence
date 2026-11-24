<?php
/**
 * Carta · Portada: título, distintivos, datos clave e imagen.
 *
 * Presenta datos ya resueltos por flacso_theme_get_carta_view().
 *
 * @package kadence-child-flacso
 */

if (!defined('ABSPATH')) {
    exit;
}

[
    'titulo' => $titulo,
    'tipo_oferta' => $tipo_oferta,
    'thumbnail_url' => $thumbnail_url,
    'abreviacion' => $abreviacion,
    'cohorte_label' => $cohorte_label,
    'anio' => $anio,
    'proximo_inicio' => $proximo_inicio,
    'duracion' => $duracion,
    'modalidad' => $modalidad,
    'url_inscripcion' => $url_inscripcion,
    'inscripciones_abiertas' => $inscripciones_abiertas,
    'hay_bloques_detallados' => $hay_bloques_detallados,
    'fc_hero_summary' => $fc_hero_summary,
    'hay_distintivos' => $hay_distintivos,
    'textos' => $textos,
] = $args['carta'];
?>
        <!-- HERO SECTION -->
        <section class="fc-hero" aria-labelledby="fc-carta-title">
            <div class="fc-hero-inner">
                <div class="fc-hero-copy">
                    <div class="fc-eyebrow">
                        <i class="bi bi-stars" aria-hidden="true"></i>
                        <span><?php echo esc_html(flacso_theme_brand_slogan()); ?></span>
                    </div>

                    <?php if ($textos['badge_presenciales'] !== '') : ?>
                        <div class="fc-hero-highlights fc-hero-highlights--top">
                            <span class="fc-hero-badge fc-hero-badge--accent">
                                <i class="bi bi-geo-alt" aria-hidden="true"></i>
                                <?php echo esc_html($textos['badge_presenciales']); ?>
                            </span>
                        </div>
                    <?php endif; ?>

                    <div class="fc-hero-meta">
                        <?php if (!empty($tipo_oferta)) : ?>
                            <span><i class="bi bi-mortarboard" aria-hidden="true"></i> <?php echo esc_html($tipo_oferta); ?></span>
                        <?php endif; ?>

                        <?php if (!empty($abreviacion)) : ?>
                            <span><?php echo esc_html($abreviacion . ' ' . $anio); ?></span>
                        <?php endif; ?>

                        <?php if (!empty($cohorte_label)) : ?>
                            <span><?php echo esc_html($cohorte_label); ?></span>
                        <?php endif; ?>
                    </div>

                    <h1 id="fc-carta-title"><?php echo esc_html($titulo); ?></h1>

                    <?php if ($fc_hero_summary !== '') : ?>
                        <p class="fc-hero-summary"><?php echo esc_html($fc_hero_summary); ?></p>
                    <?php endif; ?>

                    <?php if ($hay_distintivos) : ?>
                        <div class="fc-certifications">
                            <?php if ($textos['badge_mec'] !== '') : ?>
                                <div class="fc-certification">
                                    <i class="bi bi-award-fill" aria-hidden="true"></i>
                                    <span><?php echo esc_html($textos['badge_mec']); ?></span>
                                </div>
                            <?php endif; ?>

                            <?php if ($textos['badge_apostilla'] !== '') : ?>
                                <div class="fc-certification">
                                    <i class="bi bi-globe-americas" aria-hidden="true"></i>
                                    <span><?php echo esc_html($textos['badge_apostilla']); ?></span>
                                </div>
                            <?php endif; ?>

                            <?php if ($textos['badge_red'] !== '') : ?>
                                <div class="fc-certification">
                                    <i class="bi bi-globe" aria-hidden="true"></i>
                                    <span><?php echo esc_html($textos['badge_red']); ?></span>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($textos['badge_iin'] !== '') : ?>
                        <div class="fc-convenio-pill">
                            <i class="bi bi-shield-check" aria-hidden="true"></i>
                            <span><?php echo esc_html($textos['badge_iin']); ?></span>
                        </div>
                    <?php endif; ?>

                    <div class="fc-hero-actions">
                        <?php if ($inscripciones_abiertas) : ?>
                            <a data-flacso-preinscription-cta="1" href="<?php echo esc_url($url_inscripcion); ?>" class="fc-primary-button">
                                <i class="bi bi-pencil-square" aria-hidden="true"></i>
                                <?php esc_html_e('Preinscribirme', 'flacso-uruguay'); ?>
                            </a>
                        <?php endif; ?>

                        <?php $url_oferta = get_permalink(get_the_ID()); ?>
                        <?php if ($url_oferta) : ?>
                            <a href="<?php echo esc_url($url_oferta); ?>" class="fc-secondary-button">
                                <i class="bi bi-book" aria-hidden="true"></i>
                                <?php esc_html_e('Ver información completa', 'flacso-uruguay'); ?>
                                <i class="bi bi-arrow-up-right" aria-hidden="true"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- DATOS CLAVE: en la portada editorial -->
                <section id="fc-info-clave" class="fc-feature-grid">
                    <article class="fc-feature-card">
                        <div class="fc-feature-icon"><i class="bi bi-calendar-event" aria-hidden="true"></i></div>
                        <h3><?php esc_html_e('Próximo inicio', 'flacso-uruguay'); ?></h3>
                        <p><?php echo esc_html($proximo_inicio); ?></p>
                    </article>

                    <article class="fc-feature-card">
                        <div class="fc-feature-icon"><i class="bi bi-clock-history" aria-hidden="true"></i></div>
                        <h3><?php esc_html_e('Duración', 'flacso-uruguay'); ?></h3>
                        <p><?php echo esc_html($duracion); ?></p>
                    </article>

                    <article class="fc-feature-card">
                        <div class="fc-feature-icon"><i class="bi bi-laptop" aria-hidden="true"></i></div>
                        <h3><?php esc_html_e('Modalidad', 'flacso-uruguay'); ?></h3>
                        <p><?php echo esc_html($modalidad); ?></p>
                    </article>

                    <article class="fc-feature-card">
                        <div class="fc-feature-icon"><i class="bi bi-layers" aria-hidden="true"></i></div>
                        <h3><?php esc_html_e('Edición', 'flacso-uruguay'); ?></h3>
                        <p><?php echo esc_html($cohorte_label !== '' ? $cohorte_label : $anio); ?></p>
                    </article>

                    <?php if ($textos['badge_presenciales'] !== '') : ?>
                        <article class="fc-feature-card">
                            <div class="fc-feature-icon"><i class="bi bi-geo-alt-fill" aria-hidden="true"></i></div>
                            <h3><?php esc_html_e('Cursada', 'flacso-uruguay'); ?></h3>
                            <p><?php echo esc_html($textos['badge_presenciales']); ?></p>
                        </article>
                    <?php endif; ?>
                </section>

                <div class="fc-hero-visual">
                    <?php if ($thumbnail_url !== '') : ?>
                        <?php
                        $hero_thumb_id = (int) ($args['carta']['thumbnail_id'] ?? 0);
                        $hero_cover_attr = [
                            'alt' => '',
                            'loading' => 'eager',
                            'fetchpriority' => 'high',
                            'decoding' => 'async',
                            'sizes' => '(min-width: 1100px) 420px, 100vw',
                            'onerror' => "this.style.display='none'; if(this.nextElementSibling) this.nextElementSibling.style.display='flex';",
                        ];
                        $hero_cover_html = $hero_thumb_id > 0 && function_exists('wp_get_attachment_image')
                            ? wp_get_attachment_image($hero_thumb_id, 'large', false, $hero_cover_attr)
                            : '';
                        ?>
                        <div class="fc-cover-card">
                            <?php if ($hero_cover_html !== '') : echo $hero_cover_html; /* phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- HTML de wp_get_attachment_image */ else : ?>
                                <img src="<?php echo esc_url($thumbnail_url); ?>" alt="" loading="eager" fetchpriority="high" decoding="async" onerror="this.style.display='none'; if(this.nextElementSibling) this.nextElementSibling.style.display='flex';">
                            <?php endif; ?>
                            <div class="fc-cover-card fc-cover-placeholder" style="display:none;">
                                <i class="bi bi-mortarboard-fill" aria-hidden="true"></i>
                                <span><?php esc_html_e('FLACSO Uruguay', 'flacso-uruguay'); ?></span>
                            </div>
                        </div>
                    <?php else : ?>
                        <div class="fc-cover-card fc-cover-placeholder">
                            <i class="bi bi-mortarboard-fill" aria-hidden="true"></i>
                            <span><?php esc_html_e('FLACSO Uruguay', 'flacso-uruguay'); ?></span>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </section>
