<?php
/**
 * Modelo de vista de la carta de presentación de una Oferta Académica.
 *
 * Reúne en un solo lugar las reglas de visibilidad, los valores por defecto y
 * los textos institucionales que usa la carta, a partir del contexto de
 * `flacso_theme_get_offer_context()` y de los ajustes del plugin
 * (`FLACSO_Carta_Settings`). Las plantillas de `template-parts/carta/` solo
 * presentan lo que este modelo les entrega.
 *
 * @package kadence-child-flacso
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Color de presentación de la oferta y variables CSS derivadas.
 *
 * @param array<string,mixed> $context
 * @param array<string,mixed> $academic
 */
function flacso_theme_carta_brand_styles(array $context, array $academic, int $post_id): string {
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

    // Azul institucional FLACSO cuando no hay un color válido.
    $brand_hex = $raw_color !== '' && preg_match('/^#[0-9a-fA-F]{6}$/', $raw_color)
        ? strtolower($raw_color)
        : '#16396f';

    $r = hexdec(substr($brand_hex, 1, 2));
    $g = hexdec(substr($brand_hex, 3, 2));
    $b = hexdec(substr($brand_hex, 5, 2));

    $scale = static function (float $factor) use ($r, $g, $b): string {
        return sprintf(
            '#%02x%02x%02x',
            max(0, min(255, (int) round($r * $factor))),
            max(0, min(255, (int) round($g * $factor))),
            max(0, min(255, (int) round($b * $factor)))
        );
    };
    $lighten = static function (float $amount) use ($r, $g, $b): string {
        return sprintf(
            '#%02x%02x%02x',
            max(0, min(255, (int) round($r + (255 - $r) * $amount))),
            max(0, min(255, (int) round($g + (255 - $g) * $amount))),
            max(0, min(255, (int) round($b + (255 - $b) * $amount)))
        );
    };

    $luminance = (0.299 * $r + 0.587 * $g + 0.114 * $b) / 255;

    return sprintf(
        '--fc-brand: %s; --fc-brand-rgb: %d, %d, %d; --fc-brand-dark: %s; --fc-brand-deep: %s; --fc-brand-light: %s; --fc-brand-contrast: %s;',
        esc_attr($brand_hex),
        $r,
        $g,
        $b,
        esc_attr($scale(0.70)),
        esc_attr($scale(0.38)),
        esc_attr($lighten(0.30)),
        esc_attr($luminance > 0.65 ? '#102b56' : '#ffffff')
    );
}

/**
 * Textos institucionales de la carta, según los ajustes del plugin.
 *
 * Cada texto ya viene resuelto para esta oferta: es vacío cuando su regla de
 * visibilidad (FLACSO_Carta_Settings::applies) no se cumple o cuando quien lo
 * edita lo dejó vacío. Las plantillas solo preguntan `!== ''`.
 *
 * @param array<string,mixed> $academic
 * @return array<string,string>
 */
function flacso_theme_carta_texts(string $anio, array $academic): array {
    $text = static function (string $key) use ($anio, $academic): string {
        return class_exists('FLACSO_Carta_Settings')
            ? FLACSO_Carta_Settings::visible_text($key, $academic, ['{anio}' => $anio])
            : '';
    };

    $telefono = $text('carta_telefono');

    return [
        'badge_mec' => $text('carta_badge_mec'),
        'badge_apostilla' => $text('carta_badge_apostilla'),
        'badge_red' => $text('carta_badge_red'),
        'badge_iin' => $text('carta_badge_iin'),
        'badge_presenciales' => $text('carta_badge_presenciales'),

        'pago_dolares' => $text('carta_pago_dolares'),
        'pago_pesos' => $text('carta_pago_pesos'),
        'vencimiento_cuotas' => $text('carta_vencimiento_cuotas'),
        'titulo_exterior' => $text('carta_titulo_exterior'),
        'titulo_sin_costo' => $text('carta_titulo_sin_costo'),
        'envio_titulos' => $text('carta_envio_titulos'),
        'financiacion_label' => $text('carta_financiacion_label'),

        'mas_info_titulo_seccion' => $text('carta_mas_info_section_title'),
        'mas_info_trayectoria_titulo' => $text('carta_mas_info_trayectoria_title'),
        'mas_info_trayectoria_html' => $text('carta_mas_info_trayectoria_html'),
        'mas_info_gestion_titulo' => $text('carta_mas_info_gestion_title'),
        'mas_info_gestion_html' => $text('carta_mas_info_gestion_html'),

        // El correo se resuelve por oferta, nunca desde un ajuste global.
        'correo_inscripciones' => '',
        'telefono' => $telefono,
        'telefono_href' => $telefono !== '' ? 'tel:' . preg_replace('/[^0-9+]/', '', $telefono) : '',
        'contacto_nombre' => $text('carta_contacto_nombre'),
        'contacto_rol' => $text('carta_contacto_rol'),
    ];
}

