#!/usr/bin/env bash
#
# Instalacion inicial de DSJ en una EC2 con Amazon Linux 2023 (SQLite, Nginx,
# PHP 8.4) y registro automatico de la IP en DuckDNS. Automatiza los pasos 2-12
# de deploy/DESPLIEGUE-EC2.md. Es idempotente: se puede repetir sin romper nada.
#
# Uso (desde la instancia):
#   sudo REPO_URL=https://github.com/<usuario>/<repo>.git \
#        DUCKDNS_DOMAIN=mi-subdominio \
#        DUCKDNS_TOKEN=xxxxxxxx-xxxx-... \
#        bash setup-ec2.sh
#
# Variables opcionales: BRANCH (main), APP_DIR (/var/www/dsj), SKIP_ASSETS (1).
# Si el repo es privado, configura antes una deploy key (ver la guia, paso 7).
#
set -euo pipefail

REPO_URL="${REPO_URL:?Define REPO_URL}"
DUCKDNS_DOMAIN="${DUCKDNS_DOMAIN:?Define DUCKDNS_DOMAIN (solo el subdominio)}"
BRANCH="${BRANCH:-main}"
APP_DIR="${APP_DIR:-/var/www/dsj}"
WEB_USER=nginx

if [[ $EUID -ne 0 ]]; then
    echo "Ejecutalo con sudo." >&2
    exit 1
fi

if [[ -z "${DUCKDNS_TOKEN:-}" && ! -f /etc/duckdns.env ]]; then
    echo "Define DUCKDNS_TOKEN (o crea /etc/duckdns.env)." >&2
    exit 1
fi

# El puerto 80 es compartido en una instancia de curso: no pisar otro sitio.
if ss -tlnH 'sport = :80' | grep -q . && ! systemctl is-active --quiet nginx; then
    echo "Algo distinto de Nginx ya escucha en el 80. Revisalo antes de seguir." >&2
    exit 1
fi

echo "==> Paquetes base"
dnf install -y nginx git tar unzip rsync sqlite

echo "==> Swap de 2 GB (solo si no hay)"
if ! swapon --show --noheadings | grep -q .; then
    dd if=/dev/zero of=/swapfile bs=1M count=2048 status=progress
    chmod 600 /swapfile
    mkswap /swapfile
    swapon /swapfile
    grep -q '^/swapfile' /etc/fstab || echo '/swapfile none swap sw 0 0' >> /etc/fstab
fi

echo "==> PHP 8.4"
dnf install -y php8.4 php8.4-fpm php8.4-cli php8.4-common \
    php8.4-pdo php8.4-mbstring php8.4-xml php8.4-intl \
    php8.4-gd php8.4-bcmath php8.4-opcache

echo "==> Composer"
if ! command -v composer >/dev/null 2>&1; then
    curl -sS https://getcomposer.org/installer -o /tmp/composer-setup.php
    php /tmp/composer-setup.php --install-dir=/usr/local/bin --filename=composer
    rm -f /tmp/composer-setup.php
fi

echo "==> DuckDNS ($DUCKDNS_DOMAIN.duckdns.org)"
if [[ -n "${DUCKDNS_TOKEN:-}" ]]; then
    umask 077
    printf 'DUCKDNS_DOMAIN=%s\nDUCKDNS_TOKEN=%s\n' "$DUCKDNS_DOMAIN" "$DUCKDNS_TOKEN" > /etc/duckdns.env
    chmod 600 /etc/duckdns.env
    umask 022
fi

echo "==> Codigo de $BRANCH"
mkdir -p "$(dirname "$APP_DIR")"
if [[ ! -d "$APP_DIR/.git" ]]; then
    git clone --branch "$BRANCH" "$REPO_URL" "$APP_DIR"
fi
chown -R "$WEB_USER":"$WEB_USER" "$APP_DIR"

# Los archivos de duckdns y el resto de deploy/ vienen del repo clonado.
install -m 755 "$APP_DIR/deploy/duckdns/duckdns-update.sh" /usr/local/bin/duckdns-update.sh
install -m 644 "$APP_DIR/deploy/duckdns/duckdns-update.service" /etc/systemd/system/duckdns-update.service
install -m 644 "$APP_DIR/deploy/duckdns/duckdns-update.timer" /etc/systemd/system/duckdns-update.timer
systemctl daemon-reload
systemctl enable --now duckdns-update.timer
systemctl start duckdns-update.service

echo "==> .env de produccion (solo si no existe)"
if [[ ! -f "$APP_DIR/.env" ]]; then
    cp "$APP_DIR/deploy/.env.production.example" "$APP_DIR/.env"
    sed -i "s|^APP_URL=.*|APP_URL=http://${DUCKDNS_DOMAIN}.duckdns.org|" "$APP_DIR/.env"
    sed -i "s|^DB_DATABASE=.*|DB_DATABASE=${APP_DIR}/database/database.sqlite|" "$APP_DIR/.env"
    chown "$WEB_USER":"$WEB_USER" "$APP_DIR/.env"
    chmod 640 "$APP_DIR/.env"
    FIRST_INSTALL=1
else
    FIRST_INSTALL=0
fi

echo "==> Base de datos SQLite"
touch "$APP_DIR/database/database.sqlite"
chown "$WEB_USER":"$WEB_USER" "$APP_DIR/database" "$APP_DIR/database/database.sqlite"
chmod 775 "$APP_DIR/database"
chmod 664 "$APP_DIR/database/database.sqlite"
sudo -u "$WEB_USER" sqlite3 "$APP_DIR/database/database.sqlite" "PRAGMA journal_mode=WAL;"

echo "==> PHP-FPM y Nginx"
cp "$APP_DIR/deploy/php-fpm-dsj-amazonlinux.conf" /etc/php-fpm.d/zz-dsj.conf
mkdir -p /var/log/php-fpm && chown "$WEB_USER":"$WEB_USER" /var/log/php-fpm
cp "$APP_DIR/deploy/nginx/dsj-amazonlinux.conf" /etc/nginx/conf.d/dsj.conf
nginx -t
systemctl enable --now php-fpm nginx
systemctl restart php-fpm

# Clave y datos de demostracion solo en la primera instalacion: en las
# siguientes, regenerar la clave cerraria todas las sesiones.
if [[ "$FIRST_INSTALL" == "1" ]]; then
    echo "==> APP_KEY y semillas (primera instalacion)"
    mkdir -p /var/www/.composer && chown "$WEB_USER":"$WEB_USER" /var/www/.composer
    sudo -u "$WEB_USER" -H COMPOSER_HOME=/var/www/.composer bash -lc \
        "cd '$APP_DIR' && composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist \
         && php artisan key:generate --force \
         && php artisan migrate --force --seed"
    echo "    AVISO: cambia ya la contrasena del admin sembrado (admin1@gmail.com / 12345678)."
fi

echo "==> Despliegue de $BRANCH"
BRANCH="$BRANCH" APP_DIR="$APP_DIR" SKIP_ASSETS="${SKIP_ASSETS:-1}" bash "$APP_DIR/deploy/deploy.sh"

echo "==> Listo: http://${DUCKDNS_DOMAIN}.duckdns.org"
echo "    Recuerda subir public/build/ si SKIP_ASSETS=1 (guia, paso 8)."
