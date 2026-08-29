<?php
/**
 * Capa de compatibilidad y View Model del Dominio Académico para kadence-child-flacso.
 *
 * Proporciona acceso normalizado a OfertaAcademica, InstanciaOferta, relaciones y
 * preinscripciones, soportando tanto la base legacy (CPT seminario / cohorte)
 * como el modelo académico unificado (Release A y posterior).
 *
 * @package kadence-child-flacso
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Detecta si el modelo académico se encuentra migrado en la base de datos.
 */
function flacso_theme_academic_model_is_migrated(): bool {
    static $is_migrated = null;
    if ($is_migrated !== null) {
        return $is_migrated;
    }

    if (class_exists('FLACSO_Instancia_Oferta') && post_type_exists('instancia-oferta')) {
        $count = wp_count_posts('instancia-oferta');
        if (!empty($count->publish) && (int) $count->publish > 0) {
            $is_migrated = true;
            return true;
        }
    }

    $is_migrated = false;
    return false;
}

/**
 * Obtiene el tipo académico normalizado de una propuesta ('seminario', 'maestria', 'doctorado', etc.).
 */
function flacso_theme_get_offer_type(int $post_id): string {
    if ($post_id <= 0) {
        return '';
    }

    $post_type = get_post_type($post_id);
    if ($post_type === 'seminario') {
        return 'seminario';
    }

    if (class_exists('FLACSO_Oferta_Academica') && method_exists('FLACSO_Oferta_Academica', 'get_tipo')) {
        $type = FLACSO_Oferta_Academica::get_tipo($post_id);
        if ($type !== '') {
            return $type;
        }
    }

    $terms = wp_get_object_terms($post_id, 'tipo-oferta-academica');
    if (!is_wp_error($terms) && !empty($terms)) {
        return sanitize_key((string) $terms[0]->slug);
    }

    return '';
}

/**
 * Obtiene la etiqueta visible del tipo de oferta ('Seminario', 'Maestría', 'Doctorado', etc.).
 */
function flacso_theme_get_offer_type_label(int $post_id, string $type = ''): string {
    if ($type === '') {
        $type = flacso_theme_get_offer_type($post_id);
    }

    $labels = [
        'doctorado'       => __('Doctorado', 'flacso-uruguay'),
        'maestria'        => __('Maestría', 'flacso-uruguay'),
        'especializacion' => __('Especialización', 'flacso-uruguay'),
        'diplomado'       => __('Diplomado', 'flacso-uruguay'),
        'diploma'         => __('Diploma', 'flacso-uruguay'),
        'seminario'       => __('Seminario', 'flacso-uruguay'),
    ];

    return $labels[$type] ?? __('Oferta Académica', 'flacso-uruguay');
}

/**
 * Resuelve el ID de la instancia relevante (Cohorte o Edición) para una oferta académica.
 */
