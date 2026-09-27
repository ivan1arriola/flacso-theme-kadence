<?php
/**
 * Test de contrato para inc/academic-public-model.php con el modelo academico final (v7).
 */

define("ABSPATH", __DIR__ . "/");

$GLOBALS["posts"] = [
    10 => (object) ["ID" => 10, "post_type" => "oferta-academica", "post_status" => "publish", "post_title" => "Maestría en Educación", "post_name" => "maestria-educacion"],
    100 => (object) ["ID" => 100, "post_type" => "cohorte", "post_status" => "publish", "post_title" => "Cohorte V", "post_name" => "cohorte-v"],
    20 => (object) ["ID" => 20, "post_type" => "seminario", "post_status" => "publish", "post_title" => "Seminario de Políticas Públicas", "post_name" => "seminario-politicas"],
    200 => (object) ["ID" => 200, "post_type" => "edicion", "post_status" => "publish", "post_title" => "Edición 2026", "post_name" => "edicion-2026"],
    21 => (object) ["ID" => 21, "post_type" => "seminario", "post_status" => "publish", "post_title" => "Seminario de Género y Sociedad", "post_name" => "seminario-genero"],
    210 => (object) ["ID" => 210, "post_type" => "edicion", "post_status" => "publish", "post_title" => "Edición 2026 Género", "post_name" => "edicion-2026-genero"],
    22 => (object) ["ID" => 22, "post_type" => "seminario", "post_status" => "publish", "post_title" => "Seminario Integrado", "post_name" => "seminario-integrado"],
    220 => (object) ["ID" => 220, "post_type" => "edicion", "post_status" => "publish", "post_title" => "Edición 2026 Integrada", "post_name" => "edicion-2026-integrada"],
    40 => (object) ["ID" => 40, "post_type" => "oferta-academica", "post_status" => "publish", "post_title" => "Doctorado en Ciencias Sociales", "post_name" => "doctorado"],
    500 => (object) ["ID" => 500, "post_type" => "tabla-precio", "post_status" => "publish", "post_title" => "Aranceles Seminarios 2026", "post_name" => "tabla-seminarios-2026"],
    501 => (object) ["ID" => 501, "post_type" => "tabla-precio", "post_status" => "publish", "post_title" => "Aranceles Maestría 2026", "post_name" => "tabla-maestria-2026"],
    91 => (object) ["ID" => 91, "post_type" => "docente", "post_status" => "publish", "post_title" => "Dra. Ana Pérez", "post_name" => "ana-perez"],
    92 => (object) ["ID" => 92, "post_type" => "docente", "post_status" => "publish", "post_title" => "Dr. Juan Gómez", "post_name" => "juan-gomez"],
];

