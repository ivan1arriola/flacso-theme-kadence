<?php
/**
 * Vista individual de un Seminario (simple o integrado).
 *
 * Vista individual del Custom Post Type seminario y su edicion vigente.
 * Consume el View Model normalizado provisto en $args['context'].
 *
 * @package kadence-child-flacso
 */

if (!defined('ABSPATH')) {
    exit;
}

$context = isset($args['context']) && is_array($args['context']) ? $args['context'] : null;
if (!$context) {
    $post_id = get_the_ID();
    $context = function_exists('flacso_theme_get_seminar_context') ? flacso_theme_get_seminar_context($post_id) : [];
}

if (empty($context)) {
    return;
}

$post_id          = (int) $context['offer_id'];
$title            = (string) $context['title'];
$academic         = $context['academic'] ?? [];
$instance         = $context['instance'] ?? [];
$registration     = $context['registration'] ?? [];
$relations        = $context['relations'] ?? [];

$is_integrated    = !empty($context['is_integrated']);
$integrado_padre  = $relations['integrated_parent'] ?? null;
$posgrados        = $relations['related_programs'] ?? [];
$docentes_posts   = $instance['docentes_posts'] ?? [];
$componentes_posts = $relations['integrated_components'] ?? [];

$abierto_publico    = !empty($registration['is_open']);
$preinscripcion_url = (string) ($registration['url'] ?? '');

$valor_uyu              = $instance['valor_uyu'] ?? null;
$valor_usd              = $instance['valor_usd'] ?? null;
$valor_uyu_15_descuento = $instance['valor_uyu_15_descuento'] ?? null;
$valor_usd_15_descuento = $instance['valor_usd_15_descuento'] ?? null;
$has_inversion          = !empty($instance['has_inversion']);
$has_valor_general      = !empty($instance['has_valor_general']);
$has_valor_descuento    = !empty($instance['has_valor_descuento']);

$encuentros = $instance['encuentros'] ?? [];
$objetivos  = $academic['objetivos_especificos'] ?? [];
$unidades   = $academic['unidades_academicas'] ?? [];

// Construcción de datos rápidos para el Hero
$hero_quick_items = [];

if (!empty($instance['fecha_inicio'])) {
    $hero_quick_items[] = [
        'icon'  => 'bi-calendar3',
        'label' => __('Comienza', 'flacso-uruguay'),
        'value' => flacso_theme_format_date_es($instance['fecha_inicio'], false),
    ];
}

if (!empty($academic['carga_horaria'])) {
    $hero_quick_items[] = [
        'icon'  => 'bi-clock',
        'label' => __('Carga horaria', 'flacso-uruguay'),
        'value' => $academic['carga_horaria'] . ' horas',
    ];
}

if (!empty($instance['modalidad'])) {
    $hero_quick_items[] = [
        'icon'  => 'bi-camera-video',
        'label' => __('Modalidad', 'flacso-uruguay'),
        'value' => wp_strip_all_tags($instance['modalidad']),
    ];
}
?>

