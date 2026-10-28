<?php
/**
 * Regresión: un único documento académico cuando calendario y malla
 * señalan la misma fuente, sin ocultar PDFs diferentes.
 */
declare(strict_types=1);

define('ABSPATH', dirname(__DIR__) . '/');

// Se prueba la decisión de PRESENTACIÓN, sin cargar ni modificar el modelo.
require_once dirname(__DIR__) . '/inc/academic-document-presentation.php';

function check_shared_document(bool $ok, string $message): void {
    if (!$ok) {
        fwrite(STDERR, "FAIL: {$message}\n");
        exit(1);
    }
}

$drive = 'https://drive.google.com/uc?export=download&id=china-2026';
$other = 'https://drive.google.com/uc?export=download&id=otra-cohorte';
$proxy = static function (string $document, string $label): string {
    return 'https://flacso.edu.uy/wp-admin/admin-ajax.php?action=flacso_view_pdf'
        . '&fn=' . rawurlencode(base64_encode($label))
        . '&src=' . rawurlencode(base64_encode($document));
};

check_shared_document(
    flacso_theme_calendar_and_curriculum_share_document($drive, $drive),
    'dos URLs idénticas deben unificarse'
);
check_shared_document(
    flacso_theme_calendar_and_curriculum_share_document(' ' . $drive . ' ', $drive),
    'espacios de guardado no deben generar documentos duplicados'
);
check_shared_document(
    flacso_theme_calendar_and_curriculum_share_document(
        $proxy($drive, 'Calendario Académico'),
        $proxy($drive, 'Malla Curricular')
    ),
    'dos enlaces del proxy con fn diferentes deben resolverse al mismo PDF'
);
check_shared_document(
    flacso_theme_calendar_and_curriculum_share_document($drive, $proxy($drive, 'Malla Curricular')),
    'una URL original y una del proxy al mismo PDF deben unificarse'
);
check_shared_document(
    flacso_theme_calendar_and_curriculum_share_document(
        'https://drive.google.com/uc?export=download&amp;id=china-2026',
        $drive
    ),
    'los ampersands HTML no deben alterar la identidad del documento'
);
check_shared_document(
    !flacso_theme_calendar_and_curriculum_share_document($drive, $other),
    'dos archivos distintos deben seguir publicados por separado'
);
check_shared_document(
    !flacso_theme_calendar_and_curriculum_share_document('', $drive)
        && !flacso_theme_calendar_and_curriculum_share_document($drive, '')
        && !flacso_theme_calendar_and_curriculum_share_document('', ''),
    'se requieren ambos enlaces no vacíos'
);
check_shared_document(
    !flacso_theme_calendar_and_curriculum_share_document(
        $proxy($drive, 'Calendario Académico'),
        $proxy($other, 'Malla Curricular')
    ),
    'el proxy no debe confundir archivos distintos'
);

$root = dirname(__DIR__);
$page = (string) file_get_contents($root . '/oferta-academica/page.php');
require_once __DIR__ . '/helpers/carta-source.php';
$carta = (string) flacso_carta_template_source($root);
$model = (string) file_get_contents($root . '/inc/academic-public-model.php');
$both = 'Calendario y malla académica';

check_shared_document(
    strpos($page, 'flacso_theme_calendar_and_curriculum_share_document(') !== false
        && strpos($page, 'if ($calendario_y_malla_compartidos)') !== false
        && strpos($page, 'else :') !== false,
    'la oferta debe presentar un único documento solo cuando coinciden'
);
check_shared_document(
    strpos($page, 'Descargar calendario y malla académica (PDF)') !== false
        && strpos($page, 'Descargar malla curricular') !== false
        && strpos($page, 'Descargar Calendario Académico (PDF)') !== false,
    'la oferta conserva los textos y enlaces independientes cuando no coinciden'
);
check_shared_document(
    strpos($page, 'data-flacso-meta-doc-section="CalendarioAcademicoSectionView"') !== false,
    'el documento combinado conserva el marcador del calendario'
);
check_shared_document(
    strpos($carta, 'flacso_theme_calendar_and_curriculum_share_document(') !== false
        && strpos($carta, $both) !== false
        && strpos($carta, "esc_html_e('Calendario académico'") !== false,
    'la carta solo cambia el nombre cuando el archivo está compartido'
);
check_shared_document(
    strpos($carta, 'href="<?php echo esc_url($calendario_url); ?>"') !== false
        && strpos($carta, 'href="<?php echo esc_url($malla_curricular_document); ?>"') !== false
        && strpos($carta, '&& !$calendario_y_malla_compartidos') !== false,
    'la carta presenta un solo botón cuando coinciden y dos botones cuando son diferentes'
);
check_shared_document(
    strpos($model, '"calendario_pdf_url"') !== false
        && strpos($model, '"malla_curricular_url"') !== false
        && strpos($model, 'calendario_y_malla_compartidos') === false,
    'calendario y malla siguen siendo independientes en el backend'
);

echo "OK: calendario y malla académica unidos solo al compartir fuente.\n";
