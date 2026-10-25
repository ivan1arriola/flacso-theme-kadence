#!/usr/bin/env bash
set -euo pipefail

theme_dir="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"

while IFS= read -r -d '' php_file; do
    php -l "$php_file" >/dev/null
done < <(find "$theme_dir" -type f -name '*.php' -print0)

php "$theme_dir/tests/academic-public-model-test.php"
php "$theme_dir/tests/academic-team-scope-test.php"
php "$theme_dir/tests/intermediate-titles-carta-test.php"
php "$theme_dir/tests/academic-articulations-public-test.php"
php "$theme_dir/tests/retired-generic-financing-test.php"
php "$theme_dir/tests/no-public-curricular-plans-test.php"
php "$theme_dir/tests/faq-page-test.php"
php "$theme_dir/tests/home-v2-contract-test.php"
php "$theme_dir/tests/archive-four-five-contract-test.php"
php "$theme_dir/tests/page-formacion-color-contract-test.php"
php "$theme_dir/tests/academic-seminar-alignment-test.php"
php "$theme_dir/tests/academic-coordination-hierarchy-test.php"

if grep -rn -P 'target="_blank"(?![^>]*\brel=)' --include='*.php' "$theme_dir"; then
    echo 'Hay enlaces target="_blank" sin rel="noopener noreferrer".' >&2
    exit 1
fi

if grep -rn -E '^<style>|^</style>' --include='*.php' "$theme_dir"; then
    echo 'Hay estilos embebidos en una plantilla; trasladalos a assets/css.' >&2
    exit 1
fi

if grep -rn -E 'trailingslashit\(get_permalink\([^)]*\)\).*preinscripcion'     "$theme_dir/single-oferta-academica.php"     "$theme_dir/single-seminario.php"     "$theme_dir/oferta-academica/page.php"     "$theme_dir/template-parts/oferta/seminario.php"; then
    echo 'El tema no debe construir URLs de preinscripción; debe usar el resolver del plugin.' >&2
    exit 1
fi

if ! grep -q 'data-flacso-preinscription-cta' "$theme_dir/oferta-academica/page.php" ||    ! grep -q 'data-flacso-preinscription-cta' "$theme_dir/template-parts/oferta/seminario.php"; then
    echo 'Los CTA deben conservar el marcador semántico usado por analytics.' >&2
    exit 1
fi

if grep -q -E '(flacso-oa-team-directory|docentes_visibles|docentes_restantes)' \
    "$theme_dir/oferta-academica/page.php" \
    "$theme_dir/oferta-academica/style.css"; then
    echo 'Cada grupo docente debe mostrar a todos sus integrantes con una única jerarquía visual.' >&2
    exit 1
fi

# Verificar que no queden referencias a clases eliminadas del plugin
if grep -rn -E '(Oferta_Renderer|Oferta_Data_Schema|FLACSO_Instancia_Oferta|FLACSO_Relacion_Oferta_Academica|FLACSO_Offer_Instance_Repository|Oferta_Page_Adapter|Oferta_Consulta_Form|FLACSO_Formulario_Preinscripcion_Final)'     --include='*.php'     --exclude-dir='tests'     "$theme_dir"; then
    echo 'Hay referencias a clases legacy eliminadas del plugin.' >&2
    exit 1
fi

# Verificar que no queden metadatos academicos legacy
if grep -rn -E 'get_post_meta\([^)]*(_seminario_|_flacso_relaciones|_flacso_instancia|_flacso_docentes|_oferta_seminarios_ids|proximo_inicio|inscripciones_abiertas)'     --include='*.php'     --exclude-dir='tests'     "$theme_dir"; then
    echo 'Hay referencias a metadatos legacy.' >&2
    exit 1
fi

php "$theme_dir/tests/theme-bootstrap-contract-test.php"
php "$theme_dir/tests/seminarios-document-title-test.php"
echo 'Tema FLACSO: sintaxis, pruebas, enlaces externos, contratos de CTA y ausencia de código legacy correctos.'
