#!/usr/bin/env bash
set -euo pipefail

DB_HOST="${DB_HOST:-db}"
DB_PORT="${DB_PORT:-3306}"
DB_NAME="${DB_NAME:-projet_db}"
DB_USER="${DB_USER:-projet_user}"
DB_PASS="${DB_PASS:-projet_pass}"

SCHEMA_PATH="/docker/schema.sql"

if [[ ! -f "$SCHEMA_PATH" ]]; then
  echo "[start] schema.sql not found at $SCHEMA_PATH" >&2
  exit 1
fi

echo "[start] Waiting for MySQL at ${DB_HOST}:${DB_PORT}..."
for _ in {1..60}; do
  if mysqladmin ping -h"$DB_HOST" -P"$DB_PORT" -u"$DB_USER" -p"$DB_PASS" --silent; then
    break
  fi
  sleep 1
done

if ! mysqladmin ping -h"$DB_HOST" -P"$DB_PORT" -u"$DB_USER" -p"$DB_PASS" --silent; then
  echo "[start] MySQL is not reachable" >&2
  exit 1
fi

echo "[start] Ensuring tables exist in ${DB_NAME}..."
mysql -h"$DB_HOST" -P"$DB_PORT" -u"$DB_USER" -p"$DB_PASS" "$DB_NAME" < "$SCHEMA_PATH"

echo "[start] Starting Apache..."
exec "$@"
