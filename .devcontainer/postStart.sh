#!/usr/bin/env bash
set -Eeuo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
LOG_DIR="${TMPDIR:-/tmp}/kabita-logs"
mkdir -p "$LOG_DIR"

echo "==> Starting backend and frontend services..."

# Start Laravel backend on port 8000
if ! lsof -i :8000 >/dev/null 2>&1; then
  cd "$ROOT_DIR/backend"
  nohup php artisan serve --host=0.0.0.0 --port=8000 > "$LOG_DIR/laravel.log" 2>&1 &
  echo "Backend running on http://127.0.0.1:8000"
fi

# Start Vue frontend on port 5173
if ! lsof -i :5173 >/dev/null 2>&1; then
  cd "$ROOT_DIR/frontend"
  nohup npm run dev -- --host=0.0.0.0 --port=5173 > "$LOG_DIR/frontend.log" 2>&1 &
  echo "Frontend running on http://127.0.0.1:5173"
fi

echo "==> Application services started in background."
echo "==> Backend logs: $LOG_DIR/laravel.log"
echo "==> Frontend logs: $LOG_DIR/frontend.log"
