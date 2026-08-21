#!/usr/bin/env bash
set -Eeuo pipefail

echo "==> Setting up Kabita E-Commerce Codespace (branch: release/v0.0.1)..."

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
BACKEND_DIR="$ROOT_DIR/backend"
FRONTEND_DIR="$ROOT_DIR/frontend"

# Install Node.js locally for the workspace user to avoid relying on failing feature repos.
NODE_VERSION="v22.18.0"
NODE_INSTALL_ROOT="$HOME/.local"
NODE_SYMLINK="$NODE_INSTALL_ROOT/node"

if ! command -v node >/dev/null 2>&1; then
  echo "==> [Runtime] Installing Node.js $NODE_VERSION locally..."

  ARCH="$(dpkg --print-architecture)"
  case "$ARCH" in
    amd64) NODE_ARCH="x64" ;;
    arm64) NODE_ARCH="arm64" ;;
    *)
      echo "Unsupported CPU architecture for Node.js install: $ARCH" >&2
      exit 1
      ;;
  esac

  NODE_DIR_NAME="node-$NODE_VERSION-linux-$NODE_ARCH"
  NODE_TARBALL="$NODE_DIR_NAME.tar.xz"
  NODE_URL="https://nodejs.org/dist/$NODE_VERSION/$NODE_TARBALL"

  mkdir -p "$NODE_INSTALL_ROOT"
  TMP_NODE_DIR="$(mktemp -d)"
  curl -fsSL "$NODE_URL" -o "$TMP_NODE_DIR/$NODE_TARBALL"
  tar -xJf "$TMP_NODE_DIR/$NODE_TARBALL" -C "$NODE_INSTALL_ROOT"
  ln -sfn "$NODE_INSTALL_ROOT/$NODE_DIR_NAME" "$NODE_SYMLINK"
  rm -rf "$TMP_NODE_DIR"
fi

export PATH="$NODE_SYMLINK/bin:$PATH"
node --version
npm --version

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
