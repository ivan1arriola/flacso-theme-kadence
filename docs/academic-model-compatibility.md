# Modelo Académico en el Tema Kadence Child (FLACSO v7)

El tema consume el dominio académico del plugin exclusivamente mediante `inc/academic-public-model.php`. Esta capa es de sólo lectura y genera View Models unificados y estables para todas las plantillas.

## Principios de Diseño y Contratos

1. **CPT `programa-academico`**: Agrupación institucional de posgrados. Aporta la coordinación académica (`coordinacion`: `[{docente_id, nombre, rol}]`).
2. **CPT `oferta-academica`**: Propuestas de formación de posgrado (`maestria`, `especializacion`, `diplomado`, `diploma`). *Seminario no es un tipo de oferta académica.*
3. **CPT `cohorte`**: Ocurrencia temporal de una `oferta-academica`. Gestiona las fechas (`fecha_inicio`, `fecha_fin`), la denominación romana (`Cohorte {ROMANO}`), el calendario académico y el enlace a aranceles (`tabla_precio_id`).
4. **CPT `seminario`**: Propuesta académica de seminario (`/formacion/seminarios/{slug}/`).
5. **CPT `edicion`**: Ocurrencia temporal de un `seminario`. Gestiona las fechas, modalidad, encuentros sincrónicos (`encuentros_sincronicos`), docentes y aranceles (`tabla_precio_id`).
6. **CPT `tabla-precio`**: Fuente de verdad de aranceles y opciones de financiación (`precios_filas`, `mostrar_precios_dolares`, `precios_nota`).
7. **Preinscripción Externa**: Todos los botones de preinscripción apuntan directamente a la plataforma centralizada `https://preinscripciones.flacso.edu.uy`. No existen formularios locales ni procesadores internos en WordPress.

## Fachada de Lectura en el Tema (`inc/academic-public-model.php`)

- `flacso_theme_get_offer_context(int $offer_id)`: Genera el View Model de una Oferta Académica a partir de `FLACSO_Academic_Catalog::get_offer()`.
- `flacso_theme_get_seminar_context(int $seminar_id)`: Genera el View Model de un Seminario a partir de `FLACSO_Academic_Catalog::get_seminar()`.
- `flacso_theme_get_seminars_catalog()`: Retorna el catálogo ordenado de seminarios activos.
- `flacso_theme_get_program_seminars_catalog(int $offer_id)`: Retorna los seminarios vinculados a una oferta académica.

## Verificación

```bash
bash bin/check-theme.sh
```

El script valida sintaxis, ejecuta la suite de pruebas automatizadas y audita que no existan llamadas a clases eliminadas ni metadatos legacy.
