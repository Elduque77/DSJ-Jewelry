# Despliegue de DSJ en EC2 (Amazon Linux 2023)

Guía para la instancia que nos compartieron:

```
ec2-user@ec2-54-242-164-197.compute-1.amazonaws.com
```

El usuario `ec2-user` indica que es **Amazon Linux** (Ubuntu usaría `ubuntu`),
así que aquí se usa `dnf`, el usuario web es `nginx` y el servicio es `php-fpm`.
La región es **us-east-1** (`compute-1`). Si al entrar resulta que es Ubuntu,
usa [DESPLIEGUE-EC2-ubuntu.md](DESPLIEGUE-EC2-ubuntu.md) en su lugar.

| Componente | En el servidor | Nota |
|---|---|---|
| PHP | **8.4** (`dnf install php8.4`) | AL2023 trae 8.1–8.5 en sus repos oficiales. **Obligatorio ≥ 8.4.1**: el `composer.lock` usa Symfony 8 |
| MySQL | 8.4 LTS (repo de Oracle) | AL2023 **no** trae MySQL server; sí trae MariaDB. Usamos el repo oficial para igualar el `mysql:8.4` de desarrollo |
| Nginx | del repo de AL2023 | Sitios en `/etc/nginx/conf.d/`, no en `sites-available` |
| Node | 22 | Solo si compilas assets en el servidor; el paso 8 explica cómo evitarlo |

La app no usa colas, tareas programadas, correo ni subida de archivos, así que
**no** hace falta worker, cron de Laravel ni `php artisan storage:link`.

---

## Paso 0 — Dos bloqueos que dependen de quien creó la instancia

Lo comprobé desde fuera y ninguno se resuelve desde tu lado:

### a) Falta la llave privada (`.pem`)

El puerto 22 responde, pero el servidor solo acepta autenticación por clave
pública, no contraseña:

```
ec2-user@ec2-54-242-164-197...: Permission denied (publickey,gssapi-keyex,gssapi-with-mic)
```

Pídele a quien creó la instancia **el archivo `.pem`** del key pair. Alternativa
más limpia si no quiere compartir su llave: que agregue tu clave pública a
`/home/ec2-user/.ssh/authorized_keys`. Genera la tuya con:

```bash
ssh-keygen -t ed25519 -C "diego-dsj"
cat ~/.ssh/id_ed25519.pub   # esto es lo que le mandas
```

### b) El puerto 80 está cerrado

```
curl -I http://ec2-54-242-164-197.compute-1.amazonaws.com/
→ Failed to connect ... port 80: Couldn't connect to server
```

El security group no permite HTTP. **Aunque el despliegue quede perfecto, el
sitio no será accesible** hasta que el dueño de la cuenta AWS agregue una regla
de entrada:

| Tipo | Protocolo | Puerto | Origen |
|---|---|---|---|
| HTTP | TCP | 80 | `0.0.0.0/0` |

### c) Confirma el sistema operativo antes de instalar nada

En cuanto tengas acceso, lo primero:

```bash
ssh -i ~/dsj-key.pem ec2-user@ec2-54-242-164-197.compute-1.amazonaws.com
cat /etc/os-release && free -h && df -h / && nproc
```

Anota la RAM: si es 1 GB (t3.micro/t2.micro), el paso 3 es obligatorio.

> **Ojo con el nombre DNS:** `ec2-54-242-164-197...` es el DNS público
> automático y **cambia si la instancia se detiene y se vuelve a encender**.
> Si eso pasa, hay que actualizar `APP_URL`. Para evitarlo, pide que le asignen
> una **Elastic IP**.

---

## Paso 1 — Conectarse

```bash
chmod 400 ~/dsj-key.pem
ssh -i ~/dsj-key.pem ec2-user@ec2-54-242-164-197.compute-1.amazonaws.com
```

Todo lo que sigue va dentro de la instancia. Y ojo: **la instancia es de otra
persona y puede estar compartida con el equipo.** Antes de instalar, revisa que
no haya algo ya corriendo que puedas romper:

```bash
sudo systemctl list-units --type=service --state=running | grep -iE "nginx|httpd|apache|php|mysql|maria|docker"
sudo ss -tlnp | grep -E ':(80|443|3306|8080)\b'
ls /var/www 2>/dev/null
```

Si ahí aparece Apache (`httpd`) o algo sirviendo en el 80, **pregunta antes de
seguir**: instalar Nginx le quitaría el puerto a lo que ya esté desplegado.

---

