#!/usr/bin/env sh
set -e

cd /app

# Garante a estrutura do storage (o volume pode chegar vazio)
mkdir -p \
    storage/app/private \
    storage/app/public \
    storage/database \
    storage/framework/cache/data \
    storage/framework/sessions \
    storage/framework/views \
    storage/logs

if [ -z "$APP_KEY" ]; then
    echo "ERRO: APP_KEY não definida. Gere com: docker compose run --rm app php artisan key:generate --show" >&2
    exit 1
fi

# Banco SQLite persistido no volume
if [ "${DB_CONNECTION:-sqlite}" = "sqlite" ]; then
    DB_FILE="${DB_DATABASE:-/app/storage/database/database.sqlite}"
    if [ ! -f "$DB_FILE" ]; then
        echo "Criando banco SQLite em $DB_FILE"
        touch "$DB_FILE"
    fi
fi

if [ "${RUN_MIGRATIONS:-false}" = "true" ]; then
    php artisan migrate --force --no-interaction
fi

php artisan optimize --no-interaction

exec "$@"
