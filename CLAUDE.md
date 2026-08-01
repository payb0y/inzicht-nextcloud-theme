# CLAUDE.md

Guidance for Claude Code when working in this repository.

## What this is

The **In Zicht** theme for **Nextcloud 34** — a server theme that restyles Nextcloud
core **and every app** with the In Zicht look: deep navy + magenta-pink accent,
Space Grotesk headings over Inter, ~10px radii, soft pink-glow shadows, a light-pink
gradient backdrop with white cards, and the In Zicht header/login logo. Full light and
dark support for the app; the **login page is pinned to light** by design.

The palette is ported **verbatim** from a source design system (`newstyle`, a
Tailwind/shadcn app) — OKLCH values are copied exactly, not re-derived. When changing
colors, match the OKLCH source; don't eyeball hex.

This repo is the **source of truth** and the deployable artifact (`git pull` on a
server, then run the installer). It is NOT a Nextcloud checkout — there's no PHP app,
just the theme folder + install scripts.

## Repository layout

```
themes/inzicht/
  defaults.php              empty `class OC_Theme {}` — keeps NC's product name/footer/logo text.
                            Intentionally empty: the theme changes LOOK only, not branding strings.
  core/css/server.css       ALL styles, one file (see "Single-file CSS" below).
  core/fonts/*.woff2        self-hosted Inter + Space Grotesk (no CDN → CSP-safe).
  core/img/Logo.png         the In Zicht logo (bundled; set as NC logo via occ, see Deploy).
  core/js/iz-chart.js       canonical Chart.js theme bridge. NOT loaded by the theme — a
                            theme cannot add scripts to a page. Apps VENDOR it to
                            src/lib/izChart.js; change it here and copy to every app in
                            the same commit.
install.sh                  installer for a mounted/filesystem Nextcloud root.
deploy-docker.sh            installer for a Dockerized Nextcloud (SAFE copy — see "docker cp trap").
README.md                   user-facing install/update/uninstall docs.
CLAUDE.md                   this file.
```

## How the theme works (the mechanics that matter)

Nextcloud's `CSSResourceLocator` appends `themes/<active>/core/css/server.css` after
core CSS on **every** page, so this file always loads **last**. The theme is activated
with `'theme' => 'inzicht'` in config (via `occ config:system:set theme --value inzicht`).

Key facts, learned the hard way — respect them:

- **NC theme variables live under `[data-theme-<id>]` selectors**, injected dynamically
  and loaded AFTER this file. A bare `:root {…}` override LOSES to them. We beat them
  with the **double-attribute** selector `[data-themes][data-theme-<id>]` (specificity
  0,2,0), which wins over NC's single-attribute `[data-theme-<id>]` (0,1,0) and `:root`
  regardless of source order. Use this pattern for all token overrides.
  - Light/default → `[data-themes][data-theme-default], [data-themes][data-theme-light]`
  - Dark → `[data-themes][data-theme-dark]`
  - "Default" theme = **follow system**: a `@media (prefers-color-scheme: dark)` block
    re-applies the dark palette to `[data-theme-default]` (kept in sync with the dark block).
  - High-contrast themes are left un-recolored (accessibility).

- **The login page (`body#body-login`) has NO `[data-theme-*]` attribute** and no
  logged-in user, so token overrides don't reach it and it can only see the OS
  `prefers-color-scheme`. It is themed by its own `body#body-login {…}` block
  (specificity 1,0,1, loads last → wins over NC defaults + the blue "fluid" background
  blobs + the Theming panel). It is **pinned to light** (`color-scheme: light`) so a
  dark-OS visitor still gets a light login on this light instance. Do not add a dark
  `@media` block for login unless the intent changes.

- **`--image-background`** on `body#body-login` is set to the In Zicht gradient so the
  login backdrop is themed. This supersedes the community `custom` "login-image" app
  (which sets `--image-background` on `body#body-login` too, but only when an image is
  uploaded). To hand control back to that app, remove the `--image-background` lines.

- **Fonts:** `--font-face` is injected by NC under `[data-theme-*]`, so it's overridden
  with the same double-attribute trick. Headings get `font-family: 'Space Grotesk'`.
  Font `url('../fonts/…')` paths are relative to `core/css/` → resolve to `core/fonts/`.

- **The logo image** is not controlled purely by the theme: Nextcloud stores which
  image is the logo in its own config, set by `occ theming:config logo[header]`. The
  theme bundles `Logo.png`; the installers run the `occ` commands. `#header #nextcloud`
  is widened in CSS so the wide lockup + tagline render.

