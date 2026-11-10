# NOTA: esta guia es para instancias **Ubuntu** (usuario ssh `ubuntu`). Si tu
# instancia es Amazon Linux (usuario `ec2-user`), usa DESPLIEGUE-EC2.md.

# Despliegue de DSJ en una instancia EC2

Guía para poner DSJ en producción en una sola instancia EC2 con
**Nginx + PHP-FPM nativo + MySQL local**, accesible por la **IP pública** vía HTTP.

Nada de Sail aquí: `compose.yaml` es el entorno de **desarrollo**. En el servidor
corremos PHP y MySQL directamente, que consume mucha menos RAM y es lo estándar
para Laravel.

| Componente | Versión en el servidor | Por qué |
|---|---|---|
| Ubuntu Server | 24.04 LTS | AMI oficial, soporte hasta 2029 |
| PHP | **8.4** (FPM) vía PPA `ondrej/php` | Obligatorio: el `composer.lock` trae Symfony 8, que exige **PHP ≥ 8.4.1**. El PHP 8.3 que viene por defecto en Ubuntu 24.04 **no sirve** |
| MySQL | 8.0 (repos de Ubuntu) | Compatible con las migraciones del proyecto |
| Node | 22 LTS | Vite 8 exige `^20.19 || >=22.12` |
| Nginx | del repo de Ubuntu | Sirve `public/` y pasa PHP al socket de FPM |

Esta app **no** usa colas, tareas programadas, correo ni subida de archivos
(no hay `Storage::` en el código), así que **no** hace falta worker de colas,
cron de Laravel ni `php artisan storage:link`.

---

## Antes de empezar: commitea el `package-lock.json`

Hoy está sin versionar (`git status` lo muestra como `??`). Sin él, el servidor
instalará versiones de JS distintas a las tuyas y `npm ci` fallará:

```bash
git add package-lock.json && git commit -m "Agrega package-lock.json para builds reproducibles"
```

---

## 1. Crear la instancia

En la consola de EC2 → **Launch instance**:

| Campo | Valor |
|---|---|
| AMI | **Ubuntu Server 24.04 LTS** (x86_64) |
| Tipo | **t3.small** (2 GB RAM) recomendado. `t3.micro`/`t2.micro` (1 GB, free tier) funciona **solo si creas swap** — paso 3 |
| Key pair | Crea uno nuevo (`dsj-key.pem`) y guárdalo bien: sin él no entras |
| Almacenamiento | 20 GB gp3 |

**Security group** — solo dos reglas de entrada:

| Tipo | Puerto | Origen |
|---|---|---|
| SSH | 22 | **Mi IP** (no `0.0.0.0/0`) |
| HTTP | 80 | `0.0.0.0/0` |

No abras el 3306: MySQL solo escuchará en `127.0.0.1`.

**Asigna una Elastic IP** a la instancia (EC2 → Elastic IPs → Allocate → Associate).
Si no, la IP pública cambia cada vez que apagas y enciendes la instancia, y te toca
volver a editar `APP_URL`.

---

## 2. Conectarse

```bash
chmod 400 ~/dsj-key.pem
ssh -i ~/dsj-key.pem ubuntu@<IP_PUBLICA>
```

Todo lo que sigue va dentro de la instancia.

---

## 3. Swap (obligatorio en instancias de 1 GB)

`npm run build` con Vite es lo que más memoria consume de todo el despliegue y es
la causa típica del error `Killed` / build que muere sin explicación en t3.micro.

```bash
sudo fallocate -l 2G /swapfile
sudo chmod 600 /swapfile
sudo mkswap /swapfile
sudo swapon /swapfile
echo '/swapfile none swap sw 0 0' | sudo tee -a /etc/fstab
free -h
```

---

## 4. Instalar el stack

```bash
sudo apt update && sudo apt upgrade -y
sudo apt install -y nginx git unzip curl software-properties-common
```

### PHP 8.4

```bash
sudo add-apt-repository -y ppa:ondrej/php
sudo apt update
sudo apt install -y php8.4-fpm php8.4-cli php8.4-mysql php8.4-mbstring \
    php8.4-xml php8.4-curl php8.4-zip php8.4-bcmath php8.4-intl php8.4-gd
php -v   # debe decir 8.4.x
```

### Composer

```bash
curl -sS https://getcomposer.org/installer -o /tmp/composer-setup.php
sudo php /tmp/composer-setup.php --install-dir=/usr/local/bin --filename=composer
rm /tmp/composer-setup.php
composer -V
```

> Se instala con el script oficial a propósito: `apt install composer` arrastra
> dependencias de PHP 8.3 y te deja dos versiones de PHP peleando.

