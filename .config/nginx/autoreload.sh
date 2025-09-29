#!/bin/sh

if [ ! -f /etc/letsencrypt/live/${DOMAIN}/fullchain.pem ]; then
    echo "No cert found, generating dummy cert...";
    mkdir -p /etc/letsencrypt/live/${DOMAIN};
    openssl req -x509 -nodes -newkey rsa:2048 -days 1 \
    -keyout /etc/letsencrypt/live/${DOMAIN}/privkey.pem \
    -out /etc/letsencrypt/live/${DOMAIN}/fullchain.pem \
    -subj "/CN=localhost";
fi;

exec /docker-entrypoint.sh nginx -g "daemon off;";