/**
 * Contacto académico de la carta.
 *
 * Orden: persona elegida en Coordinación académica (con su rol y correo);
 * si no hay, el primer integrante de un grupo "asistencia" o "coordinación";
 * si tampoco, el correo propio de la oferta. Nunca un correo global de inscripciones.
 *
 * @param array<string,mixed> $academic
 * @param array<string,string> $textos Resultado de flacso_theme_carta_texts().
 * @return array{nombre:string,rol:string,correo:string,foto_url:string}
 */
function flacso_theme_carta_contact(array $academic, array $textos): array {
    $correo_oferta = sanitize_email(trim((string) ($academic['correo'] ?? '')));
    $docente_id = absint($academic['asistente_academica_docente_id'] ?? 0);
    $rol = trim((string) ($academic['asistente_academica_rol'] ?? ''));
    $correo = trim((string) ($academic['asistente_academica_correo'] ?? ''));

    $docente = $docente_id > 0 ? get_post($docente_id) : null;

    if (!$docente && !empty($academic['equipos']) && is_array($academic['equipos'])) {
        foreach ($academic['equipos'] as $equipo) {
            $nombre_equipo = mb_strtolower((string) ($equipo['nombre'] ?? ''));
            if ((strpos($nombre_equipo, 'asistent') !== false || strpos($nombre_equipo, 'coordinaci') !== false) && !empty($equipo['docentes'])) {
                $primero = is_array($equipo['docentes'][0]) ? ($equipo['docentes'][0]['id'] ?? 0) : $equipo['docentes'][0];
                if ($primero) {
                    $docente = get_post(absint($primero));
                    if ($rol === '') {
                        $rol = (string) ($equipo['nombre'] ?? '');
                    }
                    break;
                }
            }
        }
    }

    $nombre = '';
    $foto_url = '';
    if ($docente && $docente->post_type === 'docente') {
        $meta = get_post_meta($docente->ID);
        $prefijo = !empty($meta['prefijo_abrev'][0]) ? trim((string) $meta['prefijo_abrev'][0]) : '';
        $titulo = get_the_title($docente->ID);
        if (function_exists('dp_strip_prefix_from_name')) {
            $titulo = dp_strip_prefix_from_name($titulo, $prefijo);
        }
        $nombre = ($prefijo ? $prefijo . ' ' : '') . $titulo;
        $foto_url = get_the_post_thumbnail_url($docente->ID, 'medium') ?: '';
        if ($correo === '' && !empty($meta['correo'][0])) {
            $correo = trim((string) $meta['correo'][0]);
        }
    } else {
        $nombre = $textos['contacto_nombre'] !== '' ? $textos['contacto_nombre'] : __('Inscripciones FLACSO Uruguay', 'flacso-uruguay');
        $rol = $rol !== '' ? $rol : ($textos['contacto_rol'] !== '' ? $textos['contacto_rol'] : __('Asistencia Académica', 'flacso-uruguay'));
    }

    return [
        'nombre' => $nombre,
        'rol' => $rol !== '' ? $rol : __('Asistente Académica', 'flacso-uruguay'),
        'correo' => $correo !== '' ? sanitize_email($correo) : $correo_oferta,
        'foto_url' => $foto_url,
    ];
}

