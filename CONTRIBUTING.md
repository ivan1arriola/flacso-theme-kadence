# Contribuir al tema hijo

## Límites del tema

El tema controla presentación, plantillas y compatibilidad con Kadence. Los tipos de contenido, campos, formularios y reglas del catálogo académico pertenecen al plugin `flacso-uruguay-plugin`. Antes de agregar una consulta o regla académica, comprobar si el plugin ya expone un View Model o función pública.

## Plantillas y componentes

- Mantener en la raíz los archivos que WordPress busca por nombre, por ejemplo `single-seminario.php`, `archive-docente.php` y `page-contacto.php`.
- Colocar componentes reutilizables en `template-parts/<dominio>/` y pasarles datos con el argumento `context` de `get_template_part()`.
- Escapar cada valor al imprimirlo según su contexto: `esc_html()` para texto, `esc_attr()` para atributos, `esc_url()` para URL y `wp_kses_post()` solo cuando se permita HTML filtrado.
- No imprimir datos académicos sin sanitizar ni leer metadatos legacy directamente desde plantillas.

## CSS y JavaScript

- Agregar estilos en `assets/css/` y scripts en `assets/js/`, agrupados por plantilla o área.
- Registrar recursos condicionalmente en `inc/assets.php`; usar `flacso_child_asset_version()` para invalidar caché.
- Mantener los comportamientos como mejora progresiva y no agregar bloques `<style>` o dependencias tras `get_header()`.
- Los CTA de preinscripción deben conservar el portal externo y el atributo `data-flacso-preinscription-cta`.

## Validación

Antes de integrar cambios, ejecutar:

```bash
bash bin/check-theme.sh
```

El script incluye las pruebas académica, FAQ, portada y cargador modular. La prueba visual de portada requiere Chrome o Chromium; indicar una ruta personalizada con `FLACSO_TEST_BROWSER` si el ejecutable no está en PATH. Las pruebas de contratos de equipos y cartas que no figuran en `bin/check-theme.sh` pueden ejecutarse individualmente con `php tests/<archivo>.php`.

Al reorganizar módulos, conservar nombres de funciones, hooks, prioridades, dependencias y rutas de recursos. Documentar aquí o en `ARCHITECTURE.md` los cambios de estructura que afecten a futuras contribuciones.