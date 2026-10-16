<?php
/**
 * Template Name: App Prototype
 *
 * Minimal page: no header, no footer. Centered phone simulator only.
 * Full interactive app (no "Start Demo" or guided tour). Internal UX lab only.
 *
 * @package JCP_Core
 */
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="robots" content="noindex, nofollow">
	<script>
		window.JCP_IS_PROTOTYPE = true;
		window.JCP_IS_DEMO_MODE = false;
	</script>
	<?php wp_head(); ?>
	<style>
		body.jcp-prototype-page {
			margin: 0;
			padding: 0;
			min-height: 100vh;
			display: flex;
			align-items: center;
			justify-content: center;
			background: #ffffff;
		}
		.prototype-page-layout {
			display: flex;
			align-items: center;
			justify-content: center;
			min-height: 100vh;
			width: 100%;
		}
		.prototype-page-layout #jcp-app {
			flex: 0 0 auto;
		}
		body.jcp-prototype-page .demo-container {
			width: 100%;
			max-width: 100%;
			justify-content: center;
			align-items: center;
			min-height: 100vh;
			padding: 0;
		}
		body.jcp-prototype-page .phone-wrapper {
			margin: 0 auto;
		}
		/* Phone-shell hide rules live in assets/shared/assets/demo.css (body.jcp-phone-shell) */
	</style>
</head>
<body <?php body_class( 'jcp-prototype-page jcp-phone-shell' ); ?>>
<div class="prototype-page-layout">
	<div id="jcp-app" data-jcp-page="prototype" data-demo-mode="false"></div>
</div>
<?php wp_footer(); ?>
</body>
</html>
