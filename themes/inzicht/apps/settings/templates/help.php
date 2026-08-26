<?php
/**
 * In Zicht override of apps/settings/templates/help.php.
 *
 * Identical to stock except every button that leads to a Nextcloud property
 * is removed: the "Forum" links (help.nextcloud.com) and all documentation
 * buttons (docs.nextcloud.com, urlUserDocs/urlAdminDocs). Only the heading and
 * the legal-notice / privacy-policy buttons (when configured) remain.
 * The page is unreachable from the UI while knowledgebaseenabled=false, but
 * still answers on direct URL — hence the override. RE-SYNC against the stock
 * template after every Nextcloud upgrade.
 */
\OCP\Util::addStyle('settings', 'help');
?>
<?php if ($_['knowledgebaseEmbedded'] === true) : ?>
	<div id="app-navigation" role="navigation" tabindex="0">
	</div>

	<div id="app-content" class="help-includes">
		<iframe src="<?php print_unescaped($_['url']); ?>" class="help-iframe" tabindex="0">
		</iframe>
	</div>
<?php else: ?>
	<div id="app-content">
		<div class="help-wrapper">
			<div class="help-content">
				<h2 class="help-content__heading">
					<?php p($l->t('Nextcloud help & privacy resources')); ?>
				</h2>
				<div class="help-content__body">
				<?php if ($_['legalNoticeUrl']) { ?>
					<a href="<?php print_unescaped($_['legalNoticeUrl']); ?>" class="button" target="_blank" rel="noreferrer noopener">
						<?php p($l->t('Legal notice')); ?> ↗
					</a>
				<?php } ?>
				<?php if ($_['privacyUrl']) { ?>
					<a href="<?php print_unescaped($_['privacyUrl']); ?>" class="button" target="_blank" rel="noreferrer noopener">
						<?php p($l->t('Privacy policy')); ?> ↗
					</a>
				<?php } ?>
			</div>
		</div>
	</div>
<?php endif; ?>
