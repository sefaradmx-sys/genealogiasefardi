# Genealogía Sefardí

Plataforma de genealogía documental (capacidades tipo árbol / persona / fuentes / memorias / cadenas de prueba).  
Producto **original** — no es un clon de marca ni UI de FamilySearch.

- **Dominio:** https://genealogiasefardi.site  
- **Stack:** PHP 8.x + MySQL (PDO). Sin Composer ni Laravel (apto para HostGator shared / sin SSH).  
- **UI:** español.

## Credenciales demo (seed)

Tras ejecutar el instalador:

| Campo | Valor |
|-------|-------|
| Correo | `admin@genealogiasefardi.site` |
| Contraseña | `ChangeMe8507!` |
| Rol | admin |

**Cambie la contraseña** en cuanto entre la primera vez.

La familia demo (Navarro–Benveniste) es **ficticia**; no proviene de FamilySearch ni de archivos reales.

## Estructura

```
/
  index.php          Front controller
  install.php        Instalador one-shot (schema + seed)
  .htaccess          Pretty URLs + denegación de rutas sensibles
  config.sample.php  Plantilla de configuración
  config.php         Config real (NO subir a git público)
  schema.sql         Esquema MySQL
  src/               Router, Auth, Db, controladores
  views/             Plantillas PHP
  public/assets/     CSS
  storage/uploads/   Medios subidos
```

## Configuración

1. Copie `config.sample.php` → `config.php`, o deje que `install.php` / el deploy usen `.hostgator-db.json`.
2. Rellene host, nombre de BD, usuario y contraseña MySQL de cPanel.
3. No commitee `config.php`, `.env` ni `.hostgator-db.json` (están en `.gitignore`).

El cargador acepta `config.php` generado; el instalador también puede leer `.hostgator-db.json` en el mismo directorio.

## Despliegue SFTP a HostGator

1. En cPanel cree la base MySQL y el usuario (ya existente en el plan si se provisionó).
2. Con FileZilla / WinSCP / Cursor SFTP, conecte al host (p. ej. `garga.com.mx` o el hostname de HostGator) con el usuario SFTP del hosting.
3. Suba **todo el contenido** de este directorio al **document root** del dominio `genealogiasefardi.site` (suele ser `public_html/` o un subdirectorio addon-domain).  
   - `index.php` debe quedar en la raíz del sitio (no dentro de `public/`).
4. Asegúrese de subir también:
   - `config.php` (con credenciales) **o** `.hostgator-db.json`
   - carpetas `src/`, `views/`, `public/`, `storage/` (con permisos de escritura en `storage/` y `storage/uploads/`: `755` o `775`)
5. Abra en el navegador: `https://genealogiasefardi.site/install.php`
6. Lea el token en `storage/install.token` vía SFTP y péguelo en el formulario.
7. Tras el éxito, el instalador crea `storage/install.lock` y deja de aceptar re-ejecuciones. Puede borrar o renombrar `install.php` por seguridad.
8. Entre en `/login` con el admin demo y cambie la contraseña.

### Permisos recomendados

- Directorios: `755`
- Archivos PHP: `644`
- `storage/` y `storage/uploads/`: `775` (para que PHP pueda escribir uploads y el token/lock)

### Pretty URLs

Requiere `mod_rewrite` (activo en HostGator). El `.htaccess` reescribe a `index.php` y bloquea acceso directo a `config.php`, `src/`, `schema.sql` y `storage/` (excepto `storage/uploads/` para imágenes/PDF).

## Desarrollo local

```bash
# Sintaxis
find . -name '*.php' -print0 | xargs -0 -n1 php -l

# Servidor embebido (sin rewrite: use rutas ? o un router stub)
php -S localhost:8080
# Luego abra http://localhost:8080/install.php
```

En local necesitará MySQL accesible con las mismas credenciales de `config.php`, o ajuste `config.php` a una BD local.

## Roles

| Rol | Capacidades |
|-----|-------------|
| admin | Todo + definir persona raíz del árbol |
| editor | Crear/editar personas, fuentes, memorias, pruebas |
| viewer | Solo lectura |

## Módulos MVP

1. Auth (sesión + CSRF + roles)  
2. Panel / dashboard  
3. Personas (lista, búsqueda, CRUD, pestañas Detalles / Fuentes / Memorias / Línea de tiempo)  
4. Pedigree 3–4 generaciones desde `settings.root_person_id`  
5. Fuentes CRUD + adjunto a persona con *reason statement* + visor de imagen  
6. Memorias / galería + etiquetas a personas  
7. Cadenas de prueba + eslabones a fuentes  
8. Búsqueda global (personas + fuentes)  
9. Seed demo + instalador protegido por token  

## Seguridad

- Contraseñas con `password_hash` (bcrypt/argon según PHP).  
- Tokens CSRF en formularios POST.  
- Instalador de un solo uso (`install.lock`).  
- No exponer la contraseña de MySQL en este README ni en commits.