## Single-file CSS (do NOT re-split)

All styles are in one `core/css/server.css`, organized into commented sections
(1 fonts, 2 font-wiring, 3 palette, 4 components, 5 motion, 6 header logo, 7 login).

This is deliberate. Nextcloud adds a `?v=` cache-buster to the `<link>` for
`server.css`, but `@import`-ed partials are fetched **without** the query and cached by
browsers for ~6 months, so updates silently don't show. The theme used to be split into
`inzicht-{fonts,tokens,components,motion,login}.css`; that caused "my changes don't
appear" bugs. **Never re-introduce `@import`.** Edit sections in `server.css`.

## `.iz-*` UI primitives (section 8) — shared across our own apps

Sections 1–7 restyle **Nextcloud core**. Section 8 is different: it's a set of opt-in
classes (`.iz-panel`, `.iz-card`, `.iz-row`, `.iz-table`, `.iz-pill`, `.iz-badge`,
`.iz-btn`, `.iz-input`, `.iz-label`, `.iz-metric`, `.iz-pagination`, `.iz-empty`,
`.iz-modal`, …) plus an `--iz-*` token set, used by the **In Zicht custom apps**
(`superadminpage`, `adminpage`, `employee_dashboard`). It targets nothing in stock NC,
so it's inert on core pages.

It lives here rather than in each app because:

- one definition means a Projects row and a Members row can't drift apart — the apps
  had accumulated 5 row models, 10 badge variants and 4 duplicate button bases;
- **theme CSS gets NC's `?v=` cache-buster; app webpack bundles do not**, so a shared
  fix ships with `git pull` + deploy, no rebuild and no stale-bundle debugging;
- a new app inherits the look by applying classes.

Rules when working on it:

- `--iz-*` tokens derive from the section-3 palette, so light/dark follow automatically.
  Never hardcode a color in an app — add or use a token.
- Every categorical/status color has a **tint companion** (`--iz-cat-5` + `--iz-cat-5-bg`,
  `--iz-success` + `--iz-success-bg` + `--iz-success-text`). Badges must pair a tint
  background with a solid text color; using the same token for both renders invisible
  text (a bug we shipped once already).
- Two row models only: `.iz-row` (flush, separated by rules, inside a panel) and
  `.iz-row--card` (standalone card per row). Pick one per list, never mix.
- Apps keep local CSS for **layout** (grid tracks, widths, region gaps) and use
  primitives for **chrome** (surface, border, radius, shadow, type scale, hover, focus).
- Apps alias their legacy token names to `--iz-*` with a full fallback chain, e.g.
  `--bg-card: var(--iz-surface, var(--color-main-background, #fff))`, so they still
  render if the In Zicht theme isn't the active one.

## Local dev / test harness (this machine)

