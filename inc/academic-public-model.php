<?php
/**
 * Adaptador de presentacion de solo lectura para el modelo academico de FLACSO Uruguay.
 *
 * El plugin es la unica fuente de verdad para los CPTs ProgramaAcademico,
 * OfertaAcademica, Cohorte, Seminario, Edicion y TablaPrecio.
 * Este archivo construye View Models estables y limpios para las plantillas del tema.
 *
 * @package kadence-child-flacso
 */

if (!defined("ABSPATH")) {
    exit;
}

/**
 * Obtiene el slug canonico del tipo de oferta academica.
 */
function flacso_theme_get_offer_type(int $post_id): string {
    if ($post_id <= 0) {
        return "";
    }

    if (class_exists("FLACSO_Oferta_Academica")) {
        $tipo = FLACSO_Oferta_Academica::get_tipo($post_id);
        if ($tipo !== "") {
            return $tipo;
        }
    }

    $terms = wp_get_object_terms($post_id, "tipo-oferta-academica");
    return !is_wp_error($terms) && !empty($terms)
        ? sanitize_key((string) $terms[0]->slug)
        : "";
}

/**
 * Resuelve los títulos intermedios como otras Ofertas Académicas publicadas.
 * La procedencia académica siempre es estrictamente descendente:
 * Maestría > Especialización > Diplomado > Diploma.
 *
 * @return array<int,array{oferta_id:int,titulo:string,url:string,menciones:array<int,string>}>
 */
function flacso_theme_resolve_intermediate_titles(int $parent_id, $raw): array {
    if (!is_array($raw)) {
        return [];
    }
    $levels = ['maestria' => 4, 'especializacion' => 3, 'diplomado' => 2, 'diploma' => 1];
    $parent_type = flacso_theme_get_offer_type($parent_id);
    $parent_level = $levels[$parent_type] ?? 0;
    if ($parent_level <= 1) {
        return [];
    }

    $titles = [];
    $seen = [];
    foreach ($raw as $entry) {
        if (!is_array($entry)) {
            continue;
        }
        $target_id = absint($entry['oferta_id'] ?? 0);
        if (!$target_id || $target_id === $parent_id || isset($seen[$target_id])) {
            continue;
        }
        $target = get_post($target_id);
        if (!$target || $target->post_type !== 'oferta-academica' || $target->post_status !== 'publish') {
            continue;
        }
        $target_level = $levels[flacso_theme_get_offer_type($target_id)] ?? 0;
        if (!$target_level || $target_level >= $parent_level) {
            continue;
        }
        $permalink = get_permalink($target_id);
        if (!$permalink) {
            continue;
        }
        $available = get_post_meta($target_id, 'menciones', true);
        $available = is_array($available) ? array_map('strval', $available) : [];
        $requested = is_array($entry['menciones'] ?? null) ? array_map('strval', $entry['menciones']) : [];
        $mentions = array_values(array_unique(array_intersect($requested, $available)));
        $titles[] = [
            'oferta_id' => $target_id,
            'titulo' => (string) $target->post_title,
            'url' => (string) $permalink,
            'menciones' => $mentions,
        ];
        $seen[$target_id] = true;
    }
    return $titles;
}

/**
 * Determina si un post corresponde a un Seminario.
 */
function flacso_theme_is_seminar(int $post_id): bool {
    return $post_id > 0 && get_post_type($post_id) === "seminario";
}

/**
 * Obtiene la etiqueta visible para el tipo de propuesta academica.
 */
function flacso_theme_get_offer_type_label(int $post_id, string $type = ""): string {
    if (flacso_theme_is_seminar($post_id)) {
        return __("Seminario", "flacso-uruguay");
    }

    $type = $type !== "" ? $type : flacso_theme_get_offer_type($post_id);
    $labels = [
        "doctorado"       => __("Doctorado", "flacso-uruguay"),
        "maestria"        => __("Maestría", "flacso-uruguay"),
        "especializacion" => __("Especialización", "flacso-uruguay"),
        "diplomado"       => __("Diplomado", "flacso-uruguay"),
        "diploma"         => __("Diploma", "flacso-uruguay"),
        "seminario"       => __("Seminario", "flacso-uruguay"),
    ];
    return $labels[$type] ?? __("Oferta Académica", "flacso-uruguay");
}

/**
 * Formatea un numero como flotante nulo si esta vacio.
 *
 * @param mixed $value
 * @return float|null
 */
function flacso_theme_nullable_number($value) {
    if ($value === "" || $value === null) {
        return null;
    }
    if (is_int($value) || is_float($value)) {
        return (float) $value;
    }
    $str = trim((string) $value);
    if ($str === "") {
        return null;
    }
    if (strpos($str, ".") !== false && strpos($str, ",") !== false) {
        $str = str_replace(".", "", $str);
        $str = str_replace(",", ".", $str);
    } elseif (strpos($str, ".") !== false && substr_count($str, ".") === 1 && strlen(substr($str, strrpos($str, ".") + 1)) === 3) {
        $str = str_replace(".", "", $str);
    } elseif (strpos($str, ",") !== false) {
        $str = str_replace(",", ".", $str);
    }
    $cleaned = preg_replace("/[^0-9.-]/", "", $str);
    return is_numeric($cleaned) ? (float) $cleaned : null;
}

/**
 * Extrae y estructura los datos de una TablaPrecio.
 */
