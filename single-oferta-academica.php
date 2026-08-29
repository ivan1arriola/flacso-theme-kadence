<?php
/**
 * Template responsive para la página individual de una Oferta Académica.
 */

if (!defined('ABSPATH')) {
    exit;
}

$post_id = get_the_ID();

$data = class_exists('Oferta_Data_Schema')
    ? Oferta_Data_Schema::get_schema($post_id)
    : [];

if (!is_array($data)) {
    $data = [];
}

$thumbnail_url = get_the_post_thumbnail_url($post_id, 'full');

$tipo_oferta = '';
$tipo_terms = get_the_terms($post_id, 'tipo-oferta-academica');

if (!is_wp_error($tipo_terms) && !empty($tipo_terms)) {
    $tipo_oferta = $tipo_terms[0]->name;
}

$raw_content = get_post_field('post_content', $post_id);
$filtered_main_content = trim((string) apply_filters('the_content', $raw_content));
$descripcion_html = !empty($data['descripcion_html']) ? $data['descripcion_html'] : '';

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

$inscripciones_meta = get_post_meta($post_id, 'inscripciones_abiertas', true);
$preinscription_cta = function_exists('flacso_get_preinscription_cta')
    ? flacso_get_preinscription_cta($post_id)
    : null;

$inscripciones_abiertas = is_array($preinscription_cta)
    ? !empty($preinscription_cta['is_open'])
    : (!empty($data['inscripciones_abiertas'])
    || $inscripciones_meta === '1'
    || $inscripciones_meta === 'true'
    || $inscripciones_meta === true
    || $inscripciones_meta === 1);

$preinscripcion_url = is_array($preinscription_cta)
    ? (string) ($preinscription_cta['url'] ?? '')
    : '';
$inscripciones_year = class_exists('Oferta_Data_Schema')
    ? Oferta_Data_Schema::resolve_inscripciones_year($data['proximo_inicio'] ?? '', $data['cohorte'] ?? '')
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

$format_duracion = function($meses_str) {
    if (class_exists('Oferta_Renderer') && method_exists('Oferta_Renderer', 'format_duration_months')) {
        return Oferta_Renderer::format_duration_months((string) $meses_str, 'flacso-uruguay');
    }

    return '';
};

$normalize_duracion_html = function($html, $meses_str = '') {
    if (class_exists('Oferta_Renderer') && method_exists('Oferta_Renderer', 'normalize_duration_html')) {
        return Oferta_Renderer::normalize_duration_html((string) $html, (string) $meses_str, 'flacso-uruguay');
    }

    return (string) $html;
};

$raw_proximo = (string) get_post_meta($post_id, 'proximo_inicio', true);
$proximo_precision = (string) get_post_meta($post_id, 'proximo_inicio_precision', true);
$proximo_formatted = $raw_proximo !== '' && class_exists('Oferta_Renderer')
    ? Oferta_Renderer::format_proximo_inicio_text($raw_proximo, $proximo_precision)
    : trim($raw_proximo);
$cohorte = trim((string) get_post_meta($post_id, 'cohorte', true));

$modalidad_text = trim((string) ($data['modalidad_resumen'] ?? ''));
if ($modalidad_text === '') {
    $modalidad_text = trim((string) get_post_meta($post_id, 'modalidad_resumen', true));
}

$hero_quick_items = array();

if ($proximo_formatted !== '') {
    $hero_quick_items[] = array(
        'icon'  => 'bi-calendar3',
        'label' => __('Próximo inicio', 'flacso-uruguay'),
        'value' => $proximo_formatted . ($cohorte !== '' ? ' · ' . $cohorte : ''),
    );
}

if (!empty($data['duracion_meses'])) {
    $duracion_text = $format_duracion($data['duracion_meses']);
    if ($duracion_text !== '') {
        $hero_quick_items[] = array(
            'icon'  => 'bi-clock',
            'label' => __('Duración', 'flacso-uruguay'),
            'value' => $duracion_text,
        );
    }
}

