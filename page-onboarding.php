<?php
/**
 * Template Name: Customer Onboarding Hub
 *
 * Minimal chrome + interactive phone preview for live onboarding calls.
 * Reuses the same #jcp-app / phone-shell simulator as /prototype/.
 * Independent of app.jobcapturepro.com/onboarding (signup flow).
 *
 * @package JCP_Core
 */

$ios_url     = 'https://apps.apple.com/us/app/jobcapturepro/id6636248590';
$android_url = 'https://play.google.com/store/apps/details?id=com.jobcapturepro.mobile&pcampaignid=web_share';
$asset_base  = get_stylesheet_directory_uri() . '/assets';
$logo_url    = $asset_base . '/brand/jcp-logo-dark-320.webp';
$qr_ios      = $asset_base . '/brand/qr/app-store-ios.svg';
$qr_android  = $asset_base . '/brand/qr/google-play-android.svg';
$badge_ios   = $asset_base . '/badges/app-store-black.svg';
$badge_android = $asset_base . '/badges/google-play-badge.svg';
$privacy_url = 'https://jobcapturepro.com/privacy-policy/';
$support_url = home_url( '/support/' );
$year        = (int) gmdate( 'Y' );

$ob_title = __( 'Getting Started | JobCapturePro Onboarding', 'jcp-core' );
add_filter(
	'pre_get_document_title',
	static function () use ( $ob_title ) {
		return $ob_title;
	},
	99
);
add_filter(
	'rank_math/frontend/title',
	static function () use ( $ob_title ) {
		return $ob_title;
	},
	20
);
add_filter(
	'rank_math/frontend/robots',
	static function ( $robots ) {
		if ( ! is_array( $robots ) ) {
			$robots = [];
		}
		$robots['index']  = 'noindex';
		$robots['follow'] = 'follow';
		return $robots;
	},
	20
);
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="robots" content="noindex, follow">
	<meta name="description" content="<?php echo esc_attr__( 'JobCapturePro onboarding: download the app and walk through your first Check-In.', 'jcp-core' ); ?>">
	<script>
		window.JCP_IS_PROTOTYPE = true;
		window.JCP_IS_DEMO_MODE = false;
		window.JCP_IS_ONBOARDING_HUB = true;
	</script>
	<?php wp_head(); ?>
</head>
<body <?php body_class( 'jcp-onboarding-page jcp-prototype-page jcp-phone-shell' ); ?>>
<a class="jcp-ob-skip" href="#jcp-ob-preview"><?php esc_html_e( 'Skip to app preview', 'jcp-core' ); ?></a>

<header class="jcp-ob-header">
	<a class="jcp-ob-logo" href="<?php echo esc_url( home_url( '/' ) ); ?>" aria-label="<?php esc_attr_e( 'JobCapturePro home', 'jcp-core' ); ?>">
		<img
			src="<?php echo esc_url( $logo_url ); ?>"
			alt="JobCapturePro"
			width="160"
			height="36"
			decoding="async"
		/>
	</a>
</header>

<main class="jcp-ob-main">
	<section class="jcp-ob-hero" aria-labelledby="jcp-ob-title">
		<p class="jcp-ob-eyebrow"><?php esc_html_e( 'Welcome to JobCapturePro', 'jcp-core' ); ?></p>
		<h1 id="jcp-ob-title"><?php esc_html_e( "Let's get you up and running.", 'jcp-core' ); ?></h1>
		<p class="jcp-ob-lead">
			<?php esc_html_e( 'Use this page during onboarding to download the app and walk through your first Check-In.', 'jcp-core' ); ?>
		</p>
	</section>

	<section class="jcp-ob-stage" id="jcp-ob-preview" aria-labelledby="jcp-ob-preview-title">
		<div class="jcp-ob-stage__preview">
			<p id="jcp-ob-preview-title" class="jcp-ob-phone-label">
				<?php esc_html_e( 'Mobile app preview', 'jcp-core' ); ?>
			</p>
			<div class="jcp-ob-phone" data-jcp-ob-preview>
				<?php /* Must be data-jcp-page="prototype" — jcp-render.js only boots the phone shell for prototype|demo. */ ?>
				<div id="jcp-app" data-jcp-page="prototype" data-demo-mode="false"></div>
			</div>
		</div>

		<aside class="jcp-ob-downloads" aria-labelledby="jcp-ob-download-title">
			<h2 id="jcp-ob-download-title"><?php esc_html_e( 'Get the JCP mobile app', 'jcp-core' ); ?></h2>
			<p class="jcp-ob-downloads__lead">
				<?php esc_html_e( 'Scan with your phone or choose your app store.', 'jcp-core' ); ?>
			</p>

			<div class="jcp-ob-store-grid">
				<a
					class="jcp-ob-store-card"
					href="<?php echo esc_url( $ios_url ); ?>"
					target="_blank"
					rel="noopener noreferrer"
					data-jcp-store="ios"
				>
					<span class="jcp-ob-store-card__platform"><?php esc_html_e( 'iPhone', 'jcp-core' ); ?></span>
					<span class="jcp-ob-store-card__qr">
						<img
							src="<?php echo esc_url( $qr_ios ); ?>"
							alt="<?php esc_attr_e( 'QR code to download JobCapturePro on the App Store', 'jcp-core' ); ?>"
							width="168"
							height="168"
							decoding="async"
						/>
					</span>
					<img
						class="jcp-ob-badge jcp-ob-badge--ios"
						src="<?php echo esc_url( $badge_ios ); ?>"
						alt="<?php esc_attr_e( 'Download on the App Store', 'jcp-core' ); ?>"
						width="120"
						height="40"
						decoding="async"
					/>
				</a>

				<a
					class="jcp-ob-store-card"
					href="<?php echo esc_url( $android_url ); ?>"
					target="_blank"
					rel="noopener noreferrer"
					data-jcp-store="android"
				>
					<span class="jcp-ob-store-card__platform"><?php esc_html_e( 'Android', 'jcp-core' ); ?></span>
					<span class="jcp-ob-store-card__qr">
						<img
							src="<?php echo esc_url( $qr_android ); ?>"
							alt="<?php esc_attr_e( 'QR code to download JobCapturePro on Google Play', 'jcp-core' ); ?>"
							width="168"
							height="168"
							decoding="async"
						/>
					</span>
					<img
						class="jcp-ob-badge jcp-ob-badge--android"
						src="<?php echo esc_url( $badge_android ); ?>"
						alt="<?php esc_attr_e( 'Get it on Google Play', 'jcp-core' ); ?>"
						width="135"
						height="40"
						decoding="async"
					/>
				</a>
			</div>

			<div class="jcp-ob-downloads__note">
				<strong><?php esc_html_e( 'Using an integrated CRM or photo workflow?', 'jcp-core' ); ?></strong>
				<span><?php esc_html_e( 'You may not need the JCP app.', 'jcp-core' ); ?></span>
			</div>
		</aside>
	</section>
</main>

<footer class="jcp-ob-footer">
	<p class="jcp-ob-footer__copy">&copy; <?php echo esc_html( (string) $year ); ?> JobCapturePro</p>
	<nav class="jcp-ob-footer__nav" aria-label="<?php esc_attr_e( 'Legal and support', 'jcp-core' ); ?>">
		<a href="<?php echo esc_url( $privacy_url ); ?>"><?php esc_html_e( 'Privacy Policy', 'jcp-core' ); ?></a>
		<a href="<?php echo esc_url( $support_url ); ?>"><?php esc_html_e( 'Support', 'jcp-core' ); ?></a>
	</nav>
</footer>

<?php wp_footer(); ?>
</body>
</html>
