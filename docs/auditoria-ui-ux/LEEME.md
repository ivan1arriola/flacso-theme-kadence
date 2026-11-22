# Auditoría UI/UX (puntero)

La auditoría UI/UX de este tema y del plugin se documenta en el repositorio **`flacso-uruguay-plugin`**, carpeta `docs/auditoria-ui-ux/`:

- `BITACORA.md`: registro de solo-agregar de cada intervención, humana o de agente.
- `HALLAZGOS.md`: lista consolidada con ID, evidencia y estado.
- `GUIA-SUBAGENTES.md`: tiers T1/T2/T3, preámbulo obligatorio y plantillas de prompt, para cualquier herramienta.
- `TAREAS-CI.md`: paquetes de trabajo, ramas, dueños de archivos y compuertas de CI.

Reglas que valen también aquí:
- Los agentes no hacen push a `main` ni despliegan: abren PR y una persona mergea. `main` despliega a producción.
- Todo cambio de un agente se registra en la bitácora.
- Los cambios del tema que pertenecen a un paquete (por ejemplo `WP-04` tokens) se hacen en una rama `ui/WP-XX-slug` de este repositorio.
