<?php
/** Standalone contract tests for inc/academic-public-model.php. */

define('ABSPATH', __DIR__ . '/');

$GLOBALS['posts'] = [
    10 => (object) ['ID' => 10, 'post_type' => 'oferta-academica', 'post_status' => 'publish', 'post_title' => 'Maestría', 'post_name' => 'maestria'],
    20 => (object) ['ID' => 20, 'post_type' => 'oferta-academica', 'post_status' => 'publish', 'post_title' => 'Seminario unificado', 'post_name' => 'seminario-unificado'],
    21 => (object) ['ID' => 21, 'post_type' => 'seminario', 'post_status' => 'publish', 'post_title' => 'Seminario legacy', 'post_name' => 'seminario-legacy'],
    30 => (object) ['ID' => 30, 'post_type' => 'instancia-oferta', 'post_status' => 'publish', 'post_title' => 'Edición agosto 2026', 'post_name' => 'edicion-agosto'],
    40 => (object) ['ID' => 40, 'post_type' => 'oferta-academica', 'post_status' => 'publish', 'post_title' => 'Doctorado', 'post_name' => 'doctorado'],
];

$GLOBALS['types'] = [10 => 'maestria', 20 => 'seminario', 21 => 'seminario', 40 => 'doctorado'];
$GLOBALS['meta'] = [
    10 => ['_flacso_relaciones_oferta_academica' => [
        ['oferta_destino' => 20, 'tipo_relacion' => 'integra', 'orden' => 0],
    ]],
    20 => [
        '_seminario_presentacion_seminario' => 'Presentación académica',
        '_seminario_carga_horaria' => 24,
        '_seminario_creditos' => 3,
    ],
    21 => [
        '_seminario_periodo_inicio' => '2026-09-10',
        '_seminario_modalidad' => 'Presencial',
        '_seminario_abierto_publico' => '1',
    ],
    30 => [
        'oferta_academica_id' => 20,
        'fecha_inicio' => '2026-08-10',
        'fecha_fin' => '2026-08-31',
        'estado' => 'planificada',
        'modalidad' => 'Virtual',
        'docentes' => [91, 92],
        'valor_uyu' => 10000,
    ],
];

function get_post($id) { return $GLOBALS['posts'][(int) $id] ?? null; }
function get_post_type($id) { $post = get_post($id); return $post ? $post->post_type : ''; }
function get_post_status($id) { $post = get_post($id); return $post ? $post->post_status : ''; }
function get_post_meta($id, $key, $single = false) { return $GLOBALS['meta'][(int) $id][$key] ?? ''; }
function metadata_exists($type, $id, $key) { return array_key_exists($key, $GLOBALS['meta'][(int) $id] ?? []); }
function get_the_title($post) { $post = is_object($post) ? $post : get_post($post); return $post ? $post->post_title : ''; }
function get_permalink($post) { $id = is_object($post) ? $post->ID : $post; return 'https://example.test/oferta/' . $id . '/'; }
function get_post_thumbnail_id($id) { return 0; }
function get_the_post_thumbnail_url($id, $size = 'post-thumbnail') { return ''; }
function get_the_post_thumbnail($id, $size = 'post-thumbnail', $attr = []) { return ''; }
function post_type_exists($type) { return in_array($type, ['oferta-academica', 'seminario', 'instancia-oferta'], true); }
function wp_get_object_terms($id, $taxonomy) { $type = $GLOBALS['types'][(int) $id] ?? ''; return $type ? [(object) ['slug' => $type]] : []; }
function is_wp_error($value) { return false; }
function sanitize_key($value) { return strtolower(preg_replace('/[^a-z0-9_-]/', '', (string) $value)); }
function absint($value) { return abs((int) $value); }
function __($text, $domain = 'default') { return $text; }
function esc_html($text) { return htmlspecialchars((string) $text, ENT_QUOTES, 'UTF-8'); }
function wp_kses_post($text) { return (string) $text; }
function wp_timezone() { return new DateTimeZone('UTC'); }
function wp_date($format, $timestamp, $timezone = null) { return date($format, $timestamp); }

function get_posts($args = []) {
    $posts = array_values($GLOBALS['posts']);
    $types = (array) ($args['post_type'] ?? 'post');
    $statuses = (array) ($args['post_status'] ?? 'publish');
    $posts = array_values(array_filter($posts, static function ($post) use ($types, $statuses) {
        return in_array($post->post_type, $types, true) && in_array($post->post_status, $statuses, true);
    }));

    if (!empty($args['post__in'])) {
        $wanted = array_map('intval', $args['post__in']);
        $posts = array_values(array_filter($posts, static function ($post) use ($wanted) {
            return in_array((int) $post->ID, $wanted, true);
        }));
        usort($posts, static function ($a, $b) use ($wanted) {
            return array_search($a->ID, $wanted, true) <=> array_search($b->ID, $wanted, true);
        });
    }

    if (!empty($args['tax_query'])) {
        $posts = array_values(array_filter($posts, static function ($post) {
            return ($GLOBALS['types'][(int) $post->ID] ?? '') === 'seminario';
        }));
    }

    if (!empty($args['meta_query'])) {
        $query = $args['meta_query'][0];
        $posts = array_values(array_filter($posts, static function ($post) use ($query) {
            return (string) get_post_meta($post->ID, $query['key'], true) === (string) $query['value'];
        }));
    }

    if (($args['fields'] ?? '') === 'ids') {
        return array_map(static function ($post) { return (int) $post->ID; }, $posts);
    }
    if (($args['posts_per_page'] ?? -1) > -1) {
        $posts = array_slice($posts, 0, (int) $args['posts_per_page']);
    }
    return $posts;
}