/**
 * @param array<string,mixed> $context Resultado de flacso_theme_get_offer_context().
 * @return array<string,mixed>
 */
function flacso_theme_get_carta_view(array $context): array {
    $post_id = (int) $context['offer_id'];
    $academic = $context['academic'] ?? [];
    $instance = $context['instance'] ?? [];
    $registration = $context['registration'] ?? [];

    $anio = !empty($instance['anio_inicio'])
        ? (string) $instance['anio_inicio']
        : (!empty($instance['fecha_inicio']) ? substr((string) $instance['fecha_inicio'], 0, 4) : wp_date('Y'));

    $textos = flacso_theme_carta_texts($anio, $academic);
    $contacto = flacso_theme_carta_contact($academic, $textos);
    // El correo del contacto de esta oferta tiene prioridad en todas las secciones.
    $textos['correo_inscripciones'] = $contacto['correo'];

    // Inscripción: solo con cohorte abierta y enlace publicado.
    $url_inscripcion = (string) ($registration['url'] ?? '');
    if ($url_inscripcion === '') {
        $url_inscripcion = 'https://preinscripciones.flacso.edu.uy/';
    }
    $inscripciones_abiertas = !empty($registration['is_open']) && !empty($registration['url']);

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

    $duracion_meses = $academic['duracion_meses'] ?? '';
    $duracion = $duracion_meses !== '' && function_exists('flacso_theme_format_duration_months')
        ? flacso_theme_format_duration_months($duracion_meses)
        : __('A definir', 'flacso-uruguay');

    // La existencia de actividades solo presenciales no determina la modalidad
    // general (puede ser virtual, presencial o semipresencial).
    $modalidad_cruda = trim((string) ($instance['modalidad'] ?? ''));
    if ($modalidad_cruda === '') {
        $modalidad_cruda = trim((string) ($instance['modalidad_descripcion'] ?? ''));
    }
    $modalidad_texto = trim(wp_strip_all_tags($modalidad_cruda));
    $modalidad = $modalidad_texto === '' || mb_strlen($modalidad_texto) > 50
        ? __('Modalidad a confirmar', 'flacso-uruguay')
        : $modalidad_texto;

    if (!empty($instance['modalidad_descripcion']) && empty($academic['modalidad_html'])) {
        $academic['modalidad_html'] = $instance['modalidad_descripcion'];
    }

    // Tabla de precios: sin tabla, el dólar se muestra.
    $tabla_precio = $instance['tabla_precio'] ?? null;
    $precios_filas = [];
    $precios_nota = '';
    $mostrar_precios_dolares = true;
    $precios_columnas = [];
    if (!empty($tabla_precio) && is_array($tabla_precio)) {
        $precios_filas = is_array($tabla_precio['filas'] ?? null) ? $tabla_precio['filas'] : [];
        $precios_nota = (string) ($tabla_precio['nota'] ?? '');
        $mostrar_precios_dolares = !empty($tabla_precio['mostrar_dolares']);
        $precios_columnas = is_array($tabla_precio['columnas'] ?? null) ? $tabla_precio['columnas'] : [];
    }

    // Bloques de contenido: el índice y la portada dependen de que exista alguno.
    $hay_bloques_detallados = false;
    foreach ([
        'descripcion_html', 'modalidad_html', 'perfil_ingreso_html', 'requisitos_ingreso_html',
        'perfil_egreso_html', 'requisitos_egreso_html', 'titulos_certificaciones_html',
    ] as $clave) {
        if (!empty($academic[$clave])) {
            $hay_bloques_detallados = true;
            break;
        }
    }

    $detalles_academicos = [
        'perfil_ingreso_html' => __('Perfil de Ingreso', 'flacso-uruguay'),
        'requisitos_ingreso_html' => __('Requisitos de Ingreso', 'flacso-uruguay'),
        'perfil_egreso_html' => __('Perfil del Egresado', 'flacso-uruguay'),
        'requisitos_egreso_html' => __('Requisitos de egreso', 'flacso-uruguay'),
        'titulos_certificaciones_html' => __('Títulos y Certificaciones', 'flacso-uruguay'),
    ];
    $hay_detalles_academicos = false;
    foreach ($detalles_academicos as $clave => $etiqueta) {
        if (!empty($academic[$clave])) {
            $hay_detalles_academicos = true;
            break;
        }
    }

    $descripcion_html = (string) ($academic['descripcion_html'] ?? '');

    // Ambos documentos conservan su origen; la coincidencia solo modifica el HTML público.
    $calendario_url = trim((string) ($academic['calendario_pdf_url'] ?? ''));
    $malla_curricular_document = trim((string) ($academic['malla_curricular_url'] ?? ''));

    return [
        'post_id' => $post_id,
        'titulo' => (string) $context['title'],
        'tipo_oferta' => (string) $context['type_label'],
        'thumbnail_url' => (string) ($context['thumbnail_url'] ?? ''),
        'academic' => $academic,
        'inline_brand_styles' => flacso_theme_carta_brand_styles($context, $academic, $post_id),

        'abreviacion' => trim((string) ($academic['abreviacion'] ?? '')),
        'cohorte_label' => trim((string) ($instance['label'] ?? '')),
        'anio' => $anio,
        'proximo_inicio' => $proximo_inicio,
        'duracion' => $duracion,
        'modalidad' => $modalidad,


        'url_inscripcion' => $url_inscripcion,
        'inscripciones_abiertas' => $inscripciones_abiertas,

        'arr_menciones' => array_values(array_filter((array) ($academic['menciones'] ?? []))),
        'arr_orientaciones' => array_values(array_filter((array) ($academic['orientaciones'] ?? []))),
        'arr_titulos_intermedios' => array_values(array_filter((array) ($academic['titulos_intermedios'] ?? []))),

        'hay_bloques_detallados' => $hay_bloques_detallados,
        'detalles_academicos' => $detalles_academicos,
        'hay_detalles_academicos' => $hay_detalles_academicos,
        'mensaje_bienvenida' => (string) get_option('flacso_mensaje_bienvenida', ''),
        // «Información extra para Carta» (promociones, becas): la carga cada cohorte y se muestra
        // la de la cohorte vigente, justo después de la sección 01.
        'info_extra' => trim((string) ($instance['carta_info_extra_html'] ?? '')),
        'fc_hero_summary' => !empty($academic['descripcion_html'])
            ? wp_trim_words(wp_strip_all_tags((string) $academic['descripcion_html']), 24, '…')
            : '',
        'fc_audience_summary' => !empty($academic['perfil_ingreso_html'])
            ? trim(wp_strip_all_tags((string) $academic['perfil_ingreso_html']))
            : '',
        // El diseño a dos columnas solo se aplica a descripciones extensas; la
        // lectura de propuestas breves permanece lineal y sin columnas vacías.
        'fc_long_description' => mb_strlen(trim(wp_strip_all_tags($descripcion_html))) >= 800
            && !preg_match('/<(?:table|img|iframe|video|figure|pre|ul|ol|h[1-6])\b/i', $descripcion_html),

        'calendario_url' => $calendario_url,
        'malla_curricular_document' => $malla_curricular_document,
        'calendario_y_malla_compartidos' => flacso_theme_calendar_and_curriculum_share_document(
            $calendario_url,
            $malla_curricular_document
        ),
        'precios_filas' => $precios_filas,
        'precios_nota' => $precios_nota,
        'mostrar_precios_dolares' => $mostrar_precios_dolares,
        'precios_columnas' => $precios_columnas,

        'hay_distintivos' => $textos['badge_mec'] !== '' || $textos['badge_apostilla'] !== '' || $textos['badge_red'] !== '',
        'textos' => $textos,
        'contacto' => $contacto,
        'carta_cta_titulo' => __('Comenzá el año cursando un posgrado en FLACSO Uruguay', 'flacso-uruguay'),
    ];
}
