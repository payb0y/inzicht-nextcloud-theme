<?php

/**
 * In Zicht theme — visual styling plus the full product-identity overrides.
 *
 * Every getter in OC_Defaults is guarded by themeExist(), so anything absent
 * from this class falls through to Nextcloud's own value.
 *
 * All overrides travel with the theme on purpose. The same effects are mostly
 * reachable from the Theming app, but those live in appconfig — per instance,
 * invisible to this repo, lost on a DB reseed, and needing to be re-applied by
 * hand on every server. Anything set in the Theming admin UI still wins over
 * what is returned here, because ThemingDefaults reads its stored value first
 * and only falls back to these — so keep the Theming appconfig TEXT fields
 * empty and let this file be the source of truth.
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
	 * removed, and an explicitly blank one still leaves the entity link sitting
	 * in the card alone.
	 */
	public function getLongFooter(): string {
		return '';
	}

	/**
	 * The short product name. Drives the login headline ("Log in to In Zicht"),
	 * og:title/og:site_name, the public-share header, the WebDAV/CalDAV auth
	 * realm, 403/404/maintenance pages, and the welcome e-mail.
	 */
	public function getName(): string {
		return 'In Zicht';
	}

	/** HTML-escaped variant of getName(); kept in lockstep. */
	public function getHTMLName(): string {
		return 'In Zicht';
	}

	/**
	 * Browser tab title on every page — layout.user.php renders
	 * "{page} - {app} - getTitle()" and layout.guest.php "{page} – getTitle()".
	 * Also feeds apple-mobile-web-app-title.
	 */
	public function getTitle(): string {
		return 'In Zicht';
	}

	/**
	 * The legal entity used in footer copyright and e-mail attribution
	 * (ThemingDefaults::getShortFooter() builds "<a href=baseUrl>entity</a> – slogan").
	 */
	public function getEntity(): string {
		return 'In Zicht';
	}

	/**
	 * Replaces "a safe home for all your data" in every outgoing e-mail footer,
	 * og:description, and the PWA manifest description.
	 */
	public function getSlogan(): string {
		return 'Samen projecten inzichtelijk';
	}

	/**
	 * Product name shown by status.php, OCS capabilities, the "Add to your
	 * {productName}" button on public shares, update screens, and federated
	 * sharing help text.
	 */
	public function getProductName(): string {
		return 'In Zicht';
	}

	/** Footer link target and OC.theme.baseUrl (was https://nextcloud.com). */
	public function getBaseUrl(): string {
		return 'https://inzicht-new.loket.site';
	}

	/**
	 * Client-download links. There are no In Zicht-branded clients (yet), so all
	 * of these point at the product itself instead of nextcloud.com/app stores.
	 * Also removes the "Get the apps"-style promotions that consume these URLs.
	 */
	public function getSyncClientUrl(): string {
		return 'https://inzicht-new.loket.site';
	}

	public function getAndroidClientUrl(): string {
		return 'https://inzicht-new.loket.site';
	}

	public function getiOSClientUrl(): string {
		return 'https://inzicht-new.loket.site';
	}

	public function getFDroidClientUrl(): string {
		return 'https://inzicht-new.loket.site';
	}

	/**
	 * Empty string removes the <meta name="apple-itunes-app"> smart banner that
	 * advertises the Nextcloud iOS app on the login and public-share pages
	 * (layout.guest.php / layout.public.php only render the meta when this is
	 * non-empty).
	 */
	public function getiTunesAppId(): string {
		return '';
	}
}
