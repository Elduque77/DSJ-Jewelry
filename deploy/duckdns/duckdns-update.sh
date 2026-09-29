#!/usr/bin/env bash
#
# Registra la IP publica actual de la instancia en DuckDNS.
# Se ejecuta en cada arranque (y cada 5 min) desde duckdns-update.timer, porque
# la IP publica de la EC2 cambia cada vez que se apaga y se vuelve a encender.
#
# Lee /etc/duckdns.env (chmod 600, fuera del repo):
#   DUCKDNS_DOMAIN=mi-subdominio      # solo el subdominio, sin .duckdns.org
#   DUCKDNS_TOKEN=xxxxxxxx-xxxx-...
#
set -euo pipefail

ENV_FILE="${DUCKDNS_ENV_FILE:-/etc/duckdns.env}"

if [[ ! -r "$ENV_FILE" ]]; then
    echo "No se puede leer $ENV_FILE" >&2
    exit 1
fi

# shellcheck disable=SC1090
source "$ENV_FILE"

if [[ -z "${DUCKDNS_DOMAIN:-}" || -z "${DUCKDNS_TOKEN:-}" ]]; then
    echo "Faltan DUCKDNS_DOMAIN o DUCKDNS_TOKEN en $ENV_FILE" >&2
    exit 1
fi

# Sin "ip=", DuckDNS usa la IP desde la que llega la peticion, que es la
# publica de la instancia.
RESPONSE="$(curl -fsS --max-time 20 --retry 5 --retry-delay 3 --retry-all-errors \
    "https://www.duckdns.org/update?domains=${DUCKDNS_DOMAIN}&token=${DUCKDNS_TOKEN}&ip=")"

if [[ "$RESPONSE" != "OK" ]]; then
    echo "DuckDNS respondio '$RESPONSE' (se esperaba OK): revisa subdominio y token." >&2
    exit 1
fi

echo "DuckDNS actualizado: ${DUCKDNS_DOMAIN}.duckdns.org"
