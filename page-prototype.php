<?php
/**
 * Template Name: App Prototype
 *
 * Minimal page: no header, no footer. Phone simulator + right-side Component Reference Panel.
 * Full interactive app (no "Start Demo" or guided tour). Internal UX lab only.
 * Panel is canonical UX reference for FlutterFlow; not shown on demo or production.
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
			flex-direction: row;
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
			margin: 0;
		}
		/* Phone-shell hide rules live in assets/shared/assets/demo.css (body.jcp-phone-shell) */
		/* Component Reference Panel — no box; vertically centered next to phone */
		.component-reference-panel {
			width: 380px;
			flex-shrink: 0;
			padding: 24px 20px 0 40px;
			box-sizing: border-box;
			background: transparent !important;
			border: none !important;
		}
		.component-reference-panel ul {
			margin: 0.5em 0 0 0;
			padding-left: 1.25em;
		}
		.component-reference-panel li {
			margin-bottom: 0.35em;
		}
		.component-reference-content {
			transition: opacity 175ms ease;
		}
		.component-reference-note {
			font-size: 0.8rem;
			color: #6b7280;
			margin: 0.75em 0 0 0;
			line-height: 1.4;
		}
	</style>
</head>
<body <?php body_class( 'jcp-prototype-page jcp-phone-shell' ); ?>>
<div class="prototype-page-layout">
	<div id="jcp-app" data-jcp-page="prototype" data-demo-mode="false"></div>
	<?php get_template_part( 'templates/partials/component-reference-panel' ); ?>
</div>
<?php wp_footer(); ?>
<script>
(function() {
	if (!document.getElementById('component-reference-panel')) return;

	var SCREEN_COMPONENTS = {
		'home-screen': {
			title: 'This screen uses:',
			items: [
				'Header / Check-ins',
				'Location Chip',
				'Filter Tabs',
				'Check-In Card',
				'Primary FAB',
				'Bottom Navigation'
			]
		},
		'new-screen': {
			title: 'This screen uses:',
			items: [
				'Header / New Check-In',
				'Image Capture Action',
				'Image Upload Action',
				'Primary Action Button',
				'Bottom Navigation'
			]
		},
		'edit-screen': {
			title: 'This screen uses:',
			items: [
				'Header / Edit Check-In',
				'Status Badge (Published / Not Published)',
				'Location Summary Card',
				'Media Gallery',
				'Add Media Action',
				'AI Description Field',
				'Regenerate Description Action',
				'Primary Save Action',
				'Secondary Destructive Action (Archive)',
				'Review Request Action'
			]
		},
		'checkin-creation-screen': {
			title: 'This screen uses:',
			items: [
				'Header / Check-In Creation',
				'Progress Indicator (Async Creation)',
				'Step Status List',
				'Processing State Messaging',
				'Disabled Primary Action'
			],
			note: '⚠ This screen does not currently exist in the app. Recommended to implement to improve perceived performance and UX continuity.'
		},
		'request-review-screen': {
			title: 'This screen uses:',
			items: [
				'Header / Request Review',
				'Location Context Display',
				'Review Message Input',
				'Job Summary Card',
				'Media Preview',
				'Primary Action Button (Send Review Request)'
			]
		},
		'profile-screen': {
			title: 'This screen uses:',
			items: [
				'Header / Profile',
				'User Identity Card',
				'Account Settings Actions',
				'Primary Destructive Action (Log Out)',
				'Secondary Destructive Action (Delete Account)',
				'Bottom Navigation'
			]
		},
		'edit-profile-screen': {
			title: 'This screen uses:',
			items: [
				'Header / Edit Profile',
				'Profile Photo Editor',
				'Text Input Field',
				'Primary Save Action'
			]
		},
		'change-password-screen': {
			title: 'This screen uses:',
			items: [
				'Header / Change Password',
				'Secure Text Input Fields',
				'Password Visibility Toggle',
				'Validation Messaging',
				'Primary Confirm Action',
				'Secondary Cancel Action'
			]
		}
	};

	var contentEl = document.getElementById('component-reference-content');

	function renderList(spec) {
		if (!spec || !spec.items || !spec.items.length) return '';
		var html = '<p><strong>' + (spec.title || 'This screen uses:') + '</strong></p><ul>';
		spec.items.forEach(function(item) {
			html += '<li>' + item + '</li>';
		});
		html += '</ul>';
		if (spec.note) {
			html += '<p class="component-reference-note">' + spec.note + '</p>';
		}
		return html;
	}

	function setContent(html) {
		contentEl.innerHTML = html;
	}

	function updatePanel(screenId) {
		var spec = SCREEN_COMPONENTS[screenId];
		contentEl.style.opacity = '0';
		contentEl.addEventListener('transitionend', function onOut() {
			contentEl.removeEventListener('transitionend', onOut);
			setContent(spec ? renderList(spec) : '');
			contentEl.offsetHeight;
			contentEl.style.opacity = '1';
		}, { once: true });
	}

	function syncFromDOM() {
		var active = document.querySelector('.app-screen.active');
		var id = active ? active.id : null;
		if (id && SCREEN_COMPONENTS[id]) {
			setContent(renderList(SCREEN_COMPONENTS[id]));
			contentEl.style.opacity = '1';
		} else {
			setContent('');
		}
	}

	window.addEventListener('jcp-prototype-screen-change', function(e) {
		if (e.detail && e.detail.screenId) updatePanel(e.detail.screenId);
	});

	function runSync() {
		setTimeout(syncFromDOM, 100);
		setTimeout(syncFromDOM, 500);
	}
	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', runSync);
	} else {
		runSync();
	}
})();
</script>
</body>
</html>
