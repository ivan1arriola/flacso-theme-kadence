# Portada pública FLACSO Uruguay — plan histórico de implementación

Este plan responde al diseño del 28/09/2026. El diseño visual vigente se describe en [la especificación basada en la maqueta](../superpowers/specs/2026-10-15-homepage-from-mockup-design.md); actualizar este plan antes de implementarlo.

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Sustituir gradualmente la home actual por una portada editorial, con campañas programables desde WordPress y datos académicos derivados, sin romper otras rutas.

**Architecture:** El plugin aporta CPT, vigencia y un DTO estable de portada; el tema renderiza HTML/CSS. Un flag selecciona v2 o el builder existente hasta que haya validación y rollback comprobado. No hay REST/React nuevos para la home.

**Tech Stack:** WordPress/PHP, Kadence child theme, CSS mobile-first, JavaScript nativo solo para navegación, pruebas de contrato PHP existentes y pruebas WordPress/staging.

**Spec:** [diseño del tema](2026-home-redesign.md) y [dominio del plugin](../../../flacso-uruguay-plugin/docs/toDo/2026-home-campaigns.md). Leer ambos completos antes de ejecutar.

## Global Constraints

- No reescribir el modelo académico, preinscripciones, newsletter, noticias ni eventos.
- La interfaz pública dice «Preinscripciones abiertas», no «Inscripciones» como etapa administrativa final.
- No crear ruta Doctorados sin confirmar oferta/categoría/URL pública; conservar Diplomas y Diplomados separados mientras sean categorías reales.
- Solo el tema define el markup/CSS público de v2; el plugin no devuelve HTML visual, salvo la integración existente del formulario de mailing.
- Sitio en producción: cambios escalonados, respaldo de opciones/BD antes de mutaciones productivas, switch/rollback y verificación real antes de retirar legacy.
- No fusionar ramas ni desplegar automáticamente por seguir este plan; cada publicación requiere el flujo de revisión y autorización vigente.
- No borrar shortcodes, bloques ni CSS/JS legacy hasta demostrar ausencia de consumidores fuera de home.
- Vigencia de campañas como instantes UTC: inicio inclusivo, fin exclusivo tras el último minuto introducido en Admin; no usar `post_date` para programarlas.
- El bloque «Preinscripciones abiertas» exige instancia y propuesta publicadas, apertura real y enlace HTTPS accionable; no muestra drafts ni ediciones ocultas.
- El mismo modo efectivo (`v2|legacy|emergency`) decide HTML y assets. Una caída a legacy conserva su CSS; el fallback de emergencia solo se usa si falta el plugin completo.
- La caché pública debe reflejar inicio, vencimiento y rollback en un máximo de 60 segundos; sin esa garantía no se activa la programación automática.

## Review Focus

1. Borde temporal y solapamientos: el minuto `23:59` se muestra completo y vence al primer instante posterior, incluso con cambio de horario; «Próximo cambio» identifica al ganador efectivo, incluida la reaparición de otra campaña o el fallback. Tests en tareas 2–3.
2. Catálogo público: una sesión editorial, más de 200 propuestas, instancia oculta `'0'` o apertura sin enlace no publican datos ni CTA engañosos. Tests en tarea 4.
3. Dos versiones durante despliegue: el mismo modo selecciona renderer y assets, y el rollback nunca muestra hero fechado ni página sin estilo. Tests en tareas 8–10.
4. Edición: una asistente puede crear/publicar y previsualizar un borrador sin filtrarlo al público, pero no gana acceso a ajustes globales ni borrado. Tests en tareas 2–3.
5. CDN: el HTML anónimo refleja inicio, vencimiento y rollback en ≤60 segundos sin depender de una visita que dispare WP-Cron. Prueba en tarea 10.

## Mapa de responsabilidades nuevas

Plugin: `modules/main-page/includes/class-flacso-home-campaign.php` (CPT/meta/capacidades), `class-flacso-home-campaign-resolver.php` (vigencia/DTO), `class-flacso-home-campaign-admin.php` (pantalla/guardado), `class-flacso-home-data.php` (colecciones de portada). El nombre de cada clase se confirma en la primera tarea contra convenciones del módulo y se mantiene consistente. Tema: `template-parts/home/*.php` (secciones), `assets/css/home/home.css` (tokens/layout/componentes), `front-page.php` (switch), `functions.php` (assets/compatibilidad). Pruebas en `tests/home-*.php` de ambos repositorios, siguiendo los tests PHP de contrato ya presentes; para integración WordPress se usará el harness disponible en staging, no mocks como única evidencia.

