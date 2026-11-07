# Portada según maqueta Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Reemplazar la portada actual del tema por la maqueta aprobada, con textos editables desde el admin y datos reales de WordPress.

**Architecture:** El plugin guarda los textos editables en una opción nueva (`Flacso_Home_Content`) con pantalla propia. El tema arma un único arreglo de datos (`flacso_home_get_data()`) con esa opción más la capa existente (oferta, eventos, noticias) y lo pinta con siete template-parts y un CSS propio. Header y footer siguen siendo los de Kadence.

**Tech Stack:** PHP 8 (WordPress), tests PHP sin framework (`php tests/x-test.php`, patrón de `tests/home-v2-contract-test.php`), CSS nativo, Roboto.

**Spec:** `docs/superpowers/specs/2026-11-07-portada-maqueta-design.md`. Maqueta fuente: `C:\Users\Iván\repositorios\FLACSO Uruguay\flacso-portada-vscode.zip` (extraída en `%TEMP%\flacso-portada-extract\flacso-portada\dist`; si no existe, volver a extraer en una carpeta nueva).

## Global Constraints

- Orden de secciones: hero, oferta académica, campaña, agenda, noticias, investigación, "¿Qué es FLACSO Uruguay?" (el footer es el de Kadence).
- Hero: eyebrow `PROGRAMA URUGUAY`; título `Formación que conecta,` + énfasis `conocimiento que transforma.`; bajada `Formación, investigación y cooperación académica con perspectiva latinoamericana.`; botón `Explorar oferta académica` → `/formacion/`.
- Cinco niveles de oferta, en este orden: Maestrías, Especializaciones, Diplomados, Diplomas, Seminarios.
- Campaña por defecto: etiqueta `INSCRIPCIONES 2027`, título `Tu próximo paso empieza acá.`, texto `Explorá las propuestas con inscripciones abiertas.`, botón `Conocer la oferta` → `/formacion/`.
- Investigación por defecto: título `Pensar la región.` / `Producir conocimiento.`; bajada `Conocé los proyectos, centros y publicaciones de FLACSO Uruguay.`; enlaces `Proyectos de investigación` → `/institucional/investigaciones/`, `Archivo Histórico IIN–FLACSO` → `https://www.archivoiinflacso.uy/`, `Libros y publicaciones` → `/categoria/publicaciones/libros/`.
- El texto "¿Qué es FLACSO Uruguay?" ya es editable (`Flacso_Main_Page_Settings::get_section('quienes')['content']`); no se duplica. Los dos párrafos aprobados con negritas son el valor por defecto visible en la maqueta y deben quedar guardados en esa opción si está vacía.
- Imágenes de noticias y afiches en marcos 4:5 sin recorte. Agenda y campaña se ocultan por completo si no aplican.
- Enlaces `target="_blank"` llevan `rel="noopener noreferrer"`; sin `<style>` embebido en plantillas (lo exige `bin/check-theme.sh`).
- Plugin: commit directo a `main`, sin ramas. Tema: rama `feat/portada-maqueta-20261107`.

## Review Focus

- Opción guardada con campos vacíos o con tipos erróneos (string en vez de arreglo): se usan los valores por defecto, nunca una sección en blanco.
- URLs con `javascript:` o `//host` en cualquier enlace editable: se descartan y se usa el valor por defecto.
- Campaña CPT activa (fuente `campaign`): el hero sigue mostrando su título, kicker, CTA e imagen, como hoy.
- Sin imagen de hero configurada: se usa la fotografía por defecto de la maqueta, no el logo.
- Noticias: 0, 1 o más de 2 fijadas; ninguna fijada y ninguna reciente.

---

### Task 1: Contenido editable de la portada (plugin)

**Files:**
- Create: `flacso-uruguay-plugin/modules/main-page/includes/class-flacso-home-content.php`
- Modify: `flacso-uruguay-plugin/modules/main-page/init.php` (require junto a `class-flacso-home-campaign.php`, línea ~63)
- Test: `flacso-uruguay-plugin/tests/home-content-test.php` (mismo estilo de stubs que `tests/home-campaign-test.php`)

