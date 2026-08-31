<?php
/**
 * Vista individual por defecto de una Oferta Académica (maestrías, diplomas, doctorados, especializaciones, diplomados).
 *
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
    $context = function_exists('flacso_theme_get_offer_context') ? flacso_theme_get_offer_context($post_id) : [];
}

if (empty($context)) {
    return;
}

$post_id          = (int) $context['offer_id'];
$title            = (string) $context['title'];
$tipo_oferta      = (string) $context['type_label'];
$academic         = $context['academic'] ?? [];
$instance         = $context['instance'] ?? [];
$registration     = $context['registration'] ?? [];
$thumbnail_url    = (string) ($context['thumbnail_url'] ?? '');

$data = class_exists('Oferta_Data_Schema') ? Oferta_Data_Schema::get_schema($post_id) : [];
if (!is_array($data)) {
    $data = [];
}

$raw_content = get_post_field('post_content', $post_id);
$filtered_main_content = trim((string) apply_filters('the_content', $raw_content));
$descripcion_html = !empty($academic['descripcion_html']) ? $academic['descripcion_html'] : (!empty($data['descripcion_html']) ? $data['descripcion_html'] : '');

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
$preinscripcion_url = (string) ($registration['url'] ?? '');
$cta_flow = (string) ($registration['flow'] ?? 'legacy_editor');
$cta_instance_id = (int) ($registration['instance_id'] ?? 0);

$inscripciones_year = class_exists('Oferta_Data_Schema')
    ? Oferta_Data_Schema::resolve_inscripciones_year($instance['fecha_inicio'] ?? '', $instance['label'] ?? '')
    : '';

if ($inscripciones_year === '') {
    $inscripciones_year = wp_date('Y');
}

$hero_tag = $inscripciones_abiertas
    ? sprintf(
        /* translators: %s: academic year */
        __('Inscripciones %s', 'flacso-uruguay'),
        $inscripciones_year
    )
    : __('Próximamente', 'flacso-uruguay');

$hero_primary_label = $inscripciones_abiertas
    ? sprintf(
        /* translators: %s: academic year */
        __('Preinscripción %s', 'flacso-uruguay'),
        $inscripciones_year
    )
    : __('Solicitar información', 'flacso-uruguay');

$hero_primary_url = $inscripciones_abiertas
    ? $preinscripcion_url
    : '#flacso-oa-consulta';

$format_duracion = static function ($meses_str) {
    if (class_exists('Oferta_Renderer') && method_exists('Oferta_Renderer', 'format_duration_months')) {
        return Oferta_Renderer::format_duration_months((string) $meses_str, 'flacso-uruguay');
    }
    return '';
};

$normalize_duracion_html = static function ($html, $meses_str = '') {
    if (class_exists('Oferta_Renderer') && method_exists('Oferta_Renderer', 'normalize_duration_html')) {
        return Oferta_Renderer::normalize_duration_html((string) $html, (string) $meses_str, 'flacso-uruguay');
    }
    return (string) $html;
};

$raw_proximo = (string) ($instance['fecha_inicio'] ?? '');
$proximo_precision = (string) ($instance['precision_fecha_inicio'] ?? '');
$proximo_formatted = $raw_proximo !== '' && class_exists('Oferta_Renderer')
    ? Oferta_Renderer::format_proximo_inicio_text($raw_proximo, $proximo_precision)
    : trim($raw_proximo);
$cohorte = trim((string) ($instance['label'] ?? ''));

$modalidad_text = trim((string) ($data['modalidad_resumen'] ?? ''));
if ($modalidad_text === '') {
    $modalidad_text = trim((string) ($instance['modalidad'] ?? ''));
}

$hero_quick_items = [];

if ($proximo_formatted !== '') {
    $hero_quick_items[] = [
        'icon'  => 'bi-calendar3',
        'label' => __('Próximo inicio', 'flacso-uruguay'),
        'value' => $proximo_formatted . ($cohorte !== '' ? ' · ' . $cohorte : ''),
    ];
}

