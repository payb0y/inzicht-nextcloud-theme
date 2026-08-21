<?php

/**
 * In Zicht theme — visual styling, plus two deliberate text overrides.
 *
 * Most branding (logo, favicon, colours, URLs) is still NOT overridden here.
 * Every getter in OC_Defaults is guarded by method_exists(), so anything absent
 * from this class falls through to Nextcloud's own value.
 *
 * Both overrides below travel with the theme on purpose. The same effects are
 * reachable from the Theming app, but those live in appconfig — per instance,
 * invisible to this repo, and needing to be re-applied by hand on every server.
 * Anything set in the Theming admin UI still wins over what is returned here,
 * because ThemingDefaults reads its stored value first and only falls back to
 * these.
 */
class OC_Theme {
	/**
	 * Removes the "Nextcloud – a safe home for all your data" card from the
	 * login page.
	 *
	 * core/templates/layout.guest.php reads getLongFooter() once and, when it
	 * comes back empty, renders the guest footer with the `hidden` class. So an
	 * empty string is core's own supported way to drop the card — the element is
	 * removed by Nextcloud rather than hidden after the fact by theme CSS.
	 *
	 * Not reachable from the Theming app: ThemingDefaults::getSlogan() passes the
	 * core slogan as its DEFAULT, so an unset slogan renders the very text being
	 * removed, and an explicitly blank one still leaves the "Nextcloud" link
	 * sitting in the card alone.
	 */
	public function getLongFooter(): string {
		return '';
	}

	/**
	 * The short product name. Drives the login headline, which core builds as
	 * t('core', 'Log in to {productName}', ['productName' => OC.theme.name]) in
	 * core/src/components/login/LoginForm.vue — so this is what turns
	 * "Log in to Nextcloud" into "Log in to In Zicht".
	 *
	 * It reaches ThemingDefaults because that class seeds $this->name from
	 * parent::getName() in its constructor, and OC_Defaults::getName() honours a
	 * theme override via themeExist('getName').
	 *
	 * Deliberately narrow: getTitle() and getEntity() are left alone, so browser
	 * titles and copyright/e-mail attribution still say Nextcloud. Widening this
	 * is a rebrand decision, not a styling one.
	 */
	public function getName(): string {
		return 'In Zicht';
	}
}
