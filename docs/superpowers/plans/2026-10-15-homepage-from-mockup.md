# Portada basada en la maqueta — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Publicar una portada nueva que reproduzca la maqueta y conserve los datos editables de WordPress.

**Architecture:** `front-page.php` mantiene el selector de portada; `inc/home-v2.php` compone seis componentes PHP del tema. El plugin sigue siendo la fuente de campañas, oferta, noticias, actividades y texto institucional. CSS propio y una fotografía de respaldo del tema reproducen la maqueta.

**Tech Stack:** WordPress, PHP 7.4+, Kadence child theme, CSS, GitHub Actions.

**Spec:** `docs/superpowers/specs/2026-10-15-homepage-from-mockup-design.md`

## Global Constraints

- Orden: Hero → Oferta → Próximas actividades → Noticias → Institucional → Impacto.
- Roboto, azul principal `#2a4676`; noticias y actividades con imágenes 4:5.
- Actividades siempre visibles; `agenda.flacso.edu.uy` no es destino de actividades.
- Hero sin bajada ni segundo botón. Header y footer existentes.
- Contenido público desde WordPress; el tema no consulta metadatos académicos.
- Conservar un camino de regreso a la portada anterior durante el despliegue.

## Review Focus

- Cero actividades: sección y mensaje presentes, sin enlace a reservas. Verificar en tarea 3.
- Evento sin imagen o URL válida: tarjeta legible, sin enlace falso. Verificar en tarea 3.
- Cero noticias fijadas o publicación duplicada: noticia principal y recientes correctas. Verificar en tarea 4.
- Campaña sin fotografía ni CTA: hero legible con imagen de respaldo y acción a oferta. Verificar en tarea 2.
- CSS legacy y nuevo cargados juntos: evitar conflicto de estilos. Verificar en tarea 5.

---

### Task 1: Aislamiento, baseline y adaptadores

**Files:** Modify `inc/home-v2.php`; Test `tests/home-v2-contract-test.php`.

**Interfaces:** Produce `flacso_home_v2_get_data(): array` con `campaign`, `offer`, `events`, `featured_news`, `recent_news` e `institution`.

- [ ] Registrar baseline de `bash bin/check-theme.sh` y estado Git en un worktree aislado.
- [ ] Escribir verificaciones del arreglo normalizado con plugin disponible/ausente, hasta tres eventos y hasta cuatro noticias recientes sin duplicados.
- [ ] Implementar lectura de `FLACSO_Home_Campaign::view_model()`, `flacso_section_oferta_educativa_get_cards()`, `flacso_section_eventos_get_items()`, getters de novedades y `Flacso_Main_Page_Settings::get_section('quienes')`.
- [ ] Ejecutar prueba de portada y registrar resultado.
- [ ] Commit de adaptadores y prueba.

### Task 2: Hero, oferta e imagen de respaldo

**Files:** Create `template-parts/home/hero.php`, `template-parts/home/offer.php`, `assets/img/home-hero-collaboration.jpg`; Modify `inc/home-v2.php`, `tests/home-v2-contract-test.php`.

**Interfaces:** Consumes `flacso_home_v2_get_data()`; produces HTML de hero y oferta con H1 único y enlaces públicos.

- [ ] Escribir verificaciones de texto aprobado, ausencia de bajada/segundo CTA, fotografía y cinco categorías con destinos reales.
- [ ] Copiar la fotografía proporcionada al tema y crear los dos componentes con escapes de salida WordPress y fallback institucional.
- [ ] Ejecutar prueba de portada y revisión visual de hero/oferta; registrar resultado.
- [ ] Commit de componentes y recurso.

### Task 3: Próximas actividades

**Files:** Create `template-parts/home/events.php`; Modify `inc/home-v2.php`, `tests/home-v2-contract-test.php`.

**Interfaces:** Consumes `events` del adaptador; produce sección permanente y hasta tres tarjetas 4:5 enlazadas al detalle individual.

- [ ] Escribir verificaciones para cero, uno y varios eventos, fecha, imagen ausente y URL ausente; comprobar ausencia de `agenda.flacso.edu.uy` en el bloque.
- [ ] Crear componente con estado vacío visible y tarjetas que usan los datos del CPT `evento`.
- [ ] Ejecutar prueba de portada; registrar resultado.
- [ ] Commit de actividades.

### Task 4: Noticias, institución e impacto

**Files:** Create `template-parts/home/news.php`, `template-parts/home/about.php`, `template-parts/home/impact.php`; Modify `inc/home-v2.php`, `tests/home-v2-contract-test.php`.

**Interfaces:** Consumes `featured_news`, `recent_news`, `institution`; produce los tres bloques finales de la portada.

- [ ] Escribir verificaciones de dos destacados, cuatro recientes sin duplicados, imágenes 4:5, texto institucional vigente, logo y franja `+20 países`.
- [ ] Crear componentes con enlaces, fechas, imágenes WordPress y respaldos visuales.
- [ ] Ejecutar prueba de portada y revisión de enlaces; registrar resultado.
- [ ] Commit de las secciones.

### Task 5: Sistema visual e integración

**Files:** Replace `assets/css/home-v2.css`; Modify `inc/assets.php`, `front-page.php`, `tests/home-v2-contract-test.php`.

**Interfaces:** CSS de portada limitado a `.flacso-home-v2`; `front-page.php` usa el renderer existente y carga solo los assets pertinentes.

- [ ] Escribir verificaciones de orden completo, estructura semántica y enqueues sin `front-page-refresh.css` en portada v2.
- [ ] Portar el diseño responsive de la maqueta, incluida la imagen del hero y las tarjetas 4:5.
- [ ] Ejecutar `bash bin/check-theme.sh` y revisión visual a 320, 375, 768, 1024 y 1440 px; corregir desbordes, contraste y enlaces.
- [ ] Commit de integración.

### Task 6: Publicación y comprobación pública

**Files:** Tema terminado y documentación de despliegue; sin cambios de datos editoriales salvo el mínimo imprescindible.

**Interfaces:** Commit final de tema en `main`, workflow `Deploy WordPress Theme`, portada anónima pública.

- [ ] Revisar diff, imágenes, reversibilidad y estado de `origin/main`; ejecutar validación final sobre el árbol que se publicará.
- [ ] Integrar y enviar el tema a `main`, siguiendo el mecanismo Git disponible.
- [ ] Verificar SHA remoto, CI, despliegue, HTML público y revisión visual de la portada en escritorio y móvil.
- [ ] Si la opción de portada actual no muestra el renderer nuevo, ajustar la configuración de WordPress con la herramienta autorizada y verificar de nuevo.