function flacso_theme_get_relevant_instance(int $offer_id): int {
    if ($offer_id <= 0) {
        return 0;
    }

    // 1. Instancia abierta para preinscripciones según el plugin
    if (function_exists('flacso_get_open_preinscription_instance')) {
        $open_instance = flacso_get_open_preinscription_instance($offer_id);
        if ($open_instance > 0) {
            return $open_instance;
        }
    }

    if (function_exists('flacso_get_preinscription_cta')) {
        $cta = flacso_get_preinscription_cta($offer_id);
        if (is_array($cta) && !empty($cta['instance_id']) && (int) $cta['instance_id'] > 0) {
            return (int) $cta['instance_id'];
        }
    }

    // 2. Si existe InstanciaOferta en el sistema, consultar instancias asociadas
    if (class_exists('FLACSO_Instancia_Oferta') && post_type_exists('instancia-oferta')) {
        $instance_posts = get_posts([
            'post_type'      => 'instancia-oferta',
            'post_status'    => current_user_can('edit_posts') ? ['publish', 'draft', 'private'] : ['publish'],
            'meta_key'       => '_flacso_oferta_academica_id',
            'meta_value'     => $offer_id,
            'posts_per_page' => 20,
            'orderby'        => 'meta_value',
            'meta_key_type'  => 'CHAR',
            'meta_compare'   => '=',
            'order'          => 'DESC',
        ]);

        if (!empty($instance_posts)) {
            $today = current_time('Y-m-d');
            $active_or_upcoming = [];
            $fallback_instance = null;

            foreach ($instance_posts as $inst) {
                $inst_id = (int) $inst->ID;
                if ($fallback_instance === null) {
                    $fallback_instance = $inst_id;
                }

                $state = class_exists('FLACSO_Instancia_Oferta')
                    ? FLACSO_Instancia_Oferta::get_state($inst_id)
                    : get_post_meta($inst_id, '_flacso_estado_academico', true);

                $fecha_inicio = (string) get_post_meta($inst_id, '_flacso_fecha_inicio', true);

                if (in_array($state, ['planificada', 'en_curso', 'upcoming', 'active'], true)) {
                    if ($fecha_inicio === '' || $fecha_inicio >= $today) {
                        $active_or_upcoming[] = [
                            'id'           => $inst_id,
                            'fecha_inicio' => $fecha_inicio,
                        ];
                    }
                }
            }

            if (!empty($active_or_upcoming)) {
                usort($active_or_upcoming, static function ($a, $b) {
                    if ($a['fecha_inicio'] === '' && $b['fecha_inicio'] !== '') return 1;
                    if ($b['fecha_inicio'] === '' && $a['fecha_inicio'] !== '') return -1;
                    return strcmp($a['fecha_inicio'], $b['fecha_inicio']);
                });
                return (int) $active_or_upcoming[0]['id'];
            }

            if ($fallback_instance !== null) {
                return (int) $fallback_instance;
            }
        }
    }

    return 0;
}

/**
 * Obtiene las relaciones de una oferta académica según el modelo canónico o legacy.
 *
 * @param int $offer_id
 * @param string $type 'integra' | 'compuesto_por' | 'precede' | ''
 * @return array
 */
function flacso_theme_get_offer_relations(int $offer_id, string $type = ''): array {
    if ($offer_id <= 0) {
        return [];
    }

    if (class_exists('FLACSO_Relacion_Oferta_Academica')) {
        $canonical = FLACSO_Relacion_Oferta_Academica::get($offer_id, $type);
        if (!empty($canonical)) {
            return $canonical;
        }
    }

    // Fallbacks legacy
    $relations = [];
    if ($type === '' || $type === 'integra') {
        $legacy_seminarios = get_post_meta($offer_id, '_oferta_seminarios_ids', true);
        if (is_array($legacy_seminarios)) {
            foreach (array_values(array_unique(array_map('absint', $legacy_seminarios))) as $order => $dest_id) {
                if ($dest_id > 0) {
                    $relations[] = [
                        'oferta_destino' => $dest_id,
                        'tipo_relacion'  => 'integra',
                        'orden'          => $order,
                    ];
                }
            }
        }
    }

    if ($type === '' || $type === 'compuesto_por') {
        $legacy_componentes = get_post_meta($offer_id, '_seminario_seminarios_componentes', true);
        if (is_array($legacy_componentes)) {
            foreach (array_values(array_unique(array_map('absint', $legacy_componentes))) as $order => $dest_id) {
                if ($dest_id > 0) {
                    $relations[] = [
                        'oferta_destino' => $dest_id,
                        'tipo_relacion'  => 'compuesto_por',
                        'orden'          => $order,
                    ];
                }
            }
        }
    }

    return $relations;
}

/**
 * Obtiene las ofertas académicas (programas/posgrados) a las que pertenece o se vincula un seminario.
 */
