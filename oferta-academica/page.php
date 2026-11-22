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

// Separar visualmente el tipo de oferta del título si viene como prefijo en el nombre
$title_prefix = '';
$title_main   = $title;
$prefix_pattern = '/^(Diploma|Maestría|Doctorado|Especialización|Certificado|Curso)(\s+(en|de|sobre|superior en|internacional en))?(:|\s+)/iu';
if (preg_match($prefix_pattern, $title, $matches)) {
    $title_prefix = trim($matches[0], " \t\n\r\0\x0B:");
    $title_main   = trim(mb_substr($title, mb_strlen($matches[0])));
}

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

// Texto sobre el color de la Oferta (botones, etiquetas, encabezado del formulario):
// blanco o negro, el de mayor contraste WCAG 2, que siempre cumple AA.
$offer_ink = flacso_child_contrast_ink($offer_color);

$offer_style_vars = sprintf(
    '--oa-brand:%s;--oa-brand-rgb:%d,%d,%d;--oa-brand-dark:%s;--oa-brand-deep:%s;--oa-brand-light:%s;--oa-brand-contrast:%s;--oa-hero-brand-ink:%s;',
    esc_attr($offer_color),
    $offer_r,
    $offer_g,
    $offer_b,
    esc_attr($offer_dark),
    esc_attr($offer_deep),
    esc_attr($offer_light),
    esc_attr($offer_ink),
    esc_attr($offer_ink)
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

// Las cohortes planificadas también se comunican públicamente, aunque la
// preinscripción todavía no esté habilitada. Las cerradas se mantienen ocultas.
$cohort_state = sanitize_key((string) ($instance['estado'] ?? ''));
$is_planned_cohort = in_array($cohort_state, ['planificada', 'planificado'], true);
$show_cohort_section = !empty($instance['id']) && ($is_planned_cohort || ($inscripciones_abiertas && !in_array($cohort_state, ['cerrada', 'cerrado', 'finalizada', 'finalizado', 'cancelada', 'cancelado'], true)));

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
$oferta_creditos = trim((string) ($academic['creditos'] ?? ''));
$oferta_horas = trim((string) ($academic['carga_horaria'] ?? ''));

$cohorte_modalidad_raw    = !empty($instance['modalidad']) ? (string) $instance['modalidad'] : $modalidad_resumen;
$cohorte_modalidad_titulo = function_exists('flacso_theme_format_modality')
    ? flacso_theme_format_modality($cohorte_modalidad_raw)
    : mb_strtoupper(mb_substr($cohorte_modalidad_raw, 0, 1, 'UTF-8'), 'UTF-8') . mb_substr($cohorte_modalidad_raw, 1, null, 'UTF-8');
$cohorte_modalidad_desc   = !empty($instance['modalidad_descripcion']) ? (string) $instance['modalidad_descripcion'] : '';

$calendario_pdf_url = !empty($academic['calendario_pdf_url']) ? trim((string) $academic['calendario_pdf_url']) : '';
$malla_doc_url = trim((string) ($academic['malla_curricular_url'] ?? ''));
$calendario_y_malla_compartidos = flacso_theme_calendar_and_curriculum_share_document(
    $calendario_pdf_url,
    $malla_doc_url
);
// Comparar las fuentes ANTES de que el proxy genere URLs con distintos nombres.
$calendario_doc_url = $calendario_pdf_url;
if ($calendario_doc_url && function_exists('flacso_get_pdf_proxy_url')) {
    $documento_titulo = $calendario_y_malla_compartidos ? 'Calendario y malla académica' : 'Calendario Académico';
    $proxied = flacso_get_pdf_proxy_url($calendario_doc_url, $documento_titulo);
    if ($proxied) $calendario_doc_url = $proxied;
}
if ($malla_doc_url && !$calendario_y_malla_compartidos && function_exists('flacso_get_pdf_proxy_url')) {
    $proxied_malla = flacso_get_pdf_proxy_url($malla_doc_url, 'Malla curricular');
    if ($proxied_malla) $malla_doc_url = $proxied_malla;
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
$has_aside_docs               = $calendario_doc_url !== '' || $malla_doc_url !== '';

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

// Coordinación: tarjeta propia, con el CV siempre desplegado (no es un acordeón).
$render_coordinador = static function ($docente_id, $rol = '') {
    $doc_post = get_post($docente_id);
    if (!$doc_post || $doc_post->post_type !== 'docente') {
        return;
    }
    $nombre_completo = function_exists('dp_nombre_completo')
        ? dp_nombre_completo($docente_id, false)
        : get_the_title($docente_id);
    $nombre   = trim((string) get_post_meta($docente_id, 'nombre', true));
    $apellido = trim((string) get_post_meta($docente_id, 'apellido', true));
    $titulo_acad = trim((string) get_post_meta($docente_id, 'titulo_academico', true));
    if ($titulo_acad === '') {
        $titulo_acad = trim((string) get_post_meta($docente_id, 'cargo', true));
    }
    if ($titulo_acad !== '' && stripos($nombre_completo, $titulo_acad) !== false) {
        $titulo_acad = '';
    }
    $cv = trim((string) get_post_meta($docente_id, 'cv', true));
    $iniciales = ($nombre !== '' || $apellido !== '')
        ? mb_substr($nombre, 0, 1) . mb_substr($apellido, 0, 1)
        : mb_substr($nombre_completo, 0, 2);
    ?>
    <article class="flacso-coordinator">
        <header class="flacso-coordinator__head">
            <span class="flacso-coordinator__avatar" aria-hidden="true">
                <?php if (has_post_thumbnail($docente_id)) : ?>
                    <?php echo get_the_post_thumbnail($docente_id, 'medium', ['alt' => '', 'loading' => 'lazy', 'decoding' => 'async']); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                <?php else : ?>
                    <span class="flacso-coordinator__initials"><?php echo esc_html(mb_strtoupper($iniciales ?: 'FL', 'UTF-8')); ?></span>
                <?php endif; ?>
            </span>
            <div class="flacso-coordinator__id">
                <?php if (trim((string) $rol) !== '') : ?>
                    <span class="flacso-coordinator__role"><?php echo esc_html($rol); ?></span>
                <?php endif; ?>
                <h3 class="flacso-coordinator__name"><?php echo esc_html($nombre_completo); ?></h3>
                <?php if ($titulo_acad !== '') : ?>
                    <p class="flacso-coordinator__qualification"><?php echo esc_html($titulo_acad); ?></p>
                <?php endif; ?>
            </div>
        </header>
        <?php if (trim(wp_strip_all_tags($cv)) !== '') : ?>
            <div class="flacso-coordinator__bio"><?php echo wp_kses_post(wpautop($cv)); ?></div>
        <?php endif; ?>
    </article>
    <?php
};

?>

<div class="wrap kt-clear flacso-oferta-responsive" style="<?php echo esc_attr($offer_style_vars); ?>" data-offer-title="<?php echo esc_attr($title); ?>" data-offer-id="<?php echo esc_attr((string) $post_id); ?>">
    <div id="primary" class="content-area">
        <div class="content-container">
            <<?php echo flacso_child_main_tag(); ?> id="main" class="site-main">
                <article id="post-<?php echo esc_attr($post_id); ?>" <?php post_class('entry content-bg single-entry flacso-oa-article'); ?>>

                    <section class="flacso-academic-hero" aria-labelledby="flacso-oa-program-title">
                        <div class="flacso-academic-hero__inner">
                            <div class="flacso-academic-hero__content">
                                <div class="flacso-academic-hero__badges">
                                    <?php if ($tipo_oferta !== '') : ?>
                                        <span class="flacso-academic-hero__badge flacso-academic-hero__badge--type"><?php echo esc_html($tipo_oferta); ?></span>
                                    <?php endif; ?>
                                    <?php if ($duracion_text !== '' && $duracion_text !== __('A definir', 'flacso-uruguay')) : ?>
                                        <span class="flacso-academic-hero__badge flacso-academic-hero__badge--duration">
                                            <i class="bi bi-clock flacso-academic-hero__symbol" aria-hidden="true"></i>
                                            <?php echo esc_html($duracion_text); ?>
                                        </span>
                                    <?php endif; ?>
                                    <?php if ($inscripciones_abiertas) : ?>
                                        <span class="flacso-academic-hero__badge flacso-academic-hero__badge--open">
                                            <i class="bi bi-check-lg flacso-academic-hero__symbol" aria-hidden="true"></i>
                                            <?php esc_html_e('Inscripciones abiertas', 'flacso-uruguay'); ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                                <h1 id="flacso-oa-program-title" class="flacso-academic-hero__title">
                                    <?php if ($title_prefix !== '') : ?>
                                        <span class="flacso-academic-hero__title-kicker"><?php echo esc_html($title_prefix); ?></span>
                                    <?php endif; ?>
                                    <span class="flacso-academic-hero__title-main"><?php echo esc_html($title_main); ?></span>
                                </h1>
                                <div class="flacso-academic-hero__actions">
                                    <a href="<?php echo esc_url($hero_primary_url); ?>"<?php echo $has_preinscription_cta ? ' data-flacso-preinscription-cta="1"' : ''; ?> class="flacso-academic-hero__cta">
                                        <span><?php echo esc_html($hero_primary_label); ?></span>
                                        <i class="bi bi-arrow-right flacso-academic-hero__arrow" aria-hidden="true"></i>
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

                    <?php get_template_part('template-parts/oferta/menciones', null, ['menciones' => $academic['menciones'] ?? []]); ?>

                    <?php if ($reconocido_mec || $reconocimiento_internacional || $reconocimiento_red_flacso || $convenio_iin_oea) : ?>
                        <!-- Reconocimientos y convenio siempre visibles, justo bajo el hero. -->
                        <section class="flacso-academic-trust-strip" aria-label="<?php esc_attr_e('Reconocimientos', 'flacso-uruguay'); ?>">
                            <div class="flacso-academic-trust-strip__inner">
                                <ul class="flacso-academic-trust-strip__list">
                                    <?php if ($reconocido_mec) : ?>
                                        <li class="flacso-academic-trust-strip__item">
                                            <span class="flacso-academic-trust-strip__icon" aria-hidden="true">
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="m12 2 2.5 2 3.2.2 1 3 2.6 2-.8 3.1.8 3.1-2.6 2-1 3-3.2.2L12 22l-2.5-2-3.2-.2-1-3-2.6-2 .8-3.1-.8-3.1 2.6-2 1-3 3.2-.2z"/><path d="m8.5 12 2.4 2.4L15.8 9.5"/></svg>
                                            </span>
                                            <span><?php esc_html_e('Programa con Reconocimiento Oficial del Ministerio de Educación y Cultura (MEC)', 'flacso-uruguay'); ?></span>
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
                                            <span><?php esc_html_e('Título con validez internacional y reconocimiento del Sistema FLACSO', 'flacso-uruguay'); ?></span>
                                        </li>
                                    <?php endif; ?>
                                    <?php if ($convenio_iin_oea) : ?>
                                        <li class="flacso-academic-trust-strip__item">
                                            <span class="flacso-academic-trust-strip__icon" aria-hidden="true">
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="m12 2 8 3v6c0 5-3.5 8.5-8 11-4.5-2.5-8-6-8-11V5z"/><path d="m8 12 3 3 5-6"/></svg>
                                            </span>
                                            <span><?php esc_html_e('Convenio IIN/OEA', 'flacso-uruguay'); ?></span>
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
                                        <div class="kicker"><?php echo esc_html($is_planned_cohort && !$inscripciones_abiertas ? __('Próxima cohorte', 'flacso-uruguay') : __('Convocatoria vigente', 'flacso-uruguay')); ?></div>
                                        <h2><?php echo esc_html($cohorte_nombre_completo); ?></h2>

                                        <div class="cohort" id="flacso-oa-convocatoria">
                                            <div class="cohort__top">
                                                <div>
                                                    <div class="cohort__title"><?php esc_html_e('Información de cursada', 'flacso-uruguay'); ?></div>
                                                    <div style="color:var(--muted);margin-top:4px;font-size:0.9rem;">
                                                        <?php echo esc_html($is_planned_cohort && !$inscripciones_abiertas ? __('Información de la próxima cohorte. Las inscripciones aún no están abiertas.', 'flacso-uruguay') : __('Información y fechas de cursada de la convocatoria vigente.', 'flacso-uruguay')); ?>
                                                    </div>
                                                </div>
                                                <?php if ($inscripciones_abiertas) : ?>
                                                    <span class="badge-open"><i class="bi bi-check-circle-fill me-1" aria-hidden="true"></i><?php esc_html_e('Inscripciones abiertas', 'flacso-uruguay'); ?></span>
                                                <?php else : ?>
                                                    <span class="badge-open" style="background:#edf2f8;color:#173968;border-color:#d5e0ed;"><i class="bi bi-calendar3 me-1" aria-hidden="true"></i><?php esc_html_e('Planificada', 'flacso-uruguay'); ?></span>
                                                <?php endif; ?>
                                            </div>

                                            <div class="cohort__details">
                                                <div class="cohort__row">
                                                    <div class="cohort__label"><?php esc_html_e('Fecha de inicio', 'flacso-uruguay'); ?></div>
                                                    <div class="cohort__value"><?php echo esc_html($cohorte_inicio_fecha); ?></div>
                                                </div>
                                                <div class="cohort__row<?php echo mb_strlen($cohorte_modalidad_titulo, 'UTF-8') > 16 ? ' cohort__row--wide' : ''; ?>">
                                                    <div class="cohort__label"><?php esc_html_e('Modalidad', 'flacso-uruguay'); ?></div>
                                                    <div class="cohort__value"><?php echo esc_html($cohorte_modalidad_titulo); ?></div>
                                                </div>
                                                <?php if ($duracion_text !== '' && $duracion_text !== __('A definir', 'flacso-uruguay')) : ?>
                                                    <div class="cohort__row">
                                                        <div class="cohort__label"><?php esc_html_e('Duración', 'flacso-uruguay'); ?></div>
                                                        <div class="cohort__value"><?php echo esc_html($duracion_text); ?></div>
                                                    </div>
                                                <?php endif; ?>
                                                <?php if ($oferta_creditos !== '') : ?>
                                                    <div class="cohort__row"><div class="cohort__label"><?php esc_html_e('Créditos', 'flacso-uruguay'); ?></div><div class="cohort__value"><?php echo esc_html($oferta_creditos); ?></div></div>
                                                <?php endif; ?>
                                                <?php if ($oferta_horas !== '') : ?>
                                                    <div class="cohort__row"><div class="cohort__label"><?php esc_html_e('Horas de trabajo académico', 'flacso-uruguay'); ?></div><div class="cohort__value"><?php echo esc_html($oferta_horas); ?></div></div>
                                                <?php endif; ?>
                                                <?php if ($cohorte_modalidad_desc !== '') : ?>
                                                    <div class="cohort__row cohort__row--full">
                                                        <div class="cohort__label"><?php esc_html_e('Cursada y encuentros', 'flacso-uruguay'); ?></div>
                                                        <div class="cohort__value cohort__value--prose">
                                                            <?php echo wp_kses_post($cohorte_modalidad_desc); ?>
                                                        </div>
                                                    </div>
                                                <?php endif; ?>
                                            </div>

                                            <?php if ($mostrar_instancias_presenciales || $calendario_doc_url !== '' || $malla_doc_url !== '') : ?>
                                                <div class="cohort__note">
                                                    <?php if ($mostrar_instancias_presenciales) : ?>
                                                        <p class="cohort__note-warning">
                                                            <i class="bi bi-geo-alt-fill me-1" aria-hidden="true"></i>
                                                            <strong><?php esc_html_e('Incluye instancias exclusivamente presenciales.', 'flacso-uruguay'); ?></strong>
                                                        </p>
                                                    <?php endif; ?>
                                                    <?php if ($calendario_y_malla_compartidos) : ?>
                                                        <p class="mb-0 mt-2">
                                                            <a href="<?php echo esc_url($calendario_doc_url); ?>" target="_blank" rel="noopener noreferrer" class="flacso-meta-doc-link flacso-meta-doc-link--calendario flacso-meta-doc-link--malla" data-flacso-meta-doc-section="CalendarioAcademicoSectionView" data-flacso-meta-doc-also="MallaCurricularSectionView" style="color:var(--azul);font-weight:750;text-decoration:none;">
                                                                <i class="bi bi-file-earmark-pdf text-danger me-1" aria-hidden="true"></i>
                                                                <?php esc_html_e('Descargar calendario y malla académica (PDF)', 'flacso-uruguay'); ?>
                                                            </a>
                                                        </p>
                                                    <?php else : ?>
                                                        <?php if ($malla_doc_url !== '') : ?>
                                                            <p class="mb-0 mt-2"><a href="<?php echo esc_url($malla_doc_url); ?>" target="_blank" rel="noopener noreferrer" class="flacso-meta-doc-link flacso-meta-doc-link--malla" data-flacso-meta-doc-section="MallaCurricularSectionView" style="color:var(--azul);font-weight:750;text-decoration:none;"><i class="bi bi-file-earmark-pdf text-danger me-1" aria-hidden="true"></i><?php esc_html_e('Descargar malla curricular', 'flacso-uruguay'); ?></a></p>
                                                        <?php endif; ?>
                                                        <?php if ($calendario_doc_url !== '') : ?>
                                                            <p class="mb-0 mt-2">
                                                                <a href="<?php echo esc_url($calendario_doc_url); ?>" target="_blank" rel="noopener noreferrer" class="flacso-meta-doc-link flacso-meta-doc-link--calendario" data-flacso-meta-doc-section="CalendarioAcademicoSectionView" style="color:var(--azul);font-weight:750;text-decoration:none;">
                                                                    <i class="bi bi-file-earmark-pdf text-danger me-1" aria-hidden="true"></i>
                                                                    <?php esc_html_e('Descargar Calendario Académico (PDF)', 'flacso-uruguay'); ?>
                                                                </a>
                                                            </p>
                                                        <?php endif; ?>
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
                                                <span class="flacso-academic-accordion__label"><?php esc_html_e('Requisitos de egreso', 'flacso-uruguay'); ?></span>
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

                                <!-- FINAL CTA -->
                                <?php if ($has_preinscription_cta) : ?>
                                    <section class="final-cta">
                                        <div class="final-cta__text">
                                            <h3><?php esc_html_e('¿Querés postularte a la próxima cohorte?', 'flacso-uruguay'); ?></h3>
                                        </div>
                                        <a class="btn btn--primary" href="<?php echo esc_url($preinscripcion_url); ?>" data-flacso-preinscription-cta="1">
                                            <?php esc_html_e('Iniciar preinscripción', 'flacso-uruguay'); ?> <i class="bi bi-arrow-right" aria-hidden="true"></i>
                                        </a>
                                    </section>
                                <?php endif; ?>

                            </div>

                            <!-- SIDEBAR -->
                            <aside id="flacso-oa-consulta" class="sidebar flacso-mobile-info-sheet" aria-label="<?php esc_attr_e('Formulario de consulta', 'flacso-uruguay'); ?>">
                                <!-- Cabecera móvil del panel lateral; se reutiliza el único formulario. -->
                                <header class="flacso-mobile-info-sheet__header">
                                    <h2 id="flacso-mobile-info-sheet-title"><?php esc_html_e('Más Información', 'flacso-uruguay'); ?></h2>
                                    <button type="button" class="flacso-mobile-info-sheet__close" aria-label="<?php esc_attr_e('Cerrar solicitud de información', 'flacso-uruguay'); ?>">
                                        <i class="bi bi-x-lg" aria-hidden="true"></i>
                                    </button>
                                </header>
                                <div class="contact">
                                    <div class="contact__head">
                                        <h3><?php esc_html_e('Más Información', 'flacso-uruguay'); ?></h3>
                                    </div>
                                    <div class="contact__body">
                                        <?php
                                        if (function_exists('flacso_consultas_render_form')) {
                                            echo flacso_consultas_render_form(['mostrar_preinscripcion' => false, 'post_id' => $post_id]);
                                        }
                                        ?>



                                    </div>
                                </div>

                                <div class="contact-support">
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
                    $coordination_groups = [];
                    $academic_team_groups = [];

                    // Coordinación académica de la Oferta: mismo orden de integrantes que en el editor.
                    $equipos = $academic['equipos'] ?? [];
                    $oferta_coordinacion = [];
                    if (!empty($equipos) && is_array($equipos)) {
                        foreach ($equipos as $eq_index => $eq) {
                            if (($eq['tipo'] ?? '') === 'coordinacion_academica') {
                                if (!empty($eq['docentes']) && is_array($eq['docentes'])) {
                                    $oferta_coordinacion[] = $eq;
                                }
                                unset($equipos[$eq_index]);
                            }
                        }
                    }
                    foreach ($oferta_coordinacion as $eq) {
                        $coordination_groups[] = [
                            'nombre'      => __('Coordinación académica', 'flacso-uruguay'),
                            'descripcion' => (string) ($eq['descripcion'] ?? ''),
                            'docentes'    => $eq['docentes'],
                            'importancia' => '1',
                        ];
                    }

                    $coordinacion_academica = $oferta_coordinacion ? [] : ($academic['coordinacion_academica'] ?? []);
                    if (!empty($coordinacion_academica) && is_array($coordinacion_academica)) {
                        $coordination_people = [];
                        foreach ($coordinacion_academica as $coord) {
                            if (!empty($coord['docentes']) && is_array($coord['docentes'])) {
                                foreach ($coord['docentes'] as $docente_item) {
                                    $person = is_array($docente_item) ? $docente_item : ['id' => $docente_item];
                                    if (empty($person['rol'])) {
                                        $person['rol'] = (string) ($coord['rol'] ?? '');
                                    }
                                    $coordination_people[] = $person;
                                }
                            }
                        }
                        if ($coordination_people) {
                            $coordination_groups[] = [
                                'nombre'      => __('Coordinación académica', 'flacso-uruguay'),
                                'descripcion' => '',
                                'docentes'    => $coordination_people,
                                'importancia' => '1',
                            ];
                        }
                    }

                    $prog_coord = $program['coordinacion'] ?? [];
                    if (empty($coordination_groups) && !empty($prog_coord) && is_array($prog_coord)) {
                        $coordination_groups[] = [
                            'nombre'      => __('Coordinación académica', 'flacso-uruguay'),
                            'descripcion' => '',
                            'docentes'    => array_filter(array_map(static function ($m) {
                                return !empty($m['docente_id']) ? ['id' => $m['docente_id'], 'rol' => $m['rol'] ?? ''] : null;
                            }, $prog_coord)),
                            'importancia' => '1',
                        ];
                    }

                    if (!empty($equipos) && is_array($equipos)) {
                        foreach ($equipos as $eq) {
                            if (!empty($eq['docentes']) && is_array($eq['docentes'])) {
                                $academic_team_groups[] = [
                                    'nombre'      => $eq['nombre'] ?? '',
                                    'descripcion' => $eq['descripcion'] ?? '',
                                    'docentes'    => $eq['docentes'],
                                    'importancia' => strval($eq['importancia'] ?? '3'),
                                ];
                            }
                        }
                    }
                    ?>

                    <?php if (!empty($coordination_groups) || !empty($academic_team_groups)) : ?>
                        <?php foreach ([
                            ['groups' => $coordination_groups, 'title' => __('Coordinación académica', 'flacso-uruguay'), 'id' => 'flacso-academic-coordination-title', 'class' => 'flacso-academic-team--coordination'],
                            ['groups' => $academic_team_groups, 'title' => __('Equipo académico', 'flacso-uruguay'), 'id' => 'flacso-academic-team-title', 'class' => 'flacso-academic-team--faculty'],
                        ] as $team_section) : ?>
                        <?php if (empty($team_section['groups'])) { continue; } ?>
                        <section class="flacso-academic-team flacso-oa-team-section <?php echo esc_attr($team_section['class']); ?>" aria-labelledby="<?php echo esc_attr($team_section['id']); ?>">
                            <div class="flacso-oa-container flacso-academic-team__container">
                                <header class="flacso-academic-team__header">
                                    <h2 id="<?php echo esc_attr($team_section['id']); ?>"><?php echo esc_html($team_section['title']); ?></h2>
                                </header>

                                <div class="flacso-academic-team__groups">
                                    <?php
                                    // El identificador se obtiene de la cohorte publicada que alimenta esta página.
                                    $cohorte_equipo_label = absint($instance['id'] ?? 0) > 0
                                        ? trim((string) ($instance['label'] ?? ''))
                                        : '';
                                    ?>
                                    <?php foreach ($team_section['groups'] as $grupo) : ?>
                                        <?php
                                        $grupo_nombre = trim((string) ($grupo['nombre'] ?? ''));
                                        if ($grupo_nombre !== '' && ($grupo['alcance'] ?? '') === 'cohorte'
                                            && $cohorte_equipo_label !== ''
                                            && stripos($grupo_nombre, $cohorte_equipo_label) === false) {
                                            $grupo_nombre = sprintf(
                                                __('%1$s - %2$s', 'flacso-uruguay'),
                                                $grupo_nombre,
                                                $cohorte_equipo_label
                                            );
                                        }
                                        $nivel = in_array((string) ($grupo['importancia'] ?? '3'), ['1', '2', '3'], true)
                                            ? (string) ($grupo['importancia'] ?? '3')
                                            : '3';
                                        $docentes_grupo = array_values((array) ($grupo['docentes'] ?? []));
                                        if (!$docentes_grupo) {
                                            continue;
                                        }
                                        ?>
                                        <div class="flacso-academic-team__group flacso-academic-team__group--level-<?php echo esc_attr($nivel); ?>">
                                            <?php if ($grupo_nombre !== '' && $team_section['class'] !== 'flacso-academic-team--coordination') : ?>
                                                <h3 class="flacso-academic-team__group-title"><?php echo esc_html($grupo_nombre); ?></h3>
                                            <?php endif; ?>
                                            <?php if (!empty($grupo['descripcion'])) : ?>
                                                <div class="flacso-academic-team__group-description"><?php echo wp_kses_post(wpautop($grupo['descripcion'])); ?></div>
                                            <?php endif; ?>
                                            <div class="flacso-academic-team__grid">
                                                <?php foreach ($docentes_grupo as $docente_item) : ?>
                                                    <?php
                                                    $docente_id = is_array($docente_item) ? (int) ($docente_item['id'] ?? 0) : (int) $docente_item;
                                                    $rol_especifico = is_array($docente_item) ? (string) ($docente_item['rol'] ?? '') : '';
                                                    if ($team_section['class'] === 'flacso-academic-team--coordination') {
                                                        $render_coordinador($docente_id, $rol_especifico);
                                                        continue;
                                                    }
                                                    $render_docente_item($docente_id, $rol_especifico, $nivel);
                                                    ?>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </section>
                        <?php endforeach; ?>
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
                            <svg class="flacso-mobile-actions__info-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" width="18" height="18"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                            <?php esc_html_e('Más Información', 'flacso-uruguay'); ?>
                        </button>
                        <?php if ($has_preinscription_cta) : ?>
                            <a class="flacso-mobile-actions__register" href="<?php echo esc_url($preinscripcion_url); ?>" data-flacso-preinscription-cta="1">
                                <?php esc_html_e('Preinscribirme', 'flacso-uruguay'); ?>
                                <span aria-hidden="true">→</span>
                            </a>
                        <?php endif; ?>
                    </nav>

                </article>
            </<?php echo flacso_child_main_tag(); ?>>
        </div>
    </div>
</div>