$GLOBALS["types"] = [10 => "maestria", 40 => "doctorado"];
$GLOBALS["meta"] = [
    10 => [
        "programa_academico_id" => 0,
        "presentacion" => "Presentación de la maestría",
        "carga_horaria" => 480,
        "creditos" => 60,
        "colores_presentacion" => ["principal" => "#0057a8", "secundarios" => []],
        "seminarios" => [
            ["seminario_id" => 20, "orden" => 1, "caracter" => "obligatorio", "creditos_reconocidos" => 4],
        ],
    ],
    100 => [
        "oferta_academica_id" => 10,
        "numero" => 5,
        "fecha_inicio" => "2026-08-15",
        "fecha_fin" => "2028-07-31",
        "precision_fecha_inicio" => "dia",
        "estado" => "planificada",
        "tabla_precio_id" => 501,
        "link_preinscripcion" => "https://preinscripciones.flacso.edu.uy/ofertas/10/cohortes/100/",
        "preinscripcion_desde" => "2026-03-01",
        "preinscripcion_hasta" => "2026-08-01",
    ],
    20 => [
        "presentacion" => "Presentación del seminario de políticas",
        "carga_horaria" => 30,
        "creditos" => 4,
        "acredita_maestria" => 1,
        "componentes" => [],
    ],
    200 => [
        "seminario_id" => 20,
        "anio" => 2026,
        "fecha_inicio" => "2026-09-01",
        "fecha_fin" => "2026-10-15",
        "estado" => "planificada",
        "modalidad" => "Virtual",
        "docentes" => [91, 92],
        "tabla_precio_id" => 500,
        "link_preinscripcion" => "https://preinscripciones.flacso.edu.uy/seminarios/20/ediciones/200/",
        "preinscripcion_desde" => "2026-06-01",
        "preinscripcion_hasta" => "2026-08-30",
        "es_asincronica" => 0,
        "encuentros_sincronicos" => [
            ["fecha" => "2026-09-05", "hora_inicio" => "18:00", "hora_fin" => "20:00", "plataforma" => "zoom"],
        ],
    ],
    21 => [
        "presentacion" => "Presentación del seminario de género",
        "carga_horaria" => 20,
        "creditos" => 3,
        "componentes" => [],
    ],
    210 => [
        "seminario_id" => 21,
        "anio" => 2026,
        "fecha_inicio" => "2026-10-01",
        "fecha_fin" => "2026-11-15",
        "estado" => "planificada",
        "modalidad" => "Virtual asincrónica",
        "docentes" => [92],
        "tabla_precio_id" => 500,
        "link_preinscripcion" => "https://preinscripciones.flacso.edu.uy/seminarios/21/ediciones/210/",
        "preinscripcion_desde" => "2026-07-01",
        "preinscripcion_hasta" => "2026-09-30",
        "es_asincronica" => 1,
    ],
    22 => [
        "presentacion" => "Presentación integrada",
        "carga_horaria" => 60,
        "creditos" => 7,
        "componentes" => [
            ["seminario_id" => 20, "orden" => 1],
            ["seminario_id" => 21, "orden" => 2],
        ],
    ],
    220 => [
        "seminario_id" => 22,
        "anio" => 2026,
        "fecha_inicio" => "",
        "fecha_fin" => "",
        "estado" => "planificada",
        "modalidad" => "Virtual",
        "docentes" => [],
        "tabla_precio_id" => 500,
        "link_preinscripcion" => "https://preinscripciones.flacso.edu.uy/seminarios/22/ediciones/220/",
        "preinscripcion_desde" => "2026-07-01",
        "preinscripcion_hasta" => "2026-09-30",
        "es_asincronica" => 0,
    ],
    500 => [
        "tabla_precios_tipo" => "seminario",
        "mostrar_precios_dolares" => 1,
        "precios_nota" => "Nota aranceles",
        "precios_filas" => [
            ["concepto" => "Público General", "uyu" => "12.000", "usd" => "300", "destacada" => 1],
            ["concepto" => "Comunidad FLACSO (15% OFF)", "uyu" => "10.200", "usd" => "255", "destacada" => 0],
        ],
    ],
    501 => [
        "tabla_precios_tipo" => "programa",
        "mostrar_precios_dolares" => 1,
        "precios_nota" => "Financiación en cuotas",
        "precios_filas" => [
            ["concepto" => "Matrícula + 24 cuotas", "uyu" => "180.000", "usd" => "4.500", "destacada" => 1],
        ],
    ],
];

function get_post($id) { return $GLOBALS["posts"][(int) $id] ?? null; }
function get_post_type($id) { $post = get_post($id); return $post ? $post->post_type : ""; }
function get_post_status($id) { $post = get_post($id); return $post ? $post->post_status : ""; }
function get_post_meta($id, $key, $single = false) { return $GLOBALS["meta"][(int) $id][$key] ?? ""; }
function get_post_field($field, $post_id) { $post = get_post($post_id); return $post ? ($post->$field ?? "") : ""; }
function metadata_exists($type, $id, $key) { return array_key_exists($key, $GLOBALS["meta"][(int) $id] ?? []); }
function get_the_title($post) { $post = is_object($post) ? $post : get_post($post); return $post ? $post->post_title : ""; }
function get_permalink($post) { $id = is_object($post) ? $post->ID : $post; return "https://flacso.edu.uy/item/" . $id . "/"; }
function get_post_thumbnail_id($id) { return 0; }
function get_the_post_thumbnail_url($id, $size = "post-thumbnail") { return ""; }
function get_the_post_thumbnail($id, $size = "post-thumbnail", $attr = []) { return ""; }
function post_type_exists($type) { return in_array($type, ["oferta-academica", "cohorte", "seminario", "edicion", "tabla-precio", "docente", "programa-academico"], true); }
function wp_get_object_terms($id, $taxonomy) { $type = $GLOBALS["types"][(int) $id] ?? ""; return $type ? [(object) ["slug" => $type]] : []; }
function is_wp_error($value) { return false; }
function sanitize_key($value) { return strtolower(preg_replace("/[^a-z0-9_-]/", "", (string) $value)); }
function sanitize_text_field($value) { return trim(strip_tags((string) $value)); }
function absint($value) { return abs((int) $value); }
function __($text, $domain = "default") { return $text; }
function esc_html($text) { return htmlspecialchars((string) $text, ENT_QUOTES, "UTF-8"); }
function esc_attr($text) { return htmlspecialchars((string) $text, ENT_QUOTES, "UTF-8"); }
function esc_url($url) { return (string) $url; }
function wp_kses_post($text) { return (string) $text; }
function wp_timezone() { return new DateTimeZone("UTC"); }
function wp_date($format, $timestamp, $timezone = null) { return date($format, $timestamp); }

