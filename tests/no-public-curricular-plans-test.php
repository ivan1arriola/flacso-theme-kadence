<?php
/**
 * El sitio público ofrece la malla curricular de la Oferta cuando existe,
 * y el calendario académico individual de la cohorte.
 */
$root = dirname(__DIR__);
$model = file_get_contents($root . '/inc/academic-public-model.php');
$page = file_get_contents($root . '/template-parts/oferta/default.php');
$carta = file_get_contents($root . '/template-parts/oferta/carta.php');
function assert_public_docs(bool $ok, string $msg): void {
    if (!$ok) { fwrite(STDERR, "FAIL: " . $msg . "\n"); exit(1); }
}
assert_public_docs(strpos($page, "malla_doc_url") !== false, "La oferta publica malla curricular cuando está configurada");
assert_public_docs(strpos($page, "Malla Curricular") === false, "La oferta no promociona malla curricular");
assert_public_docs(strpos($page, "plan de estudios") === false, "La oferta no promete planes curriculares públicos");
assert_public_docs(strpos($page, "cartamalla_pdf_url") === false, "La oferta no muestra documentos combinados");
assert_public_docs(strpos($carta, "cartamalla_url") === false, "La carta no ofrece documentos combinados");
assert_public_docs(strpos($carta, "malla_url") === false, "La carta no ofrece mallas curriculares");
assert_public_docs(strpos($carta, "Calendario académico") !== false, "La carta conserva el calendario");
assert_public_docs(strpos($page, "calendario_doc_url") !== false, "La web conserva el calendario");
assert_public_docs(strpos($model, '"malla_curricular_url"') !== false, "El modelo expone el enlace curricular");
assert_public_docs(strpos($model, '"cartamalla_pdf_url"') === false, "El modelo no expone el documento combinado");
assert_public_docs(strpos($model, '"calendario_pdf_url"') !== false, "El modelo conserva calendario de cohorte");
echo "OK: calendario de cohorte y malla curricular de la Oferta\n";
