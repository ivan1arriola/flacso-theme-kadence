<?php
/**
 * Template para la Carta de Presentación de una Oferta Académica.
 *
 * Endpoint: /formacion/{tipo}/{slug}/carta/
 * Consume el View Model canónico de FLACSO Uruguay.
 * Integra de forma dinámica el color de presentación de la oferta académica.
 *
 * @package kadence-child-flacso
 */

if (!defined('ABSPATH')) {
    exit;
}

// Prevenir la indexación de las cartas de presentación en buscadores
if (!headers_sent()) {
    header('X-Robots-Tag: noindex, nofollow', true);
}
add_action('wp_head', static function () {
    echo '<meta name="robots" content="noindex, nofollow">' . "\n";
}, 1);

// Encolar estilos dedicados de la carta
wp_enqueue_style(
    'flacso-single-carta',
    get_stylesheet_directory_uri() . '/assets/css/single-carta.css',
    ['chld_thm_cfg_child'],
    flacso_child_asset_version('assets/css/single-carta.css')
);

wp_enqueue_style(
    'flacso-single-carta-editorial',
    get_stylesheet_directory_uri() . '/assets/css/single-carta-editorial.css',
    ['flacso-single-carta'],
    flacso_child_asset_version('assets/css/single-carta-editorial.css')
);

