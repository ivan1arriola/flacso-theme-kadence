<?php
if (!defined('ABSPATH')) {
    exit;
}

get_header();

/**
 * Convierte una fecha YYYY-MM-DD al formato español: "Lunes 20 de Enero 2026"
 */
function format_fecha_es($fecha) {
    if (empty($fecha)) return '';
    $timestamp = strtotime($fecha);
    if ($timestamp === false) return esc_html($fecha);

    $dias = array('Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado');
    $meses = array('', 'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre');

    $dia_semana = $dias[date('w', $timestamp)];
    $dia = date('j', $timestamp);
    $mes = $meses[date('n', $timestamp)];
    $ano = date('Y', $timestamp);

    return "$dia_semana $dia de $mes $ano";
}

while (have_posts()) : the_post();
    $post_id = get_the_ID();
    $meta = class_exists('Seminario_Meta') ? Seminario_Meta::get_meta($post_id) : array();
    $abierto_publico = isset($meta['abierto_publico']) && $meta['abierto_publico'] === '0' ? false : true;
    $posgrados = class_exists('Seminario_Taxonomies')
        ? Seminario_Taxonomies::get_related_ofertas($post_id)
        : array();

    // Extract array-based meta
    $objetivos = isset($meta['objetivos_especificos']) && is_array($meta['objetivos_especificos']) ? $meta['objetivos_especificos'] : array();
    $unidades = isset($meta['unidades_academicas']) && is_array($meta['unidades_academicas']) ? $meta['unidades_academicas'] : array();
    $docentes_ids = isset($meta['docentes']) && is_array($meta['docentes']) ? $meta['docentes'] : array();
    $docentes_posts = array();
    if (!empty($docentes_ids)) {
        $docentes_posts = get_posts(array(
            'post_type' => 'docente',
            'post__in' => $docentes_ids,
            'posts_per_page' => -1,
            'orderby' => 'post__in',
        ));
    }

    // Preinscription URL with tracking parameters
    $seminario_slug = get_post_field('post_name', $post_id);
    $default_url = home_url('/formacion/seminarios/' . $seminario_slug . '/preinscripcion/');
    $preinscripcion_url = apply_filters('flacso_seminario_preinscripcion_url', $default_url, $post_id);

    $valor_uyu = isset($meta['valor_uyu']) && $meta['valor_uyu'] !== '' ? (float) $meta['valor_uyu'] : null;
    $valor_uyu_15_descuento = isset($meta['valor_uyu_15_descuento']) && $meta['valor_uyu_15_descuento'] !== '' ? (float) $meta['valor_uyu_15_descuento'] : null;
    $valor_usd = isset($meta['valor_usd']) && $meta['valor_usd'] !== '' ? (float) $meta['valor_usd'] : null;
    $valor_usd_15_descuento = isset($meta['valor_usd_15_descuento']) && $meta['valor_usd_15_descuento'] !== '' ? (float) $meta['valor_usd_15_descuento'] : null;

    $has_valor_general = $valor_uyu !== null || $valor_usd !== null;
    $has_valor_descuento = $valor_uyu_15_descuento !== null || $valor_usd_15_descuento !== null;
    $has_inversion = $has_valor_general || $has_valor_descuento;

    $format_monto = function ($monto) {
        if ($monto === null) {
            return '';
        }
        $decimales = ((float) $monto === floor((float) $monto)) ? 0 : 2;
        return number_format((float) $monto, $decimales, ',', '.');
    };

    $valor_uyu_text = $format_monto($valor_uyu);
    $valor_uyu_15_descuento_text = $format_monto($valor_uyu_15_descuento);
    $valor_usd_text = $format_monto($valor_usd);
    $valor_usd_15_descuento_text = $format_monto($valor_usd_15_descuento);

    // Las imágenes nuevas se cargan en formato 4:5. Detectamos la proporción
    // desde los metadatos para conservar intacto el hero horizontal histórico.
    $thumbnail_id = (int) get_post_thumbnail_id($post_id);
    $is_featured_image_4x5 = false;
    if ($thumbnail_id > 0) {
        $thumbnail_meta = wp_get_attachment_metadata($thumbnail_id);
        $thumbnail_width = isset($thumbnail_meta['width']) ? (int) $thumbnail_meta['width'] : 0;
        $thumbnail_height = isset($thumbnail_meta['height']) ? (int) $thumbnail_meta['height'] : 0;

        if ($thumbnail_width <= 0 || $thumbnail_height <= 0) {
            $thumbnail_source = wp_get_attachment_image_src($thumbnail_id, 'full');
            $thumbnail_width = isset($thumbnail_source[1]) ? (int) $thumbnail_source[1] : 0;
            $thumbnail_height = isset($thumbnail_source[2]) ? (int) $thumbnail_source[2] : 0;
        }

        if ($thumbnail_width > 0 && $thumbnail_height > 0) {
            $is_featured_image_4x5 = abs(($thumbnail_width / $thumbnail_height) - (4 / 5)) <= 0.03;
        }
    }
