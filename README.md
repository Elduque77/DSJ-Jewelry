# DSJ — E-commerce de joyería
# usar wsl

Proyecto Laravel 13 con entorno de desarrollo en Docker vía **Laravel Sail**.
Los tres integrantes trabajamos contra exactamente la misma base de datos MySQL,
sin instalar PHP ni MySQL en la máquina.

| Componente | Versión / imagen |
|---|---|
| Laravel | 13.x (PHP 8.5 dentro del contenedor) |
| MySQL | `mysql:8.4` |
| phpMyAdmin | `phpmyadmin:latest` |

---

## 1. Requisitos

### Todos
- [Docker Desktop](https://www.docker.com/products/docker-desktop/) instalado y **corriendo**.
- Git.

### Windows (2 de nosotros)
1. WSL2 instalado (`wsl --install` en PowerShell como administrador).
2. En Docker Desktop: **Settings → Resources → WSL Integration** → activar tu distro
   (ej. `Ubuntu`) → **Apply & Restart**.
3. **Clona el proyecto DENTRO del filesystem de WSL** (`~/`), no en `C:\`.

   > ⚠️ Esto no es opcional. En `/mnt/c/...` los bind mounts de Docker son
   > lentísimos y el hot-reload de Vite falla de forma intermitente. Dentro de
   > WSL el rendimiento es igual al de macOS.

   Para editar el código con VS Code: instala la extensión **WSL** y abre la
   carpeta con `code .` desde la terminal de Ubuntu. También puedes llegar desde
   el explorador de Windows con `\wsl.localhost\Ubuntu\home\TU_USUARIO\DSJ`.

   **Todos los comandos `sail` van en la terminal de Ubuntu, nunca en PowerShell.**

### macOS
Solo Docker Desktop. Sail corre nativo, sin pasos extra.
Las imágenes de MySQL y phpMyAdmin son multi-arch, así que funcionan en Apple
Silicon (M1–M4) sin tocar nada.

---

## 2. Puesta en marcha desde cero

```bash
git clone <URL-DEL-REPO> DSJ
cd DSJ
```

### Paso 1 — Instalar dependencias de PHP

En un clon limpio la carpeta `vendor/` no existe todavía, así que **`./vendor/bin/sail`
aún no está**. Este primer paso se hace con un contenedor de Composer desechable:

```bash
docker run --rm -v "$(pwd)":/var/www/html -w /var/www/html laravelsail/php84-composer:latest composer install --ignore-platform-reqs
```

> Saltarse este paso es la causa #1 de "el README no me funciona".

### Paso 2 — Configurar el entorno

```bash
cp .env.example .env
```

### Paso 3 — Levantar los contenedores

```bash
./vendor/bin/sail up -d
```

La primera vez descarga imágenes y compila el contenedor de la app: tarda varios
minutos. Las siguientes son cuestión de segundos.

### Paso 4 — Generar la clave de la aplicación

```bash
./vendor/bin/sail artisan key:generate
```

### Paso 5 — Crear las tablas

```bash
./vendor/bin/sail artisan migrate
```

Listo. Si `migrate` termina sin errores, la conexión a MySQL funciona de punta a punta.

---

## 3. Accesos

| Servicio | URL / dirección | Credenciales |
|---|---|---|
| Aplicación | http://localhost | — |
| phpMyAdmin | http://localhost:8080 | usuario `sail` / contraseña `password` |
| MySQL (cliente externo) | `127.0.0.1:3307` | usuario `sail` / contraseña `password`, base `dsj` |
| Vite (dev server) | http://localhost:5173 | — |

### Por qué MySQL está en el puerto 3307

Dentro de la red de Docker MySQL sigue en el **3306** — por eso el `.env` dice
`DB_HOST=mysql` y `DB_PORT=3306`. Lo que cambia es el puerto que se publica en
**tu** máquina: el **3307**, para no chocar con instalaciones locales de MySQL
(XAMPP, Laragon, servicio MySQL de Windows) que ya estén usando el 3306.

Solo necesitas el 3307 si te conectas desde un cliente externo tipo TablePlus,
DBeaver o MySQL Workbench. La aplicación nunca lo usa.

Si el 3307 u 8080 también están ocupados en tu máquina, cámbialos en tu `.env`
(`FORWARD_DB_PORT` / `FORWARD_PHPMYADMIN_PORT`) — son locales y no afectan a nadie más.

---

## 4. Comandos del día a día

Todos desde la raíz del proyecto (en Windows: dentro de la terminal de Ubuntu).

```bash
./vendor/bin/sail up -d          # levantar en segundo plano
./vendor/bin/sail stop           # pausar (conserva los contenedores)
./vendor/bin/sail down           # bajar y borrar contenedores (los DATOS se conservan)
./vendor/bin/sail ps             # estado de los contenedores
./vendor/bin/sail logs -f        # ver logs en vivo

./vendor/bin/sail artisan migrate
./vendor/bin/sail artisan make:model Producto -mcr
./vendor/bin/sail artisan tinker
./vendor/bin/sail mysql          # cliente MySQL interactivo

./vendor/bin/sail composer require <paquete>
./vendor/bin/sail npm install
./vendor/bin/sail npm run dev    # Vite con hot-reload
./vendor/bin/sail test
```

> ⚠️ `./vendor/bin/sail down -v` **borra el volumen y con él toda la base de datos.**
> Úsalo solo si quieres empezar de cero. Para el uso normal, `down` a secas.

### Alias opcional

Para escribir `sail` en vez de `./vendor/bin/sail`, agrega a tu `~/.bashrc` (Linux/WSL)
o `~/.zshrc` (macOS):

```bash
alias sail='sh $([ -f sail ] && echo sail || echo vendor/bin/sail)'
```

---

## 5. Base de datos

- Nombre: **`dsj`** · usuario **`sail`** · contraseña **`password`**
- Los datos viven en un volumen de Docker llamado `dsj_sail-mysql` y **persisten**
  entre reinicios de los contenedores y de la máquina.
- Sail crea además una base `testing` que usan los tests automáticamente.

Las credenciales están en texto plano a propósito: son de desarrollo local, la base
solo escucha en tu propia máquina y `compose.yaml` las necesita para crear el
contenedor. Cuando integremos servicios reales (pasarela de pago, correo), esas
claves sí van en tu `.env` local y **nunca** en `.env.example`.

`.env` está en `.gitignore`. No lo subas.

---

## 6. Problemas frecuentes

**`php: command not found`, `composer: command not found`, o errores de extensiones PHP faltantes**
En este proyecto **no se instala PHP ni Composer en tu máquina**: todo corre dentro del contenedor,
que ya trae PHP 8.5 con todas las extensiones. Si ves alguno de esos errores, te faltó el prefijo `sail`:

| En vez de… | Usa |
|---|---|
| `php artisan migrate` | `./vendor/bin/sail artisan migrate` |
| `composer require paquete` | `./vendor/bin/sail composer require paquete` |
| `npm run dev` | `./vendor/bin/sail npm run dev` |

Aplica igual en Windows y en macOS. Si tenías un PHP instalado en el sistema, ignóralo: no es el
que usa el proyecto y solo genera confusión.

**`port is already allocated` al hacer `sail up`**
Otro proceso ocupa el 80, 3307, 8080 o 5173. Cambia el puerto correspondiente en tu
`.env` (`APP_PORT`, `FORWARD_DB_PORT`, `FORWARD_PHPMYADMIN_PORT`, `VITE_PORT`).

**`SQLSTATE[HY000] [2002] Connection refused`**
MySQL todavía está arrancando. Espera unos segundos y reintenta, o revisa
`./vendor/bin/sail ps` — el contenedor `mysql` debe decir `(healthy)`.

**`./vendor/bin/sail: No such file or directory`**
Te faltó el Paso 1 (instalar dependencias con el contenedor de Composer).

**Errores de permisos en `storage/` o `bootstrap/cache`**

```bash
./vendor/bin/sail root-shell -c "chown -R sail:sail /var/www/html/storage /var/www/html/bootstrap/cache"
```

**Windows: todo va lentísimo o Vite no recarga**
El proyecto está en `/mnt/c/...`. Muévelo al filesystem de WSL (`~/DSJ`).

**macOS Apple Silicon: una imagen no arranca**
Agrega `platform: linux/amd64` a ese servicio en `compose.yaml`. Con las imágenes
actuales no debería hacer falta: todas tienen build nativo arm64.