function flacso_theme_get_price_table_data(?int $table_id): ?array {
    if (!$table_id || $table_id <= 0) {
        return null;
    }

    $table_post = get_post($table_id);
    if (!$table_post || $table_post->post_type !== "tabla-precio") {
        return null;
    }

    $rows = get_post_meta($table_id, "precios_filas", true);
    $rows = is_array($rows) ? $rows : [];
    $type = (string) get_post_meta($table_id, "tabla_precios_tipo", true);
    $note = (string) get_post_meta($table_id, "precios_nota", true);
    $show_usd = (bool) get_post_meta($table_id, "mostrar_precios_dolares", true);

    $highlighted_row = null;
    foreach ($rows as $row) {
        if (!empty($row["destacada"])) {
            $highlighted_row = $row;
            break;
        }
    }
    if (!$highlighted_row && !empty($rows)) {
        $highlighted_row = $rows[0];
    }

    $val_uyu = $highlighted_row ? flacso_theme_nullable_number($highlighted_row["uyu"] ?? null) : null;
    $val_usd = $highlighted_row ? flacso_theme_nullable_number($highlighted_row["usd"] ?? null) : null;

    // Buscar si existe una fila secundaria con descuento (ej. Comunidad FLACSO / 15%)
    $discount_row = null;
    foreach ($rows as $row) {
        $concept = strtolower((string) ($row["concepto"] ?? ""));
        if (strpos($concept, "descuento") !== false || strpos($concept, "comunidad") !== false || strpos($concept, "15%") !== false) {
            $discount_row = $row;
            break;
        }
    }

    $val_uyu_desc = $discount_row ? flacso_theme_nullable_number($discount_row["uyu"] ?? null) : null;
    $val_usd_desc = $discount_row ? flacso_theme_nullable_number($discount_row["usd"] ?? null) : null;

    return [
        "id"                     => $table_id,
        "title"                  => (string) $table_post->post_title,
        "tipo"                   => $type,
        "filas"                  => $rows,
        "nota"                   => $note,
        "mostrar_dolares"        => $show_usd,
        "valor_uyu"              => $val_uyu,
        "valor_usd"              => $val_usd,
        "valor_uyu_15_descuento" => $val_uyu_desc,
        "valor_usd_15_descuento" => $val_usd_desc,
    ];
}

/**
 * Obtiene el View Model unificado de una Oferta Academica.
 */