?>


<main id="main" class="site-main flacso-cpt-seminario-main">
    <div class="flacso-cpt-seminario">
        <div class="entry-content-wrap entry content-bg single-entry post type-post status-publish format-standard has-post-thumbnail hentry">
            <div class="entry-content">
                <div class="content-container site-container">
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb seminario-breadcrumb mb-2">
            <li class="breadcrumb-item">
                <a href="<?php echo home_url(); ?>">Inicio</a>
            </li>
            <li class="breadcrumb-item">
                <a href="<?php echo home_url('/formacion'); ?>">Oferta académica (Formación)</a>
            </li>
            <li class="breadcrumb-item">
                <a href="<?php echo esc_url( get_post_type_archive_link( 'seminario' ) ); ?>">Seminarios</a>
            </li>
            <li class="breadcrumb-item active" aria-current="page">
                <?php echo esc_html(get_the_title()); ?>
            </li>
        </ol>

        <!-- Badges: Posgrados (oferta academica asociada) -->
        <?php if (!empty($posgrados)) : ?>
            <div class="d-flex flex-wrap gap-2 mb-3">
                <?php foreach ($posgrados as $posgrado) : ?>
                    <span class="badge seminario-badge seminario-badge-secondary">
                        <?php echo esc_html($posgrado['title']); ?>
                    </span>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </nav>

    <!-- Hero Section -->
    <div class="row align-items-center mb-5<?php echo $is_featured_image_4x5 ? ' seminario-hero--portrait' : ''; ?>">
        <!-- Image Column -->
        <div class="<?php echo $is_featured_image_4x5 ? 'col-lg-5' : 'col-lg-6'; ?> mb-4 mb-lg-0">
            <?php if (has_post_thumbnail()) : ?>
                <div class="<?php echo $is_featured_image_4x5 ? 'seminario-hero-image-4x5' : 'ratio ratio-16x9'; ?> rounded-4 overflow-hidden shadow-lg">
                    <?php the_post_thumbnail('large', array('class' => 'img-fluid w-100 h-100 object-fit-cover', 'loading' => 'eager')); ?>
                </div>
            <?php else : ?>
                <div class="ratio ratio-16x9 rounded-4 overflow-hidden shadow-lg seminario-image-placeholder d-flex align-items-center justify-content-center">
                    <span class="display-4">📚</span>
                </div>
            <?php endif; ?>
        </div>

        <!-- Content Column -->
        <div class="<?php echo $is_featured_image_4x5 ? 'col-lg-7' : 'col-lg-6'; ?>">
            <h1 class="fw-bold mb-4" style="color: var(--global-palette1);">
                <?php echo esc_html(get_the_title()); ?>
            </h1>

            <!-- CTA Button -->
            <?php if ($abierto_publico && !empty($preinscripcion_url)) : ?>
                <div class="mt-4">
                    <a href="<?php echo esc_url($preinscripcion_url); ?>" class="btn btn-inscripcion btn-lg">
                        Inscríbete Ahora <i class="bi bi-arrow-right ms-2"></i>
                    </a>
                </div>
            <?php elseif (!$abierto_publico) : ?>
                <div class="mt-4 alert alert-info py-3 px-4 border-info shadow-sm" style="border-radius: 12px; max-width: 500px; background-color: #f0f9ff; color: #1d3a72;">
                    <i class="bi bi-info-circle-fill text-info me-2"></i>
                    Este seminario es exclusivo de una formación. Solo se puede acceder al inscribirse a la oferta académica correspondiente.
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Información del Seminario -->
    <section class="py-5">
        <div class="section-header">
            <h2 class="fw-bold">Información del Seminario</h2>
        </div>

        <div class="info-grid">
            <!-- Fechas -->
            <?php if (!empty($meta['periodo_inicio']) || !empty($meta['periodo_fin'])) : ?>
                <div>
                    <div class="card info-card h-100 shadow-sm">
                        <div class="card-body text-start p-4">
                            <div class="info-card-icon">📅</div>
                            <h5 class="card-title fw-bold mb-3">Período</h5>
                            <?php if (!empty($meta['periodo_inicio'])) : ?>
                                <div class="mb-3">
                                    <p class="mb-0">
                                        <span class="text-muted">Inicio:</span>
                                        <span class="fw-bold ms-2" style="color: var(--global-palette1);"><?php echo esc_html(format_fecha_es($meta['periodo_inicio'])); ?></span>
                                    </p>
                                </div>
                            <?php endif; ?>
                            <?php if (!empty($meta['periodo_fin'])) : ?>
                                <div class="mb-0">
                                    <p class="mb-0">
                                        <span class="text-muted">Fin:</span>
                                        <span class="fw-bold ms-2" style="color: var(--global-palette1);"><?php echo esc_html(format_fecha_es($meta['periodo_fin'])); ?></span>
                                    </p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Carga Horaria y Créditos -->
            <?php if (!empty($meta['carga_horaria']) || !empty($meta['creditos'])) : ?>
                <div>
                    <div class="card info-card h-100 shadow-sm">
                        <div class="card-body text-start p-4">
                            <div class="info-card-icon">⏱️</div>
                            <h5 class="card-title fw-bold mb-3">Duración y Créditos</h5>
                            <?php if (!empty($meta['carga_horaria'])) : ?>
                                <div class="mb-3">
                                    <p class="mb-0">
                                        <span class="text-muted">Duración:</span>
                                        <span class="fw-bold ms-2" style="color: var(--global-palette1);"><?php echo esc_html($meta['carga_horaria']); ?> horas</span>
                                    </p>
                                </div>
                            <?php endif; ?>
                            <?php if (!empty($meta['creditos'])) : ?>
                                <div>
                                    <p class="mb-0">
                                        <span class="text-muted">Créditos:</span>
                                        <span class="fw-bold ms-2" style="color: var(--global-palette1);"><?php echo esc_html($meta['creditos']); ?></span>
                                    </p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Modalidad -->
            <?php if (!empty($meta['modalidad']) || !empty($meta['es_asincronico'])) : ?>
                <div>
                    <div class="card info-card h-100 shadow-sm">
                        <div class="card-body text-start p-4">
                            <div class="info-card-icon">📹</div>
                            <h5 class="card-title fw-bold mb-3">Modalidad</h5>
                            <?php if (!empty($meta['modalidad'])) : ?>
                                <p class="mb-0" style="color: var(--global-palette4);"><?php echo wp_kses_post($meta['modalidad']); ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Acreditación -->
            <?php if (!empty($meta['acredita_maestria']) || !empty($meta['acredita_doctorado'])) : ?>
                <div>
                    <div class="card info-card h-100 shadow-sm">
                        <div class="card-body text-start p-4">
                            <div class="info-card-icon">🎓</div>
                            <h5 class="card-title fw-bold mb-3">Acreditación</h5>
                            <p class="mb-0" style="font-size: 0.95rem; line-height: 1.5; color: var(--global-palette4);">
                                <?php
                                $acreditacion_text = 'Acreditable a estudios de ';
                                $acreditaciones = array();

                                if (!empty($meta['acredita_maestria'])) {
                                    $acreditaciones[] = 'Maestría';
                                }
                                if (!empty($meta['acredita_doctorado'])) {
                                    $acreditaciones[] = 'Doctorado';
                                }

                                echo $acreditacion_text . implode(' y ', $acreditaciones);
                                ?>
                            </p>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Forma de Aprobación -->
            <?php if (!empty($meta['forma_aprobacion'])) : ?>
                <div>
                    <div class="card info-card h-100 shadow-sm">
                        <div class="card-body text-start p-4">
                            <div class="info-card-icon">📝</div>
                            <h5 class="card-title fw-bold mb-3">Evaluación</h5>
                            <div class="seminario-content" style="font-size: 0.95rem; line-height: 1.5; color: var(--global-palette4);">
                                <?php echo wp_kses_post($meta['forma_aprobacion']); ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Encuentros sincronicos Card -->
            <?php
            $encuentros_card = isset($meta['encuentros_sincronicos']) && is_array($meta['encuentros_sincronicos']) ? $meta['encuentros_sincronicos'] : array();
            $encuentros_card_validos = array_filter($encuentros_card, function($e) {
                return !empty($e['fecha']);
            });
            if (!empty($encuentros_card_validos) || !empty($meta['es_asincronico'])) :
            ?>
                <div>
                    <div class="card info-card h-100 shadow-sm">
                        <div class="card-body text-start p-4">
                            <div class="info-card-icon">🎥</div>
                            <h5 class="card-title fw-bold mb-3">Encuentros sincrónicos</h5>
                            <?php if (!empty($meta['es_asincronico'])) : ?>
                                <div class="alert alert-light border-info py-3 px-3 mb-0" style="font-size: 0.9rem; border-radius: 10px; background-color: #f0f9ff;">
                                    <i class="bi bi-info-circle-fill text-info me-2"></i>
                                    Este seminario se dicta de forma <strong>totalmente asincrónica</strong>. No requiere conexiones en vivo en horarios fijos.
                                </div>
                            <?php endif; ?>
                            <div style="font-size: 0.9rem; color: var(--global-palette4);">
                                <?php foreach ($encuentros_card_validos as $index => $encuentro) :
                                    $fecha = isset($encuentro['fecha']) ? $encuentro['fecha'] : '';
                                    $hora_inicio = isset($encuentro['hora_inicio']) ? $encuentro['hora_inicio'] : '';
                                    $hora_fin = isset($encuentro['hora_fin']) ? $encuentro['hora_fin'] : '';
                                    $plataforma = isset($encuentro['plataforma']) ? $encuentro['plataforma'] : 'zoom';
                                    $plataforma_otro = isset($encuentro['plataforma_otro']) ? $encuentro['plataforma_otro'] : '';

                                    $plataforma_display = $plataforma === 'otro' ? $plataforma_otro : ucfirst($plataforma);

                                    $dias_semana = array('Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado');
                                    $meses = array('', 'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre');
                                    $timestamp = strtotime($fecha);
                                    $dia_semana = $dias_semana[date('w', $timestamp)];
                                    $dia = date('j', $timestamp);
                                    $mes = $meses[date('n', $timestamp)];
                                ?>
                                    <div style="margin-bottom: 0.8rem; padding-bottom: 0.8rem; border-bottom: 1px solid var(--global-palette7);" <?php echo $index === count($encuentros_card_validos) - 1 ? 'style="margin-bottom: 0; padding-bottom: 0; border-bottom: none;"' : ''; ?>>
                                        <div style="font-weight: 700; color: var(--global-palette1); margin-bottom: 0.3rem;">
                                            <?php echo esc_html($dia_semana . ', ' . $dia . ' de ' . $mes); ?>
                                        </div>
                                        <div style="color: var(--global-palette14); font-weight: 600;">
                                            <?php
                                            if (!empty($hora_inicio)) {
                                                echo esc_html($hora_inicio);
                                            }
                                            if (!empty($hora_fin)) {
                                                echo ' - ' . esc_html($hora_fin);
                                            }
                                            ?>
                                        </div>
                                        <div style="font-size: 0.85rem; color: var(--global-palette5);">
                                            <?php echo esc_html($plataforma_display); ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <?php if ($abierto_publico && $has_inversion) : ?>
        <section class="py-5">
            <div class="section-header">
                <h2 class="fw-bold">Inversión</h2>
            </div>

            <div class="inversion-grid">
                <?php if ($has_valor_general) : ?>
                    <article class="inversion-card">
                        <div class="inversion-card-head">
                            <h3 class="inversion-card-title">Valor general</h3>
                        </div>
                        <div class="inversion-card-body">
                            <?php if ($valor_uyu !== null) : ?>
                                <div class="inversion-monto">
                                    <span class="inversion-monto-label">UYU</span>
                                    <span class="inversion-monto-value">$<?php echo esc_html($valor_uyu_text); ?></span>
                                </div>
                            <?php endif; ?>
                            <?php if ($valor_usd !== null) : ?>
                                <div class="inversion-monto">
                                    <span class="inversion-monto-label">USD</span>
                                    <span class="inversion-monto-value">U$S <?php echo esc_html($valor_usd_text); ?></span>
                                </div>
                            <?php endif; ?>
                        </div>
                    </article>
                <?php endif; ?>

                <?php if ($has_valor_descuento) : ?>
                    <article class="inversion-card inversion-card-descuento">
                        <div class="inversion-card-head">
                            <h3 class="inversion-card-title">Comunidad FLACSO</h3>
                            <span class="inversion-card-badge">15% OFF</span>
                        </div>
                        <div class="inversion-card-body">
                            <?php if ($valor_uyu_15_descuento !== null) : ?>
                                <div class="inversion-monto">
                                    <span class="inversion-monto-label">UYU</span>
                                    <span class="inversion-monto-value">$<?php echo esc_html($valor_uyu_15_descuento_text); ?></span>
                                </div>
                            <?php endif; ?>
                            <?php if ($valor_usd_15_descuento !== null) : ?>
                                <div class="inversion-monto">
                                    <span class="inversion-monto-label">USD</span>
                                    <span class="inversion-monto-value">U$S <?php echo esc_html($valor_usd_15_descuento_text); ?></span>
                                </div>
                            <?php endif; ?>
                        </div>
                    </article>
                <?php endif; ?>
            </div>
        </section>
    <?php endif; ?>

    <!-- Sección detallada de "Encuentros sincrónicos" removida a pedido:
         se conserva solo la tarjeta-resumen en la grilla de información. -->

    <!-- Presentación del Seminario -->
    <?php if (!empty($meta['presentacion_seminario'])) : ?>
        <section class="py-5">
            <div class="section-header">
                <h2 class="fw-bold">Presentación del Seminario</h2>
            </div>
            <div class="seminario-content">
                <?php echo wp_kses_post($meta['presentacion_seminario']); ?>
            </div>
        </section>
    <?php endif; ?>

    <!-- Objetivo General -->
    <?php if (!empty($meta['objetivo_general'])) : ?>
        <section class="py-5">
            <div class="section-header">
                <h2 class="fw-bold">Objetivo General</h2>
            </div>
            <div class="seminario-content">
                <?php echo wp_kses_post($meta['objetivo_general']); ?>
            </div>
        </section>
    <?php endif; ?>

    <!-- Objetivos Específicos -->
    <?php if (!empty($objetivos) && count($objetivos) >= 1) : ?>
        <section class="py-5">
            <div class="section-header">
                <h2 class="fw-bold">Objetivos Específicos</h2>
            </div>
            <ul class="seminario-content list-unstyled">
                <?php foreach ($objetivos as $item) : ?>
                    <li class="mb-3">
                        <div class="d-flex">
                            <i class="bi bi-check-circle-fill me-3 mt-1" style="color: var(--global-palette1);"></i>
                            <span><?php echo wp_kses_post($item); ?></span>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>
        </section>
    <?php endif; ?>

    <!-- Contenidos / Unidades Académicas -->
    <?php if (!empty($unidades)) : ?>
        <section class="py-5">
            <div class="section-header">
                <h2 class="fw-bold">Contenidos del Seminario</h2>
            </div>
            <div style="counter-reset: unidad-counter;">
                <?php foreach ($unidades as $row) :
                    $titulo = isset($row['titulo']) ? trim($row['titulo']) : '';
                    $contenido = isset($row['contenido']) ? trim($row['contenido']) : '';
                    if (empty($titulo) && empty($contenido)) continue;
                ?>
                    <div class="unidad-item mb-4">
                        <?php if (!empty($titulo)) : ?>
                            <h3 class="h4 fw-bold mb-3" style="color: var(--global-palette1);"><?php echo esc_html($titulo); ?></h3>
                        <?php endif; ?>
                        <?php if (!empty($contenido)) : ?>
                            <div class="seminario-content">
                                <?php echo wp_kses_post($contenido); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endif; ?>

    <!-- Docentes -->
    <?php if (!empty($docentes_posts)) : ?>
        <section class="py-5">
            <div class="section-header">
                <h2 class="fw-bold">
                    Equipo Docente<?php echo count($docentes_posts) > 1 ? 's' : ''; ?>
                </h2>
            </div>

            <?php
                $num_docentes = count($docentes_posts);
                $col_class = 'col-12';
            ?>

            <div class="row g-4">
                <?php foreach ($docentes_posts as $docente) :
                    $doc_id = $docente->ID;
                    $nombre = (string)get_post_meta($doc_id, 'nombre', true);
                    $apellido = (string)get_post_meta($doc_id, 'apellido', true);
                    $nombre_completo = '';

                    if ($nombre && $apellido) {
                        $nombre_completo = trim($nombre . ' ' . $apellido);
                    } else {
                        $nombre_completo = $docente->post_title;
                    }

                    $prefijo_full = (string)get_post_meta($doc_id, 'prefijo_full', true);
                    $cv = (string)get_post_meta($doc_id, 'cv', true);
                    $cv_full = '';

                    if ($cv) {
                        $allowed = [
                            'p' => [], 'br' => [],
                            'ul' => [], 'ol' => [], 'li' => [],
                            'strong' => [], 'em' => [], 'b' => [], 'i' => [],
                            'h3' => [], 'h4' => [], 'h5' => [],
                            'a' => ['href' => [], 'target' => [], 'rel' => []],
                        ];
                        $cv_full = wp_kses(wpautop(trim($cv)), $allowed);
                    }
                ?>
                    <div class="<?php echo $col_class; ?>">
                        <div class="card docente-card h-100 shadow-sm">
                            <div class="card-header bg-transparent border-0 p-4 pb-0">
                                <div class="d-flex align-items-center">
                                    <div class="docente-avatar me-3">
                                        <?php if (has_post_thumbnail($doc_id)) : ?>
                                            <?php echo wp_get_attachment_image(
                                                get_post_thumbnail_id($doc_id),
                                                'medium',
                                                false,
                                                ['class' => 'img-fluid w-100 h-100 rounded-circle object-fit-cover', 'alt' => esc_attr($nombre_completo), 'loading' => 'lazy']
                                            ); ?>
                                        <?php else : ?>
                                            <div class="docente-avatar-placeholder rounded-circle">
                                                <?php
                                                $inic = 'DC';
                                                if ($nombre && $apellido) {
                                                    $inic = mb_substr($nombre, 0, 1) . mb_substr($apellido, 0, 1);
                                                } elseif ($nombre) {
                                                    $inic = mb_substr($nombre, 0, 2);
                                                }
                                                echo esc_html(strtoupper($inic));
                                                ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h3 class="h5 fw-bold mb-1" style="color: var(--global-palette1);"><?php echo esc_html($nombre_completo); ?></h3>
                                        <?php if ($prefijo_full) : ?>
                                            <p class="text-muted mb-0" style="font-size: 0.9rem; color: var(--global-palette5) !important;"><?php echo esc_html($prefijo_full); ?></p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>

                            <?php if ($cv_full) : ?>
                                <div class="card-body p-4 pt-3">
                                    <div class="docente-bio-content">
                                        <?php echo $cv_full; ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endif; ?>

    <!-- Final CTA -->
    <?php if ($abierto_publico && !empty($preinscripcion_url)) : ?>
        <section class="py-5">
            <div class="final-cta text-center py-5 px-4 rounded-4 shadow-lg">
                <h2 class="fw-bold mb-3">¿Querés comenzar?</h2>
                <p class="lead mb-4 opacity-90">
                    Unite a este seminario y formá parte de nuestra comunidad académica.
                </p>
                <a href="<?php echo esc_url($preinscripcion_url); ?>" class="btn btn-inscripcion btn-lg px-5 py-3 fw-bold">
                    Inscribirse ahora <i class="bi bi-arrow-right ms-2"></i>
                </a>
            </div>
        </section>
    <?php elseif (!$abierto_publico) : ?>
        <section class="py-5">
            <div class="final-cta text-center py-5 px-4 rounded-4 shadow-lg" style="background: linear-gradient(135deg, #1d3a72 0%, #3b82f6 100%);">
                <h2 class="fw-bold mb-3 text-white">Seminario Exclusivo</h2>
                <p class="lead mb-0 opacity-90 text-white">
                    Este seminario es de cursada exclusiva y no admite inscripciones individuales externas.
                </p>
            </div>
        </section>
    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</main>

<?php
endwhile;
?>
<script>
(function() {
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

get_footer();
