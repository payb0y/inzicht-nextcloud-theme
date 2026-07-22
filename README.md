# In Zicht — Nextcloud theme

A server theme that restyles Nextcloud (core + all apps) with the In Zicht look:
navy + magenta-pink accent, Space Grotesk headings over Inter, 10px radii, soft
pink-glow shadows, light-pink gradient backdrop with white cards, and the In Zicht
header logo. Full light **and** dark support.

Built and verified against **Nextcloud 34**. See "Version compatibility" below.

## What's in this bundle

```
themes/inzicht/            <- the theme (copy this into <nextcloud>/themes/)
  defaults.php             empty OC_Theme (keeps NC's product name/footer)
  core/css/*.css           tokens, fonts, components, motion, login
  core/fonts/*.woff2       self-hosted Inter + Space Grotesk (no CDN, CSP-safe)
  core/img/Logo.png        the In Zicht header logo
install.sh                 one-shot installer
README.md                  this file
```

## Install (automated)

Copy this folder to the production server, then:

```bash
sudo ./install.sh /path/to/nextcloud [web-user]
# e.g.  sudo ./install.sh /var/www/nextcloud www-data
```

Then hard-refresh the browser (Ctrl+Shift+R).

## Install (manual)

Let `NC=/var/www/nextcloud` and `WEB=www-data`.

```bash
# 1. Copy the theme in
sudo cp -r themes/inzicht "$NC/themes/inzicht"
sudo chown -R "$WEB":"$WEB" "$NC/themes/inzicht"

# 2. Activate it
sudo -u "$WEB" php "$NC/occ" config:system:set theme --value inzicht
#    (equivalent to adding  'theme' => 'inzicht',  to config/config.php)

# 3. Set the header logo AND the login-page logo
sudo -u "$WEB" php "$NC/occ" theming:config logoheader "$NC/themes/inzicht/core/img/Logo.png"
sudo -u "$WEB" php "$NC/occ" theming:config logo       "$NC/themes/inzicht/core/img/Logo.png"

# 4. (optional) also set the favicon
sudo -u "$WEB" php "$NC/occ" theming:config favicon "$NC/themes/inzicht/core/img/Logo.png"
```

Hard-refresh the browser afterwards.

## Light vs dark

Each user picks Light or Dark under **Settings -> Appearance**; both are themed.
To make dark the default for a user from the CLI:

```bash
sudo -u www-data php "$NC/occ" user:setting <uid> theming enabled-themes '["dark"]'
```

## Updating the theme later

Re-copy `themes/inzicht` over the old one and re-`chown`. Nextcloud serves theme
CSS with a long cache lifetime, so after an update either bump the asset cache or
have users hard-refresh:

```bash
sudo -u www-data php "$NC/occ" maintenance:mode --on
sudo -u www-data php "$NC/occ" maintenance:mode --off
```

## Uninstall / revert

```bash
sudo -u www-data php "$NC/occ" config:system:delete theme
sudo -u www-data php "$NC/occ" theming:config logoheader ''   # clears the header logo
sudo rm -rf "$NC/themes/inzicht"
```

## Version compatibility (read before deploying)

This theme maps the In Zicht palette onto Nextcloud's CSS custom properties
(`--color-primary-element`, `--color-main-background`, `--border-radius-*`, the
`[data-theme-*]` selectors, etc.). Those variable names are current as of
**Nextcloud 34**. On a different major version:

- Colors/fonts/radii will still mostly apply (the variable names are stable), but
  a few component selectors (`.button-vue--vue-primary`, `.app-content-list-item`,
  `#header #nextcloud .logo`) may drift.
- After deploying, click through Files / Dashboard / Settings in light and dark and
  check the browser console for CSS issues. If a component looks off, inspect its
  live class name and adjust the matching rule in `core/css/inzicht-components.css`
  or the header-logo block in `core/css/server.css`.

The `themes/` folder is not touched by Nextcloud core upgrades, so the theme
survives updates — but re-verify after a **major** version bump.

## Notes

- Fonts are self-hosted inside the theme (no external CDN), so they work under a
  strict Content-Security-Policy.
- High-contrast accessibility themes are intentionally left un-recolored.
- `defaults.php` is intentionally empty: the theme changes the *look* only, not the
  Nextcloud product name, footer, or documentation links.