**Interfaces:**
- Produces: `final class Flacso_Home_Content` con `public const OPTION_KEY = 'flacso_home_content'`, `public static function defaults(): array`, `public static function get(): array` (siempre devuelve todas las claves, mezcladas con `defaults()`), `public static function sanitize($raw): array`.
- Forma del arreglo: `hero{eyebrow,title_lead,title_em,intro,cta_label,cta_url}`, `campaign{enabled(bool),label,title,text,cta_label,cta_url}`, `research{title_lead,title_em,intro,link_label,link_url,links[3]{label,url}}`.
- Consumes: `FLACSO_Home_Campaign::sanitize_url(string): string` para toda URL.

- [ ] **Step 1: Escribir `home-content-test.php`** con stubs de `get_option` (lee `$GLOBALS['opt']`), `sanitize_text_field`, `wp_strip_all_tags`, y afirmaciones: `defaults()['hero']['title_em'] === 'conocimiento que transforma.'`; `defaults()['campaign']['enabled'] === true`; `count(defaults()['research']['links']) === 3`; `get()` con opción `false` devuelve `defaults()`; `get()` con `['hero'=>'texto']` (tipo erróneo) devuelve el hero por defecto; `sanitize(['hero'=>['cta_url'=>'javascript:alert(1)']])['hero']['cta_url'] === '/formacion/'`; `sanitize(['hero'=>['title_lead'=>'']])['hero']['title_lead']` es el valor por defecto; `sanitize(['campaign'=>['enabled'=>'']])['campaign']['enabled'] === false` (el desmarcado debe poder apagar la campaña).
- [ ] **Step 2: Ejecutar** `php flacso-uruguay-plugin/tests/home-content-test.php`. Esperado: error fatal por clase inexistente.
- [ ] **Step 3: Implementar la clase** con los valores de Global Constraints. `sanitize` quita etiquetas de todo texto y pasa cada URL por `sanitize_url`; un texto vacío cae al valor por defecto salvo `campaign.enabled`, que es booleano explícito. `get()` aplica `array_merge` por sección solo si el valor guardado es un arreglo.
- [ ] **Step 4: Ejecutar el test.** Esperado: sin salida y código 0. Ejecutar también `php flacso-uruguay-plugin/tests/home-campaign-test.php` (no debe romperse).
- [ ] **Step 5: Commit** en el plugin (`main`): `git add ... && git commit -m "Agregar contenido editable de la portada"`.

### Task 2: Pantalla de ajustes "Portada" (plugin)

**Files:**
- Modify: `flacso-uruguay-plugin/modules/main-page/includes/class-flacso-homepage-admin-v2.php` (agregar `render_content()` y `save_content()`, acción `flacso_save_home_content`, registrada en `init()`)
- Modify: `flacso-uruguay-plugin/modules/main-page/init.php` (agregar el submenú bajo `flacso-main-page`, slug `flacso-home-content`, título "Textos de portada", capacidad `manage_options`)
- Test: `flacso-uruguay-plugin/tests/home-content-admin-contract-test.php`

**Interfaces:**
- Consumes: `Flacso_Home_Content::get()`, `Flacso_Home_Content::sanitize($raw)`, `Flacso_Home_Content::OPTION_KEY`.
- Produces: `Flacso_Homepage_Admin_V2::render_content(): void`, `Flacso_Homepage_Admin_V2::save_content(): void`.

- [ ] **Step 1: Escribir el test de contrato** que lee el fuente y afirma: contiene `flacso_save_home_content`; `save_content` contiene `current_user_can('manage_options')` y `check_admin_referer`; llama a `Flacso_Home_Content::sanitize`; `render_content` no imprime valores sin `esc_attr`/`esc_html` (buscar que cada `value="<?php echo` use `esc_attr`).
- [ ] **Step 2: Ejecutar.** Esperado: FAIL.
- [ ] **Step 3: Implementar** el formulario (tres bloques: Hero, Campaña con casilla "Mostrar", Investigación con 3 filas de enlace) y el guardado que usa `update_option(Flacso_Home_Content::OPTION_KEY, sanitize(wp_unslash($_POST['flacso_home_content'] ?? [])))` y redirige con aviso, siguiendo `save()` existente.
- [ ] **Step 4: Ejecutar el test y `php -l` de los archivos tocados.** Esperado: PASS.
- [ ] **Step 5: Commit** en el plugin (`main`).

### Task 3: Capa de datos de la portada (tema)

