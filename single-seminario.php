<?php
if (!defined('ABSPATH')) {
    exit;
}

get_header();

if (!function_exists('flacso_seminario_format_fecha_es')) {
    /**
     * Convierte una fecha YYYY-MM-DD al formato español.
     */
    function flacso_seminario_format_fecha_es($fecha, $incluir_ano = true) {
        if (empty($fecha)) {
            return '';
        }

        $timestamp = strtotime($fecha);
        if ($timestamp === false) {
            return (string) $fecha;
        }

        $dias = array('Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado');
        $meses = array('', 'enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre');

        $texto = $dias[(int) date('w', $timestamp)] . ' ' . date('j', $timestamp) . ' de ' . $meses[(int) date('n', $timestamp)];

        if ($incluir_ano) {
            $texto .= ' de ' . date('Y', $timestamp);
        }

        return $texto;
    }
}

if (!function_exists('flacso_seminario_format_monto')) {
    function flacso_seminario_format_monto($monto) {
        if ($monto === null || $monto === '') {
            return '';
        }

        $monto = (float) $monto;
        $decimales = ($monto === floor($monto)) ? 0 : 2;
        return number_format($monto, $decimales, ',', '.');
    }
}

if (!function_exists('flacso_seminario_render_docente')) {
    /**
     * Renderiza una ficha docente tanto en seminarios simples como en componentes.
     */
    function flacso_seminario_render_docente($docente) {
        $doc_id = $docente->ID;
        $nombre = (string) get_post_meta($doc_id, 'nombre', true);
        $apellido = (string) get_post_meta($doc_id, 'apellido', true);
        $nombre_completo = ($nombre && $apellido) ? trim($nombre . ' ' . $apellido) : $docente->post_title;
        $prefijo_full = (string) get_post_meta($doc_id, 'prefijo_full', true);
        $cv = (string) get_post_meta($doc_id, 'cv', true);
        $cv_full = '';

        if ($cv) {
            $allowed = array(
                'p' => array(), 'br' => array(),
                'ul' => array(), 'ol' => array(), 'li' => array(),
                'strong' => array(), 'em' => array(), 'b' => array(), 'i' => array(),
                'h3' => array(), 'h4' => array(), 'h5' => array(),
                'a' => array('href' => array(), 'target' => array(), 'rel' => array()),
            );
            $cv_full = wp_kses(wpautop(trim($cv)), $allowed);
        }

        $iniciales = 'DC';
        if ($nombre && $apellido) {
            $iniciales = mb_substr($nombre, 0, 1) . mb_substr($apellido, 0, 1);
        } elseif ($nombre) {
            $iniciales = mb_substr($nombre, 0, 2);
        }
        ?>
        <article class="seminario-teacher">
            <div class="seminario-teacher-avatar">
                <?php if (has_post_thumbnail($doc_id)) : ?>
                    <?php
                    echo wp_get_attachment_image(
                        get_post_thumbnail_id($doc_id),
                        'medium',
                        false,
                        array(
                            'alt'     => esc_attr($nombre_completo),
                            'loading' => 'lazy',
                            'sizes'   => '82px',
                        )
                    );
                    ?>
                <?php else : ?>
                    <div class="seminario-teacher-placeholder"><?php echo esc_html(strtoupper($iniciales)); ?></div>
                <?php endif; ?>
            </div>
            <div>
                <h3 class="seminario-teacher-name"><?php echo esc_html($nombre_completo); ?></h3>
                <?php if ($prefijo_full) : ?><p class="seminario-teacher-prefix"><?php echo esc_html($prefijo_full); ?></p><?php endif; ?>
                <?php if ($cv_full) : ?><div class="seminario-teacher-bio"><?php echo $cv_full; ?></div><?php endif; ?>
            </div>
        </article>
        <?php
    }
}

