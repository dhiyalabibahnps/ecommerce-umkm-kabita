#!/usr/bin/env bash
set -Eeuo pipefail

echo "==> Setting up Kabita E-Commerce Codespace (branch: release/v0.0.1)..."

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
BACKEND_DIR="$ROOT_DIR/backend"
FRONTEND_DIR="$ROOT_DIR/frontend"

# 1. Setup Backend (Laravel)
echo "==> [Backend] Installing Composer dependencies..."
cd "$BACKEND_DIR"

if [ ! -f .env ]; then
  cp .env.example .env
  # Default to sqlite for seamless Codespaces execution if MariaDB isn't running
  sed -i 's/^DB_CONNECTION=mariadb/DB_CONNECTION=sqlite/' .env || true
fi

composer install --no-interaction --prefer-dist --optimize-autoloader

echo "==> [Backend] Running migrations and seeding..."
php artisan key:generate --force --ansi || true
touch database/database.sqlite
php artisan migrate --force
php artisan db:seed --class=KabitaFullDataSeeder --force
php artisan storage:link --force || true
php artisan optimize:clear

# 2. Setup Frontend (Vue 3)
echo "==> [Frontend] Installing Node dependencies..."
cd "$FRONTEND_DIR"

if [ ! -f .env ]; then
  cp .env.example .env
fi

npm install

echo "==> [Frontend] Running auto build..."
npm run build

echo "==> Codespaces auto build complete for branch release/v0.0.1!"