final class FLACSO_Oferta_Academica {
    public static function get_tipo(int $id): string { return $GLOBALS['types'][$id] ?? ''; }
}

final class FLACSO_Instancia_Oferta {
    public const META_OFERTA_ID = 'oferta_academica_id';
    public const META_FECHA_INICIO = 'fecha_inicio';
    public const META_FECHA_FIN = 'fecha_fin';
    public const META_PRECISION_FECHA_INICIO = 'precision_fecha_inicio';
    public const META_ESTADO = 'estado';
    public const META_MODALIDAD = 'modalidad';
    public const META_ES_ASINCRONICO = 'es_asincronico';
    public const META_DOCENTES = 'docentes';
    public const META_ENCUENTROS = 'encuentros_sincronicos';
    public const META_VALOR_UYU = 'valor_uyu';
    public const META_VALOR_USD = 'valor_usd';
    public const META_VALOR_UYU_DESCUENTO = 'valor_uyu_15_descuento';
    public const META_VALOR_USD_DESCUENTO = 'valor_usd_15_descuento';
    public static function find_open_for_offer(int $id): int { return $id === 20 ? 30 : 0; }
    public static function find_latest_for_offer(int $id): int { return $id === 20 ? 30 : 0; }
    public static function get_nombre_visible(int $id): string { return get_the_title($id); }
    public static function get_state(int $id): string { return (string) get_post_meta($id, self::META_ESTADO, true); }
}

final class FLACSO_Relacion_Oferta_Academica {
    public const META_KEY = '_flacso_relaciones_oferta_academica';
    public static function get(int $origin, string $type = ''): array {
        $relations = get_post_meta($origin, self::META_KEY, true);
        $relations = is_array($relations) ? $relations : [];
        return $type === '' ? $relations : array_values(array_filter($relations, static function ($relation) use ($type) {
            return $relation['tipo_relacion'] === $type;
        }));
    }
}

function flacso_get_preinscription_cta(int $offer_id): array {
    if ($offer_id === 20) {
        return [
            'instance_id' => 30,
            'is_open' => true,
            'flow' => 'gestor_preinscripciones',
            'url' => 'https://preinscripciones.flacso.edu.uy/ofertas/20/instancias/30/',
            'open_message' => 'Abierta',
            'closed_message' => '',
        ];
    }
    return ['instance_id' => 0, 'is_open' => $offer_id === 21, 'flow' => 'legacy_editor', 'url' => '', 'open_message' => '', 'closed_message' => ''];
}

require_once __DIR__ . '/../inc/academic-public-model.php';

$failures = 0;
function assert_same($expected, $actual, string $message): void {
    global $failures;
    if ($expected !== $actual) {
        $failures++;
        fwrite(STDERR, "FAIL: {$message}\nExpected: " . var_export($expected, true) . "\nActual: " . var_export($actual, true) . "\n");
    } else {
        echo "PASS: {$message}\n";
    }
}

assert_same('seminario', flacso_theme_get_offer_type(20), 'OfertaAcademica seminario usa el tipo canónico');
assert_same('seminario', flacso_theme_get_offer_type(21), 'CPT seminario sigue disponible durante Release A');
assert_same('Doctorado', flacso_theme_get_offer_type_label(40), 'Doctorado tiene etiqueta pública');

$unified = flacso_theme_get_offer_context(20);
assert_same(30, $unified['instance_id'], 'resuelve InstanciaOferta relevante');
assert_same('2026-08-10', $unified['instance']['fecha_inicio'], 'lee fecha desde la clave real de InstanciaOferta');
assert_same('Virtual', $unified['instance']['modalidad'], 'lee modalidad desde InstanciaOferta');
assert_same(10000.0, $unified['instance']['valor_uyu'], 'lee precio desde InstanciaOferta');
assert_same('Presentación académica', $unified['academic']['presentacion'], 'mantiene identidad académica en OfertaAcademica');
assert_same(true, $unified['registration']['is_open'], 'delega apertura al resolver del plugin');
assert_same('gestor_preinscripciones', $unified['registration']['flow'], 'conserva el flujo transitorio');

$legacy = flacso_theme_get_offer_context(21);
assert_same('2026-09-10', $legacy['instance']['fecha_inicio'], 'legacy conserva fallback de temporalidad');
assert_same('Presencial', $legacy['instance']['modalidad'], 'legacy conserva fallback de modalidad');

$catalog_ids = array_map(static function ($item) { return $item['offer_id']; }, flacso_theme_get_seminars_catalog());
sort($catalog_ids);
assert_same([20, 21], $catalog_ids, 'catálogo combina OfertaAcademica seminario y CPT legacy sin duplicar');

$program_ids = array_map(static function ($item) { return $item['offer_id']; }, flacso_theme_get_program_seminars_catalog(10));
assert_same([20], $program_ids, 'relaciones de programa devuelven seminarios canónicos');

exit($failures > 0 ? 1 : 0);