while (have_posts()) :
    the_post();

    $post_id = get_the_ID();
    $raw_meta = class_exists('Seminario_Meta') ? Seminario_Meta::get_meta($post_id) : array();
    $meta = class_exists('Seminario_Helpers') ? Seminario_Helpers::get_display_meta($post_id, $raw_meta) : $raw_meta;

    $es_integrado = !empty($raw_meta['es_integrado']);
    $integrado_padre = (!$es_integrado && class_exists('Seminario_Helpers'))
        ? Seminario_Helpers::get_integrated_parent($post_id)
        : null;

    $abierto_publico = !metadata_exists('post', $post_id, '_seminario_abierto_publico') || !empty($meta['abierto_publico']);

    if ($integrado_padre && class_exists('Seminario_Meta')) {
        $parent_meta = Seminario_Meta::get_meta($integrado_padre->ID);
        $abierto_publico = !metadata_exists('post', $integrado_padre->ID, '_seminario_abierto_publico') || !empty($parent_meta['abierto_publico']);
    }

    $posgrados = class_exists('Seminario_Helpers')
        ? Seminario_Helpers::get_related_offers($post_id)
        : array();

    $objetivos = (!$es_integrado && !empty($meta['objetivos_especificos']) && is_array($meta['objetivos_especificos']))
        ? $meta['objetivos_especificos']
        : array();

    $unidades = (!$es_integrado && !empty($meta['unidades_academicas']) && is_array($meta['unidades_academicas']))
        ? $meta['unidades_academicas']
        : array();

    $componentes_posts = ($es_integrado && class_exists('Seminario_Helpers'))
        ? Seminario_Helpers::get_component_posts($post_id)
        : array();

    $docentes_ids = (!empty($meta['docentes']) && is_array($meta['docentes'])) ? $meta['docentes'] : array();
    $docentes_posts = array();

    if (!$es_integrado && !empty($docentes_ids)) {
        $docentes_posts = get_posts(array(
            'post_type'      => 'docente',
            'post__in'       => $docentes_ids,
            'posts_per_page' => -1,
            'orderby'        => 'post__in',
        ));
    }

    $seminario_slug = get_post_field('post_name', $post_id);
    $default_url = home_url('/formacion/seminarios/' . $seminario_slug . '/preinscripcion/');
    $preinscripcion_url = apply_filters('flacso_seminario_preinscripcion_url', $default_url, $post_id);
    if (function_exists('flacso_get_open_preinscription_instance') && flacso_get_open_preinscription_instance($post_id) > 0) {
        $abierto_publico = function_exists('flacso_offer_accepts_preinscriptions')
            ? flacso_offer_accepts_preinscriptions($post_id)
            : $abierto_publico;
    }

    $valor_uyu = isset($meta['valor_uyu']) && $meta['valor_uyu'] !== '' ? (float) $meta['valor_uyu'] : null;
    $valor_uyu_15_descuento = isset($meta['valor_uyu_15_descuento']) && $meta['valor_uyu_15_descuento'] !== '' ? (float) $meta['valor_uyu_15_descuento'] : null;
    $valor_usd = isset($meta['valor_usd']) && $meta['valor_usd'] !== '' ? (float) $meta['valor_usd'] : null;
    $valor_usd_15_descuento = isset($meta['valor_usd_15_descuento']) && $meta['valor_usd_15_descuento'] !== '' ? (float) $meta['valor_usd_15_descuento'] : null;

    $has_valor_general = $valor_uyu !== null || $valor_usd !== null;
    $has_valor_descuento = $valor_uyu_15_descuento !== null || $valor_usd_15_descuento !== null;
    $has_inversion = $has_valor_general || $has_valor_descuento;

    $encuentros = !empty($meta['encuentros_sincronicos']) && is_array($meta['encuentros_sincronicos'])
        ? array_values(array_filter($meta['encuentros_sincronicos'], static function ($encuentro) {
            return !empty($encuentro['fecha']);
        }))
        : array();

    $hero_quick_items = array();

    if (!empty($meta['periodo_inicio'])) {
        $hero_quick_items[] = array(
            'icon'  => 'bi-calendar3',
            'label' => 'Comienza',
            'value' => flacso_seminario_format_fecha_es($meta['periodo_inicio'], false),
        );
    }

    if (!empty($meta['carga_horaria'])) {
        $hero_quick_items[] = array(
            'icon'  => 'bi-clock',
            'label' => 'Carga horaria',
            'value' => $meta['carga_horaria'] . ' horas',
        );
    }

    if (!empty($meta['modalidad'])) {
        $hero_quick_items[] = array(
            'icon'  => 'bi-camera-video',
            'label' => 'Modalidad',
            'value' => wp_strip_all_tags($meta['modalidad']),
        );
    }