function get_posts($args = []) {
    $posts = array_values($GLOBALS["posts"]);
    $types = (array) ($args["post_type"] ?? "post");
    $statuses = (array) ($args["post_status"] ?? "publish");
    $posts = array_values(array_filter($posts, static function ($post) use ($types, $statuses) {
        return in_array($post->post_type, $types, true) && in_array($post->post_status, $statuses, true);
    }));

    if (!empty($args["post__in"])) {
        $wanted = array_map("intval", $args["post__in"]);
        $posts = array_values(array_filter($posts, static function ($post) use ($wanted) {
            return in_array((int) $post->ID, $wanted, true);
        }));
        usort($posts, static function ($a, $b) use ($wanted) {
            return array_search($a->ID, $wanted, true) <=> array_search($b->ID, $wanted, true);
        });
    }

    if (!empty($args["post__not_in"])) {
        $not_wanted = array_map("intval", $args["post__not_in"]);
        $posts = array_values(array_filter($posts, static function ($post) use ($not_wanted) {
            return !in_array((int) $post->ID, $not_wanted, true);
        }));
    }

    if (($args["fields"] ?? "") === "ids") {
        return array_map(static function ($post) { return (int) $post->ID; }, $posts);
    }
    if (($args["posts_per_page"] ?? -1) > -1) {
        $posts = array_slice($posts, 0, (int) $args["posts_per_page"]);
    }
    return $posts;
}

final class FLACSO_Oferta_Academica {
    public static function get_tipo(int $id): string { return $GLOBALS["types"][$id] ?? ""; }
    public static function tipos(): array {
        return [
            "doctorado"       => "Doctorados",
            "maestria"        => "Maestrías",
            "especializacion" => "Especializaciones",
            "diplomado"       => "Diplomados",
            "diploma"         => "Diplomas",
        ];
    }
}

final class FLACSO_Academic_Catalog {
    public static function get_offer(int $id): array {
        if ($id !== 10) return [];
        return [
            "id" => 10,
            "nombre" => "Maestría en Educación",
            "slug" => "maestria-educacion",
            "tipo" => "maestria",
            "programa_academico_id" => 0,
            "presentacion" => "Presentación de la maestría",
            "carga_horaria" => 480,
            "creditos" => 60,
            "seminarios" => [
                ["seminario_id" => 20, "orden" => 1, "caracter" => "obligatorio", "creditos_reconocidos" => 4],
            ],
            "cohortes" => [
                [
                    "id" => 100,
                    "nombre" => "Cohorte V",
                    "numero" => 5,
                    "numero_romano" => "V",
                    "fecha_inicio" => "2026-08-15",
                    "fecha_fin" => "2028-07-31",
                    "precision_fecha_inicio" => "dia",
                    "estado" => "planificada",
                    "tabla_precio_id" => 501,
                    "link_preinscripcion" => "https://preinscripciones.flacso.edu.uy/ofertas/10/cohortes/100/",
                    "preinscripcion_desde" => "2026-03-01",
                    "preinscripcion_hasta" => "2026-08-01",
                    "preinscripcion" => [
                        "abierta" => true,
                        "url" => "https://preinscripciones.flacso.edu.uy/ofertas/10/cohortes/100/",
                    ],
                ],
            ],
            "cohorte_vigente" => [
                "id" => 100,
                "nombre" => "Cohorte V",
                "numero" => 5,
                "numero_romano" => "V",
                "fecha_inicio" => "2026-08-15",
                "fecha_fin" => "2028-07-31",
                "precision_fecha_inicio" => "dia",
                "estado" => "planificada",
                "tabla_precio_id" => 501,
                "link_preinscripcion" => "https://preinscripciones.flacso.edu.uy/ofertas/10/cohortes/100/",
                "preinscripcion_desde" => "2026-03-01",
                "preinscripcion_hasta" => "2026-08-01",
                "preinscripcion" => [
                    "abierta" => true,
                    "url" => "https://preinscripciones.flacso.edu.uy/ofertas/10/cohortes/100/",
                ],
            ],
        ];
    }