if (!wp_style_is('bootstrap-icons', 'enqueued')) {
    wp_enqueue_style(
        'bootstrap-icons',
        'https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css',
        [],
        '1.11.3'
    );
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
$titulo        = (string) $context['title'];
$tipo_oferta   = (string) $context['type_label'];
$type_slug     = (string) $context['type'];
$academic      = $context['academic'] ?? [];
$instance      = $context['instance'] ?? [];
$registration  = $context['registration'] ?? [];
$thumbnail_url = (string) ($context['thumbnail_url'] ?? '');

$is_maestria        = $type_slug === 'maestria' || stripos($tipo_oferta, 'maestr') !== false;
$es_especializacion = $type_slug === 'especializacion' || stripos($tipo_oferta, 'especializa') !== false;
$is_diplomado       = in_array($type_slug, ['diplomado', 'diploma'], true) || stripos($tipo_oferta, 'diplom') !== false;

// Resolver color de la oferta académica
$raw_color = '';
if (!empty($academic['color_principal']) && is_string($academic['color_principal'])) {
    $raw_color = trim($academic['color_principal']);
} elseif (!empty($academic['colores_presentacion']['principal']) && is_string($academic['colores_presentacion']['principal'])) {
    $raw_color = trim($academic['colores_presentacion']['principal']);
} elseif (!empty($context['color_principal']) && is_string($context['color_principal'])) {
    $raw_color = trim($context['color_principal']);
} else {
    $meta_colores = get_post_meta($post_id, 'colores_presentacion', true);
    if (is_array($meta_colores) && !empty($meta_colores['principal']) && is_string($meta_colores['principal'])) {
        $raw_color = trim($meta_colores['principal']);
    }
}

if ($raw_color !== '' && preg_match('/^#[0-9a-fA-F]{6}$/', $raw_color)) {
    $brand_hex = strtolower($raw_color);
} else {
    $brand_hex = '#16396f'; // Azul institucional FLACSO
}

// Calcular derivadas de color para gradientes, sombras y contrastes armónicos
$brand_r = hexdec(substr($brand_hex, 1, 2));
$brand_g = hexdec(substr($brand_hex, 3, 2));
$brand_b = hexdec(substr($brand_hex, 5, 2));

$dark_r = max(0, min(255, (int) round($brand_r * 0.70)));
$dark_g = max(0, min(255, (int) round($brand_g * 0.70)));
$dark_b = max(0, min(255, (int) round($brand_b * 0.70)));
$brand_dark = sprintf('#%02x%02x%02x', $dark_r, $dark_g, $dark_b);

$deep_r = max(0, min(255, (int) round($brand_r * 0.38)));
$deep_g = max(0, min(255, (int) round($brand_g * 0.38)));
$deep_b = max(0, min(255, (int) round($brand_b * 0.38)));
$brand_deep = sprintf('#%02x%02x%02x', $deep_r, $deep_g, $deep_b);

$light_r = max(0, min(255, (int) round($brand_r + (255 - $brand_r) * 0.30)));
$light_g = max(0, min(255, (int) round($brand_g + (255 - $brand_g) * 0.30)));
$light_b = max(0, min(255, (int) round($brand_b + (255 - $brand_b) * 0.30)));
$brand_light = sprintf('#%02x%02x%02x', $light_r, $light_g, $light_b);

$luminance = (0.299 * $brand_r + 0.587 * $brand_g + 0.114 * $brand_b) / 255;
$brand_contrast = $luminance > 0.65 ? '#102b56' : '#ffffff';

$inline_brand_styles = sprintf(
    '--fc-brand: %s; --fc-brand-rgb: %d, %d, %d; --fc-brand-dark: %s; --fc-brand-deep: %s; --fc-brand-light: %s; --fc-brand-contrast: %s;',
    esc_attr($brand_hex),
    $brand_r,
    $brand_g,
    $brand_b,
    esc_attr($brand_dark),
    esc_attr($brand_deep),
    esc_attr($brand_light),
    esc_attr($brand_contrast)
);

$abreviacion      = trim((string) ($academic['abreviacion'] ?? ''));
$cohorte_label    = trim((string) ($instance['label'] ?? ''));
$anio             = !empty($instance['anio_inicio']) ? (string) $instance['anio_inicio'] : (!empty($instance['fecha_inicio']) ? substr((string) $instance['fecha_inicio'], 0, 4) : wp_date('Y'));
$mostrar_instancias_presenciales = !empty($academic['carta_instancias_presenciales']);

$reconocido_mec     = !empty($academic['reconocido_mec']);
$reconocimiento_int = !empty($academic['reconocimiento_internacional']);
$reconocimiento_red_flacso = !empty($academic['reconocimiento_red_flacso']);
$mostrar_convenio_iin = !empty($academic['convenio_iin_oea']);

$arr_menciones           = array_values(array_filter((array) ($academic['menciones'] ?? [])));
$arr_orientaciones       = array_values(array_filter((array) ($academic['orientaciones'] ?? [])));
$arr_titulos_intermedios = array_values(array_filter((array) ($academic['titulos_intermedios'] ?? [])));

$url_inscripcion        = (string) ($registration['url'] ?? '');
if ($url_inscripcion === '') {
    $url_inscripcion = 'https://preinscripciones.flacso.edu.uy/';
}
$inscripciones_abiertas = true;

// Fechas y datos clave
$proximo_inicio = trim((string) ($instance['fechas_formateadas'] ?? ''));
if ($proximo_inicio === '' && !empty($instance['fecha_inicio'])) {
    $proximo_inicio = function_exists('flacso_theme_format_date_es')
        ? flacso_theme_format_date_es((string) $instance['fecha_inicio'])
        : (string) $instance['fecha_inicio'];
}
if ($proximo_inicio === '') {
    $proximo_inicio = __('A definir', 'flacso-uruguay');
}

$duracion_meses_val = $academic['duracion_meses'] ?? '';
$duracion = $duracion_meses_val !== '' && function_exists('flacso_theme_format_duration_months')
    ? flacso_theme_format_duration_months($duracion_meses_val)
    : __('A definir', 'flacso-uruguay');

$raw_modalidad = trim((string) ($instance['modalidad'] ?? ''));
if ($raw_modalidad === '') {
    $raw_modalidad = trim((string) ($instance['modalidad_descripcion'] ?? ''));
}
$modalidad_texto = trim(wp_strip_all_tags($raw_modalidad));
if ($modalidad_texto === '' || mb_strlen($modalidad_texto) > 50) {
    // La existencia de actividades solo presenciales no determina la
    // modalidad general (puede ser virtual, presencial o semipresencial).
    $modalidad = __('Modalidad a confirmar', 'flacso-uruguay');
} else {
    $modalidad = $modalidad_texto;
}

if (!empty($instance['modalidad_descripcion']) && empty($academic['modalidad_html'])) {
    $academic['modalidad_html'] = $instance['modalidad_descripcion'];
}

$carta_cta_titulo = __('Comenzá el año cursando un posgrado en FLACSO Uruguay', 'flacso-uruguay');

// Tabla de precios
$tabla_precio = $instance['tabla_precio'] ?? null;
$precios_filas = [];
$precios_nota = '';
$mostrar_precios_dolares = true;

if (!empty($tabla_precio) && is_array($tabla_precio)) {
    $precios_filas = is_array($tabla_precio['filas'] ?? null) ? $tabla_precio['filas'] : [];
    $precios_nota  = (string) ($tabla_precio['nota'] ?? '');
    $mostrar_precios_dolares = !empty($tabla_precio['mostrar_dolares']);
}

// Helper para determinante de artículo
$determinante_oferta = static function(string $tit): string {
    $primera = mb_strtolower(trim(strtok($tit, ' ')));
    return in_array($primera, ['maestría', 'maestria', 'especialización', 'especializacion', 'licenciatura', 'diplomatura'], true) ? 'de la' : 'del';
};

// Resolver asistente académico
$asistente_docente_id = absint($academic['asistente_academica_docente_id'] ?? 0);
$asistente_rol        = trim((string) ($academic['asistente_academica_rol'] ?? ''));
$asistente_correo     = trim((string) ($academic['asistente_academica_correo'] ?? ''));

$asistente_docente = null;
if ($asistente_docente_id > 0) {
    $asistente_docente = get_post($asistente_docente_id);
}

if (!$asistente_docente && !empty($academic['equipos']) && is_array($academic['equipos'])) {
    foreach ($academic['equipos'] as $eq) {
        $nombre_eq = mb_strtolower((string) ($eq['nombre'] ?? ''));
        if ((strpos($nombre_eq, 'asistent') !== false || strpos($nombre_eq, 'coordinaci') !== false) && !empty($eq['docentes'])) {
            $first_doc = is_array($eq['docentes'][0]) ? ($eq['docentes'][0]['id'] ?? 0) : $eq['docentes'][0];
            if ($first_doc) {
                $asistente_docente = get_post(absint($first_doc));
                if (empty($asistente_rol)) {
                    $asistente_rol = (string) ($eq['nombre'] ?? '');
                }
                break;
            }
        }
    }
}

$asistente_nombre_completo = '';
$asistente_foto_url = '';
if ($asistente_docente && $asistente_docente->post_type === 'docente') {
    $asist_meta = get_post_meta($asistente_docente->ID);
    $pref_abrev = !empty($asist_meta['prefijo_abrev'][0]) ? trim((string) $asist_meta['prefijo_abrev'][0]) : '';
    $doc_titulo = get_the_title($asistente_docente->ID);
    if (function_exists('dp_strip_prefix_from_name')) {
        $doc_titulo = dp_strip_prefix_from_name($doc_titulo, $pref_abrev);
    }
    $asistente_nombre_completo = ($pref_abrev ? $pref_abrev . ' ' : '') . $doc_titulo;
    $asistente_foto_url = get_the_post_thumbnail_url($asistente_docente->ID, 'medium') ?: '';
    if (empty($asistente_correo) && !empty($asist_meta['correo'][0])) {
        $asistente_correo = trim((string) $asist_meta['correo'][0]);
    }
} else {
    // Fallback institucional
    $slug = (string) ($context['slug'] ?? '');
    if (stripos($slug, 'educacion-innovacion-tecnologias') !== false) {
        $asistente_nombre_completo = 'Analía Bombau';
        $asistente_rol = $asistente_rol ?: 'Asistente Académica';
        $asistente_correo = $asistente_correo ?: 'edutic@flacso.edu.uy';
    } elseif (stripos($slug, 'genero') !== false) {
        $asistente_nombre_completo = 'Florencia Quartino';
        $asistente_rol = $asistente_rol ?: 'Asistente Académica';
        $asistente_correo = $asistente_correo ?: 'genero@flacso.edu.uy';
    } else {
        $asistente_nombre_completo = 'Inscripciones FLACSO Uruguay';
        $asistente_rol = $asistente_rol ?: 'Asistencia Académica';
        $asistente_correo = $asistente_correo ?: 'inscripciones@flacso.edu.uy';
    }
}
if (empty($asistente_correo)) {
    $asistente_correo = 'inscripciones@flacso.edu.uy';
}
if (empty($asistente_rol)) {
    $asistente_rol = __('Asistente Académica', 'flacso-uruguay');
}

// Bloques detallados de contenido
$bloques_html = [
    'descripcion_html'            => __('Información General', 'flacso-uruguay'),
    'modalidad_html'              => __('Modalidad de Cursada', 'flacso-uruguay'),
    'perfil_ingreso_html'         => __('Perfil de Ingreso', 'flacso-uruguay'),
    'requisitos_ingreso_html'     => __('Requisitos de Ingreso', 'flacso-uruguay'),
    'perfil_egreso_html'          => __('Perfil del Egresado', 'flacso-uruguay'),
    'requisitos_egreso_html'      => __('Requisitos de egreso y titulación.', 'flacso-uruguay'),
    'titulos_certificaciones_html'=> __('Títulos y Certificaciones', 'flacso-uruguay'),
];

$hay_bloques_detallados = false;
foreach ($bloques_html as $meta_key => $lbl) {
    if (!empty($academic[$meta_key])) {
        $hay_bloques_detallados = true;
        break;
    }
}

$child_content = trim((string) ($academic['carta_presentacion_html'] ?? ''));

$fc_hero_summary = '';
if (!empty($academic['descripcion_html'])) {
    $fc_hero_summary = wp_trim_words(wp_strip_all_tags((string) $academic['descripcion_html']), 24, '…');
}

$fc_audience_summary = '';
if (!empty($academic['perfil_ingreso_html'])) {
    $fc_audience_summary = wp_trim_words(wp_strip_all_tags((string) $academic['perfil_ingreso_html']), 40, '…');
}

// Sólo el calendario independiente puede ofrecerse públicamente.
$calendario_url = !empty($academic['calendario_pdf_url']) ? $academic['calendario_pdf_url'] : '';

?>

<div id="primary" class="content-area flacso-carta-virtual" style="<?php echo esc_attr($inline_brand_styles); ?>">
    <a class="fc-skip-link" href="#fc-info-clave"><?php esc_html_e('Saltar a la información principal', 'flacso-uruguay'); ?></a>
    <main id="main" class="site-main" aria-labelledby="fc-carta-title">

        <!-- HERO SECTION -->
        <section class="fc-hero" aria-labelledby="fc-carta-title">
            <div class="fc-hero-inner">
                <div class="fc-hero-copy">
                    <div class="fc-eyebrow">
                        <i class="bi bi-stars" aria-hidden="true"></i>
                        <span><?php echo esc_html(flacso_theme_brand_slogan()); ?></span>
                    </div>

                    <?php if ($mostrar_instancias_presenciales) : ?>
                        <div class="fc-hero-highlights fc-hero-highlights--top">
                            <span class="fc-hero-badge fc-hero-badge--accent">
                                <i class="bi bi-geo-alt" aria-hidden="true"></i>
                                <?php esc_html_e('Incluye instancias exclusivamente presenciales', 'flacso-uruguay'); ?>
                            </span>
                        </div>
                    <?php endif; ?>

                    <h1 id="fc-carta-title"><?php echo esc_html($titulo); ?></h1>

                    <?php if ($fc_hero_summary !== '') : ?>
                        <p class="fc-hero-summary"><?php echo esc_html($fc_hero_summary); ?></p>
                    <?php endif; ?>

                    <div class="fc-hero-meta">
                        <?php if (!empty($tipo_oferta)) : ?>
                            <span><i class="bi bi-mortarboard" aria-hidden="true"></i> <?php echo esc_html($tipo_oferta); ?></span>
                        <?php endif; ?>

                        <?php if (!empty($abreviacion)) : ?>
                            <span><?php echo esc_html($abreviacion . ' ' . $anio); ?></span>
                        <?php endif; ?>

                        <?php if (!empty($cohorte_label)) : ?>
                            <span><?php echo esc_html($cohorte_label); ?></span>
                        <?php endif; ?>
                    </div>

                    <?php if ($reconocido_mec || $reconocimiento_int || $reconocimiento_red_flacso) : ?>
                        <div class="fc-certifications">
                            <?php if ($reconocido_mec) : ?>
                                <div class="fc-certification">
                                    <i class="bi bi-award-fill" aria-hidden="true"></i>
                                    <span><?php esc_html_e('Reconocida por el M.E.C', 'flacso-uruguay'); ?></span>
                                </div>
                            <?php endif; ?>

                            <?php if ($reconocimiento_int) : ?>
                                <div class="fc-certification">
                                    <i class="bi bi-globe-americas" aria-hidden="true"></i>
                                    <span><?php esc_html_e('Apostillado de La Haya', 'flacso-uruguay'); ?></span>
                                </div>
                            <?php endif; ?>

                            <?php if ($reconocimiento_red_flacso) : ?>
                                <div class="fc-certification">
                                    <i class="bi bi-globe" aria-hidden="true"></i>
                                    <span><?php esc_html_e('Título con validez y reconocimiento en la red del Sistema Internacional FLACSO.', 'flacso-uruguay'); ?></span>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($arr_menciones)) : ?>
                        <div class="fc-chip-block">
                            <p><?php esc_html_e('Con mención en:', 'flacso-uruguay'); ?></p>
                            <div class="fc-chip-grid">
                                <?php foreach ($arr_menciones as $mencion) : ?>
                                    <span><?php echo esc_html($mencion); ?></span>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($arr_orientaciones)) : ?>
                        <div class="fc-chip-block">
                            <p><?php esc_html_e('Orientaciones metodológicas:', 'flacso-uruguay'); ?></p>
                            <div class="fc-chip-grid">
                                <?php foreach ($arr_orientaciones as $orientacion) : ?>
                                    <span><?php echo esc_html($orientacion); ?></span>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <div class="fc-hero-actions">
                        <?php if ($inscripciones_abiertas) : ?>
                            <a data-flacso-preinscription-cta="1" href="<?php echo esc_url($url_inscripcion); ?>" class="fc-primary-button">
                                <i class="bi bi-pencil-square" aria-hidden="true"></i>
                                <?php esc_html_e('Formulario de Preinscripción', 'flacso-uruguay'); ?>
                            </a>
                        <?php endif; ?>

                        <a href="#fc-info-clave" class="fc-secondary-button">
                            <i class="bi bi-info-circle" aria-hidden="true"></i>
                            <?php esc_html_e('Conocer más', 'flacso-uruguay'); ?>
                        </a>
                    </div>
                </div>

                <div class="fc-hero-visual">
                    <?php if ($mostrar_convenio_iin) : ?>
                        <div class="fc-convenio-pill">
                            <i class="bi bi-shield-check" aria-hidden="true"></i>
                            <span><?php esc_html_e('Convenio IIN-OEA', 'flacso-uruguay'); ?></span>
                        </div>
                    <?php endif; ?>

                    <?php if ($thumbnail_url !== '') : ?>
                        <div class="fc-cover-card">
                            <img src="<?php echo esc_url($thumbnail_url); ?>" alt="<?php echo esc_attr($titulo); ?>" loading="eager" fetchpriority="high" onerror="this.style.display='none'; if(this.nextElementSibling) this.nextElementSibling.style.display='flex';">
                            <div class="fc-cover-card fc-cover-placeholder" style="display:none;">
                                <i class="bi bi-mortarboard-fill" aria-hidden="true"></i>
                                <span><?php esc_html_e('FLACSO Uruguay', 'flacso-uruguay'); ?></span>
                            </div>
                        </div>
                    <?php else : ?>
                        <div class="fc-cover-card fc-cover-placeholder">
                            <i class="bi bi-mortarboard-fill" aria-hidden="true"></i>
                            <span><?php esc_html_e('FLACSO Uruguay', 'flacso-uruguay'); ?></span>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </section>

        <?php if (!empty($academic['articulaciones'])) : ?>
            <div class="fc-page-container">
                <?php get_template_part('template-parts/oferta/articulaciones', null, ['planes' => $academic['articulaciones']]); ?>
            </div>
        <?php endif; ?>

        <!-- TÍTULOS INTERMEDIOS -->
        <?php if (!empty($arr_titulos_intermedios)) : ?>
            <div class="fc-intermediate-strip">
                <div class="fc-intermediate-grid">
                    <?php foreach ($arr_titulos_intermedios as $titulo_intermedio) : ?>
                        <div class="fc-intermediate-card">
                            <div class="fc-intermediate-icon"><i class="bi bi-award-fill" aria-hidden="true"></i></div>
                            <div class="fc-intermediate-content">
                                <div class="fc-intermediate-copy">
                                    <div class="fc-intermediate-kicker"><?php esc_html_e('Título intermedio', 'flacso-uruguay'); ?></div>
                                    <h3><a href="<?php echo esc_url($titulo_intermedio['url']); ?>"><?php echo esc_html($titulo_intermedio['titulo']); ?></a></h3>
                                    <?php if (!empty($titulo_intermedio['menciones'])) : ?>
                                        <p class="fc-intermediate-mentions">
                                            <strong><?php esc_html_e(count($titulo_intermedio['menciones']) === 1 ? 'Mención:' : 'Menciones:', 'flacso-uruguay'); ?></strong>
                                            <?php echo esc_html(implode(' · ', $titulo_intermedio['menciones'])); ?>
                                        </p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <div class="fc-page-container">

            <!-- DATOS CLAVE -->
            <section id="fc-info-clave" class="fc-feature-grid">
                <article class="fc-feature-card">
                    <div class="fc-feature-icon"><i class="bi bi-calendar-event" aria-hidden="true"></i></div>
                    <h3><?php esc_html_e('Próximo inicio', 'flacso-uruguay'); ?></h3>
                    <p><?php echo esc_html($proximo_inicio); ?></p>
                </article>

                <article class="fc-feature-card">
                    <div class="fc-feature-icon"><i class="bi bi-clock-history" aria-hidden="true"></i></div>
                    <h3><?php esc_html_e('Duración', 'flacso-uruguay'); ?></h3>
                    <p><?php echo esc_html($duracion); ?></p>
                </article>

                <article class="fc-feature-card">
                    <div class="fc-feature-icon"><i class="bi bi-laptop" aria-hidden="true"></i></div>
                    <h3><?php esc_html_e('Modalidad', 'flacso-uruguay'); ?></h3>
                    <p><?php echo esc_html($modalidad); ?></p>
                </article>

                <article class="fc-feature-card">
                    <div class="fc-feature-icon"><i class="bi bi-layers" aria-hidden="true"></i></div>
                    <h3><?php esc_html_e('Edición', 'flacso-uruguay'); ?></h3>
                    <p><?php echo esc_html($cohorte_label !== '' ? $cohorte_label : $anio); ?></p>
                </article>

                <?php if ($mostrar_instancias_presenciales) : ?>
                    <article class="fc-feature-card">
                        <div class="fc-feature-icon"><i class="bi bi-geo-alt-fill" aria-hidden="true"></i></div>
                        <h3><?php esc_html_e('Cursada', 'flacso-uruguay'); ?></h3>
                        <p><?php esc_html_e('Incluye instancias exclusivamente presenciales', 'flacso-uruguay'); ?></p>
                    </article>
                <?php endif; ?>
            </section>

            <!-- CONTENIDO DETALLADO -->
            <section class="fc-content-sections" aria-label="<?php esc_attr_e('Información detallada del programa', 'flacso-uruguay'); ?>">
                <?php
                $mensaje_bienvenida = (string) get_option('flacso_mensaje_bienvenida', '');
                if (!empty($mensaje_bienvenida)) : ?>
                    <div class="fc-message-box fc-message-box-standalone">
                        <?php echo wp_kses_post($mensaje_bienvenida); ?>
                    </div>
                <?php endif; ?>

                <?php if ($hay_bloques_detallados) : ?>
                    <div class="fc-academic-layout">
                        <section class="fc-major-section fc-major-section--overview" aria-labelledby="fc-overview-title">
                            <header class="fc-major-heading">
                                <span class="fc-major-heading__kicker" aria-hidden="true">01</span>
                                <div>
                                    <h2 id="fc-overview-title"><?php esc_html_e('Sobre la propuesta', 'flacso-uruguay'); ?></h2>
                                    <span class="fc-major-heading__rule" aria-hidden="true"></span>
                                </div>
                            </header>
                            <?php if (!empty($academic['descripcion_html'])) : ?>
                            <article class="fc-content-panel fc-content-panel--primary fc-editorial-intro" aria-labelledby="fc-panel-descripcion">
                                <div class="fc-general-grid">
                                    <div class="fc-general-copy">
                                        <span class="fc-editorial-dots" aria-hidden="true"><i></i><i></i></span>
                                        <h2 id="fc-panel-descripcion"><?php esc_html_e('Información general', 'flacso-uruguay'); ?></h2>
                                        <div class="fc-content-panel-body">
                                            <?php echo wp_kses_post($academic['descripcion_html']); ?>
                                        </div>
                                    </div>

                                    <?php if ($fc_audience_summary !== '') : ?>
                                        <aside class="fc-audience-panel">
                                            <div class="fc-audience-panel__copy">
                                                <span class="fc-audience-panel__icon"><i class="bi bi-people-fill" aria-hidden="true"></i></span>
                                                <div>
                                                    <h3><?php esc_html_e('¿A quiénes está dirigido?', 'flacso-uruguay'); ?></h3>
                                                    <p><?php echo esc_html($fc_audience_summary); ?></p>
                                                </div>
                                            </div>
                                        </aside>
                                    <?php endif; ?>
                                </div>
                            </article>
                            <?php endif; ?>
                        </section>

                        <section class="fc-major-section fc-major-section--experience" aria-labelledby="fc-experience-title">
                            <header class="fc-major-heading">
                                <span class="fc-major-heading__kicker" aria-hidden="true">02</span>
                                <div>
                                    <h2 id="fc-experience-title"><?php esc_html_e('Experiencia académica', 'flacso-uruguay'); ?></h2>
                                    <span class="fc-major-heading__rule" aria-hidden="true"></span>
                                </div>
                            </header>

                            <div class="fc-experience-grid">
                        <?php if (!empty($academic['modalidad_html'])) : ?>
                            <article class="fc-content-panel fc-content-panel--modality" aria-labelledby="fc-panel-modalidad">
                                <div class="fc-content-panel__icon"><i class="bi bi-laptop" aria-hidden="true"></i></div>
                                <div>
                                    <h2 id="fc-panel-modalidad"><?php esc_html_e('Modalidad de Cursada', 'flacso-uruguay'); ?></h2>
                                    <div class="fc-content-panel-body">
                                        <?php echo wp_kses_post($academic['modalidad_html']); ?>
                                    </div>
                                </div>
                            </article>
                        <?php endif; ?>

                        <?php
                        $fc_academic_details = [
                            'perfil_ingreso_html'          => __('Perfil de Ingreso', 'flacso-uruguay'),
                            'requisitos_ingreso_html'      => __('Requisitos de Ingreso', 'flacso-uruguay'),
                            'perfil_egreso_html'           => __('Perfil del Egresado', 'flacso-uruguay'),
                            'requisitos_egreso_html'       => __('Requisitos de egreso y titulación.', 'flacso-uruguay'),
                            'titulos_certificaciones_html' => __('Títulos y Certificaciones', 'flacso-uruguay'),
                        ];
                        $fc_has_academic_details = false;
                        foreach ($fc_academic_details as $fc_detail_key => $fc_detail_label) {
                            if (!empty($academic[$fc_detail_key])) {
                                $fc_has_academic_details = true;
                                break;
                            }
                        }
                        ?>

                        <?php if ($fc_has_academic_details) : ?>
                            <div class="fc-academic-details" aria-label="<?php esc_attr_e('Detalles académicos', 'flacso-uruguay'); ?>">
                                <div class="fc-academic-details__heading">
                                    <span class="fc-academic-details__icon"><i class="bi bi-book" aria-hidden="true"></i></span>
                                    <h2><?php esc_html_e('Contenidos y estructura académica', 'flacso-uruguay'); ?></h2>
                                </div>
                                <?php foreach ($fc_academic_details as $fc_detail_key => $fc_detail_label) :
                                    if (empty($academic[$fc_detail_key])) {
                                        continue;
                                    }
                                ?>
                                    <details class="fc-academic-detail">
                                        <summary>
                                            <span><?php echo esc_html($fc_detail_label); ?></span>
                                            <i class="bi bi-plus-lg" aria-hidden="true"></i>
                                        </summary>
                                        <div class="fc-academic-detail__body">
                                            <?php echo wp_kses_post($academic[$fc_detail_key]); ?>
                                        </div>
                                    </details>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                            </div>
                        </section>
                    </div>
                <?php endif; ?>

                <?php if ($child_content !== '') : ?>
                    <article class="fc-content-panel fc-content-wrapper fc-content-wrapper-compact fc-child-content" aria-label="<?php esc_attr_e('Contenido adicional del programa', 'flacso-uruguay'); ?>">
                        <div class="fc-content-panel-body">
                            <?php echo wp_kses_post($child_content); ?>
                        </div>
                    </article>
                <?php endif; ?>
            </section>

            <section class="fc-major-section fc-major-section--planning" aria-labelledby="fc-planning-title">
                <header class="fc-major-heading">
                    <span class="fc-major-heading__kicker" aria-hidden="true">03</span>
                    <div>
                        <h2 id="fc-planning-title"><?php esc_html_e('Planificación e inversión', 'flacso-uruguay'); ?></h2>
                        <span class="fc-major-heading__rule" aria-hidden="true"></span>
                    </div>
                </header>

                <div class="fc-resource-grid">

            <!-- Calendario público: no publicar archivos curriculares internos. -->
            <?php if ($calendario_url) : ?>
                <section class="fc-section fc-documents-section">
                    <h2 class="fc-section-title"><?php esc_html_e('Calendario académico', 'flacso-uruguay'); ?></h2>
                    <div class="fc-more-grid" style="--fc-grid-cols: 1;">
                        <article class="fc-more-card">
                            <div class="fc-more-icon"><i class="bi bi-calendar2-check" aria-hidden="true"></i></div>
                            <h3><?php esc_html_e('Calendario académico', 'flacso-uruguay'); ?></h3>
                            <p><?php esc_html_e('Fechas clave, inicios y recesos de la cursada.', 'flacso-uruguay'); ?></p>
                            <a href="<?php echo esc_url($calendario_url); ?>" target="_blank" rel="noopener noreferrer" class="fc-card-button">
                                <i class="bi bi-file-earmark-pdf" aria-hidden="true"></i> <?php esc_html_e('Ver calendario (PDF)', 'flacso-uruguay'); ?>
                            </a>
                        </article>
                    </div>
                </section>
            <?php endif; ?>

            <!-- REQUISITOS DE POSTULACIÓN: fallback solo cuando no hay requisitos cargados -->
            <?php if (empty($academic['requisitos_ingreso_html'])) : ?>
                <section class="fc-section">
                    <h2 class="fc-section-title"><?php esc_html_e('Requisitos de Postulación y Admisión', 'flacso-uruguay'); ?></h2>
                    
                    <div class="fc-requirements-grid">
                        <div class="fc-requirement">
                            <div class="fc-requirement-icon"><i class="bi bi-mortarboard" aria-hidden="true"></i></div>
                            <div class="fc-requirement-content">
                                <strong><?php esc_html_e('Título universitario o terciario', 'flacso-uruguay'); ?></strong>
                                <p><?php esc_html_e('Poseer título de grado o formación terciaria equivalente en áreas afines.', 'flacso-uruguay'); ?></p>
                            </div>
                        </div>

                        <div class="fc-requirement">
                            <div class="fc-requirement-icon"><i class="bi bi-file-earmark-person" aria-hidden="true"></i></div>
                            <div class="fc-requirement-content">
                                <strong><?php esc_html_e('Currículum Vitae', 'flacso-uruguay'); ?></strong>
                                <p><?php esc_html_e('CV actualizado detallando trayectoria académica y profesional.', 'flacso-uruguay'); ?></p>
                            </div>
                        </div>

                        <div class="fc-requirement">
                            <div class="fc-requirement-icon"><i class="bi bi-person-vcard" aria-hidden="true"></i></div>
                            <div class="fc-requirement-content">
                                <strong><?php esc_html_e('Documento de Identidad', 'flacso-uruguay'); ?></strong>
                                <p><?php esc_html_e('Copia de cédula de identidad o pasaporte vigente.', 'flacso-uruguay'); ?></p>
                            </div>
                        </div>

                        <div class="fc-requirement">
                            <div class="fc-requirement-icon"><i class="bi bi-chat-square-quote" aria-hidden="true"></i></div>
                            <div class="fc-requirement-content">
                                <strong><?php esc_html_e('Carta de motivación', 'flacso-uruguay'); ?></strong>
                                <p><?php esc_html_e('Exposición de motivos e interés en cursar la propuesta.', 'flacso-uruguay'); ?></p>
                            </div>
                        </div>
                    </div>

                </section>
            <?php endif; ?>

            <!-- TABLA DE PRECIOS E INVERSIÓN -->
            <section class="fc-section fc-pricing-section">
                <h2 class="fc-section-title"><?php esc_html_e('Costos y Financiación', 'flacso-uruguay'); ?></h2>

                <?php if (!empty($precios_filas)) : ?>
                    <div class="fc-pricing-wrapper">
                        <div class="fc-table-wrap">
                            <table class="fc-pricing-table">
                                <thead>
                                    <tr>
                                        <th scope="col"><?php esc_html_e('Concepto', 'flacso-uruguay'); ?></th>
                                        <th scope="col">
                                            <?php esc_html_e('Valor en $', 'flacso-uruguay'); ?>
                                            <span><?php esc_html_e('residentes en Uruguay', 'flacso-uruguay'); ?></span>
                                        </th>
                                        <?php if ($mostrar_precios_dolares) : ?>
                                            <th scope="col">
                                                <?php esc_html_e('Valor en U$S', 'flacso-uruguay'); ?>
                                                <span><?php esc_html_e('residentes en el exterior', 'flacso-uruguay'); ?></span>
                                            </th>
                                        <?php endif; ?>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($precios_filas as $row) :
                                        if (!is_array($row)) continue;
                                        $concepto_row = $row['concepto'] ?? ($row['concept'] ?? '');
                                        $uyu_row      = $row['uyu'] ?? ($row['uy'] ?? '');
                                        $usd_row      = $row['usd'] ?? ($row['us'] ?? '');
                                        $is_destacada = !empty($row['destacada']) || !empty($row['highlight']);
                                    ?>
                                        <tr class="<?php echo $is_destacada ? 'is-highlighted' : ''; ?>">
                                            <td data-label="<?php esc_attr_e('Concepto', 'flacso-uruguay'); ?>"><?php echo wp_kses_post((string) $concepto_row); ?></td>
                                            <td data-label="<?php esc_attr_e('Valor en pesos', 'flacso-uruguay'); ?>"><?php echo wp_kses_post((string) $uyu_row); ?></td>
                                            <?php if ($mostrar_precios_dolares) : ?>
                                                <td data-label="<?php esc_attr_e('Valor en dólares', 'flacso-uruguay'); ?>"><?php echo wp_kses_post((string) $usd_row); ?></td>
                                            <?php endif; ?>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <?php if (!empty($precios_nota)) : ?>
                            <div class="fc-pricing-note">
                                <i class="bi bi-info-circle me-1" aria-hidden="true"></i> <?php echo wp_kses_post($precios_nota); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php else : ?>
                    <div class="fc-empty-pricing">
                        <i class="bi bi-currency-dollar" aria-hidden="true"></i>
                        <p>
                            <?php
                            printf(
                                /* translators: %s: email link */
                                esc_html__('Para consultar los costos, planes de financiación y políticas de becas, ponte en contacto con %s.', 'flacso-uruguay'),
                                '<a href="mailto:inscripciones@flacso.edu.uy">inscripciones@flacso.edu.uy</a>'
                            );
                            ?>
                        </p>
                    </div>
                <?php endif; ?>

                <?php if ($inscripciones_abiertas) : ?>
                    <div class="fc-pricing-cta">
                        <div>
                            <span><?php esc_html_e('¿Querés avanzar?', 'flacso-uruguay'); ?></span>
                            <strong><?php esc_html_e('Podés iniciar tu preinscripción ahora.', 'flacso-uruguay'); ?></strong>
                        </div>
                        <a data-flacso-preinscription-cta="1" href="<?php echo esc_url($url_inscripcion); ?>">
                            <i class="bi bi-pencil-square" aria-hidden="true"></i>
                            <?php esc_html_e('Preinscribirme', 'flacso-uruguay'); ?>
                        </a>
                    </div>
                <?php endif; ?>
            </section>

                </div><!-- /.fc-resource-grid -->
            </section>

            <!-- INFORMACIÓN PRÁCTICA -->
            <section class="fc-section fc-info-important fc-major-section fc-major-section--practical">
                <header class="fc-major-heading">
                    <span class="fc-major-heading__kicker" aria-hidden="true">04</span>
                    <div>
                        <h2><?php esc_html_e('Información práctica', 'flacso-uruguay'); ?></h2>
                        <span class="fc-major-heading__rule" aria-hidden="true"></span>
                    </div>
                </header>

                <div class="fc-info-list">
                    <?php if ($mostrar_precios_dolares) : ?>
                        <div class="fc-info-row">
                            <div class="fc-info-icon"><i class="bi bi-currency-dollar" aria-hidden="true"></i></div>
                            <div><?php esc_html_e('Para pagos en dólares, las cuotas se mantendrán fijas durante toda la cursada.', 'flacso-uruguay'); ?></div>
                        </div>
                    <?php endif; ?>

                    <div class="fc-info-row">
                        <div class="fc-info-icon"><i class="bi bi-currency-exchange" aria-hidden="true"></i></div>
                        <div><?php printf(esc_html__('Para pagos en pesos, las cuotas permanecerán fijas durante todo %s, y a partir del siguiente año se ajustarán en enero, por debajo de la inflación interanual.', 'flacso-uruguay'), esc_html($anio)); ?></div>
                    </div>

                    <div class="fc-info-row">
                        <div class="fc-info-icon"><i class="bi bi-calendar-check" aria-hidden="true"></i></div>
                        <div><?php esc_html_e('Las cuotas vencen el día 15 de cada mes.', 'flacso-uruguay'); ?></div>
                    </div>

                    <!-- Expedición de título: información institucional obligatoria. -->
                    <div class="fc-info-row">
                            <div class="fc-info-icon"><i class="bi bi-award" aria-hidden="true"></i></div>
                            <div><?php esc_html_e('El título se expide en el exterior y tiene un costo asociado de USD 150, que incluye el trámite de Apostilla de La Haya. El costo de envio dependerá del pais de destino.', 'flacso-uruguay'); ?></div>
                    </div>

                    <!-- Costos de envío: siempre visibles en todas las cartas. -->
                    <div class="fc-info-row">
                            <div class="fc-info-icon"><i class="bi bi-truck" aria-hidden="true"></i></div>
                            <div>
                                <strong><?php esc_html_e('Los certificados tienen costos de envío al exterior:', 'flacso-uruguay'); ?></strong>
                                <ul class="fc-envios-list">
                                    <li><span>USD 30</span> – <?php esc_html_e('envío a Argentina', 'flacso-uruguay'); ?></li>
                                    <li><span>USD 50</span> – <?php esc_html_e('envío al resto de América Latina', 'flacso-uruguay'); ?></li>
                                    <li><span>USD 70</span> – <?php esc_html_e('envío a Europa', 'flacso-uruguay'); ?></li>
                                </ul>
                            </div>
                    </div>

                    <?php if ($mostrar_convenio_iin) : ?>
                        <div class="fc-info-row">
                            <div class="fc-info-icon"><i class="bi bi-globe" aria-hidden="true"></i></div>
                            <div><strong><?php esc_html_e('Convenio IIN-OEA:', 'flacso-uruguay'); ?></strong> <?php esc_html_e('los funcionarios o referentes de los Estados Miembros que cuenten con aval institucional acceden a bonificaciones especiales para cursar este programa.', 'flacso-uruguay'); ?></div>
                        </div>
                    <?php endif; ?>

                    <div class="fc-info-row">
                        <div class="fc-info-icon"><i class="bi bi-envelope" aria-hidden="true"></i></div>
                        <div>
                            <strong><?php esc_html_e('Consultá por otros planes de financiación en', 'flacso-uruguay'); ?></strong>
                            <a href="mailto:inscripciones@flacso.edu.uy">inscripciones@flacso.edu.uy</a>
                        </div>
                    </div>
                </div>

                <div class="fc-action-buttons">
                    <a href="https://flacso.edu.uy/convenios/" target="_blank" rel="noopener" aria-label="<?php esc_attr_e('Ver convenios de FLACSO Uruguay', 'flacso-uruguay'); ?>">
                        <i class="bi bi-file-earmark-text" aria-hidden="true"></i>
                        <?php esc_html_e('Ver convenios', 'flacso-uruguay'); ?>
                    </a>

                    <a href="https://flacso.edu.uy/formas-de-pago/" target="_blank" rel="noopener" aria-label="<?php esc_attr_e('Ver formas de pago de FLACSO Uruguay', 'flacso-uruguay'); ?>">
                        <i class="bi bi-credit-card" aria-hidden="true"></i>
                        <?php esc_html_e('Ver formas de pago', 'flacso-uruguay'); ?>
                    </a>
                </div>
            </section>

            <!-- MÁS INFORMACIÓN (TRAYECTORIA Y GESTIÓN) -->
            <?php
            $carta_mas_info_section_title = trim((string) get_option('flacso_carta_mas_info_section_title', __('Por qué estudiar en FLACSO Uruguay', 'flacso-uruguay')));
            $carta_mas_info_trayectoria_title = trim((string) get_option('flacso_carta_mas_info_trayectoria_title', __('Trayectoria Académica', 'flacso-uruguay')));
            $carta_mas_info_trayectoria_html = (string) get_option('flacso_carta_mas_info_trayectoria_html', '<p>' . __('Nuestra <strong>Facultad de Posgrados</strong> busca formar a sus estudiantes a nivel <strong>académico, profesional y laboral</strong>. Nos distinguen <strong>19 años de trayectoria</strong> a nivel nacional y más de <strong>65 años a nivel internacional</strong>. Además, más de <strong>7000 personas egresadas</strong> de FLACSO Uruguay trabajan en el ámbito público y privado.', 'flacso-uruguay') . '</p>');
            $carta_mas_info_gestion_title = trim((string) get_option('flacso_carta_mas_info_gestion_title', __('Gestión Académica', 'flacso-uruguay')));
            $carta_mas_info_gestion_html = (string) get_option('flacso_carta_mas_info_gestion_html', '<p>' . __('Nos distingue un sistema de <strong>gestión académica eficiente y cercano</strong>, que acompaña de forma <strong>personalizada</strong> a cada estudiante y garantiza <strong>altos niveles de egreso, superiores al 90%</strong>.', 'flacso-uruguay') . '</p>');
            ?>
            <section class="fc-section fc-major-section fc-major-section--trust">
                <header class="fc-major-heading">
                    <span class="fc-major-heading__kicker" aria-hidden="true">05</span>
                    <div>
                        <h2><?php echo esc_html($carta_mas_info_section_title); ?></h2>
                        <span class="fc-major-heading__rule" aria-hidden="true"></span>
                    </div>
                </header>

                <div class="fc-more-grid">
                    <article class="fc-more-card">
                        <div class="fc-more-icon"><i class="bi bi-award" aria-hidden="true"></i></div>
                        <h3><?php echo esc_html($carta_mas_info_trayectoria_title); ?></h3>
                        <?php echo wp_kses_post($carta_mas_info_trayectoria_html); ?>
                    </article>

                    <article class="fc-more-card">
                        <div class="fc-more-icon"><i class="bi bi-gear" aria-hidden="true"></i></div>
                        <h3><?php echo esc_html($carta_mas_info_gestion_title); ?></h3>
                        <?php echo wp_kses_post($carta_mas_info_gestion_html); ?>
                    </article>

                </div>
            </section>

            <!-- CONTACTO ACADÉMICO -->
            <section class="fc-major-section fc-major-section--contact" aria-labelledby="fc-contact-title">
                <header class="fc-major-heading">
                    <span class="fc-major-heading__kicker" aria-hidden="true">06</span>
                    <div>
                        <h2 id="fc-contact-title"><?php esc_html_e('Contacto académico', 'flacso-uruguay'); ?></h2>
                        <span class="fc-major-heading__rule" aria-hidden="true"></span>
                    </div>
                </header>
                <div class="fc-assistant-card">
                <div class="fc-assistant-photo">
                    <?php if ($asistente_foto_url) : ?>
                        <img src="<?php echo esc_url($asistente_foto_url); ?>" alt="<?php echo esc_attr(wp_strip_all_tags($asistente_nombre_completo)); ?>">
                    <?php else : ?>
                        <div class="fc-assistant-placeholder">
                            <?php echo esc_html(strtoupper(mb_substr(wp_strip_all_tags($asistente_nombre_completo), 0, 1))); ?>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="fc-assistant-copy">
                    <span class="fc-assistant-label"><?php esc_html_e('Contacto académico', 'flacso-uruguay'); ?></span>
                    <h2><?php echo esc_html($asistente_nombre_completo); ?></h2>
                    <p><?php echo esc_html($asistente_rol); ?></p>
                </div>

                <div class="fc-assistant-meta">
                    <?php if ($asistente_correo !== '') : ?>
                        <a href="mailto:<?php echo esc_attr($asistente_correo); ?>">
                            <i class="bi bi-envelope" aria-hidden="true"></i>
                            <span><?php echo esc_html($asistente_correo); ?></span>
                        </a>
                    <?php endif; ?>
                    <a href="tel:+59824817459">
                        <i class="bi bi-telephone" aria-hidden="true"></i>
                        <span>+598 2481 7459</span>
                    </a>
                </div>

                <?php if ($asistente_correo !== '') : ?>
                    <a class="fc-assistant-action" href="mailto:<?php echo esc_attr($asistente_correo); ?>">
                        <?php esc_html_e('Realizar una consulta', 'flacso-uruguay'); ?>
                        <i class="bi bi-arrow-right" aria-hidden="true"></i>
                    </a>
                <?php endif; ?>
                </div>
            </section>

            <!-- FINAL CTA -->
            <section class="fc-final-cta">
                <div>
                    <span><?php echo esc_html(flacso_theme_brand_slogan()); ?></span>
                    <h2><?php echo esc_html($carta_cta_titulo); ?></h2>
                    <p><strong><?php esc_html_e('Formación 100% a distancia', 'flacso-uruguay'); ?></strong></p>
                </div>

                <?php if ($inscripciones_abiertas) : ?>
                    <a data-flacso-preinscription-cta="1" href="<?php echo esc_url($url_inscripcion); ?>" aria-label="<?php echo esc_attr(sprintf(__('Abrir formulario de preinscripción para %s', 'flacso-uruguay'), $titulo)); ?>">
                        <i class="bi bi-pencil-square" aria-hidden="true"></i>
                        <?php esc_html_e('Formulario de Preinscripción', 'flacso-uruguay'); ?>
                    </a>
                <?php else : ?>
                    <a href="mailto:inscripciones@flacso.edu.uy" aria-label="<?php echo esc_attr(sprintf(__('Solicitar información para %s', 'flacso-uruguay'), $titulo)); ?>">
                        <i class="bi bi-envelope" aria-hidden="true"></i>
                        <?php esc_html_e('Solicitar información', 'flacso-uruguay'); ?>
                    </a>
                <?php endif; ?>
            </section>

            <?php if ($inscripciones_abiertas) : ?>
                <a class="fc-floating-preinscripcion is-hidden" data-flacso-preinscription-cta="1" href="<?php echo esc_url($url_inscripcion); ?>" aria-label="<?php echo esc_attr(sprintf(__('Abrir formulario de preinscripción para %s', 'flacso-uruguay'), $titulo)); ?>" aria-hidden="true" tabindex="-1">
                    <i class="bi bi-pencil-square" aria-hidden="true"></i>
                    <span class="fc-floating-copy">
                        <strong><?php esc_html_e('Formulario de preinscripción', 'flacso-uruguay'); ?></strong>
                        <small><?php esc_html_e('Inscripciones abiertas', 'flacso-uruguay'); ?></small>
                    </span>
                </a>
            <?php endif; ?>

        </div>
    </main>
