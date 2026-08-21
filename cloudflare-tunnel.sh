#!/usr/bin/env bash
set -euo pipefail

LOCAL_URL="${1:-http://127.0.0.1:5173}"
BIN_DIR="${HOME}/.local/bin"
CLOUDFLARED_BIN="${BIN_DIR}/cloudflared"

log() {
  printf '[cloudflare-tunnel] %s\n' "$*"
}

die() {
  printf '[cloudflare-tunnel] ERROR: %s\n' "$*" >&2
  exit 1
}

detect_platform() {
  local os arch
  os="$(uname -s 2>/dev/null || true)"
  arch="$(uname -m 2>/dev/null || true)"

  case "$os" in
    Linux)
      PLATFORM="linux"
      ;;
    Darwin)
      PLATFORM="darwin"
      ;;
    MINGW*|MSYS*|CYGWIN*)
      PLATFORM="windows"
      ;;
    *)
      die "Unsupported OS: ${os:-unknown}. On Windows run this script from Git Bash, MSYS2, Cygwin, or WSL."
      ;;
  esac

  case "$arch" in
    x86_64|amd64)
      ARCH="amd64"
      ;;
    aarch64|arm64)
      ARCH="arm64"
      ;;
    *)
      die "Unsupported CPU architecture: ${arch:-unknown}"
      ;;
  esac
}

find_cloudflared() {
  if command -v cloudflared >/dev/null 2>&1; then
    CLOUDFLARED_BIN="$(command -v cloudflared)"
    return 0
  fi

  if [ -x "${BIN_DIR}/cloudflared" ]; then
    CLOUDFLARED_BIN="${BIN_DIR}/cloudflared"
    return 0
  fi

  if [ -x "${BIN_DIR}/cloudflared.exe" ]; then
    CLOUDFLARED_BIN="${BIN_DIR}/cloudflared.exe"
    return 0
  fi

  return 1
}

download_cloudflared() {
  command -v curl >/dev/null 2>&1 || die "curl is required to download cloudflared."

  mkdir -p "$BIN_DIR"

  local asset target url
  case "$PLATFORM" in
    linux)
      asset="cloudflared-linux-${ARCH}"
      target="${BIN_DIR}/cloudflared"
      ;;
    darwin)
      asset="cloudflared-darwin-${ARCH}.tgz"
      target="${BIN_DIR}/cloudflared"
      ;;
    windows)
      asset="cloudflared-windows-${ARCH}.exe"
      target="${BIN_DIR}/cloudflared.exe"
      ;;
  esac

  url="https://github.com/cloudflare/cloudflared/releases/latest/download/${asset}"
  log "cloudflared not found. Downloading ${asset}..."

  if [ "$PLATFORM" = "darwin" ]; then
    local tmp_dir
    tmp_dir="$(mktemp -d)"
    trap 'rm -rf "${tmp_dir:-}"' EXIT
    curl -fL "$url" -o "${tmp_dir}/${asset}"
    tar -xzf "${tmp_dir}/${asset}" -C "$tmp_dir"
    mv "${tmp_dir}/cloudflared" "$target"
  else
    curl -fL "$url" -o "$target"
  fi

  chmod +x "$target" 2>/dev/null || true
  CLOUDFLARED_BIN="$target"

  case ":$PATH:" in
    *":${BIN_DIR}:"*) ;;
    *)
      log "Tip: add ${BIN_DIR} to PATH for future shells."
      ;;
  esac
}

main() {
  detect_platform

  if ! find_cloudflared; then
    download_cloudflared
  fi

  log "OS       : ${PLATFORM}/${ARCH}"
  log "Binary   : ${CLOUDFLARED_BIN}"
  log "Local URL: ${LOCAL_URL}"
  log "Starting Cloudflare Quick Tunnel..."
  log "Press Ctrl+C to stop."
  printf '\n'

  exec "$CLOUDFLARED_BIN" tunnel --no-autoupdate --url "$LOCAL_URL"
}

main "$@"
