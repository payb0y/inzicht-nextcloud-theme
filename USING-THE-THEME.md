# Using the In Zicht theme in an app

Canonical guidance for `adminpage`, `superadminpage`, `employee_dashboard` and
`organization`.
Each app's `CLAUDE.md` points here rather than repeating it — this file is the
one to edit.

Everything below is something that has already caused a bug in one of the three
apps. None of it is general advice.

---

## 1. The theme owns the look

Colours, radii, shadows, type scale and component chrome come from section 8 of
`themes/inzicht/core/css/server.css` as opt-in `.iz-*` classes and `--iz-*`
tokens. An app defines none of it.

**Why it lives here and not in the app:** Nextcloud appends
`themes/<active>/core/css/server.css` on every page *with a `?v=` cache-buster*.
An app's webpack bundle gets no such buster. A fix shipped in the theme reaches
users with `git pull` + deploy — no rebuild, no stale-bundle debugging — and it
lands in all three apps at once.

---

## 2. How tokens reach an app

The app's root element carries **`iz-app`**. That class is the "App token
bridge": it defines the generic names the components read — `--bg-card`,
`--color-text-primary`, `--radius-card`, `--accent`, `--chart-1`…`--chart-5`,
the badge pairs, the spacing scale — in terms of the `--iz-*` primitives.

Two consequences:

- **An undefined custom property does not fall back — it invalidates the whole
  declaration.** `background: var(--nope)` renders as nothing, silently. If a
  colour has vanished, check the token exists before checking anything else.
  This is exactly how `adminpage`'s public share view lost its palette: it
  defined eleven of the twenty-five names its components read.
- `iz-app` is also the ancestor that `.iz-input`, `.iz-select`, `.iz-close`,
  `.iz-tab` and the native form-control accent rules are scoped to. Without it
  they are inert — they were inert in `adminpage` for months.

Do not re-add a local block of token definitions to an app. Change a value in
the theme.

---

## 3. The rules

- **Chrome → primitive. Layout → local.** Surface, border, radius, shadow, type
  scale, hover and focus come from `.iz-*`. Grid tracks, column widths and gaps
  between regions stay in the component.
- **Never hardcode a colour, a font size or a font family.** Use a token.
  Monospace is `var(--iz-font-mono)` — the stack had been written out by hand
  twelve times across the four apps in three different forms, so the same UID
  rendered in a different face depending on which panel showed it.
- **Never put a layout property in a shared primitive.** `flex-grow` on
  `.iz-meter` meant "fill the width" in a row and "stretch the height" in a
  column; it shipped a collapsed bar in one place and a fat circle in another.
  Chrome generalises, layout does not.
- **Badges pair a tint background with a solid text colour.** Every status and
  categorical colour has both (`--iz-success` / `--iz-success-bg` /
  `--iz-success-text`; `--iz-cat-5` / `--iz-cat-5-bg` / `--iz-cat-5-text`).
  Using one token for both renders invisible text — shipped twice.
- **Don't build a class name from data.** `'prefix--' + row.status` silently
  emits a class that may not exist. Map through an explicit table with a neutral
  fallback.
- **Semantic colours mean status.** Don't reach for `--color-success` because
  green looks nice — an org avatar filled with it reads as "healthy".

---

## 4. Adding the class is only half the job

**Delete the local rule too.** Vue scoped CSS compiles to
`.my-class[data-v-abc]` — specificity (0,2,0), the *same* as
`.iz-app .iz-input` — and webpack injects app styles after the theme's `<link>`.
**On a tie the app wins**, so a local copy left in place silently keeps
overriding the primitive it was meant to defer to. The class looks applied and
nothing changes.

In `adminpage` this hid for two rounds: a search field and three selects were
each overriding about forty properties — the entire primitive — while wearing
its class.

**The inverse trap:** rules in an *unscoped* `<style>` block get no
`[data-v-…]`, so a bare class there is (0,1,0) and *loses* to the theme.
Qualify those on a parent: `.my-widget__filters .my-widget__select`.

**Audit, don't read.** For every element carrying an `.iz-*` class, compare the
properties the theme rule sets against those any app rule sets on the same
element; anything overlapping is a leftover. Reading the file is not reliable —
this has been proven twice.

---

## 5. Nextcloud core fights you on bare elements

Core styles `button`, `input`, `select` and headings directly at (0,1,1), and
some of it is `!important`. Anything built on those elements needs a qualified
selector; primitives use `.iz-app .iz-btn, button.iz-btn`. **If you qualify a
base, qualify its modifiers too**, or the base outranks them.

Known traps, all of which have shipped as bugs:

| Core does | Effect | Answer |
|---|---|---|
| `min-height: var(--default-clickable-area)` (34px) on every bare button | `min-height` beats `height`; a 28px icon button renders 28×34 | reset on the button — a parent cannot cap a taller child |
| `padding: 7.5px 12px` on bare buttons | on a 28px-wide button leaves ~2px of content box and crushes the glyph | `.iz-btn--icon` |
| `:focus { background: var(--color-primary-element-light) }` at (0,2,1) | a focused red danger button repaints pale pink under white text | the theme counters this for `.iz-btn`; a bare button will still flash |
| `outline: … !important` on `:focus-visible` | a custom focus ring on a bare button is dead CSS | use a primitive |
| `.iz-input` / `.iz-select` are `width: 100%` | right for a stacked form field, wrong in a toolbar row | `width: auto` locally |