</div>

<script>
(function () {
    const groups = Array.from(document.querySelectorAll('.fc-academic-detail'));

    groups.forEach((detail) => {
        detail.addEventListener('toggle', () => {
            if (!detail.open) return;

            groups.forEach((other) => {
                if (other !== detail && other.open) {
                    other.open = false;
                }
            });
        });
    });
}());
</script>

<script>
(function () {
    const floatingButton = document.querySelector('.fc-floating-preinscripcion');
    if (!floatingButton || !('IntersectionObserver' in window)) {
        return;
    }

    const preinscriptionButtons = Array.from(document.querySelectorAll('.fc-primary-button, .fc-final-cta a, a[href*="/preinscripcion"]'))
        .filter((button) => button !== floatingButton);

    if (!preinscriptionButtons.length) {
        floatingButton.classList.remove('is-hidden');
        floatingButton.removeAttribute('aria-hidden');
        floatingButton.removeAttribute('tabindex');
        return;
    }

    const visibleButtons = new Set();
    const setFloatingVisibility = () => {
        const shouldHide = visibleButtons.size > 0;
        floatingButton.classList.toggle('is-hidden', shouldHide);

        if (shouldHide) {
            floatingButton.setAttribute('aria-hidden', 'true');
            floatingButton.setAttribute('tabindex', '-1');
        } else {
            floatingButton.removeAttribute('aria-hidden');
            floatingButton.removeAttribute('tabindex');
        }
    };

    const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            if (entry.isIntersecting && entry.intersectionRatio >= 0.35) {
                visibleButtons.add(entry.target);
            } else {
                visibleButtons.delete(entry.target);
            }
        });
        setFloatingVisibility();
    }, {
        threshold: [0, 0.35, 0.65],
        rootMargin: '0px 0px -8% 0px'
    });

    preinscriptionButtons.forEach((button) => observer.observe(button));
    setFloatingVisibility();
}());
</script>

<script>
(function() {
    if (typeof window.flacsoMetaTrack !== 'function') return;
    try {
        window.flacsoMetaTrack('ViewContent', {
            content_name: <?php echo wp_json_encode((string) $titulo); ?>,
            content_category: 'oferta_academica',
            content_ids: ['oferta-' + <?php echo wp_json_encode((string) $post_id); ?>],
            flacso_stage: 'carta_informacion'
        });
    } catch (e) {}
})();
</script>
