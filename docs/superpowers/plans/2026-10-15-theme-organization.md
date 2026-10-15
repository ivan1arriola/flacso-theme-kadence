# Organización del tema hijo FLACSO: plan de implementación

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task.

**Goal:** Mejorar la mantenibilidad y documentación del tema hijo sin alterar su comportamiento público ni la jerarquía de plantillas de WordPress.

**Architecture:** `functions.php` queda como cargador ordenado de módulos de presentación en `inc/`; todas las funciones y altas de hooks conservan sus firmas y argumentos. Se actualizan los documentos del proyecto y se crea una guía de contribución alineada con las validaciones existentes.

**Tech Stack:** PHP, WordPress, Bash, Markdown.

**Spec:** `docs/superpowers/specs/2026-10-15-theme-organization-design.md`

## Global Constraints

- No cambiar rutas, nombres de plantillas, hooks, prioridades, lógica de negocio, contenido editorial, dependencias ni comportamiento visual.
- No mover plantillas de la raíz.
- Cargar módulos en orden explícito desde `functions.php` y conservar el mismo contexto de carga.
- Conservar nombres y contratos de funciones, callbacks, hooks, filtros y recursos.
- Documentar únicamente validaciones que realmente ejecuten `bin/check-theme.sh` y `.github/workflows/deploy-theme.yml`.

## Review Focus

- Plantillas incluidas por nombre desde rutas virtuales: verificar que permanezcan en raíz y que `template_include` siga resolviendo `/contactos/`.
- Recursos específicos de `/formacion/`: verificar que la detección de ruta virtual y el enqueue mantengan el CSS/JS actuales.
- Orden de registro de hooks y prioridades: comparar antes/después para `init`, `after_setup_theme`, `wp_enqueue_scripts`, `customize_register`, `template_include` y filtros asociados.
- Cargas en CLI y pruebas sin WordPress: preservar la protección `ABSPATH` y las dependencias explícitas para que las pruebas contractuales sigan funcionando.
- Paquete de producción: verificar que los módulos `inc/` se incluyan y la documentación/pruebas/herramientas permanezcan excluidas por el workflow.

---

### Task 1: Proteger el contrato de carga antes de extraer módulos

**Files:**
- Create: `tests/theme-bootstrap-contract-test.php`
- Modify: `bin/check-theme.sh`

**Interfaces:**
- El test lee `functions.php` y módulos bajo `inc/`; no requiere WordPress ni cambia interfaces productivas.

- [ ] **Step 1: Escribir un test contractual que falle con el estado actual**

El test debe afirmar que `functions.php` contiene la protección ABSPATH, carga de forma explícita todos los módulos de runtime y no conserva lógica de dominio (excepto las declaraciones acordadas que sean necesarias). Añadir al script invocación de este test. La primera corrida debe fallar porque aún no existen los módulos.

- [ ] **Step 2: Confirmar el fallo esperado**

Run: `php tests/theme-bootstrap-contract-test.php`
Expected: FAIL describiendo la ausencia de los módulos de runtime.

- [ ] **Step 3: Implementar los módulos y convertir functions.php en cargador**

Crear `inc/theme-setup.php`, `inc/navigation.php`, `inc/media.php`, `inc/assets.php` y `inc/contact.php`. Mover bloques completos conservando las funciones globales, el orden de registro y el comportamiento. Mantener en `functions.php` el guard `ABSPATH`, las funciones de eslogan solo si el test o una referencia de compatibilidad demuestra que deben quedarse, y `require_once` ordenados. Cualquier dependencia entre módulos debe resolverse mediante orden explícito de carga, sin renombrar símbolos.

- [ ] **Step 4: Ejecutar el contrato y validación PHP**

Run: `php tests/theme-bootstrap-contract-test.php`
Expected: PASS y `OK theme-bootstrap-contract-test`.

Run: `find . -type f -name '*.php' -print0 | xargs -0 -n1 php -l`
Expected: todos los archivos informan `No syntax errors detected`.

- [ ] **Step 5: Commit**

Commit: `refactor: split child theme bootstrap into modules`

### Task 2: Documentar la estructura real y las convenciones de mantenimiento

**Files:**
- Modify: `README.md`
- Modify: `ARCHITECTURE.md`
- Create: `CONTRIBUTING.md`

**Interfaces:**
- Los documentos describen módulos introducidos en Task 1 y comandos existentes.

- [ ] **Step 1: Corregir la estructura del README**

Eliminar `templates/` de la estructura (no existe), enumerar `inc/`, `template-parts/`, `assets/`, `tests/`, `docs/` y `bin/`, y describir carpetas clave sin listar una estructura ficticia. Guardar en UTF-8.

- [ ] **Step 2: Actualizar arquitectura y crear guía de contribución**

Documentar responsabilidad de cada módulo, la frontera tema/plugin, jerarquía de plantillas WordPress, escaping contextual, recursos condicionales y comandos reales de validación/despliegue. La guía debe dejar claro que el script actual ejecuta los tests nombrados en él; no atribuirle cobertura de otros scripts salvo que también se invoquen explícitamente.

- [ ] **Step 3: Revisar consistencia de enlaces y rutas documentadas**

Confirmar que cada ruta descrita exista y que todo comando mencionado esté definido en el repositorio.

- [ ] **Step 4: Commit**

Commit: `docs: clarify child theme structure and contribution rules`

### Task 3: Verificar el cambio de punta a punta

**Files:**
- Modify: `bin/check-theme.sh` (solo si el contrato necesita registrarse aquí; no duplicar entradas)
- Inspect: `.github/workflows/deploy-theme.yml`

**Interfaces:**
- Usa el cargador y módulos de Task 1 y la documentación de Task 2.

- [ ] **Step 1: Ejecutar la validación oficial**

Run: `bash bin/check-theme.sh`
Expected: exit code 0 y mensaje final `Tema FLACSO: sintaxis, pruebas, enlaces externos, contratos de CTA y ausencia de código legacy correctos.`

- [ ] **Step 2: Revisar referencias y diferencias**

Comparar `git diff` contra los contratos de carga y comprobar que ninguna plantilla, URL de recurso, hook, prioridad o exclusión de despliegue haya cambiado. Confirmar que el workflow empaqueta `inc/*.php` y excluye documentación y herramientas según sus patrones actuales.

- [ ] **Step 3: Commit de ajustes de verificación, si hubiera alguno**

Commit: `test: validate modular theme bootstrap`
