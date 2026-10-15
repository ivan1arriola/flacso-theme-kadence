# Kadence Child FLACSO

Tema hijo de WordPress para el sitio institucional de FLACSO Uruguay. Extiende Kadence y contiene las plantillas y la presentación del sitio; el plugin `flacso-uruguay-plugin` conserva los tipos de contenido, campos, formularios, consultas y reglas académicas.

## Requisitos

- WordPress 6.2 o posterior.
- PHP 7.4 o posterior.
- Tema padre `kadence` instalado.
- Bash y PHP para ejecutar la validación local; Chrome o Chromium para las comprobaciones visuales de portada.
- WP-CLI en el servidor para el smoke test de despliegue.

El slug del tema es `kadence-child-flacso`. WordPress lee sus metadatos desde `style.css`.

## Modelo académico

El tema consume el modelo canónico del plugin (`FLACSO_Academic_Catalog` y `FLACSO_Academic_Repository`) mediante el adaptador de sólo lectura `inc/academic-public-model.php`. Las ofertas académicas tienen ocurrencias `Cohorte`; los seminarios tienen `EdicionSeminario`; los aranceles provienen de `TablaPrecio`. Los CTA de preinscripción apuntan al portal externo centralizado. Ver [`docs/academic-model-compatibility.md`](docs/academic-model-compatibility.md).

## Estructura

```text
kadence-child-flacso/
├── assets/                 CSS, JavaScript e imágenes
├── bin/                    validación local
├── docs/                   documentación técnica
├── inc/                    módulos PHP del tema
├── template-parts/         componentes reutilizables por dominio
├── tests/                  pruebas contractuales y de presentación
├── *.php                   plantillas reconocidas por WordPress
├── functions.php           protección y carga ordenada del tema
├── style.css               metadatos y estilos globales
├── ARCHITECTURE.md         límites y responsabilidades
└── CONTRIBUTING.md         convenciones para cambios
```

Los archivos de plantilla como `single-seminario.php`, `archive-docente.php` y `page-contacto.php` permanecen en la raíz para conservar la jerarquía de plantillas de WordPress. La responsabilidad de cada módulo está descrita en [`ARCHITECTURE.md`](ARCHITECTURE.md).

## Desarrollo y validación

La validación local oficial es:

```bash
bash bin/check-theme.sh
```

El script revisa la sintaxis de todos los PHP, ejecuta las pruebas académica, FAQ, portada y cargador modular, y comprueba enlaces externos, estilos embebidos, CTA y referencias legacy. La prueba de portada necesita Chrome o Chromium disponible en el entorno; puede indicarse su ruta mediante `FLACSO_TEST_BROWSER`.

No se utiliza `./build`, `package-theme.sh` ni otro script local para preparar entregas. Para agregar o modificar componentes, seguir [`CONTRIBUTING.md`](CONTRIBUTING.md).

## Despliegue

Cada push a `main` activa `.github/workflows/deploy-theme.yml`. El flujo valida el SHA y los archivos requeridos, calcula la versión efectiva, ejecuta `bin/check-theme.sh`, crea y sube un ZIP, copia los archivos por SSH a un staging temporal, sincroniza exclusivamente la carpeta del tema y verifica con WP-CLI el tema activo y la versión instalada.

WordPress está instalado en `/var/www/clients/client2/web5/web`; el destino es `/var/www/clients/client2/web5/web/wp-content/themes/kadence-child-flacso`. El servidor no ejecuta `git pull` dentro de WordPress. El workflow requiere los secretos `EC2_HOST`, `EC2_USER` y `EC2_SSH_KEY`.

El artifact excluye `.git`, `.github`, `docs/`, `tests/`, `bin/`, `node_modules/`, `vendor/`, documentación de raíz y archivos temporales del sistema. Las reglas están en el workflow.

## Versionado automático

El workflow deriva la versión del historial Git del SHA con el formato `<versión base>.<cantidad de commits>`. La versión base se declara en `style.css` y solo se cambia manualmente al iniciar una nueva línea. El workflow no crea commits automáticos.