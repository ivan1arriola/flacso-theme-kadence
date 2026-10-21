<?php
/**
 * Template responsive para la página individual de una Oferta Académica.
 *
 * Mantiene la estructura visual y secciones exactas originales.
 * Los precios se gestionan exclusivamente en la subpágina Carta del programa.
 *
 * @package kadence-child-flacso
 */

if (!defined('ABSPATH')) {
    exit;
}

$context = isset($args['context']) && is_array($args['context']) ? $args['context'] : null;
if (!$context) {
    $post_id = get_the_ID();
    $context = function_exists('flacso_theme_get_offer_context') ? flacso_theme_get_offer_context($post_id) : [];
}

if (empty($context)) {
    return;
}

$post_id       = (int) $context['offer_id'];
$title         = (string) $context['title'];
$tipo_oferta   = (string) $context['type_label'];
$academic      = $context['academic'] ?? [];
$instance      = $context['instance'] ?? [];
$registration  = $context['registration'] ?? [];
$program       = $context['program'] ?? [];
$thumbnail_url = (string) ($context['thumbnail_url'] ?? '');

// Color principal de la oferta académica.
// Si no hay un color válido configurado, se usa el azul institucional FLACSO.
$offer_color_fallback = '#0a3470';
$offer_color_candidates = [
    $academic['color_principal'] ?? '',
    $academic['colores_presentacion']['principal'] ?? '',
    $context['color_principal'] ?? '',
];

$offer_color = $offer_color_fallback;
foreach ($offer_color_candidates as $candidate) {
    if (!is_string($candidate)) {
        continue;
    }

    $candidate = trim($candidate);
    if (preg_match('/^#[0-9a-fA-F]{6}$/', $candidate)) {
        $offer_color = strtolower($candidate);
        break;
    }
}

$offer_r = hexdec(substr($offer_color, 1, 2));
$offer_g = hexdec(substr($offer_color, 3, 2));
$offer_b = hexdec(substr($offer_color, 5, 2));

$offer_dark = sprintf(
    '#%02x%02x%02x',
    max(0, min(255, (int) round($offer_r * 0.68))),
    max(0, min(255, (int) round($offer_g * 0.68))),
    max(0, min(255, (int) round($offer_b * 0.68)))
);

$offer_deep = sprintf(
    '#%02x%02x%02x',
    max(0, min(255, (int) round($offer_r * 0.42))),
    max(0, min(255, (int) round($offer_g * 0.42))),
    max(0, min(255, (int) round($offer_b * 0.42)))
);

$offer_light = sprintf(
    '#%02x%02x%02x',
    max(0, min(255, (int) round($offer_r + (255 - $offer_r) * 0.82))),
    max(0, min(255, (int) round($offer_g + (255 - $offer_g) * 0.82))),
    max(0, min(255, (int) round($offer_b + (255 - $offer_b) * 0.82)))
);

$offer_luminance = (0.299 * $offer_r + 0.587 * $offer_g + 0.114 * $offer_b) / 255;
$offer_contrast = $offer_luminance > 0.64 ? '#082b60' : '#ffffff';

// Contraste WCAG del botón/acento: elegir el texto que mejor contraste
// con cualquier color configurado por la Oferta (incluidos claros y amarillos).
$channel_luminance = static function (int $value): float {
    $normalized = $value / 255;
    return $normalized <= 0.04045
        ? $normalized / 12.92
        : pow(($normalized + 0.055) / 1.055, 2.4);
};
$brand_luminance = 0.2126 * $channel_luminance($offer_r)
    + 0.7152 * $channel_luminance($offer_g)
    + 0.0722 * $channel_luminance($offer_b);
$navy_luminance = 0.2126 * $channel_luminance(8)
    + 0.7152 * $channel_luminance(43)
    + 0.0722 * $channel_luminance(96);
$white_contrast_ratio = 1.05 / ($brand_luminance + 0.05);
$navy_contrast_ratio = (max($brand_luminance, $navy_luminance) + 0.05)
    / (min($brand_luminance, $navy_luminance) + 0.05);
$hero_brand_ink = $white_contrast_ratio >= $navy_contrast_ratio ? '#ffffff' : '#082b60';

$offer_style_vars = sprintf(
    '--oa-brand:%s;--oa-brand-rgb:%d,%d,%d;--oa-brand-dark:%s;--oa-brand-deep:%s;--oa-brand-light:%s;--oa-brand-contrast:%s;--oa-hero-brand-ink:%s;',
    esc_attr($offer_color),
    $offer_r,
    $offer_g,
    $offer_b,
    esc_attr($offer_dark),
    esc_attr($offer_deep),
    esc_attr($offer_light),
    esc_attr($offer_contrast),
    esc_attr($hero_brand_ink)
);

$raw_content           = get_post_field('post_content', $post_id);
$filtered_main_content = trim((string) apply_filters('the_content', $raw_content));
$descripcion_html      = !empty($academic['descripcion_html']) ? $academic['descripcion_html'] : (!empty($academic['presentacion']) ? $academic['presentacion'] : '');

