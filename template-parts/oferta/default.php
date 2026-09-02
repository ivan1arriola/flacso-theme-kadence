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

$hero_quick_items = [];
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
$hero_quick_items[] = [
    'icon'  => 'bi-calendar3',
    'label' => __('Próximo inicio', 'flacso-uruguay'),
    'value' => $fechas_hero,
];

$duracion_text = $duracion_meses_val !== '' && function_exists('flacso_theme_format_duration_months')
    ? flacso_theme_format_duration_months($duracion_meses_val)
    : '';
if ($duracion_text === '' || $duracion_text === '0 meses' || (is_numeric($duracion_meses_val) && (int) $duracion_meses_val <= 0)) {
    $duracion_text = __('A definir', 'flacso-uruguay');
}
$hero_quick_items[] = [
    'icon'  => 'bi-clock',
    'label' => __('Duración', 'flacso-uruguay'),
    'value' => $duracion_text,
];

$modalidad_resumen = trim((string) ($academic['modalidad_resumen'] ?? ''));
if ($modalidad_resumen === '') {
    $modalidad_resumen = trim((string) ($instance['modalidad'] ?? ''));
}
if ($modalidad_resumen === '') {
    $modalidad_resumen = __('A definir', 'flacso-uruguay');
}
$hero_quick_items[] = [
    'icon'  => 'bi-camera-video',
    'label' => __('Modalidad', 'flacso-uruguay'),
    'value' => $modalidad_resumen,
];

$hero_badge_text                 = trim((string) ($academic['carta_hero_etiqueta'] ?? ''));
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
$cohorte_periodo_inscripcion = '';
if (!empty($registration['hasta'])) {
    $fecha_hasta_fmt = function_exists('flacso_theme_format_date_es')
        ? flacso_theme_format_date_es((string) $registration['hasta'])
        : (string) $registration['hasta'];
    $cohorte_periodo_inscripcion = sprintf(__('Postulaciones hasta el %s', 'flacso-uruguay'), $fecha_hasta_fmt);
}

$cohorte_modalidad_titulo = !empty($instance['modalidad']) ? (string) $instance['modalidad'] : $modalidad_resumen;
$cohorte_modalidad_desc   = !empty($instance['modalidad_descripcion']) ? (string) $instance['modalidad_descripcion'] : '';

$calendario_pdf_url = !empty($academic['calendario_pdf_url']) ? trim((string) $academic['calendario_pdf_url']) : '';
$cartamalla_pdf_url = !empty($academic['cartamalla_pdf_url']) ? trim((string) $academic['cartamalla_pdf_url']) : '';
$calendario_doc_url = $calendario_pdf_url ?: $cartamalla_pdf_url;
if ($calendario_doc_url && function_exists('flacso_get_pdf_proxy_url')) {
    $proxied = flacso_get_pdf_proxy_url($calendario_doc_url, 'Calendario Académico');
    if ($proxied) $calendario_doc_url = $proxied;
}