if ($modalidad_text !== '') {
    $hero_quick_items[] = array(
        'icon'  => 'bi-camera-video',
        'label' => __('Modalidad', 'flacso-uruguay'),
        'value' => $modalidad_text,
    );
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
            'body' => $body,
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
        'body' => '',
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
                'body' => '',
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

if (class_exists('Oferta_Renderer')) {
    Oferta_Renderer::enqueue_styles();
}

get_header();
?>

<div id="inner-wrap" class="wrap kt-clear flacso-oferta-responsive">
    <div id="primary" class="content-area">
        <div class="content-container">
            <main id="main" class="site-main">
                <article id="post-<?php echo esc_attr($post_id); ?>" <?php post_class('entry content-bg single-entry flacso-oa-article'); ?>>

                    <?php
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

                    $custom_mensaje = is_array($preinscription_cta)
                        ? ($preinscription_cta['open_message'] ?? '')
                        : get_post_meta($post_id, 'inscripciones_mensaje', true);
                    $custom_mensaje_cerrado = is_array($preinscription_cta)
                        ? ($preinscription_cta['closed_message'] ?? '')
                        : get_post_meta($post_id, 'inscripciones_mensaje_cerrado', true);
                    $default_mensaje_abierto = get_option('flacso_inscripciones_mensaje_abierto_default', 'Descuentos especiales disponibles. Solicitá información e inscribite hoy.');
                    $default_mensaje_cerrado = get_option('flacso_inscripciones_mensaje_cerrado_default', 'Mantente atento a nuestras próximas aperturas.');

                    if ($inscripciones_abiertas) {
                        $banner_cta_text = !empty($custom_mensaje) ? $custom_mensaje : $default_mensaje_abierto;
                    } else {
                        $banner_cta_text = !empty($custom_mensaje_cerrado) ? $custom_mensaje_cerrado : $default_mensaje_cerrado;
                    }
                    ?>

                    <div class="flacso-oa-container flacso-oa-single-hero">
                        <section class="flacso-oa-program-hero" aria-labelledby="flacso-oa-program-title">
                            <div class="flacso-oa-program-hero__media-wrap">
                                <div class="flacso-oa-program-hero__media">
                                    <img
                                        src="<?php echo esc_url($banner_featured_url); ?>"
                                        alt="<?php echo esc_attr(get_the_title($post_id)); ?>"
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

                                <h1 id="flacso-oa-program-title" class="flacso-oa-program-hero__title"><?php the_title(); ?></h1>

                                <?php if (has_excerpt()) : ?>
                                    <div class="flacso-oa-program-hero__excerpt"><?php the_excerpt(); ?></div>
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
                                    <a href="<?php echo esc_url($hero_primary_url); ?>"<?php echo $inscripciones_abiertas ? ' data-flacso-preinscription-cta="1" data-flacso-cta-flow="' . esc_attr(is_array($preinscription_cta) ? ($preinscription_cta['flow'] ?? 'legacy_editor') : 'legacy_editor') . '" data-flacso-instance-id="' . esc_attr(is_array($preinscription_cta) ? ($preinscription_cta['instance_id'] ?? 0) : 0) . '"' : ''; ?> class="flacso-oa-program-hero__button flacso-oa-program-hero__button--primary">
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
                                $modalidad_html = !empty($data['modalidad_html'])
                                    ? $data['modalidad_html']
                                    : '<p>' . esc_html__('Virtual.', 'flacso-uruguay') . '</p>';

                                $render_info_card(
                                    __('Modalidad', 'flacso-uruguay'),
                                    $modalidad_html
                                );

                                $duracion_html = $normalize_duracion_html($data['duracion_html'] ?? '', $data['duracion_meses'] ?? '');
                                if (!empty($duracion_html)) {
                                    $render_info_card(
                                        __('Duración', 'flacso-uruguay'),
                                        $duracion_html
                                    );
                                }

                                if (!empty($data['objetivos_html'])) {
                                    $render_info_card(
                                        __('Objetivos', 'flacso-uruguay'),
                                        $data['objetivos_html']
                                    );
                                }

                                if (!empty($data['perfil_ingreso_html'])) {
                                    $render_info_card(
                                        __('Perfil de ingreso', 'flacso-uruguay'),
                                        $data['perfil_ingreso_html']
                                    );
                                }

                                if (!empty($data['requisitos_ingreso_html'])) {
                                    $render_info_card(
                                        __('Requisitos de ingreso', 'flacso-uruguay'),
                                        $data['requisitos_ingreso_html']
                                    );
                                }

                                if (!empty($data['perfil_egreso_html'])) {
                                    $render_info_card(
                                        __('Perfil de egreso', 'flacso-uruguay'),
                                        $data['perfil_egreso_html']
                                    );
                                }

                                if (!empty($data['requisitos_egreso_html'])) {
                                    $render_info_card(
                                        __('Requisitos de egreso', 'flacso-uruguay'),
                                        $data['requisitos_egreso_html']
                                    );
                                }

                                if (!empty($data['titulos_certificaciones_html'])) {
                                    $render_info_card(
                                        __('Títulos y Certificaciones', 'flacso-uruguay'),
                                        $data['titulos_certificaciones_html']
                                    );
                                }

                                $documentos = !empty($data['documentos']) && is_string($data['documentos']) ? json_decode($data['documentos'], true) : [];

                                $cartamalla_pdf_url = !empty($documentos['cartamalla']['link']) ? trim((string) $documentos['cartamalla']['link']) : '';

                                if ($cartamalla_pdf_url) {
                                    if (function_exists('flacso_get_pdf_proxy_url')) {
                                        $proxied = flacso_get_pdf_proxy_url($cartamalla_pdf_url, 'Calendario y Malla Curricular');
                                        if ($proxied) $cartamalla_pdf_url = $proxied;
                                    }
                                    $combined_body = '<p style="margin-bottom:1rem;">' . esc_html__('Podés descargar el calendario de cursada y la malla curricular.', 'flacso-uruguay') . '</p>' .
                                                     '<a href="' . esc_url($cartamalla_pdf_url) . '" target="_blank" rel="noopener noreferrer" class="flacso-oa-link-btn flacso-meta-doc-link flacso-meta-doc-link--combined" style="display:inline-flex; align-items:center; gap:0.5rem;"><i class="bi bi-file-earmark-pdf"></i> ' . esc_html__('Ver Calendario y Malla (PDF)', 'flacso-uruguay') . '</a>';

                                    $render_info_card(
                                        __('Malla y Calendario Académico', 'flacso-uruguay'),
                                        $combined_body,
                                        'flacso-oa-info-card--wide',
                                        [
                                            'data-flacso-meta-doc-section' => 'MallaCalendarioSectionView',
                                            'data-flacso-meta-doc-layout' => 'combined_pdf',
                                        ]
                                    );
                                } else {
                                    $malla_pdf_url = !empty($documentos['malla']['link']) ? trim((string) $documentos['malla']['link']) : (!empty($data['malla_curricular']) ? trim((string) $data['malla_curricular']) : '');
                                    $calendario_pdf_url = !empty($documentos['calendario']['link']) ? trim((string) $documentos['calendario']['link']) : (!empty($data['calendario']) ? trim((string) $data['calendario']) : '');

                                    if ($malla_pdf_url && $calendario_pdf_url) {
                                        if (function_exists('flacso_get_pdf_proxy_url')) {
                                            $proxied_malla = flacso_get_pdf_proxy_url($malla_pdf_url, 'Malla curricular');
                                            if ($proxied_malla) $malla_pdf_url = $proxied_malla;

                                            $proxied_cal = flacso_get_pdf_proxy_url($calendario_pdf_url, 'Calendario Academico');
                                            if ($proxied_cal) $calendario_pdf_url = $proxied_cal;
                                        }
                                        $combined_body = '<div style="display:flex; flex-wrap:wrap; gap:1rem;">' .
                                                         '<a href="' . esc_url($malla_pdf_url) . '" target="_blank" rel="noopener noreferrer" class="flacso-oa-link-btn flacso-meta-doc-link flacso-meta-doc-link--malla" style="display:inline-flex; align-items:center; gap:0.5rem;"><i class="bi bi-file-earmark-pdf"></i> ' . esc_html__('Ver Malla Curricular (PDF)', 'flacso-uruguay') . '</a>' .
                                                         '<a href="' . esc_url($calendario_pdf_url) . '" target="_blank" rel="noopener noreferrer" class="flacso-oa-link-btn flacso-meta-doc-link flacso-meta-doc-link--calendario" style="display:inline-flex; align-items:center; gap:0.5rem;"><i class="bi bi-file-earmark-pdf"></i> ' . esc_html__('Ver Calendario (PDF)', 'flacso-uruguay') . '</a>' .
                                                         '</div>';

                                        $render_info_card(
                                            __('Malla y Calendario Académico', 'flacso-uruguay'),
                                            $combined_body,
                                            'flacso-oa-info-card--wide',
                                            [
                                                'data-flacso-meta-doc-section' => 'MallaCalendarioSectionView',
                                                'data-flacso-meta-doc-layout' => 'split_buttons',
                                            ]
                                        );
                                    } else {
                                        if ($malla_pdf_url) {
                                            if (function_exists('flacso_get_pdf_proxy_url')) {
                                                $proxied = flacso_get_pdf_proxy_url($malla_pdf_url, 'Malla curricular');
                                                if ($proxied) $malla_pdf_url = $proxied;
                                            }
                                            $malla_body = '<a href="' . esc_url($malla_pdf_url) . '" target="_blank" rel="noopener noreferrer" class="flacso-oa-link-btn flacso-meta-doc-link flacso-meta-doc-link--malla" style="display:inline-flex; align-items:center; gap:0.5rem;"><i class="bi bi-file-earmark-pdf"></i> ' . esc_html__('Ver Malla Curricular (PDF)', 'flacso-uruguay') . '</a>';

                                            $render_info_card(
                                                __('Malla Curricular', 'flacso-uruguay'),
                                                $malla_body,
                                                'flacso-oa-info-card--wide',
                                                [
                                                    'data-flacso-meta-doc-section' => 'MallaCurricularSectionView',
                                                    'data-flacso-meta-doc-layout' => 'single_malla',
                                                ]
                                            );
                                        }

                                        if ($calendario_pdf_url) {
                                            if (function_exists('flacso_get_pdf_proxy_url')) {
                                                $proxied = flacso_get_pdf_proxy_url($calendario_pdf_url, 'Calendario Academico');
                                                if ($proxied) $calendario_pdf_url = $proxied;
                                            }
                                            $calendario_body = '<a href="' . esc_url($calendario_pdf_url) . '" target="_blank" rel="noopener noreferrer" class="flacso-oa-link-btn flacso-meta-doc-link flacso-meta-doc-link--calendario" style="display:inline-flex; align-items:center; gap:0.5rem;"><i class="bi bi-file-earmark-pdf"></i> ' . esc_html__('Ver Calendario (PDF)', 'flacso-uruguay') . '</a>';

                                            $render_info_card(
                                                __('Calendario Académico', 'flacso-uruguay'),
                                                $calendario_body,
                                                'flacso-oa-info-card--wide',
                                                [
                                                    'data-flacso-meta-doc-section' => 'CalendarioAcademicoSectionView',
                                                    'data-flacso-meta-doc-layout' => 'single_calendario',
                                                ]
                                            );
                                        }
                                    }
                                }

                                $financiacion_html = !empty($data['financiacion_html'])
                                    ? (string) $data['financiacion_html']
                                    : '';

                                if ($financiacion_html === '') {
                                    $financiacion_html  = '<p>' . esc_html__('FLACSO ofrece financiación flexible, el monto de las cuotas puede variar dependiendo de las promociones que haya aprovechado, o el plan de pagos que se coordine con la Institución. Todos los posgrados pueden abonarse en cuotas, siguiendo un plan mensual de pagos que acompañan la cursada. No obstante, es posible extender los planes de pago en forma flexible, con valores de cuota a su alcance.', 'flacso-uruguay') . '</p>';
                                    $financiacion_html .= '<p>' . esc_html__('Quienes cursen desde fuera de Uruguay pueden pagar de forma segura a través de la plataforma de pago de la institución, mientras las personas que cursan en el país, disponen de otras vías para pagar.', 'flacso-uruguay') . '</p>';
                                    $financiacion_html .= '<p>' . esc_html__('Las becas para cursar en FLACSO Uruguay están sujetas a convenios inter institucionales y son limitadas por cohorte. Para obtener más información sobre las posibles becas disponibles puede comunicarse con la asistente académica.', 'flacso-uruguay') . '</p>';
                                }

                                $render_info_card(
                                    __('Financiación y becas', 'flacso-uruguay'),
                                    $financiacion_html,
                                    'flacso-oa-info-card--wide'
                                );
                                ?>
                            </div>
                        </div>
                    </section>

                    <section class="flacso-oa-cta-section">
                        <div class="flacso-oa-container">
                            <div class="flacso-oa-cta">
                                <p>
                                    <?php esc_html_e('Para realizar las postulaciones puede completar el formulario y en breve el personal de asistencia académica se pondrá en contacto.', 'flacso-uruguay'); ?>
                                </p>
                            </div>
                        </div>
                    </section>

                    <?php
                    // Recolectar y fusionar equipos para mantener retrocompatibilidad
                    $merged_equipos = [];

                    if (!empty($data['coordinacion_academica']) && is_array($data['coordinacion_academica'])) {
                        foreach ($data['coordinacion_academica'] as $coord) {
                            if (!empty($coord['docentes']) && is_array($coord['docentes'])) {
                                $merged_equipos[] = [
                                    'nombre' => !empty($coord['rol']) ? $coord['rol'] : __('Coordinación académica', 'flacso-uruguay'),
                                    'descripcion' => $coord['descripcion'] ?? '',
                                    'docentes' => $coord['docentes'],
                                    'importancia' => '1' // Por defecto Nivel 1
                                ];
                            }
                        }
                    }

                    if (!empty($data['equipos']) && is_array($data['equipos'])) {
                        foreach ($data['equipos'] as $eq) {
                            if (!empty($eq['docentes']) && is_array($eq['docentes'])) {
                                $merged_equipos[] = [
                                    'nombre' => $eq['nombre'] ?? '',
                                    'descripcion' => $eq['descripcion'] ?? '',
                                    'docentes' => $eq['docentes'],
                                    'importancia' => strval($eq['importancia'] ?? '3')
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
                                        ?>
                                        <div class="flacso-oa-team-subgroup flacso-oa-team-subgroup--nivel-<?php echo esc_attr($nivel); ?>">
                                            <?php if (!empty($grupo['nombre'])) : ?>
                                                <h4 class="flacso-oa-team-subgroup__title">
                                                    <?php echo esc_html($grupo['nombre']); ?>
                                                </h4>
                                            <?php endif; ?>

                                            <?php if (!empty($grupo['descripcion'])) : ?>
                                                <div class="flacso-oa-team-subgroup__description">
                                                    <?php echo wpautop(wp_kses_post($grupo['descripcion'])); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
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
                    if (class_exists('Oferta_Seminarios_Integration')) :
                        $seminarios_all = Oferta_Seminarios_Integration::get_programa_seminarios_data($post_id);
                        $seminarios = [];
                        $hoy = new DateTimeImmutable('today', wp_timezone());

                        if (is_array($seminarios_all)) {
                            foreach ($seminarios_all as $seminario) {
                                if (empty($seminario['fecha_inicio'])) {
                                    continue;
                                }

                                $inicio_obj = DateTimeImmutable::createFromFormat('Y-m-d|', $seminario['fecha_inicio'], wp_timezone());

                                if (!$inicio_obj) {
                                    continue;
                                }

                                $dias_hasta_inicio = (int) floor(($inicio_obj->getTimestamp() - $hoy->getTimestamp()) / DAY_IN_SECONDS);
                                $is_upcoming = $dias_hasta_inicio >= 0;
                                $is_started_recent = $dias_hasta_inicio < 0 && $dias_hasta_inicio >= -7;

                                if ($is_upcoming || $is_started_recent) {
                                    $seminarios[] = $seminario;
                                }
                            }
                        }

                        usort($seminarios, static function ($a, $b) {
                            return strcmp($a['fecha_inicio'] ?? '', $b['fecha_inicio'] ?? '');
                        });
                        ?>

                        <?php
                        if ( ! empty( $seminarios ) ) {
                            get_template_part(
                                'template-parts/oferta/seminarios-vinculados',
                                null,
                                array(
                                    'post_id'    => $post_id,
                                    'seminarios' => $seminarios,
                                )
                            );
                        }
                        ?>
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
            content_name: <?php echo wp_json_encode((string) get_the_title()); ?>,
            content_category: 'oferta_academica',
            content_ids: ['oferta-' + <?php echo wp_json_encode((string) get_the_ID()); ?>],
            flacso_stage: 'pagina_oferta'
        });
    } catch (e) {}
})();

(function() {
    if (typeof window.flacsoMetaTrackCustom !== 'function') return;

    var ofertaTitle = <?php echo wp_json_encode((string) get_the_title()); ?>;
    var ofertaId = <?php echo wp_json_encode((string) get_the_ID()); ?>;
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
<?php
get_footer();