function flacso_theme_get_related_programs(int $seminar_id): array {
    if ($seminar_id <= 0) {
        return [];
    }

    // 1. Delegar en Seminario_Helpers si está disponible
    if (class_exists('Seminario_Helpers') && method_exists('Seminario_Helpers', 'get_related_offers')) {
        $offers = Seminario_Helpers::get_related_offers($seminar_id);
        if (!empty($offers)) {
            return array_map(static function ($offer) {
                return [
                    'id'    => (int) ($offer['id'] ?? 0),
                    'title' => (string) ($offer['title'] ?? ''),
                    'slug'  => (string) ($offer['slug'] ?? ''),
                    'url'   => (string) ($offer['url'] ?? get_permalink((int) ($offer['id'] ?? 0))),
                ];
            }, $offers);
        }
    }

    // 2. Consulta canónica: ofertas que tienen relación 'integra' hacia este seminario
    $candidate_offers = get_posts([
        'post_type'      => 'oferta-academica',
        'post_status'    => 'publish',
        'posts_per_page' => -1,
        'fields'         => 'ids',
    ]);

    $related = [];
    foreach ($candidate_offers as $candidate_id) {
        $rels = flacso_theme_get_offer_relations((int) $candidate_id, 'integra');
        foreach ($rels as $rel) {
            if ((int) $rel['oferta_destino'] === $seminar_id) {
                $related[] = [
                    'id'    => (int) $candidate_id,
                    'title' => get_the_title($candidate_id),
                    'slug'  => get_post_field('post_name', $candidate_id),
                    'url'   => get_permalink($candidate_id),
                ];
                break;
            }
        }
    }

    return $related;
}

/**
 * Obtiene los componentes de una oferta/seminario integrado.
 */
function flacso_theme_get_integrated_components(int $offer_id): array {
    if ($offer_id <= 0) {
        return [];
    }

    $components_ids = [];

    // Relación canónica compuesto_por
    $rels = flacso_theme_get_offer_relations($offer_id, 'compuesto_por');
    if (!empty($rels)) {
        foreach ($rels as $rel) {
            $components_ids[] = (int) $rel['oferta_destino'];
        }
    } else {
        // Fallback legacy
        $legacy = get_post_meta($offer_id, '_seminario_seminarios_componentes', true);
        if (is_array($legacy)) {
            $components_ids = array_map('absint', $legacy);
        }
    }

    $components_ids = array_values(array_filter(array_unique($components_ids)));
    if (empty($components_ids)) {
        return [];
    }

    $posts = get_posts([
        'post_type'      => ['oferta-academica', 'seminario'],
        'post_status'    => current_user_can('edit_posts') ? ['publish', 'draft', 'private'] : ['publish'],
        'post__in'       => $components_ids,
        'posts_per_page' => -1,
        'orderby'        => 'post__in',
    ]);

    return $posts;
}

/**
 * Obtiene la oferta compuesta / seminario integrado padre si el post actual es un componente.
 */
function flacso_theme_get_integrated_parent(int $offer_id): ?object {
    if ($offer_id <= 0) {
        return null;
    }

    if (class_exists('Seminario_Helpers') && method_exists('Seminario_Helpers', 'get_integrated_parent')) {
        $parent = Seminario_Helpers::get_integrated_parent($offer_id);
        if (is_object($parent)) {
            return $parent;
        }
    }

    // Búsqueda inversa de relación compuesto_por
    $parents = get_posts([
        'post_type'      => ['oferta-academica', 'seminario'],
        'post_status'    => current_user_can('edit_posts') ? ['publish', 'draft', 'private'] : ['publish'],
        'posts_per_page' => 10,
    ]);

    foreach ($parents as $parent_candidate) {
        $cand_id = (int) $parent_candidate->ID;
        if ($cand_id === $offer_id) {
            continue;
        }
        $rels = flacso_theme_get_offer_relations($cand_id, 'compuesto_por');
        foreach ($rels as $rel) {
            if ((int) $rel['oferta_destino'] === $offer_id) {
                return $parent_candidate;
            }
        }
    }

    return null;
}

/**
 * Formatea fechas en formato legible en español.
 */