function flacso_theme_get_offer_context(int $offer_id): array {
    if ($offer_id <= 0) {
        return [];
    }

    $post = get_post($offer_id);
    if (!$post) {
        return [];
    }

    if ($post->post_type === "seminario") {
        return flacso_theme_get_seminar_context($offer_id);
    }

    if ($post->post_type !== "oferta-academica") {
        return [];
    }

    $data = class_exists("FLACSO_Academic_Catalog")
        ? FLACSO_Academic_Catalog::get_offer($offer_id)
        : (class_exists("FLACSO_Academic_Repository") ? FLACSO_Academic_Repository::to_array("ofertas", $offer_id) : []);

    if (empty($data)) {
        return [];
    }

    $type = (string) ($data["tipo"] ?? flacso_theme_get_offer_type($offer_id));
    $cohorts = $data["cohortes"] ?? [];
    // En la página pública y la carta se presenta SIEMPRE la última cohorte
    // publicada (mayor número), aunque otra anterior tenga inscripción abierta.
    // Mantener cohorte_vigente para integraciones operativas; las consultas usan
    // su propio resolvedor cohorte_consulta, independiente de la presentación.
    // array_key_exists conserva null si todavía no existe cohorte publicada.
    $current_cohort = array_key_exists("cohorte_mas_reciente", $data)
        ? $data["cohorte_mas_reciente"]
        : ($data["cohorte_vigente"] ?? (!empty($cohorts) ? $cohorts[0] : null));

    $table_id = !empty($current_cohort["tabla_precio_id"]) ? absint($current_cohort["tabla_precio_id"]) : 0;
    $price_table = flacso_theme_get_price_table_data($table_id);

    $raw_anio_inicio = absint($current_cohort["anio_inicio"] ?? 0);
    $raw_anio_fin = absint($current_cohort["anio_fin"] ?? 0);
    $raw_fecha_inicio = (string) ($current_cohort["fecha_inicio"] ?? "");
    $raw_fecha_fin = (string) ($current_cohort["fecha_fin"] ?? "");
    if ($raw_anio_inicio === 0 && $raw_fecha_inicio !== "") {
        $raw_anio_inicio = (int) substr($raw_fecha_inicio, 0, 4);
    }
    if ($raw_anio_fin === 0 && $raw_fecha_fin !== "") {
        $raw_anio_fin = (int) substr($raw_fecha_fin, 0, 4);
    }

    $instance = [
        "id"                     => absint($current_cohort["id"] ?? 0),
        "label"                  => (string) ($current_cohort["nombre"] ?? __("Cohorte", "flacso-uruguay")),
        "numero"                 => absint($current_cohort["numero"] ?? 0),
        "numero_romano"          => (string) ($current_cohort["numero_romano"] ?? ""),
        "fecha_inicio"           => $raw_fecha_inicio,
        "fecha_fin"              => $raw_fecha_fin,
        "anio_inicio"            => $raw_anio_inicio,
        "anio_fin"               => $raw_anio_fin,
        "precision_fecha_inicio" => (string) ($current_cohort["precision_fecha_inicio"] ?? "dia"),
        "estado"                 => (string) ($current_cohort["estado"] ?? "planificada"),
        "calendario_academico"   => (string) ($current_cohort["calendario_academico"] ?? ""),
        "tabla_precio_id"        => $table_id,
        "tabla_precio"           => $price_table,
        "valor_uyu"              => $price_table["valor_uyu"] ?? null,
        "valor_usd"              => $price_table["valor_usd"] ?? null,
        "valor_uyu_15_descuento" => $price_table["valor_uyu_15_descuento"] ?? null,
        "valor_usd_15_descuento" => $price_table["valor_usd_15_descuento"] ?? null,
        "modalidad"              => flacso_theme_format_modality($current_cohort["modalidad"] ?? ""),
        "modalidad_descripcion"  => (string) ($current_cohort["modalidad_descripcion"] ?? ""),
        "docentes_ids"           => [],
        "encuentros"             => [],
    ];
    $instance["fechas_formateadas"] = flacso_theme_format_cohort_dates($instance);

    $instance["has_valor_general"] = $instance["valor_uyu"] !== null || $instance["valor_usd"] !== null;
    $instance["has_valor_descuento"] = $instance["valor_uyu_15_descuento"] !== null || $instance["valor_usd_15_descuento"] !== null;
    $instance["has_inversion"] = $instance["has_valor_general"] || $instance["has_valor_descuento"];

    $registration_data = $current_cohort["preinscripcion"] ?? [];
    $link_preinscripcion = (string) ($current_cohort["link_preinscripcion"] ?? ($registration_data["url"] ?? ""));
    $is_registration_open = !empty($registration_data["abierta"]) && $link_preinscripcion !== "";

    $registration = [
        "is_open"        => $is_registration_open,
        "url"            => $link_preinscripcion,
        "instance_id"    => $instance["id"],
    ];

    $documentos_raw = $data["documentos"] ?? get_post_meta($offer_id, "documentos", true);
    $documentos = !empty($documentos_raw) && is_string($documentos_raw) ? json_decode($documentos_raw, true) : (is_array($documentos_raw) ? $documentos_raw : []);

    $cartamalla_pdf = !empty($documentos['cartamalla']['link']) ? trim((string) $documentos['cartamalla']['link']) : (string) get_post_meta($offer_id, "cartamalla_pdf_url", true);
    $malla_pdf = !empty($data['malla_curricular'])
    ? trim((string) $data['malla_curricular'])
    : (!empty($documentos['malla']['link'])
        ? trim((string) $documentos['malla']['link'])
        : (string) get_post_meta($offer_id, "malla_pdf_url", true));
    $calendario_pdf = trim((string) $instance["calendario_academico"]);

    $academic = [
        "presentacion"                => (string) ($data["presentacion"] ?? get_post_meta($offer_id, "presentacion", true)),
        "descripcion_html"            => (string) ($data["presentacion"] ?? get_post_meta($offer_id, "presentacion", true)),
        "resumen"                     => (string) ($data["resumen"] ?? get_post_meta($offer_id, "resumen", true)),
        "modalidad_html"              => (string) $instance["modalidad_descripcion"],
        "duracion_meses"              => (string) ($data["duracion_meses"] ?? ""),
        "perfil_ingreso_html"         => (string) ($data["perfil_ingreso_html"] ?? get_post_meta($offer_id, "perfil_ingreso_html", true)),
        "requisitos_ingreso_html"     => (string) ($data["requisitos_ingreso_html"] ?? get_post_meta($offer_id, "requisitos_ingreso_html", true)),
        "perfil_egreso_html"          => (string) ($data["perfil_egreso_html"] ?? get_post_meta($offer_id, "perfil_egreso_html", true)),
        "titulos_certificaciones_html"=> (string) ($data["titulos_certificaciones_html"] ?? get_post_meta($offer_id, "titulos_certificaciones_html", true)),
        "requisitos_egreso_html"      => (string) ($data["requisitos_egreso_html"] ?? get_post_meta($offer_id, "requisitos_egreso_html", true)),
        "financiacion_html" => (string) get_option("flacso_financiacion_html", get_option("flacso_carta_mas_info_financiacion_html", "")),
        "composicion_academica"       => is_array($data["composicion_academica"] ?? null) ? $data["composicion_academica"] : [],
        "carga_horaria"               => $data["carga_horaria"] ?? "",
        "creditos"                    => $data["creditos"] ?? "",
        "correo"                      => (string) ($data["correo"] ?? get_post_meta($offer_id, "correo", true)),
        "abreviacion"                 => (string) ($data["abreviacion"] ?? get_post_meta($offer_id, "abreviacion", true)),
        "documentos"                  => $documentos_raw,
        "cartamalla_pdf_url"          => $cartamalla_pdf,
        "malla_pdf_url"               => $malla_pdf,
        "calendario_pdf_url"          => $calendario_pdf,
        "equipos"                     => $data["equipos"] ?? get_post_meta($offer_id, "equipos", true),
        "coordinacion_academica"      => [],
        "carta_instancias_presenciales"=> !empty($current_cohort["instancias_presenciales"]) || !empty($data["carta_instancias_presenciales"]) || (bool) get_post_meta($offer_id, "carta_instancias_presenciales", true),
        "menciones"                   => (array) ($data["menciones"] ?? (get_post_meta($offer_id, "menciones", true) ?: [])),
        "orientaciones"               => (array) ($data["orientaciones"] ?? (get_post_meta($offer_id, "orientaciones", true) ?: [])),
        "titulos_intermedios"         => flacso_theme_resolve_intermediate_titles($offer_id, $data["titulos_intermedios"] ?? get_post_meta($offer_id, "titulos_intermedios", true)),
        "articulaciones"              => class_exists('FLACSO_Academic_Articulations')
            ? FLACSO_Academic_Articulations::for_offer($offer_id)
            : [],
        "reconocido_mec"              => !empty($data["reconocido_mec"]) || (bool) get_post_meta($offer_id, "reconocido_mec", true),
        "reconocimiento_internacional"=> !empty($data["reconocimiento_internacional"]) || (bool) get_post_meta($offer_id, "reconocimiento_internacional", true),
        "reconocimiento_red_flacso"   => !empty($data["reconocimiento_red_flacso"]) || (bool) get_post_meta($offer_id, "reconocimiento_red_flacso", true),
        "convenio_iin_oea"            => !empty($data["convenio_iin_oea"]) || (bool) get_post_meta($offer_id, "convenio_iin_oea", true),
        // Contenido informativo obligatorio en todas las cartas, incluso con metadatos históricos falsos.
        "mostrar_costos_envio"        => true,
        "mostrar_expedicion_titulo"   => true,
        "carta_presentacion_html"     => (string) ($data["carta_presentacion_html"] ?? get_post_meta($offer_id, "carta_presentacion_html", true)),
        "asistente_academica_docente_id" => absint($data["asistente_academica_docente_id"] ?? get_post_meta($offer_id, "asistente_academica_docente_id", true)),
        "asistente_academica_rol"     => (string) ($data["asistente_academica_rol"] ?? get_post_meta($offer_id, "asistente_academica_rol", true)),
        "asistente_academica_correo"  => (string) ($data["asistente_academica_correo"] ?? get_post_meta($offer_id, "asistente_academica_correo", true)),
        "colores_presentacion"        => (array) ($data["colores_presentacion"] ?? (get_post_meta($offer_id, "colores_presentacion", true) ?: [])),
        "color_principal"             => !empty($data["colores_presentacion"]["principal"])
            ? strtolower((string) $data["colores_presentacion"]["principal"])
            : (string) (get_post_meta($offer_id, "colores_presentacion", true)["principal"] ?? ""),
    ];

    $program_id = absint($data["programa_academico_id"] ?? 0);
    $program_coordination = [];
    if ($program_id > 0) {
        $coordination_meta = get_post_meta($program_id, "coordinacion", true);
        if (is_array($coordination_meta)) {
            $program_coordination = $coordination_meta;
        }
    }

    $raw_seminars = is_array($data["seminarios"] ?? null) ? $data["seminarios"] : [];

    // El formulario público se refiere a la MISMA cohorte mostrada en la web
    // y carta. Compatibilidad con plugins anteriores sin la nueva clave.
    $inquiry_cohort_data = array_key_exists("cohorte_mas_reciente", $data)
        ? $current_cohort
        : ($data['cohorte_consulta'] ?? $current_cohort);
    $has_inquiry_cohort  = !empty($inquiry_cohort_data);
    $inquiry_is_open     = $has_inquiry_cohort && !empty($inquiry_cohort_data['preinscripcion']['abierta']);
    $inquiry_name        = $has_inquiry_cohort ? (string) ($inquiry_cohort_data['nombre'] ?? '') : '';

    $inquiry_cohort = [
        'has_cohort' => $has_inquiry_cohort,
        'name'       => $inquiry_name,
        'number'     => $has_inquiry_cohort ? absint($inquiry_cohort_data['numero'] ?? 0) : null,
        'is_open'    => $inquiry_is_open,
        'label'      => $has_inquiry_cohort
            ? ($inquiry_is_open
                ? sprintf(__('Inscripciones abiertas — %s', 'flacso-uruguay'), $inquiry_name)
                : sprintf(__('Próxima cohorte — %s', 'flacso-uruguay'), $inquiry_name))
            : '',
    ];

    return [
        "offer_id"        => $offer_id,
        "instance_id"     => $instance["id"],
        "type"            => $type,
        "type_label"      => flacso_theme_get_offer_type_label($offer_id, $type),
        "is_seminar"      => false,
        "is_integrated"   => false,
        "title"           => (string) ($data["nombre"] ?? get_the_title($offer_id)),
        "slug"            => (string) ($data["slug"] ?? $post->post_name),
        "permalink"       => get_permalink($offer_id),
        "thumbnail_id"    => (int) get_post_thumbnail_id($offer_id),
        "thumbnail_url"   => (string) ($data["imagen"] ?? get_the_post_thumbnail_url($offer_id, "full")),
        "color_principal" => $academic["color_principal"],
        "academic"        => $academic,
        "instance"        => $instance,
        "registration"    => $registration,
        "inquiry_cohort"  => $inquiry_cohort,
        "program"         => [
            "id"           => $program_id,
            "coordinacion" => $program_coordination,
        ],
        "relations"       => [
            "is_integrated"         => false,
            "integrated_parent"     => null,
            "integrated_components" => [],
            "seminarios"            => $raw_seminars,
            "related_programs"      => [],
        ],
    ];
}

