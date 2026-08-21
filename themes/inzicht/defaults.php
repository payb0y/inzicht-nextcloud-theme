<?php

/**
 * In Zicht theme — visual styling only, with one deliberate exception.
 *
 * Branding (name/logo/favicon) is still intentionally NOT overridden. Every
 * getter in OC_Defaults is guarded by method_exists(), so anything absent from
 * this class falls through to Nextcloud's own value.
 *
 * The exception is getLongFooter(). core/templates/layout.guest.php reads it
 * once and, when it comes back as an empty string, renders the guest footer
 * with the `hidden` class. Returning '' is therefore core's own supported way
 * to drop the whole "Nextcloud – a safe home for all your data" card from the
 * login page — the element is removed from view by Nextcloud itself rather than
 * hidden after the fact by theme CSS.
 *
 * This is not reachable from the Theming app: ThemingDefaults::getSlogan()
 * passes the core slogan as its DEFAULT, so an unset slogan renders the very
 * text being removed, and an explicitly blank one still leaves the "Nextcloud"
 * link sitting in the card on its own.
 */
class OC_Theme {
	/**
	 * An empty footer makes core mark the guest <footer> hidden.
	 */
	public function getLongFooter(): string {
		return '';
	}
}