$malla_pdf_url = !empty($academic['malla_pdf_url']) ? trim((string) $academic['malla_pdf_url']) : '';
$malla_doc_url = $malla_pdf_url ?: $cartamalla_pdf_url;
if ($malla_doc_url && function_exists('flacso_get_pdf_proxy_url')) {
    $proxied = flacso_get_pdf_proxy_url($malla_doc_url, 'Malla Curricular');
    if ($proxied) $malla_doc_url = $proxied;
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
$convenio_iin_oea             = !empty($academic['convenio_iin_oea']);
$has_aside_badges             = $reconocido_mec || $reconocimiento_internacional || $convenio_iin_oea;
$has_aside_docs               = ($malla_doc_url !== '' || $calendario_doc_url !== '');

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

    $nivel = in_array((string) $nivel, ['1', '2', '3'], true) ? (string) $nivel : '3';

    $nombre     = trim((string) get_post_meta($docente_id, 'nombre', true));
    $apellido   = trim((string) get_post_meta($docente_id, 'apellido', true));
    $pref_abrev = trim((string) get_post_meta($docente_id, 'prefijo_abrev', true));
    $tit_acad   = trim((string) get_post_meta($docente_id, 'titulo_academico', true));
    $cargo_meta = trim((string) get_post_meta($docente_id, 'cargo', true));

    $doc_titulo = function_exists('dp_nombre_completo')
        ? dp_nombre_completo($docente_id, false)
        : get_the_title($docente_id);

    if ($pref_abrev && stripos($doc_titulo, $pref_abrev) === false) {
        $doc_titulo = $pref_abrev . ' ' . $doc_titulo;
    }

    $academic_label = $tit_acad ? $tit_acad : ($cargo_meta ?: $pref_abrev);
    if ($academic_label !== '' && stripos($doc_titulo, $academic_label) !== false) {
        $academic_label = '';
    }

    $cv_raw = (string) get_post_meta($docente_id, 'cv', true);
    $has_cv = trim(wp_strip_all_tags($cv_raw)) !== '';

    $avatar_size = [
        '1' => 260,
        '2' => 120,
        '3' => 88,
    ][$nivel];

    $avatar_class = $nivel === '1'
        ? 'flacso-oa-person-card__avatar'
        : 'flacso-oa-person-card__avatar-round';

    $card_classes = sprintf(
        'flacso-oa-person-card flacso-oa-person-card--nivel-%s%s',
        esc_attr($nivel),
        $has_cv ? ' flacso-oa-person-card--has-cv' : ' flacso-oa-person-card--no-cv'
    );

    $render_avatar_html = static function() use ($docente_id, $doc_titulo, $avatar_size, $avatar_class, $nombre, $apellido) {
        if (function_exists('dp_avatar_markup')) {
            $markup = dp_avatar_markup($docente_id, $doc_titulo, $avatar_size, $avatar_class);
            if (!empty($markup)) {
                return $markup;
            }
        }
        if (has_post_thumbnail($docente_id)) {
            return get_the_post_thumbnail($docente_id, $avatar_size > 150 ? 'medium_large' : 'thumbnail', [
                'class'   => $avatar_class,
                'alt'     => esc_attr($doc_titulo),
                'loading' => 'lazy',
            ]);
        }
        $initials = '';
        if ($nombre || $apellido) {
            $initials = mb_substr($nombre, 0, 1) . mb_substr($apellido, 0, 1);
        } elseif ($doc_titulo) {
            $initials = mb_substr($doc_titulo, 0, 2);
        }
        $initials = strtoupper($initials ?: 'FL');
        $colors = ['#1d3a72', '#0070ba', '#2c5282', '#1a365d', '#0f2942'];
        $bg_color = $colors[abs(crc32($doc_titulo ?: (string) $docente_id)) % count($colors)];

        return sprintf(
            '<div class="%s flacso-docente-card__initials" style="width:100%%;height:100%%;display:flex;align-items:center;justify-content:center;background:%s;color:#fff;font-weight:800;font-size:%dpx;border-radius:inherit;">%s</div>',
            esc_attr($avatar_class),
            esc_attr($bg_color),
            max(14, (int) ($avatar_size * 0.35)),
            esc_html($initials)
        );
    };

    echo '<div class="flacso-oa-docente-item flacso-oa-docente-item--nivel-' . esc_attr($nivel) . '">';
    echo '<article class="' . esc_attr($card_classes) . '" aria-label="' . esc_attr(sprintf(__('Docente: %s', 'flacso-uruguay'), $doc_titulo)) . '">';

    if ($nivel === '1') {
        echo '<div class="flacso-oa-person-card__media">';
        echo '<div class="flacso-oa-person-card__avatar-wrap">';
        echo $render_avatar_html(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        echo '</div>';
        echo '</div>';

        echo '<div class="flacso-oa-person-card__content">';
        if ($rol) {
            echo '<div class="flacso-oa-person-card__meta-row">';
            echo '<span class="flacso-oa-person-card__role-pill">' . esc_html($rol) . '</span>';
            echo '</div>';
        }

        echo '<h4 class="flacso-oa-person-card__name">' . esc_html($doc_titulo) . '</h4>';

        if ($academic_label) {
            echo '<p class="flacso-oa-person-card__academic">' . esc_html($academic_label) . '</p>';
        }

        if ($has_cv) {
            echo '<div class="flacso-oa-person-card__cv">' . wp_kses_post(wpautop($cv_raw)) . '</div>';
        }

        echo '</div>';
    } else {
        echo '<div class="flacso-oa-person-card__header">';
        echo '<div class="flacso-oa-person-card__avatar-circle">';
        echo $render_avatar_html(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        echo '</div>';

        echo '<div class="flacso-oa-person-card__header-info">';
        if ($rol) {
            echo '<span class="flacso-oa-person-card__role-pill">' . esc_html($rol) . '</span>';
        }
        echo '<h4 class="flacso-oa-person-card__name">' . esc_html($doc_titulo) . '</h4>';
        if ($academic_label) {
            echo '<p class="flacso-oa-person-card__academic">' . esc_html($academic_label) . '</p>';
        }
        echo '</div>';
        echo '</div>';

        if ($nivel === '2' && $has_cv) {
            echo '<div class="flacso-oa-person-card__content">';
            echo '<div class="flacso-oa-person-card__cv">' . wp_kses_post(wpautop($cv_raw)) . '</div>';
            echo '</div>';
        }
    }

    echo '</article>';
    echo '</div>';
};

?>

<div id="inner-wrap" class="wrap kt-clear flacso-oferta-responsive">
    <div id="primary" class="content-area">
        <div class="content-container">
            <main id="main" class="site-main">
                <article id="post-<?php echo esc_attr($post_id); ?>" <?php post_class('entry content-bg single-entry flacso-oa-article'); ?>>

                    <div class="flacso-oa-container flacso-oa-single-hero">
                        <section class="flacso-oa-program-hero" aria-labelledby="flacso-oa-program-title">
                            <div class="flacso-oa-program-hero__media-wrap">
                                <div class="flacso-oa-program-hero__media">
                                    <?php if (!empty($thumbnail_url)) : ?>
                                        <img
                                            src="<?php echo esc_url($thumbnail_url); ?>"
                                            alt="<?php echo esc_attr($title); ?>"
                                            loading="eager"
                                            fetchpriority="high"
                                            onerror="this.style.display='none'; if(this.nextElementSibling) this.nextElementSibling.style.display='flex';"
                                        >
                                        <div class="flacso-oa-program-hero__placeholder" style="display:none;">
                                            <i class="bi bi-mortarboard" aria-hidden="true"></i>
                                            <span><?php esc_html_e('FLACSO Uruguay', 'flacso-uruguay'); ?></span>
                                        </div>
                                    <?php else : ?>
                                        <div class="flacso-oa-program-hero__placeholder">
                                            <i class="bi bi-mortarboard" aria-hidden="true"></i>
                                            <span><?php esc_html_e('FLACSO Uruguay', 'flacso-uruguay'); ?></span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="flacso-oa-program-hero__content">
                                <div class="flacso-oa-program-hero__badges">
                                    <?php if ($tipo_oferta !== '') : ?>
                                        <p class="flacso-oa-program-hero__kicker"><?php echo esc_html($tipo_oferta); ?></p>
                                    <?php endif; ?>
                                    <?php if ($inscripciones_abiertas) : ?>
                                        <span class="flacso-oa-program-hero__status-badge flacso-oa-program-hero__status-badge--open">
                                            <i class="bi bi-check-circle-fill" aria-hidden="true"></i>
                                            <?php esc_html_e('Inscripciones abiertas', 'flacso-uruguay'); ?>
                                        </span>
                                    <?php else : ?>
                                        <span class="flacso-oa-program-hero__status-badge flacso-oa-program-hero__status-badge--soon">
                                            <i class="bi bi-clock" aria-hidden="true"></i>
                                            <?php esc_html_e('Próxima apertura', 'flacso-uruguay'); ?>
                                        </span>
                                    <?php endif; ?>
                                    <?php if ($hero_badge_text !== '') : ?>
                                        <span class="flacso-oa-program-hero__custom-badge"><?php echo esc_html($hero_badge_text); ?></span>
                                    <?php endif; ?>
                                </div>

                                <h1 id="flacso-oa-program-title" class="flacso-oa-program-hero__title"><?php echo esc_html($title); ?></h1>

                                <?php if (has_excerpt($post_id)) : ?>
                                    <div class="flacso-oa-program-hero__excerpt"><?php echo esc_html(get_the_excerpt($post_id)); ?></div>
                                <?php endif; ?>

                                <?php if ($mostrar_instancias_presenciales) : ?>
                                    <p class="flacso-oa-program-hero__notice">
                                        <i class="bi bi-geo-alt-fill" aria-hidden="true"></i>
                                        <?php esc_html_e('Con instancias presenciales', 'flacso-uruguay'); ?>
                                    </p>
                                <?php endif; ?>

                                <div class="flacso-oa-program-hero__actions">
                                    <a href="<?php echo esc_url($hero_primary_url); ?>"<?php echo $has_preinscription_cta ? ' data-flacso-preinscription-cta="1"' : ''; ?> class="flacso-oa-program-hero__button flacso-oa-program-hero__button--primary">
                                        <?php echo esc_html($hero_primary_label); ?>
                                        <i class="bi bi-arrow-right" aria-hidden="true"></i>
                                    </a>
                                    <a href="#programa" class="flacso-oa-program-hero__button flacso-oa-program-hero__button--secondary">
                                        <?php esc_html_e('Ver propuesta académica', 'flacso-uruguay'); ?>
                                    </a>
                                </div>
                            </div>
                        </section>
                    </div>

                    <!-- ========================================== -->
                    <!-- FRANJA HORIZONTAL: 4 DATOS CLAVE -->
                    <!-- ========================================== -->
                    <div class="flacso-oa-container facts-wrap" aria-label="<?php esc_attr_e('Información principal', 'flacso-uruguay'); ?>">
                        <div class="facts">
                            <div class="fact">
                                <div class="fact__label"><?php esc_html_e('Próximo inicio', 'flacso-uruguay'); ?></div>
                                <div class="fact__value"><?php echo esc_html($fechas_hero); ?></div>
                            </div>
                            <div class="fact">
                                <div class="fact__label"><?php esc_html_e('Duración', 'flacso-uruguay'); ?></div>
                                <div class="fact__value"><?php echo esc_html($duracion_text); ?></div>
                            </div>
                            <div class="fact">
                                <div class="fact__label"><?php esc_html_e('Modalidad', 'flacso-uruguay'); ?></div>
                                <div class="fact__value"><?php echo esc_html($modalidad_resumen); ?></div>
                            </div>
                            <div class="fact">
                                <div class="fact__label"><?php esc_html_e('Edición', 'flacso-uruguay'); ?></div>
                                <div class="fact__value"><?php echo esc_html($cohorte_label ?: sprintf(__('Cohorte %s', 'flacso-uruguay'), $inscripciones_year)); ?></div>
                            </div>
                        </div>
                    </div>

                    <!-- ========================================== -->
                    <!-- CONTENIDO PRINCIPAL + SIDEBAR DE CONTACTO -->
                    <!-- ========================================== -->
                    <section class="main" id="flacso-oa-contenido">
                        <div class="flacso-oa-container layout">

                            <div class="content">

                                <!-- SECCIÓN: CONVOCATORIA VIGENTE (UNA SOLA PIEZA, SIN CAMPUS INVENTADO) -->
                                <?php if ($cohorte_label !== '' || $cohorte_inicio_fecha !== '') : ?>
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
                                                <?php if ($cohorte_modalidad_desc !== '') : ?>
                                                    <div class="cohort__row" style="grid-column: 1 / -1; padding-left: 0; border-left: 0;">
                                                        <div class="cohort__label"><?php esc_html_e('Cursada y encuentros', 'flacso-uruguay'); ?></div>
                                                        <div class="cohort__value" style="font-weight: normal; font-size: 0.95rem;">
                                                            <?php echo wp_kses_post($cohorte_modalidad_desc); ?>
                                                        </div>
                                                    </div>
                                                <?php endif; ?>
                                            </div>

                                            <?php if ($mostrar_instancias_presenciales || $calendario_doc_url !== '' || $cohorte_periodo_inscripcion !== '') : ?>
                                                <div class="cohort__note">
                                                    <?php if ($mostrar_instancias_presenciales) : ?>
                                                        <p class="mb-1" style="color: #b45309; font-weight: 700;">
                                                            <i class="bi bi-geo-alt-fill me-1" aria-hidden="true"></i>
                                                            <?php esc_html_e('Esta propuesta incluye instancias presenciales programadas.', 'flacso-uruguay'); ?>
                                                        </p>
                                                    <?php endif; ?>
                                                    <?php if ($cohorte_periodo_inscripcion !== '') : ?>
                                                        <p class="mb-1">
                                                            <i class="bi bi-calendar-check me-1" aria-hidden="true"></i>
                                                            <?php echo esc_html($cohorte_periodo_inscripcion); ?>.
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
                                <section class="section" id="programa">
                                    <div class="kicker"><?php esc_html_e('Propuesta académica', 'flacso-uruguay'); ?></div>
                                    <h2><?php echo esc_html($title); ?></h2>

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

                                    <?php
                                    $objetivo_text = trim((string) ($academic['objetivo_general'] ?? ''));
                                    if ($objetivo_text === '' && !empty($academic['objetivos_html'])) {
                                        $objetivo_text = trim((string) $academic['objetivos_html']);
                                    }
                                    ?>
                                    <?php if ($objetivo_text !== '') : ?>
                                        <div class="objectives">
                                            <h3><?php esc_html_e('Objetivo general', 'flacso-uruguay'); ?></h3>
                                            <div style="margin:10px 0 0">
                                                <?php echo wpautop(wp_kses_post($objetivo_text)); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                                            </div>
                                        </div>
                                    <?php endif; ?>

                                    <!-- ACORDEÓN DE DETALLES ACADÉMICOS -->
                                    <div class="details">

                                        <!-- 1. PERFIL DE INGRESO -->
                                        <details open>
                                            <summary><?php esc_html_e('Perfil de ingreso', 'flacso-uruguay'); ?></summary>
                                            <div class="detail-body">
                                                <?php if (!empty($academic['perfil_ingreso_html'])) : ?>
                                                    <?php echo wpautop(wp_kses_post($academic['perfil_ingreso_html'])); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                                                <?php else : ?>
                                                    <p class="flacso-oa-empty-field"><i class="bi bi-info-circle" aria-hidden="true"></i> <?php esc_html_e('Información sobre perfil de ingreso no disponible por el momento.', 'flacso-uruguay'); ?></p>
                                                <?php endif; ?>
                                            </div>
                                        </details>

                                        <!-- 2. REQUISITOS DE INGRESO -->
                                        <details>
                                            <summary><?php esc_html_e('Requisitos de ingreso', 'flacso-uruguay'); ?></summary>
                                            <div class="detail-body">
                                                <?php if (!empty($academic['requisitos_ingreso_html'])) : ?>
                                                    <?php echo wpautop(wp_kses_post($academic['requisitos_ingreso_html'])); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                                                <?php else : ?>
                                                    <p class="flacso-oa-empty-field"><i class="bi bi-info-circle" aria-hidden="true"></i> <?php esc_html_e('Información sobre requisitos de ingreso no disponible por el momento.', 'flacso-uruguay'); ?></p>
                                                <?php endif; ?>
                                            </div>
                                        </details>

                                        <!-- 3. MALLA CURRICULAR -->
                                        <details>
                                            <summary><?php esc_html_e('Malla Curricular', 'flacso-uruguay'); ?></summary>
                                            <div class="detail-body">
                                                <?php if (!empty($academic['forma_aprobacion'])) : ?>
                                                    <p><?php echo esc_html($academic['forma_aprobacion']); ?></p>
                                                <?php endif; ?>
                                                <?php if ($malla_doc_url !== '') : ?>
                                                    <p style="margin-bottom:1rem">
                                                        <?php esc_html_e('Descargá el documento curricular oficial con el plan de estudios, materias y créditos.', 'flacso-uruguay'); ?>
                                                    </p>
                                                    <a href="<?php echo esc_url($malla_doc_url); ?>" target="_blank" rel="noopener noreferrer" class="flacso-oa-link-btn flacso-meta-doc-link flacso-meta-doc-link--malla" data-flacso-meta-doc-section="MallaCurricularSectionView" data-flacso-meta-doc-layout="single_malla" style="display:inline-flex;align-items:center;gap:0.5rem;background:var(--azul);color:#fff;padding:8px 18px;border-radius:999px;text-decoration:none;font-weight:700;font-size:0.9rem;">
                                                        <i class="bi bi-file-earmark-pdf" aria-hidden="true"></i>
                                                        <?php esc_html_e('Descargar Malla Curricular (PDF)', 'flacso-uruguay'); ?>
                                                    </a>
                                                <?php elseif (empty($academic['forma_aprobacion'])) : ?>
                                                    <p class="flacso-oa-empty-field"><i class="bi bi-info-circle" aria-hidden="true"></i> <?php esc_html_e('Documentación de malla curricular no disponible para descarga por el momento.', 'flacso-uruguay'); ?></p>
                                                <?php endif; ?>
                                            </div>
                                        </details>

                                        <!-- 4. PERFIL DE EGRESO -->
                                        <details>
                                            <summary><?php esc_html_e('Perfil de egreso', 'flacso-uruguay'); ?></summary>
                                            <div class="detail-body">
                                                <?php if (!empty($academic['perfil_egreso_html'])) : ?>
                                                    <?php echo wpautop(wp_kses_post($academic['perfil_egreso_html'])); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                                                <?php else : ?>
                                                    <p class="flacso-oa-empty-field"><i class="bi bi-info-circle" aria-hidden="true"></i> <?php esc_html_e('Información sobre perfil de egreso no disponible por el momento.', 'flacso-uruguay'); ?></p>
                                                <?php endif; ?>
                                            </div>
                                        </details>

                                        <!-- 5. REQUISITOS DE EGRESO Y TITULACIÓN -->
                                        <details id="titulacion">
                                            <summary><?php esc_html_e('Requisitos de egreso y titulación', 'flacso-uruguay'); ?></summary>
                                            <div class="detail-body">
                                                <?php if (!empty($academic['requisitos_egreso_html'])) : ?>
                                                    <?php echo wpautop(wp_kses_post($academic['requisitos_egreso_html'])); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                                                <?php else : ?>
                                                    <p class="flacso-oa-empty-field"><i class="bi bi-info-circle" aria-hidden="true"></i> <?php esc_html_e('Información sobre requisitos de egreso no disponible por el momento.', 'flacso-uruguay'); ?></p>
                                                <?php endif; ?>
                                            </div>
                                        </details>

                                        <!-- 6. TÍTULO Y CERTIFICACIÓN -->
                                        <details>
                                            <summary><?php esc_html_e('Título y certificación', 'flacso-uruguay'); ?></summary>
                                            <div class="detail-body">
                                                <?php
                                                $titulos_body = '';
                                                if (!empty($academic['titulos_certificaciones_html'])) {
                                                    $titulos_body .= $academic['titulos_certificaciones_html'];
                                                }
                                                if (!empty($academic['reconocido_mec'])) {
                                                    $titulos_body .= '<p class="mt-2 text-primary fw-semibold"><i class="bi bi-patch-check-fill me-1" aria-hidden="true"></i> ' . esc_html__('Programa con Reconocimiento Oficial del Ministerio de Educación y Cultura (MEC).', 'flacso-uruguay') . '</p>';
                                                }
                                                if (!empty($academic['reconocimiento_internacional'])) {
                                                    $titulos_body .= '<p class="mt-2"><i class="bi bi-globe me-1" aria-hidden="true"></i> ' . esc_html__('Título con validez y reconocimiento en la red del Sistema Internacional FLACSO.', 'flacso-uruguay') . '</p>';
                                                }
                                                if (!empty($academic['convenio_iin_oea'])) {
                                                    $titulos_body .= '<p class="mt-2"><i class="bi bi-shield-check me-1" aria-hidden="true"></i> ' . esc_html__('Convenio con el Instituto Interamericano del Niño, la Niña y Adolescentes (IIN - OEA).', 'flacso-uruguay') . '</p>';
                                                }
                                                ?>
                                                <?php if ($titulos_body !== '') : ?>
                                                    <?php echo wp_kses_post($titulos_body); ?>
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
                                            <p><?php esc_html_e('La preinscripción se realiza en la plataforma de FLACSO Uruguay.', 'flacso-uruguay'); ?></p>
                                        </div>
                                        <a class="btn btn--primary" href="<?php echo esc_url($preinscripcion_url); ?>" data-flacso-preinscription-cta="1" style="background:var(--azul) !important;color:#fff !important;white-space:nowrap;" onclick="if(typeof window.flacsoMetaTrack === 'function'){ window.flacsoMetaTrack('InitiateCheckout', { content_name: <?php echo wp_json_encode($title); ?>, content_category: 'oferta_academica' }); }">
                                            <?php esc_html_e('Iniciar preinscripción →', 'flacso-uruguay'); ?>
                                        </a>
                                    </section>
                                <?php endif; ?>

                            </div>

                            <!-- SIDEBAR -->
                            <aside id="flacso-oa-consulta" class="sidebar" aria-label="<?php esc_attr_e('Formulario de consulta', 'flacso-uruguay'); ?>">
                                <div class="contact">
                                    <div class="contact__head">
                                        <h3><?php esc_html_e('Solicitá información', 'flacso-uruguay'); ?></h3>
                                        <p><?php esc_html_e('Recibí orientación sobre cursada, requisitos e inscripción.', 'flacso-uruguay'); ?></p>
                                    </div>
                                    <div class="contact__body">
                                        <?php
                                        if (function_exists('flacso_consultas_render_form')) {
                                            echo flacso_consultas_render_form(['mostrar_preinscripcion' => false]);
                                        }
                                        ?>

                                        <?php if ($has_preinscription_cta) : ?>
                                            <div style="margin-top: 1rem;">
                                                <a href="<?php echo esc_url($preinscripcion_url); ?>" data-flacso-preinscription-cta="1" class="btn btn--primary" style="width: 100%; background: var(--verde) !important; color: #fff !important; border-radius: 999px; padding: 12px 20px; font-weight: 800; font-size: 1rem;" onclick="if(typeof window.flacsoMetaTrack === 'function'){ window.flacsoMetaTrack('InitiateCheckout', { content_name: <?php echo wp_json_encode($title); ?>, content_category: 'oferta_academica' }); }">
                                                    <i class="bi bi-stars me-2"></i>
                                                    <?php printf(esc_html__('Preinscripción %s →', 'flacso-uruguay'), esc_html($inscripciones_year)); ?>
                                                </a>
                                            </div>
                                        <?php endif; ?>

                                        <div class="contact__links">
                                            <a href="#programa"><i class="bi bi-arrow-down-short"></i> <?php esc_html_e('Ver propuesta académica', 'flacso-uruguay'); ?></a>
                                            <a href="#cohorte"><i class="bi bi-arrow-down-short"></i> <?php esc_html_e('Información de la cohorte', 'flacso-uruguay'); ?></a>
                                            <a href="#titulacion"><i class="bi bi-arrow-down-short"></i> <?php esc_html_e('Requisitos de titulación', 'flacso-uruguay'); ?></a>
                                            <?php if ($malla_doc_url !== '') : ?>
                                                <a href="<?php echo esc_url($malla_doc_url); ?>" target="_blank" rel="noopener noreferrer" class="flacso-meta-doc-link flacso-meta-doc-link--malla">
                                                    <i class="bi bi-file-earmark-pdf text-danger"></i> <?php esc_html_e('Malla Curricular (PDF)', 'flacso-uruguay'); ?>
                                                </a>
                                            <?php endif; ?>
                                        </div>

                                        <?php if ($asistente_nombre !== '') : ?>
                                            <div class="flacso-oa-aside-person" style="margin-top: 1.25rem; display: flex; align-items: center; gap: 12px; padding-top: 16px; border-top: 1px solid var(--borde);">
                                                <?php if ($asistente_avatar !== '') : ?>
                                                    <img src="<?php echo esc_url($asistente_avatar); ?>" alt="" class="flacso-oa-aside-person__avatar" style="width: 46px; height: 46px; border-radius: 50%; object-fit: cover;" />
                                                <?php else : ?>
                                                    <span class="flacso-oa-aside-person__avatar-placeholder" style="width: 46px; height: 46px; border-radius: 50%; background: #e2e8f0; display: grid; place-items: center; color: var(--azul);"><i class="bi bi-person-fill"></i></span>
                                                <?php endif; ?>
                                                <div class="flacso-oa-aside-person__info">
                                                    <strong style="display: block; font-size: 0.95rem; color: var(--texto);"><?php echo esc_html($asistente_nombre); ?></strong>
                                                    <small style="color: var(--muted);"><?php echo esc_html($asistente_rol); ?></small>
                                                </div>
                                            </div>
                                        <?php endif; ?>

                                        <?php if ($asistente_correo !== '') : ?>
                                            <a href="mailto:<?php echo esc_attr($asistente_correo); ?>" class="flacso-oa-aside-contact__email" style="margin-top: 0.75rem; width: 100%; display: flex; align-items: center; justify-content: center; gap: 8px; font-size: 0.88rem; color: var(--azul); font-weight: 700; text-decoration: none;">
                                                <i class="bi bi-envelope-fill"></i>
                                                <span><?php echo esc_html($asistente_correo); ?></span>
                                            </a>
                                        <?php endif; ?>
                                    </div>
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
                        <section class="flacso-oa-team-section">
                            <div class="flacso-oa-container">
                                <header class="flacso-oa-section-header">
                                    <h2><?php esc_html_e('Equipo Académico', 'flacso-uruguay'); ?></h2>
                                </header>

                                <div class="flacso-oa-team-subgroups">
                                    <?php foreach ($merged_equipos as $grupo) : ?>
                                        <?php
                                        $nivel = in_array((string) ($grupo['importancia'] ?? '3'), ['1', '2', '3'], true)
                                            ? (string) ($grupo['importancia'] ?? '3')
                                            : '3';
                                        $docentes_grupo = array_values((array) $grupo['docentes']);
                                        if (empty($docentes_grupo)) {
                                            continue;
                                        }

                                        ?>
                                        <div class="flacso-oa-team-subgroup flacso-oa-team-subgroup--nivel-<?php echo esc_attr($nivel); ?>">
                                            <?php if (!empty($grupo['nombre'])) : ?>
                                                <h3 class="flacso-oa-team-subgroup__title">
                                                    <?php echo esc_html($grupo['nombre']); ?>
                                                </h3>
                                            <?php endif; ?>

                                            <?php if (!empty($grupo['descripcion'])) : ?>
                                                <div class="flacso-oa-team-subgroup__description">
                                                    <?php echo wpautop(wp_kses_post($grupo['descripcion'])); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                                                </div>
                                            <?php endif; ?>

                                            <div class="flacso-oa-docentes-grid flacso-oa-docentes-grid--nivel-<?php echo esc_attr($nivel); ?>">
                                                <?php foreach ($docentes_grupo as $docente_item) : ?>
                                                    <?php
                                                    $docente_id = is_array($docente_item) ? ($docente_item['id'] ?? 0) : $docente_item;
                                                    $rol_especifico = is_array($docente_item) ? ($docente_item['rol'] ?? '') : '';
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
                                        <i class="bi bi-info-circle" aria-hidden="true"></i>
                                        <?php esc_html_e('El equipo docente y coordinación se encuentra en proceso de confirmación.', 'flacso-uruguay'); ?>
                                    </p>
                                </div>
                            </div>
                        </section>
                    <?php endif; ?>


                </article>
            </main>
        </div>
    </div>
</div>

<script>
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

        if (link.classList.contains('flacso-meta-doc-link--combined')) {
            eventName = 'MallaCalendarioClick';
        } else if (link.classList.contains('flacso-meta-doc-link--malla')) {
            eventName = 'MallaCurricularClick';
        } else if (link.classList.contains('flacso-meta-doc-link--calendario')) {
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
