<?php
/**
 * Test Suite de Contrato: Capa de Compatibilidad y View Model del Dominio Académico (kadence-child-flacso).
 *
 * Evalúa los dos estados del sistema:
 * - Estado A: Base Legacy (CPT seminario / cohorte, metadatos directos).
 * - Estado B: Base Migrada (CPT oferta-academica, CPT instancia-oferta, FLACSO_Relacion_Oferta_Academica).
 */

declare(strict_types=1);

// Mocking WordPress Environment if not already loaded
if (!defined('ABSPATH')) {
    define('ABSPATH', __DIR__ . '/../');
}

// Global store for mocks
$GLOBALS['mock_posts'] = [];
$GLOBALS['mock_post_meta'] = [];
$GLOBALS['mock_post_terms'] = [];
$GLOBALS['mock_options'] = [];

if (!function_exists('wp_timezone')) {
    function wp_timezone(): DateTimeZone {
        return new DateTimeZone('America/Montevideo');
    }
}
if (!function_exists('current_time')) {
    function current_time(string $type): string {
        return date('Y-m-d');
    }
}
if (!function_exists('wp_date')) {
    function wp_date(string $format, int $timestamp = null, DateTimeZone $timezone = null): string {
        $ts = $timestamp ?? time();
        return date($format, $ts);
    }
}
if (!function_exists('get_post')) {
    function get_post($post = null) {
        $id = is_object($post) ? (int) $post->ID : (int) $post;
        return $GLOBALS['mock_posts'][$id] ?? null;
    }
}
if (!function_exists('get_post_type')) {
    function get_post_type($post = null): string {
        $p = get_post($post);
        return $p ? (string) $p->post_type : '';
    }
}
if (!function_exists('post_type_exists')) {
    function post_type_exists(string $pt): bool {
        return true;
    }
}
if (!function_exists('get_the_title')) {
    function get_the_title($post = 0): string {
        $p = get_post($post);
        return $p ? (string) $p->post_title : '';
    }
}
if (!function_exists('get_post_field')) {
    function get_post_field(string $field, $post): string {
        $p = get_post($post);
        return $p ? (string) ($p->$field ?? '') : '';
    }
}
if (!function_exists('get_permalink')) {
    function get_permalink($post = 0): string {
        $p = get_post($post);
        if (!$p) return '';
        $prefix = $p->post_type === 'seminario' || ($p->tipo_oferta ?? '') === 'seminario'
            ? 'https://flacso.edu.uy/formacion/seminarios/'
            : 'https://flacso.edu.uy/formacion/programas/';
        return $prefix . ($p->post_name ?? 'item-' . $p->ID) . '/';
    }
}
if (!function_exists('get_the_excerpt')) {
    function get_the_excerpt($post = null): string {
        $p = get_post($post);
        return $p ? (string) ($p->post_excerpt ?? '') : '';
    }
}
if (!function_exists('get_post_thumbnail_id')) {
    function get_post_thumbnail_id($post = null): int {
        $p = get_post($post);
        return $p ? (int) ($p->thumbnail_id ?? 0) : 0;
    }
}
if (!function_exists('get_the_post_thumbnail_url')) {
    function get_the_post_thumbnail_url($post = null, $size = 'full'): string {
        $p = get_post($post);
        return $p && !empty($p->thumbnail_url) ? (string) $p->thumbnail_url : '';
    }
}
if (!function_exists('get_post_meta')) {
    function get_post_meta(int $post_id, string $key = '', bool $single = false) {
        if ($key === '') {
            return $GLOBALS['mock_post_meta'][$post_id] ?? [];
        }
        $val = $GLOBALS['mock_post_meta'][$post_id][$key] ?? null;
        if ($single) {
            return $val !== null ? $val : '';
        }
        return $val !== null ? [$val] : [];
    }
}
if (!function_exists('metadata_exists')) {
    function metadata_exists(string $type, int $id, string $key): bool {
        return isset($GLOBALS['mock_post_meta'][$id][$key]);
    }
}
if (!function_exists('wp_get_object_terms')) {
    function wp_get_object_terms($post_ids, $taxonomies, $args = []) {
        $id = is_array($post_ids) ? reset($post_ids) : (int) $post_ids;
        return $GLOBALS['mock_post_terms'][$id] ?? [];
    }
}
if (!function_exists('get_posts')) {
    function get_posts(array $args = []): array {
        $results = [];
        $pts = (array) ($args['post_type'] ?? []);
        $meta_key = $args['meta_key'] ?? '';
        $meta_val = $args['meta_value'] ?? null;
        $post__in = $args['post__in'] ?? null;

        foreach ($GLOBALS['mock_posts'] as $p) {
            if (!empty($pts) && !in_array($p->post_type, $pts, true)) {
                continue;
            }
            if ($post__in !== null && !in_array((int) $p->ID, array_map('intval', $post__in), true)) {
                continue;
            }
            if ($meta_key !== '') {
                $val = $GLOBALS['mock_post_meta'][$p->ID][$meta_key] ?? null;
                if ($meta_val !== null && (string) $val !== (string) $meta_val) {
                    continue;
                }
            }
            if (!empty($args['fields']) && $args['fields'] === 'ids') {
                $results[] = (int) $p->ID;
            } else {
                $results[] = $p;
            }
        }
        return $results;
    }
}
if (!function_exists('current_user_can')) {
    function current_user_can(string $cap): bool {
        return false;
    }
}
if (!function_exists('sanitize_key')) {
    function sanitize_key(string $key): string {
        return strtolower(preg_replace('/[^a-z0-9_-]/i', '', $key));
    }
}
if (!function_exists('wp_strip_all_tags')) {
    function wp_strip_all_tags(string $str): string {
        return strip_tags($str);
    }
}
if (!function_exists('wp_trim_words')) {
    function wp_trim_words(string $text, int $num_words = 55, string $more = null): string {
        $words = preg_split('/\s+/', trim($text));
        if (count($words) <= $num_words) {
            return $text;
        }
        return implode(' ', array_slice($words, 0, $num_words)) . ($more ?? '...');
    }
}
if (!function_exists('remove_accents')) {
    function remove_accents(string $str): string {
        return iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $str);
    }
}
if (!function_exists('apply_filters')) {
    function apply_filters(string $tag, $value, ...$args) {
        return $value;
    }
}
if (!function_exists('is_wp_error')) {
    function is_wp_error($thing): bool {
        return false;
    }
}
if (!function_exists('absint')) {
    function absint($maybeint): int {
        return abs((int) $maybeint);
    }
}
if (!function_exists('wp_count_posts')) {
    function wp_count_posts(string $type = 'post'): object {
        $count = 0;
        foreach ($GLOBALS['mock_posts'] as $p) {
            if ($p->post_type === $type) $count++;
        }
        return (object) ['publish' => $count];
    }
}
if (!function_exists('__')) {
    function __(string $text, string $domain = 'default'): string {
        return $text;
    }
}
if (!function_exists('_e')) {
    function _e(string $text, string $domain = 'default'): void {
        echo $text;
    }
}
if (!function_exists('esc_html__')) {
    function esc_html__(string $text, string $domain = 'default'): string {
        return $text;
    }
}
if (!function_exists('esc_attr')) {
    function esc_attr(string $text): string {
        return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
    }
}
if (!function_exists('esc_html')) {
    function esc_html(string $text): string {
        return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
    }
}
if (!function_exists('esc_url')) {
    function esc_url(string $url): string {
        return $url;
    }
}
if (!function_exists('get_option')) {
    function get_option(string $option, $default = false) {
        return $GLOBALS['mock_options'][$option] ?? $default;
    }
}