## Secuencia ejecutable

### Tarea 1. Baseline y decisiones editoriales

**Archivos:** documentación de ambas specs; sin PHP/CSS.

- [ ] Registrar SHA/branch/status de ambos repos y fuentes reales de menús, noticias, evento, mailing, hero, CSS y shortcodes; no tocar trabajo ajeno.
- [ ] Confirmar con contenido/Administración la URL del índice de preinscripciones abiertas, CTA header, categorías de formación y si Doctorados tiene destino publicable. Registrar respuesta en ambas specs; si no se confirma, usar destinos existentes sin Doctorados.
- [ ] Capturar baseline desktop/móvil, HTML semántico, LCP/CLS, CSS/JS transferidos y enlaces clave en staging/live de solo lectura; guardar resultados para comparar en tarea 10.
- [ ] Revisar versión WordPress, soporte de revisiones de meta, capacidades reales de las asistentes y `FLACSO_Editor_Admin_Mode`; registrar cómo se restaurarán vigencia y CTA, además de título.
- [ ] Medir caché de página/CDN y confirmar si ofrece TTL efectivo ≤60 segundos o purga programada independiente del tráfico; registrar el mecanismo y el procedimiento de rollback. Si no existe, detener la activación automática prevista en tarea 10.
- [ ] Commit solo de estas decisiones/baseline en una rama documental revisable, sin merge ni deploy.

### Tarea 2. CPT y validación de campañas, aún sin mostrar en home

**Plugin:** crear `modules/main-page/includes/class-flacso-home-campaign.php`; modificar `modules/main-page/init.php`; crear `tests/home-campaign-model-test.php`.

**Produce:** CPT `flacso_home_campaign`; metadatos prefijados y capacidades de la spec, sin URL pública; validación al guardar. **Consume:** esquema exacto de [campañas](../../../flacso-uruguay-plugin/docs/toDo/2026-home-campaigns.md).

- [ ] Escribir tests: CPT no indexable; `post_status=future` excluido; título e inicio obligatorios; entrada local convertida a enteros UTC; fin `23:59` almacenado como 60 segundos después del instante `23:59:00`, incluso en transición DST; fin exclusivo ≤ inicio rechazado; horas DST inexistentes/ambiguas rechazadas; cambio posterior de zona horaria no desplaza los instantes; URL insegura, CTA incompleto, adjunto que no es imagen, variante y prioridad inválidas rechazados o normalizados conforme al spec.
- [ ] Escribir tests de capacidades `create_posts`, editar propio/ajeno/publicado, publicar, borrar y `upload_files` por rol; probar que restaurar una revisión repone vigencia, imagen y CTA si la versión WordPress admite revisión de meta.
- [ ] Ejecutar `php tests/home-campaign-model-test.php` y verificar fallos por API ausente.
- [ ] Registrar CPT con `map_meta_cap=true`, soporte de autor/excerpt/revisiones y mapa explícito de primitivas; registrar `_flacso_home_start_at_utc` y `_flacso_home_end_at_exclusive_utc` como enteros y las demás meta con autorización y revisión cuando proceda. Convertir el formulario local con `wp_timezone()` y rechazar horas DST problemáticas; conservar `post_date` como fecha editorial presente, sin tocar opción hero legacy.
- [ ] Repetir test y `php -l` de archivos nuevos/modificados; comprobar que URLs públicas y admin actual no cambian.
- [ ] Commit de plugin acotado; no publicar aún.

### Tarea 3. Resolución y panel editorial

**Plugin:** crear `class-flacso-home-campaign-resolver.php`, `class-flacso-home-campaign-admin.php`, `tests/home-campaign-resolver-test.php`, `tests/home-campaign-admin-test.php`; modificar `class-flacso-main-page-admin.php`, `class-flacso-main-page-unified-settings.php` y, si corresponde, `includes/core/class-flacso-editor-admin-mode.php`.

