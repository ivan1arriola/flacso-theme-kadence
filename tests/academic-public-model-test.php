<?php
/**
 * Test de contrato para inc/academic-public-model.php con el modelo academico final (v7).
 */

define("ABSPATH", __DIR__ . "/");

if (!function_exists("get_option")) {
    function get_option($key, $default = false) { return $default; }
}
if (!function_exists("mb_strtoupper")) {
    function mb_strtoupper($value, $encoding = null) { return strtoupper($value); }
}
if (!function_exists("mb_substr")) {
    function mb_substr($value, $start, $length = null, $encoding = null) {
        return $length === null ? substr($value, $start) : substr($value, $start, $length);
    }
}

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
    41 => (object) ["ID" => 41, "post_type" => "oferta-academica", "post_status" => "publish", "post_title" => "Diploma Sin Cohorte", "post_name" => "diploma-sin-cohorte"],
    42 => (object) ["ID" => 42, "post_type" => "oferta-academica", "post_status" => "publish", "post_title" => "Oferta con cohorte nueva", "post_name" => "oferta-nueva"],
    43 => (object) ["ID" => 43, "post_type" => "oferta-academica", "post_status" => "publish", "post_title" => "Oferta sin cohorte publicada", "post_name" => "oferta-sin-publicada"],
    44 => (object) ["ID" => 44, "post_type" => "oferta-academica", "post_status" => "publish", "post_title" => "Oferta con instancia exclusivamente presencial", "post_name" => "oferta-instancias-presenciales"],
    500 => (object) ["ID" => 500, "post_type" => "tabla-precio", "post_status" => "publish", "post_title" => "Aranceles Seminarios 2026", "post_name" => "tabla-seminarios-2026"],
    501 => (object) ["ID" => 501, "post_type" => "tabla-precio", "post_status" => "publish", "post_title" => "Aranceles Maestría 2026", "post_name" => "tabla-maestria-2026"],
    91 => (object) ["ID" => 91, "post_type" => "docente", "post_status" => "publish", "post_title" => "Dra. Ana Pérez", "post_name" => "ana-perez"],
    92 => (object) ["ID" => 92, "post_type" => "docente", "post_status" => "publish", "post_title" => "Dr. Juan Gómez", "post_name" => "juan-gomez"],
];