// Load the theme View Model
require_once __DIR__ . '/../inc/academic-public-model.php';

function run_tests(): void {
    echo "========================================================\n";
    echo " TEST SUITE: kadence-child-flacso Academic View Model\n";
    echo "========================================================\n\n";

    $passed = 0;
    $failed = 0;

    $assert = function (bool $condition, string $message) use (&$passed, &$failed) {
        if ($condition) {
            echo " [PASS] $message\n";
            $passed++;
        } else {
            echo " [FAIL] $message\n";
            $failed++;
        }
    };

    // -------------------------------------------------------------
    // ESCENARIO 1: Estado A - Base Legacy (Pre-migración)
    // -------------------------------------------------------------
    echo "--- Escenario 1: Base Legacy (Pre-Migración) ---\n";

    // Setup Mock Legacy
    $GLOBALS['mock_posts'] = [
        101 => (object) [
            'ID'          => 101,
            'post_type'   => 'oferta-academica',
            'post_title'  => 'Maestría en Educación',
            'post_name'   => 'maestria-educacion',
            'post_status' => 'publish',
        ],
        201 => (object) [
            'ID'          => 201,
            'post_type'   => 'seminario',
            'post_title'  => 'Seminario de Políticas Públicas',
            'post_name'   => 'seminario-politicas-publicas',
            'post_status' => 'publish',
        ],
        301 => (object) [
            'ID'          => 301,
            'post_type'   => 'seminario',
            'post_title'  => 'Seminario Integrado de Género y Sociedad',
            'post_name'   => 'seminario-integrado-genero',
            'post_status' => 'publish',
        ],
        302 => (object) [
            'ID'          => 302,
            'post_type'   => 'seminario',
            'post_title'  => 'Módulo I: Introducción al Género',
            'post_name'   => 'modulo-1-genero',
            'post_status' => 'publish',
        ],
        303 => (object) [
            'ID'          => 303,
            'post_type'   => 'seminario',
            'post_title'  => 'Módulo II: Políticas de Igualdad',
            'post_name'   => 'modulo-2-igualdad',
            'post_status' => 'publish',
        ],
        501 => (object) [
            'ID'          => 501,
            'post_type'   => 'docente',
            'post_title'  => 'Dra. Ana López',
            'post_status' => 'publish',
        ],
    ];

    $GLOBALS['mock_post_meta'] = [
        101 => [
            'proximo_inicio'           => '2026-04-15',
            'proximo_inicio_precision' => 'dia',
            'cohorte'                  => 'Cohorte 2026',
            'inscripciones_abiertas'   => '1',
            '_oferta_seminarios_ids'   => [201, 301],
        ],
        201 => [
            '_seminario_periodo_inicio'        => '2026-05-10',
            '_seminario_periodo_fin'           => '2026-06-20',
            '_seminario_modalidad'             => 'Virtual',
            '_seminario_carga_horaria'         => 30,
            '_seminario_creditos'              => 3,
            '_seminario_docentes'              => [501],
            '_seminario_abierto_publico'       => '1',
            '_seminario_valor_uyu'             => 12000,
            '_seminario_valor_usd'             => 300,
            '_seminario_presentacion_seminario'=> 'Presentación legacy de políticas públicas.',
        ],
        301 => [
            '_seminario_es_integrado'              => '1',
            '_seminario_seminarios_componentes'    => [302, 303],
            '_seminario_abierto_publico'           => '1',
            '_seminario_valor_uyu'                 => 20000,
            '_seminario_valor_usd'                 => 500,
        ],
        302 => [
            '_seminario_periodo_inicio' => '2026-06-01',
            '_seminario_periodo_fin'    => '2026-06-15',
            '_seminario_carga_horaria'  => 20,
            '_seminario_creditos'       => 2,
            '_seminario_docentes'       => [501],
        ],
        303 => [
            '_seminario_periodo_inicio' => '2026-06-16',
            '_seminario_periodo_fin'    => '2026-06-30',
            '_seminario_carga_horaria'  => 25,
            '_seminario_creditos'       => 2.5,
        ],
    ];

    $GLOBALS['mock_post_terms'] = [
        101 => [(object) ['slug' => 'maestria', 'name' => 'Maestrías']],
    ];

    // Test tipo de oferta
    $assert(flacso_theme_get_offer_type(101) === 'maestria', 'Oferta 101 se reconoce como maestría');
    $assert(flacso_theme_get_offer_type(201) === 'seminario', 'Seminario legacy 201 se reconoce como seminario');

    // Test contexto oferta 101
    $ctx_101 = flacso_theme_get_offer_context(101);
    $assert($ctx_101['type'] === 'maestria', 'Contexto 101 tipo correcto');
    $assert($ctx_101['instance']['fecha_inicio'] === '2026-04-15', 'Contexto 101 fecha inicio resuelta desde legacy');
    $assert($ctx_101['registration']['is_open'] === true, 'Contexto 101 inscripciones abiertas');

    // Test contexto seminario simple 201
    $ctx_201 = flacso_theme_get_offer_context(201);
    $assert($ctx_201['is_seminar'] === true, 'Contexto 201 marcado como seminario');
    $assert($ctx_201['instance']['fecha_inicio'] === '2026-05-10', 'Contexto 201 fecha inicio resuelta');
    $assert($ctx_201['academic']['carga_horaria'] == 30, 'Contexto 201 carga horaria correcta');
    $assert($ctx_201['instance']['valor_uyu'] == 12000, 'Contexto 201 valor UYU correcto');

    // Test seminario integrado 301 con agregación
    $ctx_301 = flacso_theme_get_offer_context(301);
    $assert($ctx_301['is_integrated'] === true, 'Contexto 301 marcado como integrado');
    $assert($ctx_301['instance']['fecha_inicio'] === '2026-06-01', 'Contexto 301 fecha inicio agregada mínima (2026-06-01)');
    $assert($ctx_301['instance']['fecha_fin'] === '2026-06-30', 'Contexto 301 fecha fin agregada máxima (2026-06-30)');
    $assert($ctx_301['academic']['carga_horaria'] == 45, 'Contexto 301 carga horaria agregada (20 + 25 = 45)');
    $assert($ctx_301['academic']['creditos'] == 4.5, 'Contexto 301 créditos agregados (2 + 2.5 = 4.5)');

    // Test componente detecta a su padre
    $parent = flacso_theme_get_integrated_parent(302);
    $assert($parent instanceof stdClass && (int) $parent->ID === 301, 'Componente 302 reconoce a su padre integrado 301');

    // Test catálogo de seminarios
    $catalog = flacso_theme_get_seminars_catalog();
    $catalog_ids = array_map(fn($c) => (int) $c['offer_id'], $catalog);
    $assert(in_array(201, $catalog_ids, true), 'Catálogo incluye seminario 201');
    $assert(in_array(301, $catalog_ids, true), 'Catálogo incluye integrado 301');

    // -------------------------------------------------------------
    // ESCENARIO 2: Estado B - Base Migrada (Modelo Unificado)
    // -------------------------------------------------------------
    echo "\n--- Escenario 2: Base Migrada (Modelo Unificado) ---\n";

    // Mock FLACSO_Relacion_Oferta_Academica class
    if (!class_exists('FLACSO_Relacion_Oferta_Academica')) {
        class FLACSO_Relacion_Oferta_Academica {
            public const INTEGRA = 'integra';
            public const COMPUESTO_POR = 'compuesto_por';
            public const PRECEDE = 'precede';

            public static function get(int $origin_id, string $type = ''): array {
                $rels = $GLOBALS['mock_canonical_relations'][$origin_id] ?? [];
                if ($type === '') {
                    return $rels;
                }
                return array_values(array_filter($rels, fn($r) => $r['tipo_relacion'] === $type));
            }
        }
    }

    // Mock FLACSO_Instancia_Oferta class
    if (!class_exists('FLACSO_Instancia_Oferta')) {
        class FLACSO_Instancia_Oferta {
            public static function get_state(int $instance_id): string {
                return (string) get_post_meta($instance_id, '_flacso_estado_academico', true);
            }
            public static function get_nombre_visible(int $instance_id): string {
                return (string) get_the_title($instance_id);
            }
            public static function acepta_preinscripciones(int $instance_id): bool {
                return (bool) get_post_meta($instance_id, '_flacso_acepta_preinscripciones', true);
            }
        }
    }

    // Mock FLACSO_Oferta_Academica class
    if (!class_exists('FLACSO_Oferta_Academica')) {
        class FLACSO_Oferta_Academica {
            public const TIPO_SEMINARIO = 'seminario';
            public static function get_tipo(int $offer_id): string {
                $p = get_post($offer_id);
                return $p ? (string) ($p->tipo_oferta ?? '') : '';
            }
            public static function tipos(): array {
                return [
                    'doctorado'       => 'Doctorados',
                    'maestria'        => 'Maestrías',
                    'especializacion' => 'Especializaciones',
                    'diplomado'       => 'Diplomados',
                    'diploma'         => 'Diplomas',
                    'seminario'       => 'Seminarios',
                ];
            }
        }
    }

    // Reconfigurar Mock DB para Estado Migrado
    $GLOBALS['mock_posts'] = [
        1001 => (object) [
            'ID'          => 1001,
            'post_type'   => 'oferta-academica',
            'post_title'  => 'Maestría en Políticas Públicas',
            'post_name'   => 'maestria-politicas-publicas',
            'tipo_oferta' => 'maestria',
            'post_status' => 'publish',
        ],
        2001 => (object) [
            'ID'          => 2001,
            'post_type'   => 'oferta-academica',
            'post_title'  => 'Seminario de Inteligencia Artificial y Educación',
            'post_name'   => 'seminario-ia-educacion',
            'tipo_oferta' => 'seminario',
            'post_status' => 'publish',
        ],
        2002 => (object) [
            'ID'          => 2002,
            'post_type'   => 'oferta-academica',
            'post_title'  => 'Seminario Integrado en Gobernanza Digital',
            'post_name'   => 'seminario-gobernanza-digital',
            'tipo_oferta' => 'seminario',
            'post_status' => 'publish',
        ],
        2003 => (object) [
            'ID'          => 2003,
            'post_type'   => 'oferta-academica',
            'post_title'  => 'Módulo A: Datos Abiertos',
            'post_name'   => 'modulo-a-datos-abiertos',
            'tipo_oferta' => 'seminario',
            'post_status' => 'publish',
        ],
        2004 => (object) [
            'ID'          => 2004,
            'post_type'   => 'oferta-academica',
            'post_title'  => 'Módulo B: Ciberseguridad',
            'post_name'   => 'modulo-b-ciberseguridad',
            'tipo_oferta' => 'seminario',
            'post_status' => 'publish',
        ],
        // Instancias
        7001 => (object) [
            'ID'          => 7001,
            'post_type'   => 'instancia-oferta',
            'post_title'  => 'Edición 2026',
            'post_status' => 'publish',
        ],
        7002 => (object) [
            'ID'          => 7002,
            'post_type'   => 'instancia-oferta',
            'post_title'  => 'Edición 2026 - Módulo A',
            'post_status' => 'publish',
        ],
        7003 => (object) [
            'ID'          => 7003,
            'post_type'   => 'instancia-oferta',
            'post_title'  => 'Edición 2026 - Módulo B',
            'post_status' => 'publish',
        ],
    ];

    $GLOBALS['mock_post_meta'] = [
        // Ofertas canónicas
        2001 => [
            '_seminario_nombre'                => 'Seminario de Inteligencia Artificial y Educación',
            '_seminario_presentacion_seminario'=> 'Contenido canónico de IA en Educación.',
            '_seminario_carga_horaria'         => 36,
            '_seminario_creditos'              => 4,
        ],
        2002 => [
            '_seminario_nombre'                => 'Seminario Integrado en Gobernanza Digital',
            '_seminario_presentacion_seminario'=> 'Propuesta compuesta de gobernanza digital.',
        ],
        2003 => [
            '_seminario_nombre'                => 'Módulo A: Datos Abiertos',
            '_seminario_carga_horaria'         => 18,
            '_seminario_creditos'              => 2,
        ],
        2004 => [
            '_seminario_nombre'                => 'Módulo B: Ciberseguridad',
            '_seminario_carga_horaria'         => 18,
            '_seminario_creditos'              => 2,
        ],
        // Instancias
        7001 => [
            '_flacso_oferta_academica_id'      => 2001,
            '_flacso_fecha_inicio'             => '2026-07-01',
            '_flacso_fecha_fin'                => '2026-07-31',
            '_flacso_modalidad'                => 'Virtual Asincrónica',
            '_flacso_es_asincronico'           => 1,
            '_flacso_valor_uyu'                => 15000,
            '_flacso_valor_usd'                => 380,
            '_flacso_estado_academico'         => 'planificada',
            '_flacso_acepta_preinscripciones'  => 1,
        ],
        7002 => [
            '_flacso_oferta_academica_id'      => 2003,
            '_flacso_fecha_inicio'             => '2026-08-01',
            '_flacso_fecha_fin'                => '2026-08-15',
            '_flacso_estado_academico'         => 'planificada',
        ],
        7003 => [
            '_flacso_oferta_academica_id'      => 2004,
            '_flacso_fecha_inicio'             => '2026-08-16',
            '_flacso_fecha_fin'                => '2026-08-31',
            '_flacso_estado_academico'         => 'planificada',
        ],
    ];

    $GLOBALS['mock_canonical_relations'] = [
        1001 => [
            ['oferta_destino' => 2001, 'tipo_relacion' => 'integra', 'orden' => 0],
            ['oferta_destino' => 2002, 'tipo_relacion' => 'integra', 'orden' => 1],
        ],
        2002 => [
            ['oferta_destino' => 2003, 'tipo_relacion' => 'compuesto_por', 'orden' => 0],
            ['oferta_destino' => 2004, 'tipo_relacion' => 'compuesto_por', 'orden' => 1],
        ],
    ];

    // Mock function flacso_get_preinscription_cta
    if (!function_exists('flacso_get_preinscription_cta')) {
        function flacso_get_preinscription_cta(int $offer_id): array {
            if ($offer_id === 2001) {
                return [
                    'instance_id' => 7001,
                    'is_open'     => true,
                    'flow'        => 'gestor_preinscripciones',
                    'url'         => 'https://preinscripciones.flacso.edu.uy/oferta/2001/instancia/7001/?utm_source=web',
                    'open_message'=> 'Inscripciones abiertas para la Edición 2026.',
                    'closed_message' => '',
                ];
            }
            return [
                'instance_id' => 0,
                'is_open'     => false,
                'flow'        => 'legacy_editor',
                'url'         => '',
                'open_message'=> '',
                'closed_message' => 'Próximamente.',
            ];
        }
    }

    // Tests en Estado Migrado
    $assert(flacso_theme_academic_model_is_migrated() === true, 'Detección de modelo migrado activa');
    $assert(flacso_theme_get_offer_type(2001) === 'seminario', 'OfertaAcademica 2001 identificada como seminario');
    $assert(flacso_theme_get_offer_type(1001) === 'maestria', 'OfertaAcademica 1001 identificada como maestría');

    // Contexto Seminario 2001
    $ctx_2001 = flacso_theme_get_offer_context(2001);
    $assert($ctx_2001['instance_id'] === 7001, 'Instancia 7001 resuelta como instancia relevante');
    $assert($ctx_2001['instance']['fecha_inicio'] === '2026-07-01', 'Fecha inicio leída desde InstanciaOferta 7001');
    $assert($ctx_2001['instance']['valor_uyu'] == 15000, 'Arancel UYU leído desde InstanciaOferta 7001');
    $assert($ctx_2001['academic']['presentacion'] === 'Contenido canónico de IA en Educación.', 'Presentación leída desde OfertaAcademica');
    $assert($ctx_2001['registration']['is_open'] === true, 'Preinscripción abierta detectada via resolver');
    $assert($ctx_2001['registration']['flow'] === 'gestor_preinscripciones', 'Flujo de preinscripción gestor_preinscripciones');
    $assert(strpos($ctx_2001['registration']['url'], 'https://preinscripciones.flacso.edu.uy/') === 0, 'URL de preinscripción apunta al gestor');

    // Relaciones canónicas
    $related_2001 = flacso_theme_get_related_programs(2001);
    $assert(count($related_2001) === 1 && $related_2001[0]['id'] === 1001, 'Seminario 2001 encuentra su programa vinculado (1001) via relación integra');

    // Seminario integrado 2002 en modelo canónico compuesto_por
    $ctx_2002 = flacso_theme_get_offer_context(2002);
    $assert($ctx_2002['is_integrated'] === true, 'Oferta 2002 identificada como seminario integrado');
    $assert($ctx_2002['instance']['fecha_inicio'] === '2026-08-01', 'Inicio agregado de componentes (2026-08-01)');
    $assert($ctx_2002['instance']['fecha_fin'] === '2026-08-31', 'Fin agregado de componentes (2026-08-31)');
    $assert($ctx_2002['academic']['carga_horaria'] == 36, 'Horas agregadas de componentes (18 + 18 = 36)');
    $assert($ctx_2002['academic']['creditos'] == 4, 'Créditos agregados de componentes (2 + 2 = 4)');

    // Componente 2003 encuentra a su padre 2002
    $parent_2003 = flacso_theme_get_integrated_parent(2003);
    $assert($parent_2003 instanceof stdClass && (int) $parent_2003->ID === 2002, 'Componente 2003 encuentra a su padre 2002 via relación inversa compuesto_por');

    echo "\n========================================================\n";
    echo " RESULTADOS: $passed PASSED, $failed FAILED\n";
    echo "========================================================\n";

    if ($failed > 0) {
        exit(1);
    }
}

run_tests();