**Produce:** `flacso_get_active_home_campaign(?DateTimeImmutable $at = null): array`, `flacso_get_home_campaign_schedule(?DateTimeImmutable $at = null): array` y FLACSO → Portada con estado actual/próximo/programado/anterior. **Consume:** CPT de tarea 2.

- [ ] Escribir tests con reloj inyectado para segundo anterior/exacto al inicio, segundo anterior/exacto al fin exclusivo, fin abierto, prioridad, inicio e ID, metadatos corruptos, borrador/`future`/papelera y fallback. El DTO usa `start_at_utc`/`end_at_exclusive_utc` ISO 8601 o `null`.
- [ ] Escribir tests de agenda: futura de menor prioridad que nunca gana, campaña anterior que reaparece cuando vence la dominante, salto a fallback y `next=null` cuando no hay próximo cambio. `scheduled` puede incluir campañas futuras sin victoria.
- [ ] Escribir tests de capability + nonce para guardar/ver, acceso directo bloqueado, asistentes habilitadas en Editor Mode y ninguna exposición de ajustes globales. Probar preview autenticada de borrador/programada con cabecera `no-store`, sin cambiar el ganador público ni filtrarse en caché compartida.
- [ ] Ejecutar tests nuevos y confirmar fallos por funcionalidad ausente.
- [ ] Implementar consulta determinista y fallback, sin caché persistente; calcular `next` recorriendo todos los inicios/finales futuros y comparando el ganador visible antes/después de cada límite. Crear dashboard «Próximo cambio visible» y editor en cinco grupos con vista previa separada de «Ver portada». Mantener opciones/pestañas legacy detrás de acceso administrativo hasta migración.
- [ ] Ejecutar tests, `php -l`, revisión manual de admin con roles reales y de intervalos solapados; commit de plugin acotado.

### Tarea 4. Adaptador de preinscripciones abiertas

**Plugin:** crear `modules/main-page/includes/class-flacso-home-data.php` y, solo si es necesario, modificar `modules/oferta-academica/includes/class-academic-catalog.php`/`class-academic-repositories.php`; crear `tests/home-open-preinscriptions-test.php`.

**Produce:** `flacso_get_home_open_preinscriptions(int $limit = 3): array` con `kind`, `title`, `type`, `instance`, `modality`, `start_date`, `start_precision`, `information_url`, `preinscription_url` por ítem. **Consume:** apertura de `FLACSO_Cohorte`/`FLACSO_Edicion`, DTO/repositorio académico existente y estados publicados; no recorre `registration_catalog()` completo en cada visita.

- [ ] Escribir tests de cohorte/edición abierta y cerrada, propuesta o instancia borrador con usuario editor, >200 propuestas, edición con `mostrar_en_formulario='0'`, apertura sin enlace/URL insegura, página informativa privada, múltiples instancias, fechas de precisión mes/año y orden estable. Afirmar una instancia por propuesta y un aviso administrativo para apertura sin enlace válido.
- [ ] Ejecutar tests y confirmar fallo.
- [ ] Implementar consulta pública paginada/acotada que reutiliza apertura existente, filtra `publish` sin depender de `current_user_can`, normaliza `mostrar_en_formulario`, exige enlace HTTPS accionable y no confunde fecha de inicio con período de preinscripción; no añadir estado manual.
- [ ] Ejecutar tests y contrato de catálogo existente; medir SQL/tiempo con más de 200 propuestas y descartar un recorrido N+1 de todo el catálogo en cada home antes de aprobar la tarea.
- [ ] Commit de plugin acotado.

### Tarea 5. Colecciones de noticias, agenda, institución y newsletter

**Plugin:** completar `class-flacso-home-data.php`; crear `tests/home-data-contract-test.php`; extraer consulta solo donde sea necesario de `sections/novedades-section.php`, `eventos-carousel.php`, `mailing.php` sin romper funciones existentes.

**Produce:** `flacso_get_home_view_model(): array` versión 1, datos y enlaces, sin HTML visual; `newsletter.available` para reutilizar formulario. **Consume:** tareas 3–4 y fuentes WP existentes.

