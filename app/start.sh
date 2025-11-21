#!/usr/bin/env bash
set -euo pipefail

# Always run from project root where this script resides
cd "$(dirname "$0")"

echo "[start.sh] Initializing Laravel app..."

# Ensure environment file exists
if [ ! -f .env ]; then
  echo "[start.sh] .env not found. Creating from .env.example"
  cp .env.example .env || true
fi

# Install PHP dependencies if Composer is available
if command -v composer >/dev/null 2>&1; then
  echo "[start.sh] Installing PHP dependencies (composer install)"
  composer install --no-interaction --prefer-dist
else
  echo "[start.sh] Composer not found. Skipping composer install."
fi

# Generate APP_KEY (safe to re-run)
if command -v php >/dev/null 2>&1; then
  echo "[start.sh] Generating APP_KEY"
  php artisan key:generate --force || true
else
  echo "[start.sh] PHP not found. Cannot run artisan commands."
fi

# Install Node dependencies and build assets if npm is available
if command -v npm >/dev/null 2>&1; then
  echo "[start.sh] Installing Node dependencies (npm install)"
  npm install
  echo "[start.sh] Building assets (npm run build)"
  npm run build
else
  echo "[start.sh] npm not found. Skipping asset build."
fi

# Start Laravel development server
PORT_FROM_ENV=${PORT:-8000}
echo "[start.sh] Starting Laravel dev server on port ${PORT_FROM_ENV}"
exec php artisan serve --host=0.0.0.0 --port="${PORT_FROM_ENV}"