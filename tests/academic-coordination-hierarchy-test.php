<?php
/** Regression: academic coordination leads the team without changing other groups. */
declare(strict_types=1);

$root = dirname(__DIR__);
$template = (string) file_get_contents($root . '/oferta-academica/page.php');
$styles = (string) file_get_contents($root . '/oferta-academica/style.css');

function check_coordination(bool $ok, string $message): void {
    if (!$ok) {
        fwrite(STDERR, "FAIL: {$message}\n");
        exit(1);
    }
}

check_coordination(strpos($template, '$coordination_groups') !== false, 'coordinación separada de otros equipos');
check_coordination(strpos($template, '$academic_team_groups') !== false, 'otros equipos conservan su propio grupo');
check_coordination(strpos($template, '$coordination_people[] = $person;') !== false, 'roles de coordinación reunidos en una fila');
$coord_heading = strpos($template, "'title' => __('Coordinación académica'");
$team_heading = strpos($template, "'title' => __('Equipo académico'");
check_coordination($coord_heading !== false && $team_heading !== false && $coord_heading < $team_heading, 'coordinación se presenta primero');
check_coordination(strpos($styles, '.flacso-academic-team--coordination .flacso-academic-team__grid') !== false, 'tarjetas de coordinación destacadas');
check_coordination(strpos($styles, 'grid-template-columns: repeat(2, minmax(0, 1fr));') !== false, 'dos coordinadores usan dos columnas');
check_coordination(strpos($styles, ':has(> :nth-child(3))') !== false, 'tres coordinadores caben en una fila');

echo "Jerarquía de coordinación y equipo académico verificada.\n";