$has_visible_html = static function ($html): bool {
    $html = trim((string) $html);
    if ($html === '') {
        return false;
    }

    if (preg_match('/<(img|iframe|video|audio|object|embed|svg|canvas|table)\b/i', $html)) {
        return true;
    }

    $text = html_entity_decode(wp_strip_all_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $text = str_replace("\xc2\xa0", ' ', $text);
    $text = preg_replace('/\s+/u', ' ', $text);

    return trim((string) $text) !== '';
};

$has_main_content = $has_visible_html($filtered_main_content) || $has_visible_html($descripcion_html);

$inscripciones_abiertas = !empty($registration['is_open']);
$preinscripcion_url      = (string) ($registration['url'] ?? '');
$has_preinscription_cta = $inscripciones_abiertas && $preinscripcion_url !== '';

// La última cohorte solo se anuncia si existe y acepta preinscripciones.
// Evita presentar cohortes cerradas (o inexistentes) como convocatorias vigentes.
$show_cohort_section = !empty($instance['id']) && $inscripciones_abiertas;

$inscripciones_year = !empty($instance['anio_inicio'])
    ? (string) $instance['anio_inicio']
    : (!empty($instance['fecha_inicio']) ? substr((string) $instance['fecha_inicio'], 0, 4) : wp_date('Y'));

$hero_primary_label = $has_preinscription_cta
    ? sprintf(
        /* translators: %s: academic year */
        __('Preinscripción %s', 'flacso-uruguay'),
        $inscripciones_year
    )
    : __('Solicitar información', 'flacso-uruguay');
$hero_primary_url = $has_preinscription_cta ? $preinscripcion_url : '#flacso-oa-consulta';

$duracion_meses_val = $academic['duracion_meses'] ?? '';

$fechas_hero = trim((string) ($instance['fechas_formateadas'] ?? ''));
if ($fechas_hero === '' && !empty($instance['fecha_inicio'])) {
    $fechas_hero = function_exists('flacso_theme_format_date_es')
        ? flacso_theme_format_date_es((string) $instance['fecha_inicio'])
        : (string) $instance['fecha_inicio'];
}
if ($fechas_hero !== '') {
    if (($instance['precision_fecha_inicio'] ?? '') === 'anio' && !str_starts_with(mb_strtolower($fechas_hero), 'en ')) {
        $fechas_hero = sprintf(__('en %s', 'flacso-uruguay'), $fechas_hero);
    }
    $cohorte_hero = trim((string) ($instance['label'] ?? ''));
    if ($cohorte_hero !== '') {
        $fechas_hero .= ' · ' . $cohorte_hero;
    }
} else {
    $fechas_hero = __('A confirmar', 'flacso-uruguay');
}
$duracion_text = $duracion_meses_val !== '' && function_exists('flacso_theme_format_duration_months')
    ? flacso_theme_format_duration_months($duracion_meses_val)
    : '';
if ($duracion_text === '' || $duracion_text === '0 meses' || (is_numeric($duracion_meses_val) && (int) $duracion_meses_val <= 0)) {
    $duracion_text = __('A definir', 'flacso-uruguay');
}
$modalidad_resumen = trim((string) ($academic['modalidad_resumen'] ?? ''));
if ($modalidad_resumen === '') {
    $modalidad_resumen = trim((string) ($instance['modalidad'] ?? ''));
}
if ($modalidad_resumen === '') {
    $modalidad_resumen = __('A definir', 'flacso-uruguay');
} else {
    $modalidad_resumen = function_exists('flacso_theme_format_modality')
        ? flacso_theme_format_modality($modalidad_resumen)
        : mb_strtoupper(mb_substr($modalidad_resumen, 0, 1, 'UTF-8'), 'UTF-8') . mb_substr($modalidad_resumen, 1, null, 'UTF-8');
}
$instancias_presenciales_meta    = $academic['carta_instancias_presenciales'] ?? false;
$mostrar_instancias_presenciales = $instancias_presenciales_meta === true
    || $instancias_presenciales_meta === 1
    || $instancias_presenciales_meta === '1'
    || $instancias_presenciales_meta === 'true';

// ----------------------------------------------------
// Variables de la Cohorte Vigente y Convocatoria
// ----------------------------------------------------
$cohorte_label = trim((string) ($instance['label'] ?? ''));
$cohorte_nombre_completo = $cohorte_label !== ''
    ? $cohorte_label
    : sprintf(__('Convocatoria %s', 'flacso-uruguay'), $inscripciones_year);

$cohorte_inicio_fecha = $fechas_hero;

$cohorte_modalidad_raw    = !empty($instance['modalidad']) ? (string) $instance['modalidad'] : $modalidad_resumen;
$cohorte_modalidad_titulo = function_exists('flacso_theme_format_modality')
    ? flacso_theme_format_modality($cohorte_modalidad_raw)
    : mb_strtoupper(mb_substr($cohorte_modalidad_raw, 0, 1, 'UTF-8'), 'UTF-8') . mb_substr($cohorte_modalidad_raw, 1, null, 'UTF-8');
$cohorte_modalidad_desc   = !empty($instance['modalidad_descripcion']) ? (string) $instance['modalidad_descripcion'] : '';

$calendario_pdf_url = !empty($academic['calendario_pdf_url']) ? trim((string) $academic['calendario_pdf_url']) : '';
$calendario_doc_url = $calendario_pdf_url;
if ($calendario_doc_url && function_exists('flacso_get_pdf_proxy_url')) {
    $proxied = flacso_get_pdf_proxy_url($calendario_doc_url, 'Calendario Académico');
    if ($proxied) $calendario_doc_url = $proxied;
}

$correo_institucional = trim((string) ($academic['correo'] ?? ''));
if ($correo_institucional === '') {
    $correo_institucional = 'informes@flacso.edu.uy';
}

$asistente_docente_id = !empty($academic['asistente_academica_docente_id']) ? (int) $academic['asistente_academica_docente_id'] : 0;
$asistente_nombre     = '';
$asistente_avatar     = '';
if ($asistente_docente_id > 0) {
    $asistente_nombre = function_exists('dp_nombre_completo')
        ? dp_nombre_completo($asistente_docente_id, false)
        : get_the_title($asistente_docente_id);
    if (has_post_thumbnail($asistente_docente_id)) {
        $asistente_avatar = get_the_post_thumbnail_url($asistente_docente_id, 'thumbnail');
    }
}
$asistente_rol    = trim((string) ($academic['asistente_academica_rol'] ?? ''));
if ($asistente_rol === '') {
    $asistente_rol = __('Asistencia Académica', 'flacso-uruguay');
}
$asistente_correo = trim((string) ($academic['asistente_academica_correo'] ?? ''));
if ($asistente_correo === '') {
    $asistente_correo = $correo_institucional;
}

$reconocido_mec               = !empty($academic['reconocido_mec']);
$reconocimiento_internacional = !empty($academic['reconocimiento_internacional']);
$reconocimiento_red_flacso    = !empty($academic['reconocimiento_red_flacso']);
$convenio_iin_oea             = !empty($academic['convenio_iin_oea']);
$has_public_recognitions      = $reconocido_mec || $reconocimiento_internacional || $reconocimiento_red_flacso;
$has_aside_docs               = $calendario_doc_url !== '';

$render_info_card = static function ($card_title, $body, $extra_class = '', array $attrs = []) use ($has_visible_html) {
    if (!$has_visible_html($body)) {
        return;
    }
    $attrs_html = '';
    foreach ($attrs as $name => $value) {
        if ($value === null || $value === false || $value === '') {
            continue;
        }

        if ($value === true) {
            $attrs_html .= ' ' . esc_attr((string) $name);
            continue;
        }

        $attrs_html .= sprintf(' %s="%s"', esc_attr((string) $name), esc_attr((string) $value));
    }
    ?>
    <section class="flacso-oa-info-card <?php echo esc_attr($extra_class); ?>"<?php echo $attrs_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
        <header class="flacso-oa-info-card__header">
            <h3><?php echo esc_html($card_title); ?></h3>
        </header>
        <div class="flacso-oa-info-card__body">
            <?php echo wp_kses_post($body); ?>
        </div>
    </section>
    <?php
};

$build_main_content_cards = static function ($html) use ($has_visible_html) {
    $html = trim((string) $html);

    if (!$has_visible_html($html)) {
        return [];
    }

    $make_card = static function ($card_title, $body) use ($has_visible_html) {
        $card_title = trim((string) $card_title);
        $body = trim((string) $body);

        if (!$has_visible_html($body)) {
            return null;
        }

        $classes = [];

        if ($card_title === '' || preg_match('/<(table|iframe)\b/i', $body)) {
            $classes[] = 'flacso-oa-content-card--wide';
        }

        return [
            'title' => $card_title,
            'body'  => $body,
            'class' => implode(' ', $classes),
        ];
    };

    if (!class_exists('DOMDocument')) {
        $fallback = $make_card('', $html);
        return $fallback ? [$fallback] : [];
    }

    $internal_errors = libxml_use_internal_errors(true);
    $document = new DOMDocument('1.0', 'UTF-8');
    $loaded = $document->loadHTML(
        '<?xml encoding="utf-8" ?><div id="flacso-oa-content-root">' . $html . '</div>',
        LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
    );
    libxml_clear_errors();
    libxml_use_internal_errors($internal_errors);

    if (!$loaded) {
        $fallback = $make_card('', $html);
        return $fallback ? [$fallback] : [];
    }

    $root = $document->getElementById('flacso-oa-content-root');

    if (!$root) {
        $fallback = $make_card('', $html);
        return $fallback ? [$fallback] : [];
    }

    $sections = [];
    $current = [
        'title' => '',
        'body'  => '',
    ];
    $nodes = [];

    foreach ($root->childNodes as $child) {
        $nodes[] = $child;
    }

    foreach ($nodes as $node) {
        if ($node->nodeType === XML_COMMENT_NODE) {
            continue;
        }

        if ($node->nodeType === XML_TEXT_NODE && trim((string) $node->textContent) === '') {
            continue;
        }

        $tag = $node instanceof DOMElement ? strtolower($node->tagName) : '';

        if (in_array($tag, ['h2', 'h3', 'h4', 'h5', 'h6'], true)) {
            $card = $make_card($current['title'], $current['body']);

            if ($card) {
                $sections[] = $card;
            }

            $current = [
                'title' => trim(wp_strip_all_tags($document->saveHTML($node))),
                'body'  => '',
            ];
            continue;
        }

        $current['body'] .= $document->saveHTML($node);
    }

    $card = $make_card($current['title'], $current['body']);

    if ($card) {
        $sections[] = $card;
    }

    if (empty($sections)) {
        $fallback = $make_card('', $html);
        return $fallback ? [$fallback] : [];
    }

    return $sections;
};

$main_content_html = trim($descripcion_html . $filtered_main_content);
$main_content_cards = $build_main_content_cards($main_content_html);

$render_docente_item = static function ($docente_id, $rol = '', $nivel = '3') {
    $doc_post = get_post($docente_id);
    if (!$doc_post || $doc_post->post_type !== 'docente') {
        return;
    }

    $doc_titulo = function_exists('dp_nombre_completo')
        ? dp_nombre_completo($docente_id, false)
        : get_the_title($docente_id);
    $nombre = trim((string) get_post_meta($docente_id, 'nombre', true));
    $apellido = trim((string) get_post_meta($docente_id, 'apellido', true));
    $tit_acad = trim((string) get_post_meta($docente_id, 'titulo_academico', true));
    $cargo = trim((string) get_post_meta($docente_id, 'cargo', true));
    $academic_label = $tit_acad ?: $cargo;

    // No mostrar el mismo título académico dos veces.
    if ($academic_label !== '' && stripos($doc_titulo, $academic_label) !== false) {
        $academic_label = '';
    }

    $cv = trim((string) get_post_meta($docente_id, 'cv', true));
    $has_bio = trim(wp_strip_all_tags($cv)) !== '' || $academic_label !== '' || trim((string) $rol) !== '';
    // No utilizar dp_avatar_markup() en estas tarjetas: devuelve un div e
    // imagen de 104px que se recortan dentro de un avatar de 56px.
    // "medium" obtiene la fotografía sin el recorte cuadrado de "thumbnail".
    $avatar_html = '';
    if (has_post_thumbnail($docente_id)) {
        $avatar_html = (string) get_the_post_thumbnail($docente_id, 'medium', [
            'class' => 'flacso-academic-person__avatar-image',
            'alt' => '',
            'loading' => 'lazy',
            'decoding' => 'async',
        ]);
    }
    if ($avatar_html === '') {
        $initials = $nombre !== '' || $apellido !== ''
            ? mb_substr($nombre, 0, 1) . mb_substr($apellido, 0, 1)
            : mb_substr($doc_titulo, 0, 2);
        $avatar_html = '<span class="flacso-academic-person__initials" aria-hidden="true">'
            . esc_html(mb_strtoupper($initials ?: 'FL', 'UTF-8')) . '</span>';
    }
    ?>
    <div class="flacso-academic-person">
        <?php if ($has_bio) : ?>
            <details class="flacso-academic-person__details">
                <summary class="flacso-academic-person__summary">
                    <span class="flacso-academic-person__avatar" aria-hidden="true"><?php echo $avatar_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
                    <span class="flacso-academic-person__identity">
                        <?php if (trim((string) $rol) !== '') : ?>
                            <span class="flacso-academic-person__role"><?php echo esc_html($rol); ?></span>
                        <?php endif; ?>
                        <span class="flacso-academic-person__name"><?php echo esc_html($doc_titulo); ?></span>
                    </span>
                    <span class="flacso-academic-person__toggle" aria-hidden="true"></span>
                </summary>
                <div class="flacso-academic-person__body">
                    <?php if ($academic_label !== '') : ?>
                        <p class="flacso-academic-person__qualification"><?php echo esc_html($academic_label); ?></p>
                    <?php endif; ?>
                    <?php if (trim(wp_strip_all_tags($cv)) !== '') : ?>
                        <div class="flacso-academic-person__bio"><?php echo wp_kses_post(wpautop($cv)); ?></div>
                    <?php endif; ?>
                </div>
            </details>
        <?php else : ?>
            <article class="flacso-academic-person__static" aria-label="<?php echo esc_attr(sprintf(__('Docente: %s', 'flacso-uruguay'), $doc_titulo)); ?>">
                <span class="flacso-academic-person__avatar" aria-hidden="true"><?php echo $avatar_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
                <span class="flacso-academic-person__name"><?php echo esc_html($doc_titulo); ?></span>
            </article>
        <?php endif; ?>
    </div>
    <?php
};
?>

<div id="inner-wrap" class="wrap kt-clear flacso-oferta-responsive" style="<?php echo esc_attr($offer_style_vars); ?>">
    <div id="primary" class="content-area">
        <div class="content-container">
            <main id="main" class="site-main">
                <article id="post-<?php echo esc_attr($post_id); ?>" <?php post_class('entry content-bg single-entry flacso-oa-article'); ?>>

                    <?php
                    // Esta plantilla posee su hoja de estilos específica.
                    // Se incorpora aquí porque default.php se renderiza luego de wp_head().
                    // No depende de los CSS/JS antiguos del hero ni del cargador global.
                    $default_css_file = __DIR__ . '/default.css';
                    if (is_readable($default_css_file)) :
                    ?>
                        <style id="flacso-academic-offer-default-css"><?php readfile($default_css_file); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_readfile ?></style>
                    <?php endif; ?>

                    <section class="flacso-academic-hero" aria-labelledby="flacso-oa-program-title">
                        <div class="flacso-academic-hero__inner">
                            <div class="flacso-academic-hero__content">
                                <div class="flacso-academic-hero__badges">
                                    <?php if ($tipo_oferta !== '') : ?>
                                        <span class="flacso-academic-hero__badge flacso-academic-hero__badge--type"><?php echo esc_html($tipo_oferta); ?></span>
                                    <?php endif; ?>
                                    <?php if ($duracion_text !== '' && $duracion_text !== __('A definir', 'flacso-uruguay')) : ?>
                                        <span class="flacso-academic-hero__badge flacso-academic-hero__badge--duration">
                                            <span class="flacso-academic-hero__symbol" aria-hidden="true">◷</span>
                                            <?php echo esc_html($duracion_text); ?>
                                        </span>
                                    <?php endif; ?>
                                    <?php if ($inscripciones_abiertas) : ?>
                                        <span class="flacso-academic-hero__badge flacso-academic-hero__badge--open">
                                            <span class="flacso-academic-hero__symbol" aria-hidden="true">✓</span>
                                            <?php esc_html_e('Inscripciones abiertas', 'flacso-uruguay'); ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                                <h1 id="flacso-oa-program-title" class="flacso-academic-hero__title"><?php echo esc_html($title); ?></h1>
                                <div class="flacso-academic-hero__actions">
                                    <a href="<?php echo esc_url($hero_primary_url); ?>"<?php echo $has_preinscription_cta ? ' data-flacso-preinscription-cta="1"' : ''; ?> class="flacso-academic-hero__cta">
                                        <span><?php echo esc_html($hero_primary_label); ?></span>
                                        <span class="flacso-academic-hero__arrow" aria-hidden="true">→</span>
                                    </a>
                                </div>
                            </div>
                            <div class="flacso-academic-hero__media">
                                <?php if ($thumbnail_url !== '') : ?>
                                    <img
                                        class="flacso-academic-hero__image"
                                        src="<?php echo esc_url($thumbnail_url); ?>"
                                        alt="<?php echo esc_attr($title); ?>"
                                        loading="eager"
                                        fetchpriority="high"
                                        decoding="async"
                                        onerror="this.hidden=true;this.nextElementSibling.hidden=false"
                                    >
                                    <div class="flacso-academic-hero__placeholder" hidden>
                                        <span class="flacso-academic-hero__placeholder-mark" aria-hidden="true">F</span>
                                        <span><?php esc_html_e('FLACSO Uruguay', 'flacso-uruguay'); ?></span>
                                    </div>
                                <?php else : ?>
                                    <div class="flacso-academic-hero__placeholder" aria-label="<?php esc_attr_e('Imagen institucional de FLACSO Uruguay', 'flacso-uruguay'); ?>">
                                        <span class="flacso-academic-hero__placeholder-mark" aria-hidden="true">F</span>
                                        <span><?php esc_html_e('FLACSO Uruguay', 'flacso-uruguay'); ?></span>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </section>

                    <?php if ($has_public_recognitions) : ?>
                        <!-- Reconocimientos siempre visibles; no son títulos ni convenios. -->
                        <section class="flacso-academic-trust-strip" aria-labelledby="flacso-academic-trust-title">
                            <div class="flacso-academic-trust-strip__inner">
                                <h2 id="flacso-academic-trust-title"><?php esc_html_e('Reconocimientos', 'flacso-uruguay'); ?></h2>
                                <ul class="flacso-academic-trust-strip__list">
                                    <?php if ($reconocido_mec) : ?>
                                        <li class="flacso-academic-trust-strip__item">
                                            <span class="flacso-academic-trust-strip__icon" aria-hidden="true">
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="m12 2 2.5 2 3.2.2 1 3 2.6 2-.8 3.1.8 3.1-2.6 2-1 3-3.2.2L12 22l-2.5-2-3.2-.2-1-3-2.6-2 .8-3.1-.8-3.1 2.6-2 1-3 3.2-.2z"/><path d="m8.5 12 2.4 2.4L15.8 9.5"/></svg>
                                            </span>
                                            <span><?php esc_html_e('Reconocimiento oficial MEC', 'flacso-uruguay'); ?></span>
                                        </li>
                                    <?php endif; ?>
                                    <?php if ($reconocimiento_internacional) : ?>
                                        <li class="flacso-academic-trust-strip__item">
                                            <span class="flacso-academic-trust-strip__icon" aria-hidden="true">
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M3 12h18M12 3c-5 5-5 13 0 18M12 3c5 5 5 13 0 18"/></svg>
                                            </span>
                                            <span><?php esc_html_e('Apostillado de La Haya', 'flacso-uruguay'); ?></span>
                                        </li>
                                    <?php endif; ?>
                                    <?php if ($reconocimiento_red_flacso) : ?>
                                        <li class="flacso-academic-trust-strip__item">
                                            <span class="flacso-academic-trust-strip__icon" aria-hidden="true">
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><circle cx="5" cy="12" r="2"/><circle cx="19" cy="5" r="2"/><circle cx="19" cy="19" r="2"/><path d="m7 11 10-5M7 13l10 5"/></svg>
                                            </span>
                                            <span><?php esc_html_e('Reconocimiento en la red FLACSO', 'flacso-uruguay'); ?></span>
                                        </li>
                                    <?php endif; ?>
                                </ul>
                            </div>
                        </section>
                    <?php endif; ?>

                    <?php if (!empty($academic['articulaciones'])) : ?>
                        <div class="flacso-oa-container">
                            <?php get_template_part('template-parts/oferta/articulaciones', null, ['planes' => $academic['articulaciones']]); ?>
                        </div>
                    <?php endif; ?>

                    <!-- ========================================== -->
                    <!-- CONTENIDO PRINCIPAL + SIDEBAR DE CONTACTO -->
                    <!-- ========================================== -->
                    <section class="main" id="flacso-oa-contenido">
                        <div class="flacso-oa-container layout">

                            <div class="content">

                                <!-- SECCIÓN: CONVOCATORIA VIGENTE (UNA SOLA PIEZA, SIN CAMPUS INVENTADO) -->
                                <?php if ($show_cohort_section) : ?>
                                    <section class="section" id="cohorte">
                                        <div class="kicker"><?php esc_html_e('Convocatoria vigente', 'flacso-uruguay'); ?></div>
                                        <h2><?php echo esc_html($cohorte_nombre_completo); ?></h2>

                                        <div class="cohort" id="flacso-oa-convocatoria">
                                            <div class="cohort__top">
                                                <div>
                                                    <div class="cohort__title"><?php esc_html_e('Información de cursada', 'flacso-uruguay'); ?></div>
                                                    <div style="color:var(--muted);margin-top:4px;font-size:0.9rem;">
                                                        <?php esc_html_e('Información y fechas de cursada de la convocatoria vigente.', 'flacso-uruguay'); ?>
                                                    </div>
                                                </div>
                                                <?php if ($inscripciones_abiertas) : ?>
                                                    <span class="badge-open">
                                                        <i class="bi bi-check-circle-fill me-1" aria-hidden="true"></i>
                                                        <?php esc_html_e('Inscripciones abiertas', 'flacso-uruguay'); ?>
                                                    </span>
                                                <?php else : ?>
                                                    <span class="badge-open badge-open--soon">
                                                        <i class="bi bi-clock me-1" aria-hidden="true"></i>
                                                        <?php esc_html_e('Próxima apertura', 'flacso-uruguay'); ?>
                                                    </span>
                                                <?php endif; ?>
                                            </div>

                                            <div class="cohort__details">
                                                <div class="cohort__row">
                                                    <div class="cohort__label"><?php esc_html_e('Fecha de inicio', 'flacso-uruguay'); ?></div>
                                                    <div class="cohort__value"><?php echo esc_html($cohorte_inicio_fecha); ?></div>
                                                </div>
                                                <div class="cohort__row">
                                                    <div class="cohort__label"><?php esc_html_e('Modalidad', 'flacso-uruguay'); ?></div>
                                                    <div class="cohort__value"><?php echo esc_html($cohorte_modalidad_titulo); ?></div>
                                                </div>
                                                <?php if ($duracion_text !== '' && $duracion_text !== __('A definir', 'flacso-uruguay')) : ?>
                                                    <div class="cohort__row">
                                                        <div class="cohort__label"><?php esc_html_e('Duración', 'flacso-uruguay'); ?></div>
                                                        <div class="cohort__value"><?php echo esc_html($duracion_text); ?></div>
                                                    </div>
                                                <?php endif; ?>
                                                <?php if ($cohorte_modalidad_desc !== '') : ?>
                                                    <div class="cohort__row" style="grid-column: 1 / -1; padding-left: 0; border-left: 0;">
                                                        <div class="cohort__label"><?php esc_html_e('Cursada y encuentros', 'flacso-uruguay'); ?></div>
                                                        <div class="cohort__value" style="font-weight: normal; font-size: 0.95rem;">
                                                            <?php echo wp_kses_post($cohorte_modalidad_desc); ?>
                                                        </div>
                                                    </div>
                                                <?php endif; ?>
                                            </div>

                                            <?php if ($mostrar_instancias_presenciales || $calendario_doc_url !== '') : ?>
                                                <div class="cohort__note">
                                                    <?php if ($mostrar_instancias_presenciales) : ?>
                                                        <p class="mb-1" style="color: #b45309; font-weight: 700;">
                                                            <i class="bi bi-geo-alt-fill me-1" aria-hidden="true"></i>
                                                            <strong><?php esc_html_e('Incluye instancias exclusivamente presenciales', 'flacso-uruguay'); ?>.</strong>
                                                            <?php esc_html_e('Estas actividades no ofrecen alternativa de participación virtual.', 'flacso-uruguay'); ?>
                                                        </p>
                                                    <?php endif; ?>
                                                    <?php if ($calendario_doc_url !== '') : ?>
                                                        <p class="mb-0 mt-2">
                                                            <a href="<?php echo esc_url($calendario_doc_url); ?>" target="_blank" rel="noopener noreferrer" class="flacso-meta-doc-link flacso-meta-doc-link--calendario" data-flacso-meta-doc-section="CalendarioAcademicoSectionView" style="color:var(--azul);font-weight:750;text-decoration:none;">
                                                                <i class="bi bi-file-earmark-pdf text-danger me-1" aria-hidden="true"></i>
                                                                <?php esc_html_e('Descargar Calendario Académico (PDF)', 'flacso-uruguay'); ?>
                                                            </a>
                                                        </p>
                                                    <?php endif; ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </section>
                                <?php endif; ?>

                                <!-- SECCIÓN: PROPUESTA ACADÉMICA (CON ACORDEONES SEMÁNTICOS <details>) -->
                                <section class="section section--programa" id="programa">
                                    <?php if ($has_main_content) : ?>
                                        <div class="prose lead">
                                            <?php echo wpautop(wp_kses_post($filtered_main_content ?: $descripcion_html)); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                                        </div>
                                    <?php else : ?>
                                        <div class="prose lead">
                                            <p class="flacso-oa-empty-field">
                                                <i class="bi bi-info-circle" aria-hidden="true"></i>
                                                <?php esc_html_e('Información de presentación en proceso de actualización.', 'flacso-uruguay'); ?>
                                            </p>
                                        </div>
                                    <?php endif; ?>

                                    <!-- Acordeones informativos: componente accesible nativo, responsive. -->
                                    <div class="details flacso-academic-accordions">

                                        <!-- 1. PERFIL DE INGRESO -->
                                        <details class="flacso-academic-accordion">
                                            <summary>
                                                <span class="flacso-academic-accordion__icon" aria-hidden="true">
                                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="4"/><path d="M4.5 21v-2.5a7.5 7.5 0 0 1 15 0V21z"/></svg>
                                                </span>
                                                <span class="flacso-academic-accordion__label"><?php esc_html_e('Perfil de ingreso', 'flacso-uruguay'); ?></span>
                                                <span class="flacso-academic-accordion__chevron" aria-hidden="true">
                                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
                                                </span>
                                            </summary>
                                            <div class="detail-body flacso-academic-accordion__body">
                                                <?php if (!empty($academic['perfil_ingreso_html'])) : ?>
                                                    <?php echo wpautop(wp_kses_post($academic['perfil_ingreso_html'])); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                                                <?php else : ?>
                                                    <p class="flacso-oa-empty-field"><i class="bi bi-info-circle" aria-hidden="true"></i> <?php esc_html_e('Información sobre perfil de ingreso no disponible por el momento.', 'flacso-uruguay'); ?></p>
                                                <?php endif; ?>
                                            </div>
                                        </details>

                                        <!-- 2. REQUISITOS DE INGRESO -->
                                        <details class="flacso-academic-accordion">
                                            <summary>
                                                <span class="flacso-academic-accordion__icon" aria-hidden="true">
                                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M7 2.5h8l4 4V21H7z"/><path d="M15 2.5V7h4"/><path d="M10 12h6M10 16h6"/></svg>
                                                </span>
                                                <span class="flacso-academic-accordion__label"><?php esc_html_e('Requisitos de ingreso', 'flacso-uruguay'); ?></span>
                                                <span class="flacso-academic-accordion__chevron" aria-hidden="true">
                                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
                                                </span>
                                            </summary>
                                            <div class="detail-body flacso-academic-accordion__body">
                                                <?php if (!empty($academic['requisitos_ingreso_html'])) : ?>
                                                    <?php echo wpautop(wp_kses_post($academic['requisitos_ingreso_html'])); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                                                <?php else : ?>
                                                    <p class="flacso-oa-empty-field"><i class="bi bi-info-circle" aria-hidden="true"></i> <?php esc_html_e('Información sobre requisitos de ingreso no disponible por el momento.', 'flacso-uruguay'); ?></p>
                                                <?php endif; ?>
                                            </div>
                                        </details>

                                        <!-- 4. PERFIL DE EGRESO -->
                                        <details class="flacso-academic-accordion">
                                            <summary>
                                                <span class="flacso-academic-accordion__icon" aria-hidden="true">
                                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M4 21v-7h4v7M10 21V5h4v16M16 21v-11h4v11"/></svg>
                                                </span>
                                                <span class="flacso-academic-accordion__label"><?php esc_html_e('Perfil de egreso', 'flacso-uruguay'); ?></span>
                                                <span class="flacso-academic-accordion__chevron" aria-hidden="true">
                                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
                                                </span>
                                            </summary>
                                            <div class="detail-body flacso-academic-accordion__body">
                                                <?php if (!empty($academic['perfil_egreso_html'])) : ?>
                                                    <?php echo wpautop(wp_kses_post($academic['perfil_egreso_html'])); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                                                <?php else : ?>
                                                    <p class="flacso-oa-empty-field"><i class="bi bi-info-circle" aria-hidden="true"></i> <?php esc_html_e('Información sobre perfil de egreso no disponible por el momento.', 'flacso-uruguay'); ?></p>
                                                <?php endif; ?>
                                            </div>
                                        </details>

                                        <!-- 5. REQUISITOS DE EGRESO Y TITULACIÓN -->
                                        <details class="flacso-academic-accordion" id="titulacion">
                                            <summary>
                                                <span class="flacso-academic-accordion__icon" aria-hidden="true">
                                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="m2.5 9.5 9.5-5 9.5 5-9.5 5z"/><path d="M6.5 12v5.5c3.5 2.3 7.5 2.3 11 0V12M21.5 10v7"/></svg>
                                                </span>
                                                <span class="flacso-academic-accordion__label"><?php esc_html_e('Requisitos de egreso y titulación.', 'flacso-uruguay'); ?></span>
                                                <span class="flacso-academic-accordion__chevron" aria-hidden="true">
                                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
                                                </span>
                                            </summary>
                                            <div class="detail-body flacso-academic-accordion__body">
                                                <?php if (!empty($academic['requisitos_egreso_html'])) : ?>
                                                    <?php echo wpautop(wp_kses_post($academic['requisitos_egreso_html'])); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                                                <?php else : ?>
                                                    <p class="flacso-oa-empty-field"><i class="bi bi-info-circle" aria-hidden="true"></i> <?php esc_html_e('Información sobre requisitos de egreso no disponible por el momento.', 'flacso-uruguay'); ?></p>
                                                <?php endif; ?>
                                            </div>
                                        </details>

                                        <!-- 6. TÍTULO Y CERTIFICACIÓN -->
                                        <details class="flacso-academic-accordion">
                                            <summary>
                                                <span class="flacso-academic-accordion__icon" aria-hidden="true">
                                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="5"/><circle cx="12" cy="8" r="2"/><path d="m8.5 12-2 9 5.5-2.8L17.5 21l-2-9"/></svg>
                                                </span>
                                                <span class="flacso-academic-accordion__label"><?php esc_html_e('Título y certificación', 'flacso-uruguay'); ?></span>
                                                <span class="flacso-academic-accordion__chevron" aria-hidden="true">
                                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
                                                </span>
                                            </summary>
                                            <div class="detail-body flacso-academic-accordion__body">
                                                <?php if (!empty(trim(wp_strip_all_tags((string) ($academic['titulos_certificaciones_html'] ?? ''))))) : ?>
                                                    <?php echo wp_kses_post($academic['titulos_certificaciones_html']); ?>
                                                <?php else : ?>
                                                    <p class="flacso-oa-empty-field"><i class="bi bi-info-circle" aria-hidden="true"></i> <?php esc_html_e('Información sobre títulos y certificaciones no disponible por el momento.', 'flacso-uruguay'); ?></p>
                                                <?php endif; ?>
                                            </div>
                                        </details>

                                    </div>
                                </section>

                                <?php if ($convenio_iin_oea) : ?>
                                    <aside class="flacso-academic-convention" aria-labelledby="flacso-academic-convention-title">
                                        <span class="flacso-academic-convention__symbol" aria-hidden="true">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="m12 2 8 3v6c0 5-3.5 8.5-8 11-4.5-2.5-8-6-8-11V5z"/><path d="m8 12 3 3 5-6"/></svg>
                                        </span>
                                        <div class="flacso-academic-convention__content">
                                            <h3 id="flacso-academic-convention-title"><?php esc_html_e('Convenio institucional', 'flacso-uruguay'); ?></h3>
                                            <p><?php esc_html_e('Convenio con el Instituto Interamericano del Niño, la Niña y Adolescentes (IIN - OEA).', 'flacso-uruguay'); ?></p>
                                        </div>
                                    </aside>
                                <?php endif; ?>

                                <!-- FINAL CTA -->
                                <?php if ($has_preinscription_cta) : ?>
                                    <section class="final-cta">
                                        <div class="final-cta__text">
                                            <h3><?php esc_html_e('¿Querés postularte a la próxima cohorte?', 'flacso-uruguay'); ?></h3>
                                        </div>
                                        <a class="btn btn--primary" href="<?php echo esc_url($preinscripcion_url); ?>" data-flacso-preinscription-cta="1" style="background:var(--azul) !important;color:#fff !important;white-space:nowrap;" onclick="if(typeof window.flacsoMetaTrack === 'function'){ window.flacsoMetaTrack('InitiateCheckout', { content_name: <?php echo wp_json_encode($title); ?>, content_category: 'oferta_academica' }); }">
                                            <?php esc_html_e('Iniciar preinscripción →', 'flacso-uruguay'); ?>
                                        </a>
                                    </section>
                                <?php endif; ?>

                            </div>

                            <!-- SIDEBAR -->
                            <aside id="flacso-oa-consulta" class="sidebar flacso-mobile-info-sheet" aria-label="<?php esc_attr_e('Formulario de consulta', 'flacso-uruguay'); ?>">
                                <!-- Cabecera móvil del panel lateral; se reutiliza el único formulario. -->
                                <header class="flacso-mobile-info-sheet__header">
                                    <h2 id="flacso-mobile-info-sheet-title"><?php esc_html_e('Más información', 'flacso-uruguay'); ?></h2>
                                    <button type="button" class="flacso-mobile-info-sheet__close" aria-label="<?php esc_attr_e('Cerrar más información', 'flacso-uruguay'); ?>">
                                        <span aria-hidden="true">×</span>
                                    </button>
                                </header>
                                <div class="contact">
                                    <div class="contact__head">
                                        <h3><?php esc_html_e('Solicitá información', 'flacso-uruguay'); ?></h3>
                                    </div>
                                    <div class="contact__body">
                                        <?php
                                        if (function_exists('flacso_consultas_render_form')) {
                                            echo flacso_consultas_render_form(['mostrar_preinscripcion' => false]);
                                        }
                                        ?>



                                    </div>
                                </div>

                                <div class="contact-support">
                                    <div class="contact-support__section">
                                        <span class="contact-support__eyebrow"><?php esc_html_e('Información útil', 'flacso-uruguay'); ?></span>
                                        <nav class="contact-support__links" aria-label="<?php esc_attr_e('Enlaces de la oferta académica', 'flacso-uruguay'); ?>">
                                            <?php if ($show_cohort_section) : ?>
                                                <a href="#cohorte"><i class="bi bi-arrow-down-short"></i> <?php esc_html_e('Información de la cohorte', 'flacso-uruguay'); ?></a>
                                            <?php endif; ?>
                                            <a href="#titulacion"><i class="bi bi-arrow-down-short"></i> <?php esc_html_e('Requisitos de egreso y titulación.', 'flacso-uruguay'); ?></a>
                                        </nav>
                                    </div>

                                    <?php if ($asistente_nombre !== '' || $asistente_correo !== '') : ?>
                                        <div class="contact-support__section contact-support__person">
                                            <span class="contact-support__eyebrow"><?php esc_html_e('Contacto académico', 'flacso-uruguay'); ?></span>

                                            <?php if ($asistente_nombre !== '') : ?>
                                                <div class="flacso-oa-aside-person">
                                                    <?php if ($asistente_avatar !== '') : ?>
                                                        <img src="<?php echo esc_url($asistente_avatar); ?>" alt="" class="flacso-oa-aside-person__avatar" />
                                                    <?php else : ?>
                                                        <span class="flacso-oa-aside-person__avatar-placeholder"><i class="bi bi-person-fill"></i></span>
                                                    <?php endif; ?>
                                                    <div class="flacso-oa-aside-person__info">
                                                        <strong><?php echo esc_html($asistente_nombre); ?></strong>
                                                        <small><?php echo esc_html($asistente_rol); ?></small>
                                                    </div>
                                                </div>
                                            <?php endif; ?>

                                            <?php if ($asistente_correo !== '') : ?>
                                                <a href="mailto:<?php echo esc_attr($asistente_correo); ?>" class="flacso-oa-aside-contact__email">
                                                    <i class="bi bi-envelope-fill"></i>
                                                    <span><?php echo esc_html($asistente_correo); ?></span>
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </aside>

                        </div>
                    </section>

                    <!-- ========================================== -->
                    <!-- EQUIPO ACADÉMICO -->
                    <!-- ========================================== -->
                    <?php
                    $merged_equipos = [];

                    $coordinacion_academica = $academic['coordinacion_academica'] ?? [];
                    if (!empty($coordinacion_academica) && is_array($coordinacion_academica)) {
                        foreach ($coordinacion_academica as $coord) {
                            if (!empty($coord['docentes']) && is_array($coord['docentes'])) {
                                $merged_equipos[] = [
                                    'nombre'      => !empty($coord['rol']) ? $coord['rol'] : __('Coordinación académica', 'flacso-uruguay'),
                                    'descripcion' => $coord['descripcion'] ?? '',
                                    'docentes'    => $coord['docentes'],
                                    'importancia' => '1',
                                ];
                            }
                        }
                    }

                    $prog_coord = $program['coordinacion'] ?? [];
                    if (empty($merged_equipos) && !empty($prog_coord) && is_array($prog_coord)) {
                        $merged_equipos[] = [
                            'nombre'      => __('Coordinación académica', 'flacso-uruguay'),
                            'descripcion' => '',
                            'docentes'    => array_filter(array_map(static function ($m) {
                                return !empty($m['docente_id']) ? ['id' => $m['docente_id'], 'rol' => $m['rol'] ?? ''] : null;
                            }, $prog_coord)),
                            'importancia' => '1',
                        ];
                    }

                    $equipos = $academic['equipos'] ?? [];
                    if (!empty($equipos) && is_array($equipos)) {
                        foreach ($equipos as $eq) {
                            if (!empty($eq['docentes']) && is_array($eq['docentes'])) {
                                $merged_equipos[] = [
                                    'nombre'      => $eq['nombre'] ?? '',
                                    'descripcion' => $eq['descripcion'] ?? '',
                                    'docentes'    => $eq['docentes'],
                                    'importancia' => strval($eq['importancia'] ?? '3'),
                                ];
                            }
                        }
                    }
                    ?>

                    <?php if (!empty($merged_equipos)) : ?>
                        <section class="flacso-academic-team flacso-oa-team-section" aria-labelledby="flacso-academic-team-title">
                            <div class="flacso-oa-container flacso-academic-team__container">
                                <header class="flacso-academic-team__header">
                                    <h2 id="flacso-academic-team-title"><?php esc_html_e('Equipo académico', 'flacso-uruguay'); ?></h2>
                                </header>

                                <div class="flacso-academic-team__groups">
                                    <?php foreach ($merged_equipos as $grupo) : ?>
                                        <?php
                                        $nivel = in_array((string) ($grupo['importancia'] ?? '3'), ['1', '2', '3'], true)
                                            ? (string) ($grupo['importancia'] ?? '3')
                                            : '3';
                                        $docentes_grupo = array_values((array) ($grupo['docentes'] ?? []));
                                        if (!$docentes_grupo) {
                                            continue;
                                        }
                                        ?>
                                        <div class="flacso-academic-team__group flacso-academic-team__group--level-<?php echo esc_attr($nivel); ?>">
                                            <?php if (!empty($grupo['nombre'])) : ?>
                                                <h3 class="flacso-academic-team__group-title"><?php echo esc_html($grupo['nombre']); ?></h3>
                                            <?php endif; ?>
                                            <?php if (!empty($grupo['descripcion'])) : ?>
                                                <div class="flacso-academic-team__group-description"><?php echo wp_kses_post(wpautop($grupo['descripcion'])); ?></div>
                                            <?php endif; ?>
                                            <div class="flacso-academic-team__grid">
                                                <?php foreach ($docentes_grupo as $docente_item) : ?>
                                                    <?php
                                                    $docente_id = is_array($docente_item) ? (int) ($docente_item['id'] ?? 0) : (int) $docente_item;
                                                    $rol_especifico = is_array($docente_item) ? (string) ($docente_item['rol'] ?? '') : '';
                                                    $render_docente_item($docente_id, $rol_especifico, $nivel);
                                                    ?>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </section>
                    <?php else : ?>
                        <section class="flacso-oa-team-section flacso-oa-team-section--empty">
                            <div class="flacso-oa-container">
                                <header class="flacso-oa-section-header">
                                    <h2><?php esc_html_e('Equipo Académico', 'flacso-uruguay'); ?></h2>
                                </header>
                                <div class="flacso-oa-team-empty">
                                    <p class="flacso-oa-empty-field">
                                        <?php esc_html_e('El equipo docente y coordinación se encuentra en proceso de confirmación.', 'flacso-uruguay'); ?>
                                    </p>
                                </div>
                            </div>
                        </section>
                    <?php endif; ?>

                    <!-- Barra inferior móvil y fondo del panel lateral de consultas. -->
                    <div class="flacso-mobile-info-overlay" aria-hidden="true"></div>
                    <nav class="flacso-mobile-actions" aria-label="<?php esc_attr_e('Acciones de la Oferta Académica', 'flacso-uruguay'); ?>">
                        <button type="button" class="flacso-mobile-actions__info" aria-expanded="false" aria-controls="flacso-oa-consulta">
                            <span class="flacso-mobile-actions__info-icon" aria-hidden="true">ⓘ</span>
                            <?php esc_html_e('Más información', 'flacso-uruguay'); ?>
                        </button>
                        <?php if ($has_preinscription_cta) : ?>
                            <a class="flacso-mobile-actions__register" href="<?php echo esc_url($preinscripcion_url); ?>" data-flacso-preinscription-cta="1">
                                <?php esc_html_e('Preinscribirme', 'flacso-uruguay'); ?>
                                <span aria-hidden="true">→</span>
                            </a>
                        <?php endif; ?>
                    </nav>

                </article>
            </main>
        </div>
    </div>
</div>

<script>
/* Panel lateral derecho de "Más información": reutiliza el único formulario existente. */
(function() {
    var root = document.querySelector('.flacso-oferta-responsive');
    if (!root || !window.matchMedia) return;

    var aside = root.querySelector('#flacso-oa-consulta.flacso-mobile-info-sheet');
    var openButton = root.querySelector('.flacso-mobile-actions__info');
    var closeButton = aside ? aside.querySelector('.flacso-mobile-info-sheet__close') : null;
    var overlay = root.querySelector('.flacso-mobile-info-overlay');
    if (!aside || !openButton || !closeButton || !overlay) return;

    var mobile = window.matchMedia('(max-width: 760px)');
    var isOpen = false;
    var returnFocus = null;
    var savedScroll = 0;
    var bodyBefore = {};
    var topLayerActive = false;

    function isMobile() { return mobile.matches; }

    // Los widgets de accesibilidad y traducción pueden estar por encima de
    // cualquier z-index normal. La top layer nativa evita solapamientos.
    function showInTopLayer() {
        if (typeof aside.showPopover !== 'function' || typeof overlay.showPopover !== 'function') {
            return;
        }

        overlay.setAttribute('popover', 'manual');
        aside.setAttribute('popover', 'manual');
        try {
            overlay.showPopover();
            aside.showPopover();
            topLayerActive = true;
        } catch (error) {
            if (aside.matches(':popover-open')) aside.hidePopover();
            if (overlay.matches(':popover-open')) overlay.hidePopover();
            aside.removeAttribute('popover');
            overlay.removeAttribute('popover');
            topLayerActive = false;
        }
    }

    function hideFromTopLayer() {
        if (!topLayerActive) return;
        if (aside.matches(':popover-open')) aside.hidePopover();
        if (overlay.matches(':popover-open')) overlay.hidePopover();
        aside.removeAttribute('popover');
        overlay.removeAttribute('popover');
        topLayerActive = false;
    }

    function setClosedAccessibility() {
        aside.inert = isMobile();
        if (isMobile()) {
            aside.setAttribute('aria-hidden', 'true');
        } else {
            aside.removeAttribute('aria-hidden');
        }
        aside.removeAttribute('role');
        aside.removeAttribute('aria-modal');
        aside.removeAttribute('aria-labelledby');
        openButton.setAttribute('aria-expanded', 'false');
    }

    function restoreBody() {
        document.body.style.position = bodyBefore.position || '';
        document.body.style.top = bodyBefore.top || '';
        document.body.style.left = bodyBefore.left || '';
        document.body.style.right = bodyBefore.right || '';
        document.body.style.width = bodyBefore.width || '';
        document.body.style.overflow = bodyBefore.overflow || '';
        window.scrollTo(0, savedScroll);
    }

    function closePanel(focusBack) {
        if (!isOpen) return;
        isOpen = false;
        root.classList.remove('flacso-mobile-sheet-open');
        aside.classList.remove('is-open');
        overlay.classList.remove('is-visible');
        hideFromTopLayer();
        setClosedAccessibility();
        restoreBody();
        if (focusBack !== false && returnFocus && returnFocus.isConnected) {
            returnFocus.focus({preventScroll: true});
        }
    }

    function openPanel(trigger) {
        if (!isMobile() || isOpen) return;
        returnFocus = trigger || document.activeElement;
        savedScroll = window.scrollY || document.documentElement.scrollTop || 0;
        bodyBefore = {
            position: document.body.style.position,
            top: document.body.style.top,
            left: document.body.style.left,
            right: document.body.style.right,
            width: document.body.style.width,
            overflow: document.body.style.overflow
        };
        document.body.style.position = 'fixed';
        document.body.style.top = '-' + savedScroll + 'px';
        document.body.style.left = '0';
        document.body.style.right = '0';
        document.body.style.width = '100%';
        document.body.style.overflow = 'hidden';

        isOpen = true;
        aside.inert = false;
        aside.removeAttribute('aria-hidden');
        aside.setAttribute('role', 'dialog');
        aside.setAttribute('aria-modal', 'true');
        aside.setAttribute('aria-labelledby', 'flacso-mobile-info-sheet-title');
        root.classList.add('flacso-mobile-sheet-open');
        aside.classList.add('is-open');
        overlay.classList.add('is-visible');
        showInTopLayer();
        openButton.setAttribute('aria-expanded', 'true');
        aside.scrollTop = 0;
        closeButton.focus({preventScroll: true});
    }

    function syncMode() {
        if (!isMobile() && isOpen) closePanel(false);
        root.classList.toggle('flacso-mobile-sheet-ready', isMobile());
        if (!isOpen) setClosedAccessibility();
    }

    openButton.addEventListener('click', function() { openPanel(openButton); });
    closeButton.addEventListener('click', function() { closePanel(true); });
    overlay.addEventListener('click', function() { closePanel(true); });

    // Cerrar deslizando hacia la derecha sin interferir con los campos del formulario.
    // Un desplazamiento vertical no debe activar el gesto de cierre.
    var touchStart = null;
    aside.addEventListener('touchstart', function(event) {
        if (!isOpen || event.touches.length !== 1) {
            touchStart = null;
            return;
        }
        var target = event.target;
        if (target instanceof Element && target.closest('input, textarea, select, button, a, label, [contenteditable="true"]')) {
            touchStart = null;
            return;
        }
        touchStart = {
            x: event.touches[0].clientX,
            y: event.touches[0].clientY
        };
    }, {passive: true});

    aside.addEventListener('touchend', function(event) {
        if (!touchStart || !isOpen || !event.changedTouches.length) return;
        var deltaX = event.changedTouches[0].clientX - touchStart.x;
        var deltaY = event.changedTouches[0].clientY - touchStart.y;
        touchStart = null;
        if (deltaX > 80 && Math.abs(deltaX) > Math.abs(deltaY) * 1.5) {
            closePanel(true);
        }
    }, {passive: true});
    aside.addEventListener('touchcancel', function() { touchStart = null; }, {passive: true});

    // Reutilizar también los CTA de solicitar información existentes.
    root.addEventListener('click', function(event) {
        if (!isMobile() || !(event.target instanceof Element)) return;
        var link = event.target.closest('a[href="#flacso-oa-consulta"]');
        if (!link) return;
        event.preventDefault();
        openPanel(link);
    });

    aside.addEventListener('click', function(event) {
        if (!isOpen || !(event.target instanceof Element)) return;
        var destination = event.target.closest('a[href^="#"]');
        if (destination && destination.getAttribute('href') !== '#flacso-oa-consulta') {
            closePanel(false);
        }
    });

    document.addEventListener('keydown', function(event) {
        if (!isOpen) return;
        if (event.key === 'Escape') {
            event.preventDefault();
            closePanel(true);
            return;
        }
        if (event.key !== 'Tab') return;
        var elements = Array.prototype.filter.call(
            aside.querySelectorAll('a[href], button:not([disabled]), input:not([disabled]), select:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex="-1"])'),
            function(el) { return el.getClientRects().length && !el.closest('[hidden]'); }
        );
        if (!elements.length) {
            event.preventDefault();
            closeButton.focus();
            return;
        }
        var first = elements[0], last = elements[elements.length - 1];
        if (event.shiftKey && document.activeElement === first) {
            event.preventDefault();
            last.focus();
        } else if (!event.shiftKey && document.activeElement === last) {
            event.preventDefault();
            first.focus();
        }
    });

    if (mobile.addEventListener) {
        mobile.addEventListener('change', syncMode);
    } else if (mobile.addListener) {
        mobile.addListener(syncMode);
    }
    syncMode();
})();


(function() {
    if (typeof window.flacsoMetaTrack !== 'function') return;
    try {
        window.flacsoMetaTrack('ViewContent', {
            content_name: <?php echo wp_json_encode($title); ?>,
            content_category: 'oferta_academica',
            content_ids: ['oferta-' + <?php echo wp_json_encode((string) $post_id); ?>],
            flacso_stage: 'pagina_oferta'
        });
    } catch (e) {}
})();

(function() {
    if (typeof window.flacsoMetaTrackCustom !== 'function') return;

    var ofertaTitle = <?php echo wp_json_encode($title); ?>;
    var ofertaId = <?php echo wp_json_encode((string) $post_id); ?>;
    var trackedSections = {};
    var baseParams = {
        content_name: ofertaTitle,
        content_category: 'oferta_academica',
        content_ids: ['oferta-' + ofertaId]
    };

    function trackDocumentEvent(eventName, extraParams) {
        if (!eventName) return;

        try {
            window.flacsoMetaTrackCustom(eventName, Object.assign({}, baseParams, extraParams || {}));
        } catch (e) {}
    }

    document.querySelectorAll('[data-flacso-meta-doc-section]').forEach(function(section) {
        var eventName = section.getAttribute('data-flacso-meta-doc-section') || '';
        var layout = section.getAttribute('data-flacso-meta-doc-layout') || '';

        if (!eventName) {
            return;
        }

        function fireSectionView() {
            if (trackedSections[eventName]) {
                return;
            }

            trackedSections[eventName] = true;
            trackDocumentEvent(eventName, {
                flacso_stage: 'document_section_visible',
                flacso_doc_layout: layout
            });
        }

        if ('IntersectionObserver' in window) {
            var observer = new IntersectionObserver(function(entries) {
                entries.forEach(function(entry) {
                    if (!entry.isIntersecting || entry.intersectionRatio < 0.35) {
                        return;
                    }

                    fireSectionView();
                    observer.disconnect();
                });
            }, {
                threshold: [0.35]
            });

            observer.observe(section);
        } else {
            fireSectionView();
        }
    });

    document.addEventListener('click', function(event) {
        var link = event.target.closest('.flacso-meta-doc-link');

        if (!link) {
            return;
        }

        var section = link.closest('[data-flacso-meta-doc-section]');
        var layout = section ? (section.getAttribute('data-flacso-meta-doc-layout') || '') : '';
        var eventName = '';

        if (link.classList.contains('flacso-meta-doc-link--calendario')) {
            eventName = 'CalendarioAcademicoClick';
        }

        trackDocumentEvent(eventName, {
            flacso_stage: 'document_click',
            flacso_doc_layout: layout,
            flacso_doc_url: link.href || ''
        });
    }, true);
})();
</script>
