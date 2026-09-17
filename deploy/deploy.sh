#!/usr/bin/env bash
#
# Redespliegue de DSJ en la EC2 (para actualizaciones, no para la instalacion
# inicial: para esa, ver deploy/DESPLIEGUE-EC2.md).
#
# Uso:
#   sudo bash /var/www/dsj/deploy/deploy.sh
#   sudo BRANCH=Samuel bash /var/www/dsj/deploy/deploy.sh
#
set -euo pipefail

APP_DIR="${APP_DIR:-/var/www/dsj}"
BRANCH="${BRANCH:-main}"
WEB_USER="${WEB_USER:-www-data}"

# Composer y npm necesitan un HOME escribible; www-data no tiene uno propio.
export COMPOSER_HOME="${COMPOSER_HOME:-/var/www/.composer}"
export NPM_CACHE_DIR="${NPM_CACHE_DIR:-/var/www/.npm}"

# Ejecuta un comando como el usuario del servidor web, para que los archivos
# generados (cache, logs, vendor) no queden con dueño root.
as_web() {
    sudo -u "$WEB_USER" -H \
        COMPOSER_HOME="$COMPOSER_HOME" \
        COMPOSER_ALLOW_SUPERUSER=0 \
        npm_config_cache="$NPM_CACHE_DIR" \
        bash -lc "cd '$APP_DIR' && $1"
}

if [[ $EUID -ne 0 ]]; then
    echo "Ejecutalo con sudo: sudo bash $0" >&2
    exit 1
fi

cd "$APP_DIR"

mkdir -p "$COMPOSER_HOME" "$NPM_CACHE_DIR"
chown "$WEB_USER":"$WEB_USER" "$COMPOSER_HOME" "$NPM_CACHE_DIR"

echo "==> Modo mantenimiento"
as_web "php artisan down --retry=15" || true
# Pase lo que pase, el sitio vuelve a levantarse al salir.
trap 'as_web "php artisan up" || true' EXIT

echo "==> Traer codigo de origin/$BRANCH"
as_web "git fetch origin --prune"
as_web "git reset --hard 'origin/$BRANCH'"

echo "==> Dependencias PHP (sin paquetes de desarrollo)"
as_web "composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist"

echo "==> Dependencias JS y build de assets"
if [[ -f package-lock.json ]]; then
    as_web "npm ci --ignore-scripts"
else
    echo "    (aviso: no hay package-lock.json, usando npm install)"
    as_web "npm install --ignore-scripts"
fi
as_web "npm run build"

echo "==> Migraciones"
as_web "php artisan migrate --force"

echo "==> Regenerar caches de produccion"
as_web "php artisan optimize:clear"
as_web "php artisan config:cache"
as_web "php artisan route:cache"
as_web "php artisan view:cache"
as_web "php artisan event:cache"

echo "==> Permisos"
chown -R "$WEB_USER":"$WEB_USER" "$APP_DIR/storage" "$APP_DIR/bootstrap/cache"
find "$APP_DIR/storage" "$APP_DIR/bootstrap/cache" -type d -exec chmod 775 {} +

echo "==> Recargar PHP-FPM"
systemctl reload php8.4-fpm

echo "==> Listo. Sitio arriba de nuevo."