/**
 * Obtiene el View Model de un Seminario.
 */
function flacso_theme_get_seminar_context(int $seminar_id): array {
    static $resolving = [];
    if ($seminar_id <= 0 || isset($resolving[$seminar_id])) {
        return [];
    }
    $resolving[$seminar_id] = true;

    $post = get_post($seminar_id);
    if (!$post || $post->post_type !== "seminario") {
        unset($resolving[$seminar_id]);
        return [];
    }

    $data = class_exists("FLACSO_Academic_Catalog")
        ? FLACSO_Academic_Catalog::get_seminar($seminar_id)
        : (class_exists("FLACSO_Academic_Repository") ? FLACSO_Academic_Repository::to_array("seminarios", $seminar_id) : []);

    if (empty($data)) {
        unset($resolving[$seminar_id]);
        return [];
    }

    $editions = $data["ediciones"] ?? [];
    $current_edition = $data["edicion_vigente"] ?? (!empty($editions) ? $editions[0] : null);

    $table_id = !empty($current_edition["tabla_precio_id"]) ? absint($current_edition["tabla_precio_id"]) : 0;
    $price_table = flacso_theme_get_price_table_data($table_id);

    $docentes_ids = !empty($current_edition["docentes"]) && is_array($current_edition["docentes"])
        ? array_map("absint", $current_edition["docentes"])
        : [];

    $instance = [
        "id"                     => absint($current_edition["id"] ?? 0),
        "label"                  => (string) ($current_edition["nombre"] ?? __("Edición", "flacso-uruguay")),
        "anio"                   => absint($current_edition["anio"] ?? 0),
        "fecha_inicio"           => (string) ($current_edition["fecha_inicio"] ?? ""),
        "fecha_fin"              => (string) ($current_edition["fecha_fin"] ?? ""),
        "precision_fecha_inicio" => "dia",
        "estado"                 => (string) ($current_edition["estado"] ?? "planificada"),
        "modalidad"              => flacso_theme_format_modality($current_edition["modalidad"] ?? ""),
        "encuentros"             => is_array($current_edition["encuentros_sincronicos"] ?? null) ? $current_edition["encuentros_sincronicos"] : [],
        "es_asincronico"         => !empty($current_edition["es_asincronica"]) || empty($current_edition["encuentros_sincronicos"]),
        "docentes_ids"           => array_values(array_unique(array_filter($docentes_ids))),
        "tabla_precio_id"        => $table_id,
        "tabla_precio"           => $price_table,
        "valor_uyu"              => $price_table["valor_uyu"] ?? null,
        "valor_usd"              => $price_table["valor_usd"] ?? null,
        "valor_uyu_15_descuento" => $price_table["valor_uyu_15_descuento"] ?? null,
        "valor_usd_15_descuento" => $price_table["valor_usd_15_descuento"] ?? null,
    ];

    $instance["has_valor_general"] = $instance["valor_uyu"] !== null || $instance["valor_usd"] !== null;
    $instance["has_valor_descuento"] = $instance["valor_uyu_15_descuento"] !== null || $instance["valor_usd_15_descuento"] !== null;
    $instance["has_inversion"] = $instance["has_valor_general"] || $instance["has_valor_descuento"];

    $registration_data = $current_edition["preinscripcion"] ?? [];
    $edition_id = absint($instance["id"] ?? 0);
    $is_registration_open = isset($registration_data["abierta"])
        ? !empty($registration_data["abierta"])
        : ($edition_id > 0 && class_exists('FLACSO_Edicion') ? FLACSO_Edicion::accepts_registration($edition_id) : true);

    $link_preinscripcion = (string) ($current_edition["link_preinscripcion"] ?? ($registration_data["url"] ?? ""));
    if ($link_preinscripcion === "") {
        $link_preinscripcion = "https://preinscripciones.flacso.edu.uy/";
    }

    $registration = [
        "is_open"        => $is_registration_open,
        "url"            => $link_preinscripcion,
        "instance_id"    => $instance["id"],
    ];

    $academic = [
        "presentacion"              => (string) ($data["presentacion"] ?? ""),
        "objetivo_general"          => (string) ($data["objetivo_general"] ?? ""),
        "objetivos_especificos"     => is_array($data["objetivos_especificos"] ?? null) ? $data["objetivos_especificos"] : [],
        "composicion_academica"     => is_array($data["composicion_academica"] ?? null) ? $data["composicion_academica"] : [],
        "unidades_academicas"       => is_array($data["composicion_academica"] ?? null) ? $data["composicion_academica"] : [],
        "forma_aprobacion"          => (string) ($data["forma_aprobacion"] ?? ""),
        "carga_horaria"             => $data["carga_horaria"] ?? "",
        "carga_horaria_descripcion" => (string) ($data["carga_horaria_descripcion"] ?? ""),
        "creditos"                  => $data["creditos"] ?? "",
        "acreditacion"              => (string) ($data["acreditacion"] ?? ""),
        "acredita_maestria"         => !empty($data["acredita_maestria"]),
        "acredita_doctorado"        => !empty($data["acredita_doctorado"]),
        "correo"                    => (string) ($data["correo"] ?? ""),
    ];

    // Componentes del seminario (si es integrado)
    $raw_components = is_array($data["componentes"] ?? null) ? $data["componentes"] : [];
    $component_ids = array_values(array_filter(array_map(static function ($item): int {
        return is_array($item) ? absint($item["seminario_id"] ?? 0) : 0;
    }, $raw_components)));

    $components_posts = empty($component_ids) ? [] : get_posts([
        "post_type"      => "seminario",
        "post_status"    => "publish",
        "post__in"       => $component_ids,
        "posts_per_page" => -1,
        "orderby"        => "post__in",
    ]);

    $is_integrated = !empty($components_posts) || !empty($component_ids);

    // En caso de Seminario Integrado, la edición y los docentes se resuelven transitivamente de sus componentes
    if ($is_integrated && !empty($component_ids)) {
        $transitive_docentes_ids = [];
        $transitive_encuentros = [];
        $min_start = '';
        $max_end = '';

        foreach ($component_ids as $comp_id) {
            $comp_context = flacso_theme_get_seminar_context($comp_id);
            if (!empty($comp_context['instance'])) {
                if (!empty($comp_context['instance']['docentes_ids'])) {
                    $transitive_docentes_ids = array_merge($transitive_docentes_ids, $comp_context['instance']['docentes_ids']);
                }
                if (!empty($comp_context['instance']['encuentros'])) {
                    $transitive_encuentros = array_merge($transitive_encuentros, $comp_context['instance']['encuentros']);
                }
                $c_start = (string) ($comp_context['instance']['fecha_inicio'] ?? '');
                $c_end = (string) ($comp_context['instance']['fecha_fin'] ?? '');
                if ($c_start !== '' && ($min_start === '' || $c_start < $min_start)) {
                    $min_start = $c_start;
                }
                if ($c_end !== '' && ($max_end === '' || $c_end > $max_end)) {
                    $max_end = $c_end;
                }
            }
        }

        $instance['docentes_ids'] = array_values(array_unique(array_filter(array_merge($instance['docentes_ids'], $transitive_docentes_ids))));
        if (empty($instance['encuentros'])) {
            $instance['encuentros'] = $transitive_encuentros;
        }
        if (empty($instance['fecha_inicio']) && $min_start !== '') {
            $instance['fecha_inicio'] = $min_start;
        }
        if (empty($instance['fecha_fin']) && $max_end !== '') {
            $instance['fecha_fin'] = $max_end;
        }
    }

    $instance["docentes_posts"] = empty($instance["docentes_ids"]) ? [] : get_posts([
        "post_type"      => "docente",
        "post_status"    => "publish",
        "post__in"       => $instance["docentes_ids"],
        "posts_per_page" => -1,
        "orderby"        => "post__in",
    ]);

    // Si es un componente hijo de otro seminario integrado, encontrar al padre
    $parent = null;
    if (!$is_integrated) {
        $parent_candidates = get_posts([
            "post_type"      => "seminario",
            "post_status"    => "publish",
            "posts_per_page" => -1,
            "post__not_in"   => [$seminar_id],
        ]);
        foreach ($parent_candidates as $candidate) {
            $candidate_components = get_post_meta($candidate->ID, "componentes", true);
            if (is_array($candidate_components)) {
                foreach ($candidate_components as $comp) {
                    if (absint($comp["seminario_id"] ?? 0) === $seminar_id) {
                        $parent = $candidate;
                        break 2;
                    }
                }
            }
        }
    }

    // Programas academicos que integran este seminario en su plan
    $related_programs = [];
    $offers = get_posts([
        "post_type"      => "oferta-academica",
        "post_status"    => "publish",
        "posts_per_page" => -1,
    ]);
    foreach ($offers as $offer) {
        $offer_seminars = get_post_meta($offer->ID, "seminarios", true);
        if (is_array($offer_seminars)) {
            foreach ($offer_seminars as $item) {
                if (absint($item["seminario_id"] ?? 0) === $seminar_id) {
                    $related_programs[] = [
                        "id"    => (int) $offer->ID,
                        "title" => get_the_title($offer),
                        "slug"  => (string) $offer->post_name,
                        "url"   => get_permalink($offer),
                    ];
                    break;
                }
            }
        }
    }
    $oferta_id = absint($data["oferta_academica_id"] ?? get_post_meta($seminar_id, "oferta_academica_id", true));
    if ($oferta_id > 0) {
        $offer_post = get_post($oferta_id);
        if ($offer_post && $offer_post->post_type === "oferta-academica") {
            $related_programs[] = [
                "id"    => (int) $offer_post->ID,
                "title" => get_the_title($offer_post),
                "slug"  => (string) $offer_post->post_name,
                "url"   => get_permalink($offer_post),
            ];
        }
    }

    $result = [
        "offer_id"       => $seminar_id,
        "instance_id"    => $instance["id"],
        "type"           => "seminario",
        "type_label"     => __("Seminario", "flacso-uruguay"),
        "is_seminar"     => true,
        "is_integrated"  => $is_integrated,
        "title"          => (string) ($data["nombre"] ?? get_the_title($seminar_id)),
        "slug"           => (string) ($data["slug"] ?? $post->post_name),
        "permalink"      => get_permalink($seminar_id),
        "thumbnail_id"   => (int) get_post_thumbnail_id($seminar_id),
        "thumbnail_url"  => (string) ($data["imagen"] ?? get_the_post_thumbnail_url($seminar_id, "full")),
        "academic"       => array_merge($academic, ["oferta_academica_id" => $oferta_id]),
        "instance"       => $instance,
        "registration"   => $registration,
        "program"        => [
            "id" => absint($data["programa_academico_id"] ?? 0),
        ],
        "relations"      => [
            "is_integrated"         => $is_integrated,
            "integrated_parent"     => $parent ? [
                "id"    => (int) $parent->ID,
                "title" => get_the_title($parent),
                "url"   => get_permalink($parent),
            ] : null,
            "integrated_components" => $components_posts,
            "componentes"           => $raw_components,
            "related_programs"      => $related_programs,
        ],
    ];

    unset($resolving[$seminar_id]);
    return $result;
}

