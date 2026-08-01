/**
 * In Zicht — Chart.js theme bridge (canonical source).
 *
 * Chart.js paints on a <canvas>, so it cannot consume CSS custom properties:
 * every colour has to be resolved to a concrete value at render time, and
 * re-resolved whenever the chart is rebuilt after a theme switch. These helpers
 * are the one place that knows how.
 *
 * WHY THIS FILE IS VENDORED, NOT LOADED
 * A Nextcloud theme can only contribute CSS and static assets — it has no hook
 * for adding a script to a page, and each app ships its own webpack bundle. So
 * this file is the source of truth and apps copy it to `src/lib/izChart.js`.
 * When you change it here, copy it to every app (adminpage, superadminpage,
 * employee-dashboard) in the same change. It is small and dependency-free on
 * purpose, so that stays cheap.
 *
 * Usage in a Vue component:
 *
 *   import { themeColor, tooltipTheme } from "../lib/izChart";
 *   ...
 *   backgroundColor: themeColor(this.$el, "--chart-3", "#2f9e8f"),
 *   plugins: { tooltip: tooltipTheme(this.$el) },
 */

/**
 * Resolve a CSS custom property against a live element.
 *
 * `el` must be in the document — custom properties inherit, so the value
 * depends on where you ask. Pass the component's own root ($el); passing
 * document.documentElement will miss anything scoped to `.iz-app`.
 *
 * Returns `fallback` when the element is not mounted yet or the property is
 * undefined, so a chart built before mount still renders in sensible colours.
 */
export function themeColor(el, name, fallback) {
  if (!el || !el.ownerDocument) return fallback;
  const v = getComputedStyle(el).getPropertyValue(name);
  return (v && v.trim()) || fallback;
}

/**
 * Chart.js tooltip options that invert correctly in both schemes.
 *
 * The tooltip box is painted in the page's *text* colour and its label in the
 * page's *surface* colour. That gives a dark box with light text on the light
 * scheme and a light box with dark text on the dark one. Chart.js defaults the
 * label to white, which disappears against the light box on dark — this is the
 * single most common theming bug in canvas charts.
 */
export function tooltipTheme(el, extra) {
  return Object.assign(
    {
      backgroundColor: themeColor(el, "--color-text-primary", "#1a1a2e"),
      titleColor: themeColor(el, "--bg-card", "#ffffff"),
      bodyColor: themeColor(el, "--bg-card", "#ffffff"),
      titleFont: { size: 13, weight: "600" },
      bodyFont: { size: 12 },
      padding: 10,
      cornerRadius: 8,
    },
    extra || {},
  );
}

/**
 * The categorical series colours, in order, resolved against `el`.
 *
 * Series 1 is the accent. Use this rather than hand-picking hexes so a chart
 * added later lands in the same palette as the ones beside it. Fallbacks match
 * the light-mode values in the theme's section 8.
 */
export function chartPalette(el) {
  return [
    themeColor(el, "--chart-1", "#cc3d94"),
    themeColor(el, "--chart-2", "#3a2350"),
    themeColor(el, "--chart-3", "#2f9e8f"),
    themeColor(el, "--chart-4", "#d98a2b"),
    themeColor(el, "--chart-5", "#7c5cbf"),
  ];
}

/**
 * The colour for text drawn *on top of* a saturated fill — an in-slice
 * percentage label, a value inside a bar, a custom canvas tooltip.
 *
 * Deliberately not --bg-card: the ground here is the series colour, which stays
 * saturated in both schemes, so the label must stay light in both. --bg-card
 * would flip to near-black on the dark scheme and vanish.
 */
export function onFillColor(el) {
  return themeColor(el, "--iz-accent-text", "#ffffff");
}
