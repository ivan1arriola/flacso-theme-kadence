<?php
/**
 * El sitio público usa la malla curricular de la Oferta y el calendario
 * académico de la Cohorte como fuentes independientes. Las vistas del tema
 * pueden agrupar sus enlaces si apuntan al mismo documento.
 */
$root = dirname(__DIR__);
$model = file_get_contents($root . '/inc/academic-public-model.php');
$page = file_get_contents($root . '/oferta-academica/page.php');
require_once __DIR__ . '/helpers/carta-source.php';
$carta = flacso_carta_template_source($root);
function assert_public_docs(bool $ok, string $msg): void {
    if (!$ok) { fwrite(STDERR, "FAIL: " . $msg . "\n"); exit(1); }
}
assert_public_docs(strpos($page, "malla_doc_url") !== false, "La oferta publica malla curricular cuando está configurada");
assert_public_docs(strpos($page, "Malla Curricular") === false, "La oferta no promociona malla curricular");
assert_public_docs(strpos($page, "plan de estudios") === false, "La oferta no promete planes curriculares públicos");
assert_public_docs(strpos($page, "cartamalla_pdf_url") === false, "La oferta no muestra documentos combinados");
assert_public_docs(strpos($carta, "cartamalla_url") === false, "No se introduce un documento nuevo en el backend");
assert_public_docs(strpos($carta, 'malla_curricular_document') !== false, "La carta muestra la malla pública de la Oferta cuando corresponde");
assert_public_docs(strpos($carta, "Calendario académico") !== false, "La carta conserva el calendario");
assert_public_docs(strpos($page, "calendario_doc_url") !== false, "La web conserva el calendario");
assert_public_docs(strpos($model, '"malla_curricular_url"') !== false, "El modelo expone el enlace curricular");
assert_public_docs(strpos($model, '"cartamalla_pdf_url"') === false, "El modelo no expone el documento combinado");
assert_public_docs(strpos($model, '"calendario_pdf_url"') !== false, "El modelo conserva calendario de cohorte");
echo "OK: fuentes independientes, presentación combinada únicamente si coinciden\n";
