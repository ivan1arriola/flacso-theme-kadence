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

echo 'Tema FLACSO: sintaxis, enlaces externos y separación de CSS correctos.'