/**
 * Catalogo publico de seminarios ordenados por fecha de inicio de su edicion vigente.
 */
function flacso_theme_get_seminars_catalog(array $args = []): array {
    $status = !empty($args["post_status"]) ? $args["post_status"] : "publish";
    $posts = get_posts([
        "post_type"      => "seminario",
        "post_status"    => $status,
        "posts_per_page" => -1,
    ]);

    $catalog = [];
    foreach ($posts as $post) {
        $context = flacso_theme_get_seminar_context((int) $post->ID);
        if (!empty($context)) {
            $catalog[] = $context;
        }
    }

    $hoy = function_exists('current_time') ? current_time('Y-m-d') : date('Y-m-d');

    usort($catalog, static function (array $a, array $b) use ($hoy): int {
        $date_a = (string) ($a["instance"]["fecha_inicio"] ?? "");
        $date_b = (string) ($b["instance"]["fecha_inicio"] ?? "");
        $open_a = !empty($a["registration"]["is_open"]);
        $open_b = !empty($b["registration"]["is_open"]);
        $status_a = (string) ($a["instance"]["estado"] ?? "");
        $status_b = (string) ($b["instance"]["estado"] ?? "");

        // Tiers de prioridad:
        // Tier 1: Próximos a iniciar (fecha_inicio >= hoy) o con inscripciones abiertas
        // Tier 2: En curso actualmente
        // Tier 3: Pasados / Finalizados (fecha_inicio < hoy)
        // Tier 4: Sin fecha definida
        $tier_a = 4;
        if ($date_a !== "") {
            if ($date_a >= $hoy || $open_a) {
                $tier_a = 1;
            } elseif ($status_a === 'en_curso') {
                $tier_a = 2;
            } else {
                $tier_a = 3;
            }
        }

        $tier_b = 4;
        if ($date_b !== "") {
            if ($date_b >= $hoy || $open_b) {
                $tier_b = 1;
            } elseif ($status_b === 'en_curso') {
                $tier_b = 2;
            } else {
                $tier_b = 3;
            }
        }

        if ($tier_a !== $tier_b) {
            return $tier_a <=> $tier_b;
        }

        // Dentro del Tier 1 (Próximos a empezar / abiertos): el más próximo a empezar va primero (ASC)
        if ($tier_a === 1) {
            if ($date_a !== $date_b) {
                if ($date_a === "") return 1;
                if ($date_b === "") return -1;
                return strcmp($date_a, $date_b);
            }
        }

        // Dentro de Tier 2 o 3 (En curso o pasados): los más recientes primero (DESC)
        if ($tier_a === 2 || $tier_a === 3) {
            if ($date_a !== $date_b) {
                return strcmp($date_b, $date_a);
            }
        }

        return strcasecmp((string) ($a["title"] ?? ""), (string) ($b["title"] ?? ""));
    });

    return $catalog;
}