- [ ] Escribir tests de noticia destacada + secundarias sin duplicados, evento publicado/visible/próximo, secciones vacías seguras, institución sin copia duplicada, newsletter disponible/no disponible, DTO `schema_version=1` y ausencia de HTML/CSS en campos.
- [ ] Ejecutar tests y comprobar fallo; implementar agregación y normalización de lectura sin REST pública nueva.
- [ ] Ejecutar tests nuevos, tests existentes de noticias/eventos/mailing que haya en el repo y `php -l`; inspeccionar SQL/tiempo de home con datos reales de staging.
- [ ] Commit de plugin acotado.

### Tarea 6. Hero y sistema visual v2 del tema

**Tema:** crear `template-parts/home/hero.php`, `assets/css/home/home.css`, `tests/home-hero-test.php`; modificar `functions.php` solo para registrar assets v2, sin activarlos en público.

**Produce:** `flacso_child_render_home_hero(array $campaign): string`; tokens v2 prefijados. **Consume:** DTO campaña de tarea 3, nunca post meta.

- [ ] Escribir tests para campaña/fallback, H1 único, CTA opcionales, imagen desktop/móvil ausente, clases de cuatro variantes, escape de título/URL y no aparición automática de «Inscripciones» por un año.
- [ ] Ejecutar test y confirmar fallo; implementar markup PHP semántico, `<picture>`/dimensiones/fetchpriority y CSS mobile-first bajo `.flacso-home-v2`.
- [ ] Ejecutar test y `php -l`; revisar visualmente 320/375/768/1024/1440px con y sin imagen/CTA y contraste de cada variante.
- [ ] Commit de tema acotado.

### Tarea 7. Secciones editoriales y header/footer

**Tema:** crear `template-parts/home/{formation,open-preinscriptions,news,agenda,institution,newsletter}.php`, `tests/home-sections-test.php`; completar `assets/css/home/home.css`; modificar navbar/footer solo si la tarea 1 confirmó los destinos.

**Produce:** renderers por sección que reciben arrays DTO; newsletter integra **el formulario existente** sin crear POST nuevo. **Consume:** tarea 5.

- [ ] Escribir tests de secciones llenas/vacías, solo enlaces reales, noticias no duplicadas, formación sin Doctorados ficticio, «Preinscripciones abiertas» solo con instancias accionables, ausencia del bloque y del CTA de cabecera si no las hay, enlace informativo/CTA separados, ausencia de imagen, orden H2/H3, alt/focus.
- [ ] Ejecutar tests y confirmar fallo; implementar listas editoriales, noticias jerarquizadas, agenda sin carrusel, institución y newsletter con formulario actual.
- [ ] Ejecutar tests y `php -l`; comprobar menú/búsqueda/CTA teclado y móvil, menú de Kadence y enlaces footer en páginas no-home.
- [ ] Commit de tema acotado.

### Tarea 8. Integración tras flag y compatibilidad entre versiones

**Tema:** modificar `front-page.php`, `functions.php`; crear `tests/home-switch-contract-test.php`. **Plugin:** adaptar `class-flacso-main-page-loader.php` solo si se necesita para no encolar CSS/JS legacy en v2.

**Produce:** `flacso_child_home_render_context(): array` con `mode` (`v2|legacy|emergency`) y `view_model`; filtro `flacso_main_page_requires_legacy_assets` con valor por defecto `true` en plugin. La misma decisión rige markup y assets. **Consume:** tareas 5–7.

- [ ] Escribir tests para opción ausente/`0`/`'0'`/`false` y `true`/`1`/`'1'`, plugin viejo/nuevo, API ausente, DTO mal formado o excepción, shortcode/bloque en otras páginas y un solo `<main>`; exigir que modo legacy encole CSS/JS legacy aunque la opción diga v2, y que emergencia no deje pantalla blanca.
- [ ] Ejecutar tests y comprobar fallo; implementar `flacso_child_home_render_context()` memoizada por petición. Solo `true`, `1` o `'1'` y DTO `schema_version=1` válido seleccionan v2. El loader aplica el filtro `flacso_main_page_requires_legacy_assets` con valor por defecto `true`; el tema devuelve `mode !== 'v2'`. Conservar el builder como fallback y un hero institucional mínimo cuando falta el plugin completo.
- [ ] Ejecutar contratos y `php -l`; probar combinaciones de releases plugin/tema en staging (plugin primero, tema primero y rollback de uno solo).
- [ ] Commit separados por repo con diff auditado; no activar flag todavía.