    public static function get_seminar(int $id): array {
        if ($id === 20) {
            return [
                "id" => 20,
                "nombre" => "Seminario de Políticas Públicas",
                "slug" => "seminario-politicas",
                "programa_academico_id" => 0,
                "presentacion" => "Presentación del seminario de políticas",
                "carga_horaria" => 30,
                "creditos" => 4,
                "acredita_maestria" => true,
                "docentes_base" => [91],
                "componentes" => [],
                "ediciones" => [
                    [
                        "id" => 200,
                        "nombre" => "Edición 2026",
                        "anio" => 2026,
                        "fecha_inicio" => "2026-09-01",
                        "fecha_fin" => "2026-10-15",
                        "estado" => "planificada",
                        "modalidad" => "Virtual",
                        "docentes" => [91, 92],
                        "tabla_precio_id" => 500,
                        "link_preinscripcion" => "https://preinscripciones.flacso.edu.uy/seminarios/20/ediciones/200/",
                        "preinscripcion_desde" => "2026-06-01",
                        "preinscripcion_hasta" => "2026-08-30",
                        "es_asincronica" => false,
                        "encuentros_sincronicos" => [
                            ["fecha" => "2026-09-05", "hora_inicio" => "18:00", "hora_fin" => "20:00", "plataforma" => "zoom"],
                        ],
                        "preinscripcion" => [
                            "abierta" => true,
                            "url" => "https://preinscripciones.flacso.edu.uy/seminarios/20/ediciones/200/",
                        ],
                    ],
                ],
                "edicion_vigente" => [
                    "id" => 200,
                    "nombre" => "Edición 2026",
                    "anio" => 2026,
                    "fecha_inicio" => "2026-09-01",
                    "fecha_fin" => "2026-10-15",
                    "estado" => "planificada",
                    "modalidad" => "Virtual",
                    "docentes" => [91, 92],
                    "tabla_precio_id" => 500,
                    "link_preinscripcion" => "https://preinscripciones.flacso.edu.uy/seminarios/20/ediciones/200/",
                    "preinscripcion_desde" => "2026-06-01",
                    "preinscripcion_hasta" => "2026-08-30",
                    "es_asincronica" => false,
                    "encuentros_sincronicos" => [
                        ["fecha" => "2026-09-05", "hora_inicio" => "18:00", "hora_fin" => "20:00", "plataforma" => "zoom"],
                    ],
                    "preinscripcion" => [
                        "abierta" => true,
                        "url" => "https://preinscripciones.flacso.edu.uy/seminarios/20/ediciones/200/",
                    ],
                ],
            ];
        }
        if ($id === 21) {
            return [
                "id" => 21,
                "nombre" => "Seminario de Género y Sociedad",
                "slug" => "seminario-genero",
                "programa_academico_id" => 0,
                "presentacion" => "Presentación del seminario de género",
                "carga_horaria" => 20,
                "creditos" => 3,
                "docentes_base" => [92],
                "componentes" => [],
                "ediciones" => [
                    [
                        "id" => 210,
                        "nombre" => "Edición 2026 Género",
                        "anio" => 2026,
                        "fecha_inicio" => "2026-10-01",
                        "fecha_fin" => "2026-11-15",
                        "estado" => "planificada",
                        "modalidad" => "Virtual asincrónica",
                        "docentes" => [92],
                        "tabla_precio_id" => 500,
                        "link_preinscripcion" => "https://preinscripciones.flacso.edu.uy/seminarios/21/ediciones/210/",
                        "preinscripcion_desde" => "2026-07-01",
                        "preinscripcion_hasta" => "2026-09-30",
                        "es_asincronica" => true,
                        "encuentros_sincronicos" => [],
                        "preinscripcion" => [
                            "abierta" => true,
                            "url" => "https://preinscripciones.flacso.edu.uy/seminarios/21/ediciones/210/",
                        ],
                    ],
                ],
                "edicion_vigente" => [
                    "id" => 210,
                    "nombre" => "Edición 2026 Género",
                    "anio" => 2026,
                    "fecha_inicio" => "2026-10-01",
                    "fecha_fin" => "2026-11-15",
                    "estado" => "planificada",
                    "modalidad" => "Virtual asincrónica",
                    "docentes" => [92],
                    "tabla_precio_id" => 500,
                    "link_preinscripcion" => "https://preinscripciones.flacso.edu.uy/seminarios/21/ediciones/210/",
                    "preinscripcion_desde" => "2026-07-01",
                    "preinscripcion_hasta" => "2026-09-30",
                    "es_asincronica" => true,
                    "encuentros_sincronicos" => [],
                    "preinscripcion" => [
                        "abierta" => true,
                        "url" => "https://preinscripciones.flacso.edu.uy/seminarios/21/ediciones/210/",
                    ],
                ],
            ];
        }
        if ($id === 22) {
            return [
                "id" => 22,
                "nombre" => "Seminario Integrado",
                "slug" => "seminario-integrado",
                "programa_academico_id" => 0,
                "presentacion" => "Presentación integrada",
                "carga_horaria" => 60,
                "creditos" => 7,
                "componentes" => [
                    ["seminario_id" => 20, "orden" => 1],
                    ["seminario_id" => 21, "orden" => 2],
                ],
                "ediciones" => [
                    [
                        "id" => 220,
                        "nombre" => "Edición 2026 Integrada",
                        "anio" => 2026,
                        "fecha_inicio" => "",
                        "fecha_fin" => "",
                        "estado" => "planificada",
                        "modalidad" => "Virtual",
                        "docentes" => [],
                        "tabla_precio_id" => 500,
                        "link_preinscripcion" => "https://preinscripciones.flacso.edu.uy/seminarios/22/ediciones/220/",
                        "preinscripcion_desde" => "2026-07-01",
                        "preinscripcion_hasta" => "2026-09-30",
                        "es_asincronica" => false,
                        "encuentros_sincronicos" => [],
                        "preinscripcion" => [
                            "abierta" => true,
                            "url" => "https://preinscripciones.flacso.edu.uy/seminarios/22/ediciones/220/",
                        ],
                    ],
                ],
                "edicion_vigente" => [
                    "id" => 220,
                    "nombre" => "Edición 2026 Integrada",
                    "anio" => 2026,
                    "fecha_inicio" => "",
                    "fecha_fin" => "",
                    "estado" => "planificada",
                    "modalidad" => "Virtual",
                    "docentes" => [],
                    "tabla_precio_id" => 500,
                    "link_preinscripcion" => "https://preinscripciones.flacso.edu.uy/seminarios/22/ediciones/220/",
                    "preinscripcion_desde" => "2026-07-01",
                    "preinscripcion_hasta" => "2026-09-30",
                    "es_asincronica" => false,
                    "encuentros_sincronicos" => [],
                    "preinscripcion" => [
                        "abierta" => true,
                        "url" => "https://preinscripciones.flacso.edu.uy/seminarios/22/ediciones/220/",
                    ],
                ],
            ];
        }
        return [];
    }
}

