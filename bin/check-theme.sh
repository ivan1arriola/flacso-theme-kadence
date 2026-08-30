#!/usr/bin/env bash
set -euo pipefail

theme_dir="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"

while IFS= read -r -d '' php_file; do
    php -l "$php_file" >/dev/null
done < <(find "$theme_dir" -type f -name '*.php' -print0)

php "$theme_dir/tests/academic-public-model-test.php"

if grep -rn -P 'target="_blank"(?![^>]*\brel=)' --include='*.php' "$theme_dir"; then
    echo 'Hay enlaces target="_blank" sin rel="noopener noreferrer".' >&2
    exit 1
fi

if grep -rn -E '^<style>|^</style>' --include='*.php' --exclude='carta-template.php' "$theme_dir"; then
    echo 'Hay estilos embebidos en una plantilla; trasladalos a assets/css.' >&2
    exit 1
fi

if grep -rn -E 'trailingslashit\(get_permalink\([^)]*\)\).*preinscripcion' \
    "$theme_dir/single-oferta-academica.php" \
    "$theme_dir/single-seminario.php" \
    "$theme_dir/template-parts/oferta/default.php" \
    "$theme_dir/template-parts/oferta/seminario.php" \
    "$theme_dir/templates/preinscripciones/carta-template.php"; then
    echo 'El tema no debe construir URLs de preinscripción; debe usar el resolver del plugin.' >&2
    exit 1
fi

if ! grep -q 'data-flacso-preinscription-cta' "$theme_dir/template-parts/oferta/default.php" || \
   ! grep -q 'data-flacso-preinscription-cta' "$theme_dir/template-parts/oferta/seminario.php"; then
    echo 'Los CTA deben conservar el marcador semántico usado por analytics.' >&2
    exit 1
fi

# El acceso a metadatos legacy de seminarios e instancias se encapsula en el adaptador.
if grep -rn -E "get_post_meta\\([^,]+,\\s*['\"](_seminario_periodo_inicio|_seminario_periodo_fin|_seminario_docentes|_seminario_modalidad|_oferta_seminarios_ids|_flacso_fecha_inicio|_flacso_fecha_fin|_flacso_modalidad|_flacso_docentes)['\"]" \
    --include='*.php' \
    --exclude-dir='inc' \
    --exclude-dir='tests' \
    "$theme_dir"; then
    echo 'Los templates deben consumir inc/academic-public-model.php y no metadatos académicos directos.' >&2
    exit 1
fi

echo 'Tema FLACSO: sintaxis, enlaces externos, contratos de CTA y separación de CSS correctos.'
