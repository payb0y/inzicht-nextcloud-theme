#!/usr/bin/env bash
#
# Deploys the "In Zicht" theme into a Dockerized Nextcloud, safely.
#
# Why this script exists: `docker cp <dir> <ct>:<dest>` NESTS the folder inside
# <dest> when <dest> already exists (creating .../themes/inzicht/inzicht/...), so
# repeated updates silently land in a buried, unused directory and never show.
# This script removes the target first, so the copy always lands correctly.
#
# Usage:
#   ./deploy-docker.sh <container> [nextcloud-root] [web-user]
#
# Examples:
#   ./deploy-docker.sh nextcloud-app
#   ./deploy-docker.sh nextcloud /var/www/html www-data
#
set -euo pipefail

CT="${1:?Usage: deploy-docker.sh <container> [nextcloud-root] [web-user]}"
NC_ROOT="${2:-/var/www/html}"
WEB_USER="${3:-www-data}"
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
DEST="$NC_ROOT/themes/inzicht"

echo "==> Removing any existing theme at $CT:$DEST (prevents nested copies)"
docker exec -u root "$CT" rm -rf "$DEST"

echo "==> Copying theme into the container"
docker cp "$SCRIPT_DIR/themes/inzicht" "$CT":"$DEST"
docker exec -u root "$CT" chown -R "$WEB_USER":"$WEB_USER" "$DEST"

echo "==> Activating theme + setting logos"
docker exec -u "$WEB_USER" "$CT" php "$NC_ROOT/occ" config:system:set theme --value inzicht
docker exec -u "$WEB_USER" "$CT" php "$NC_ROOT/occ" theming:config logoheader "$DEST/core/img/Logo.png"
docker exec -u "$WEB_USER" "$CT" php "$NC_ROOT/occ" theming:config logo       "$DEST/core/img/Logo.png"

echo "==> Busting Nextcloud's asset cache"
docker exec -u "$WEB_USER" "$CT" php "$NC_ROOT/occ" maintenance:mode --on
docker exec -u "$WEB_USER" "$CT" php "$NC_ROOT/occ" maintenance:mode --off

echo "==> Verifying the deployed file is current (expect: 1)"
docker exec -u "$WEB_USER" "$CT" grep -c "In Zicht theme for Nextcloud 34 — single-file" "$DEST/core/css/server.css" \
  || echo "WARNING: server.css does not look like the current single-file version"

echo
echo "Done. Hard-refresh once (Ctrl+Shift+R). The theme is a single server.css,"
echo "so future deploys are picked up after the maintenance toggle above."