require_once __DIR__ . "/../inc/academic-public-model.php";

$failures = 0;
function assert_same($expected, $actual, string $message): void {
    global $failures;
    if ($expected !== $actual) {
        $failures++;
        fwrite(STDERR, "FAIL: {$message}
Expected: " . var_export($expected, true) . "
Actual: " . var_export($actual, true) . "
");
    } else {
        echo "PASS: {$message}
";
    }
}

// 1. Tipos y etiquetas de oferta
assert_same("maestria", flacso_theme_get_offer_type(10), "OfertaAcademica devuelve el tipo canónico");
assert_same("", flacso_theme_get_offer_type(20), "Seminario no tiene tipo de oferta academica");
assert_same(true, flacso_theme_is_seminar(20), "flacso_theme_is_seminar identifica el CPT seminario");
assert_same("Seminario", flacso_theme_get_offer_type_label(20), "Seminario tiene etiqueta publica correcta");
assert_same("Maestría", flacso_theme_get_offer_type_label(10), "Maestría tiene etiqueta publica correcta");
assert_same("Doctorado", flacso_theme_get_offer_type_label(40), "Doctorado tiene etiqueta publica correcta");

// 2. Contexto de Oferta Académica
$offer_context = flacso_theme_get_offer_context(10);
assert_same(100, $offer_context["instance_id"], "Resuelve cohorte vigente de OfertaAcademica");
assert_same("Cohorte V", $offer_context["instance"]["label"], "Lee nombre de cohorte en números romanos");
assert_same(5, $offer_context["instance"]["numero"], "Lee número de cohorte");
assert_same("2026-08-15", $offer_context["instance"]["fecha_inicio"], "Lee fecha de inicio de la cohorte");
assert_same("https://preinscripciones.flacso.edu.uy/ofertas/10/cohortes/100/", $offer_context["registration"]["url"], "URL de preinscripción apunta al portal externo");
assert_same(true, $offer_context["registration"]["is_open"], "Preinscripción abierta");
assert_same(180000.0, $offer_context["instance"]["valor_uyu"], "Lee arancel UYU desde TablaPrecio");

// 3. Contexto de Seminario
$seminar_context = flacso_theme_get_seminar_context(20);
assert_same(200, $seminar_context["instance_id"], "Resuelve edición vigente de Seminario");
assert_same("2026-09-01", $seminar_context["instance"]["fecha_inicio"], "Lee fecha de inicio de la edición");
assert_same("Virtual", $seminar_context["instance"]["modalidad"], "Lee modalidad de la edición");
assert_same(12000.0, $seminar_context["instance"]["valor_uyu"], "Lee arancel general UYU desde TablaPrecio");
assert_same(10200.0, $seminar_context["instance"]["valor_uyu_15_descuento"], "Lee arancel con descuento 15% OFF desde TablaPrecio");
assert_same("https://preinscripciones.flacso.edu.uy/seminarios/20/ediciones/200/", $seminar_context["registration"]["url"], "URL de preinscripción externa de la edición");
assert_same(2, count($seminar_context["instance"]["docentes_ids"]), "Resuelve docentes de la edición");
assert_same(1, count($seminar_context["instance"]["encuentros"]), "Resuelve encuentros sincrónicos de la edición");

// 3.b Seminario Integrado (hereda transitivamente docentes y fechas de componentes)
$integrated_context = flacso_theme_get_seminar_context(22);
assert_same(true, $integrated_context["is_integrated"], "Identifica seminario integrado");
assert_same([91, 92], $integrated_context["instance"]["docentes_ids"], "Hereda transitivamente docentes de las ediciones componentes");
assert_same("2026-09-01", $integrated_context["instance"]["fecha_inicio"], "Hereda transitivamente fecha inicio de componentes");
assert_same("2026-11-15", $integrated_context["instance"]["fecha_fin"], "Hereda transitivamente fecha fin de componentes");
assert_same(1, count($integrated_context["instance"]["encuentros"]), "Hereda transitivamente encuentros sincrónicos de componentes");

// 4. Catálogo de Seminarios
$catalog = flacso_theme_get_seminars_catalog();
$catalog_ids = array_map(static function ($item) { return $item["offer_id"]; }, $catalog);
assert_same([20, 22, 21], $catalog_ids, "Catálogo lista todos los seminarios ordenados por fecha de inicio");

// 5. Relaciones de programa
$program_seminars = flacso_theme_get_program_seminars_catalog(10);
$program_seminar_ids = array_map(static function ($item) { return $item["offer_id"]; }, $program_seminars);
assert_same([20], $program_seminar_ids, "Relación de oferta académica devuelve seminarios vinculados");

// 6. Formateo de fechas y períodos de Cohortes
assert_same("2027 – 2029", flacso_theme_format_cohort_dates([
    "precision_fecha_inicio" => "anio",
    "fecha_inicio" => "2027-01-01",
    "fecha_fin" => "2029-02-01",
    "anio_inicio" => 2027,
    "anio_fin" => 2029,
]), "Formatea período de años cuando la precisión es anio");

assert_same("2027", flacso_theme_format_cohort_dates([
    "precision_fecha_inicio" => "anio",
    "fecha_inicio" => "2027-01-01",
    "anio_inicio" => 2027,
]), "Formatea año único de inicio cuando la precisión es anio");

// 7. Contexto para Carta de Presentación
assert_same(true, array_key_exists("reconocido_mec", $offer_context["academic"]), "Contexto incluye reconocido_mec para carta");
assert_same(true, array_key_exists("carta_hero_etiqueta", $offer_context["academic"]), "Contexto incluye carta_hero_etiqueta");
assert_same(true, array_key_exists("menciones", $offer_context["academic"]), "Contexto incluye menciones");
assert_same(true, array_key_exists("colores_presentacion", $offer_context["academic"]), "Contexto incluye colores_presentacion");
assert_same("#0057a8", $offer_context["color_principal"], "Contexto expone color_principal en la raíz");
assert_same("#0057a8", $offer_context["academic"]["color_principal"], "Contexto expone color_principal en academic");
assert_same(true, file_exists(__DIR__ . "/../template-parts/oferta/carta.php"), "Template part de carta existe");
assert_same(true, file_exists(__DIR__ . "/../assets/css/single-carta.css"), "Estilos de carta existen");

exit($failures > 0 ? 1 : 0);

