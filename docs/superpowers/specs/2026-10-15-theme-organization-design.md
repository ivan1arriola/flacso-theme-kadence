# Organización del tema hijo FLACSO

## Objetivo

Hacer más fácil mantener el tema hijo `kadence-child-flacso` sin cambiar su comportamiento público ni la forma en que WordPress selecciona sus plantillas.

## Alcance

- Mantener en la raíz las plantillas PHP cuyos nombres forman parte de la jerarquía de plantillas de WordPress.
- Mantener `functions.php` como punto de entrada y trasladar su código a módulos pequeños bajo `inc/`, agrupados por responsabilidad.
- Actualizar `README.md` y `ARCHITECTURE.md` para describir la estructura que realmente existe, los límites entre tema y plugin y el flujo actual de validación/despliegue.
- Añadir una guía de contribución concisa con las convenciones presentes: seguridad de salida, recursos condicionales, límites tema/plugin, validación local y cautela con archivos de plantilla.
- Ajustar el script de validación únicamente si la guía o la reorganización revela una comprobación necesaria que pueda automatizarse con las herramientas actuales.

## Límites de diseño

- No cambiar rutas, nombres de plantillas, hooks, prioridades, lógica de negocio, contenido editorial, dependencias ni comportamiento visual.
- No mover plantillas de la raíz a subdirectorios: WordPress identifica varios archivos por nombre y ruta dentro de la jerarquía de plantillas.
- Los módulos incluidos deben cargarse en un orden explícito desde `functions.php` y conservar el mismo contexto de carga del tema.
- Cada función y callback existente debe permanecer disponible con el mismo nombre y registrar los mismos hooks, filtros y recursos.
- La documentación debe reflejar el script real `bin/check-theme.sh` y el workflow `.github/workflows/deploy-theme.yml`, sin afirmar que el workflow ejecuta controles inexistentes.

## Estructura objetivo

`functions.php` conserva únicamente la protección de acceso, las funciones que deban permanecer allí por compatibilidad y los `require_once` ordenados para módulos bajo `inc/`. Las responsabilidades actuales se separan en módulos de carga de tema y presentación, recursos, miniaturas/medios, Bootstrap/jQuery UI y personalizador/contacto/rutas virtuales, según los límites que confirme la implementación.

Las plantillas permanecen en raíz. Los componentes reutilizables continúan en `template-parts/<dominio>/`; recursos permanecen en `assets/css/` y `assets/js/`.

## Documentación

`README.md` mostrará la estructura real, incluidos `inc/`, `template-parts/`, `tests/`, `docs/` y `bin/`, y describirá requisitos y comandos actuales. `ARCHITECTURE.md` explicará responsabilidades y límites de capas. La guía de contribución indicará cómo agregar plantillas y recursos, qué salida escapar y qué validar.

## Verificación y criterios de aceptación

- La reorganización preserva nombres de funciones, callbacks, prioridades, dependencias y rutas de recursos.
- `bash bin/check-theme.sh` pasa después del cambio.
- Las pruebas contractuales existentes siguen pasando.
- El workflow de despliegue continúa empaquetando los archivos runtime y excluyendo documentación, pruebas y herramientas locales según su configuración.
- La documentación enumera solo directorios y validaciones existentes.
- No se cambian archivos PHP de plantilla salvo que una referencia quede necesariamente desactualizada por la reorganización; cualquier excepción se justifica en el cambio.