### Tarea 9. Preparación editorial y validación integral en staging

**Archivos:** documentación de evidencia/criterios; sin borrado legacy.

- [ ] Crear respaldo verificable de BD/opciones antes de preparar campañas en staging; conservar IDs y contenido legacy intactos.
- [ ] Crear campaña borrador a partir de hero actual solo con aprobación editorial; comprobar preview autenticada/no-store, imágenes, enlaces, autor/revisiones, vigencia y fallback. La preview no debe aparecer al visitante ni en una respuesta cacheada pública.
- [ ] Respaldar el valor del hero legacy y, antes de activar v2, dejarle una copia institucional aprobada y sin fecha; comprobar que el rollback no anuncia una campaña vencida. No sincronizar campañas nuevas con la opción legacy.
- [ ] Ejecutar tests de plugin y tema, `php -l`, y pruebas de integración de preinscripción, mailing, noticias, eventos, menú/buscador y páginas con shortcodes/bloques.
- [ ] Verificar a 320/375/768/1024/1440px, teclado, `focus-visible`, AA, reduced-motion, imágenes/CLS y Lighthouse contra baseline de tarea 1; corregir antes de activar.
- [ ] Registrar evidencias de rollback y revisión final; obtener aprobación específica para activar en producción.

### Tarea 10. Activación controlada y observación

**Archivos:** flag/configuración de rollout y registro de release, según infraestructura real; no asumir método de despliegue.

- [ ] Respaldar estado productivo y fijar SHA exactos; desplegar primero plugin compatible y luego tema con flag apagado, comprobando salud y rutas no-home tras cada paso.
- [ ] Preparar campaña/fallback mediante Admin autorizado y verificar zona horaria, CTA y alt sin publicar cambios no aprobados.
- [ ] Confirmar mediante cabeceras/configuración que caché de página/CDN cumple ≤60 segundos o dispone de purga programada fiable sin depender de visitas; si no se demuestra, mantener flag apagado y no prometer programación automática.
- [ ] Activar v2 con aprobación, purgar caché de página/CDN y comprobar HTML público/anónimo, móvil y enlaces; medir LCP/CLS/error logs.
- [ ] Probar en staging y producción controlada los segundos de borde al inicio/fin, superposición y retorno a fallback, con HTTP anónimo repetido antes y después del TTL; exigir cambio visible dentro de 60 segundos sin guardar manualmente.
- [ ] Ejecutar rollback del flag en staging y dejar procedimiento listo; verificar HTML y CSS legacy, hero institucional vigente y propagación por caché ≤60 segundos; documentar SHA, CI, despliegue, salud y verificación visual por separado.

### Tarea 11. Retiro diferido de legacy

**Ambos repos:** archivos legacy marcados en ambas specs, únicamente tras ventana de observación e inventario de usos.

- [ ] Enumerar shortcodes, bloques, filtro React, templates y enqueues realmente usados fuera de la portada; documentar consumidores y enlaces guardados.
- [ ] Escribir tests que prueben compatibilidad de esos consumidores y ausencia de carga CSS/JS legacy en home v2.
- [ ] Retirar primero overrides de `front-page-refresh.css` y el FAB/JS del hero viejo cuando no sean necesarios; retirar CSS/plugin renderer solo si los consumidores quedan migrados o mantienen asset separado.
- [ ] Ejecutar toda la regresión, inspección visual y comparación de payload; verificar rollback alternativo antes de eliminar cualquier soporte legacy.
- [ ] Commit/PR de limpieza independiente; no confundir «v2 funciona» con permiso para borrar el builder completo.

## Control final del plan

Cada tarea entrega algo comprobable: modelo, resolución/admin, catálogo abierto, DTO agregado, hero, secciones, switch, validación, activación y limpieza. Las funciones que deciden vigencia/apertura/permisos están solo en plugin; las que deciden semántica visual/CSS están solo en tema. Las tareas 2–7 se integran con flag apagado; 8 conserva legacy; 9–10 verifican rollback y el caché temporal; 11 es deliberadamente posterior. Las decisiones de la tarea 1 son bloqueantes para enlaces/copias concretos, no para el resto del modelo.