<<?php echo flacso_child_main_tag(); ?> id="main" class="site-main flacso-seminario-page">
    <div class="flacso-seminario-shell">
        <nav class="seminario-breadcrumb-wrap" aria-label="<?php esc_attr_e('Migas de pan', 'flacso-uruguay'); ?>">
            <ol class="seminario-breadcrumb">
                <li><a href="<?php echo esc_url(home_url('/')); ?>"><?php esc_html_e('Inicio', 'flacso-uruguay'); ?></a></li>
                <li><a href="<?php echo esc_url(home_url('/formacion')); ?>"><?php esc_html_e('Formación', 'flacso-uruguay'); ?></a></li>
                <li><a href="<?php echo esc_url(home_url('/formacion/seminarios/')); ?>"><?php esc_html_e('Seminarios', 'flacso-uruguay'); ?></a></li>
                <li aria-current="page"><?php echo esc_html($title); ?></li>
            </ol>

            <?php if (!empty($posgrados)) : ?>
                <div class="seminario-offer-tags" aria-label="<?php esc_attr_e('Formaciones asociadas', 'flacso-uruguay'); ?>">
                    <?php foreach ($posgrados as $posgrado) : ?>
                        <span class="seminario-offer-tag"><?php echo esc_html($posgrado['title']); ?></span>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </nav>

        <?php if ($integrado_padre) : ?>
            <div class="seminario-parent-notice" role="status">
                <i class="bi bi-info-circle-fill" aria-hidden="true"></i>
                <div>
                    <strong><?php esc_html_e('Este seminario forma parte de una propuesta integrada.', 'flacso-uruguay'); ?></strong>
                    <?php esc_html_e('La inscripción y el arancel corresponden a', 'flacso-uruguay'); ?>
                    <a href="<?php echo esc_url($integrado_padre['url']); ?>"><?php echo esc_html($integrado_padre['title']); ?></a>.
                </div>
            </div>
        <?php endif; ?>

        <section class="seminario-hero" aria-labelledby="seminario-title">
            <div class="seminario-hero-media-wrap">
                <div class="seminario-hero-media">
                    <?php if (has_post_thumbnail($post_id)) : ?>
                        <?php
                        echo wp_get_attachment_image(
                            get_post_thumbnail_id($post_id),
                            'large',
                            false,
                            [
                                'class'         => 'seminario-featured-image',
                                'loading'       => 'eager',
                                'fetchpriority' => 'high',
                                'sizes'         => '(max-width: 767px) calc(100vw - 24px), (max-width: 1200px) 38vw, 470px',
                                'alt'           => esc_attr($title),
                            ]
                        );
                        ?>
                    <?php else : ?>
                        <div class="seminario-hero-placeholder" aria-hidden="true">
                            <i class="bi bi-image"></i>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="seminario-hero-content">
                <p class="seminario-kicker"><?php echo $is_integrated ? esc_html__('Seminario integrado', 'flacso-uruguay') : esc_html__('Seminario', 'flacso-uruguay'); ?></p>
                <?php
                $title_length = mb_strlen($title);
                $title_class  = $title_length > 110 ? ' seminario-title--xlong' : ($title_length > 60 ? ' seminario-title--long' : '');
                ?>
                <h1 id="seminario-title" class="seminario-title<?php echo esc_attr($title_class); ?>"><?php echo esc_html($title); ?></h1>

                <?php if (!empty($hero_quick_items)) : ?>
                    <div class="seminario-quick-meta" aria-label="<?php esc_attr_e('Datos principales', 'flacso-uruguay'); ?>">
                        <?php foreach ($hero_quick_items as $item) : ?>
                            <div class="seminario-quick-item">
                                <div class="seminario-quick-label">
                                    <i class="bi <?php echo esc_attr($item['icon']); ?>" aria-hidden="true"></i>
                                    <?php echo esc_html($item['label']); ?>
                                </div>
                                <div class="seminario-quick-value"><?php echo esc_html($item['value']); ?></div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <?php if ($abierto_publico && !empty($preinscripcion_url)) : ?>
                    <div class="seminario-hero-actions">
                        <a
                            href="<?php echo esc_url($preinscripcion_url); ?>"
                            data-flacso-preinscription-cta="1"
                            class="seminario-btn-primary"
                        >
                            <?php echo esc_html($integrado_padre ? __('Inscribirse al seminario integrado', 'flacso-uruguay') : __('Inscribirse ahora', 'flacso-uruguay')); ?>
                            <i class="bi bi-arrow-right" aria-hidden="true"></i>
                        </a>
                        <a href="#informacion" class="seminario-btn-secondary"><?php esc_html_e('Ver información', 'flacso-uruguay'); ?></a>
                    </div>
                <?php elseif (!$abierto_publico) : ?>
                    <div class="seminario-closed-note">
                        <i class="bi bi-info-circle-fill" aria-hidden="true"></i>
                        <span><?php esc_html_e('Las inscripciones para la edición actual de este seminario se encuentran cerradas.', 'flacso-uruguay'); ?></span>
                    </div>
                <?php endif; ?>
            </div>
        </section>

        <?php if (!empty($academic['presentacion'])) : ?>
            <section class="seminario-section seminario-section--presentation" aria-labelledby="seminario-presentacion-title">
                <div class="seminario-section-header">
                    <h2 id="seminario-presentacion-title" class="seminario-section-title"><?php esc_html_e('Presentación del seminario', 'flacso-uruguay'); ?></h2>
                </div>
                <div class="seminario-prose seminario-prose--lead"><?php echo wp_kses_post($academic['presentacion']); ?></div>
            </section>
        <?php endif; ?>

        <section id="informacion" class="seminario-section seminario-section--information" aria-labelledby="seminario-info-title">
            <div class="seminario-section-header">
                <h2 id="seminario-info-title" class="seminario-section-title"><?php esc_html_e('Información del seminario', 'flacso-uruguay'); ?></h2>
            </div>

            <div class="seminario-info-grid">
                <?php if (!empty($instance['fecha_inicio']) || !empty($instance['fecha_fin'])) : ?>
                    <article class="seminario-info-card">
                        <div class="seminario-info-card-head">
                            <div class="seminario-info-icon"><i class="bi bi-calendar3" aria-hidden="true"></i></div>
                            <h3><?php esc_html_e('Período', 'flacso-uruguay'); ?></h3>
                        </div>
                        <div class="seminario-detail-list">
                            <?php if (!empty($instance['fecha_inicio'])) : ?>
                                <div class="seminario-detail-row">
                                    <span class="seminario-detail-label"><?php esc_html_e('Inicio', 'flacso-uruguay'); ?></span>
                                    <span class="seminario-detail-value"><?php echo esc_html(flacso_theme_format_date_es($instance['fecha_inicio'])); ?></span>
                                </div>
                            <?php endif; ?>
                            <?php if (!empty($instance['fecha_fin'])) : ?>
                                <div class="seminario-detail-row">
                                    <span class="seminario-detail-label"><?php esc_html_e('Fin', 'flacso-uruguay'); ?></span>
                                    <span class="seminario-detail-value"><?php echo esc_html(flacso_theme_format_date_es($instance['fecha_fin'])); ?></span>
                                </div>
                            <?php endif; ?>
                        </div>
                    </article>
                <?php endif; ?>

                <?php if (!empty($academic['carga_horaria']) || !empty($academic['creditos'])) : ?>
                    <article class="seminario-info-card">
                        <div class="seminario-info-card-head">
                            <div class="seminario-info-icon"><i class="bi bi-clock" aria-hidden="true"></i></div>
                            <h3><?php esc_html_e('Duración y créditos', 'flacso-uruguay'); ?></h3>
                        </div>
                        <div class="seminario-detail-list">
                            <?php if (!empty($academic['carga_horaria'])) : ?>
                                <div class="seminario-detail-row">
                                    <span class="seminario-detail-label"><?php esc_html_e('Carga horaria', 'flacso-uruguay'); ?></span>
                                    <span class="seminario-detail-value"><?php echo esc_html($academic['carga_horaria']); ?> <?php esc_html_e('horas', 'flacso-uruguay'); ?></span>
                                </div>
                            <?php endif; ?>
                            <?php if (!empty($academic['creditos'])) : ?>
                                <div class="seminario-detail-row">
                                    <span class="seminario-detail-label"><?php esc_html_e('Créditos', 'flacso-uruguay'); ?></span>
                                    <span class="seminario-detail-value"><?php echo esc_html($academic['creditos']); ?></span>
                                </div>
                            <?php endif; ?>
                        </div>
                    </article>
                <?php endif; ?>

                <?php if (!empty($instance['modalidad']) || !empty($instance['es_asincronico'])) : ?>
                    <article class="seminario-info-card">
                        <div class="seminario-info-card-head">
                            <div class="seminario-info-icon"><i class="bi bi-camera-video" aria-hidden="true"></i></div>
                            <h3><?php esc_html_e('Modalidad', 'flacso-uruguay'); ?></h3>
                        </div>
                        <?php if (!empty($instance['modalidad'])) : ?>
                            <div class="seminario-prose"><?php echo wp_kses_post($instance['modalidad']); ?></div>
                        <?php endif; ?>
                        <?php if (!empty($instance['es_asincronico'])) : ?>
                            <div class="seminario-async-note"><?php esc_html_e('Cursada totalmente asincrónica: no requiere conexiones en vivo en horarios fijos.', 'flacso-uruguay'); ?></div>
                        <?php endif; ?>
                    </article>
                <?php endif; ?>

                <?php if (!empty($academic['acredita_maestria']) || !empty($academic['acredita_doctorado'])) : ?>
                    <article class="seminario-info-card">
                        <div class="seminario-info-card-head">
                            <div class="seminario-info-icon"><i class="bi bi-mortarboard" aria-hidden="true"></i></div>
                            <h3><?php esc_html_e('Acreditación', 'flacso-uruguay'); ?></h3>
                        </div>
                        <?php
                        $acreditaciones = [];
                        if (!empty($academic['acredita_maestria'])) {
                            $acreditaciones[] = __('Maestría', 'flacso-uruguay');
                        }
                        if (!empty($academic['acredita_doctorado'])) {
                            $acreditaciones[] = __('Doctorado', 'flacso-uruguay');
                        }
                        ?>
                        <p class="seminario-detail-value"><?php printf(esc_html__('Acreditable a estudios de %s.', 'flacso-uruguay'), esc_html(implode(' y ', $acreditaciones))); ?></p>
                    </article>
                <?php endif; ?>

                <?php if (!$is_integrated && !empty($academic['forma_aprobacion'])) : ?>
                    <article class="seminario-info-card">
                        <div class="seminario-info-card-head">
                            <div class="seminario-info-icon"><i class="bi bi-clipboard-check" aria-hidden="true"></i></div>
                            <h3><?php esc_html_e('Evaluación', 'flacso-uruguay'); ?></h3>
                        </div>
                        <div class="seminario-prose"><?php echo wp_kses_post($academic['forma_aprobacion']); ?></div>
                    </article>
                <?php endif; ?>

                <?php if (!empty($encuentros) || !empty($instance['es_asincronico'])) : ?>
                    <article class="seminario-info-card">
                        <div class="seminario-info-card-head">
                            <div class="seminario-info-icon"><i class="bi bi-broadcast" aria-hidden="true"></i></div>
                            <h3><?php esc_html_e('Encuentros sincrónicos', 'flacso-uruguay'); ?></h3>
                        </div>

                        <?php if (!empty($instance['es_asincronico'])) : ?>
                            <div class="seminario-async-note"><?php esc_html_e('Este seminario se dicta de forma totalmente asincrónica.', 'flacso-uruguay'); ?></div>
                        <?php endif; ?>

                        <?php if (!empty($encuentros)) : ?>
                            <div class="seminario-meetings">
                                <?php foreach ($encuentros as $encuentro) :
                                    $fecha = $encuentro['fecha'] ?? '';
                                    $hora_inicio = $encuentro['hora_inicio'] ?? '';
                                    $hora_fin = $encuentro['hora_fin'] ?? '';
                                    $plataforma = $encuentro['plataforma'] ?? 'zoom';
                                    $plataforma_otro = $encuentro['plataforma_otro'] ?? '';
                                    $plataforma_display = $plataforma === 'otro' ? $plataforma_otro : ucfirst($plataforma);
                                    $horario = trim($hora_inicio . (!empty($hora_fin) ? ' – ' . $hora_fin : ''));
                                ?>
                                    <div class="seminario-meeting">
                                        <span class="seminario-meeting-date"><?php echo esc_html(flacso_theme_format_date_es($fecha, false)); ?></span>
                                        <?php if ($horario) : ?><span class="seminario-meeting-time"><?php echo esc_html($horario); ?></span><?php endif; ?>
                                        <?php if ($plataforma_display) : ?><span class="seminario-meeting-platform"><?php echo esc_html($plataforma_display); ?></span><?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </article>
                <?php endif; ?>
            </div>
        </section>

        <?php if ($abierto_publico && $has_inversion) : ?>
            <section class="seminario-section seminario-section--investment" aria-labelledby="seminario-inversion-title">
                <div class="seminario-section-header">
                    <h2 id="seminario-inversion-title" class="seminario-section-title"><?php esc_html_e('Inversión', 'flacso-uruguay'); ?></h2>
                </div>

                <div class="seminario-investment-grid<?php echo ($has_valor_general xor $has_valor_descuento) ? ' has-one-card' : ''; ?>">
                    <?php if ($has_valor_general) : ?>
                        <article class="seminario-investment-card" aria-label="<?php esc_attr_e('Valor general del seminario', 'flacso-uruguay'); ?>">
                            <div class="seminario-investment-head">
                                <h3 class="seminario-investment-title"><?php esc_html_e('Valor general', 'flacso-uruguay'); ?></h3>
                            </div>
                            <div class="seminario-amounts<?php echo (($valor_uyu !== null) xor ($valor_usd !== null)) ? ' has-one-amount' : ''; ?>">
                                <?php if ($valor_uyu !== null) : ?>
                                    <div class="seminario-amount">
                                        <span class="seminario-amount-label">UYU</span>
                                        <span class="seminario-amount-value">$<?php echo esc_html(flacso_theme_format_amount($valor_uyu)); ?></span>
                                    </div>
                                <?php endif; ?>
                                <?php if ($valor_usd !== null) : ?>
                                    <div class="seminario-amount">
                                        <span class="seminario-amount-label">USD</span>
                                        <span class="seminario-amount-value">U$S <?php echo esc_html(flacso_theme_format_amount($valor_usd)); ?></span>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </article>
                    <?php endif; ?>

                    <?php if ($has_valor_descuento) : ?>
                        <article class="seminario-investment-card is-discount" aria-label="<?php esc_attr_e('Valor para la comunidad FLACSO', 'flacso-uruguay'); ?>">
                            <div class="seminario-investment-head">
                                <h3 class="seminario-investment-title"><?php esc_html_e('Comunidad FLACSO', 'flacso-uruguay'); ?></h3>
                                <span class="seminario-discount-badge">15% OFF</span>
                            </div>
                            <div class="seminario-amounts<?php echo (($valor_uyu_15_descuento !== null) xor ($valor_usd_15_descuento !== null)) ? ' has-one-amount' : ''; ?>">
                                <?php if ($valor_uyu_15_descuento !== null) : ?>
                                    <div class="seminario-amount">
                                        <span class="seminario-amount-label">UYU</span>
                                        <span class="seminario-amount-value">$<?php echo esc_html(flacso_theme_format_amount($valor_uyu_15_descuento)); ?></span>
                                    </div>
                                <?php endif; ?>
                                <?php if ($valor_usd_15_descuento !== null) : ?>
                                    <div class="seminario-amount">
                                        <span class="seminario-amount-label">USD</span>
                                        <span class="seminario-amount-value">U$S <?php echo esc_html(flacso_theme_format_amount($valor_usd_15_descuento)); ?></span>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </article>
                    <?php endif; ?>
                </div>
            </section>
        <?php endif; ?>

        <?php if (!empty($componentes_posts)) : ?>
            <section class="seminario-section" aria-labelledby="seminario-componentes-title">
                <div class="seminario-section-header">
                    <h2 id="seminario-componentes-title" class="seminario-section-title"><?php esc_html_e('Seminarios que integran la propuesta', 'flacso-uruguay'); ?></h2>
                </div>

                <div class="seminario-component-list">
                    <?php foreach ($componentes_posts as $componente) :
                        $comp_id = (int) $componente->ID;
                        $comp_context = flacso_theme_get_seminar_context($comp_id);
                        $comp_academic = $comp_context['academic'] ?? [];
                        $comp_instance = $comp_context['instance'] ?? [];
                        $comp_docentes_posts = $comp_instance['docentes_posts'] ?? [];
                    ?>
                        <article class="seminario-component">
                            <h3 class="seminario-component-title"><?php echo esc_html(get_the_title($componente)); ?></h3>

                            <div class="seminario-component-meta">
                                <?php if (!empty($comp_instance['fecha_inicio']) || !empty($comp_instance['fecha_fin'])) :
                                    $rango = [];
                                    if (!empty($comp_instance['fecha_inicio'])) {
                                        $rango[] = flacso_theme_format_date_es($comp_instance['fecha_inicio']);
                                    }
                                    if (!empty($comp_instance['fecha_fin'])) {
                                        $rango[] = flacso_theme_format_date_es($comp_instance['fecha_fin']);
                                    }
                                ?>
                                    <span><i class="bi bi-calendar3" aria-hidden="true"></i><?php echo esc_html(implode(' al ', $rango)); ?></span>
                                <?php endif; ?>

                                <?php if (!empty($comp_instance['encuentros'])) : ?>
                                    <span><i class="bi bi-camera-video" aria-hidden="true"></i><?php echo esc_html(count($comp_instance['encuentros'])); ?> <?php echo count($comp_instance['encuentros']) === 1 ? esc_html__('encuentro sincrónico', 'flacso-uruguay') : esc_html__('encuentros sincrónicos', 'flacso-uruguay'); ?></span>
                                <?php endif; ?>
                            </div>

                            <?php if (!empty($comp_instance['encuentros'])) : ?>
                                <h4><?php esc_html_e('Clases sincrónicas', 'flacso-uruguay'); ?></h4>
                                <ul class="seminario-prose">
                                    <?php foreach ($comp_instance['encuentros'] as $encuentro) :
                                        if (empty($encuentro['fecha'])) {
                                            continue;
                                        }
                                        $horario = trim(($encuentro['hora_inicio'] ?? '') . (!empty($encuentro['hora_fin']) ? ' a ' . $encuentro['hora_fin'] : ''));
                                    ?>
                                        <li><?php echo esc_html(flacso_theme_format_date_es($encuentro['fecha']) . ($horario ? ', ' . $horario : '')); ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php endif; ?>

                            <?php if (!empty($comp_academic['objetivo_general'])) : ?>
                                <h4><?php esc_html_e('Objetivo general', 'flacso-uruguay'); ?></h4>
                                <div class="seminario-prose"><?php echo wp_kses_post($comp_academic['objetivo_general']); ?></div>
                            <?php endif; ?>

                            <?php if (!empty($comp_academic['objetivos_especificos'])) : ?>
                                <h4><?php esc_html_e('Objetivos específicos', 'flacso-uruguay'); ?></h4>
                                <ul class="seminario-prose">
                                    <?php foreach ($comp_academic['objetivos_especificos'] as $objetivo) : ?>
                                        <li><?php echo wp_kses_post($objetivo); ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php endif; ?>

                            <?php if (!empty($comp_academic['forma_aprobacion'])) : ?>
                                <h4><?php esc_html_e('Evaluación y aprobación', 'flacso-uruguay'); ?></h4>
                                <div class="seminario-prose"><?php echo wp_kses_post($comp_academic['forma_aprobacion']); ?></div>
                            <?php endif; ?>

                            <?php if (!empty($comp_academic['unidades_academicas'])) : ?>
                                <h4><?php esc_html_e('Contenidos y unidades', 'flacso-uruguay'); ?></h4>
                                <?php foreach ($comp_academic['unidades_academicas'] as $unidad) : ?>
                                    <div class="seminario-unit">
                                        <?php if (!empty($unidad['titulo'])) : ?><h5><?php echo esc_html($unidad['titulo']); ?></h5><?php endif; ?>
                                        <?php if (!empty($unidad['contenido'])) : ?><div class="seminario-prose"><?php echo wp_kses_post($unidad['contenido']); ?></div><?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>

                            <?php if (!empty($comp_docentes_posts)) : ?>
                                <h4><?php esc_html_e('Equipo docente', 'flacso-uruguay'); ?></h4>
                                <div class="seminario-teachers-grid seminario-teachers-grid--component">
                                    <?php foreach ($comp_docentes_posts as $componente_docente) : ?>
                                        <?php flacso_theme_render_teacher_card($componente_docente); ?>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>

                            <div class="seminario-component-action">
                                <a href="<?php echo esc_url(get_permalink($componente)); ?>" class="seminario-btn-secondary"><?php esc_html_e('Ver ficha original', 'flacso-uruguay'); ?></a>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endif; ?>

        <?php if (!$is_integrated && !empty($academic['objetivo_general'])) : ?>
            <section class="seminario-section" aria-labelledby="seminario-objetivo-general-title">
                <div class="seminario-section-header">
                    <h2 id="seminario-objetivo-general-title" class="seminario-section-title"><?php esc_html_e('Objetivo general', 'flacso-uruguay'); ?></h2>
                </div>
                <div class="seminario-prose"><?php echo wp_kses_post($academic['objetivo_general']); ?></div>
            </section>
        <?php endif; ?>

        <?php if (!empty($objetivos)) : ?>
            <section class="seminario-section" aria-labelledby="seminario-objetivos-title">
                <div class="seminario-section-header">
                    <h2 id="seminario-objetivos-title" class="seminario-section-title"><?php esc_html_e('Objetivos específicos', 'flacso-uruguay'); ?></h2>
                </div>
                <ul class="seminario-objectives">
                    <?php foreach ($objetivos as $item) : ?>
                        <li class="seminario-objective">
                            <i class="bi bi-check-circle-fill" aria-hidden="true"></i>
                            <div><?php echo wp_kses_post($item); ?></div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </section>
        <?php endif; ?>

        <?php if (!empty($unidades)) : ?>
            <section class="seminario-section" aria-labelledby="seminario-contenidos-title">
                <div class="seminario-section-header">
                    <h2 id="seminario-contenidos-title" class="seminario-section-title"><?php esc_html_e('Contenidos del seminario', 'flacso-uruguay'); ?></h2>
                </div>
                <div class="seminario-units">
                    <?php foreach ($unidades as $row) :
                        $titulo = isset($row['titulo']) ? trim($row['titulo']) : '';
                        $contenido = isset($row['contenido']) ? trim($row['contenido']) : '';
                        if ($titulo === '' && $contenido === '') {
                            continue;
                        }
                    ?>
                        <article class="seminario-unit">
                            <?php if ($titulo !== '') : ?><h3><?php echo esc_html($titulo); ?></h3><?php endif; ?>
                            <?php if ($contenido !== '') : ?><div class="seminario-prose"><?php echo wp_kses_post($contenido); ?></div><?php endif; ?>
                        </article>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endif; ?>

        <?php if (!$is_integrated && !empty($docentes_posts)) : ?>
            <section class="seminario-section" aria-labelledby="seminario-docentes-title">
                <div class="seminario-section-header">
                    <h2 id="seminario-docentes-title" class="seminario-section-title"><?php esc_html_e('Equipo docente', 'flacso-uruguay'); ?></h2>
                </div>

                <div class="seminario-teachers-grid">
                    <?php foreach ($docentes_posts as $docente) : ?>
                        <?php flacso_theme_render_teacher_card($docente); ?>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endif; ?>

        <?php if ($abierto_publico && !empty($preinscripcion_url)) : ?>
            <section class="seminario-final-cta" aria-labelledby="seminario-cta-title">
                <div>
                    <h2 id="seminario-cta-title"><?php esc_html_e('¿Querés participar?', 'flacso-uruguay'); ?></h2>
                    <p><?php esc_html_e('Completá la preinscripción para recibir la información necesaria y continuar con el proceso de inscripción.', 'flacso-uruguay'); ?></p>
                </div>
                <a
                    href="<?php echo esc_url($preinscripcion_url); ?>"
                    data-flacso-preinscription-cta="1"
                    class="seminario-btn-primary"
                >
                    <?php echo esc_html($integrado_padre ? __('Inscribirse al seminario integrado', 'flacso-uruguay') : __('Inscribirse ahora', 'flacso-uruguay')); ?>
                    <i class="bi bi-arrow-right" aria-hidden="true"></i>
                </a>
            </section>
        <?php elseif (!$abierto_publico) : ?>
            <section class="seminario-final-cta" aria-labelledby="seminario-cta-title">
                <div>
                    <h2 id="seminario-cta-title"><?php esc_html_e('Inscripciones cerradas', 'flacso-uruguay'); ?></h2>
                    <p><?php esc_html_e('Las inscripciones para la edición actual de este seminario se encuentran cerradas.', 'flacso-uruguay'); ?></p>
                </div>
            </section>
        <?php endif; ?>
    </div>
</<?php echo flacso_child_main_tag(); ?>>

<script>
(function () {
    if (typeof window.flacsoMetaTrack !== 'function') {
        return;
    }

    try {
        window.flacsoMetaTrack('ViewContent', {
            content_name: <?php echo wp_json_encode($title); ?>,
            content_category: 'seminario',
            content_ids: ['seminario-' + <?php echo (int) $post_id; ?>],
            flacso_stage: 'pagina_seminario'
        });
    } catch (e) {
        if (window.console && typeof window.console.warn === 'function') {
            console.warn('[Seminario] Error enviando ViewContent:', e);
        }
    }
})();
</script>
