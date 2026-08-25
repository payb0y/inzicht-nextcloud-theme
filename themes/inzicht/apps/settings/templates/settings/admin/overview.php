<?php
/**
 * In Zicht override of apps/settings/templates/settings/admin/overview.php.
 *
 * Loaded because Template\Base::getAppTemplateDirs() checks
 * themes/<theme>/apps/<app>/templates/ before the app's own templates.
 * The stock template hardcodes "Nextcloud Hub 26 Spring" as raw HTML (not a
 * translatable string), so a template override is the only clean fix.
 *
 * Kept byte-for-byte identical to the stock template except the version line,
 * which now uses the theme's product name. RE-SYNC against the stock file
 * after every Nextcloud upgrade (the branding hook warns when the version
 * changes).
 */

/** @var \OCP\IL10N $l */
/** @var array $_ */
/** @var \OCP\Defaults $theme */

?>

<div id="vue-admin-settings-setup-checks"></div>

<div id="version" class="section">
	<!-- should be the last part, so Updater can follow if enabled (it has no heading therefore). -->
	<h2><?php p($l->t('Version'));?></h2>
	<p><strong><a href="<?php print_unescaped($theme->getBaseUrl()); ?>" rel="noreferrer noopener" target="_blank"><?php p($theme->getName()); ?></a> (<?php p($_['version']) ?>)</strong></p>
</div>
