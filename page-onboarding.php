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
	<meta name="description" content="<?php echo esc_attr__( 'Get started with JobCapturePro. Explore the mobile app, download it for iPhone or Android, and learn how JCP works with your existing job photo workflow.', 'jcp-core' ); ?>">
	<script>
		window.JCP_IS_PROTOTYPE = true;
		window.JCP_IS_DEMO_MODE = false;
		window.JCP_IS_ONBOARDING_HUB = true;
	</script>
	<?php wp_head(); ?>
</head>
<body <?php body_class( 'jcp-onboarding-page jcp-phone-shell' ); ?>>
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
		<h1 id="jcp-ob-title"><?php esc_html_e( "Let's get your jobs working for you.", 'jcp-core' ); ?></h1>
		<p class="jcp-ob-lead">
			<?php esc_html_e( "Welcome aboard! JobCapturePro turns the work your team already completes into valuable marketing proof. Explore the app below, or get ready to connect the tools your crew already uses. We'll help you get everything working during onboarding.", 'jcp-core' ); ?>
		</p>
	</section>

	<section class="jcp-ob-stage" id="jcp-ob-preview" aria-labelledby="jcp-ob-preview-title">
		<div class="jcp-ob-stage__preview">
			<div class="jcp-ob-preview-intro">
				<p class="jcp-ob-label"><?php esc_html_e( 'Interactive app preview', 'jcp-core' ); ?></p>
				<h2 id="jcp-ob-preview-title"><?php esc_html_e( 'Take a look around', 'jcp-core' ); ?></h2>
				<p><?php esc_html_e( 'Try the interactive preview to see how easy it is to capture a completed job with JobCapturePro.', 'jcp-core' ); ?></p>
			</div>
			<div class="jcp-ob-phone" data-jcp-ob-preview>
				<div id="jcp-app" data-jcp-page="onboarding" data-demo-mode="false"></div>
			</div>
		</div>

		<aside class="jcp-ob-downloads" aria-labelledby="jcp-ob-download-title">
			<h2 id="jcp-ob-download-title"><?php esc_html_e( 'Ready to use JCP on your phone?', 'jcp-core' ); ?></h2>
			<p class="jcp-ob-downloads__lead">
				<?php esc_html_e( "If you'll be creating Check-Ins directly with the JobCapturePro app, download it before your onboarding call so you're ready to go.", 'jcp-core' ); ?>
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
						class="jcp-ob-badge"
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
						class="jcp-ob-badge"
						src="<?php echo esc_url( $badge_android ); ?>"
						alt="<?php esc_attr_e( 'Get it on Google Play', 'jcp-core' ); ?>"
						width="135"
						height="40"
						decoding="async"
					/>
				</a>
			</div>

			<p class="jcp-ob-downloads__note">
				<?php esc_html_e( 'Using a supported CRM or photo app instead? You may not need to install anything — see below.', 'jcp-core' ); ?>
			</p>
		</aside>
	</section>

	<section class="jcp-ob-alt" aria-labelledby="jcp-ob-alt-title">
		<h2 id="jcp-ob-alt-title"><?php esc_html_e( 'Already using a CRM or photo app?', 'jcp-core' ); ?></h2>
		<p>
			<?php esc_html_e( "Your crew may not need another app. If you're already capturing job photos in a supported system, JobCapturePro can use your existing workflow to turn completed jobs into marketing proof.", 'jcp-core' ); ?>
		</p>
		<p class="jcp-ob-alt__support">
			<?php esc_html_e( "During onboarding, we'll review your setup and help connect the right tools where supported.", 'jcp-core' ); ?>
		</p>
		<p class="jcp-ob-alt__platforms">
			<span><?php esc_html_e( 'Examples of supported capture workflows:', 'jcp-core' ); ?></span>
			<strong>Housecall Pro</strong>
			<span aria-hidden="true">·</span>
			<strong>CompanyCam</strong>
			<span aria-hidden="true">·</span>
			<strong><?php esc_html_e( 'Phone camera roll', 'jcp-core' ); ?></strong>
		</p>
		<p class="jcp-ob-alt__reassure"><?php esc_html_e( 'No unnecessary extra steps for your crew.', 'jcp-core' ); ?></p>
	</section>

	<section class="jcp-ob-prep" aria-labelledby="jcp-ob-prep-title">
		<h2 id="jcp-ob-prep-title"><?php esc_html_e( 'A little prep goes a long way.', 'jcp-core' ); ?></h2>
		<p class="jcp-ob-prep__lead">
			<?php esc_html_e( "A few things to have ready so we can spend your onboarding getting JCP working, not tracking down logins.", 'jcp-core' ); ?>
		</p>
		<ol class="jcp-ob-prep__list">
			<li>
				<strong><?php esc_html_e( 'Know where your job photos live', 'jcp-core' ); ?></strong>
				<span><?php esc_html_e( "Are your technicians using their phone cameras, Housecall Pro, CompanyCam, or another system? We'll help you choose the right capture workflow.", 'jcp-core' ); ?></span>
			</li>
			<li>
				<strong><?php esc_html_e( 'Have your business logins handy', 'jcp-core' ); ?></strong>
				<span><?php esc_html_e( 'You may need access to your website, Google Business Profile, and existing CRM or photo platform. Never share passwords through this page.', 'jcp-core' ); ?></span>
			</li>
			<li>
				<strong><?php esc_html_e( 'Have a recent job in mind', 'jcp-core' ); ?></strong>
				<span><?php esc_html_e( 'If possible, have a few photos from a completed job ready so we can demonstrate the workflow using a real example.', 'jcp-core' ); ?></span>
			</li>
		</ol>
	</section>

	<section class="jcp-ob-close" aria-labelledby="jcp-ob-close-title">
		<h2 id="jcp-ob-close-title"><?php esc_html_e( "We'll take it from here.", 'jcp-core' ); ?></h2>
		<p>
			<?php esc_html_e( "You don't need to figure everything out on your own. Your onboarding specialist will walk you through the setup and help you get your first real Check-In working.", 'jcp-core' ); ?>
		</p>
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
