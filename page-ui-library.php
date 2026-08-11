<?php
/**
 * Template Name: UI Library (Internal)
 *
 * INTERNAL-ONLY page: single source of truth for all JobCapturePro UI components.
 * Not linked in navigation; NOINDEX/NOFOLLOW. URL: /ui-library.
 *
 * @package JCP_Core
 */

get_header(); 

// Get icon helper function (returns escaped URL for safe output)
$icon = function( $name ) {
    $path = '/assets/shared/assets/icons/lucide/' . sanitize_file_name( $name ) . '.svg';
    return esc_url( get_stylesheet_directory_uri() . $path );
};
?>

<style>
	/* Prevent indexing */
	body.page-ui-library {
		--ui-library-page: true;
	}
</style>

<div class="jcp-shell">
	<main class="jcp-marketing">
		
		<!-- ============================================================
		     PAGE HEADER
		     ============================================================ -->
		<section class="jcp-section">
			<div class="jcp-container">
				<h1>UI Library</h1>
				<p class="jcp-hero-subtitle">Single source of truth for all JobCapturePro UI components. Every component shown here is the canonical version used across all pages.</p>
				<p style="font-size: var(--jcp-font-size-sm); color: var(--jcp-color-text-secondary);"><strong>Status:</strong> Internal documentation • Not indexed • Developers only</p>
			</div>
		</section>

		<!-- Typography section removed per user request - only showing UI components -->

		<!-- ============================================================
		     BUTTONS
		     ============================================================ -->
		<section class="jcp-section" style="background: var(--jcp-color-bg-secondary);">
			<div class="jcp-container">
				<h2 style="margin-bottom: var(--jcp-space-3xl);">Buttons</h2>
				
				<div style="margin-bottom: var(--jcp-space-4xl);">
					<h3 style="font-size: var(--jcp-font-size-lg); margin-bottom: var(--jcp-space-lg);">Button Variants</h3>
					<div class="jcp-actions" style="flex-wrap: wrap; gap: var(--jcp-space-md);">
						<a class="btn btn-primary" href="#">Primary Button</a>
						<a class="btn btn-secondary" href="#">Border Button</a>
					</div>
				</div>


				<div>
					<h3 style="font-size: var(--jcp-font-size-lg); margin-bottom: var(--jcp-space-lg);">Buttons with Icons</h3>
					<div class="jcp-actions" style="flex-wrap: wrap; gap: var(--jcp-space-md);">
						<a class="btn btn-primary" href="#">
							<span>Online Demo</span>
							<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
								<path d="M5 12h14M13 5l7 7-7 7"/>
							</svg>
						</a>
						<a class="btn btn-secondary" href="#">
							<span>Learn More</span>
							<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
								<path d="M5 12h14M13 5l7 7-7 7"/>
							</svg>
						</a>
					</div>
				</div>
			</div>
		</section>

		<!-- ============================================================
		     HERO SECTION
		     ============================================================ -->
		<section class="jcp-section">
			<div class="jcp-container">
				<h2 style="margin-bottom: var(--jcp-space-3xl);">Hero Section</h2>
			</div>
		</section>

		<section class="directory-hero jcp-hero jcp-hero--standard">
			<div class="hero-split jcp-grid-2">
				<div class="hero-copy jcp-hero-copy">
					<h1 class="jcp-hero-title">Automatically turn every completed job into more <span class="jcp-hero-rotating-word" aria-live="polite">visibility</span></h1>
					<p class="jcp-hero-subtitle">
						Your team already takes job photos. JobCapturePro turns those jobs into website updates, Google visibility, social posts, and directory listings — and your crew can ask for a review on site with a QR code.
					</p>
					<div class="directory-cta-row jcp-actions">
						<a class="btn btn-primary directory-cta directory-cta-secondary" href="/demo">Watch the Live Demo</a>
						<a class="btn btn-secondary directory-cta" href="#how-it-works">Learn how it works</a>
					</div>
					<div class="directory-meta">
						<div class="meta-item">
							<div class="meta-label">
								<img src="<?php echo $icon('camera'); ?>" class="meta-icon" alt="">
								<strong>1 photo</strong>
							</div>
							<span>proof everywhere</span>
						</div>
						<div class="meta-item">
							<div class="meta-label">
								<img src="<?php echo $icon('map'); ?>" class="meta-icon" alt="">
								<strong>4 channels</strong>
							</div>
							<span>website, directory, GBP, social</span>
						</div>
						<div class="meta-item">
							<div class="meta-label">
								<img src="<?php echo $icon('clock'); ?>" class="meta-icon" alt="">
								<strong>0 busywork</strong>
							</div>
							<span>zero admin work</span>
						</div>
					</div>
				</div>
				<div class="hero-visual jcp-hero-visual">
					<div class="hero-proof-stack">
						<div class="hero-media-card">
							<div class="hero-media-glow" aria-hidden="true"></div>
							<div class="hero-media-content">
								<div class="hero-media-title">Verified job proof</div>
								<div class="hero-media-subtitle">AI check-ins appear in minutes</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</section>

		<!-- ============================================================
		     HOW IT WORKS SECTION
		     ============================================================ -->
		<section class="jcp-section rankings-section">
			<div class="jcp-container">
				<h2 style="margin-bottom: var(--jcp-space-3xl);">How It Works Section</h2>
			</div>
		</section>

		<section class="jcp-section rankings-section" id="how-it-works">
			<div class="jcp-container">
				<div class="rankings-header">
					<h2>How JobCapturePro works</h2>
					<p class="rankings-subtitle">
						Every completed job becomes verified proof across every channel that matters. Here's the simple flow your crew already knows.
					</p>
				</div>

				<div class="how-it-works-timeline">
					<h3 class="timeline-title">The proof pipeline</h3>
					<div class="timeline-steps">
						<div class="timeline-step">
							<div class="step-number">1</div>
							<div class="step-content">
								<h4 class="step-title">Capture</h4>
								<p class="step-description">Crew snaps a photo or the job completes in your CRM.</p>
							</div>
						</div>
						<div class="timeline-connector"></div>
						<div class="timeline-step">
							<div class="step-number">2</div>
							<div class="step-content">
								<h4 class="step-title">AI Check-In</h4>
								<p class="step-description">JobCapturePro generates the full check-in automatically.</p>
							</div>
						</div>
						<div class="timeline-connector"></div>
						<div class="timeline-step">
							<div class="step-number">3</div>
							<div class="step-content">
								<h4 class="step-title">Publish</h4>
								<p class="step-description">Website, directory, GBP, and social update instantly.</p>
							</div>
						</div>
						<div class="timeline-connector"></div>
						<div class="timeline-step">
							<div class="step-number">4</div>
							<div class="step-content">
								<h4 class="step-title">Review</h4>
								<p class="step-description">Crew shows a QR code so customers can review on the spot.</p>
							</div>
						</div>
					</div>
				</div>
			</div>
		</section>

		<!-- ============================================================
		     FEATURES / BENEFITS GRID
		     ============================================================ -->
		<section class="jcp-section rankings-section">
			<div class="jcp-container">
				<h2 style="margin-bottom: var(--jcp-space-3xl);">Features / Benefits Grid</h2>
			</div>
		</section>

		<section class="jcp-section rankings-section" id="features">
			<div class="jcp-container">
				<div class="rankings-header">
					<h2>Benefits that show up in the market</h2>
					<p class="rankings-subtitle">
						JobCapturePro creates proof customers can see, rankings that improve, and demand that compounds.
					</p>
				</div>

				<div class="ranking-factors-grid">
					<div class="ranking-factor-card">
						<div class="factor-icon-wrapper">
							<img src="<?php echo $icon('badge-check'); ?>" class="factor-icon" alt="">
						</div>
						<h3 class="factor-title">Verified proof</h3>
						<p class="factor-description">Real check-ins replace claims with proof homeowners trust.</p>
						<div class="factor-stat">
							<span class="stat-value">Proof</span>
							<span class="stat-label">from real jobs</span>
						</div>
					</div>
					<div class="ranking-factor-card">
						<div class="factor-icon-wrapper">
							<img src="<?php echo $icon('map-pin'); ?>" class="factor-icon" alt="">
						</div>
						<h3 class="factor-title">Local visibility</h3>
						<p class="factor-description">Fresh job activity drives stronger map coverage and ranking.</p>
						<div class="factor-stat">
							<span class="stat-value">Map</span>
							<span class="stat-label">coverage grows</span>
						</div>
					</div>
					<div class="ranking-factor-card">
						<div class="factor-icon-wrapper">
							<img src="<?php echo $icon('message-square'); ?>" class="factor-icon" alt="">
						</div>
						<h3 class="factor-title">Consistent presence</h3>
						<p class="factor-description">Social and GBP stay active without manual posting.</p>
						<div class="factor-stat">
							<span class="stat-value">Always</span>
							<span class="stat-label">on brand</span>
						</div>
					</div>
					<div class="ranking-factor-card">
						<div class="factor-icon-wrapper">
							<img src="<?php echo $icon('star'); ?>" class="factor-icon" alt="">
						</div>
						<h3 class="factor-title">More reviews</h3>
						<p class="factor-description">Requests go out while the job is fresh and credible.</p>
						<div class="factor-stat">
							<span class="stat-value">Reviews</span>
							<span class="stat-label">on autopilot</span>
						</div>
					</div>
				</div>
			</div>
		</section>

		<!-- ============================================================
		     WHO IT'S FOR SECTION
		     ============================================================ -->
		<section class="jcp-section rankings-section">
			<div class="jcp-container">
				<h2 style="margin-bottom: var(--jcp-space-3xl);">Who It's For Section</h2>
			</div>
		</section>

		<section class="jcp-section rankings-section" id="who-its-for">
			<div class="jcp-container">
				<div class="rankings-header">
					<h2>Built for contractors, owners, and crews</h2>
					<p class="rankings-subtitle">
						Designed for real job sites, real schedules, and real growth goals.
					</p>
				</div>

				<div class="guarantees-grid">
					<div class="guarantee-item">
						<div class="guarantee-icon">
							<img src="<?php echo $icon('hard-hat'); ?>" alt="">
						</div>
						<div class="guarantee-content">
							<strong>Contractors & Trades</strong>
							<p>Turn every completed job into proof that wins the next one.</p>
						</div>
					</div>
					<div class="guarantee-item">
						<div class="guarantee-icon">
							<img src="<?php echo $icon('briefcase'); ?>" alt="">
						</div>
						<div class="guarantee-content">
							<strong>Owners & Office Teams</strong>
							<p>Automate visibility without chasing photos or posts.</p>
						</div>
					</div>
					<div class="guarantee-item">
						<div class="guarantee-icon">
							<img src="<?php echo $icon('camera'); ?>" alt="">
						</div>
						<div class="guarantee-content">
							<strong>Field Crews</strong>
							<p>Capture once and move on — no extra admin work.</p>
						</div>
					</div>
				</div>
			</div>
		</section>

		<!-- ============================================================
		     DIRECTORY CARDS
		     ============================================================ -->
		<section class="jcp-section rankings-section">
			<div class="jcp-container">
				<h2 style="margin-bottom: var(--jcp-space-3xl);">Directory Cards</h2>
			</div>
		</section>

		<section class="jcp-section rankings-section directory-preview">
			<div class="jcp-container">
				<div class="rankings-header">
					<h2>Preview the live directory</h2>
					<p class="rankings-subtitle">
						This is exactly what prospects see — real work, verified activity, and earned rankings.
					</p>
				</div>

				<div class="directory-grid preview-grid">
					<a class="directory-card" href="#">
						<span class="directory-badge verified">Verified</span>
						<div class="card-header">
							<div class="company-mark">
								<div class="company-avatar">SR</div>
							</div>
							<div class="card-header-content">
								<h3 class="card-name">Summit Roofing</h3>
							</div>
						</div>
						<div class="card-location">
							<img src="<?php echo $icon('map-pin'); ?>" class="lucide-icon lucide-icon-xs" alt="">
							<span>Austin, TX</span>
						</div>
						<div class="card-meta-row">
							<span class="meta-inline">
								<img src="<?php echo $icon('camera'); ?>" class="lucide-icon lucide-icon-xs" alt="">
								82 jobs
							</span>
							<span class="meta-divider">·</span>
							<span class="meta-inline">
								<img src="<?php echo $icon('clock'); ?>" class="lucide-icon lucide-icon-xs" alt="">
								Active recently
							</span>
						</div>
						<div class="card-rating">
							<div class="stars">★★★★★</div>
							<span class="rating-text">4.9 (120)</span>
						</div>
						<div class="card-footer">
							<span class="view-profile">View activity</span>
						</div>
					</a>

					<a class="directory-card" href="#">
						<span class="directory-badge trusted">Trusted Pro</span>
						<div class="card-header">
							<div class="company-mark">
								<div class="company-avatar">LP</div>
							</div>
							<div class="card-header-content">
								<h3 class="card-name">Lakeview Plumbing</h3>
							</div>
						</div>
						<div class="card-location">
							<img src="<?php echo $icon('map-pin'); ?>" class="lucide-icon lucide-icon-xs" alt="">
							<span>Dallas, TX</span>
						</div>
						<div class="card-meta-row">
							<span class="meta-inline">
								<img src="<?php echo $icon('camera'); ?>" class="lucide-icon lucide-icon-xs" alt="">
								64 jobs
							</span>
							<span class="meta-divider">·</span>
							<span class="meta-inline">
								<img src="<?php echo $icon('clock'); ?>" class="lucide-icon lucide-icon-xs" alt="">
								Active today
							</span>
						</div>
						<div class="card-rating">
							<div class="stars">★★★★★</div>
							<span class="rating-text">4.8 (98)</span>
						</div>
						<div class="card-footer">
							<span class="view-profile">View activity</span>
						</div>
					</a>
				</div>
			</div>
		</section>

		<!-- ============================================================
		     FAQ SECTION
		     ============================================================ -->
		<section class="jcp-section rankings-section">
			<div class="jcp-container">
				<h2 style="margin-bottom: var(--jcp-space-3xl);">FAQ Section</h2>
			</div>
		</section>

		<section class="jcp-section rankings-section faq-section">
			<div class="jcp-container">
				<div class="rankings-header">
					<h2>FAQ</h2>
					<p class="rankings-subtitle">Clear answers for contractors evaluating the system.</p>
				</div>
				<div class="faq-grid">
					<details class="faq-item">
						<summary>How fast can we launch?</summary>
						<p>Most teams are live in days. Once your crew uploads a job photo, JobCapturePro handles the rest.</p>
					</details>
					<details class="faq-item">
						<summary>Do crews need to learn new tools?</summary>
						<p>No. They only capture a photo or complete a job. The AI builds the check‑in and publishes.</p>
					</details>
					<details class="faq-item">
						<summary>Where does proof get published?</summary>
						<p>Your website, the JobCapturePro directory, Google Business Profile, and social — all automated.</p>
					</details>
					<details class="faq-item">
						<summary>Is this real activity or staged content?</summary>
						<p>Everything is tied to real jobs with location and timestamp verification.</p>
					</details>
				</div>
			</div>
		</section>

		<!-- ============================================================
		     CTA SECTION
		     ============================================================ -->
		<section class="jcp-section rankings-section">
			<div class="jcp-container">
				<h2 style="margin-bottom: var(--jcp-space-3xl);">CTA Section</h2>
			</div>
		</section>

		<section class="jcp-section rankings-section">
			<div class="jcp-container">
				<div class="rankings-cta">
					<div class="cta-content">
						<h3>Ready to see it live?</h3>
						<p>Watch the demo and see how one job turns into real demand.</p>
					</div>
					<a class="btn rankings-cta-btn" href="/demo">Watch the Live Demo</a>
				</div>
			</div>
		</section>

		<!-- ============================================================
		     SINGLE POST (BLOG)
		     ============================================================ -->
		<section class="jcp-section rankings-section">
			<div class="jcp-container">
				<h2 style="margin-bottom: var(--jcp-space-3xl);">Single Post (Blog)</h2>
			</div>
		</section>

		<section class="jcp-section rankings-section">
			<div class="jcp-container">
				<div class="rankings-header">
					<h1>Example blog post title</h1>
					<div class="jcp-post-meta jcp-single-hero-meta">
						<div class="jcp-post-meta-line jcp-post-meta-author-line">
							<a href="#" class="jcp-post-meta-author" rel="author">
								<img src="<?php echo esc_url( get_avatar_url( 1, [ 'size' => 36 ] ) ); ?>" class="jcp-post-meta-avatar" alt="" width="36" height="36">
								<span class="jcp-post-meta-author-name">Author Name</span>
							</a>
						</div>
						<div class="jcp-post-meta-line jcp-post-meta-details">
							<time datetime="2025-01-15" class="jcp-post-date">January 15, 2025</time>
							<span class="jcp-post-meta-sep" aria-hidden="true">·</span>
							<span class="jcp-post-categories">
								<a href="#" class="jcp-post-category">Category</a>
							</span>
							<span class="jcp-post-meta-sep" aria-hidden="true">·</span>
							<span class="jcp-post-reading-time">5 min read</span>
						</div>
					</div>
				</div>
				<div class="jcp-single-hero-image-wrapper">
					<img src="https://jobcapturepro.com/wp-content/uploads/2025/12/jcp-map-bg-light.jpg" alt="" class="jcp-single-post-featured-img" loading="eager" />
				</div>
			</div>
		</section>

		<!-- ============================================================
		     FORMS
		     ============================================================ -->
		<section class="jcp-section rankings-section">
			<div class="jcp-container">
				<h2 style="margin-bottom: var(--jcp-space-3xl);">Forms</h2>
				<div class="rankings-header">
					<h3 style="margin-bottom: var(--jcp-space-lg);">Inputs &amp; submit</h3>
					<p class="rankings-subtitle">Used on contact, early-access, and survey flows.</p>
				</div>
				<div style="max-width: 480px;">
					<div style="margin-bottom: var(--jcp-space-lg);">
						<label for="ui-lib-name" style="display: block; font-size: var(--jcp-font-size-sm); font-weight: var(--jcp-font-weight-semibold); color: var(--jcp-color-text-primary); margin-bottom: var(--jcp-space-xs);">Name</label>
						<input type="text" id="ui-lib-name" name="name" placeholder="Your name" style="width: 100%; padding: var(--jcp-space-sm) var(--jcp-space-md); border-radius: var(--jcp-radius-md); border: 2px solid var(--jcp-color-border); font-size: var(--jcp-font-size-base); color: var(--jcp-color-text-primary); background: var(--jcp-color-bg-primary); box-sizing: border-box;">
					</div>
					<div style="margin-bottom: var(--jcp-space-lg);">
						<label for="ui-lib-email" style="display: block; font-size: var(--jcp-font-size-sm); font-weight: var(--jcp-font-weight-semibold); color: var(--jcp-color-text-primary); margin-bottom: var(--jcp-space-xs);">Email</label>
						<input type="email" id="ui-lib-email" name="email" placeholder="you@example.com" style="width: 100%; padding: var(--jcp-space-sm) var(--jcp-space-md); border-radius: var(--jcp-radius-md); border: 2px solid var(--jcp-color-border); font-size: var(--jcp-font-size-base); color: var(--jcp-color-text-primary); background: var(--jcp-color-bg-primary); box-sizing: border-box;">
					</div>
					<div style="margin-bottom: var(--jcp-space-lg);">
						<label for="ui-lib-message" style="display: block; font-size: var(--jcp-font-size-sm); font-weight: var(--jcp-font-weight-semibold); color: var(--jcp-color-text-primary); margin-bottom: var(--jcp-space-xs);">Message</label>
						<textarea id="ui-lib-message" name="message" rows="4" placeholder="Your message…" style="width: 100%; padding: var(--jcp-space-sm) var(--jcp-space-md); border-radius: var(--jcp-radius-md); border: 2px solid var(--jcp-color-border); font-size: var(--jcp-font-size-base); color: var(--jcp-color-text-primary); background: var(--jcp-color-bg-primary); font-family: inherit; box-sizing: border-box; resize: vertical;"></textarea>
					</div>
					<button type="button" class="btn btn-primary">Submit</button>
				</div>
			</div>
		</section>

		<!-- ============================================================
		     PRICING COMPARISON TABLE
		     ============================================================ -->
		<section class="jcp-section rankings-section">
			<div class="jcp-container">
				<h2 style="margin-bottom: var(--jcp-space-3xl);">Pricing Comparison Table</h2>
			</div>
		</section>

		<section class="jcp-section rankings-section">
			<div class="jcp-container">
				<div class="rankings-header">
					<h2>Plan comparison</h2>
					<p class="rankings-subtitle">Same structure as live pricing page.</p>
				</div>
				<div class="jcp-compare-table">
					<div class="jcp-compare-row jcp-compare-head">
						<div>Feature</div>
						<div>Starter</div>
						<div>Scale</div>
						<div>Enterprise</div>
					</div>
					<div class="jcp-compare-row jcp-compare-group">
						<div>Photo capture</div>
						<div><img src="<?php echo $icon( 'check' ); ?>" class="lucide-icon lucide-icon-xs" alt="Included"></div>
						<div><img src="<?php echo $icon( 'check' ); ?>" class="lucide-icon lucide-icon-xs" alt="Included"></div>
						<div><img src="<?php echo $icon( 'check' ); ?>" class="lucide-icon lucide-icon-xs" alt="Included"></div>
					</div>
					<div class="jcp-compare-row">
						<div>CRM integration</div>
						<div><img src="<?php echo $icon( 'x' ); ?>" class="lucide-icon lucide-icon-xs" alt="Not available"></div>
						<div><img src="<?php echo $icon( 'check' ); ?>" class="lucide-icon lucide-icon-xs" alt="Included"></div>
						<div><img src="<?php echo $icon( 'check' ); ?>" class="lucide-icon lucide-icon-xs" alt="Included"></div>
					</div>
					<div class="jcp-compare-row jcp-compare-group">
						<div>Website publishing</div>
						<div><img src="<?php echo $icon( 'check' ); ?>" class="lucide-icon lucide-icon-xs" alt="Included"></div>
						<div><img src="<?php echo $icon( 'check' ); ?>" class="lucide-icon lucide-icon-xs" alt="Included"></div>
						<div><img src="<?php echo $icon( 'check' ); ?>" class="lucide-icon lucide-icon-xs" alt="Included"></div>
					</div>
					<div class="jcp-compare-row">
						<div>Social publishing</div>
						<div><img src="<?php echo $icon( 'x' ); ?>" class="lucide-icon lucide-icon-xs" alt="Not available"></div>
						<div><img src="<?php echo $icon( 'check' ); ?>" class="lucide-icon lucide-icon-xs" alt="Included"></div>
						<div><img src="<?php echo $icon( 'check' ); ?>" class="lucide-icon lucide-icon-xs" alt="Included"></div>
					</div>
				</div>
			</div>
		</section>

		<!-- ============================================================
		     ALERTS / INFO BOXES
		     ============================================================ -->
		<section class="jcp-section rankings-section">
			<div class="jcp-container">
				<h2 style="margin-bottom: var(--jcp-space-3xl);">Alerts / Info Boxes</h2>
				<div style="display: flex; flex-direction: column; gap: var(--jcp-space-md); max-width: 560px;">
					<div style="background: #f0f9ff; border-left: 4px solid var(--jcp-color-info); padding: var(--jcp-space-md) var(--jcp-space-lg); border-radius: var(--jcp-radius-sm);">
						<strong>Info:</strong> This is an informational message.
					</div>
					<div style="background: #fef3c7; border-left: 4px solid var(--jcp-color-warning); padding: var(--jcp-space-md) var(--jcp-space-lg); border-radius: var(--jcp-radius-sm);">
						<strong>Warning:</strong> This is a warning message.
					</div>
					<div style="background: #fee2e2; border-left: 4px solid var(--jcp-color-error); padding: var(--jcp-space-md) var(--jcp-space-lg); border-radius: var(--jcp-radius-sm);">
						<strong>Error:</strong> This is an error message.
					</div>
				</div>
			</div>
		</section>

		<!-- ============================================================
		     BADGES & PILLS
		     ============================================================ -->
		<section class="jcp-section" style="background: var(--jcp-color-bg-secondary);">
			<div class="jcp-container">
				<h2 style="margin-bottom: var(--jcp-space-3xl);">Badges & Pills</h2>
				
				<div style="display: grid; gap: var(--jcp-space-lg);">
					<div>
						<h3 style="font-size: var(--jcp-font-size-lg); margin-bottom: var(--jcp-space-md);">Directory Badges</h3>
						<div style="display: flex; gap: var(--jcp-space-md); flex-wrap: wrap;">
							<span class="directory-badge verified">Verified</span>
							<span class="directory-badge trusted">Trusted Pro</span>
							<span class="directory-badge listed">Listed</span>
						</div>
					</div>
					<div>
						<h3 style="font-size: var(--jcp-font-size-lg); margin-bottom: var(--jcp-space-md);">Hero Media Pills</h3>
						<div style="display: flex; gap: var(--jcp-space-md); flex-wrap: wrap;">
							<span class="hero-media-pill">Real job proof</span>
						</div>
					</div>
					<div>
						<h3 style="font-size: var(--jcp-font-size-lg); margin-bottom: var(--jcp-space-md);">Preview Pills</h3>
						<div style="display: flex; gap: var(--jcp-space-md); flex-wrap: wrap;">
							<span class="jcp-preview-pill jcp-badge-pulse">ROI focus</span>
							<span class="jcp-preview-pill jcp-badge-pulse">Early access</span>
						</div>
					</div>
				</div>
			</div>
		</section>

		<!-- ============================================================
		     METRICS / STATS
		     ============================================================ -->
		<section class="jcp-section">
			<div class="jcp-container">
				<h2 style="margin-bottom: var(--jcp-space-3xl);">Metrics / Stats</h2>
				
				<div style="margin-bottom: var(--jcp-space-4xl);">
					<h3 style="font-size: var(--jcp-font-size-lg); margin-bottom: var(--jcp-space-lg);">Hero Metrics</h3>
					<div class="jcp-hero-metrics">
						<div>
							<span class="jcp-metric">Proof → trust</span>
							<span class="jcp-metric-label">wins more jobs</span>
						</div>
						<div>
							<span class="jcp-metric">Automated</span>
							<span class="jcp-metric-label">no extra labor</span>
						</div>
						<div>
							<span class="jcp-metric">Local lift</span>
							<span class="jcp-metric-label">map visibility</span>
						</div>
					</div>
				</div>

				<div>
					<h3 style="font-size: var(--jcp-font-size-lg); margin-bottom: var(--jcp-space-lg);">Factor Stats</h3>
					<div class="ranking-factors-grid" style="grid-template-columns: repeat(3, 1fr);">
						<div class="ranking-factor-card">
							<div class="factor-stat">
								<span class="stat-value">Proof</span>
								<span class="stat-label">from real jobs</span>
							</div>
						</div>
						<div class="ranking-factor-card">
							<div class="factor-stat">
								<span class="stat-value">Map</span>
								<span class="stat-label">coverage grows</span>
							</div>
						</div>
						<div class="ranking-factor-card">
							<div class="factor-stat">
								<span class="stat-value">Always</span>
								<span class="stat-label">on brand</span>
							</div>
						</div>
					</div>
				</div>
			</div>
		</section>

		<!-- ============================================================
		     DESIGN TOKENS (Spacing, Colors, Typography)
		     ============================================================ -->
		<section class="jcp-section" style="background: var(--jcp-color-bg-secondary);">
			<div class="jcp-container">
				<h2 style="margin-bottom: var(--jcp-space-3xl);">Design Tokens</h2>
				<div style="margin-bottom: var(--jcp-space-4xl);">
					<h3 style="font-size: var(--jcp-font-size-lg); margin-bottom: var(--jcp-space-lg);">Spacing scale (8px base)</h3>
					<div style="display: flex; flex-wrap: wrap; gap: var(--jcp-space-md); align-items: flex-end;">
						<div style="text-align: center;"><div style="width: var(--jcp-space-xs); height: 24px; background: var(--jcp-color-primary); border-radius: 2px;"></div><code style="font-size: var(--jcp-font-size-xs);">xs 4px</code></div>
						<div style="text-align: center;"><div style="width: var(--jcp-space-sm); height: 24px; background: var(--jcp-color-primary); border-radius: 2px;"></div><code style="font-size: var(--jcp-font-size-xs);">sm 8px</code></div>
						<div style="text-align: center;"><div style="width: var(--jcp-space-md); height: 24px; background: var(--jcp-color-primary); border-radius: 2px;"></div><code style="font-size: var(--jcp-font-size-xs);">md 16px</code></div>
						<div style="text-align: center;"><div style="width: var(--jcp-space-lg); height: 24px; background: var(--jcp-color-primary); border-radius: 2px;"></div><code style="font-size: var(--jcp-font-size-xs);">lg 24px</code></div>
						<div style="text-align: center;"><div style="width: var(--jcp-space-xl); height: 24px; background: var(--jcp-color-primary); border-radius: 2px;"></div><code style="font-size: var(--jcp-font-size-xs);">xl 32px</code></div>
						<div style="text-align: center;"><div style="width: var(--jcp-space-2xl); height: 24px; background: var(--jcp-color-primary); border-radius: 2px;"></div><code style="font-size: var(--jcp-font-size-xs);">2xl 40px</code></div>
						<div style="text-align: center;"><div style="width: var(--jcp-space-3xl); height: 24px; background: var(--jcp-color-primary); border-radius: 2px;"></div><code style="font-size: var(--jcp-font-size-xs);">3xl 48px</code></div>
						<div style="text-align: center;"><div style="width: var(--jcp-space-5xl); height: 24px; background: var(--jcp-color-primary); border-radius: 2px;"></div><code style="font-size: var(--jcp-font-size-xs);">5xl 64px</code></div>
						<div style="text-align: center;"><div style="width: var(--jcp-space-6xl); height: 24px; background: var(--jcp-color-primary); border-radius: 2px;"></div><code style="font-size: var(--jcp-font-size-xs);">6xl 80px</code></div>
					</div>
				</div>
				<div style="margin-bottom: var(--jcp-space-4xl);">
					<h3 style="font-size: var(--jcp-font-size-lg); margin-bottom: var(--jcp-space-lg);">Colors</h3>
					<div style="display: flex; flex-wrap: wrap; gap: var(--jcp-space-lg);">
						<div><div style="width: 64px; height: 64px; background: var(--jcp-color-primary); border-radius: var(--jcp-radius-md);"></div><code style="font-size: var(--jcp-font-size-xs);">primary</code></div>
						<div><div style="width: 64px; height: 64px; background: var(--jcp-color-secondary); border-radius: var(--jcp-radius-md);"></div><code style="font-size: var(--jcp-font-size-xs);">secondary</code></div>
						<div><div style="width: 64px; height: 64px; background: var(--jcp-color-success); border-radius: var(--jcp-radius-md);"></div><code style="font-size: var(--jcp-font-size-xs);">success</code></div>
						<div><div style="width: 64px; height: 64px; background: var(--jcp-color-warning); border-radius: var(--jcp-radius-md);"></div><code style="font-size: var(--jcp-font-size-xs);">warning</code></div>
						<div><div style="width: 64px; height: 64px; background: var(--jcp-color-error); border-radius: var(--jcp-radius-md);"></div><code style="font-size: var(--jcp-font-size-xs);">error</code></div>
						<div><div style="width: 64px; height: 64px; background: var(--jcp-color-info); border-radius: var(--jcp-radius-md);"></div><code style="font-size: var(--jcp-font-size-xs);">info</code></div>
						<div><div style="width: 64px; height: 64px; background: var(--jcp-color-text-primary); border-radius: var(--jcp-radius-md);"></div><code style="font-size: var(--jcp-font-size-xs);">text-primary</code></div>
						<div><div style="width: 64px; height: 64px; background: var(--jcp-color-bg-secondary); border: 1px solid var(--jcp-color-border); border-radius: var(--jcp-radius-md);"></div><code style="font-size: var(--jcp-font-size-xs);">bg-secondary</code></div>
					</div>
				</div>
				<div>
					<h3 style="font-size: var(--jcp-font-size-lg); margin-bottom: var(--jcp-space-lg);">Typography</h3>
					<div style="display: flex; flex-direction: column; gap: var(--jcp-space-xl);">
						<div><h1 style="margin: 0;">Heading 1</h1><code style="font-size: var(--jcp-font-size-xs);">H1 — page titles</code></div>
						<div><h2 style="margin: 0;">Heading 2</h2><code style="font-size: var(--jcp-font-size-xs);">H2 — section titles</code></div>
						<div><h3 style="margin: 0;">Heading 3</h3><code style="font-size: var(--jcp-font-size-xs);">H3 — subsection / card titles</code></div>
						<div><h4 style="margin: 0;">Heading 4</h4><code style="font-size: var(--jcp-font-size-xs);">H4 — feature headers</code></div>
						<div><p style="margin: 0;">Body text (16px). Default paragraph.</p><code style="font-size: var(--jcp-font-size-xs);">Body</code></div>
						<div><p style="margin: 0; font-size: var(--jcp-font-size-sm);">Small text (14px). Subtext, labels.</p><code style="font-size: var(--jcp-font-size-xs);">Small</code></div>
					</div>
				</div>
			</div>
		</section>
	</main>
</div>

<?php get_footer(); ?>