?>

<main id="main" class="site-main flacso-seminario-page">
    <div class="flacso-seminario-shell">
        <nav class="seminario-breadcrumb-wrap" aria-label="Migas de pan">
            <ol class="seminario-breadcrumb">
                <li><a href="<?php echo esc_url(home_url('/')); ?>">Inicio</a></li>
                <li><a href="<?php echo esc_url(home_url('/formacion')); ?>">Formación</a></li>
                <li><a href="<?php echo esc_url(get_post_type_archive_link('seminario')); ?>">Seminarios</a></li>
                <li aria-current="page"><?php echo esc_html(get_the_title()); ?></li>
            </ol>

            <?php if (!empty($posgrados)) : ?>
                <div class="seminario-offer-tags" aria-label="Formaciones asociadas">
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
                    <strong>Este seminario forma parte de una propuesta integrada.</strong>
                    La inscripción y el arancel corresponden a
                    <a href="<?php echo esc_url(get_permalink($integrado_padre)); ?>"><?php echo esc_html(get_the_title($integrado_padre)); ?></a>.
                </div>
            </div>
        <?php endif; ?>

        <section class="seminario-hero" aria-labelledby="seminario-title">
            <div class="seminario-hero-media-wrap">
                <div class="seminario-hero-media">
                    <?php if (has_post_thumbnail()) : ?>
                        <?php
                        echo wp_get_attachment_image(
                            get_post_thumbnail_id($post_id),
                            'large',
                            false,
                            array(
                                'class'         => 'seminario-featured-image',
                                'loading'       => 'eager',
                                'fetchpriority' => 'high',
                                'sizes'         => '(max-width: 767px) calc(100vw - 24px), (max-width: 1200px) 38vw, 470px',
                                'alt'           => esc_attr(get_the_title()),
                            )
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
                <p class="seminario-kicker"><?php echo $es_integrado ? 'Seminario integrado' : 'Seminario'; ?></p>
                <h1 id="seminario-title" class="seminario-title"><?php echo esc_html(get_the_title()); ?></h1>

                <?php if (!empty($hero_quick_items)) : ?>
                    <div class="seminario-quick-meta" aria-label="Datos principales">
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
                        <a href="<?php echo esc_url($preinscripcion_url); ?>" data-flacso-preinscription-cta="1" class="seminario-btn-primary">
                            <?php echo esc_html($integrado_padre ? 'Inscribirse al seminario integrado' : 'Inscribirse ahora'); ?>
                            <i class="bi bi-arrow-right" aria-hidden="true"></i>
                        </a>
                        <a href="#informacion" class="seminario-btn-secondary">Ver información</a>
                    </div>
                <?php elseif (!$abierto_publico) : ?>
                    <div class="seminario-exclusive-note">
                        <i class="bi bi-info-circle-fill" aria-hidden="true"></i>
                        <span>Este seminario es exclusivo de una formación y no admite inscripción individual externa.</span>
                    </div>
                <?php endif; ?>
            </div>
        </section>

        <?php if (!empty($meta['presentacion_seminario'])) : ?>
            <section class="seminario-section seminario-section--presentation" aria-labelledby="seminario-presentacion-title">
                <div class="seminario-section-header">
                    <h2 id="seminario-presentacion-title" class="seminario-section-title">Presentación del seminario</h2>
                </div>
                <div class="seminario-prose seminario-prose--lead"><?php echo wp_kses_post($meta['presentacion_seminario']); ?></div>
            </section>
        <?php endif; ?>

        <section id="informacion" class="seminario-section seminario-section--information" aria-labelledby="seminario-info-title">
            <div class="seminario-section-header">
                <h2 id="seminario-info-title" class="seminario-section-title">Información del seminario</h2>
            </div>

            <div class="seminario-info-grid">
                <?php if (!empty($meta['periodo_inicio']) || !empty($meta['periodo_fin'])) : ?>
                    <article class="seminario-info-card">
                        <div class="seminario-info-card-head">
                            <div class="seminario-info-icon"><i class="bi bi-calendar3" aria-hidden="true"></i></div>
                            <h3>Período</h3>
                        </div>
                        <div class="seminario-detail-list">
                            <?php if (!empty($meta['periodo_inicio'])) : ?>
                                <div class="seminario-detail-row">
                                    <span class="seminario-detail-label">Inicio</span>
                                    <span class="seminario-detail-value"><?php echo esc_html(flacso_seminario_format_fecha_es($meta['periodo_inicio'])); ?></span>
                                </div>
                            <?php endif; ?>
                            <?php if (!empty($meta['periodo_fin'])) : ?>
                                <div class="seminario-detail-row">
                                    <span class="seminario-detail-label">Fin</span>
                                    <span class="seminario-detail-value"><?php echo esc_html(flacso_seminario_format_fecha_es($meta['periodo_fin'])); ?></span>
                                </div>
                            <?php endif; ?>
                        </div>
                    </article>
                <?php endif; ?>

                <?php if (!empty($meta['carga_horaria']) || !empty($meta['creditos'])) : ?>
                    <article class="seminario-info-card">
                        <div class="seminario-info-card-head">
                            <div class="seminario-info-icon"><i class="bi bi-clock" aria-hidden="true"></i></div>
                            <h3>Duración y créditos</h3>
                        </div>
                        <div class="seminario-detail-list">
                            <?php if (!empty($meta['carga_horaria'])) : ?>
                                <div class="seminario-detail-row">
                                    <span class="seminario-detail-label">Carga horaria</span>
                                    <span class="seminario-detail-value"><?php echo esc_html($meta['carga_horaria']); ?> horas</span>
                                </div>
                            <?php endif; ?>
                            <?php if (!empty($meta['creditos'])) : ?>
                                <div class="seminario-detail-row">
                                    <span class="seminario-detail-label">Créditos</span>
                                    <span class="seminario-detail-value"><?php echo esc_html($meta['creditos']); ?></span>
                                </div>
                            <?php endif; ?>
                        </div>
                    </article>
                <?php endif; ?>

                <?php if (!empty($meta['modalidad']) || !empty($meta['es_asincronico'])) : ?>
                    <article class="seminario-info-card">
                        <div class="seminario-info-card-head">
                            <div class="seminario-info-icon"><i class="bi bi-camera-video" aria-hidden="true"></i></div>
                            <h3>Modalidad</h3>
                        </div>
                        <?php if (!empty($meta['modalidad'])) : ?>
                            <div class="seminario-prose"><?php echo wp_kses_post($meta['modalidad']); ?></div>
                        <?php endif; ?>
                        <?php if (!empty($meta['es_asincronico'])) : ?>
                            <div class="seminario-async-note">Cursada totalmente asincrónica: no requiere conexiones en vivo en horarios fijos.</div>
                        <?php endif; ?>
                    </article>
                <?php endif; ?>

                <?php if (!empty($meta['acredita_maestria']) || !empty($meta['acredita_doctorado'])) : ?>
                    <article class="seminario-info-card">
                        <div class="seminario-info-card-head">
                            <div class="seminario-info-icon"><i class="bi bi-mortarboard" aria-hidden="true"></i></div>
                            <h3>Acreditación</h3>
                        </div>
                        <?php
                        $acreditaciones = array();
                        if (!empty($meta['acredita_maestria'])) {
                            $acreditaciones[] = 'Maestría';
                        }
                        if (!empty($meta['acredita_doctorado'])) {
                            $acreditaciones[] = 'Doctorado';
                        }
                        ?>
                        <p class="seminario-detail-value">Acreditable a estudios de <?php echo esc_html(implode(' y ', $acreditaciones)); ?>.</p>
                    </article>
                <?php endif; ?>

                <?php if (!$es_integrado && !empty($meta['forma_aprobacion'])) : ?>
                    <article class="seminario-info-card">
                        <div class="seminario-info-card-head">
                            <div class="seminario-info-icon"><i class="bi bi-clipboard-check" aria-hidden="true"></i></div>
                            <h3>Evaluación</h3>
                        </div>
                        <div class="seminario-prose"><?php echo wp_kses_post($meta['forma_aprobacion']); ?></div>
                    </article>
                <?php endif; ?>

                <?php if (!empty($encuentros) || !empty($meta['es_asincronico'])) : ?>
                    <article class="seminario-info-card">
                        <div class="seminario-info-card-head">
                            <div class="seminario-info-icon"><i class="bi bi-broadcast" aria-hidden="true"></i></div>
                            <h3>Encuentros sincrónicos</h3>
                        </div>

                        <?php if (!empty($meta['es_asincronico'])) : ?>
                            <div class="seminario-async-note">Este seminario se dicta de forma totalmente asincrónica.</div>
                        <?php endif; ?>

                        <?php if (!empty($encuentros)) : ?>
                            <div class="seminario-meetings">
                                <?php foreach ($encuentros as $encuentro) :
                                    $fecha = isset($encuentro['fecha']) ? $encuentro['fecha'] : '';
                                    $hora_inicio = isset($encuentro['hora_inicio']) ? $encuentro['hora_inicio'] : '';
                                    $hora_fin = isset($encuentro['hora_fin']) ? $encuentro['hora_fin'] : '';
                                    $plataforma = isset($encuentro['plataforma']) ? $encuentro['plataforma'] : 'zoom';
                                    $plataforma_otro = isset($encuentro['plataforma_otro']) ? $encuentro['plataforma_otro'] : '';
                                    $plataforma_display = $plataforma === 'otro' ? $plataforma_otro : ucfirst($plataforma);
                                    $horario = trim($hora_inicio . (!empty($hora_fin) ? ' – ' . $hora_fin : ''));
                                ?>
                                    <div class="seminario-meeting">
                                        <span class="seminario-meeting-date"><?php echo esc_html(flacso_seminario_format_fecha_es($fecha, false)); ?></span>
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
                    <h2 id="seminario-inversion-title" class="seminario-section-title">Inversión</h2>
                </div>

                <div class="seminario-investment-grid<?php echo ($has_valor_general xor $has_valor_descuento) ? ' has-one-card' : ''; ?>">
                    <?php if ($has_valor_general) : ?>
                        <article class="seminario-investment-card" aria-label="Valor general del seminario">
                            <div class="seminario-investment-head">
                                <h3 class="seminario-investment-title">Valor general</h3>
                            </div>
                            <div class="seminario-amounts<?php echo (($valor_uyu !== null) xor ($valor_usd !== null)) ? ' has-one-amount' : ''; ?>">
                                <?php if ($valor_uyu !== null) : ?>
                                    <div class="seminario-amount">
                                        <span class="seminario-amount-label">UYU</span>
                                        <span class="seminario-amount-value">$<?php echo esc_html(flacso_seminario_format_monto($valor_uyu)); ?></span>
                                    </div>
                                <?php endif; ?>
                                <?php if ($valor_usd !== null) : ?>
                                    <div class="seminario-amount">
                                        <span class="seminario-amount-label">USD</span>
                                        <span class="seminario-amount-value">U$S <?php echo esc_html(flacso_seminario_format_monto($valor_usd)); ?></span>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </article>
                    <?php endif; ?>

                    <?php if ($has_valor_descuento) : ?>
                        <article class="seminario-investment-card is-discount" aria-label="Valor para la comunidad FLACSO">
                            <div class="seminario-investment-head">
                                <h3 class="seminario-investment-title">Comunidad FLACSO</h3>
                                <span class="seminario-discount-badge">15% OFF</span>
                            </div>
                            <div class="seminario-amounts<?php echo (($valor_uyu_15_descuento !== null) xor ($valor_usd_15_descuento !== null)) ? ' has-one-amount' : ''; ?>">
                                <?php if ($valor_uyu_15_descuento !== null) : ?>
                                    <div class="seminario-amount">
                                        <span class="seminario-amount-label">UYU</span>
                                        <span class="seminario-amount-value">$<?php echo esc_html(flacso_seminario_format_monto($valor_uyu_15_descuento)); ?></span>
                                    </div>
                                <?php endif; ?>
                                <?php if ($valor_usd_15_descuento !== null) : ?>
                                    <div class="seminario-amount">
                                        <span class="seminario-amount-label">USD</span>
                                        <span class="seminario-amount-value">U$S <?php echo esc_html(flacso_seminario_format_monto($valor_usd_15_descuento)); ?></span>
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
                    <h2 id="seminario-componentes-title" class="seminario-section-title">Seminarios que integran la propuesta</h2>
                </div>

                <div class="seminario-component-list">
                    <?php foreach ($componentes_posts as $componente) :
                        $componente_meta = class_exists('Seminario_Meta') ? Seminario_Meta::get_meta($componente->ID) : array();
                        $componente_objetivos = !empty($componente_meta['objetivos_especificos']) && is_array($componente_meta['objetivos_especificos']) ? $componente_meta['objetivos_especificos'] : array();
                        $componente_unidades = !empty($componente_meta['unidades_academicas']) && is_array($componente_meta['unidades_academicas']) ? $componente_meta['unidades_academicas'] : array();
                        $componente_encuentros = !empty($componente_meta['encuentros_sincronicos']) && is_array($componente_meta['encuentros_sincronicos']) ? $componente_meta['encuentros_sincronicos'] : array();
                        $componente_docentes_ids = !empty($componente_meta['docentes']) && is_array($componente_meta['docentes']) ? $componente_meta['docentes'] : array();
                        $componente_docentes = !empty($componente_docentes_ids)
                            ? get_posts(array(
                                'post_type'      => 'docente',
                                'post__in'       => $componente_docentes_ids,
                                'posts_per_page' => -1,
                                'orderby'        => 'post__in',
                            ))
                            : array();
                    ?>
                        <article class="seminario-component">
                            <h3 class="seminario-component-title"><?php echo esc_html(get_the_title($componente)); ?></h3>

                            <div class="seminario-component-meta">
                                <?php if (!empty($componente_meta['periodo_inicio']) || !empty($componente_meta['periodo_fin'])) :
                                    $rango = array();
                                    if (!empty($componente_meta['periodo_inicio'])) {
                                        $rango[] = flacso_seminario_format_fecha_es($componente_meta['periodo_inicio']);
                                    }
                                    if (!empty($componente_meta['periodo_fin'])) {
                                        $rango[] = flacso_seminario_format_fecha_es($componente_meta['periodo_fin']);
                                    }
                                ?>
                                    <span><i class="bi bi-calendar3" aria-hidden="true"></i><?php echo esc_html(implode(' al ', $rango)); ?></span>
                                <?php endif; ?>

                                <?php if (!empty($componente_encuentros)) : ?>
                                    <span><i class="bi bi-camera-video" aria-hidden="true"></i><?php echo esc_html(count($componente_encuentros)); ?> encuentro<?php echo count($componente_encuentros) === 1 ? '' : 's'; ?> sincrónico<?php echo count($componente_encuentros) === 1 ? '' : 's'; ?></span>
                                <?php endif; ?>
                            </div>

                            <?php if (!empty($componente_encuentros)) : ?>
                                <h4>Clases sincrónicas</h4>
                                <ul class="seminario-prose">
                                    <?php foreach ($componente_encuentros as $encuentro) :
                                        if (empty($encuentro['fecha'])) {
                                            continue;
                                        }
                                        $horario = trim(($encuentro['hora_inicio'] ?? '') . (!empty($encuentro['hora_fin']) ? ' a ' . $encuentro['hora_fin'] : ''));
                                    ?>
                                        <li><?php echo esc_html(flacso_seminario_format_fecha_es($encuentro['fecha']) . ($horario ? ', ' . $horario : '')); ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php endif; ?>

                            <?php if (!empty($componente_meta['objetivo_general'])) : ?>
                                <h4>Objetivo general</h4>
                                <div class="seminario-prose"><?php echo wp_kses_post($componente_meta['objetivo_general']); ?></div>
                            <?php endif; ?>

                            <?php if (!empty($componente_objetivos)) : ?>
                                <h4>Objetivos específicos</h4>
                                <ul class="seminario-prose">
                                    <?php foreach ($componente_objetivos as $objetivo) : ?>
                                        <li><?php echo wp_kses_post($objetivo); ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php endif; ?>

                            <?php if (!empty($componente_meta['forma_aprobacion'])) : ?>
                                <h4>Evaluación y aprobación</h4>
                                <div class="seminario-prose"><?php echo wp_kses_post($componente_meta['forma_aprobacion']); ?></div>
                            <?php endif; ?>

                            <?php if (!empty($componente_unidades)) : ?>
                                <h4>Contenidos y unidades</h4>
                                <?php foreach ($componente_unidades as $unidad) : ?>
                                    <div class="seminario-unit">
                                        <?php if (!empty($unidad['titulo'])) : ?><h5><?php echo esc_html($unidad['titulo']); ?></h5><?php endif; ?>
                                        <?php if (!empty($unidad['contenido'])) : ?><div class="seminario-prose"><?php echo wp_kses_post($unidad['contenido']); ?></div><?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>

                            <?php if (!empty($componente_docentes)) : ?>
                                <h4>Equipo docente</h4>
                                <div class="seminario-teachers-grid seminario-teachers-grid--component">
                                    <?php foreach ($componente_docentes as $componente_docente) : ?>
                                        <?php flacso_seminario_render_docente($componente_docente); ?>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>

                            <div class="seminario-component-action">
                                <a href="<?php echo esc_url(get_permalink($componente)); ?>" class="seminario-btn-secondary">Ver ficha original</a>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endif; ?>

        <?php if (!$es_integrado && !empty($meta['objetivo_general'])) : ?>
            <section class="seminario-section" aria-labelledby="seminario-objetivo-general-title">
                <div class="seminario-section-header">
                    <h2 id="seminario-objetivo-general-title" class="seminario-section-title">Objetivo general</h2>
                </div>
                <div class="seminario-prose"><?php echo wp_kses_post($meta['objetivo_general']); ?></div>
            </section>
        <?php endif; ?>

        <?php if (!empty($objetivos)) : ?>
            <section class="seminario-section" aria-labelledby="seminario-objetivos-title">
                <div class="seminario-section-header">
                    <h2 id="seminario-objetivos-title" class="seminario-section-title">Objetivos específicos</h2>
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
                    <h2 id="seminario-contenidos-title" class="seminario-section-title">Contenidos del seminario</h2>
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

        <?php if (!$es_integrado && !empty($docentes_posts)) : ?>
            <section class="seminario-section" aria-labelledby="seminario-docentes-title">
                <div class="seminario-section-header">
                    <h2 id="seminario-docentes-title" class="seminario-section-title">Equipo docente</h2>
                </div>

                <div class="seminario-teachers-grid">
                    <?php foreach ($docentes_posts as $docente) : ?>
                        <?php flacso_seminario_render_docente($docente); ?>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endif; ?>

        <?php if ($abierto_publico && !empty($preinscripcion_url)) : ?>
            <section class="seminario-final-cta" aria-labelledby="seminario-cta-title">
                <div>
                    <h2 id="seminario-cta-title">¿Querés participar?</h2>
                    <p>Completá la preinscripción para recibir la información necesaria y continuar con el proceso de inscripción.</p>
                </div>
                <a href="<?php echo esc_url($preinscripcion_url); ?>" data-flacso-preinscription-cta="1" class="seminario-btn-primary">
                    <?php echo esc_html($integrado_padre ? 'Inscribirse al seminario integrado' : 'Inscribirse ahora'); ?>
                    <i class="bi bi-arrow-right" aria-hidden="true"></i>
                </a>
            </section>
        <?php elseif (!$abierto_publico) : ?>
            <section class="seminario-final-cta" aria-labelledby="seminario-cta-title">
                <div>
                    <h2 id="seminario-cta-title">Seminario exclusivo</h2>
                    <p>La cursada está disponible únicamente para quienes integran la formación académica correspondiente.</p>
                </div>
            </section>
        <?php endif; ?>
    </div>
</main>

<script>
(function () {
    if (typeof window.flacsoMetaTrack !== 'function') {
        return;
    }

    try {
        window.flacsoMetaTrack('ViewContent', {
            content_name: <?php echo wp_json_encode(get_the_title($post_id)); ?>,
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

<?php
endwhile;

get_footer();
