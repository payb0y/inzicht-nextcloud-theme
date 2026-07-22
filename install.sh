#!/usr/bin/env bash
#
# Installs the "In Zicht" Nextcloud theme onto a server.
#
# Usage:
#   sudo ./install.sh <nextcloud-root> [web-user]
#
# Examples:
#   sudo ./install.sh /var/www/nextcloud www-data
#   sudo ./install.sh /var/www/html         # defaults web-user to www-data
#
set -euo pipefail

NC_PATH="${1:?Usage: install.sh <nextcloud-root> [web-user]  (e.g. /var/www/nextcloud)}"
WEB_USER="${2:-www-data}"
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"

if [[ ! -f "$NC_PATH/occ" ]]; then
  echo "ERROR: $NC_PATH/occ not found — is that the Nextcloud root?" >&2
  exit 1
fi

OCC=(sudo -u "$WEB_USER" php "$NC_PATH/occ")

echo "==> Copying theme into $NC_PATH/themes/inzicht"
mkdir -p "$NC_PATH/themes"
rm -rf "$NC_PATH/themes/inzicht"
cp -r "$SCRIPT_DIR/themes/inzicht" "$NC_PATH/themes/inzicht"
chown -R "$WEB_USER":"$WEB_USER" "$NC_PATH/themes/inzicht"

echo "==> Activating theme (config.php: 'theme' => 'inzicht')"
"${OCC[@]}" config:system:set theme --value inzicht

echo "==> Setting the header + login logos"
"${OCC[@]}" theming:config logoheader "$NC_PATH/themes/inzicht/core/img/Logo.png"
"${OCC[@]}" theming:config logo       "$NC_PATH/themes/inzicht/core/img/Logo.png"

echo
echo "Done. Hard-refresh the browser (Ctrl+Shift+R) to clear cached CSS."
echo "Users choose Light/Dark under Settings -> Appearance; both are themed."