### Node 22

```bash
curl -fsSL https://deb.nodesource.com/setup_22.x | sudo -E bash -
sudo apt install -y nodejs
node -v   # v22.x
```

### MySQL

```bash
sudo apt install -y mysql-server
sudo systemctl enable --now mysql
sudo mysql_secure_installation   # pon contraseña de root, responde "y" a todo lo demás
```

Crear la base y el usuario de la aplicación (cambia la contraseña):

```bash
sudo mysql <<'SQL'
CREATE DATABASE dsj CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'dsj'@'127.0.0.1' IDENTIFIED BY 'CAMBIA_ESTA_PASSWORD';
GRANT ALL PRIVILEGES ON dsj.* TO 'dsj'@'127.0.0.1';
FLUSH PRIVILEGES;
SQL
```

Comprueba que MySQL **no** está expuesto a internet (debe decir `127.0.0.1:3306`):

```bash
sudo ss -tlnp | grep 3306
```

---

## 5. Traer el código

```bash
sudo mkdir -p /var/www
sudo git clone https://github.com/<usuario>/<repo>.git /var/www/dsj
sudo git -C /var/www/dsj checkout main
```

**Si el repositorio es privado**, usa una *deploy key* en lugar de tu contraseña:

```bash
sudo mkdir -p /var/www/.ssh && sudo chown www-data:www-data /var/www/.ssh
sudo -u www-data -H ssh-keygen -t ed25519 -f /var/www/.ssh/id_ed25519 -N "" -C "ec2-dsj"
sudo cat /var/www/.ssh/id_ed25519.pub
```

Copia esa clave pública en GitHub → repo → **Settings → Deploy keys → Add deploy key**
(solo lectura), y clona con la URL SSH (`git@github.com:<usuario>/<repo>.git`).

Dejar todo a nombre de `www-data`:

```bash
sudo chown -R www-data:www-data /var/www/dsj
```

---

## 6. Configurar el `.env`

```bash
sudo cp /var/www/dsj/deploy/.env.production.example /var/www/dsj/.env
sudo nano /var/www/dsj/.env
```

Rellena:
- `APP_URL=http://<IP_PUBLICA>` (sin barra final)
- `DB_PASSWORD=` la que pusiste en el paso 4
- `GEMINI_API_KEY=` tu clave, o la sección de recomendaciones del home dará error

Y protégelo (contiene credenciales; solo `www-data` debe poder leerlo):

```bash
sudo chown www-data:www-data /var/www/dsj/.env
sudo chmod 640 /var/www/dsj/.env
```

> `APP_DEBUG` ya viene en `false` en la plantilla. **Déjalo así**: en `true`,
> cualquier error muestra tu `.env` completo, con contraseñas y API key, a quien
> visite el sitio.

---

## 7. Instalar dependencias y compilar

```bash
sudo mkdir -p /var/www/.composer /var/www/.npm
sudo chown www-data:www-data /var/www/.composer /var/www/.npm
cd /var/www/dsj

sudo -u www-data -H COMPOSER_HOME=/var/www/.composer \
    composer install --no-dev --optimize-autoloader --no-interaction

sudo -u www-data -H npm_config_cache=/var/www/.npm npm ci --ignore-scripts
sudo -u www-data -H npm_config_cache=/var/www/.npm npm run build
```

`--no-dev` deja fuera PHPUnit, Sail, Pint y compañía: no tienen nada que hacer en
producción. `npm run build` genera `public/build/`, que está en `.gitignore` y por
eso hay que compilarlo **en el servidor** en cada despliegue.

---

## 8. Clave de app, migraciones y cachés

El orden importa: `key:generate` escribe en `.env`, así que va **antes** de cachear
la configuración.

```bash
cd /var/www/dsj
sudo -u www-data php artisan key:generate --force
sudo -u www-data php artisan migrate --force --seed

sudo -u www-data php artisan config:cache
sudo -u www-data php artisan route:cache
sudo -u www-data php artisan view:cache
sudo -u www-data php artisan event:cache
```

`--seed` crea el catálogo de demostración y el administrador
`admin1@gmail.com` / `12345678`. **Cambia esa contraseña** antes de dejar el sitio
público, o no siembres y crea el admin a mano.

---

## 9. Permisos

```bash
sudo chown -R www-data:www-data /var/www/dsj
sudo find /var/www/dsj -type d -exec chmod 755 {} +
sudo find /var/www/dsj -type f -exec chmod 644 {} +
sudo chmod -R 775 /var/www/dsj/storage /var/www/dsj/bootstrap/cache
sudo chmod 640 /var/www/dsj/.env
sudo chmod +x /var/www/dsj/artisan /var/www/dsj/deploy/deploy.sh
```