## Paso 2 — Actualizar y paquetes base

```bash
sudo dnf update -y
sudo dnf install -y nginx git tar unzip rsync
```

---

## Paso 3 — Swap (obligatorio si la instancia tiene 1 GB)

```bash
sudo dd if=/dev/zero of=/swapfile bs=1M count=2048 status=progress
sudo chmod 600 /swapfile
sudo mkswap /swapfile
sudo swapon /swapfile
echo '/swapfile none swap sw 0 0' | sudo tee -a /etc/fstab
free -h
```

> En Amazon Linux se usa `dd` en lugar de `fallocate`: el swap sobre un archivo
> con huecos (*sparse*) que crea `fallocate` puede fallar al activarse.

---

## Paso 4 — PHP 8.4

Primero mira qué hay disponible (los nombres son versionados, tipo `php8.4-fpm`):

```bash
dnf list available 'php8.4*' | head -40
```

Instala:

```bash
sudo dnf install -y php8.4 php8.4-fpm php8.4-cli php8.4-common \
    php8.4-mysqlnd php8.4-pdo php8.4-mbstring php8.4-xml php8.4-intl \
    php8.4-gd php8.4-bcmath php8.4-opcache
php -v          # debe decir 8.4.x (>= 8.4.1)
php -m | sort   # verifica: pdo_mysql, mbstring, intl, bcmath, dom, curl, openssl
```

> Si algún nombre de la lista no existe en tu versión de AL2023, quítalo del
> comando y vuelve a intentar; lo mínimo indispensable es
> `php8.4 php8.4-fpm php8.4-mysqlnd php8.4-mbstring php8.4-xml`.
>
> Si `php -m` no muestra `zip`, no pasa nada: Composer usa el binario `unzip`
> que instalamos en el paso 2.

---

## Paso 5 — Composer

```bash
curl -sS https://getcomposer.org/installer -o /tmp/composer-setup.php
sudo php /tmp/composer-setup.php --install-dir=/usr/local/bin --filename=composer
rm /tmp/composer-setup.php
composer -V
```

---

## Paso 6 — MySQL 8.4

Amazon Linux 2023 no trae MySQL server en sus repos, así que se agrega el
oficial de Oracle (AL2023 es compatible con los paquetes de EL9):

```bash
sudo rpm --import https://repo.mysql.com/RPM-GPG-KEY-mysql-2023
sudo dnf install -y https://dev.mysql.com/get/mysql84-community-release-el9-1.noarch.rpm
sudo dnf install -y mysql-community-server
sudo systemctl enable --now mysqld
```

MySQL genera una contraseña temporal de `root` en el log. Sácala y cámbiala:

```bash
sudo grep 'temporary password' /var/log/mysqld.log
sudo mysql_secure_installation   # te pedirá la temporal y luego una nueva
```

Crea la base y el usuario de la app (cambia la contraseña):

```bash
mysql -u root -p <<'SQL'
CREATE DATABASE dsj CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'dsj'@'127.0.0.1' IDENTIFIED BY 'CAMBIA_ESTA_PASSWORD';
GRANT ALL PRIVILEGES ON dsj.* TO 'dsj'@'127.0.0.1';
FLUSH PRIVILEGES;
SQL
```

Comprueba que **solo** escucha en local (debe decir `127.0.0.1:3306`):

```bash
sudo ss -tlnp | grep 3306
```

> MySQL 8.4 exige contraseñas que cumplan su política por defecto: mínimo 8
> caracteres con mayúscula, minúscula, número y símbolo. Si la rechaza, ese es
> el motivo.

---

## Paso 7 — Traer el código

```bash
sudo mkdir -p /var/www
sudo git clone https://github.com/<usuario>/<repo>.git /var/www/dsj
sudo git -C /var/www/dsj checkout Diego     # o la rama que vayas a desplegar
sudo chown -R nginx:nginx /var/www/dsj
```

Si el repo es privado, usa una *deploy key* de solo lectura:

```bash
sudo mkdir -p /var/www/.ssh && sudo chown nginx:nginx /var/www/.ssh
sudo -u nginx -H ssh-keygen -t ed25519 -f /var/www/.ssh/id_ed25519 -N "" -C "ec2-dsj"
sudo cat /var/www/.ssh/id_ed25519.pub
```

Esa clave pública va en GitHub → repo → **Settings → Deploy keys**, y luego
clonas con la URL SSH (`git@github.com:<usuario>/<repo>.git`).

---

## Paso 8 — Los assets de Vite: compílalos en tu máquina