$GLOBALS["types"] = [10 => "maestria", 40 => "doctorado", 41 => "diploma", 42 => "maestria", 43 => "maestria", 44 => "maestria"];
$GLOBALS["meta"] = [
    10 => [
        "programa_academico_id" => 0,
        "presentacion" => "Presentación de la maestría",
        "carga_horaria" => 480,
        "creditos" => 60,
        "reconocimiento_internacional" => 1,
        "reconocimiento_red_flacso" => 1,
        // Valores históricos desactivados: la carta debe ignorarlos.
        "mostrar_costos_envio" => 0,
        "mostrar_expedicion_titulo" => 0,
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
        $GLOBALS["offer_catalog_calls"][$id] = ($GLOBALS["offer_catalog_calls"][$id] ?? 0) + 1;
        if ($id === 40) {
            return [
                "id" => 40,
                "nombre" => "Doctorado en Ciencias Sociales",
                "slug" => "doctorado",
                "tipo" => "doctorado",
                "programa_academico_id" => 0,
                "presentacion" => "Presentación del doctorado",
                "carga_horaria" => 600,
                "creditos" => 80,
                "seminarios" => [],
                "cohortes" => [],
                "cohorte_vigente" => null,
                "cohorte_consulta" => [
                    "id" => 400,
                    "nombre" => "Cohorte 2027",
                    "numero" => 1,
                    "numero_romano" => "I",
                    "preinscripcion" => [
                        "abierta" => false,
                        "url" => "",
                    ],
                ],
            ];
        }
        if ($id === 41) {
            return [
                "id" => 41,
                "nombre" => "Diploma Sin Cohorte",
                "slug" => "diploma-sin-cohorte",
                "tipo" => "diploma",
                "programa_academico_id" => 0,
                "presentacion" => "",
                "seminarios" => [],
                "cohortes" => [],
                "cohorte_vigente" => null,
                "cohorte_consulta" => null,
            ];
        }
        if ($id === 44) {
            $data = self::get_offer(42);
            $data["id"] = 44;
            $data["cohorte_mas_reciente"]["instancias_presenciales"] = true;
            return $data;
        }
        if ($id === 42) {
            $data = self::get_offer(10);
            $data["id"] = 42;
            // Oferta con bandera heredada activada, pero cohorte actual
            // desactivada: no debe aparecer el aviso.
            $data["carta_instancias_presenciales"] = true;
            $data["cohorte_mas_reciente"] = [
                "id" => 102, "nombre" => "Cohorte VI", "numero" => 6, "numero_romano" => "VI",
                "fecha_inicio" => "2027-08-15", "fecha_fin" => "2029-07-31",
                "precision_fecha_inicio" => "dia", "estado" => "planificada",
                "tabla_precio_id" => 500, "modalidad" => "virtual",
                "instancias_presenciales" => false,
                "calendario_academico" => "https://flacso.edu.uy/calendario-nuevo.pdf",
                "link_preinscripcion" => "https://preinscripciones.flacso.edu.uy/ofertas/42/cohortes/102/",
                "preinscripcion" => ["abierta" => false, "url" => "https://preinscripciones.flacso.edu.uy/ofertas/42/cohortes/102/"],
            ];
            return $data;
        }
        if ($id === 43) {
            $data = self::get_offer(10);
            $data["id"] = 43;
            $data["cohorte_mas_reciente"] = null;
            return $data;
        }
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
            "cohorte_consulta" => [
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
        $GLOBALS["seminar_catalog_calls"][$id] = ($GLOBALS["seminar_catalog_calls"][$id] ?? 0) + 1;
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

// 2.a La cohorte pública de mayor número prima sobre otra anterior abierta.
$latest_context = flacso_theme_get_offer_context(42);
assert_same(102, $latest_context["instance_id"], "Web y carta usan la Cohorte VI en vez de la V abierta");
assert_same("Cohorte VI", $latest_context["instance"]["label"], "El título visible pertenece a la nueva cohorte");
assert_same("2027-08-15", $latest_context["instance"]["fecha_inicio"], "La fecha visible pertenece a la nueva cohorte");
assert_same("https://flacso.edu.uy/calendario-nuevo.pdf", $latest_context["instance"]["calendario_academico"], "El calendario pertenece a la nueva cohorte");
assert_same(12000.0, $latest_context["instance"]["valor_uyu"], "Los aranceles corresponden a la nueva cohorte");
assert_same("https://preinscripciones.flacso.edu.uy/ofertas/42/cohortes/102/", $latest_context["registration"]["url"], "El link externo corresponde a la cohorte presentada");
assert_same(false, $latest_context["registration"]["is_open"], "No se hereda apertura de inscripciones de la cohorte anterior");
assert_same(102, $latest_context["registration"]["instance_id"], "El CTA está contextualizado en la cohorte nueva");
assert_same("Cohorte VI", $latest_context["inquiry_cohort"]["name"], "Formulario de consultas usa la misma cohorte VI");
assert_same(6, $latest_context["inquiry_cohort"]["number"], "El número del formulario coincide con el de la web");
assert_same(false, $latest_context["inquiry_cohort"]["is_open"], "El formulario no hereda la inscripción abierta de V");
assert_same("Próxima cohorte — Cohorte VI", $latest_context["inquiry_cohort"]["label"], "El texto del formulario coincide con VI");
assert_same(false, $latest_context["academic"]["carta_instancias_presenciales"], "La cohorte nueva desmarcada prevalece sobre el antiguo ajuste de Oferta");
$exclusively_present_context = flacso_theme_get_offer_context(44);
assert_same(true, $exclusively_present_context["academic"]["carta_instancias_presenciales"], "La marca de actividades exclusivamente presenciales se conserva en la cohorte");
assert_same("Virtual", $exclusively_present_context["instance"]["modalidad"], "La modalidad general sigue siendo virtual aunque haya sesiones solo presenciales");

$unpublished_context = flacso_theme_get_offer_context(43);
assert_same(0, $unpublished_context["instance_id"], "Si no hay cohorte publicada no se filtra una anterior");
assert_same(false, $unpublished_context["registration"]["is_open"], "Sin cohorte publicada no se abre CTA");
assert_same(false, $unpublished_context["inquiry_cohort"]["has_cohort"], "Sin cohorte publicada el formulario no muestra otra cohorte");

// 2.b Cohorte de Consulta (Inquiry Cohort View Model)
assert_same(true, isset($offer_context["inquiry_cohort"]), "Contexto incluye inquiry_cohort");
assert_same(true, $offer_context["inquiry_cohort"]["has_cohort"], "inquiry_cohort has_cohort es true para cohorte abierta");
assert_same("Cohorte V", $offer_context["inquiry_cohort"]["name"], "inquiry_cohort name es Cohorte V");
assert_same(5, $offer_context["inquiry_cohort"]["number"], "inquiry_cohort number es 5");
assert_same(true, $offer_context["inquiry_cohort"]["is_open"], "inquiry_cohort is_open es true");
assert_same("Inscripciones abiertas — Cohorte V", $offer_context["inquiry_cohort"]["label"], "inquiry_cohort label para cohorte abierta");

$offer_40_context = flacso_theme_get_offer_context(40);
assert_same(true, isset($offer_40_context["inquiry_cohort"]), "Contexto oferta 40 incluye inquiry_cohort");
assert_same(true, $offer_40_context["inquiry_cohort"]["has_cohort"], "inquiry_cohort has_cohort con cohorte cerrada es true");
assert_same("Cohorte 2027", $offer_40_context["inquiry_cohort"]["name"], "inquiry_cohort name es Cohorte 2027");
assert_same(1, $offer_40_context["inquiry_cohort"]["number"], "inquiry_cohort number es 1");
assert_same(false, $offer_40_context["inquiry_cohort"]["is_open"], "inquiry_cohort is_open con cohorte cerrada es false");
assert_same("Próxima cohorte — Cohorte 2027", $offer_40_context["inquiry_cohort"]["label"], "inquiry_cohort label para cohorte cerrada");

$offer_41_context = flacso_theme_get_offer_context(41);
assert_same(true, isset($offer_41_context["inquiry_cohort"]), "Contexto oferta 41 incluye inquiry_cohort");
assert_same(false, $offer_41_context["inquiry_cohort"]["has_cohort"], "inquiry_cohort has_cohort sin cohorte es false");
assert_same("", $offer_41_context["inquiry_cohort"]["name"], "inquiry_cohort name sin cohorte es vacío");
assert_same(null, $offer_41_context["inquiry_cohort"]["number"], "inquiry_cohort number sin cohorte es null");
assert_same(false, $offer_41_context["inquiry_cohort"]["is_open"], "inquiry_cohort is_open sin cohorte es false");
assert_same("", $offer_41_context["inquiry_cohort"]["label"], "inquiry_cohort label sin cohorte es vacío");

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

assert_same(22, $seminar_context["relations"]["integrated_parent"]["id"] ?? null, "El componente encuentra a su seminario integrado padre (mapa por petición)");
assert_same([10], array_column($seminar_context["relations"]["related_programs"], "id"), "Los programas relacionados salen del mapa seminario→ofertas, sin repetir ofertas");

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
assert_same([], $catalog_ids, "Catálogo excluye seminarios sin edición vigente aunque conserve registros históricos");

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
assert_same(true, $offer_context["academic"]["reconocimiento_internacional"], "Contexto conserva el reconocimiento internacional/apostillado");
assert_same(true, $offer_context["academic"]["reconocimiento_red_flacso"], "Contexto incluye el reconocimiento de la red FLACSO");
assert_same(true, $offer_context["academic"]["mostrar_costos_envio"], "Los costos de envío son obligatorios aunque el metadato sea 0");
assert_same(true, $offer_context["academic"]["mostrar_expedicion_titulo"], "La expedición es obligatoria aunque el metadato sea 0");
assert_same(true, $offer_41_context["academic"]["mostrar_costos_envio"], "Los costos de envío se incluyen sin metadatos anteriores");
assert_same(true, $offer_41_context["academic"]["mostrar_expedicion_titulo"], "La expedición se incluye sin metadatos anteriores");
assert_same(false, array_key_exists("carta_hero_etiqueta", $offer_context["academic"]), "Contexto omite la etiqueta de preinscripcion");
assert_same(true, array_key_exists("menciones", $offer_context["academic"]), "Contexto incluye menciones");
assert_same(true, array_key_exists("colores_presentacion", $offer_context["academic"]), "Contexto incluye colores_presentacion");
assert_same("#0057a8", $offer_context["color_principal"], "Contexto expone color_principal en la raíz");
assert_same("#0057a8", $offer_context["academic"]["color_principal"], "Contexto expone color_principal en academic");
assert_same(true, file_exists(__DIR__ . "/../template-parts/oferta/carta.php"), "Template part de carta existe");
assert_same(true, file_exists(__DIR__ . "/../assets/css/single-carta.css"), "Estilos de carta existen");
$offer_template = file_get_contents(__DIR__ . "/../oferta-academica/page.php");
require_once __DIR__ . '/helpers/carta-source.php';
$carta_template = flacso_carta_template_source(__DIR__ . "/..");
assert_same(true, strpos($offer_template, "reconocimiento_red_flacso") !== false, "La página pública usa la opción independiente de reconocimiento de red");
assert_same(true, strpos($offer_template, "Incluye instancias exclusivamente presenciales") !== false, "La oferta explica el carácter exclusivo de las instancias");
assert_same(true, strpos($carta_template, "carta_badge_presenciales") !== false, "La carta explica el carácter exclusivo de las instancias (texto y regla en el plugin)");
assert_same(false, strpos($carta_template, 'if ($mostrar_instancias_presenciales ||') !== false, "La bandera no debe convertir una modalidad virtual en híbrida");
assert_same(true, strpos($carta_template, "Modalidad a confirmar") !== false, "Sin modalidad clara no se inventa cursada híbrida ni virtual");
assert_same(true, strpos($carta_template, "carta_badge_red") !== false, "La carta usa la opción independiente de reconocimiento de red (regla en el plugin)");
assert_same(true, strpos($carta_template, 'carta_envio_titulos') !== false && strpos($carta_template, "\$textos['envio_titulos']") !== false, "El texto de envío viene de los ajustes del plugin");
assert_same(false, strpos($carta_template, "USD 30") !== false, "La carta no publica tarifas fijas de envío");
assert_same(true, strpos($carta_template, 'carta_titulo_exterior') !== false, "La expedición del título viene de los ajustes del plugin");
assert_same(true, strpos($carta_template, "\$textos['titulo_exterior']") !== false && strpos($carta_template, "\$textos['titulo_sin_costo']") !== false, "La carta muestra la emisión del título con costo o sin costo según su regla");
assert_same(true, strpos($carta_template, 'FLACSO_Carta_Settings::visible_text') !== false, "Los textos institucionales salen de FLACSO_Carta_Settings");
assert_same(false, strpos($carta_template, 'if ($mostrar_expedicion)') !== false, "No puede condicionarse la expedición a una opción");
assert_same(true, strpos($offer_template, "Apostillado de La Haya") !== false, "La página pública identifica por separado la titulación apostillada");

// 8. E2: memoización de contextos (P3a), retiro de código muerto (M1) y LCP de carta/programa (P2)
$offer_calls_before = $GLOBALS["offer_catalog_calls"][40] ?? 0;
$offer_40_again = flacso_theme_get_offer_context(40);
assert_same($offer_40_context, $offer_40_again, "flacso_theme_get_offer_context devuelve el mismo resultado memorizado");
assert_same($offer_calls_before, $GLOBALS["offer_catalog_calls"][40] ?? 0, "flacso_theme_get_offer_context no recalcula en la misma petición");

$sem_calls_before = $GLOBALS["seminar_catalog_calls"][20] ?? 0;
$seminar_again = flacso_theme_get_seminar_context(20);
assert_same($seminar_context, $seminar_again, "flacso_theme_get_seminar_context devuelve el mismo resultado memorizado");
assert_same($sem_calls_before, $GLOBALS["seminar_catalog_calls"][20] ?? 0, "flacso_theme_get_seminar_context no recalcula en la misma petición");

assert_same(false, function_exists("flacso_theme_normalize_duration_html"), "M1: flacso_theme_normalize_duration_html eliminada");
if (!function_exists("add_action")) { function add_action($hook, $cb, $p = 10, $a = 1) {} }
if (!function_exists("add_filter")) { function add_filter($hook, $cb, $p = 10, $a = 1) {} }
require_once __DIR__ . "/../inc/open-graph.php";
assert_same(false, function_exists("flacso_theme_build_url_image_data"), "M1: flacso_theme_build_url_image_data eliminada");
assert_same(false, function_exists("flacso_theme_get_current_request_url"), "M1: flacso_theme_get_current_request_url eliminada");
assert_same(false, file_exists(__DIR__ . "/../assets/js/page-formacion.js"), "M1: assets/js/page-formacion.js eliminado");

$carta_hero_tpl = (string) file_get_contents(__DIR__ . "/../template-parts/carta/hero.php");
assert_same(true, strpos($carta_hero_tpl, "wp_get_attachment_image(") !== false && strpos($carta_hero_tpl, "'fetchpriority' => 'high'") !== false && strpos($carta_hero_tpl, 'loading="lazy"') === false, "P2: hero de carta usa large/srcset con fetchpriority y sin lazy");
$prog_tpl = (string) file_get_contents(__DIR__ . "/../single-programa-academico.php");
assert_same(true, strpos($prog_tpl, "'fetchpriority' => 'high'") !== false, "P2: hero de programa marca fetchpriority high");

exit($failures > 0 ? 1 : 0);