/**
 * Catalogo de seminarios vinculados a una Oferta Academica.
 * Catalogo de seminarios vinculados a una Oferta Academica (desacoplado; ya no se vincula desde la oferta).
 */
function flacso_theme_get_program_seminars_catalog(int $offer_id): array {
    if ($offer_id <= 0) {
        return [];
    }

    $raw_seminars = get_post_meta($offer_id, "seminarios", true);
    if (!is_array($raw_seminars)) {
        return [];
    }

    $catalog = [];
    $seen = [];
    foreach ($raw_seminars as $item) {
        $seminar_id = is_array($item) ? absint($item["seminario_id"] ?? 0) : absint($item);
        if ($seminar_id <= 0 || isset($seen[$seminar_id])) {
            continue;
        }
        $seen[$seminar_id] = true;
        $context = flacso_theme_get_seminar_context($seminar_id);
        if (!empty($context)) {
            $catalog[] = $context;
        }
    }

    return $catalog;
    return [];
}

/**
 * Formatea las fechas o período de una cohorte según su precisión y estado.
 */
function flacso_theme_format_cohort_dates(array $instance): string {
    $precision = (string) ($instance['precision_fecha_inicio'] ?? 'dia');
    $fecha_inicio = (string) ($instance['fecha_inicio'] ?? '');
    $fecha_fin = (string) ($instance['fecha_fin'] ?? '');
    $anio_inicio = absint($instance['anio_inicio'] ?? 0);
    $anio_fin = absint($instance['anio_fin'] ?? 0);

    $y_start = $anio_inicio > 0 ? $anio_inicio : ($fecha_inicio !== '' ? (int) date('Y', strtotime($fecha_inicio)) : 0);
    $y_end = $anio_fin > 0 ? $anio_fin : ($fecha_fin !== '' ? (int) date('Y', strtotime($fecha_fin)) : 0);

    if ($precision === 'anio' || ($fecha_inicio === '' && ($y_start > 0 || $y_end > 0))) {
        if ($y_start > 0 && $y_end > 0 && $y_start !== $y_end) {
            return $y_start . ' – ' . $y_end;
        } elseif ($y_start > 0) {
            return (string) $y_start;
        } elseif ($y_end > 0) {
            return (string) $y_end;
        }
        return '';
    }

    if ($precision === 'mes' && $fecha_inicio !== '') {
        try {
            $dt_start = new DateTime($fecha_inicio, wp_timezone());
            $mes_start = wp_date('F \d\e Y', $dt_start->getTimestamp(), wp_timezone());
            if ($fecha_fin !== '') {
                $dt_end = new DateTime($fecha_fin, wp_timezone());
                $mes_end = wp_date('F \d\e Y', $dt_end->getTimestamp(), wp_timezone());
                return $mes_start . ' – ' . $mes_end;
            }
            return $mes_start;
        } catch (Exception $e) {
            // fallback silencioso
        }
    }

    if ($fecha_inicio !== '') {
        try {
            $dt_start = new DateTime($fecha_inicio, wp_timezone());
            $formatted_start = wp_date('j \d\e F \d\e Y', $dt_start->getTimestamp(), wp_timezone());
            if ($fecha_fin !== '') {
                $dt_end = new DateTime($fecha_fin, wp_timezone());
                $formatted_end = wp_date('j \d\e F \d\e Y', $dt_end->getTimestamp(), wp_timezone());
                return $formatted_start . ' al ' . $formatted_end;
            }
            return $formatted_start;
        } catch (Exception $e) {
            // fallback silencioso
        }
    }

    return '';
}