`public/build/` está en `.gitignore`, así que hay que generarlo. **Recomendado:
compilarlo en tu WSL y subirlo**, en vez de instalar Node en el servidor:

```bash
# en tu máquina, dentro de /home/diego/DSJ
npm ci && npm run build
rsync -avz -e "ssh -i ~/dsj-key.pem" public/build/ \
    ec2-user@ec2-54-242-164-197.compute-1.amazonaws.com:/tmp/build/
```

Y en el servidor:

```bash
sudo rsync -a --delete /tmp/build/ /var/www/dsj/public/build/
sudo chown -R nginx:nginx /var/www/dsj/public/build
```

Así te ahorras Node en el servidor y el riesgo de que `npm run build` muera por
falta de memoria (es lo que más RAM consume de todo el despliegue).

<details>
<summary>Alternativa: compilar en el servidor</summary>

```bash
sudo dnf install -y nodejs22 || {
    curl -fsSL https://rpm.nodesource.com/setup_22.x | sudo bash -
    sudo dnf install -y nodejs
}
node -v   # necesita >= 22.12 (o 20.19+): Vite 8 no arranca con menos
sudo mkdir -p /var/www/.npm && sudo chown nginx:nginx /var/www/.npm
cd /var/www/dsj
sudo -u nginx -H npm_config_cache=/var/www/.npm npm ci --ignore-scripts
sudo -u nginx -H npm_config_cache=/var/www/.npm npm run build
```
</details>

---

## Paso 9 — Configurar el `.env`

```bash
sudo cp /var/www/dsj/deploy/.env.production.example /var/www/dsj/.env
sudo nano /var/www/dsj/.env
```

Rellena:
- `APP_URL=http://ec2-54-242-164-197.compute-1.amazonaws.com` (sin barra final)
- `DB_PASSWORD=` la del paso 6
- `GEMINI_API_KEY=` tu clave (solo aplica si despliegas una rama que ya tenga
  la integración con Gemini; en `Diego` ese servicio todavía no existe)

Protégelo, que lleva credenciales:

```bash
sudo chown nginx:nginx /var/www/dsj/.env
sudo chmod 640 /var/www/dsj/.env
```

> `APP_DEBUG` viene en `false`. **Déjalo así**: en `true`, cualquier error
> muestra el `.env` completo —contraseñas y API key incluidas— a quien visite
> el sitio.

---

## Paso 10 — Dependencias PHP, clave, migraciones y cachés

El orden importa: `key:generate` escribe en `.env`, así que va **antes** de
cachear la configuración.

```bash
sudo mkdir -p /var/www/.composer && sudo chown nginx:nginx /var/www/.composer
cd /var/www/dsj

sudo -u nginx -H COMPOSER_HOME=/var/www/.composer \
    composer install --no-dev --optimize-autoloader --no-interaction

sudo -u nginx php artisan key:generate --force
sudo -u nginx php artisan migrate --force --seed

sudo -u nginx php artisan config:cache
sudo -u nginx php artisan route:cache
sudo -u nginx php artisan view:cache
sudo -u nginx php artisan event:cache
```

`--seed` crea el catálogo de demostración y el administrador
`admin1@gmail.com` / `12345678`. **Cambia esa contraseña** antes de dejar el
sitio público.

---

## Paso 11 — Permisos

```bash
sudo chown -R nginx:nginx /var/www/dsj
sudo find /var/www/dsj -type d -exec chmod 755 {} +
sudo find /var/www/dsj -type f -exec chmod 644 {} +
sudo chmod -R 775 /var/www/dsj/storage /var/www/dsj/bootstrap/cache
sudo chmod 640 /var/www/dsj/.env
sudo chmod +x /var/www/dsj/artisan /var/www/dsj/deploy/deploy.sh
```

`storage/` y `bootstrap/cache/` son los únicos directorios donde Laravel
escribe. Si `nginx` no puede escribir ahí, el sitio responde 500.

---

## Paso 12 — Nginx y PHP-FPM

```bash
sudo cp /var/www/dsj/deploy/php-fpm-dsj-amazonlinux.conf /etc/php-fpm.d/zz-dsj.conf
sudo mkdir -p /var/log/php-fpm && sudo chown nginx:nginx /var/log/php-fpm
sudo systemctl enable --now php-fpm
sudo systemctl restart php-fpm

sudo cp /var/www/dsj/deploy/nginx/dsj-amazonlinux.conf /etc/nginx/conf.d/dsj.conf
sudo nginx -t
sudo systemctl enable --now nginx
sudo systemctl reload nginx
```

