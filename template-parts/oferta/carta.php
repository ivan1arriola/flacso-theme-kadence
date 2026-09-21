<?php
/**
 * Template para la Carta de Presentación de una Oferta Académica.
 *
 * Endpoint: /formacion/{tipo}/{slug}/carta/
 * Consume el View Model canónico de FLACSO Uruguay.
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

$abreviacion      = trim((string) ($academic['abreviacion'] ?? ''));
$cohorte_label    = trim((string) ($instance['label'] ?? ''));
$anio             = !empty($instance['anio_inicio']) ? (string) $instance['anio_inicio'] : (!empty($instance['fecha_inicio']) ? substr((string) $instance['fecha_inicio'], 0, 4) : wp_date('Y'));
$hero_badge_text  = trim((string) ($academic['carta_hero_etiqueta'] ?? ''));
$mostrar_instancias_presenciales = !empty($academic['carta_instancias_presenciales']);

$reconocido_mec     = !empty($academic['reconocido_mec']);
$reconocimiento_int = !empty($academic['reconocimiento_internacional']);
$mostrar_convenio_iin = !empty($academic['convenio_iin_oea']);
$mostrar_costos_envio = !empty($academic['mostrar_costos_envio']);

$mostrar_expedicion = false;
if (isset($academic['mostrar_expedicion_titulo']) && $academic['mostrar_expedicion_titulo'] !== '') {
    $mostrar_expedicion = !empty($academic['mostrar_expedicion_titulo']);
} else {
    $mostrar_expedicion = ($is_maestria || $es_especializacion);
}

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
    : (!empty($academic['duracion_html']) ? wp_strip_all_tags($academic['duracion_html']) : __('A definir', 'flacso-uruguay'));

$raw_modalidad = trim((string) ($instance['modalidad'] ?? ''));
if ($raw_modalidad === '') {
    $raw_modalidad = trim((string) ($instance['modalidad_descripcion'] ?? ''));
}
$modalidad_texto = trim(wp_strip_all_tags($raw_modalidad));
if ($modalidad_texto === '' || mb_strlen($modalidad_texto) > 50) {
    if ($mostrar_instancias_presenciales || stripos($modalidad_texto, 'presencial') !== false) {
        $modalidad = __('Híbrida / Semipresencial', 'flacso-uruguay');
    } else {
        $modalidad = __('100% virtual', 'flacso-uruguay');
    }
} else {
    $modalidad = $modalidad_texto;
}

if (!empty($instance['modalidad_descripcion']) && empty($academic['modalidad_html'])) {
    $academic['modalidad_html'] = $instance['modalidad_descripcion'];
}

// CTA global o por defecto
$carta_cta_titulo_global = trim((string) get_option('flacso_carta_cta_titulo_default', ''));
$carta_cta_titulo = $carta_cta_titulo_global !== ''
    ? $carta_cta_titulo_global
    : sprintf(__('Comenzá el año cursando un posgrado en FLACSO Uruguay', 'flacso-uruguay'));

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
    'objetivos_html'              => __('Objetivos del Programa', 'flacso-uruguay'),
    'modalidad_html'              => __('Modalidad de Cursada', 'flacso-uruguay'),
    'duracion_html'               => __('Duración y Carga Horaria', 'flacso-uruguay'),
    'perfil_ingreso_html'         => __('Perfil de Ingreso', 'flacso-uruguay'),
    'requisitos_ingreso_html'     => __('Requisitos de Ingreso', 'flacso-uruguay'),
    'perfil_egreso_html'          => __('Perfil del Egresado', 'flacso-uruguay'),
    'requisitos_egreso_html'      => __('Requisitos para Titulación', 'flacso-uruguay'),
    'titulos_certificaciones_html'=> __('Títulos y Certificaciones', 'flacso-uruguay'),
    'acreditacion'                => __('Acreditaciones y Validez Oficial', 'flacso-uruguay'),
];

$hay_bloques_detallados = false;
foreach ($bloques_html as $meta_key => $lbl) {
    if (!empty($academic[$meta_key])) {
        $hay_bloques_detallados = true;
        break;
    }
}

$child_content = trim((string) ($academic['carta_presentacion_html'] ?? ''));

// Documentos adjuntos (malla y calendario)
$cartamalla_url = !empty($academic['cartamalla_pdf_url']) ? $academic['cartamalla_pdf_url'] : '';
$malla_url      = !empty($academic['malla_pdf_url']) ? $academic['malla_pdf_url'] : '';
$calendario_url = !empty($academic['calendario_pdf_url']) ? $academic['calendario_pdf_url'] : '';

?>

<div id="primary" class="content-area flacso-carta-virtual">
    <a class="fc-skip-link" href="#fc-info-clave"><?php esc_html_e('Saltar a la información principal', 'flacso-uruguay'); ?></a>
    <main id="main" class="site-main" aria-labelledby="fc-carta-title">

        <!-- HERO SECTION -->
        <section class="fc-hero" aria-labelledby="fc-carta-title">
            <div class="fc-hero-inner<?php echo $hero_badge_text !== '' ? ' has-hero-ribbon' : ''; ?>">
                <?php if ($hero_badge_text !== '') : ?>
                    <div class="fc-hero-ribbon">
                        <i class="bi bi-bookmark-star-fill" aria-hidden="true"></i>
                        <span><?php echo esc_html($hero_badge_text); ?></span>
                    </div>
                <?php endif; ?>

                <div class="fc-hero-copy">
                    <div class="fc-eyebrow">
                        <i class="bi bi-stars" aria-hidden="true"></i>
                        <span><?php esc_html_e('Formación de excelencia, estés donde estés', 'flacso-uruguay'); ?></span>
                    </div>

                    <?php if ($mostrar_instancias_presenciales) : ?>
                        <div class="fc-hero-highlights fc-hero-highlights--top">
                            <span class="fc-hero-badge fc-hero-badge--accent">
                                <i class="bi bi-geo-alt" aria-hidden="true"></i>
                                <?php esc_html_e('Con instancias presenciales', 'flacso-uruguay'); ?>
                            </span>
                        </div>
                    <?php endif; ?>

                    <h1 id="fc-carta-title"><?php echo esc_html($titulo); ?></h1>

                    <div class="fc-hero-meta">
                        <?php if (!empty($abreviacion)) : ?>
                            <span><?php echo esc_html($abreviacion . ' ' . $anio); ?></span>
                        <?php endif; ?>

                        <?php if (!empty($cohorte_label)) : ?>
                            <span><?php echo esc_html($cohorte_label); ?></span>
                        <?php endif; ?>
                    </div>

                    <?php if ($reconocido_mec || $reconocimiento_int) : ?>
                        <div class="fc-certifications">
                            <?php if ($reconocido_mec) : ?>
                                <div class="fc-certification">
                                    <i class="bi bi-award" aria-hidden="true"></i>
                                    <span><?php esc_html_e('Reconocida por el M.E.C', 'flacso-uruguay'); ?></span>
                                </div>
                            <?php endif; ?>

                            <?php if ($reconocimiento_int) : ?>
                                <div class="fc-certification">
                                    <i class="bi bi-globe-americas" aria-hidden="true"></i>
                                    <span><?php esc_html_e('Apostillado de La Haya', 'flacso-uruguay'); ?></span>
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
                                    <h3><?php echo esc_html($titulo_intermedio); ?></h3>
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
                    <div class="fc-feature-icon"><i class="bi bi-clock" aria-hidden="true"></i></div>
                    <h3><?php esc_html_e('Duración', 'flacso-uruguay'); ?></h3>
                    <p><?php echo esc_html($duracion); ?></p>
                </article>

                <article class="fc-feature-card">
                    <div class="fc-feature-icon"><i class="bi bi-laptop" aria-hidden="true"></i></div>
                    <h3><?php esc_html_e('Modalidad', 'flacso-uruguay'); ?></h3>
                    <p><?php echo esc_html($modalidad); ?></p>
                </article>

                <?php if ($mostrar_instancias_presenciales) : ?>
                    <article class="fc-feature-card">
                        <div class="fc-feature-icon"><i class="bi bi-geo-alt" aria-hidden="true"></i></div>
                        <h3><?php esc_html_e('Cursada', 'flacso-uruguay'); ?></h3>
                        <p><?php esc_html_e('Con instancias presenciales', 'flacso-uruguay'); ?></p>
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
                    <div class="fc-rich-grid">
                        <?php foreach ($bloques_html as $meta_key => $titulo_bloque) :
                            $bloque_val = $academic[$meta_key] ?? '';
                            if (!empty($bloque_val)) :
                                $panel_id = 'fc-panel-' . sanitize_title($meta_key);
                        ?>
                                <article class="fc-content-panel fc-content-wrapper fc-content-wrapper-compact fc-rich-block" aria-labelledby="<?php echo esc_attr($panel_id); ?>">
                                    <h2 id="<?php echo esc_attr($panel_id); ?>"><?php echo esc_html($titulo_bloque); ?></h2>
                                    <div class="fc-content-panel-body">
                                        <?php echo wp_kses_post($bloque_val); ?>
                                    </div>
                                </article>
                        <?php endif; endforeach; ?>
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

            <!-- DOCUMENTOS: MALLA Y CALENDARIO -->
            <?php if ($cartamalla_url || $malla_url || $calendario_url) :
                $fc_cards_count = $cartamalla_url ? 1 : (($malla_url ? 1 : 0) + ($calendario_url ? 1 : 0));
            ?>
                <section class="fc-section">
                    <h2 class="fc-section-title"><?php esc_html_e('Calendario y Malla Curricular', 'flacso-uruguay'); ?></h2>
                    <div class="fc-more-grid" style="--fc-grid-cols: <?php echo esc_attr((string) $fc_cards_count); ?>;">
                        <?php if ($cartamalla_url) : ?>
                            <article class="fc-more-card" style="text-align: center; padding: 2rem;">
                                <div class="fc-more-icon" style="margin: 0 auto 1rem;"><i class="bi bi-journal-check" aria-hidden="true"></i></div>
                                <h3><?php esc_html_e('Calendario y Malla', 'flacso-uruguay'); ?></h3>
                                <p style="margin-bottom: 1.5rem;"><?php esc_html_e('Incluye el calendario de cursada y la malla curricular completa.', 'flacso-uruguay'); ?></p>
                                <a href="<?php echo esc_url($cartamalla_url); ?>" target="_blank" rel="noopener noreferrer" class="fc-secondary-button fc-card-button" style="display: inline-flex; width: auto; justify-content: center;">
                                    <i class="bi bi-file-earmark-pdf"></i> <?php esc_html_e('Ver Documento (PDF)', 'flacso-uruguay'); ?>
                                </a>
                            </article>
                        <?php else : ?>
                            <?php if ($calendario_url) : ?>
                                <article class="fc-more-card" style="text-align: center; padding: 2rem;">
                                    <div class="fc-more-icon" style="margin: 0 auto 1rem;"><i class="bi bi-calendar2-check" aria-hidden="true"></i></div>
                                    <h3><?php esc_html_e('Calendario Académico', 'flacso-uruguay'); ?></h3>
                                    <p style="margin-bottom: 1.5rem;"><?php esc_html_e('Fechas clave, inicios y recesos de la cursada.', 'flacso-uruguay'); ?></p>
                                    <a href="<?php echo esc_url($calendario_url); ?>" target="_blank" rel="noopener noreferrer" class="fc-secondary-button fc-card-button" style="display: inline-flex; width: auto; justify-content: center;">
                                        <i class="bi bi-file-earmark-pdf"></i> <?php esc_html_e('Ver Calendario (PDF)', 'flacso-uruguay'); ?>
                                    </a>
                                </article>
                            <?php endif; ?>
                            <?php if ($malla_url) : ?>
                                <article class="fc-more-card" style="text-align: center; padding: 2rem;">
                                    <div class="fc-more-icon" style="margin: 0 auto 1rem;"><i class="bi bi-journal-bookmark" aria-hidden="true"></i></div>
                                    <h3><?php esc_html_e('Malla Curricular', 'flacso-uruguay'); ?></h3>
                                    <p style="margin-bottom: 1.5rem;"><?php esc_html_e('Programa completo y asignaturas del posgrado.', 'flacso-uruguay'); ?></p>
                                    <a href="<?php echo esc_url($malla_url); ?>" target="_blank" rel="noopener noreferrer" class="fc-secondary-button fc-card-button" style="display: inline-flex; width: auto; justify-content: center;">
                                        <i class="bi bi-file-earmark-pdf"></i> <?php esc_html_e('Ver Malla (PDF)', 'flacso-uruguay'); ?>
                                    </a>
                                </article>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </section>
            <?php endif; ?>

            <!-- REQUISITOS DE POSTULACIÓN -->
            <section class="fc-section">
                <h2 class="fc-section-title"><?php esc_html_e('Requisitos de Postulación y Admisión', 'flacso-uruguay'); ?></h2>

                <?php if (!empty($academic['requisitos_ingreso_html'])) : ?>
                    <div class="fc-content-wrapper">
                        <?php echo wp_kses_post($academic['requisitos_ingreso_html']); ?>
                    </div>
                <?php else : ?>
                    <div class="fc-requirements-grid">
                        <div class="fc-requirement">
                            <div class="fc-requirement-icon"><i class="bi bi-mortarboard" aria-hidden="true"></i></div>
                            <div class="fc-requirement-content">
                                <strong><?php esc_html_e('Título universitario o terciario', 'flacso-uruguay'); ?></strong>
                                <p><?php esc_html_e('Poseer título de grado o formación terciaria equivalente en áreas afines.', 'flacso-uruguay'); ?></p>
                            </div>
                        </div>

                        <div class="fc-requirement">
                            <div class="fc-requirement-icon"><i class="bi bi-file-person" aria-hidden="true"></i></div>
                            <div class="fc-requirement-content">
                                <strong><?php esc_html_e('Currículum Vitae', 'flacso-uruguay'); ?></strong>
                                <p><?php esc_html_e('CV actualizado detallando trayectoria académica y profesional.', 'flacso-uruguay'); ?></p>
                            </div>
                        </div>

                        <div class="fc-requirement">
                            <div class="fc-requirement-icon"><i class="bi bi-card-text" aria-hidden="true"></i></div>
                            <div class="fc-requirement-content">
                                <strong><?php esc_html_e('Documento de Identidad', 'flacso-uruguay'); ?></strong>
                                <p><?php esc_html_e('Copia de cédula de identidad o pasaporte vigente.', 'flacso-uruguay'); ?></p>
                            </div>
                        </div>

                        <div class="fc-requirement">
                            <div class="fc-requirement-icon"><i class="bi bi-chat-left-quote" aria-hidden="true"></i></div>
                            <div class="fc-requirement-content">
                                <strong><?php esc_html_e('Carta de motivación', 'flacso-uruguay'); ?></strong>
                                <p><?php esc_html_e('Exposición de motivos e interés en cursar la propuesta.', 'flacso-uruguay'); ?></p>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </section>

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
                                <?php echo wp_kses_post($precios_nota); ?>
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
            </section>

            <!-- INFORMACIÓN IMPORTANTE -->
            <section class="fc-section fc-info-important">
                <h2 class="fc-section-title"><?php esc_html_e('Información importante', 'flacso-uruguay'); ?></h2>

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

                    <?php if ($mostrar_expedicion) : ?>
                        <div class="fc-info-row">
                            <div class="fc-info-icon"><i class="bi bi-award" aria-hidden="true"></i></div>
                            <div><?php esc_html_e('El título se expide en el exterior y tiene un costo asociado de USD 150, que incluye el trámite de Apostilla de La Haya. El costo de envio dependerá del pais de destino.', 'flacso-uruguay'); ?></div>
                        </div>
                    <?php endif; ?>

                    <?php if ($mostrar_costos_envio) : ?>
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
                    <?php endif; ?>

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

            <!-- MÁS INFORMACIÓN (TRAYECTORIA, GESTIÓN, FINANCIACIÓN) -->
            <?php
            $carta_mas_info_section_title = trim((string) get_option('flacso_carta_mas_info_section_title', __('Más información', 'flacso-uruguay')));
            $carta_mas_info_trayectoria_title = trim((string) get_option('flacso_carta_mas_info_trayectoria_title', __('Trayectoria Académica', 'flacso-uruguay')));
            $carta_mas_info_trayectoria_html = (string) get_option('flacso_carta_mas_info_trayectoria_html', '<p>' . __('Nuestra <strong>Facultad de Posgrados</strong> busca formar a sus estudiantes a nivel <strong>académico, profesional y laboral</strong>. Nos distinguen <strong>19 años de trayectoria</strong> a nivel nacional y más de <strong>65 años a nivel internacional</strong>. Además, más de <strong>7000 personas egresadas</strong> de FLACSO Uruguay trabajan en el ámbito público y privado.', 'flacso-uruguay') . '</p>');
            $carta_mas_info_gestion_title = trim((string) get_option('flacso_carta_mas_info_gestion_title', __('Gestión Académica', 'flacso-uruguay')));
            $carta_mas_info_gestion_html = (string) get_option('flacso_carta_mas_info_gestion_html', '<p>' . __('Nos distingue un sistema de <strong>gestión académica eficiente y cercano</strong>, que acompaña de forma <strong>personalizada</strong> a cada estudiante y garantiza <strong>altos niveles de egreso, superiores al 90%</strong>.', 'flacso-uruguay') . '</p>');
            $carta_mas_info_financiacion_title = trim((string) get_option('flacso_carta_mas_info_financiacion_title', __('Financiamiento Flexible', 'flacso-uruguay')));
            $carta_mas_info_financiacion_html = (string) get_option('flacso_carta_mas_info_financiacion_html', '<p>' . __('Puedes abonar el posgrado en <strong>cuotas sin recargo</strong> a lo largo de la cursada. Contamos con <strong>múltiples convenios, descuentos de hasta el 25%</strong> y la posibilidad de acceder a <strong>becas</strong>.', 'flacso-uruguay') . '</p>');
            ?>
            <section class="fc-section">
                <h2 class="fc-section-title"><?php echo esc_html($carta_mas_info_section_title); ?></h2>

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

                    <article class="fc-more-card">
                        <div class="fc-more-icon"><i class="bi bi-currency-dollar" aria-hidden="true"></i></div>
                        <h3><?php echo esc_html($carta_mas_info_financiacion_title); ?></h3>
                        <?php echo wp_kses_post($carta_mas_info_financiacion_html); ?>
                    </article>
                </div>
            </section>

            <!-- ASISTENTE ACADÉMICA -->
            <?php
            $prep_posgrado = $determinante_oferta($titulo);
            $enlace_correo = '<a href="mailto:' . esc_attr($asistente_correo) . '">' . esc_html($asistente_correo) . '</a>';
            $presentacion_asistente = sprintf(
                __('Mi nombre es %1$s y soy %2$s %3$s %4$s. Si tienes dudas o consultas puedes contactarme al correo electrónico %5$s.', 'flacso-uruguay'),
                esc_html($asistente_nombre_completo),
                esc_html($asistente_rol),
                esc_html($prep_posgrado),
                esc_html($titulo),
                $enlace_correo
            );
            ?>
            <section class="fc-assistant-card">
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
                    <span class="fc-assistant-label"><?php echo esc_html($asistente_rol); ?></span>
                    <h2><?php echo esc_html($asistente_nombre_completo); ?></h2>
                    <p><?php echo wp_kses_post($presentacion_asistente); ?></p>
                </div>
            </section>

            <!-- FINAL CTA -->
            <section class="fc-final-cta">
                <div>
                    <span><?php printf(esc_html__('Preinscripciones %s', 'flacso-uruguay'), esc_html($anio)); ?></span>
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