`storage/` y `bootstrap/cache/` son los dos únicos directorios donde Laravel
escribe. Si no son escribibles por `www-data`, el sitio responde 500.

---

## 10. Nginx y PHP-FPM

```bash
sudo cp /var/www/dsj/deploy/php-fpm-dsj-ubuntu.conf /etc/php/8.4/fpm/pool.d/zz-dsj.conf
sudo systemctl restart php8.4-fpm

sudo cp /var/www/dsj/deploy/nginx/dsj-ubuntu.conf /etc/nginx/sites-available/dsj
sudo ln -sfn /etc/nginx/sites-available/dsj /etc/nginx/sites-enabled/dsj
sudo rm -f /etc/nginx/sites-enabled/default
sudo nginx -t
sudo systemctl reload nginx
sudo systemctl enable nginx php8.4-fpm
```

---

## 11. Verificar

Desde la instancia:

```bash
curl -I http://localhost              # 200 OK
curl -s http://localhost | head -20   # HTML del home
```

Desde tu navegador: `http://<IP_PUBLICA>`

Si algo falla, los logs en orden de utilidad:

```bash
sudo tail -50 /var/www/dsj/storage/logs/laravel.log
sudo tail -50 /var/log/nginx/dsj-error.log
sudo journalctl -u php8.4-fpm -n 50
```

---

## 12. Despliegues siguientes

Ya está todo instalado; para subir cambios basta con:

```bash
sudo bash /var/www/dsj/deploy/deploy.sh
```

El script pone el sitio en mantenimiento, hace `git reset --hard origin/main`,
reinstala dependencias, recompila assets, corre migraciones, regenera las cachés
y vuelve a levantar el sitio. Para desplegar otra rama:

```bash
sudo BRANCH=Samuel bash /var/www/dsj/deploy/deploy.sh
```

> Ojo: `git reset --hard` **descarta** cualquier cambio hecho a mano en el
> servidor. Es intencional — el servidor debe ser un espejo del repo.

---

## 13. Problemas frecuentes

**502 Bad Gateway**
Nginx no encuentra el socket de FPM. Verifica que el nombre coincida:
`ls /run/php/` debe mostrar `php8.4-fpm.sock`. Si ahí hay otra versión, corrige la
línea `fastcgi_pass` del sitio. Luego `sudo systemctl status php8.4-fpm`.

**Error 500 y página en blanco**
Casi siempre permisos de `storage/`, o `APP_KEY` vacía. Revisa
`storage/logs/laravel.log` y repite el paso 9.

**La página carga pero sin estilos (todo texto plano)**
Falta `npm run build`, o falló. Comprueba que exista
`/var/www/dsj/public/build/manifest.json`.

**`npm run build` muere con `Killed`**
Se quedó sin RAM. Crea el swap del paso 3. Como alternativa, compila en tu máquina
y sube solo la carpeta:
`rsync -avz -e "ssh -i ~/dsj-key.pem" public/build/ ubuntu@<IP>:/tmp/build/ && sudo rsync -a /tmp/build/ /var/www/dsj/public/build/`

**`SQLSTATE[HY000] [1045] Access denied`**
`DB_PASSWORD` del `.env` no coincide con la de MySQL, o `DB_HOST` quedó en `mysql`
(el nombre del contenedor de Sail). En producción debe ser `127.0.0.1`.

**Cambié el `.env` y no surte efecto**
La configuración está cacheada. `sudo -u www-data php artisan config:cache`.

**Composer se queja de la versión de PHP**
Estás en 8.3. Symfony 8 exige ≥ 8.4.1: repite la instalación de PHP 8.4 y
comprueba con `php -v` y `sudo update-alternatives --config php`.

**Necesito entrar a la base de datos desde mi PC**
No abras el 3306 ni instales phpMyAdmin. Usa un túnel SSH y conecta tu cliente a
`127.0.0.1:3307`:

```bash
ssh -i ~/dsj-key.pem -L 3307:127.0.0.1:3306 ubuntu@<IP_PUBLICA>
```

---

## 14. Siguiente paso: dominio y HTTPS

Cuando tengan dominio, apunta un registro **A** a la Elastic IP y luego:

```bash
sudo apt install -y certbot python3-certbot-nginx
sudo certbot --nginx -d dsj.midominio.com
```

Después, en el `.env`: `APP_URL=https://dsj.midominio.com` y
`SESSION_SECURE_COOKIE=true`, seguido de `php artisan config:cache`.
En `deploy/nginx/dsj-ubuntu.conf`, reemplaza `server_name _;` por tu dominio.
