<?php

$root = dirname(__DIR__);
$helper_file = $root . '/inc/academic-team-context.php';
$single_file = $root . '/single-oferta-academica.php';

function theme_team_assert(bool $condition, string $message): void {
    if (!$condition) {
        fwrite(STDERR, "FAIL: {$message}\n");
        exit(1);
    }
}

theme_team_assert(file_exists($helper_file), 'debe existir el adaptador de equipos por alcance');
$helper = (string) file_get_contents($helper_file);
$single = (string) file_get_contents($single_file);

theme_team_assert(strpos($helper, "get_post_meta(\$offer_id, 'equipo_academico'") !== false, 'debe leer Equipo académico desde la Oferta');
theme_team_assert(strpos($helper, "get_post_meta(\$cohort_id, 'equipos'") !== false, 'debe leer los demás equipos desde la Cohorte vigente');
theme_team_assert(strpos($helper, "\$context['academic']['equipos'] = \$display_teams") !== false, 'debe mantener el contrato público de equipos');
theme_team_assert(strpos($helper, "\$context['instance']['equipos'] = \$cohort_teams") !== false, 'debe exponer los equipos de cohorte en instance');
theme_team_assert(strpos($single, 'inc/academic-team-context.php') !== false, 'single oferta debe cargar el adaptador');
theme_team_assert(strpos($single, 'flacso_theme_apply_scoped_teams') !== false, 'single oferta debe aplicar el alcance de equipos');

echo "OK academic-team-scope-test\n";
