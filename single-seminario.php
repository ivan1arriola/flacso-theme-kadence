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

<style>
    .flacso-seminario-page {
        --seminario-primary: var(--global-palette1, #123b7a);
        --seminario-primary-dark: #0d2f63;
        --seminario-accent: var(--global-palette2, #489ddd);
        --seminario-text: var(--global-palette4, #263342);
        --seminario-muted: var(--global-palette5, #687386);
        --seminario-surface: #ffffff;
        --seminario-surface-soft: #f6f9fc;
        --seminario-border: rgba(18, 59, 122, 0.13);
        --seminario-shadow: 0 16px 44px rgba(25, 46, 78, 0.10);
        --seminario-radius: 22px;
        color: var(--seminario-text);
        overflow-x: clip;
    }

    .flacso-seminario-page *,
    .flacso-seminario-page *::before,
    .flacso-seminario-page *::after {
        box-sizing: border-box;
    }

    .flacso-seminario-shell {
        width: min(1180px, calc(100% - 32px));
        margin-inline: auto;
    }

    .seminario-breadcrumb-wrap {
        padding: 24px 0 14px;
    }

    .seminario-breadcrumb {
        display: flex;
        align-items: center;
        gap: 8px;
        margin: 0;
        padding: 0;
        list-style: none;
        color: var(--seminario-muted);
        font-size: 0.88rem;
        overflow-x: auto;
        white-space: nowrap;
        scrollbar-width: none;
    }

    .seminario-breadcrumb::-webkit-scrollbar {
        display: none;
    }

    .seminario-breadcrumb li {
        display: inline-flex;
        align-items: center;
        min-width: max-content;
    }

    .seminario-breadcrumb li:not(:last-child)::after {
        content: "/";
        margin-left: 8px;
        color: #a5afbd;
    }

    .seminario-breadcrumb a {
        color: var(--seminario-primary);
        text-decoration: none;
    }

    .seminario-breadcrumb a:hover,
    .seminario-breadcrumb a:focus-visible {
        text-decoration: underline;
    }

    .seminario-offer-tags {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-top: 14px;
    }

    .seminario-offer-tag {
        display: inline-flex;
        align-items: center;
        min-height: 30px;
        padding: 6px 11px;
        border: 1px solid var(--seminario-border);
        border-radius: 999px;
        background: var(--seminario-surface-soft);
        color: var(--seminario-primary);
        font-size: 0.78rem;
        font-weight: 700;
        line-height: 1.2;
    }

    .seminario-parent-notice {
        display: flex;
        gap: 12px;
        align-items: flex-start;
        margin: 8px 0 24px;
        padding: 14px 16px;
        border: 1px solid rgba(72, 157, 221, 0.28);
        border-radius: 16px;
        background: #f0f8ff;
        color: #23486f;
        font-size: 0.94rem;
        line-height: 1.5;
    }

    .seminario-parent-notice i {
        flex: 0 0 auto;
        margin-top: 2px;
        color: var(--seminario-accent);
        font-size: 1.15rem;
    }

    .seminario-parent-notice a {
        color: var(--seminario-primary);
        font-weight: 800;
    }

    .seminario-hero {
        display: grid;
        grid-template-columns: minmax(300px, 0.82fr) minmax(0, 1.18fr);
        gap: clamp(34px, 5vw, 72px);
        align-items: center;
        padding: 24px 0 52px;
    }

    .seminario-hero-media-wrap {
        width: min(100%, 470px);
        justify-self: center;
    }

    .seminario-hero-media {
        position: relative;
        width: 100%;
        aspect-ratio: 4 / 5;
        overflow: hidden;
        border-radius: 26px;
        background: #eaf0f6;
        box-shadow: 0 24px 64px rgba(25, 46, 78, 0.18);
        isolation: isolate;
    }

    .seminario-hero-media::after {
        content: "";
        position: absolute;
        inset: 0;
        pointer-events: none;
        border: 1px solid rgba(255, 255, 255, 0.45);
        border-radius: inherit;
        z-index: 2;
    }

    .seminario-hero-media img {
        display: block;
        width: 100%;
        height: 100%;
        object-fit: cover;
        object-position: center;
    }

    .seminario-hero-placeholder {
        display: grid;
        place-items: center;
        width: 100%;
        height: 100%;
        background: linear-gradient(145deg, #edf4fa, #dce9f5);
        color: var(--seminario-primary);
        font-size: 3rem;
    }

    .seminario-kicker {
        margin: 0 0 12px;
        color: var(--seminario-primary);
        font-size: 0.8rem;
        font-weight: 800;
        letter-spacing: 0.095em;
        text-transform: uppercase;
    }

    .seminario-title {
        max-width: 850px;
        margin: 0;
        color: var(--seminario-primary);
        font-size: clamp(2rem, 4vw, 3.65rem);
        font-weight: 800;
        letter-spacing: -0.035em;
        line-height: 1.03;
        text-wrap: balance;
    }

    .seminario-quick-meta {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 10px;
        margin-top: 26px;
    }

    .seminario-quick-item {
        min-width: 0;
        padding: 13px 14px;
        border: 1px solid var(--seminario-border);
        border-radius: 15px;
        background: var(--seminario-surface-soft);
    }

    .seminario-quick-label {
        display: flex;
        align-items: center;
        gap: 7px;
        margin-bottom: 4px;
        color: var(--seminario-muted);
        font-size: 0.74rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.035em;
    }

    .seminario-quick-label i {
        color: var(--seminario-accent);
        font-size: 0.88rem;
    }

    .seminario-quick-value {
        color: var(--seminario-text);
        font-size: 0.91rem;
        font-weight: 750;
        line-height: 1.35;
        overflow-wrap: anywhere;
    }

    .seminario-hero-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
        align-items: center;
        margin-top: 28px;
    }

    .seminario-btn-primary,
    .seminario-btn-secondary {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 9px;
        min-height: 50px;
        padding: 13px 20px;
        border-radius: 13px;
        font-weight: 800;
        text-decoration: none;
        transition: transform .18s ease, box-shadow .18s ease, background-color .18s ease;
    }

    .seminario-btn-primary {
        border: 1px solid var(--seminario-primary);
        background: var(--seminario-primary);
        color: #fff;
        box-shadow: 0 12px 28px rgba(18, 59, 122, 0.22);
    }

    .seminario-btn-primary:hover,
    .seminario-btn-primary:focus-visible {
        background: var(--seminario-primary-dark);
        color: #fff;
        transform: translateY(-1px);
        box-shadow: 0 15px 34px rgba(18, 59, 122, 0.27);
    }

    .seminario-btn-secondary {
        border: 1px solid var(--seminario-border);
        background: #fff;
        color: var(--seminario-primary);
    }

    .seminario-btn-secondary:hover,
    .seminario-btn-secondary:focus-visible {
        background: var(--seminario-surface-soft);
        color: var(--seminario-primary);
    }

    .seminario-exclusive-note {
        display: flex;
        align-items: flex-start;
        gap: 10px;
        width: min(100%, 620px);
        margin-top: 24px;
        padding: 14px 16px;
        border-radius: 15px;
        background: #f0f8ff;
        color: #23486f;
        font-size: 0.92rem;
        line-height: 1.5;
    }

    .seminario-exclusive-note i {
        margin-top: 2px;
        color: var(--seminario-accent);
    }

    .seminario-section {
        padding: 46px 0;
        border-top: 1px solid var(--seminario-border);
    }

    .seminario-section:first-of-type {
        border-top: 0;
    }

    .seminario-section-header {
        display: flex;
        align-items: flex-end;
        justify-content: space-between;
        gap: 20px;
        margin-bottom: 24px;
    }

    .seminario-section-title {
        margin: 0;
        color: var(--seminario-primary);
        font-size: clamp(1.55rem, 2.3vw, 2.15rem);
        font-weight: 800;
        letter-spacing: -0.02em;
        line-height: 1.12;
    }

    .seminario-info-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 16px;
    }

    .seminario-info-card {
        min-width: 0;
        padding: 20px;
        border: 1px solid var(--seminario-border);
        border-radius: 18px;
        background: var(--seminario-surface);
        box-shadow: 0 7px 24px rgba(25, 46, 78, 0.055);
    }

    .seminario-info-card-head {
        display: flex;
        align-items: center;
        gap: 11px;
        margin-bottom: 16px;
    }

    .seminario-info-icon {
        display: grid;
        place-items: center;
        flex: 0 0 38px;
        width: 38px;
        height: 38px;
        border-radius: 11px;
        background: rgba(72, 157, 221, 0.11);
        color: var(--seminario-primary);
        font-size: 1rem;
    }

    .seminario-info-card h3 {
        margin: 0;
        color: var(--seminario-primary);
        font-size: 1rem;
        font-weight: 800;
        line-height: 1.25;
    }

    .seminario-detail-list {
        display: grid;
        gap: 11px;
        margin: 0;
    }

    .seminario-detail-row {
        display: grid;
        gap: 2px;
    }

    .seminario-detail-label {
        color: var(--seminario-muted);
        font-size: 0.78rem;
        font-weight: 650;
    }

    .seminario-detail-value {
        color: var(--seminario-text);
        font-size: 0.94rem;
        font-weight: 750;
        line-height: 1.45;
    }

    .seminario-info-card p:last-child,
    .seminario-info-card ul:last-child,
    .seminario-info-card div:last-child {
        margin-bottom: 0;
    }

    .seminario-meetings {
        display: grid;
        gap: 10px;
    }

    .seminario-meeting {
        display: grid;
        grid-template-columns: 1fr auto;
        gap: 8px 14px;
        padding-bottom: 10px;
        border-bottom: 1px solid var(--seminario-border);
    }

    .seminario-meeting:last-child {
        padding-bottom: 0;
        border-bottom: 0;
    }

    .seminario-meeting-date {
        color: var(--seminario-primary);
        font-size: 0.9rem;
        font-weight: 800;
        line-height: 1.35;
    }

    .seminario-meeting-time {
        color: var(--seminario-text);
        font-size: 0.87rem;
        font-weight: 750;
        white-space: nowrap;
    }

    .seminario-meeting-platform {
        grid-column: 1 / -1;
        color: var(--seminario-muted);
        font-size: 0.8rem;
    }

    .seminario-async-note {
        margin-bottom: 13px;
        padding: 11px 12px;
        border-radius: 12px;
        background: #f0f8ff;
        color: #23486f;
        font-size: 0.86rem;
        line-height: 1.5;
    }

    .seminario-investment-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 16px;
    }

    .seminario-investment-card {
        position: relative;
        overflow: hidden;
        padding: 24px;
        border: 1px solid var(--seminario-border);
        border-radius: 20px;
        background: #fff;
        box-shadow: 0 8px 26px rgba(25, 46, 78, 0.06);
    }

    .seminario-investment-card.is-discount {
        border-color: rgba(72, 157, 221, 0.35);
        background: linear-gradient(145deg, #f7fbff, #ffffff);
    }

    .seminario-investment-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        margin-bottom: 18px;
    }

    .seminario-investment-title {
        margin: 0;
        color: var(--seminario-primary);
        font-size: 1.05rem;
        font-weight: 800;
    }

    .seminario-discount-badge {
        padding: 5px 9px;
        border-radius: 999px;
        background: var(--seminario-primary);
        color: #fff;
        font-size: 0.72rem;
        font-weight: 850;
        letter-spacing: .03em;
    }

    .seminario-amounts {
        display: flex;
        flex-wrap: wrap;
        gap: 12px 22px;
    }

    .seminario-amount {
        display: grid;
        gap: 2px;
    }

    .seminario-amount-label {
        color: var(--seminario-muted);
        font-size: 0.72rem;
        font-weight: 750;
        letter-spacing: .04em;
    }

    .seminario-amount-value {
        color: var(--seminario-text);
        font-size: clamp(1.45rem, 3vw, 2rem);
        font-weight: 850;
        letter-spacing: -0.025em;
        line-height: 1.05;
    }

    .seminario-prose {
        max-width: 900px;
        color: var(--seminario-text);
        font-size: 1rem;
        line-height: 1.72;
    }

    .seminario-prose > :first-child {
        margin-top: 0;
    }

    .seminario-prose > :last-child {
        margin-bottom: 0;
    }

    .seminario-prose p,
    .seminario-prose ul,
    .seminario-prose ol {
        margin-bottom: 1rem;
    }

    .seminario-prose a {
        color: var(--seminario-primary);
        font-weight: 650;
        text-underline-offset: 2px;
    }

    .seminario-objectives {
        display: grid;
        gap: 12px;
        max-width: 920px;
        margin: 0;
        padding: 0;
        list-style: none;
    }

    .seminario-objective {
        display: grid;
        grid-template-columns: 24px minmax(0, 1fr);
        gap: 11px;
        align-items: start;
        padding: 15px 16px;
        border: 1px solid var(--seminario-border);
        border-radius: 14px;
        background: #fff;
        line-height: 1.6;
    }

    .seminario-objective i {
        margin-top: 3px;
        color: var(--seminario-accent);
    }

    .seminario-units {
        display: grid;
        gap: 16px;
        counter-reset: seminario-unit;
    }

    .seminario-unit {
        position: relative;
        padding: 22px 22px 22px 76px;
        border: 1px solid var(--seminario-border);
        border-radius: 18px;
        background: #fff;
        counter-increment: seminario-unit;
    }

    .seminario-unit::before {
        content: counter(seminario-unit, decimal-leading-zero);
        position: absolute;
        top: 22px;
        left: 22px;
        color: rgba(18, 59, 122, 0.34);
        font-size: 0.82rem;
        font-weight: 850;
        letter-spacing: .04em;
    }

    .seminario-unit h3,
    .seminario-unit h4,
    .seminario-unit h5 {
        margin-top: 0;
        color: var(--seminario-primary);
    }

    .seminario-component-list {
        display: grid;
        gap: 18px;
    }

    .seminario-component {
        padding: clamp(20px, 3vw, 30px);
        border: 1px solid var(--seminario-border);
        border-radius: 20px;
        background: #fff;
        box-shadow: 0 7px 24px rgba(25, 46, 78, 0.05);
    }

    .seminario-component-title {
        margin: 0 0 15px;
        color: var(--seminario-primary);
        font-size: clamp(1.2rem, 2vw, 1.55rem);
        font-weight: 800;
        line-height: 1.25;
    }

    .seminario-component-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 8px 18px;
        margin-bottom: 20px;
        color: var(--seminario-muted);
        font-size: 0.9rem;
    }

    .seminario-component-meta span {
        display: inline-flex;
        align-items: flex-start;
        gap: 7px;
    }

    .seminario-component h4 {
        margin: 23px 0 10px;
        color: var(--seminario-primary);
        font-size: 1rem;
        font-weight: 800;
    }

    .seminario-component .seminario-unit {
        margin-top: 12px;
        background: var(--seminario-surface-soft);
    }

    .seminario-teachers-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 16px;
    }

    .seminario-teachers-grid--component {
        margin-top: 12px;
    }

    .seminario-component .seminario-teacher {
        background: var(--seminario-surface-soft);
        box-shadow: none;
    }

    .seminario-teacher {
        display: grid;
        grid-template-columns: 82px minmax(0, 1fr);
        gap: 18px;
        align-items: start;
        padding: 20px;
        border: 1px solid var(--seminario-border);
        border-radius: 18px;
        background: #fff;
        box-shadow: 0 7px 24px rgba(25, 46, 78, 0.05);
    }

    .seminario-teacher-avatar {
        width: 82px;
        aspect-ratio: 1;
        overflow: hidden;
        border-radius: 50%;
        background: #e9f0f7;
    }

    .seminario-teacher-avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .seminario-teacher-placeholder {
        display: grid;
        place-items: center;
        width: 100%;
        height: 100%;
        background: linear-gradient(145deg, #edf4fa, #dce9f5);
        color: var(--seminario-primary);
        font-size: 1.15rem;
        font-weight: 850;
    }

    .seminario-teacher-name {
        margin: 2px 0 3px;
        color: var(--seminario-primary);
        font-size: 1.06rem;
        font-weight: 800;
        line-height: 1.25;
    }

    .seminario-teacher-prefix {
        margin: 0 0 10px;
        color: var(--seminario-muted);
        font-size: 0.86rem;
    }

    .seminario-teacher-bio {
        color: var(--seminario-text);
        font-size: 0.9rem;
        line-height: 1.58;
    }

    .seminario-teacher-bio > :last-child {
        margin-bottom: 0;
    }

    .seminario-final-cta {
        position: relative;
        overflow: hidden;
        display: grid;
        grid-template-columns: minmax(0, 1fr) auto;
        gap: 26px;
        align-items: center;
        margin: 34px 0 58px;
        padding: clamp(26px, 4vw, 42px);
        border-radius: 24px;
        background: linear-gradient(135deg, var(--seminario-primary-dark), var(--seminario-primary) 58%, #2968a5);
        color: #fff;
        box-shadow: var(--seminario-shadow);
    }

    .seminario-final-cta::after {
        content: "";
        position: absolute;
        width: 240px;
        height: 240px;
        right: -95px;
        top: -110px;
        border-radius: 50%;
        background: rgba(255,255,255,.08);
        pointer-events: none;
    }

    .seminario-final-cta h2 {
        margin: 0 0 7px;
        color: #fff;
        font-size: clamp(1.55rem, 3vw, 2.3rem);
        font-weight: 850;
        letter-spacing: -0.025em;
    }

    .seminario-final-cta p {
        max-width: 680px;
        margin: 0;
        color: rgba(255,255,255,.86);
        line-height: 1.55;
    }

    .seminario-final-cta .seminario-btn-primary {
        position: relative;
        z-index: 1;
        border-color: #fff;
        background: #fff;
        color: var(--seminario-primary);
        box-shadow: none;
        white-space: nowrap;
    }

    .seminario-final-cta .seminario-btn-primary:hover,
    .seminario-final-cta .seminario-btn-primary:focus-visible {
        background: #f4f8fb;
        color: var(--seminario-primary-dark);
    }

    @media (max-width: 991.98px) {
        .seminario-hero {
            grid-template-columns: minmax(260px, .85fr) minmax(0, 1.15fr);
            gap: 30px;
        }

        .seminario-title {
            font-size: clamp(2rem, 5vw, 3rem);
        }

        .seminario-quick-meta {
            grid-template-columns: 1fr;
        }

        .seminario-info-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .seminario-teachers-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 767.98px) {
        .flacso-seminario-shell {
            width: min(100% - 24px, 680px);
        }

        .seminario-breadcrumb-wrap {
            padding-top: 14px;
        }

        .seminario-breadcrumb {
            font-size: 0.8rem;
        }

        .seminario-parent-notice {
            margin-bottom: 18px;
        }

        .seminario-hero {
            grid-template-columns: 1fr;
            gap: 24px;
            padding: 12px 0 34px;
        }

        .seminario-hero-media-wrap {
            width: min(100%, 520px);
        }

        .seminario-hero-media {
            border-radius: 20px;
            box-shadow: 0 16px 42px rgba(25, 46, 78, 0.16);
        }

        .seminario-kicker {
            margin-bottom: 9px;
            font-size: 0.75rem;
        }

        .seminario-title {
            font-size: clamp(1.85rem, 9vw, 2.7rem);
            line-height: 1.06;
        }

        .seminario-quick-meta {
            grid-template-columns: repeat(2, minmax(0, 1fr));
            margin-top: 20px;
        }

        .seminario-quick-item:last-child:nth-child(odd) {
            grid-column: 1 / -1;
        }

        .seminario-hero-actions {
            margin-top: 20px;
        }

        .seminario-btn-primary,
        .seminario-btn-secondary {
            width: 100%;
        }

        .seminario-section {
            padding: 34px 0;
        }

        .seminario-section-header {
            margin-bottom: 18px;
        }

        .seminario-section-title {
            font-size: 1.55rem;
        }

        .seminario-info-grid,
        .seminario-investment-grid {
            grid-template-columns: 1fr;
            gap: 12px;
        }

        .seminario-info-card,
        .seminario-investment-card {
            padding: 17px;
            border-radius: 16px;
        }

        .seminario-prose {
            font-size: .97rem;
            line-height: 1.67;
        }

        .seminario-unit {
            padding: 18px;
        }

        .seminario-unit::before {
            position: static;
            display: block;
            margin-bottom: 8px;
        }

        .seminario-component {
            border-radius: 17px;
        }

        .seminario-teacher {
            grid-template-columns: 64px minmax(0, 1fr);
            gap: 14px;
            padding: 16px;
        }

        .seminario-teacher-avatar {
            width: 64px;
        }

        .seminario-final-cta {
            grid-template-columns: 1fr;
            gap: 20px;
            margin: 24px 0 36px;
            border-radius: 20px;
        }

        .seminario-final-cta .seminario-btn-primary {
            width: 100%;
            white-space: normal;
            text-align: center;
        }
    }

    @media (max-width: 420px) {
        .flacso-seminario-shell {
            width: calc(100% - 20px);
        }

        .seminario-quick-meta {
            grid-template-columns: 1fr;
        }

        .seminario-quick-item:last-child:nth-child(odd) {
            grid-column: auto;
        }

        .seminario-meeting {
            grid-template-columns: 1fr;
        }

        .seminario-meeting-time,
        .seminario-meeting-platform {
            grid-column: 1;
        }
    }

    @media (prefers-reduced-motion: reduce) {
        .flacso-seminario-page *,
        .flacso-seminario-page *::before,
        .flacso-seminario-page *::after {
            scroll-behavior: auto !important;
            transition: none !important;
        }
    }
</style>

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
                        <a href="<?php echo esc_url($preinscripcion_url); ?>" class="seminario-btn-primary">
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

        <section id="informacion" class="seminario-section" aria-labelledby="seminario-info-title">
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
            <section class="seminario-section" aria-labelledby="seminario-inversion-title">
                <div class="seminario-section-header">
                    <h2 id="seminario-inversion-title" class="seminario-section-title">Inversión</h2>
                </div>

                <div class="seminario-investment-grid">
                    <?php if ($has_valor_general) : ?>
                        <article class="seminario-investment-card">
                            <div class="seminario-investment-head">
                                <h3 class="seminario-investment-title">Valor general</h3>
                            </div>
                            <div class="seminario-amounts">
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
                        <article class="seminario-investment-card is-discount">
                            <div class="seminario-investment-head">
                                <h3 class="seminario-investment-title">Comunidad FLACSO</h3>
                                <span class="seminario-discount-badge">15% OFF</span>
                            </div>
                            <div class="seminario-amounts">
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

        <?php if (!empty($meta['presentacion_seminario'])) : ?>
            <section class="seminario-section" aria-labelledby="seminario-presentacion-title">
                <div class="seminario-section-header">
                    <h2 id="seminario-presentacion-title" class="seminario-section-title">Presentación del seminario</h2>
                </div>
                <div class="seminario-prose"><?php echo wp_kses_post($meta['presentacion_seminario']); ?></div>
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

                            <div style="margin-top:22px;">
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
                <a href="<?php echo esc_url($preinscripcion_url); ?>" class="seminario-btn-primary">
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