$duracion_meses_val = $academic['duracion_meses'] ?? ($data['duracion_meses'] ?? '');
if (!empty($duracion_meses_val)) {
    $duracion_text = $format_duracion($duracion_meses_val);
    if ($duracion_text !== '') {
        $hero_quick_items[] = [
            'icon'  => 'bi-clock',
            'label' => __('Duración', 'flacso-uruguay'),
            'value' => $duracion_text,
        ];
    }
}

if ($modalidad_text !== '') {
    $hero_quick_items[] = [
        'icon'  => 'bi-camera-video',
        'label' => __('Modalidad', 'flacso-uruguay'),
        'value' => $modalidad_text,
    ];
}

$hero_badge_text = trim((string) ($data['carta_hero_etiqueta'] ?? get_post_meta($post_id, 'carta_hero_etiqueta', true)));
$instancias_presenciales_meta = $data['carta_instancias_presenciales'] ?? get_post_meta($post_id, 'carta_instancias_presenciales', true);
$mostrar_instancias_presenciales = $instancias_presenciales_meta === true
    || $instancias_presenciales_meta === 1
    || $instancias_presenciales_meta === '1'
    || $instancias_presenciales_meta === 'true';

$render_info_card = static function ($title, $body, $extra_class = '', array $attrs = []) use ($has_visible_html) {
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
            <h3><?php echo esc_html($title); ?></h3>
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

    $make_card = static function ($title, $body) use ($has_visible_html) {
        $title = trim((string) $title);
        $body = trim((string) $body);

        if (!$has_visible_html($body)) {
            return null;
        }

        $classes = [];

        if ($title === '' || preg_match('/<(table|iframe)\b/i', $body)) {
            $classes[] = 'flacso-oa-content-card--wide';
        }

        return [
            'title' => $title,
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
    $post = get_post($docente_id);
    if (!$post || $post->post_type !== 'docente') {
        return;
    }

    $nivel = in_array((string) $nivel, ['1', '2', '3'], true) ? (string) $nivel : '3';

    $titulo = function_exists('dp_nombre_completo') ? dp_nombre_completo($docente_id) : get_the_title($docente_id);
    $pref_abrev = trim((string) get_post_meta($docente_id, 'prefijo_abrev', true));
    $tit_acad   = trim((string) get_post_meta($docente_id, 'titulo_academico', true));

    $academic_label = $tit_acad ? $tit_acad : $pref_abrev;
    if ($academic_label !== '' && stripos($titulo, $academic_label) !== false) {
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

    echo '<div class="flacso-oa-docente-item flacso-oa-docente-item--nivel-' . esc_attr($nivel) . '">';
    echo '<article class="' . esc_attr($card_classes) . '" aria-label="' . esc_attr(sprintf(__('Docente: %s', 'flacso-uruguay'), $titulo)) . '">';

    if ($nivel === '1') {
        echo '<div class="flacso-oa-person-card__media">';
        echo '<div class="flacso-oa-person-card__avatar-wrap">';
        if (function_exists('dp_avatar_markup')) {
            echo dp_avatar_markup($docente_id, $titulo, $avatar_size, $avatar_class);
        } else {
            echo get_the_post_thumbnail($docente_id, 'medium_large', ['class' => $avatar_class]);
        }
        echo '</div>';
        echo '</div>';

        echo '<div class="flacso-oa-person-card__content">';
        if ($rol) {
            echo '<div class="flacso-oa-person-card__meta-row">';
            echo '<span class="flacso-oa-person-card__role-pill">' . esc_html($rol) . '</span>';
            echo '</div>';
        }

        echo '<h4 class="flacso-oa-person-card__name">' . esc_html($titulo) . '</h4>';

        if ($academic_label) {
            echo '<p class="flacso-oa-person-card__academic">' . esc_html($academic_label) . '</p>';
        }

        if ($has_cv) {
            echo '<div class="flacso-oa-person-card__cv">' . wp_kses_post($cv_raw) . '</div>';
        }

        echo '</div>';
    } else {
        echo '<div class="flacso-oa-person-card__header">';
        echo '<div class="flacso-oa-person-card__avatar-circle">';
        if (function_exists('dp_avatar_markup')) {
            echo dp_avatar_markup($docente_id, $titulo, $avatar_size, $avatar_class);
        } else {
            echo get_the_post_thumbnail($docente_id, 'thumbnail', ['class' => $avatar_class]);
        }
        echo '</div>';

        echo '<div class="flacso-oa-person-card__header-info">';
        if ($rol) {
            echo '<span class="flacso-oa-person-card__role-pill">' . esc_html($rol) . '</span>';
        }
        echo '<h4 class="flacso-oa-person-card__name">' . esc_html($titulo) . '</h4>';
        if ($academic_label) {
            echo '<p class="flacso-oa-person-card__academic">' . esc_html($academic_label) . '</p>';
        }
        echo '</div>';
        echo '</div>';

        if ($nivel === '2' && $has_cv) {
            echo '<div class="flacso-oa-person-card__content">';
            echo '<div class="flacso-oa-person-card__cv">' . wp_kses_post($cv_raw) . '</div>';
            echo '</div>';
        }
    }

    echo '</article>';
    echo '</div>';
};

$banner_featured_url = $thumbnail_url;
if (!$banner_featured_url) {
    $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="1400" height="788" viewBox="0 0 1400 788">'
        . '<defs><linearGradient id="g" x1="0" y1="0" x2="1" y2="1">'
        . '<stop offset="0" stop-color="#1d3a72"/><stop offset="1" stop-color="#0f1f3e"/>'
        . '</linearGradient></defs>'
        . '<rect width="1400" height="788" fill="url(#g)"/>'
        . '<circle cx="1080" cy="240" r="210" fill="rgba(254,210,34,0.18)"/>'
        . '<circle cx="1160" cy="320" r="150" fill="rgba(254,210,34,0.12)"/>'
        . '<rect x="90" y="560" width="1220" height="140" rx="16" fill="rgba(0,0,0,0.18)"/>'
        . '<text x="110" y="615" font-family="Inter, Arial" font-size="44" fill="rgba(255,255,255,0.92)">Previsualizacion</text>'
        . '<text x="110" y="670" font-family="Inter, Arial" font-size="28" fill="rgba(255,255,255,0.78)">Defini una imagen destacada para ver la foto real.</text>'
        . '</svg>';
    $banner_featured_url = 'data:image/svg+xml;base64,' . base64_encode($svg);
}

$custom_mensaje = (string) ($registration['open_message'] ?? get_post_meta($post_id, 'inscripciones_mensaje', true));
$custom_mensaje_cerrado = (string) ($registration['closed_message'] ?? get_post_meta($post_id, 'inscripciones_mensaje_cerrado', true));
$default_mensaje_abierto = get_option('flacso_inscripciones_mensaje_abierto_default', 'Descuentos especiales disponibles. Solicitá información e inscribite hoy.');
$default_mensaje_cerrado = get_option('flacso_inscripciones_mensaje_cerrado_default', 'Mantente atento a nuestras próximas aperturas.');

if ($inscripciones_abiertas) {
    $banner_cta_text = !empty($custom_mensaje) ? $custom_mensaje : $default_mensaje_abierto;
} else {
    $banner_cta_text = !empty($custom_mensaje_cerrado) ? $custom_mensaje_cerrado : $default_mensaje_cerrado;
}
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
                                    <img
                                        src="<?php echo esc_url($banner_featured_url); ?>"
                                        alt="<?php echo esc_attr($title); ?>"
                                        loading="eager"
                                        fetchpriority="high">
                                </div>
                            </div>

                            <div class="flacso-oa-program-hero__content">
                                <div class="flacso-oa-program-hero__badges">
                                    <?php if ($tipo_oferta !== '') : ?>
                                        <p class="flacso-oa-program-hero__kicker"><?php echo esc_html($tipo_oferta); ?></p>
                                    <?php endif; ?>
                                    <?php if ($hero_badge_text !== '') : ?>
                                        <span class="flacso-oa-program-hero__custom-badge"><?php echo esc_html($hero_badge_text); ?></span>
                                    <?php endif; ?>
                                </div>

                                <h1 id="flacso-oa-program-title" class="flacso-oa-program-hero__title"><?php echo esc_html($title); ?></h1>

                                <?php if (has_excerpt($post_id)) : ?>
                                    <div class="flacso-oa-program-hero__excerpt"><?php echo get_the_excerpt($post_id); ?></div>
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

                                <p class="flacso-oa-program-hero__availability<?php echo $inscripciones_abiertas ? ' is-open' : ''; ?>">
                                    <span><?php echo esc_html($banner_cta_text); ?></span>
                                </p>

                                <div class="flacso-oa-program-hero__actions">
                                    <a href="<?php echo esc_url($hero_primary_url); ?>"<?php echo $inscripciones_abiertas ? ' data-flacso-preinscription-cta="1" data-flacso-cta-flow="' . esc_attr($cta_flow) . '" data-flacso-instance-id="' . esc_attr((string) $cta_instance_id) . '"' : ''; ?> class="flacso-oa-program-hero__button flacso-oa-program-hero__button--primary">
                                        <?php echo esc_html($hero_primary_label); ?>
                                        <i class="bi bi-arrow-right" aria-hidden="true"></i>
                                    </a>
                                    <a href="#flacso-oa-contenido" class="flacso-oa-program-hero__button flacso-oa-program-hero__button--secondary">
                                        <?php esc_html_e('Ver información', 'flacso-uruguay'); ?>
                                    </a>
                                </div>
                            </div>
                        </section>
                    </div>

                    <section id="flacso-oa-contenido" class="flacso-oa-main-section">
                        <div class="flacso-oa-container">
                            <div class="flacso-oa-main-grid">

                                <div class="flacso-oa-main-content">

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
                                    <?php endif; ?>

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
                                                } elseif (class_exists('Oferta_Consulta_Form')) {
                                                    echo Oferta_Consulta_Form::render_inline_form($post_id);
                                                }
                                                ?>
                                            </div>
                                        </div>

                                        <?php if ($inscripciones_abiertas && function_exists('flacso_consultas_render_preinscripcion_button')) : ?>
                                            <div class="flacso-oa-preinsc-wrapper" style="margin-top: 0.75rem; padding: 0 clamp(20px, 3vw, 30px);">
                                                <?php echo flacso_consultas_render_preinscripcion_button(); ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </aside>

                            </div>

                            <div class="flacso-oa-info-grid" style="margin-top: 3rem;">
                                <?php
                                $modalidad_html = !empty($academic['modalidad_html'])
                                    ? $academic['modalidad_html']
                                    : (!empty($data['modalidad_html']) ? $data['modalidad_html'] : '<p>' . esc_html__('Virtual.', 'flacso-uruguay') . '</p>');

                                $render_info_card(
                                    __('Modalidad', 'flacso-uruguay'),
                                    $modalidad_html
                                );

                                $duracion_html = $normalize_duracion_html($academic['duracion_html'] ?? ($data['duracion_html'] ?? ''), $academic['duracion_meses'] ?? ($data['duracion_meses'] ?? ''));
                                if (!empty($duracion_html)) {
                                    $render_info_card(
                                        __('Duración', 'flacso-uruguay'),
                                        $duracion_html
                                    );
                                }

                                if (!empty($academic['objetivos_html']) || !empty($data['objetivos_html'])) {
                                    $render_info_card(
                                        __('Objetivos', 'flacso-uruguay'),
                                        $academic['objetivos_html'] ?? $data['objetivos_html']
                                    );
                                }

                                if (!empty($academic['perfil_ingreso_html']) || !empty($data['perfil_ingreso_html'])) {
                                    $render_info_card(
                                        __('Perfil de ingreso', 'flacso-uruguay'),
                                        $academic['perfil_ingreso_html'] ?? $data['perfil_ingreso_html']
                                    );
                                }

                                if (!empty($academic['requisitos_ingreso_html']) || !empty($data['requisitos_ingreso_html'])) {
                                    $render_info_card(
                                        __('Requisitos de ingreso', 'flacso-uruguay'),
                                        $academic['requisitos_ingreso_html'] ?? $data['requisitos_ingreso_html']
                                    );
                                }

                                if (!empty($academic['certificacion_html']) || !empty($data['certificacion_html'])) {
                                    $render_info_card(
                                        __('Certificación', 'flacso-uruguay'),
                                        $academic['certificacion_html'] ?? $data['certificacion_html']
                                    );
                                }

                                if (!empty($academic['aranceles_html']) || !empty($data['aranceles_html'])) {
                                    $render_info_card(
                                        __('Aranceles', 'flacso-uruguay'),
                                        $academic['aranceles_html'] ?? $data['aranceles_html']
                                    );
                                }

                                // Malla / Calendario PDFs
                                $malla_pdf_url = $academic['pdf_malla_url'] ?? ($data['pdf_malla_url'] ?? '');
                                $calendario_pdf_url = $academic['pdf_calendario_url'] ?? ($data['pdf_calendario_url'] ?? '');
                                $cartamalla_pdf_url = $academic['pdf_cartamalla_url'] ?? ($data['pdf_cartamalla_url'] ?? '');

                                if (!empty($cartamalla_pdf_url)) {
                                    $cartamalla_body = '<a href="' . esc_url($cartamalla_pdf_url) . '" target="_blank" rel="noopener noreferrer" class="flacso-oa-link-btn flacso-meta-doc-link flacso-meta-doc-link--combined" style="display:inline-flex; align-items:center; gap:0.5rem;"><i class="bi bi-file-earmark-pdf"></i> ' . esc_html__('Ver Calendario y Malla (PDF)', 'flacso-uruguay') . '</a>';
                                    $render_info_card(
                                        __('Calendario y Malla', 'flacso-uruguay'),
                                        $cartamalla_body,
                                        '',
                                        [
                                            'data-flacso-meta-doc-section' => 'MallaCalendarioView',
                                            'data-flacso-meta-doc-layout'  => 'combined',
                                        ]
                                    );
                                } elseif (!empty($malla_pdf_url) && !empty($calendario_pdf_url)) {
                                    $combined_body = '<div style="display:flex; flex-direction:column; gap:0.75rem;">' .
                                        '<a href="' . esc_url($malla_pdf_url) . '" target="_blank" rel="noopener noreferrer" class="flacso-oa-link-btn flacso-meta-doc-link flacso-meta-doc-link--malla" style="display:inline-flex; align-items:center; gap:0.5rem;"><i class="bi bi-file-earmark-pdf"></i> ' . esc_html__('Ver Malla Curricular (PDF)', 'flacso-uruguay') . '</a>' .
                                        '<a href="' . esc_url($calendario_pdf_url) . '" target="_blank" rel="noopener noreferrer" class="flacso-oa-link-btn flacso-meta-doc-link flacso-meta-doc-link--calendario" style="display:inline-flex; align-items:center; gap:0.5rem;"><i class="bi bi-file-earmark-pdf"></i> ' . esc_html__('Ver Calendario (PDF)', 'flacso-uruguay') . '</a>' .
                                        '</div>';
                                    $render_info_card(
                                        __('Malla y Calendario', 'flacso-uruguay'),
                                        $combined_body,
                                        '',
                                        [
                                            'data-flacso-meta-doc-section' => 'MallaCalendarioSplitView',
                                            'data-flacso-meta-doc-layout'  => 'split_combined',
                                        ]
                                    );
                                } else {
                                    if (!empty($malla_pdf_url)) {
                                        $malla_body = '<a href="' . esc_url($malla_pdf_url) . '" target="_blank" rel="noopener noreferrer" class="flacso-oa-link-btn flacso-meta-doc-link flacso-meta-doc-link--malla" style="display:inline-flex; align-items:center; gap:0.5rem;"><i class="bi bi-file-earmark-pdf"></i> ' . esc_html__('Ver Malla Curricular (PDF)', 'flacso-uruguay') . '</a>';
                                        $render_info_card(
                                            __('Malla curricular', 'flacso-uruguay'),
                                            $malla_body,
                                            '',
                                            [
                                                'data-flacso-meta-doc-section' => 'MallaCurricularView',
                                                'data-flacso-meta-doc-layout'  => 'standalone_malla',
                                            ]
                                        );
                                    }

                                    if (!empty($calendario_pdf_url)) {
                                        $calendario_body = '<a href="' . esc_url($calendario_pdf_url) . '" target="_blank" rel="noopener noreferrer" class="flacso-oa-link-btn flacso-meta-doc-link flacso-meta-doc-link--calendario" style="display:inline-flex; align-items:center; gap:0.5rem;"><i class="bi bi-file-earmark-pdf"></i> ' . esc_html__('Ver Calendario (PDF)', 'flacso-uruguay') . '</a>';
                                        $render_info_card(
                                            __('Calendario', 'flacso-uruguay'),
                                            $calendario_body,
                                            '',
                                            [
                                                'data-flacso-meta-doc-section' => 'CalendarioAcademicoView',
                                                'data-flacso-meta-doc-layout'  => 'standalone_calendario',
                                            ]
                                        );
                                    }
                                }

                                if (!empty($academic['faq_html']) || !empty($data['faq_html'])) {
                                    $render_info_card(
                                        __('Preguntas frecuentes', 'flacso-uruguay'),
                                        $academic['faq_html'] ?? $data['faq_html'],
                                        'flacso-oa-info-card--faq'
                                    );
                                }
                                ?>
                            </div>
                        </div>
                    </section>

                    <?php
                    $docentes_grupos = class_exists('Oferta_Data_Schema')
                        && method_exists('Oferta_Data_Schema', 'get_docentes_grupos')
                        ? Oferta_Data_Schema::get_docentes_grupos($post_id)
                        : [];

                    if (!empty($docentes_grupos)) :
                    ?>
                        <section class="flacso-oa-docentes-section" aria-labelledby="flacso-docentes-title">
                            <div class="flacso-oa-container">
                                <header class="flacso-oa-docentes-section__header">
                                    <h2 id="flacso-docentes-title"><?php esc_html_e('Equipo docente', 'flacso-uruguay'); ?></h2>
                                </header>

                                <div class="flacso-oa-docentes-grupos">
                                    <?php foreach ($docentes_grupos as $grupo) :
                                        $nivel = in_array((string) $grupo['nivel'], ['1', '2', '3'], true) ? (string) $grupo['nivel'] : '3';
                                    ?>
                                        <div class="flacso-oa-docentes-grupo flacso-oa-docentes-grupo--nivel-<?php echo esc_attr($nivel); ?>">
                                            <?php if (!empty($grupo['titulo'])) : ?>
                                                <div class="flacso-oa-docentes-grupo__header">
                                                    <h3 class="flacso-oa-docentes-grupo__title"><?php echo esc_html($grupo['titulo']); ?></h3>
                                                </div>
                                            <?php endif; ?>

                                            <div class="flacso-oa-docentes-grid flacso-oa-docentes-grid--nivel-<?php echo esc_attr($nivel); ?>">
                                                <?php foreach ($grupo['docentes'] as $docente_item) : ?>
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
                    <?php endif; ?>

                    <?php
                    if (function_exists('flacso_theme_get_program_seminars_catalog')) :
                        $seminarios_all = flacso_theme_get_program_seminars_catalog($post_id);
                        $seminarios = [];
                        $hoy = new DateTimeImmutable('today', wp_timezone());

                        foreach ($seminarios_all as $seminario_context) {
                            $seminario_instance = $seminario_context['instance'] ?? [];
                            $fecha_inicio = (string) ($seminario_instance['fecha_inicio'] ?? '');
                            if ($fecha_inicio === '') {
                                continue;
                            }

                            $inicio_obj = DateTimeImmutable::createFromFormat('Y-m-d|', $fecha_inicio, wp_timezone());
                            if (!$inicio_obj) {
                                continue;
                            }

                            $dias_hasta_inicio = (int) floor(($inicio_obj->getTimestamp() - $hoy->getTimestamp()) / DAY_IN_SECONDS);
                            if ($dias_hasta_inicio < -7) {
                                continue;
                            }

                            $seminarios[] = [
                                'id'           => (int) ($seminario_context['offer_id'] ?? 0),
                                'titulo'       => (string) ($seminario_context['title'] ?? ''),
                                'permalink'    => (string) ($seminario_context['permalink'] ?? ''),
                                'fecha_inicio' => $fecha_inicio,
                            ];
                        }

                        usort($seminarios, static function ($a, $b) {
                            return strcmp($a['fecha_inicio'] ?? '', $b['fecha_inicio'] ?? '');
                        });

                        if (!empty($seminarios)) {
                            get_template_part(
                                'template-parts/oferta/seminarios-vinculados',
                                null,
                                [
                                    'post_id'    => $post_id,
                                    'seminarios' => $seminarios,
                                ]
                            );
                        }
                    endif;
                    ?>

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
