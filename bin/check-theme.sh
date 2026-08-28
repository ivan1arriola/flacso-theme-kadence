#!/usr/bin/env bash
set -euo pipefail

theme_dir="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"

while IFS= read -r -d '' php_file; do
    php -l "$php_file" >/dev/null
done < <(find "$theme_dir" -type f -name '*.php' -print0)

if rg -n --pcre2 'target="_blank"(?![^>]*\brel=)' "$theme_dir" -g '*.php'; then
    echo 'Hay enlaces target="_blank" sin rel="noopener noreferrer".' >&2
    exit 1
fi

if rg -n '^<style>|^</style>' "$theme_dir" -g '*.php' -g '!**/carta-template.php'; then
    echo 'Hay estilos embebidos en una plantilla; trasladalos a assets/css.' >&2
    exit 1
fi

if rg -n 'trailingslashit\(get_permalink\([^)]*\)\).*preinscripcion' \
    "$theme_dir/single-oferta-academica.php" \
    "$theme_dir/templates/preinscripciones/carta-template.php"; then
    echo 'El tema no debe construir URLs de preinscripción; debe usar el resolver del plugin.' >&2
    exit 1
fi

if ! rg -q 'data-flacso-preinscription-cta' "$theme_dir/single-oferta-academica.php" "$theme_dir/single-seminario.php"; then
    echo 'Los CTA deben conservar el marcador semántico usado por analytics.' >&2
    exit 1
fi

echo 'Tema FLACSO: sintaxis, enlaces externos y separación de CSS correctos.'