/**
 * Formatea una fecha en formato legible en español.
 */
function flacso_theme_format_date_es($date, bool $include_year = true): string {
    $timestamp = 0;
    if (is_numeric($date)) {
        $timestamp = (int) $date;
    } else {
        try {
            $dt = new DateTime((string) $date, wp_timezone());
            $timestamp = $dt->getTimestamp();
        } catch (Exception $e) {
            return (string) $date;
        }
    }
    
    if (!$timestamp) {
        return (string) $date;
    }
    
    $format = $include_year ? 'j \d\e F \d\e Y' : 'j \d\e F';
    return wp_date($format, $timestamp, wp_timezone());
}

/**
 * Formatea un monto numerico con separadores de miles y decimales.
 */
function flacso_theme_format_amount($amount): string {
    $amount = (float) $amount;
    return number_format($amount, $amount === floor($amount) ? 0 : 2, ",", ".");
}

/**
 * Renderiza la tarjeta de un docente.
 */
function flacso_theme_render_teacher_card($teacher): void {
    if (!$teacher || empty($teacher->ID)) {
        return;
    }
    $id = (int) $teacher->ID;
    $name = function_exists('dp_nombre_completo') ? dp_nombre_completo($id) : get_the_title($teacher);
    $nombre = trim((string) get_post_meta($id, "nombre", true));
    $apellido = trim((string) get_post_meta($id, "apellido", true));
    $prefijo = trim((string) get_post_meta($id, "prefijo_abrev", true));
    $titulo_acad = trim((string) get_post_meta($id, "titulo_academico", true));
    $cargo = trim((string) get_post_meta($id, "cargo", true));
    $prefix_label = $titulo_acad ? $titulo_acad : ($cargo ?: $prefijo);

    if ($prefijo && stripos($name, $prefijo) === false) {
        $name = $prefijo . ' ' . $name;
    }
    if ($prefix_label !== '' && stripos($name, $prefix_label) !== false) {
        $prefix_label = '';
    }

    $initials = '';
    if ($nombre || $apellido) {
        $initials = mb_substr($nombre, 0, 1) . mb_substr($apellido, 0, 1);
    } elseif ($name) {
        $initials = mb_substr($name, 0, 2);
    }
    $initials = strtoupper($initials ?: 'FL');
    $colors = ['#1d3a72', '#0070ba', '#2c5282', '#1a365d', '#0f2942'];
    $bg_color = $colors[abs(crc32($name ?: (string) $id)) % count($colors)];
    ?>
    <article class="seminario-teacher">
        <div class="seminario-teacher-avatar">
            <?php if (has_post_thumbnail($id)) : ?>
                <?php echo get_the_post_thumbnail($id, "medium", ["alt" => esc_attr($name), "loading" => "lazy"]); ?>
            <?php else : ?>
                <div class="seminario-teacher-placeholder" style="background:<?php echo esc_attr($bg_color); ?>;color:#fff;font-weight:800;">
                    <?php echo esc_html($initials); ?>
                </div>
            <?php endif; ?>
        </div>
        <div>
            <h3 class="seminario-teacher-name"><?php echo esc_html($name); ?></h3>
            <?php if ($prefix_label) : ?>
                <p class="seminario-teacher-prefix"><?php echo esc_html($prefix_label); ?></p>
            <?php endif; ?>
            <?php $cv = get_post_meta($id, "cv", true); ?>
            <?php if ($cv) : ?><div class="seminario-teacher-bio"><?php echo wp_kses_post(wpautop($cv)); ?></div><?php endif; ?>
        </div>
    </article>
    <?php
}

