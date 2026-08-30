<?php
/**
 * Read-only presentation adapter for the academic domain.
 *
 * The plugin owns OfertaAcademica, InstanciaOferta, relations and registration
 * rules. This file only builds stable view models and keeps Release A legacy
 * reads behind one boundary.
 *
 * @package kadence-child-flacso
 */

if (!defined('ABSPATH')) {
    exit;
}

function flacso_theme_get_offer_type(int $post_id): string {
    if ($post_id <= 0) {
        return '';
    }

    if (class_exists('FLACSO_Oferta_Academica')) {
        return (string) FLACSO_Oferta_Academica::get_tipo($post_id);
    }

    if (get_post_type($post_id) === 'seminario') {
        return 'seminario';
    }

    $terms = wp_get_object_terms($post_id, 'tipo-oferta-academica');
    return !is_wp_error($terms) && !empty($terms)
        ? sanitize_key((string) $terms[0]->slug)
        : '';
}

function flacso_theme_is_seminar(int $post_id): bool {
    return flacso_theme_get_offer_type($post_id) === 'seminario';
}

function flacso_theme_get_offer_type_label(int $post_id, string $type = ''): string {
    $type = $type !== '' ? $type : flacso_theme_get_offer_type($post_id);
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

/** Resolve a plugin-owned instance meta key without copying its schema. */
function flacso_theme_instance_meta_key(string $field): string {
    $keys = [
        'oferta_id'              => ['META_OFERTA_ID', 'oferta_academica_id'],
        'fecha_inicio'           => ['META_FECHA_INICIO', 'fecha_inicio'],
        'fecha_fin'              => ['META_FECHA_FIN', 'fecha_fin'],
        'precision_fecha_inicio' => ['META_PRECISION_FECHA_INICIO', 'precision_fecha_inicio'],
        'estado'                 => ['META_ESTADO', 'estado'],
        'modalidad'              => ['META_MODALIDAD', 'modalidad'],
        'es_asincronico'         => ['META_ES_ASINCRONICO', 'es_asincronico'],
        'docentes'               => ['META_DOCENTES', 'docentes'],
        'encuentros'             => ['META_ENCUENTROS', 'encuentros_sincronicos'],
        'valor_uyu'              => ['META_VALOR_UYU', 'valor_uyu'],
        'valor_usd'              => ['META_VALOR_USD', 'valor_usd'],
        'valor_uyu_descuento'    => ['META_VALOR_UYU_DESCUENTO', 'valor_uyu_15_descuento'],
        'valor_usd_descuento'    => ['META_VALOR_USD_DESCUENTO', 'valor_usd_15_descuento'],
    ];

    if (!isset($keys[$field])) {
        return '';
    }

    [$constant_name, $fallback] = $keys[$field];
    $constant = 'FLACSO_Instancia_Oferta::' . $constant_name;
    return class_exists('FLACSO_Instancia_Oferta') && defined($constant)
        ? (string) constant($constant)
        : $fallback;
}

function flacso_theme_get_relevant_instance(int $offer_id): int {
    if ($offer_id <= 0) {
        return 0;
    }

    if (function_exists('flacso_get_preinscription_cta')) {
        $cta = flacso_get_preinscription_cta($offer_id);
        if (is_array($cta) && !empty($cta['instance_id'])) {
            return absint($cta['instance_id']);
        }
    }

    if (class_exists('FLACSO_Instancia_Oferta')) {
        $open = FLACSO_Instancia_Oferta::find_open_for_offer($offer_id);
        return $open > 0 ? $open : FLACSO_Instancia_Oferta::find_latest_for_offer($offer_id);
    }

    $ids = get_posts([
        'post_type'      => 'instancia-oferta',
        'post_status'    => 'publish',
        'posts_per_page' => 1,
        'fields'         => 'ids',
        'orderby'        => 'modified',
        'order'          => 'DESC',
        'meta_query'     => [[
            'key'   => flacso_theme_instance_meta_key('oferta_id'),
            'value' => $offer_id,
        ]],
    ]);
    return !empty($ids) ? absint($ids[0]) : 0;
}

function flacso_theme_get_offer_relations(int $offer_id, string $type = ''): array {
    if ($offer_id <= 0) {
        return [];
    }

    if (class_exists('FLACSO_Relacion_Oferta_Academica')) {
        $relations = FLACSO_Relacion_Oferta_Academica::get($offer_id, $type);
        if (metadata_exists('post', $offer_id, FLACSO_Relacion_Oferta_Academica::META_KEY) || !empty($relations)) {
            return $relations;
        }
    }

    $relations = [];
    $legacy_keys = [
        'integra'       => '_oferta_seminarios_ids',
        'compuesto_por' => '_seminario_seminarios_componentes',
    ];
    foreach ($legacy_keys as $relation_type => $meta_key) {
        if ($type !== '' && $type !== $relation_type) {
            continue;
        }
        $ids = get_post_meta($offer_id, $meta_key, true);
        if (!is_array($ids)) {
            continue;
        }
        $seen = [];
        foreach ($ids as $order => $destination) {
            $destination = absint($destination);
            if ($destination <= 0 || isset($seen[$destination])) {
                continue;
            }
            $seen[$destination] = true;
            $relations[] = [
                'oferta_destino' => $destination,
                'tipo_relacion'  => $relation_type,
                'orden'          => absint($order),
            ];
        }
    }
    return $relations;
}

function flacso_theme_get_integrated_components(int $offer_id): array {
    $ids = array_map(static function (array $relation): int {
        return absint($relation['oferta_destino'] ?? 0);
    }, flacso_theme_get_offer_relations($offer_id, 'compuesto_por'));
    $ids = array_values(array_filter(array_unique($ids)));
    if (empty($ids)) {
        return [];
    }
    return get_posts([
        'post_type'      => ['oferta-academica', 'seminario'],
        'post_status'    => 'publish',
        'post__in'       => $ids,
        'posts_per_page' => -1,
        'orderby'        => 'post__in',
    ]);
}

function flacso_theme_get_integrated_parent(int $offer_id) {
    if ($offer_id <= 0) {
        return null;
    }
    $parents = array_merge(
        get_posts(['post_type' => 'oferta-academica', 'post_status' => 'publish', 'posts_per_page' => -1]),
        get_posts(['post_type' => 'seminario', 'post_status' => 'publish', 'posts_per_page' => -1])
    );
    foreach ($parents as $parent) {
        if ((int) $parent->ID === $offer_id) {
            continue;
        }
        foreach (flacso_theme_get_offer_relations((int) $parent->ID, 'compuesto_por') as $relation) {
            if (absint($relation['oferta_destino'] ?? 0) === $offer_id) {
                return $parent;
            }
        }
    }
    return null;
}

function flacso_theme_get_related_programs(int $seminar_id): array {
    $programs = get_posts([
        'post_type'      => 'oferta-academica',
        'post_status'    => 'publish',
        'posts_per_page' => -1,
    ]);
    $related = [];
    foreach ($programs as $program) {
        if (flacso_theme_is_seminar((int) $program->ID)) {
            continue;
        }
        foreach (flacso_theme_get_offer_relations((int) $program->ID, 'integra') as $relation) {
            if (absint($relation['oferta_destino'] ?? 0) === $seminar_id) {
                $related[] = [
                    'id'    => (int) $program->ID,
                    'title' => get_the_title($program),
                    'slug'  => (string) $program->post_name,
                    'url'   => get_permalink($program),
                ];
                break;
            }
        }
    }
    return $related;
}

function flacso_theme_get_registration_context(int $offer_id): array {
    if (function_exists('flacso_get_preinscription_cta')) {
        $cta = flacso_get_preinscription_cta($offer_id);
        if (is_array($cta)) {
            return [
                'is_open'        => !empty($cta['is_open']),
                'url'            => (string) ($cta['url'] ?? ''),
                'instance_id'    => absint($cta['instance_id'] ?? 0),
                'flow'           => (string) ($cta['flow'] ?? 'legacy_editor'),
                'open_message'   => (string) ($cta['open_message'] ?? ''),
                'closed_message' => (string) ($cta['closed_message'] ?? ''),
            ];
        }
    }

    $legacy_key = get_post_type($offer_id) === 'seminario'
        ? '_seminario_abierto_publico'
        : 'inscripciones_abiertas';
    $is_open = in_array(get_post_meta($offer_id, $legacy_key, true), [true, 1, '1', 'true'], true);
    $url = function_exists('flacso_get_preinscription_url')
        ? flacso_get_preinscription_url(0, $offer_id)
        : '';
    return [
        'is_open'        => $is_open,
        'url'            => $url,
        'instance_id'    => 0,
        'flow'           => 'legacy_editor',
        'open_message'   => (string) get_post_meta($offer_id, 'inscripciones_mensaje', true),
        'closed_message' => (string) get_post_meta($offer_id, 'inscripciones_mensaje_cerrado', true),
    ];
}

function flacso_theme_meta_array(int $post_id, string $key): array {
    $value = get_post_meta($post_id, $key, true);
    return is_array($value) ? array_values(array_filter($value)) : [];
}

function flacso_theme_nullable_number($value) {
    return $value === '' || $value === null ? null : (float) $value;
}

function flacso_theme_get_offer_context(int $offer_id): array {
    $post = $offer_id > 0 ? get_post($offer_id) : null;
    if (!$post || !in_array($post->post_type, ['oferta-academica', 'seminario'], true)) {
        return [];
    }

    $type = flacso_theme_get_offer_type($offer_id);
    $is_seminar = $type === 'seminario';
    $instance_id = flacso_theme_get_relevant_instance($offer_id);
    $registration = flacso_theme_get_registration_context($offer_id);
    if ($instance_id <= 0 && !empty($registration['instance_id'])) {
        $instance_id = (int) $registration['instance_id'];
    }

    $schema = class_exists('Oferta_Data_Schema') ? Oferta_Data_Schema::get_schema($offer_id) : [];
    $schema = is_array($schema) ? $schema : [];
    $academic = [
        'presentacion'            => get_post_meta($offer_id, '_seminario_presentacion_seminario', true) ?: get_post_meta($offer_id, 'presentacion', true),
        'objetivo_general'        => (string) get_post_meta($offer_id, '_seminario_objetivo_general', true),
        'objetivos_especificos'   => flacso_theme_meta_array($offer_id, '_seminario_objetivos_especificos'),
        'unidades_academicas'     => flacso_theme_meta_array($offer_id, '_seminario_unidades_academicas'),
        'forma_aprobacion'        => (string) get_post_meta($offer_id, '_seminario_forma_aprobacion', true),
        'carga_horaria'           => get_post_meta($offer_id, '_seminario_carga_horaria', true),
        'creditos'                => get_post_meta($offer_id, '_seminario_creditos', true),
        'acredita_maestria'       => (bool) get_post_meta($offer_id, '_seminario_acredita_maestria', true),
        'acredita_doctorado'      => (bool) get_post_meta($offer_id, '_seminario_acredita_doctorado', true),
        'descripcion_html'        => $schema['descripcion_html'] ?? '',
        'modalidad_html'          => $schema['modalidad_html'] ?? '',
        'duracion_html'           => $schema['duracion_html'] ?? '',
        'duracion_meses'          => $schema['duracion_meses'] ?? '',
        'objetivos_html'          => $schema['objetivos_html'] ?? '',
        'perfil_ingreso_html'     => $schema['perfil_ingreso_html'] ?? '',
        'requisitos_ingreso_html' => $schema['requisitos_ingreso_html'] ?? '',
        'certificacion_html'      => $schema['certificacion_html'] ?? '',
        'aranceles_html'          => $schema['aranceles_html'] ?? '',
        'faq_html'                => $schema['faq_html'] ?? '',
        'pdf_malla_url'           => $schema['pdf_malla_url'] ?? '',
        'pdf_calendario_url'      => $schema['pdf_calendario_url'] ?? '',
        'pdf_cartamalla_url'      => $schema['pdf_cartamalla_url'] ?? '',
    ];

    $instance = [
        'id'                     => $instance_id,
        'label'                  => $is_seminar ? __('Edición', 'flacso-uruguay') : __('Cohorte', 'flacso-uruguay'),
        'fecha_inicio'           => '',
        'fecha_fin'              => '',
        'precision_fecha_inicio' => '',
        'estado'                 => 'planificada',
        'modalidad'              => '',
        'es_asincronico'         => false,
        'docentes_ids'           => [],
        'encuentros'             => [],
        'valor_uyu'              => null,
        'valor_usd'              => null,
        'valor_uyu_15_descuento' => null,
        'valor_usd_15_descuento' => null,
    ];

    if ($instance_id > 0) {
        $instance['label'] = class_exists('FLACSO_Instancia_Oferta')
            ? FLACSO_Instancia_Oferta::get_nombre_visible($instance_id)
            : (string) get_the_title($instance_id);
        foreach (['fecha_inicio', 'fecha_fin', 'precision_fecha_inicio', 'modalidad'] as $field) {
            $instance[$field] = (string) get_post_meta($instance_id, flacso_theme_instance_meta_key($field), true);
        }
        $instance['estado'] = class_exists('FLACSO_Instancia_Oferta')
            ? FLACSO_Instancia_Oferta::get_state($instance_id)
            : (string) get_post_meta($instance_id, flacso_theme_instance_meta_key('estado'), true);
        $instance['es_asincronico'] = (bool) get_post_meta($instance_id, flacso_theme_instance_meta_key('es_asincronico'), true);
        $instance['docentes_ids'] = array_map('absint', flacso_theme_meta_array($instance_id, flacso_theme_instance_meta_key('docentes')));
        $instance['encuentros'] = flacso_theme_meta_array($instance_id, flacso_theme_instance_meta_key('encuentros'));
        $instance['valor_uyu'] = flacso_theme_nullable_number(get_post_meta($instance_id, flacso_theme_instance_meta_key('valor_uyu'), true));
        $instance['valor_usd'] = flacso_theme_nullable_number(get_post_meta($instance_id, flacso_theme_instance_meta_key('valor_usd'), true));
        $instance['valor_uyu_15_descuento'] = flacso_theme_nullable_number(get_post_meta($instance_id, flacso_theme_instance_meta_key('valor_uyu_descuento'), true));
        $instance['valor_usd_15_descuento'] = flacso_theme_nullable_number(get_post_meta($instance_id, flacso_theme_instance_meta_key('valor_usd_descuento'), true));
    } else {
        $legacy = [
            'fecha_inicio'           => $is_seminar ? '_seminario_periodo_inicio' : 'proximo_inicio',
            'fecha_fin'              => $is_seminar ? '_seminario_periodo_fin' : 'fecha_fin',
            'precision_fecha_inicio' => 'proximo_inicio_precision',
            'modalidad'              => $is_seminar ? '_seminario_modalidad' : 'modalidad',
        ];
        foreach ($legacy as $field => $key) {
            $instance[$field] = (string) get_post_meta($offer_id, $key, true);
        }
        $instance['label'] = (string) get_post_meta($offer_id, 'cohorte', true) ?: $instance['label'];
        $instance['es_asincronico'] = (bool) get_post_meta($offer_id, '_seminario_es_asincronico', true);
        $instance['docentes_ids'] = array_map('absint', flacso_theme_meta_array($offer_id, '_seminario_docentes'));
        $instance['encuentros'] = flacso_theme_meta_array($offer_id, '_seminario_encuentros_sincronicos');
        $instance['valor_uyu'] = flacso_theme_nullable_number(get_post_meta($offer_id, '_seminario_valor_uyu', true));
        $instance['valor_usd'] = flacso_theme_nullable_number(get_post_meta($offer_id, '_seminario_valor_usd', true));
        $instance['valor_uyu_15_descuento'] = flacso_theme_nullable_number(get_post_meta($offer_id, '_seminario_valor_uyu_15_descuento', true));
        $instance['valor_usd_15_descuento'] = flacso_theme_nullable_number(get_post_meta($offer_id, '_seminario_valor_usd_15_descuento', true));
    }

    $components = flacso_theme_get_integrated_components($offer_id);
    $is_integrated = !empty($components) || (bool) get_post_meta($offer_id, '_seminario_es_integrado', true);
    $parent = !$is_integrated ? flacso_theme_get_integrated_parent($offer_id) : null;

    if ($is_integrated && !empty($components)) {
        $starts = $ends = $teachers = $meetings = [];
        $hours = $credits = 0;
        foreach ($components as $component) {
            $child = flacso_theme_get_offer_context((int) $component->ID);
            if (!empty($child['instance']['fecha_inicio'])) {
                $starts[] = $child['instance']['fecha_inicio'];
            }
            if (!empty($child['instance']['fecha_fin'])) {
                $ends[] = $child['instance']['fecha_fin'];
            }
            $hours += (float) ($child['academic']['carga_horaria'] ?? 0);
            $credits += (float) ($child['academic']['creditos'] ?? 0);
            $teachers = array_merge($teachers, $child['instance']['docentes_ids'] ?? []);
            $meetings = array_merge($meetings, $child['instance']['encuentros'] ?? []);
        }
        sort($starts);
        rsort($ends);
        $instance['fecha_inicio'] = $instance['fecha_inicio'] ?: ($starts[0] ?? '');
        $instance['fecha_fin'] = $instance['fecha_fin'] ?: ($ends[0] ?? '');
        $academic['carga_horaria'] = $academic['carga_horaria'] ?: ($hours ?: '');
        $academic['creditos'] = $academic['creditos'] ?: ($credits ?: '');
        $instance['docentes_ids'] = $instance['docentes_ids'] ?: array_values(array_unique(array_map('absint', $teachers)));
        $instance['encuentros'] = $instance['encuentros'] ?: $meetings;
    }

    $instance['docentes_posts'] = empty($instance['docentes_ids']) ? [] : get_posts([
        'post_type'      => 'docente',
        'post_status'    => 'publish',
        'post__in'       => $instance['docentes_ids'],
        'posts_per_page' => -1,
        'orderby'        => 'post__in',
    ]);
    $instance['has_valor_general'] = $instance['valor_uyu'] !== null || $instance['valor_usd'] !== null;
    $instance['has_valor_descuento'] = $instance['valor_uyu_15_descuento'] !== null || $instance['valor_usd_15_descuento'] !== null;
    $instance['has_inversion'] = $instance['has_valor_general'] || $instance['has_valor_descuento'];

    return [
        'offer_id'      => $offer_id,
        'instance_id'   => $instance_id,
        'type'          => $type,
        'type_label'    => flacso_theme_get_offer_type_label($offer_id, $type),
        'is_seminar'    => $is_seminar,
        'is_integrated' => $is_integrated,
        'title'         => get_the_title($offer_id),
        'slug'          => (string) $post->post_name,
        'permalink'     => get_permalink($offer_id),
        'thumbnail_id'  => (int) get_post_thumbnail_id($offer_id),
        'thumbnail_url' => (string) get_the_post_thumbnail_url($offer_id, 'full'),
        'academic'      => $academic,
        'instance'      => $instance,
        'registration'  => $registration,
        'relations'     => [
            'is_integrated'         => $is_integrated,
            'integrated_parent'     => $parent ? [
                'id'    => (int) $parent->ID,
                'title' => get_the_title($parent),
                'url'   => get_permalink($parent),
            ] : null,
            'integrated_components' => $components,
            'related_programs'      => flacso_theme_get_related_programs($offer_id),
        ],
    ];
}

function flacso_theme_get_seminar_context(int $post_id): array {
    return flacso_theme_is_seminar($post_id) ? flacso_theme_get_offer_context($post_id) : [];
}

function flacso_theme_get_seminars_catalog(array $args = []): array {
    $status = !empty($args['post_status']) ? $args['post_status'] : 'publish';
    $posts = get_posts([
        'post_type'      => 'oferta-academica',
        'post_status'    => $status,
        'posts_per_page' => -1,
        'tax_query'      => [[
            'taxonomy' => 'tipo-oferta-academica',
            'field'    => 'slug',
            'terms'    => ['seminario'],
        ]],
    ]);
    if (post_type_exists('seminario')) {
        $posts = array_merge($posts, get_posts([
            'post_type'      => 'seminario',
            'post_status'    => $status,
            'posts_per_page' => -1,
        ]));
    }

    $catalog = $seen = [];
    foreach ($posts as $post) {
        $id = (int) $post->ID;
        if ($id <= 0 || isset($seen[$id]) || !flacso_theme_is_seminar($id)) {
            continue;
        }
        $seen[$id] = true;
        $context = flacso_theme_get_offer_context($id);
        if (!empty($context)) {
            $catalog[] = $context;
        }
    }
    usort($catalog, static function (array $a, array $b): int {
        $date_a = (string) ($a['instance']['fecha_inicio'] ?? '');
        $date_b = (string) ($b['instance']['fecha_inicio'] ?? '');
        if ($date_a === $date_b) {
            return strcasecmp((string) $a['title'], (string) $b['title']);
        }
        if ($date_a === '') {
            return 1;
        }
        if ($date_b === '') {
            return -1;
        }
        return strcmp($date_a, $date_b);
    });
    return $catalog;
}

function flacso_theme_get_program_seminars_catalog(int $program_id): array {
    $catalog = [];
    $seen = [];
    foreach (flacso_theme_get_offer_relations($program_id, 'integra') as $relation) {
        $id = absint($relation['oferta_destino'] ?? 0);
        $parent = flacso_theme_get_integrated_parent($id);
        if ($parent) {
            $id = (int) $parent->ID;
        }
        if ($id <= 0 || isset($seen[$id])) {
            continue;
        }
        $seen[$id] = true;
        $context = flacso_theme_get_seminar_context($id);
        if (!empty($context)) {
            $catalog[] = $context;
        }
    }
    return $catalog;
}

function flacso_theme_format_date_es($date, bool $include_year = true): string {
    $timestamp = is_numeric($date) ? (int) $date : strtotime((string) $date);
    if (!$timestamp) {
        return (string) $date;
    }
    $format = $include_year ? 'l j \\d\\e F \\d\\e Y' : 'l j \\d\\e F';
    return wp_date($format, $timestamp, wp_timezone());
}

function flacso_theme_format_amount($amount): string {
    $amount = (float) $amount;
    return number_format($amount, $amount === floor($amount) ? 0 : 2, ',', '.');
}

function flacso_theme_render_teacher_card($teacher): void {
    if (!$teacher || empty($teacher->ID)) {
        return;
    }
    $id = (int) $teacher->ID;
    $name = get_the_title($teacher);
    ?>
    <article class="seminario-teacher">
        <div class="seminario-teacher-avatar">
            <?php echo get_the_post_thumbnail($id, 'medium', ['alt' => $name, 'loading' => 'lazy']); ?>
        </div>
        <div>
            <h3 class="seminario-teacher-name"><?php echo esc_html($name); ?></h3>
            <?php $cv = get_post_meta($id, 'cv', true); ?>
            <?php if ($cv) : ?><div class="seminario-teacher-bio"><?php echo wp_kses_post($cv); ?></div><?php endif; ?>
        </div>
    </article>
    <?php
}
