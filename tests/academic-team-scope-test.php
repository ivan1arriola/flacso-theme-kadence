<?php

$root = dirname(__DIR__);
$helper_file = $root . '/inc/academic-team-context.php';
$single_file = $root . '/single-oferta-academica.php';
$default_file = $root . '/oferta-academica/page.php';

function theme_team_assert(bool $condition, string $message): void {
    if (!$condition) {
        fwrite(STDERR, "FAIL: {$message}\n");
        exit(1);
    }
}

theme_team_assert(file_exists($helper_file), 'debe existir el adaptador de equipos por alcance');
$helper = (string) file_get_contents($helper_file);
$single = (string) file_get_contents($single_file);
$default = (string) file_get_contents($default_file);

theme_team_assert(strpos($helper, "get_post_meta(\$offer_id, 'equipo_academico'") !== false, 'debe leer Equipo académico desde la Oferta');
theme_team_assert(strpos($helper, "get_post_meta(\$cohort_id, 'equipos'") !== false, 'debe leer los demás equipos desde la Cohorte vigente');
theme_team_assert(strpos($helper, "\$context['academic']['equipos'] = \$display_teams") !== false, 'debe mantener el contrato público de equipos');
theme_team_assert(strpos($helper, "\$context['instance']['equipos'] = \$cohort_teams") !== false, 'debe exponer los equipos de cohorte en instance');
theme_team_assert(strpos($single, 'inc/academic-team-context.php') !== false, 'single oferta debe cargar el adaptador');
theme_team_assert(strpos($single, 'flacso_theme_apply_scoped_teams') !== false, 'single oferta debe aplicar el alcance de equipos');

theme_team_assert(strpos($helper, "\$group['alcance'] = 'oferta'") !== false, 'el equipo general se identifica como parte de la Oferta');
theme_team_assert(strpos($helper, "\$group['alcance'] = 'cohorte'") !== false, 'el equipo de la Cohorte se identifica independientemente');
theme_team_assert(strpos($default, "(\$grupo['alcance'] ?? '') === 'cohorte'") !== false, 'solo los equipos de Cohorte reciben su etiqueta');
theme_team_assert(strpos($default, "\$cohorte_equipo_label = absint(\$instance['id'] ?? 0) > 0") !== false, 'no se atribuye cohorte a equipos si no hay cohorte publicada');
theme_team_assert(strpos($default, "stripos(\$grupo_nombre, \$cohorte_equipo_label)") !== false, 'se evita duplicar cohortes ya escritas en los títulos');
theme_team_assert(strpos($default, "'%1\$s - %2\$s'") !== false, 'el título incluye nombre de grupo y cohorte');
theme_team_assert(strpos($default, 'Estas actividades no ofrecen alternativa de participación virtual.') === false, 'se elimina la segunda oración de la advertencia');
theme_team_assert(strpos($default, 'Incluye instancias exclusivamente presenciales.') !== false, 'se conserva la advertencia breve');

echo "OK academic-team-scope-test\n";
