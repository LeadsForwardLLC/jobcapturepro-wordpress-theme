<?php
/**
 * Mobile: copy or share a personalized demo link for desktop.
 *
 * @package JCP_Core
 */
?>
<div class="survey-desktop-handoff" id="surveyDesktopHandoff" hidden>
	<p class="survey-desktop-handoff-title"><?php esc_html_e( 'Continue on your computer', 'jcp-core' ); ?></p>
	<p class="survey-desktop-handoff-text">
		<?php esc_html_e( 'The live demo works best on a bigger screen. Copy or share this link, then open it on a desktop or laptop.', 'jcp-core' ); ?>
	</p>
	<div class="survey-desktop-handoff-actions">
		<button type="button" class="survey-btn survey-btn-handoff" data-action="copy-demo-link" id="surveyCopyDemoLink">
			<?php esc_html_e( 'Copy link', 'jcp-core' ); ?>
		</button>
		<button type="button" class="survey-btn survey-btn-handoff secondary" data-action="share-demo-link" id="surveyShareDemoLink">
			<?php esc_html_e( 'Share link', 'jcp-core' ); ?>
		</button>
	</div>
	<p class="survey-desktop-handoff-status" id="surveyDesktopHandoffStatus" aria-live="polite"></p>
</div>