> **Detalle que muerde en Amazon Linux:** el pool de PHP-FPM viene configurado
> para correr como usuario `apache`, no `nginx`. El archivo
> `zz-dsj.conf` lo cambia a `nginx` para que un solo usuario sea dueño de todo.
> Sin eso, PHP escribiría en `storage/` como `apache` y tendrías errores de
> permisos difíciles de rastrear.

Revisa SELinux (AL2023 normalmente viene en `permissive`, que no estorba):

```bash
getenforce
```

Si dijera `Enforcing` y tuvieras 403/502 sin causa aparente:

```bash
sudo setsebool -P httpd_can_network_connect 1
sudo chcon -R -t httpd_sys_rw_content_t /var/www/dsj/storage
```

---

## Paso 13 — Verificar

Desde la instancia (esto funciona aunque el puerto 80 siga cerrado en el
security group, porque es tráfico local):

```bash
curl -I http://localhost
curl -s http://localhost | head -20
```

Desde tu navegador, una vez abierto el puerto 80:
`http://ec2-54-242-164-197.compute-1.amazonaws.com`

Si algo falla, los logs en orden de utilidad:

```bash
sudo tail -50 /var/www/dsj/storage/logs/laravel.log
sudo tail -50 /var/log/nginx/dsj-error.log
sudo journalctl -u php-fpm -n 50
```

---

## Paso 14 — Despliegues siguientes

```bash
sudo BRANCH=Diego SKIP_ASSETS=1 bash /var/www/dsj/deploy/deploy.sh
```

El script detecta solo el usuario web (`nginx`) y el servicio de FPM
(`php-fpm`). Con `SKIP_ASSETS=1` no compila en el servidor: recuerda subir
`public/build/` con el `rsync` del paso 8 cuando cambien los estilos o el JS.
Sin esa variable, intentará `npm ci && npm run build` en la instancia.

> `git reset --hard` **descarta** cambios hechos a mano en el servidor. Es
> intencional: el servidor es un espejo del repo.

---

## Problemas frecuentes

**502 Bad Gateway**
Nginx no encuentra el socket de FPM. Verifica que exista y que el dueño sea
`nginx`: `ls -l /run/php-fpm/www.sock`. Si el nombre es otro, corrige
`fastcgi_pass` en `/etc/nginx/conf.d/dsj.conf`. Luego
`sudo systemctl status php-fpm`.

**403 Forbidden en la raíz**
`nginx` no puede leer `/var/www/dsj/public`, o falta permiso de ejecución en un
directorio padre. Repite el paso 11 y comprueba `ls -ld /var/www /var/www/dsj`.

**Error 500 / página en blanco**
Casi siempre permisos de `storage/` o `APP_KEY` vacía. Mira
`storage/logs/laravel.log`.

**Carga pero sin estilos**
Falta `public/build/`. Comprueba que exista
`/var/www/dsj/public/build/manifest.json` (paso 8).

**`Access denied for user 'dsj'@'localhost'`**
Creamos el usuario como `'dsj'@'127.0.0.1'`. Asegúrate de que el `.env` diga
`DB_HOST=127.0.0.1` y no `localhost` (con `localhost`, MySQL usa el socket Unix
y busca otro usuario). Y nunca `mysql`, que era el nombre del contenedor de Sail.

**Cambié el `.env` y no surte efecto**
La configuración está cacheada: `sudo -u nginx php artisan config:cache`.

**El sitio funcionaba y ahora el DNS no resuelve**
Reiniciaron la instancia y cambió el DNS público. Pide una Elastic IP y
actualiza `APP_URL`.

**Necesito la base de datos desde mi PC**
No abras el 3306 ni instales phpMyAdmin. Túnel SSH y conecta tu cliente a
`127.0.0.1:3307`:

```bash
ssh -i ~/dsj-key.pem -L 3307:127.0.0.1:3306 ec2-user@ec2-54-242-164-197.compute-1.amazonaws.com
```

---

## Siguiente paso: HTTPS

Requiere un dominio propio apuntando a la instancia (Let's Encrypt no emite
certificados para nombres `*.amazonaws.com`). Con dominio:

```bash
sudo dnf install -y certbot python3-certbot-nginx
sudo certbot --nginx -d dsj.midominio.com
```

Después, en el `.env`: `APP_URL=https://...` y `SESSION_SECURE_COOKIE=true`,
seguido de `php artisan config:cache`.