**Files:**
- Create: `inc/home.php`
- Modify: `functions.php:13` (cambiar el `require_once` de `inc/home-v2.php` por `inc/home.php`)
- Create (rename del test): `tests/home-contract-test.php` a partir de `tests/home-v2-contract-test.php`
- Modify: `bin/check-theme.sh` (reemplazar la línea de `home-v2-contract-test.php`)

**Interfaces:**
- Produces:
  - `flacso_home_get_data(): array` con claves `hero{eyebrow,title_lead,title_em,intro,cta{label,url},image,image_mobile,image_alt}`, `cards` (5, cada una `{title,url,open}`), `campaign` (`null` si está apagada, si no `{label,title,text,cta{label,url}}`), `event` (primer evento o `null`), `featured_news` (máx. 2), `recent_news` (máx. 4), `research{title_lead,title_em,intro,link,links}`, `institution`.
  - `flacso_home_part(string $name, array $data): void`, `flacso_home_render(): string`, `flacso_home_news_card($post, string $variant, bool $is_pinned = false): void`.
- Consumes: `Flacso_Home_Content::get()`, `FLACSO_Home_Campaign::view_model()`, y las mismas funciones de plugin que usa `home-v2.php` (`flacso_section_oferta_educativa_get_cards`, `flacso_section_eventos_get_items`, `flacso_section_novedades_get_sticky_posts`, `flacso_section_novedades_get_posts`).

- [ ] **Step 1: Copiar y adaptar el test** a `tests/home-contract-test.php`: stub de `Flacso_Home_Content` con `get()` que devuelva `defaults` o `$GLOBALS['content']`; afirmar sobre `flacso_home_get_data()`: `hero.title_lead === 'Formación que conecta,'`; `count($data['cards']) === 5` y el orden de claves `maestrias, especializaciones, diplomados, diplomas, seminarios`; con `$GLOBALS['content']['campaign']['enabled']=false` → `campaign === null`; sin eventos → `event === null`; con un evento → `event['id']` es el primero; con campaña CPT `source=campaign` y título propio → `hero.title_lead` es ese título y `title_em === ''`; con 0 fijadas y 5 recientes → `featured_news` tiene 1 y `recent_news` 4; con 3 fijadas → `featured_news` 2, y la tercera aparece primera en `recent_news` sin duplicarse; sin noticias → ambos vacíos.
- [ ] **Step 2: Ejecutar** `php tests/home-contract-test.php`. Esperado: FAIL (función inexistente).
- [ ] **Step 3: Crear `inc/home.php`** moviendo la lógica de `home-v2.php` (ofertas, conteo de inscripciones abiertas, eventos, noticias, `news_card`) y agregando: textos desde `Flacso_Home_Content::get()`; hero de campaña CPT sobrescribe título/kicker/CTA/imagen solo si `source === 'campaign'`; imagen de hero sin configurar → `get_stylesheet_directory_uri() . '/assets/img/home-hero.jpg'`.
- [ ] **Step 4: Ejecutar el test.** Esperado: PASS para las afirmaciones de datos (las de markup se agregan en la Task 4).
- [ ] **Step 5: Commit** en el tema.

### Task 4: Plantillas de la portada (tema)

**Files:**
- Create: `template-parts/home/campaign.php`, `template-parts/home/agenda.php`, `template-parts/home/research.php`
- Modify (reescribir según `home.html` de la maqueta): `template-parts/home/hero.php`, `offer.php`, `news.php`, `about.php`
- Delete: `template-parts/home/events.php`, `template-parts/home/impact.php`
- Modify: `inc/home.php` (`flacso_home_render()` recorre `['hero','offer','campaign','agenda','news','research','about']`), `front-page.php` (una sola ruta: `echo flacso_home_render();`, sin el builder ni el encolado de `front-page-refresh.css`)
- Test: `tests/home-contract-test.php`

**Interfaces:**
- Consumes: el arreglo de `flacso_home_get_data()` (Task 3).
- Produces: `<main id="main" class="flacso-home" role="main">` con las secciones y estas clases y ids: `hero`, `offer-section#oferta`, `campaign`, `agenda-section`, `news-section#noticias`, `research-section#investigacion`, `about-section#institucional`.

