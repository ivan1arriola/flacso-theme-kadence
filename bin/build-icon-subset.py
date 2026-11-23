#!/usr/bin/env python3
"""Genera el subconjunto de Bootstrap Icons con solo los iconos usados.

Uso:  python bin/build-icon-subset.py [--check]
Requiere: pip install fonttools brotli

1. Recorre el tema y, si existe ../flacso-uruguay-plugin, el plugin (php/js/css/html,
   sin vendor, node_modules, .worktrees, dev, tests ni *.min.*) y junta cada `bi-nombre`.
2. Toma los codepoints de assets/vendor/bootstrap-icons/font/bootstrap-icons.min.css (fuente completa).
3. Escribe bootstrap-icons-subset.min.css y fonts/bootstrap-icons-subset.woff2.
Falla (exit 1) si un `bi-*` usado no existe en la fuente completa o si hay nombres armados
por concatenacion (`bi-` suelto): agregalos a EXTRA_ICONS o a IGNORE abajo tras revisarlos.
Los iconos guardados en contenido de WordPress (menus, bloques) no se ven desde el codigo:
agregalos a EXTRA_ICONS.
"""
import os, re, subprocess, sys

EXTRA_ICONS = set()   # iconos usados solo desde contenido de WordPress
IGNORE = set()        # coincidencias `bi-*` que no son iconos
ROOT = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
FONT_DIR = os.path.join(ROOT, 'assets/vendor/bootstrap-icons/font')
SKIP = {'vendor', 'node_modules', '.worktrees', 'dev', 'tests', '.git'}

def scan(base):
    used, dynamic = {}, {}
    for d, dirs, files in os.walk(base):
        dirs[:] = [x for x in dirs if x not in SKIP]
        for f in files:
            if not re.search(r'\.(php|js|css|html)$', f) or '.min.' in f or '-subset' in f:
                continue
            p = os.path.join(d, f)
            txt = open(p, encoding='utf-8', errors='replace').read()
            for m in re.finditer(r'(?<![\w-])bi-([a-z0-9]+(?:-[a-z0-9]+)*)?(-?)', txt):
                name, dash = m.group(1), m.group(2)
                if not name or dash:
                    dynamic.setdefault(p, set()).add(m.group(0))
                else:
                    used.setdefault(name, set()).add(os.path.relpath(p, base))
    return used, dynamic

def main():
    full = open(os.path.join(FONT_DIR, 'bootstrap-icons.min.css'), encoding='utf-8').read()
    codes = dict(re.findall(r'\.bi-([a-z0-9-]+)::before\{content:"\\([0-9a-f]+)"\}', full))
    bases = [ROOT]
    plugin = os.path.join(os.path.dirname(ROOT), 'flacso-uruguay-plugin')
    if os.path.isdir(plugin):
        bases.append(plugin)
    used, dynamic = {}, {}
    for b in bases:
        u, dy = scan(b)
        for k, v in u.items():
            used.setdefault(k, set()).update(v)
        dynamic.update(dy)
    names = (set(used) | EXTRA_ICONS) - IGNORE
    missing = sorted(n for n in names if n not in codes)
    dyn = {p: s for p, s in dynamic.items() if s - IGNORE}
    print('iconos usados: %d | sin resolver (dinamicos): %d archivos' % (len(names), len(dyn)))
    for p, s in dyn.items():
        print('  dinamico:', p, sorted(s))
    if missing:
        print('FALTAN en la fuente completa:', missing, file=sys.stderr)
        sys.exit(1)
    if '--check' in sys.argv:
        return
    uni = ','.join('U+' + codes[n] for n in sorted(names))
    out = os.path.join(FONT_DIR, 'fonts/bootstrap-icons-subset.woff2')
    subprocess.check_call([sys.executable, '-m', 'fontTools.subset',
        os.path.join(FONT_DIR, 'fonts/bootstrap-icons.woff2'), '--unicodes=' + uni,
        '--flavor=woff2', '--layout-features=', '--no-hinting', '--output-file=' + out])
    head = full[:full.index('}', full.index('.bi::before')) + 1]
    head = re.sub(r'url\("fonts/bootstrap-icons\.woff2[^"]*"\) format\("woff2"\),url\("fonts/bootstrap-icons\.woff[^"]*"\) format\("woff"\)',
                  'url("fonts/bootstrap-icons-subset.woff2") format("woff2")', head)
    rules = ''.join('.bi-%s::before{content:"\\%s"}' % (n, codes[n]) for n in sorted(names))
    with open(os.path.join(FONT_DIR, 'bootstrap-icons-subset.min.css'), 'w', encoding='utf-8', newline='') as fh:
        fh.write(head + rules + '\n')
    print('OK', len(names), 'iconos ->', out)

main()
