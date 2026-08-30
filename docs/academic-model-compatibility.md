# Compatibilidad con el modelo académico unificado

El tema consume el dominio académico del plugin mediante
`inc/academic-public-model.php`. Esa capa es de sólo lectura: no registra tipos,
no persiste relaciones y no decide si una instancia acepta preinscripciones.

## Modelo público

- Toda propuesta, incluido un seminario, se presenta como `OfertaAcademica`.
- La cohorte o edición visible se obtiene de una `InstanciaOferta`.
- La palabra «Edición» se usa para seminarios y «Cohorte» para los demás tipos.
- El CTA se obtiene de `flacso_get_preinscription_cta()`; el tema no interpreta
  fechas ni estados para decidir si una preinscripción está abierta.
- Las relaciones `integra` y `compuesto_por` se leen mediante
  `FLACSO_Relacion_Oferta_Academica`.

## Release A

Mientras el CPT `seminario` siga registrado, el adaptador combina en lectura:

1. `oferta-academica` con término `seminario`;
2. posts legacy `seminario` todavía no migrados.

Las plantillas reciben el mismo View Model en ambos casos. El fallback legacy
está encapsulado en el adaptador y se podrá retirar en Release B sin volver a
modificar las vistas.

## Superficies cubiertas

- ficha individual de oferta y ficha legacy de seminario;
- catálogo general de seminarios;
- seminarios vinculados a una formación;
- archivo de tipos de oferta;
- selector del formulario de consultas;
- formulario local de preinscripción legacy.

Cuando una instancia usa `gestor_preinscripciones`, el formulario local deriva
al URL resuelto por el plugin. El formulario WordPress queda reservado al flujo
`legacy_editor` durante la transición.

## Verificación

```bash
bash bin/check-theme.sh
```

El comando valida sintaxis, ejecuta el test contractual del View Model y evita
que las plantillas vuelvan a leer directamente claves temporales legacy o
claves `_flacso_*` que no pertenecen al esquema actual.