Testing needs a running NC 34. On this machine there's a docker dev stack at
`/home/payboy/src/nextcloud-docker-dev` (Julius Härtl's `nextcloud-dev`). The theme is
served from `workspace/server/themes/inzicht` there (that dir is a copy of this repo's
`themes/inzicht`).

### ⚠️ Starting it — `start`, never `up`

The instance runs on **PostgreSQL** in a container named **`nc_pg`** (created by hand,
not by this compose file — it has no compose labels and is NOT a compose service).
The compose file still defines a `database-mysql` service holding a **stale June dev
copy**; the live production data is only in `nc_pg`.

`docker compose up` starts `database-mysql` and runs the nextcloud entrypoint's
**auto-installer**, which overwrites `/var/www/html/config/config.php` with a fresh
MySQL stub whenever it can't validate the install. That silently repoints the instance
at the wrong (stale) database. It happened on 2026-07-24. Use `start`:

```bash
cd /home/payboy/src/nextcloud-docker-dev
docker start nc_pg                        # the real database — start it FIRST
docker compose start nextcloud proxy redis mail   # start, NOT up — up re-runs the installer

# Only if a container genuinely doesn't exist yet does `up` make sense — and then
# back up config.php first (see below).
```

Config lives in the **`master_config` docker volume**, not in
`workspace/server/config/` (that dir is empty by design — don't be fooled by it):

```bash
docker run --rm -v master_config:/c alpine cat /c/config.php
```

Known-good copies are kept in `/home/payboy/src/nextcloud-docker-dev/backups/`
(`config.php.pgsql-working-*` plus `pg_dump` snapshots). If the installer clobbers
config.php again, restore from there — the essential keys are `dbtype => pgsql`,
`dbhost => nc_pg`, `dbuser/dbpassword => nextcloud`, plus `instanceid`,
`passwordsalt`, `secret` and `theme => inzicht`. Losing `secret`/`passwordsalt` breaks
sessions and stored credentials, so never hand-write a config without them.

```bash
# URL: http://nextcloud.local:8080/   (admin / admin)
# nextcloud.local must resolve; WSL wipes /etc/hosts on restart, so if it's missing:
#   echo "127.0.0.1 nextcloud.local" | sudo tee -a /etc/hosts   (needs the user's password)

# occ (theme is 'inzicht'):
docker compose exec -u www-data -T nextcloud php occ config:system:get theme
docker compose exec -u www-data -T nextcloud php occ config:system:set theme --value inzicht

# fresh DB backup before any risky change:
docker exec nc_pg pg_dump -U nextcloud -d nextcloud --clean --if-exists \
  > backups/nextcloud_pg_$(date +%Y%m%d_%H%M%S).sql
```

Iterating: edit `server.css` in this repo, then sync it into the dev instance and
hard-refresh:

```bash
cp /home/payboy/src/inzicht-nextcloud-theme/themes/inzicht/core/css/server.css \
   /home/payboy/src/nextcloud-docker-dev/workspace/server/themes/inzicht/core/css/server.css
```

(Or symlink `workspace/server/themes/inzicht` → this repo's `themes/inzicht` once, for
zero-copy iteration.) The dir is mounted into the container live; no restart needed.

The `custom` login-image app is installed on this dev instance WITH an image uploaded,
which masks the login background. To test the login backdrop as production sees it,
temporarily disable it: `occ app:disable custom` (re-enable with `occ app:enable custom`).

## Testing methodology

Verify in a real browser — token values alone lie (background images, blur vars, and
the login page aren't visible from `getComputedStyle` of a var). Playwright is available.

**Always bust caches before checking** — theme CSS has a ~6-month max-age. With
Playwright/CDP:

```js
const client = await page.context().newCDPSession(page);
await client.send('Network.setCacheDisabled', { cacheDisabled: true });
await client.send('Network.clearBrowserCache');   // <-- the essential one; setCacheDisabled alone isn't enough
```

Checklist when changing anything visual:
- App: Dashboard + Files + Settings, in **light and dark** (`occ user:setting admin
  theming enabled-themes '["light"]'` / `'["dark"]'`; `'["default"]'` = follow system).
- Login: `page.emulateMedia({ colorScheme: 'dark' })` AND `'light'` — must be light in both.
  Clear cookies to hit the logged-out page.
- Compare rendered element colors to the OKLCH source (differences are usually just
  browser trailing-zero formatting, e.g. `0.60`→`0.6` — those are equal).
- Confirm no stray `@import` and no 404s in the console.

## Deploy (to a server) — and two traps

Users deploy by `git pull` + an installer. Two failure modes to know:

1. **`docker cp` nesting.** `docker cp themes/inzicht CT:/var/www/html/themes/inzicht`
   copies the folder *inside* the destination when it already exists →
   `…/themes/inzicht/inzicht/…`, so updates land in a buried dir and never apply.
   `deploy-docker.sh` and the README always `rm -rf` the target first. Never hand-run a
   bare `docker cp` over an existing theme dir.
2. **CSS cache.** After deploying, bust NC's asset cache
   (`occ maintenance:mode --on` then `--off`) and hard-refresh once. Single-file CSS
   means the `?v=` covers everything after that.

Scripts: `./deploy-docker.sh <container> [nc-root] [web-user]` for Docker;
`sudo ./install.sh <nc-root> [web-user]` for filesystem. Both set the theme + both logos
and bust the cache.

## Release workflow

1. Edit `themes/inzicht/core/css/server.css` in this repo.
2. Sync into the dev instance + hard-refresh; verify per the checklist above.
3. Commit + push here (`origin/master`). Commit style: `type(scope): summary` +
   `Co-Authored-By: Claude Opus 4.8 (1M context) <noreply@anthropic.com>`.
4. On the server: `git pull && ./deploy-docker.sh <container>`.

## Version compatibility

Built and verified on **Nextcloud 34**. Colors/fonts/radii use stable variable names
and should mostly carry to other majors, but component selectors
(`.button-vue--vue-primary`, `.app-content-list-item`, `.app-navigation-toggle`,
`#header #nextcloud .logo`, `body#body-login`) may drift. After any NC major upgrade,
re-run the testing checklist and adjust selectors in `server.css`.