function flacso_theme_format_date_es($date, bool $include_year = true): string {
    if (empty($date)) {
        return '';
    }

    $timestamp = is_numeric($date) ? (int) $date : strtotime((string) $date);
    if ($timestamp === false || $timestamp <= 0) {
        return (string) $date;
    }

    $dias = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
    $meses = ['', 'enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'];

    $texto = $dias[(int) date('w', $timestamp)] . ' ' . date('j', $timestamp) . ' de ' . $meses[(int) date('n', $timestamp)];
    if ($include_year) {
        $texto .= ' de ' . date('Y', $timestamp);
    }

    return $texto;
}

/**
 * Formatea montos numéricos en moneda local.
 */
function flacso_theme_format_amount($amount): string {
    if ($amount === null || $amount === '') {
        return '';
    }

    $amount = (float) $amount;
    $decimals = ($amount === floor($amount)) ? 0 : 2;
    return number_format($amount, $decimals, ',', '.');
}

/**
 * Renderiza la tarjeta de un docente (para uso en plantillas y partials).
 */
function flacso_theme_render_teacher_card($docente): void {
    if (!is_object($docente) || empty($docente->ID)) {
        return;
    }
    $doc_id = (int) $docente->ID;
    $nombre = (string) get_post_meta($doc_id, 'nombre', true);
    $apellido = (string) get_post_meta($doc_id, 'apellido', true);
    $nombre_completo = ($nombre && $apellido) ? trim($nombre . ' ' . $apellido) : $docente->post_title;
    $prefijo_full = (string) get_post_meta($doc_id, 'prefijo_full', true);
    $cv = (string) get_post_meta($doc_id, 'cv', true);
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
                    [
                        'alt'     => esc_attr($nombre_completo),
                        'loading' => 'lazy',
                        'sizes'   => '82px',
                    ]
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

/**
 * Construye el View Model normalizado y completo para cualquier propuesta académica.
 *
 * @param int $offer_id
 * @return array
 */
function flacso_theme_get_offer_context(int $offer_id): array {
    if ($offer_id <= 0) {
        return [];
    }

    $post = get_post($offer_id);
    if (!$post) {
        return [];
    }

    $type = flacso_theme_get_offer_type($offer_id);
    $is_seminar = ($type === 'seminario');
    $type_label = flacso_theme_get_offer_type_label($offer_id, $type);

    // Identidad
    $title = get_the_title($offer_id);
    $slug = (string) $post->post_name;
    $permalink = get_permalink($offer_id);
    $thumbnail_id = (int) get_post_thumbnail_id($offer_id);
    $thumbnail_url = (string) get_the_post_thumbnail_url($offer_id, 'full');

    // Instancia Relevante
    $instance_id = flacso_theme_get_relevant_instance($offer_id);

    // Contrato de Preinscripción
    $cta = function_exists('flacso_get_preinscription_cta')
        ? flacso_get_preinscription_cta($offer_id)
        : null;

    $is_open = false;
    $preinscripcion_url = '';
    $flow = 'legacy_editor';
    $open_message = '';
    $closed_message = '';

    if (is_array($cta)) {
        $is_open = !empty($cta['is_open']);
        $preinscripcion_url = (string) ($cta['url'] ?? '');
        $flow = (string) ($cta['flow'] ?? 'legacy_editor');
        $open_message = (string) ($cta['open_message'] ?? '');
        $closed_message = (string) ($cta['closed_message'] ?? '');
    } else {
        $is_open = function_exists('flacso_offer_accepts_preinscriptions')
            ? flacso_offer_accepts_preinscriptions($offer_id)
            : (!empty(get_post_meta($offer_id, 'inscripciones_abiertas', true)) || !empty(get_post_meta($offer_id, '_seminario_abierto_publico', true)));
        $preinscripcion_url = apply_filters('flacso_preinscripcion_url', '', $offer_id);
    }

    // Datos Académicos (Identidad permanente de OfertaAcademica)
    $raw_presentacion = get_post_meta($offer_id, '_seminario_presentacion_seminario', true);
    if (empty($raw_presentacion)) {
        $raw_presentacion = get_post_meta($offer_id, 'presentacion', true);
    }

    $objetivo_general = (string) get_post_meta($offer_id, '_seminario_objetivo_general', true);
    $objetivos_especificos = get_post_meta($offer_id, '_seminario_objetivos_especificos', true);
    $objetivos_especificos = is_array($objetivos_especificos) ? array_values(array_filter($objetivos_especificos)) : [];

    $unidades_academicas = get_post_meta($offer_id, '_seminario_unidades_academicas', true);
    $unidades_academicas = is_array($unidades_academicas) ? array_values(array_filter($unidades_academicas)) : [];

    $forma_aprobacion = (string) get_post_meta($offer_id, '_seminario_forma_aprobacion', true);
    $carga_horaria = get_post_meta($offer_id, '_seminario_carga_horaria', true);
    $creditos = get_post_meta($offer_id, '_seminario_creditos', true);
    $acredita_maestria = (bool) get_post_meta($offer_id, '_seminario_acredita_maestria', true);
    $acredita_doctorado = (bool) get_post_meta($offer_id, '_seminario_acredita_doctorado', true);

    // Datos para Ofertas Estándar (CPT oferta-academica schema)
    $schema_data = class_exists('Oferta_Data_Schema') ? Oferta_Data_Schema::get_schema($offer_id) : [];
    if (!is_array($schema_data)) {
        $schema_data = [];
    }

    // Relaciones de Composición (Seminarios Integrados)
    $components_posts = flacso_theme_get_integrated_components($offer_id);
    $is_integrated = !empty($components_posts) || !empty(get_post_meta($offer_id, '_seminario_es_integrado', true));
    $integrated_parent_post = !$is_integrated ? flacso_theme_get_integrated_parent($offer_id) : null;
    $related_programs = flacso_theme_get_related_programs($offer_id);

    // Datos de Instancia Relevante (o agregados si es integrado)
    $instance_label = '';
    $fecha_inicio = '';
    $fecha_fin = '';
    $precision_fecha_inicio = '';
    $estado_academico = 'planificada';
    $modalidad = '';
    $es_asincronico = false;
    $docentes_ids = [];
    $encuentros = [];
    $valor_uyu = null;
    $valor_usd = null;
    $valor_uyu_15_descuento = null;
    $valor_usd_15_descuento = null;

    if ($instance_id > 0) {
        $instance_label = class_exists('FLACSO_Instancia_Oferta')
            ? FLACSO_Instancia_Oferta::get_nombre_visible($instance_id)
            : (string) get_the_title($instance_id);

        $fecha_inicio = (string) get_post_meta($instance_id, '_flacso_fecha_inicio', true);
        $fecha_fin = (string) get_post_meta($instance_id, '_flacso_fecha_fin', true);
        $precision_fecha_inicio = (string) get_post_meta($instance_id, '_flacso_precision_fecha_inicio', true);
        $estado_academico = (string) get_post_meta($instance_id, '_flacso_estado_academico', true);
        $modalidad = (string) get_post_meta($instance_id, '_flacso_modalidad', true);
        $es_asincronico = (bool) get_post_meta($instance_id, '_flacso_es_asincronico', true);

        $docentes_meta = get_post_meta($instance_id, '_flacso_docentes', true);
        $docentes_ids = is_array($docentes_meta) ? array_map('absint', $docentes_meta) : [];

        $encuentros_meta = get_post_meta($instance_id, '_flacso_encuentros_sincronicos', true);
        $encuentros = is_array($encuentros_meta) ? array_values(array_filter($encuentros_meta)) : [];

        $v_uyu = get_post_meta($instance_id, '_flacso_valor_uyu', true);
        $valor_uyu = ($v_uyu !== '' && $v_uyu !== null) ? (float) $v_uyu : null;

        $v_usd = get_post_meta($instance_id, '_flacso_valor_usd', true);
        $valor_usd = ($v_usd !== '' && $v_usd !== null) ? (float) $v_usd : null;

        $v_uyu_desc = get_post_meta($instance_id, '_flacso_valor_uyu_15_descuento', true);
        $valor_uyu_15_descuento = ($v_uyu_desc !== '' && $v_uyu_desc !== null) ? (float) $v_uyu_desc : null;

        $v_usd_desc = get_post_meta($instance_id, '_flacso_valor_usd_15_descuento', true);
        $valor_usd_15_descuento = ($v_usd_desc !== '' && $v_usd_desc !== null) ? (float) $v_usd_desc : null;
    } else {
        // Fallback a metadatos directos del post (soporte pre-migración)
        $raw_meta = class_exists('Seminario_Meta') ? Seminario_Meta::get_meta($offer_id) : [];
        $meta = class_exists('Seminario_Helpers') ? Seminario_Helpers::get_display_meta($offer_id, $raw_meta) : $raw_meta;

        $fecha_inicio = $meta['periodo_inicio'] ?? (string) get_post_meta($offer_id, '_seminario_periodo_inicio', true);
        if ($fecha_inicio === '') {
            $fecha_inicio = (string) get_post_meta($offer_id, 'proximo_inicio', true);
        }

        $fecha_fin = $meta['periodo_fin'] ?? (string) get_post_meta($offer_id, '_seminario_periodo_fin', true);
        $precision_fecha_inicio = (string) get_post_meta($offer_id, 'proximo_inicio_precision', true);
        $modalidad = $meta['modalidad'] ?? (string) get_post_meta($offer_id, '_seminario_modalidad', true);
        $es_asincronico = !empty($meta['es_asincronico']);

        $doc_meta = $meta['docentes'] ?? get_post_meta($offer_id, '_seminario_docentes', true);
        $docentes_ids = is_array($doc_meta) ? array_map('absint', $doc_meta) : [];

        $enc_meta = $meta['encuentros_sincronicos'] ?? get_post_meta($offer_id, '_seminario_encuentros_sincronicos', true);
        $encuentros = is_array($enc_meta) ? array_values(array_filter($enc_meta)) : [];

        $v_uyu = $meta['valor_uyu'] ?? get_post_meta($offer_id, '_seminario_valor_uyu', true);
        $valor_uyu = ($v_uyu !== '' && $v_uyu !== null) ? (float) $v_uyu : null;

        $v_usd = $meta['valor_usd'] ?? get_post_meta($offer_id, '_seminario_valor_usd', true);
        $valor_usd = ($v_usd !== '' && $v_usd !== null) ? (float) $v_usd : null;

        $v_uyu_desc = $meta['valor_uyu_15_descuento'] ?? get_post_meta($offer_id, '_seminario_valor_uyu_15_descuento', true);
        $valor_uyu_15_descuento = ($v_uyu_desc !== '' && $v_uyu_desc !== null) ? (float) $v_uyu_desc : null;

        $v_usd_desc = $meta['valor_usd_15_descuento'] ?? get_post_meta($offer_id, '_seminario_valor_usd_15_descuento', true);
        $valor_usd_15_descuento = ($v_usd_desc !== '' && $v_usd_desc !== null) ? (float) $v_usd_desc : null;

        $cohorte_meta = trim((string) get_post_meta($offer_id, 'cohorte', true));
        $instance_label = $cohorte_meta !== '' ? $cohorte_meta : ($is_seminar ? __('Edición', 'flacso-uruguay') : __('Cohorte', 'flacso-uruguay'));
    }

    // Comportamiento Agregado para Seminarios Integrados
    if ($is_integrated && !empty($components_posts)) {
        $comp_inicios = [];
        $comp_fines = [];
        $comp_horas_total = 0;
        $comp_creditos_total = 0;
        $comp_docentes_all = [];
        $comp_encuentros_all = [];

        foreach ($components_posts as $component_post) {
            $comp_id = (int) $component_post->ID;
            $comp_context = flacso_theme_get_offer_context($comp_id);

            if (!empty($comp_context['instance']['fecha_inicio'])) {
                $comp_inicios[] = $comp_context['instance']['fecha_inicio'];
            }
            if (!empty($comp_context['instance']['fecha_fin'])) {
                $comp_fines[] = $comp_context['instance']['fecha_fin'];
            }
            if (!empty($comp_context['academic']['carga_horaria'])) {
                $comp_horas_total += (int) $comp_context['academic']['carga_horaria'];
            }
            if (!empty($comp_context['academic']['creditos'])) {
                $comp_creditos_total += (float) $comp_context['academic']['creditos'];
            }
            if (!empty($comp_context['instance']['docentes_ids'])) {
                $comp_docentes_all = array_merge($comp_docentes_all, $comp_context['instance']['docentes_ids']);
            }
            if (!empty($comp_context['instance']['encuentros'])) {
                $comp_encuentros_all = array_merge($comp_encuentros_all, $comp_context['instance']['encuentros']);
            }
        }

        if (!empty($comp_inicios) && empty($fecha_inicio)) {
            sort($comp_inicios);
            $fecha_inicio = $comp_inicios[0];
        }
        if (!empty($comp_fines) && empty($fecha_fin)) {
            rsort($comp_fines);
            $fecha_fin = $comp_fines[0];
        }
        if ($comp_horas_total > 0 && empty($carga_horaria)) {
            $carga_horaria = $comp_horas_total;
        }
        if ($comp_creditos_total > 0 && empty($creditos)) {
            $creditos = $comp_creditos_total;
        }
        if (empty($docentes_ids)) {
            $docentes_ids = array_values(array_unique(array_filter($comp_docentes_all)));
        }
        if (empty($encuentros)) {
            $encuentros = $comp_encuentros_all;
        }
    }

    // Carga de posts de docentes
    $docentes_posts = [];
    if (!empty($docentes_ids)) {
        $docentes_posts = get_posts([
            'post_type'      => 'docente',
            'post__in'       => $docentes_ids,
            'posts_per_page' => -1,
            'orderby'        => 'post__in',
        ]);
    }

    $has_valor_general = ($valor_uyu !== null || $valor_usd !== null);
    $has_valor_descuento = ($valor_uyu_15_descuento !== null || $valor_usd_15_descuento !== null);
    $has_inversion = $has_valor_general || $has_valor_descuento;

    return [
        'offer_id'       => $offer_id,
        'instance_id'    => $instance_id,
        'type'           => $type,
        'type_label'     => $type_label,
        'is_seminar'     => $is_seminar,
        'is_integrated'  => $is_integrated,
        'title'          => $title,
        'slug'           => $slug,
        'permalink'      => $permalink,
        'thumbnail_id'   => $thumbnail_id,
        'thumbnail_url'  => $thumbnail_url,
        'academic'       => [
            'presentacion'           => $raw_presentacion,
            'objetivo_general'       => $objetivo_general,
            'objetivos_especificos'  => $objetivos_especificos,
            'unidades_academicas'    => $unidades_academicas,
            'forma_aprobacion'       => $forma_aprobacion,
            'carga_horaria'          => $carga_horaria,
            'creditos'               => $creditos,
            'acredita_maestria'      => $acredita_maestria,
            'acredita_doctorado'     => $acredita_doctorado,
            // Schema para ofertas estándar
            'descripcion_html'       => $schema_data['descripcion_html'] ?? '',
            'modalidad_html'         => $schema_data['modalidad_html'] ?? '',
            'duracion_html'          => $schema_data['duracion_html'] ?? '',
            'duracion_meses'         => $schema_data['duracion_meses'] ?? '',
            'objetivos_html'         => $schema_data['objetivos_html'] ?? '',
            'perfil_ingreso_html'    => $schema_data['perfil_ingreso_html'] ?? '',
            'requisitos_ingreso_html'=> $schema_data['requisitos_ingreso_html'] ?? '',
            'certificacion_html'     => $schema_data['certificacion_html'] ?? '',
            'aranceles_html'         => $schema_data['aranceles_html'] ?? '',
            'faq_html'               => $schema_data['faq_html'] ?? '',
            'pdf_malla_url'          => $schema_data['pdf_malla_url'] ?? '',
            'pdf_calendario_url'     => $schema_data['pdf_calendario_url'] ?? '',
            'pdf_cartamalla_url'     => $schema_data['pdf_cartamalla_url'] ?? '',
        ],
        'instance'       => [
            'id'                     => $instance_id,
            'label'                  => $instance_label,
            'fecha_inicio'           => $fecha_inicio,
            'fecha_fin'              => $fecha_fin,
            'precision_fecha_inicio' => $precision_fecha_inicio,
            'estado'                 => $estado_academico,
            'modalidad'              => $modalidad,
            'es_asincronico'         => $es_asincronico,
            'docentes_ids'           => $docentes_ids,
            'docentes_posts'         => $docentes_posts,
            'encuentros'             => $encuentros,
            'valor_uyu'              => $valor_uyu,
            'valor_usd'              => $valor_usd,
            'valor_uyu_15_descuento' => $valor_uyu_15_descuento,
            'valor_usd_15_descuento' => $valor_usd_15_descuento,
            'has_inversion'          => $has_inversion,
            'has_valor_general'      => $has_valor_general,
            'has_valor_descuento'    => $has_valor_descuento,
        ],
        'registration'   => [
            'is_open'        => $is_open,
            'url'            => $preinscripcion_url,
            'instance_id'    => is_array($cta) ? (int) ($cta['instance_id'] ?? 0) : $instance_id,
            'flow'           => $flow,
            'open_message'   => $open_message,
            'closed_message' => $closed_message,
        ],
        'relations'      => [
            'is_integrated'          => $is_integrated,
            'integrated_parent'      => $integrated_parent_post ? [
                'id'    => (int) $integrated_parent_post->ID,
                'title' => get_the_title($integrated_parent_post),
                'url'   => get_permalink($integrated_parent_post),
            ] : null,
            'integrated_components'  => $components_posts,
            'related_programs'       => $related_programs,
        ],
    ];
}

/**
 * Adaptador específico para contexto de seminario (usado en single-seminario.php y template-parts/oferta/seminario.php).
 */
function flacso_theme_get_seminar_context(int $post_id): array {
    return flacso_theme_get_offer_context($post_id);
}

/**
 * Obtiene de forma eficiente en batch el catálogo de todos los seminarios para la vista de listado.
 *
 * @param array $args
 * @return array
 */
function flacso_theme_get_seminars_catalog(array $args = []): array {
    $post_types = ['seminario'];
    if (post_type_exists('oferta-academica')) {
        $post_types[] = 'oferta-academica';
    }

    $tax_query = [];
    // Si consultamos oferta-academica, limitamos a la taxonomía seminario
    $query_args = [
        'post_type'      => ['seminario', 'oferta-academica'],
        'post_status'    => current_user_can('edit_posts') ? ['publish', 'draft', 'private'] : ['publish'],
        'posts_per_page' => -1,
        'orderby'        => 'title',
        'order'          => 'ASC',
    ];

    $all_posts = get_posts($query_args);
    $seminar_posts = [];
    $seen_ids = [];

    foreach ($all_posts as $p) {
        $pid = (int) $p->ID;
        if (isset($seen_ids[$pid])) {
            continue;
        }

        $ptype = get_post_type($pid);
        if ($ptype === 'seminario') {
            $seminar_posts[] = $p;
            $seen_ids[$pid] = true;
        } elseif ($ptype === 'oferta-academica') {
            if (flacso_theme_get_offer_type($pid) === 'seminario') {
                $seminar_posts[] = $p;
                $seen_ids[$pid] = true;
            }
        }
    }

    $catalog = [];
    foreach ($seminar_posts as $seminar_post) {
        $sid = (int) $seminar_post->ID;
        $context = flacso_theme_get_offer_context($sid);
        if (empty($context)) {
            continue;
        }

        $catalog[] = $context;
    }

    return $catalog;
}
