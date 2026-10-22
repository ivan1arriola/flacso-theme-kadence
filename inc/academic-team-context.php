<?php
/**
 * Ajusta el contexto público de Oferta Académica al nuevo alcance de equipos.
 *
 * - `equipo_academico` vive en la Oferta Académica.
 * - `equipos` vive en la Cohorte vigente.
 * - todos los integrantes siguen siendo entradas del CPT `docente`.
 *
 * Durante la transición se conserva una lectura compatible del meta legacy
 * `equipos` de la Oferta, sin reescribir ni borrar esos datos.
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * @param mixed $value
 * @return array<int,array<string,mixed>>
 */
function flacso_theme_normalize_team_groups($value): array {
    if (!is_array($value)) {
        return [];
    }

    $groups = [];
    foreach ($value as $group) {
        if (!is_array($group)) {
            continue;
        }

        $members = [];
        foreach ((array) ($group['docentes'] ?? []) as $member) {
            $id = is_array($member) ? absint($member['id'] ?? 0) : absint($member);
            if ($id < 1) {
                continue;
            }

            $entry = [
                'id' => $id,
                'rol' => is_array($member) ? sanitize_text_field((string) ($member['rol'] ?? '')) : '',
            ];
            if (is_array($member) && !empty($member['correo'])) {
                $entry['correo'] = sanitize_email((string) $member['correo']);
            }
            $members[] = $entry;
        }

        if (empty($members)) {
            continue;
        }

        $groups[] = [
            'nombre' => sanitize_text_field((string) ($group['nombre'] ?? '')),
            'descripcion' => wp_kses_post((string) ($group['descripcion'] ?? '')),
            'importancia' => in_array((string) ($group['importancia'] ?? '3'), ['1', '2', '3'], true)
                ? (string) $group['importancia']
                : '3',
            'docentes' => $members,
        ];
    }

    return $groups;
}

/**
 * @param array<string,mixed> $context
 * @return array<string,mixed>
 */
function flacso_theme_apply_scoped_teams(array $context, int $offer_id): array {
    if ($offer_id <= 0 || empty($context)) {
        return $context;
    }

    $academic_team = flacso_theme_normalize_team_groups(
        get_post_meta($offer_id, 'equipo_academico', true)
    );

    $cohort_id = absint($context['instance']['id'] ?? $context['instance_id'] ?? 0);
    $cohort_teams = $cohort_id > 0
        ? flacso_theme_normalize_team_groups(get_post_meta($cohort_id, 'equipos', true))
        : [];

    // Compatibilidad: los datos anteriores guardaban todos los grupos en la
    // Oferta. Sólo se usa este fallback cuando el nivel nuevo correspondiente
    // todavía no fue configurado.
    $legacy = flacso_theme_normalize_team_groups(
        $context['academic']['equipos'] ?? get_post_meta($offer_id, 'equipos', true)
    );
    $legacy_academic = [];
    $legacy_cohort = [];

    foreach ($legacy as $group) {
        $normalized_name = remove_accents(mb_strtolower(trim((string) ($group['nombre'] ?? '')), 'UTF-8'));
        $normalized_name = preg_replace('/\s+/u', ' ', $normalized_name);
        if ($normalized_name === 'equipo academico') {
            $legacy_academic[] = $group;
        } else {
            $legacy_cohort[] = $group;
        }
    }

    if (empty($academic_team)) {
        $academic_team = $legacy_academic;
    }
    if (empty($cohort_teams)) {
        $cohort_teams = $legacy_cohort;
    }

    // Distinguir los equipos permanentes de la Oferta de los propios de una Cohorte.
    // El marcador se usa solo al presentar el encabezado; no modifica los metadatos.
    $display_teams = array_values(array_merge(
        array_map(static function (array $group): array {
            $group['alcance'] = 'oferta';
            return $group;
        }, $academic_team),
        array_map(static function (array $group): array {
            $group['alcance'] = 'cohorte';
            return $group;
        }, $cohort_teams)
    ));

    if (!isset($context['academic']) || !is_array($context['academic'])) {
        $context['academic'] = [];
    }
    if (!isset($context['instance']) || !is_array($context['instance'])) {
        $context['instance'] = [];
    }

    $context['academic']['equipo_academico'] = $academic_team;
    // Las plantillas existentes consumen academic.equipos. Mantener ese contrato
    // evita duplicar renderers mientras la fuente real queda correctamente
    // separada entre Oferta y Cohorte.
    $context['academic']['equipos'] = $display_teams;
    $context['instance']['equipos'] = $cohort_teams;

    return $context;
}