/**
 * Formatea la duración en meses.
 */
function flacso_theme_format_duration_months($months_str): string {
    $raw_value = trim(str_replace(',', '.', (string) $months_str));
    if ($raw_value === '' || !is_numeric($raw_value)) {
        return '';
    }
    $value = (float) $raw_value;
    $integer = (int) floor($value);
    $fraction = round($value - $integer, 2);
    if (abs($fraction - 0.5) < 0.001) {
        if ($integer > 0) {
            return sprintf('%1$d %2$s y medio', $integer, 1 === $integer ? __('mes', 'flacso-uruguay') : __('meses', 'flacso-uruguay'));
        }
        return __('Medio mes', 'flacso-uruguay');
    }
    $display_value = abs($value - round($value)) < 0.001 ? (string) ((int) round($value)) : rtrim(rtrim(number_format($value, 2, '.', ''), '0'), '.');
    return sprintf('%1$s %2$s', $display_value, (abs($value - 1.0) < 0.001) ? __('mes', 'flacso-uruguay') : __('meses', 'flacso-uruguay'));
}

/**
 * Normaliza el HTML de duración.
 */
function flacso_theme_normalize_duration_html($html, $months_str = ''): string {
    $html = trim((string) $html);
    $formatted_months = flacso_theme_format_duration_months($months_str);
    if ($html === '') {
        return $formatted_months !== '' ? '<p>' . esc_html($formatted_months) . '</p>' : '';
    }
    $text = html_entity_decode(wp_strip_all_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $text = trim((string) preg_replace('/\s+/u', ' ', $text));
    if ($text === '') {
        return $formatted_months !== '' ? '<p>' . esc_html($formatted_months) . '</p>' : '';
    }
    if (preg_match('/^(\d+(?:[.,]\d+)?)\s*mes(?:es)?$/iu', $text, $matches) || preg_match('/^(\d+(?:[.,]\d+)?)$/u', $text, $matches)) {
        $formatted_text = flacso_theme_format_duration_months((string) $matches[1]);
        if ($formatted_text !== '') {
            return '<p>' . esc_html($formatted_text) . '</p>';
        }
    }
    return $html;
}


/**
 * Normaliza y capitaliza la modalidad académica (ej: 'virtual' -> 'Virtual').
 */
function flacso_theme_format_modality($modality): string {
    $modality = trim((string) $modality);
    if ($modality === '') {
        return '';
    }
    return mb_strtoupper(mb_substr($modality, 0, 1, 'UTF-8'), 'UTF-8') . mb_substr($modality, 1, null, 'UTF-8');
}