---

## 6. Panels: pick the right variant

`.iz-panel` pads its content by `--iz-pad-panel` (20px) — use it when the panel
does the padding.

Use **`.iz-panel--list`** when the component pads its own header and body. Every
collapsible widget is this case: the whole header is the click target, so its
hover tint has to run to the card edge. Using the base class on those
double-pads them (20 + 24 = 44px) and insets the tint.

`.iz-panel--flush` drops the surface entirely.

---

## 7. Expandable rows have one design

Chevron **on the right**, last element in the row, chevron-down rotating 180°,
muted → accent on hover and while open. The summary tints on hover and the card
border firms up; expandable rows deliberately do **not** take the −4px lift —
that reads as "navigates away", and these open in place. Don't invent a fourth
answer; three components had already invented three.

---

## 8. Both schemes, every time

The theme has full light and dark. Check both before calling anything done.

The common failure is **a token that inverts being used as a solid fill under
white text**. `--accent-strong` (aliases `--iz-cat-2`) and the
`--color-badge-*-text` ramps all lighten on dark. For a solid fill use
`--accent`, `--color-danger`, `--color-success`, with `--iz-accent-text` for the
label on top.

Simulate dark without the theming app: set `data-themes="dark"` and
`data-theme-dark=""` on **both** `<body>` and `<html>`. Native form controls
stay light under that fake toggle — ignore those.

---

## 9. Blind spots

- **Colours embedded in `<template>` SVGs** — `stroke="#6b7280"`, marker fills —
  sit outside every `<style>` block and have escaped every colour sweep so far.
  Icons should take `stroke="currentColor"` so they follow the element they sit
  in.
- **One-line CSS rules** (`.a { color: x }` all on one line) survive naive
  deletion patterns that expect the brace on its own line.
- **Chart.js paints on canvas** and cannot read CSS variables. Colours must be
  resolved at render time — see the vendored bridge below.

---

## 10. Vendored files — copy, don't fork

A Nextcloud theme can ship only CSS and static assets, so anything else is
vendored into each app and **must be updated everywhere in the same change**:

- **`ConfirmDialog.vue`** — the one confirmation/notice dialog. No `alert()` or
  `confirm()`; native dialogs cannot be themed. The parent owns the busy flag
  and the error string, and the dialog never closes itself, so a failed action
  stays open with its reason attached.
  **`organization` is Vue 3 and cannot import this Vue 2 SFC**, so it carries a
  ported fourth copy at `src/components/ConfirmDialog.vue` with the same
  contract. A change here has to be ported into it, not copied.
- **`src/lib/izChart.js`** — the Chart.js bridge, canonical copy at
  `themes/inzicht/core/js/iz-chart.js`. Carries `themeColor()`,
  `tooltipTheme()`, `chartPalette()` and `onFillColor()`. The tooltip rule
  matters: the box takes `--color-text-primary` and the label `--bg-card`, so it
  inverts correctly — Chart.js defaults the label to white, which disappears
  against the light box on the dark scheme. Vendor it only into apps that
  actually draw on a canvas; an unused copy is the one that goes stale.

---

## 11. Changing a shared style

1. Edit `server.css` in this repo.
2. Deploy: `./deploy-docker.sh master-nextcloud-1`.
3. Verify in a browser in **light and dark** and at **two viewport widths** —
   the `.iz-meter` bug was invisible at the one width that got tested.
4. Bust the cache. Reloading the page does not re-fetch either the theme CSS or
   an app bundle: `fetch(url, {cache: 'reload'})` for each, then reload.
5. Deploy the **theme before or with the app** — a new bundle emits `.iz-*`
   classes that only exist in the new `server.css`.

Never edit the deployed copy inside the container or under
`nextcloud-docker-dev/workspace/`; both are outputs.

---

## 12. Known gaps

Three shapes every consumer hand-rolls. They are listed here so the next person
adds the primitive instead of writing a fifth copy — but each needs a decision
about the right geometry first, which is why none of them exists yet.

- **Key–value detail grid.** Four names for one pattern:
  `members-panel__detail-*`, `org-detail__profile-*`, `proj-details__info-*`,
  and two more in `organization`'s backup and handover tabs. They disagree on
  whether the label sits *above* the value (better for a wide row detail) or
  *left of* it (better in a narrow card), which is the decision to make before
  naming it `.iz-kv`. Until then use `.iz-label` for the label and keep only
  the grid tracks local — that at least makes the labels agree.
- **Step / timeline list.** A job with ordered steps, each with its own status.
  `organization` has one in `src/components/jobs/JobSteps.vue` built from
  `.iz-pill` + `.iz-dot`; superadminpage's `HandoverPanel` has a flat
  three-column event grid. Neither is general yet.
- **Indeterminate meter.** `.iz-meter` is determinate only, and `iz-spin` is
  the only keyframes in the file. A queued job with no percentage to report
  currently needs a local animation.

`.iz-metrics` / `.iz-metric` is a fourth case, but inverted: it exists and is
correct, and no sibling calls it — `superadminpage/KpiCard.vue` retypes it by
hand, minus the `font-variant-numeric: tabular-nums` that stops a polled
number jittering. Reach for the primitive.