- [ ] **Step 1: Ampliar el test** con afirmaciones sobre `flacso_home_render()`: los encabezados `Formación que conecta`, `Encontrá tu próxima formación`, `Noticias`, `Pensar la región.`, `¿Qué es FLACSO Uruguay?` aparecen en ese orden; exactamente un `<h1`; `Próximas actividades` ausente sin eventos y presente con uno; `Tu próximo paso empieza acá.` ausente con la campaña apagada; 5 enlaces de oferta; 3 enlaces de investigación numerados `01`, `02`, `03`; ningún `target="_blank"` sin `rel`; sin la cadena `Conocer las novedades`.
- [ ] **Step 2: Ejecutar.** Esperado: FAIL.
- [ ] **Step 3: Escribir las plantillas** copiando el marcado y los textos accesibles de `home.html` (iconos SVG inline de la oferta, `aria-label`, marcos de afiche), sustituyendo textos por `$data`. Cada parte oculta se corta con `return;` al inicio, como hacía `events.php`.
- [ ] **Step 4: Ejecutar el test y `bin/check-theme.sh`.** Esperado: PASS y sin errores.
- [ ] **Step 5: Commit** en el tema.

### Task 5: Estilos y recursos (tema)

**Files:**
- Create: `assets/css/front-page.css` (a partir de `dist/home.css` de la maqueta), `assets/img/home-hero.jpg` (de `dist/assets/hero.jpg`)
- Modify: `inc/assets.php:60-95` (`flacso_child_enqueue_front_page_assets` encola solo `flacso-front-page` con dependencia `flacso-site-chrome`; se conserva `assets/js/home-offer-reveal.js` solo si el CSS nuevo lo usa, si no se retira)

**Interfaces:**
- Consumes: clases definidas en la Task 4.

- [ ] **Step 1: Adaptar el CSS**: quitar reglas de variantes (`[data-variant]`), `.topbar`, `.header*`, `.mobile-menu`, `.dropdown`, `.skip`, `dialog` y el pie propio; prefijar todo bajo `.flacso-home`; conservar tokens, tipografía, marcos 4:5 y `prefers-reduced-motion`. Referenciar Roboto como ya hace el tema (no copiar las `.ttf`).
- [ ] **Step 2: Cambiar el encolado** y verificar con `php -l inc/assets.php`.
- [ ] **Step 3: Verificación visual** con la skill `run` (WordPress local con Docker, ver memoria de despliegue): capturas a 320, 390, 768 y 1440 px comparadas con `dist/home.html?v=elegida`. Criterio: sin desborde horizontal, hero, oferta y noticias coinciden con la maqueta; los afiches no se recortan.
- [ ] **Step 4: Commit** en el tema.

### Task 6: Retirar lo que quedó sin uso (tema)

**Files:**
- Delete (solo si `Grep` confirma cero referencias fuera de ellos): `inc/home-v2.php`, `assets/css/home-v2.css`, `assets/css/front-page-refresh.css`, `assets/css/front-page-base.css`, `assets/css/front-page-hero.css`, `assets/js/front-page-hero.js`, `template-parts/front-page/hero.php`, `tests/home-v2-contract-test.php`

- [ ] **Step 1: Buscar referencias** de cada archivo con `Grep` en tema y plugin (sin `.worktrees`); borrar solo los que no tengan ninguna.
- [ ] **Step 2: Ejecutar** `bin/check-theme.sh`. Esperado: sin errores.
- [ ] **Step 3: Commit** en el tema.
- [ ] **Step 4: Revisión final de la rama** con `git diff main...HEAD --stat` y despliegue siguiendo la nota "Despliegue del plugin Mautic" solo en lo aplicable (rsync in situ). El plugin se despliega antes que el tema.

---

## Self-review (hecha)

- **Cobertura del spec:** ajustes de plugin (T1–T2), template-parts y `front-page.php` (T3–T4), CSS y recursos (T5), retiro de lo no usado (T6), comportamiento dinámico y pruebas (T3–T4), entrega por repositorio (Global Constraints).
- **Decisión que se aparta del spec, a confirmar:** el spec dice retirar el builder del plugin. El plan retira solo las referencias del tema y deja el builder y `flacso_home_v2_enabled` en el plugin, porque `Flacso_Homepage_Admin_V2` documenta que sirven para volver atrás sin migrar datos; borrarlos es un cambio aparte.
- **Hero y CPT de campaña:** el spec no los menciona. El plan conserva la campaña CPT existente como sobreescritura temporal del hero.
- **Tipos:** `flacso_home_get_data` / `flacso_home_render` / `flacso_home_part` / `flacso_home_news_card` usan el mismo nombre en T3, T4 y T6.
