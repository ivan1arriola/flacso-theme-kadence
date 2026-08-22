# Kadence Child FLACSO

Tema hijo de WordPress utilizado por el sitio institucional de FLACSO Uruguay.

Extiende al tema padre Kadence con:

- portada y navegación institucional;
- plantillas de oferta académica y seminarios;
- docentes, eventos, convenios y novedades;
- formularios y páginas de confirmación;
- estilos y scripts propios del sitio;
- metadatos Open Graph.

## Requisitos

- WordPress 6.0 o posterior;
- PHP 7.4 o posterior;
- tema padre `kadence` instalado;
- WP-CLI para las validaciones posteriores al despliegue.

El slug del tema es `kadence-child-flacso` y su archivo principal de metadatos es `style.css`.

## Versionado automático

Cada commit de `main` produce una versión efectiva con el formato:

```text
<versión base>.<cantidad de commits>
```

Por ejemplo, si `style.css` declara `1.1.52` y el commit es el número 250 del historial, la versión desplegada será `1.1.52.250`.

El workflow deriva la versión directamente del historial Git del SHA, actualiza la copia de distribución de `style.css` y comprueba después del despliegue que WordPress reporte exactamente esa versión.

La versión base solo debe cambiarse manualmente cuando corresponda iniciar una nueva línea de versión. El workflow no crea commits automáticos.

## Desarrollo local

No se utiliza `./build`, `package-theme.sh` ni otro script local para preparar entregas.

La validación antes de publicar cambios es:

```bash
bash bin/check-theme.sh
```

`bin/check-theme.sh` valida sintaxis PHP, enlaces externos seguros y que las plantillas no vuelvan a introducir estilos embebidos. El workflow de producción lo ejecuta como gate antes de crear el artifact.

## Despliegue a producción

Cada push a `main` activa `.github/workflows/deploy-theme.yml`.

El flujo oficial es:

1. checkout del SHA que activó el workflow;
2. cálculo de la versión efectiva;
3. validación de archivos requeridos, sintaxis PHP y separación del CSS;
4. creación del ZIP como artifact;
5. copia por SSH a un staging exclusivo bajo `/tmp`;
6. sincronización con `rsync --delete` exclusivamente sobre la carpeta del tema;
7. asignación de propietario y grupo `web5:client2`;
8. inicialización de WordPress mediante WP-CLI como usuario `web5`;
9. comprobación del tema activo y de la versión instalada.

WordPress está instalado en:

```text
/var/www/clients/client2/web5/web
```

El tema se despliega exclusivamente en:

```text
/var/www/clients/client2/web5/web/wp-content/themes/kadence-child-flacso
```

El servidor no ejecuta `git pull` dentro de WordPress.

### GitHub Secrets

El workflow requiere:

- `EC2_HOST`;
- `EC2_USER`;
- `EC2_SSH_KEY`.

## Estructura principal

```text
kadence-child-flacso/
├── style.css
├── functions.php
├── header.php
├── footer.php
├── front-page.php
├── assets/
│   ├── css/
│   ├── js/
│   └── img/
├── inc/
├── template-parts/
├── templates/
├── bin/
└── ARCHITECTURE.md
```

## Archivos excluidos de producción

El artifact y el despliegue excluyen:

- metadatos Git y workflows;
- `node_modules/` y `vendor/`;
- herramientas de desarrollo bajo `bin/`;
- `README.md`, `ARCHITECTURE.md` y `phpcs.xml.dist`;
- logs y archivos temporales del sistema.
