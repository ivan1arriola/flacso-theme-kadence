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

                                <?php if (!empty($hero_quick_items)) : ?>
                                    <div class="flacso-oa-program-hero__quick-meta" aria-label="<?php esc_attr_e('Datos principales', 'flacso-uruguay'); ?>">
                                        <?php foreach ($hero_quick_items as $item) : ?>
                                            <div class="flacso-oa-program-hero__quick-item">
                                                <div class="flacso-oa-program-hero__quick-label">
                                                    <i class="bi <?php echo esc_attr($item['icon']); ?>" aria-hidden="true"></i>
                                                    <?php echo esc_html($item['label']); ?>
                                                </div>
                                                <div class="flacso-oa-program-hero__quick-value"><?php echo esc_html($item['value']); ?></div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
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
                                    <a href="#flacso-oa-contenido" class="flacso-oa-program-hero__button flacso-oa-program-hero__button--secondary">
                                        <?php esc_html_e('Ver propuesta académica', 'flacso-uruguay'); ?>
                                    </a>
                                </div>
                            </div>
                        </section>
                    </div>

                    <!-- ========================================== -->
                    <!-- CONTENIDO PRINCIPAL + SIDEBAR DE CONTACTO -->
                    <!-- ========================================== -->
                    <section id="flacso-oa-contenido" class="flacso-oa-main-section">
                        <div class="flacso-oa-container">
                            <div class="flacso-oa-main-grid">

                                <div class="flacso-oa-main-content">

                                    <?php if ($cohorte_nombre !== '' || $cohorte_inicio_fecha !== '') : ?>
                                        <!-- Callout Convocatoria Vigente (Compacto y Elegante) -->
                                        <div class="flacso-oa-cohort-callout" id="flacso-oa-convocatoria">
                                            <div class="flacso-oa-cohort-callout__top">
                                                <div class="flacso-oa-cohort-callout__header">
                                                    <i class="bi bi-clock-history" aria-hidden="true"></i>
                                                    <span><?php esc_html_e('Convocatoria vigente:', 'flacso-uruguay'); ?></span>
                                                    <strong><?php echo esc_html($cohorte_nombre_completo); ?></strong>
                                                </div>
                                                <div class="flacso-oa-cohort-callout__status">
                                                    <?php if ($inscripciones_abiertas) : ?>
                                                        <span class="flacso-badge flacso-badge--open">
                                                            <i class="bi bi-check-circle-fill" aria-hidden="true"></i>
                                                            <?php esc_html_e('Inscripciones abiertas', 'flacso-uruguay'); ?>
                                                        </span>
                                                        <?php if ($cohorte_periodo_inscripcion !== '') : ?>
                                                            <span class="flacso-oa-cohort-callout__deadline">
                                                                <?php echo esc_html($cohorte_periodo_inscripcion); ?>
                                                            </span>
                                                        <?php endif; ?>
                                                    <?php else : ?>
                                                        <span class="flacso-badge flacso-badge--soon">
                                                            <i class="bi bi-hourglass-split" aria-hidden="true"></i>
                                                            <?php esc_html_e('Próxima apertura', 'flacso-uruguay'); ?>
                                                        </span>
                                                    <?php endif; ?>
                                                </div>
                                            </div>

                                            <div class="flacso-oa-cohort-callout__items">
                                                <div class="flacso-oa-cohort-callout__item">
                                                    <i class="bi bi-calendar3" aria-hidden="true"></i>
                                                    <div>
                                                        <small><?php esc_html_e('Inicio:', 'flacso-uruguay'); ?></small>
                                                        <strong><?php echo esc_html($cohorte_inicio_fecha); ?></strong>
                                                    </div>
                                                </div>

                                                <div class="flacso-oa-cohort-callout__item">
                                                    <i class="bi bi-laptop" aria-hidden="true"></i>
                                                    <div>
                                                        <small><?php esc_html_e('Modalidad:', 'flacso-uruguay'); ?></small>
                                                        <strong><?php echo esc_html($cohorte_modalidad_titulo); ?></strong>
                                                    </div>
                                                </div>

                                                <?php if ($calendario_doc_url !== '') : ?>
                                                    <div class="flacso-oa-cohort-callout__item">
                                                        <i class="bi bi-file-earmark-pdf" aria-hidden="true"></i>
                                                        <div>
                                                            <small><?php esc_html_e('Calendario:', 'flacso-uruguay'); ?></small>
                                                            <a href="<?php echo esc_url($calendario_doc_url); ?>" target="_blank" rel="noopener noreferrer" class="flacso-meta-doc-link--calendario" data-flacso-meta-doc-section="CalendarioAcademicoSectionView">
                                                                <?php esc_html_e('Descargar PDF', 'flacso-uruguay'); ?>
                                                            </a>
                                                        </div>
                                                    </div>
                                                <?php endif; ?>

                                                <?php if ($mostrar_instancias_presenciales) : ?>
                                                    <div class="flacso-oa-cohort-callout__item">
                                                        <i class="bi bi-geo-alt-fill" style="color: #b45309;" aria-hidden="true"></i>
                                                        <div>
                                                            <span style="color: #92400e; font-weight: 600; font-size: 0.85rem;"><?php esc_html_e('Con instancias presenciales', 'flacso-uruguay'); ?></span>
                                                        </div>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    <?php endif; ?>

                                    <?php if ($has_main_content) : ?>
                                        <div class="flacso-oa-content-grid">
                                            <?php foreach ($main_content_cards as $content_card) : ?>
                                                <section class="flacso-oa-content-card <?php echo esc_attr($content_card['class']); ?>">
                                                    <?php if (!empty($content_card['title'])) : ?>
                                                        <header class="flacso-oa-content-card__header">
                                                            <h2><?php echo esc_html($content_card['title']); ?></h2>
                                                        </header>
                                                    <?php endif; ?>

                                                    <div class="flacso-oa-content-card__body">
                                                        <?php echo wp_kses_post($content_card['body']); ?>
                                                    </div>
                                                </section>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php else : ?>
                                        <div class="flacso-oa-content-grid">
                                            <section class="flacso-oa-content-card flacso-oa-content-card--empty">
                                                <header class="flacso-oa-content-card__header">
                                                    <h2><?php esc_html_e('Presentación', 'flacso-uruguay'); ?></h2>
                                                </header>
                                                <div class="flacso-oa-content-card__body">
                                                    <p class="flacso-oa-empty-field">
                                                        <i class="bi bi-info-circle" aria-hidden="true"></i>
                                                        <?php esc_html_e('Información de presentación no disponible por el momento. Podés solicitar más detalles a través del formulario de consulta.', 'flacso-uruguay'); ?>
                                                    </p>
                                                </div>
                                            </section>
                                        </div>
                                    <?php endif; ?>

                                    <!-- GRID DE TARJETAS DEL PROGRAMA ACADÉMICO -->
                                    <div class="flacso-oa-info-grid" style="margin-top: 2rem;">
                                        <?php
                                        // 1. OBJETIVOS DEL PROGRAMA
                                        if (!empty($academic['objetivos_html'])) {
                                            $render_info_card(
                                                __('Objetivos del Programa', 'flacso-uruguay'),
                                                $academic['objetivos_html']
                                            );
                                        } elseif (!empty($academic['objetivo_general']) || !empty($academic['objetivos_especificos'])) {
                                            $obj_html = '';
                                            if (!empty($academic['objetivo_general'])) {
                                                $obj_html .= '<p>' . wp_kses_post($academic['objetivo_general']) . '</p>';
                                            }
                                            if (!empty($academic['objetivos_especificos'])) {
                                                $obj_html .= '<ul>';
                                                foreach ($academic['objetivos_especificos'] as $obj_item) {
                                                    $obj_html .= '<li>' . wp_kses_post($obj_item) . '</li>';
                                                }
                                                $obj_html .= '</ul>';
                                            }
                                            $render_info_card(__('Objetivos del Programa', 'flacso-uruguay'), $obj_html);
                                        } else {
                                            $render_info_card(
                                                __('Objetivos del Programa', 'flacso-uruguay'),
                                                '<p class="flacso-oa-empty-field"><i class="bi bi-info-circle" aria-hidden="true"></i>' . esc_html__('Información sobre objetivos no disponible por el momento.', 'flacso-uruguay') . '</p>',
                                                'flacso-oa-info-card--empty'
                                            );
                                        }

                                        // 2. PLAN DE ESTUDIOS Y MALLA CURRICULAR
                                        if ($malla_doc_url !== '') {
                                            $malla_body = '<p style="margin-bottom:1rem;">' . esc_html__('Descargá el documento curricular oficial con el plan de estudios, materias y créditos.', 'flacso-uruguay') . '</p>' .
                                                          '<a href="' . esc_url($malla_doc_url) . '" target="_blank" rel="noopener noreferrer" class="flacso-oa-link-btn flacso-meta-doc-link flacso-meta-doc-link--malla" data-flacso-meta-doc-section="MallaCurricularSectionView" data-flacso-meta-doc-layout="single_malla" style="display:inline-flex; align-items:center; gap:0.5rem;"><i class="bi bi-file-earmark-pdf" aria-hidden="true"></i> ' . esc_html__('Descargar Malla Curricular (PDF)', 'flacso-uruguay') . '</a>';
                                            $render_info_card(
                                                __('Plan de Estudios y Malla Curricular', 'flacso-uruguay'),
                                                $malla_body,
                                                'flacso-oa-info-card--wide'
                                            );
                                        } else {
                                            $render_info_card(
                                                __('Plan de Estudios y Malla Curricular', 'flacso-uruguay'),
                                                '<p class="flacso-oa-empty-field"><i class="bi bi-info-circle" aria-hidden="true"></i>' . esc_html__('Documentación de malla curricular no disponible para descarga por el momento.', 'flacso-uruguay') . '</p>',
                                                'flacso-oa-info-card--wide flacso-oa-info-card--empty'
                                            );
                                        }

                                        // 3. PERFIL DE INGRESO
                                        if (!empty($academic['perfil_ingreso_html'])) {
                                            $render_info_card(
                                                __('Perfil de ingreso', 'flacso-uruguay'),
                                                $academic['perfil_ingreso_html']
                                            );
                                        } else {
                                            $render_info_card(
                                                __('Perfil de ingreso', 'flacso-uruguay'),
                                                '<p class="flacso-oa-empty-field"><i class="bi bi-info-circle" aria-hidden="true"></i>' . esc_html__('Información sobre perfil de ingreso no disponible por el momento.', 'flacso-uruguay') . '</p>',
                                                'flacso-oa-info-card--empty'
                                            );
                                        }

                                        // 4. REQUISITOS DE INGRESO
                                        if (!empty($academic['requisitos_ingreso_html'])) {
                                            $render_info_card(
                                                __('Requisitos de ingreso', 'flacso-uruguay'),
                                                $academic['requisitos_ingreso_html']
                                            );
                                        } else {
                                            $render_info_card(
                                                __('Requisitos de ingreso', 'flacso-uruguay'),
                                                '<p class="flacso-oa-empty-field"><i class="bi bi-info-circle" aria-hidden="true"></i>' . esc_html__('Información sobre requisitos de ingreso no disponible por el momento.', 'flacso-uruguay') . '</p>',
                                                'flacso-oa-info-card--empty'
                                            );
                                        }

                                        // 5. PERFIL DE EGRESO
                                        if (!empty($academic['perfil_egreso_html'])) {
                                            $render_info_card(
                                                __('Perfil de egreso', 'flacso-uruguay'),
                                                $academic['perfil_egreso_html']
                                            );
                                        } else {
                                            $render_info_card(
                                                __('Perfil de egreso', 'flacso-uruguay'),
                                                '<p class="flacso-oa-empty-field"><i class="bi bi-info-circle" aria-hidden="true"></i>' . esc_html__('Información sobre perfil de egreso no disponible por el momento.', 'flacso-uruguay') . '</p>',
                                                'flacso-oa-info-card--empty'
                                            );
                                        }

                                        // 6. REQUISITOS DE EGRESO
                                        if (!empty($academic['requisitos_egreso_html'])) {
                                            $render_info_card(
                                                __('Requisitos de egreso y titulación', 'flacso-uruguay'),
                                                $academic['requisitos_egreso_html']
                                            );
                                        } else {
                                            $render_info_card(
                                                __('Requisitos de egreso y titulación', 'flacso-uruguay'),
                                                '<p class="flacso-oa-empty-field"><i class="bi bi-info-circle" aria-hidden="true"></i>' . esc_html__('Información sobre requisitos de egreso no disponible por el momento.', 'flacso-uruguay') . '</p>',
                                                'flacso-oa-info-card--empty'
                                            );
                                        }

                                        // 7. TÍTULOS Y CERTIFICACIONES
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
                                        if ($titulos_body !== '') {
                                            $render_info_card(
                                                __('Títulos, Certificaciones y Validez', 'flacso-uruguay'),
                                                $titulos_body,
                                                'flacso-oa-info-card--wide'
                                            );
                                        } else {
                                            $render_info_card(
                                                __('Títulos, Certificaciones y Validez', 'flacso-uruguay'),
                                                '<p class="flacso-oa-empty-field"><i class="bi bi-info-circle" aria-hidden="true"></i>' . esc_html__('Información sobre títulos y certificaciones no disponible por el momento.', 'flacso-uruguay') . '</p>',
                                                'flacso-oa-info-card--wide flacso-oa-info-card--empty'
                                            );
                                        }
                                        ?>
                                    </div>

                                </div>

                                <aside id="flacso-oa-consulta" class="flacso-oa-aside" aria-label="<?php esc_attr_e('Formulario de consulta', 'flacso-uruguay'); ?>">
                                    <div class="flacso-oa-aside__sticky">
                                        <div class="flacso-oa-form-panel">
                                            <div class="flacso-oa-form-panel__header">
                                                <h2><?php esc_html_e('Solicitá información', 'flacso-uruguay'); ?></h2>
                                                <p><?php esc_html_e('Completá el formulario y recibí información sobre cursada, inscripción y financiación.', 'flacso-uruguay'); ?></p>
                                            </div>

                                            <div class="flacso-oa-form-panel__body">
                                                <?php
                                                if (function_exists('flacso_consultas_render_form')) {
                                                    echo flacso_consultas_render_form(['mostrar_preinscripcion' => false]);
                                                }
                                                ?>
                                            </div>
                                        </div>

                                        <?php if ($inscripciones_abiertas && function_exists('flacso_consultas_render_preinscripcion_button')) : ?>
                                            <div class="flacso-oa-preinsc-wrapper" style="margin-top: 0.75rem; padding: 0 clamp(20px, 3vw, 30px);">
                                                <?php echo flacso_consultas_render_preinscripcion_button(); ?>
                                            </div>
                                        <?php elseif ($inscripciones_abiertas && $preinscripcion_url !== '') : ?>
                                            <div class="flacso-oa-preinsc-wrapper" style="margin-top: 0.75rem; padding: 0 clamp(20px, 3vw, 30px);">
                                                <a href="<?php echo esc_url($preinscripcion_url); ?>" data-flacso-preinscription-cta="1" class="btn btn-primary w-100 py-3 fw-bold" style="border-radius: 12px; font-size: 1.05rem;">
                                                    <i class="bi bi-pencil-square me-2" aria-hidden="true"></i> <?php esc_html_e('Preinscripción online', 'flacso-uruguay'); ?>
                                                </a>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </aside>

                            </div>
                        </div>
                    </section>

                    <!-- ========================================== -->
                    <!-- CTA SECTION -->
                    <!-- ========================================== -->
                    <section class="flacso-oa-cta-section">
                        <div class="flacso-oa-container">
                            <div class="flacso-oa-cta">
                                <p>
                                    <?php esc_html_e('Para realizar las postulaciones puede completar el formulario y en breve el personal de asistencia académica se pondrá en contacto.', 'flacso-uruguay'); ?>
                                </p>
                            </div>
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
