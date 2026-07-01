<?php
/**
 * JobCapturePro Theme Bootstrap
 * Loads all modular theme functionality from /inc/ directory
 *
 * @package JCP_Core
 */

// Load helper functions (asset paths, URLs, ACF helpers)
require_once get_template_directory() . '/inc/helpers.php';

// App onboarding handoff URLs (marketing site → SaaS signup)
require_once get_template_directory() . '/inc/onboarding.php';

// Sitewide settings (banner, signup URL, nav CTAs)
require_once get_template_directory() . '/inc/global-settings.php';

// Load company data functions (description resolution, demo companies, save_post description generation)
require_once get_template_directory() . '/inc/company-data.php';

// JCP Companies CPT + API sync (Import from API, daily cron, shortcode). API key: set JCP_API_TOKEN in wp-config.php.
require_once get_template_directory() . '/inc/jcp-api-cpt.php';

// Load asset enqueuing logic
require_once get_template_directory() . '/inc/enqueue.php';

// Load template routing
require_once get_template_directory() . '/inc/template-routes.php';

// SEO for directory and contractor profile pages (meta description, profile titles, schema)
require_once get_template_directory() . '/inc/seo-directory.php';

// Load ACF configuration (if ACF is available)
require_once get_template_directory() . '/inc/acf-config.php';

// Industry / niche landing pages (CPT, JSON content, /industries/ archive)
require_once get_template_directory() . '/inc/page-blocks/registry.php';
require_once get_template_directory() . '/inc/page-blocks/layout.php';
require_once get_template_directory() . '/inc/page-blocks/presets.php';
require_once get_template_directory() . '/inc/page-blocks/schema.php';
require_once get_template_directory() . '/inc/page-blocks/industry-media.php';
require_once get_template_directory() . '/inc/page-blocks/doc-sections.php';
require_once get_template_directory() . '/inc/niche-landing/cpt.php';
require_once get_template_directory() . '/inc/niche-landing/schema.php';
require_once get_template_directory() . '/inc/niche-landing/doc-parser.php';
require_once get_template_directory() . '/inc/page-blocks/doc-parser.php';
require_once get_template_directory() . '/inc/niche-landing/partials.php';
require_once get_template_directory() . '/inc/niche-landing/components.php';
require_once get_template_directory() . '/inc/niche-landing/media.php';
require_once get_template_directory() . '/inc/niche-landing/editable.php';
require_once get_template_directory() . '/inc/niche-landing/split-block.php';
require_once get_template_directory() . '/inc/niche-landing/split-block.php';
require_once get_template_directory() . '/inc/niche-landing/render.php';
require_once get_template_directory() . '/inc/page-blocks/render.php';
require_once get_template_directory() . '/inc/page-blocks/rest-content.php';
require_once get_template_directory() . '/inc/niche-landing/seed.php';
require_once get_template_directory() . '/inc/page-blocks/migrate-pages.php';
if ( is_admin() ) {
	require_once get_template_directory() . '/inc/niche-landing/admin.php';
	require_once get_template_directory() . '/inc/page-blocks/seo-audit.php';
	require_once get_template_directory() . '/inc/page-blocks/admin-structure.php';
}

// Canonical form field names (REST params + GHL keys); Demo Survey = source of truth
require_once get_template_directory() . '/inc/form-fields.php';

// REST: Demo Survey form → GHL webhook
require_once get_template_directory() . '/inc/rest-demo-survey.php';

// REST: Contact form → GHL webhook
require_once get_template_directory() . '/inc/rest-contact.php';

// Demo analytics: DB table + REST endpoint
require_once get_template_directory() . '/inc/demo-analytics.php';

// Page template dropdown filter (must load on REST too — block editor template list).
require_once get_template_directory() . '/inc/admin-page-templates.php';

if ( is_admin() ) {
    require_once get_template_directory() . '/inc/admin-theme-docs.php';
    require_once get_template_directory() . '/inc/admin-block-library.php';
    require_once get_template_directory() . '/inc/admin-demo-analytics.php';
    require_once get_template_directory() . '/inc/admin-global-settings.php';
}

/**
 * Theme setup: text domain and SEO-safe document title.
 */
function jcp_core_theme_setup(): void {
	load_theme_textdomain( 'jcp-core', get_template_directory() . '/languages' );
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
}
add_action( 'after_setup_theme', 'jcp_core_theme_setup' );

/**
 * Flush permalinks once after removing the legacy jcp_page CPT.
 */
function jcp_core_flush_after_jcp_page_cpt_removed(): void {
	if ( get_option( 'jcp_page_cpt_removed_flush' ) === '1' ) {
		return;
	}
	flush_rewrite_rules( false );
	update_option( 'jcp_page_cpt_removed_flush', '1' );
}
add_action( 'init', 'jcp_core_flush_after_jcp_page_cpt_removed', 99 );

/**
 * Remove tailwind.min.css if it's being enqueued (prevents 404 errors)
 */
function jcp_core_remove_tailwind() {
    wp_dequeue_style( 'tailwind' );
    wp_deregister_style( 'tailwind' );
    wp_dequeue_style( 'tailwindcss' );
    wp_deregister_style( 'tailwindcss' );
    wp_dequeue_style( 'tailwind.min.css' );
    wp_deregister_style( 'tailwind.min.css' );
}
add_action( 'wp_enqueue_scripts', 'jcp_core_remove_tailwind', 999 );

/**
 * On the prototype page, dequeue plugin scripts that throw (e.g. Fido2 CredentialsContainer)
 * so they don't break the app on live.
 */
function jcp_core_prototype_dequeue_conflicting_scripts(): void {
	$pages = jcp_core_get_page_detection();
	if ( empty( $pages['is_prototype'] ) ) {
		return;
	}
	$wp_scripts = wp_scripts();
	if ( ! $wp_scripts ) {
		return;
	}
	$conflicting = [ 'fido2', 'fido-2' ];
	foreach ( $wp_scripts->registered as $handle => $obj ) {
		if ( ! isset( $obj->src ) || ! is_string( $obj->src ) ) {
			continue;
		}
		$src_lower = strtolower( $obj->src );
		foreach ( $conflicting as $needle ) {
			if ( strpos( $src_lower, $needle ) !== false ) {
				wp_dequeue_script( $handle );
				break;
			}
		}
	}
}
add_action( 'wp_enqueue_scripts', 'jcp_core_prototype_dequeue_conflicting_scripts', 9999 );


/**
 * JCP Review Page - abdul's Complete redesign
 */

// Main function to display review data
function display_jcp_review_data() {
    // Get checkin_ref and company_ref from URL parameters
    $checkin_id = isset($_GET['checkin_ref']) ? sanitize_text_field($_GET['checkin_ref']) : '';
    $company_id = isset($_GET['company_ref']) ? sanitize_text_field($_GET['company_ref']) : '';
    
    // If no IDs provided, show error
    if (empty($checkin_id) || empty($company_id)) {
        return '<div class="jcp-error">
                <h3>Missing Parameters</h3>
                <p>Please provide both checkin_ref and company_ref in the URL.</p>
                <p>Example: <code>' . esc_url(home_url('/review_request/')) . '?checkin_ref=iaGlbtlFv6PUzP07JbFa&company_ref=Lkk6W0v0YmSP4feEoahd</code></p>
                </div>';
    }
    
    // Set your access token and base URL
    $access_token = '17487994cd7b7c5e6c51320dd6cae6db';
    $base_url = 'https://app.jobcapturepro.com/api/';
    
    // Fetch data
    $checkin_data = jcp_fetch_review_data('checkins', $checkin_id, $access_token, $base_url);
    $company_data = jcp_fetch_review_data('companies', $company_id, $access_token, $base_url);
    
    // Add CSS
    jcp_add_review_styles();
    
    // Add JavaScript for share functionality
    jcp_add_share_scripts();
    
    // Start building output
    $output = '<div class="jcp-review-container">';
    $output .= '<div class="rankings-header">';
    $output .= '<div class="jcp-archive-intro">';
    
    // Main content - Two columns
    $output .= '<div class="jcp-review-grid">';
    
    // LEFT COLUMN - Review content (centered)
    $output .= '<div class="jcp-review-left">';
    $output .= jcp_display_left_column($checkin_data, $company_data);
    $output .= '</div>';
    
    // RIGHT COLUMN - Images
    $output .= '<div class="jcp-review-right">';
    $output .= jcp_display_image_slider($checkin_data);
    $output .= '</div>';
    
    $output .= '</div>'; // Close grid
    $output .= '</div>'; // Close jcp-archive-intro
    $output .= '</div>'; // Close rankings-header
    $output .= '</div>'; // Close container
    
    return $output;
}

/**
 * Display left column with review content
 */
function jcp_display_left_column($checkin_data, $company_data) {
    // Get company info
    $company_name = '';
    $company_logo_url = '';
    $google_review_url = '#';
    
    if ($company_data && !is_wp_error($company_data)) {
        $company_name = !empty($company_data['name']) ? $company_data['name'] : '';
        $company_logo_url = !empty($company_data['logoUrl']) ? $company_data['logoUrl'] : '';
        
        // Fix logo URL if relative
        if ($company_logo_url && strpos($company_logo_url, 'http') !== 0) {
            $company_logo_url = 'https://app.jobcapturepro.com/' . ltrim($company_logo_url, '/');
        }
        
        // Try to get Google review URL - check multiple possible field names
        $possible_url_fields = ['googleReviewUrl', 'googleReviewURL', 'google_review_url', 'reviewUrl', 'reviewURL', 'googlePlaceId'];
        foreach ($possible_url_fields as $field) {
            if (!empty($company_data[$field])) {
                // If it's a googlePlaceId, construct the URL
                if ($field === 'googlePlaceId') {
                    $google_review_url = 'https://search.google.com/local/writereview?placeid=' . urlencode($company_data[$field]);
                } elseif (filter_var($company_data[$field], FILTER_VALIDATE_URL)) {
                    $google_review_url = $company_data[$field];
                }
                if ($google_review_url !== '#') break;
            }
        }
    }
    
    // Also check in checkin data for URL
    if ($google_review_url === '#' && $checkin_data && !is_wp_error($checkin_data)) {
        $possible_url_fields = ['googleReviewUrl', 'googleReviewURL', 'google_review_url', 'reviewUrl', 'reviewURL', 'googlePlaceId'];
        foreach ($possible_url_fields as $field) {
            if (!empty($checkin_data[$field])) {
                if ($field === 'googlePlaceId') {
                    $google_review_url = 'https://search.google.com/local/writereview?placeid=' . urlencode($checkin_data[$field]);
                } elseif (filter_var($checkin_data[$field], FILTER_VALIDATE_URL)) {
                    $google_review_url = $checkin_data[$field];
                }
                if ($google_review_url !== '#') break;
            }
        }
    }
    
    $output = '';
    
    // Company logo moved above "Thank you from" text - centered
    if ($company_logo_url) {
        $output .= '<div class="jcp-logo-wrapper">';
        $output .= '<img src="' . esc_url($company_logo_url) . '" alt="' . esc_attr($company_name) . ' Logo" class="jcp-company-logo">';
        $output .= '</div>';
    }
    
    // Thank you from header with company name (no logo here anymore)
    $output .= '<div class="jcp-thankyou-header">';
    $output .= '<h4 class="jcp-thankyou-from">THANK YOU FROM</h4>';
    if ($company_name) {
        $output .= '<div class="jcp-company-name-wrapper">';
        $output .= '<span class="jcp-company-name">' . esc_html($company_name) . '</span>';
        $output .= '</div>';
    }
    $output .= '</div>';
    
    // Main thank you message
    $output .= '<h2 class="jcp-main-thankyou">We greatly appreciate your feedback</h2>';
    
    $output .= '<p class="jcp-review-message">We loved working with you. A quick review is the single biggest way you can help support our team. It helps your neighbors find us and keeps our team busy.</p>';
    
    // 5 Stars SVG - Each star is a clickable link to Google Review
    $output .= '<div class="jcp-stars-container">';
    for ($i = 0; $i < 5; $i++) {
        $output .= '<a href="' . esc_url($google_review_url) . '" target="_blank" rel="noopener noreferrer" class="jcp-star-link">';
        $output .= '<svg class="jcp-star-icon" width="38" height="38" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round">';
        $output .= '<path d="M12 2.5l2.9 5.88 6.49.94-4.7 4.58 1.11 6.46L12 17.77l-5.8 3.05 1.1-6.46-4.69-4.58 6.49-.94L12 2.5z"/>';
        $output .= '</svg>';
        $output .= '</a>';
    }
    $output .= '</div>';
    
    // Review button with Google icon
    $output .= '<a href="' . esc_url($google_review_url) . '" target="_blank" class="jcp-review-button" rel="noopener noreferrer">';
    $output .= '<svg class="jcp-google-icon" width="16" height="16" viewBox="0 0 48 48" aria-hidden="true">';
    $output .= '<path fill="#4285F4" d="M45.12 24.5c0-1.56-.14-3.06-.4-4.5H24v8.51h11.84c-.51 2.75-2.06 5.08-4.39 6.64v5.52h7.11c4.16-3.83 6.56-9.47 6.56-16.17z"/>';
    $output .= '<path fill="#34A853" d="M24 46c5.94 0 10.92-1.97 14.56-5.33l-7.11-5.52c-1.97 1.32-4.49 2.1-7.45 2.1-5.73 0-10.58-3.87-12.31-9.07H4.34v5.7C7.96 41.07 15.4 46 24 46z"/>';
    $output .= '<path fill="#FBBC05" d="M11.69 28.18c-.44-1.32-.69-2.73-.69-4.18s.25-2.86.69-4.18v-5.7H4.34A21.99 21.99 0 0 0 2 24c0 3.55.85 6.91 2.34 9.88l7.35-5.7z"/>';
    $output .= '<path fill="#EA4335" d="M24 10.75c3.23 0 6.13 1.11 8.41 3.29l6.31-6.31C34.91 4.18 29.93 2 24 2 15.4 2 7.96 6.93 4.34 14.12l7.35 5.7c1.73-5.2 6.58-9.07 12.31-9.07z"/>';
    $output .= '</svg>';
    $output .= 'Review Us on Google';
    $output .= '</a>';
    
    // Tap a star message
    $output .= '<p class="jcp-tap-star">Tap a star — it opens Google</p>';
    
    return $output;
}

/**
 * Display image slider with share functionality only
 */
function jcp_display_image_slider($checkin_data) {
    $image_urls = array();
    
    if ($checkin_data && !is_wp_error($checkin_data)) {
        // Check for imageUrls field
        if (!empty($checkin_data['imageUrls']) && is_array($checkin_data['imageUrls'])) {
            $image_urls = $checkin_data['imageUrls'];
        }
        // Also check for other possible image fields
        elseif (!empty($checkin_data['images']) && is_array($checkin_data['images'])) {
            $image_urls = $checkin_data['images'];
        }
        elseif (!empty($checkin_data['photos']) && is_array($checkin_data['photos'])) {
            $image_urls = $checkin_data['photos'];
        }
    }
    
    // Filter out empty URLs
    $image_urls = array_filter($image_urls);
    
    // Process each URL - keep original URLs from API
    foreach ($image_urls as $key => $url) {
        // If URL is empty, remove it
        if (empty($url)) {
            unset($image_urls[$key]);
            continue;
        }
        // Keep the URL as-is from the API
        $image_urls[$key] = $url;
    }
    
    // Remove duplicate URLs and re-index
    $image_urls = array_values(array_unique($image_urls));
    
    // Debug log to see what URLs we have
    error_log('JCP Image URLs from API: ' . print_r($image_urls, true));
    
    if (empty($image_urls)) {
        return '<div class="jcp-no-images">No photos available for this job</div>';
    }
    
    $image_count = count($image_urls);
    $slider_id = 'jcp-slider-' . uniqid();
    
    $output = '<div class="jcp-image-section">';
    $output .= '<div class="jcp-photos-header">';
    $output .= '<h3>PHOTOS FROM YOUR JOB</h3>';
    $output .= '<span class="jcp-photo-count">' . $image_count . ' PHOTOS</span>';
    $output .= '</div>';
    
    // Image slider container
    $output .= '<div class="jcp-slider-container-main" id="' . $slider_id . '">';
    
    // Slider
    $output .= '<div class="jcp-slider-wrapper">';
    $output .= '<div class="jcp-slider-track">';
    
    foreach ($image_urls as $index => $url) {
        // Use a proxy for images to avoid CORS and 403 errors
        $proxy_url = admin_url('admin-ajax.php?action=jcp_proxy_image&image_url=' . urlencode($url));
        
        $output .= '<div class="jcp-slide" data-url="' . esc_url($url) . '" data-index="' . $index . '">';
        $output .= '<img src="' . esc_url($proxy_url) . '" alt="Job photo ' . ($index + 1) . '" class="jcp-slide-img" loading="lazy" data-original-url="' . esc_url($url) . '">';
        
        // Share button only (bottom left)
        $output .= '<button class="jcp-share-single-btn" data-url="' . esc_url($url) . '" data-index="' . $index . '" aria-label="Share this photo">';
        $output .= '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">';
        $output .= '<circle cx="18" cy="5" r="3"></circle>';
        $output .= '<circle cx="6" cy="12" r="3"></circle>';
        $output .= '<circle cx="18" cy="19" r="3"></circle>';
        $output .= '<line x1="8.59" y1="13.51" x2="15.42" y2="17.49"></line>';
        $output .= '<line x1="15.41" y1="6.51" x2="8.59" y2="10.49"></line>';
        $output .= '</svg>';
        $output .= '</button>';
        $output .= '</div>';
    }
    
    $output .= '</div>';
    $output .= '</div>';
    
    // Left and Right Arrow Navigation
    if ($image_count > 1) {
        $output .= '<button class="jcp-slider-arrow prev" aria-label="Previous slide">';
        $output .= '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">';
        $output .= '<polyline points="15 18 9 12 15 6"></polyline>';
        $output .= '</svg>';
        $output .= '</button>';
        $output .= '<button class="jcp-slider-arrow next" aria-label="Next slide">';
        $output .= '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">';
        $output .= '<polyline points="9 18 15 12 9 6"></polyline>';
        $output .= '</svg>';
        $output .= '</button>';
    }
    
    // Dots navigation
    if ($image_count > 1) {
        $output .= '<div class="jcp-slider-dots">';
        for ($i = 0; $i < $image_count; $i++) {
            $active_class = ($i === 0) ? ' active' : '';
            $output .= '<button class="jcp-slider-dot' . $active_class . '" data-index="' . $i . '" aria-label="Go to slide ' . ($i + 1) . '"></button>';
        }
        $output .= '</div>';
    }
    
    $output .= '</div>'; // Close slider container
    
    // Footer text with camera icon
    $output .= '<div class="jcp-reviews-footer">';
    $output .= '<svg class="jcp-camera-icon" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">';
    $output .= '<path d="M3 8a2 2 0 0 1 2-2h2l1.5-2h7L19 6h0a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>';
    $output .= '<circle cx="12" cy="13" r="3.2"/>';
    $output .= '</svg>';
    $output .= '<span>Reviews with a photo get noticed first.<br><b>Share yours</b> and add one when you post — it only takes a sec.</span>';
    $output .= '</div>';
    $output .= '<p class="jcp-powered-by">Reviews collected with <b>JobCapturePro</b></p>';
    $output .= '</div>';
    
    // Add slider initialization script
    $output .= '<script>
    (function() {
        var container = document.getElementById("' . $slider_id . '");
        if (container) {
            var track = container.querySelector(".jcp-slider-track");
            var slides = container.querySelectorAll(".jcp-slide");
            var dots = container.querySelectorAll(".jcp-slider-dot");
            var prevBtn = container.querySelector(".jcp-slider-arrow.prev");
            var nextBtn = container.querySelector(".jcp-slider-arrow.next");
            
            if (track && slides.length > 0) {
                var currentIndex = 0;
                var totalSlides = slides.length;
                var touchStartX = 0;
                var touchEndX = 0;
                
                function updateSlider() {
                    track.style.transform = "translateX(-" + (currentIndex * 100) + "%)";
                    for (var i = 0; i < dots.length; i++) {
                        if (i === currentIndex) {
                            dots[i].classList.add("active");
                        } else {
                            dots[i].classList.remove("active");
                        }
                    }
                }
                
                function nextSlide() {
                    currentIndex = (currentIndex + 1) % totalSlides;
                    updateSlider();
                }
                
                function prevSlide() {
                    currentIndex = (currentIndex - 1 + totalSlides) % totalSlides;
                    updateSlider();
                }
                
                if (prevBtn) {
                    prevBtn.addEventListener("click", function(e) {
                        e.stopPropagation();
                        prevSlide();
                    });
                }
                
                if (nextBtn) {
                    nextBtn.addEventListener("click", function(e) {
                        e.stopPropagation();
                        nextSlide();
                    });
                }
                
                track.addEventListener("touchstart", function(e) {
                    touchStartX = e.changedTouches[0].screenX;
                });
                
                track.addEventListener("touchend", function(e) {
                    touchEndX = e.changedTouches[0].screenX;
                    if (touchEndX < touchStartX - 50) nextSlide();
                    if (touchEndX > touchStartX + 50) prevSlide();
                });
                
                for (var i = 0; i < dots.length; i++) {
                    dots[i].addEventListener("click", (function(index) {
                        return function() {
                            currentIndex = index;
                            updateSlider();
                        };
                    })(i));
                }
                
                updateSlider();
            }
        }
    })();
    </script>';
    
    return $output;
}

/**
 * Add CSS styles matching the design
 */
function jcp_add_review_styles() {
    if (defined('JCP_REVIEW_STYLES_ADDED')) {
        return;
    }
    define('JCP_REVIEW_STYLES_ADDED', true);
    
    $css = '
    <style>
    /* Reset and Base */
    .jcp-review-container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 40px 20px;
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
        background: #fff;
    }
    
    .jcp-archive-intro {
        max-width: 100%;
        margin-left: auto;
        margin-right: auto;
        margin-top: 0;
        margin-bottom: var(--jcp-space-md);
        text-align: center;
        font-size: var(--jcp-font-size-lg);
        line-height: var(--jcp-line-height-relaxed);
        color: var(--jcp-color-text-secondary);
    }
    
    .rankings-header {
        text-align: center;
        margin-bottom: var(--jcp-space-6xl);
        max-width: 100%;
        margin-left: auto;
        margin-right: auto;
    }
    
    .jcp-review-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 60px;
        align-items: start;
        text-align: left;
    }
    
    @media (max-width: 768px) {
        .jcp-review-grid {
            grid-template-columns: 1fr;
            gap: 40px;
        }
        .jcp-review-container {
            padding: 20px;
        }
        .jcp-main-thankyou {
            margin-bottom: 15px !important;
            line-height: 1.3 !important;
        }
        .jcp-slider-arrow {
            width: 36px !important;
            height: 36px !important;
        }
        .jcp-slider-arrow svg {
            width: 20px !important;
            height: 20px !important;
        }
        .jcp-share-single-btn {
            width: 36px !important;
            height: 36px !important;
        }
        .jcp-share-single-btn svg {
            width: 18px !important;
            height: 18px !important;
        }
    }
    
    /* LEFT COLUMN STYLES - Centered */
    .jcp-review-left {
        text-align: center;
    }
    
    .jcp-logo-wrapper {
        margin-bottom: 20px;
        display: flex;
        justify-content: center;
    }
    
    .jcp-company-logo {
        max-width: 200px;
        max-height: 60px;
        object-fit: contain;
    }
    
    .jcp-thankyou-header {
        margin-bottom: 24px;
    }
    
    .jcp-thankyou-from {
        font-size: 14px;
        letter-spacing: 2px;
        color: #9ca3af;
        margin: 0 0 8px 0;
        font-weight: 600;
    }
    
    .jcp-company-name-wrapper {
        display: flex;
        justify-content: center;
    }
    
    .jcp-company-name {
        font-size: 18px;
        font-weight: 700;
        color: #111827;
    }
    
    .jcp-main-thankyou {
        font-size: 25px !important;
        font-weight: 700;
        color: #111827;
        margin: 0 0 20px 0;
        line-height: 1.2;
    }
    
    .jcp-review-message {
        font-size: 16px;
        line-height: 1.5;
        color: #4b5563;
        margin: 0 0 30px 0;
    }
    
    .jcp-stars-container {
        display: flex;
        gap: 6px;
        margin-bottom: 30px;
        justify-content: center;
    }
    
    .jcp-star-link {
        display: inline-block;
        cursor: pointer;
        transition: transform 0.2s ease;
    }
    
    .jcp-star-link:hover {
        transform: scale(1.1);
    }
    
    .jcp-star-icon {
        width: 38px;
        height: 38px;
        color: #f59e0b;
        stroke: #f59e0b;
        fill: #f59e0b;
        pointer-events: none;
    }
    
    .jcp-review-button {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        background: #ee4b3a;
        color: white;
        padding: 14px 32px;
        border-radius: 999px;
        text-decoration: none;
        font-weight: 700;
        font-size: 16px;
        transition: all 0.2s ease;
        border: none;
        cursor: pointer;
        margin-bottom: 20px;
    }
    
    .jcp-review-button:hover {
        background: #d63f22;
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }
    
    .jcp-google-icon {
        width: 18px;
        height: 18px;
        background: #fff;
        border-radius: 50%;
    }
    
    .jcp-tap-star {
        font-size: 14px;
        color: #6b7280;
        margin: 0;
    }
    
    /* RIGHT COLUMN - IMAGE SLIDER STYLES */
    .jcp-image-section {
        background: #f3f4f6;
        padding: 25px;
        border-radius: 20px;
    }
    
    .jcp-photos-header {
        display: flex;
        justify-content: space-between;
        align-items: baseline;
        margin-bottom: 16px;
    }
    
    .jcp-photos-header h3 {
        font-size: 12px;
        letter-spacing: 1.5px;
        color: #9ca3af;
        margin: 0;
        font-weight: 600;
    }
    
    .jcp-photo-count {
        font-size: 12px;
        color: #9ca3af;
        font-weight: 500;
    }
    
    .jcp-slider-container-main {
        position: relative;
        background: #f3f4f6;
        border-radius: 20px;
        overflow: hidden;
    }
    
    .jcp-slider-wrapper {
        position: relative;
        overflow: hidden;
        border-radius: 16px;
    }
    
    .jcp-slider-track {
        display: flex;
        transition: transform 0.4s ease-out;
        cursor: grab;
    }
    
    .jcp-slider-track:active {
        cursor: grabbing;
    }
    
    .jcp-slide {
        min-width: 100%;
        position: relative;
        aspect-ratio: 4 / 3;
    }
    
    .jcp-slide-img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
        background: #e5e7eb;
    }
    
    /* Share Button - Bottom Left */
    .jcp-share-single-btn {
        position: absolute;
        bottom: 15px;
        left: 15px;
        background: rgba(0,0,0,0.6);
        backdrop-filter: blur(4px);
        border: none;
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.2s ease;
        color: white;
        z-index: 10;
    }
    
    .jcp-share-single-btn:hover {
        background: rgba(0,0,0,0.8);
        transform: scale(1.05);
    }
    
    .jcp-share-single-btn:active {
        transform: scale(0.95);
    }
    
    .jcp-share-single-btn svg {
        width: 20px;
        height: 20px;
        stroke: white;
        stroke-width: 2;
    }
    
    /* Arrow Navigation Styles */
    .jcp-slider-arrow {
        position: absolute;
        top: 50%;
        transform: translateY(-50%);
        background: rgba(0,0,0,0.5);
        backdrop-filter: blur(4px);
        border: none;
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.2s ease;
        z-index: 15;
        padding: 0;
        margin: 0;
        -webkit-tap-highlight-color: transparent;
    }
    
    .jcp-slider-arrow svg {
        width: 24px;
        height: 24px;
        stroke: white;
        stroke-width: 2;
        display: block;
    }
    
    .jcp-slider-arrow:hover {
        background: rgba(0,0,0,0.8);
        transform: translateY(-50%) scale(1.05);
    }
    
    .jcp-slider-arrow:active {
        transform: translateY(-50%) scale(0.95);
    }
    
    .jcp-slider-arrow.prev {
        left: 12px;
    }
    
    .jcp-slider-arrow.next {
        right: 12px;
    }
    
    /* Touch device optimization */
    @media (hover: none) and (pointer: coarse) {
        .jcp-slider-arrow {
            width: 44px;
            height: 44px;
            background: rgba(0,0,0,0.6);
        }
        .jcp-slider-arrow svg {
            width: 22px;
            height: 22px;
        }
        .jcp-share-single-btn {
            width: 44px;
            height: 44px;
            background: rgba(0,0,0,0.6);
        }
        .jcp-share-single-btn svg {
            width: 22px;
            height: 22px;
        }
    }
    
    .jcp-slider-dots {
        display: flex;
        justify-content: center;
        gap: 8px;
        padding: 16px 0 8px;
    }
    
    .jcp-slider-dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: #d1d5db;
        border: none;
        padding: 0;
        cursor: pointer;
        transition: all 0.2s;
    }
    
    .jcp-slider-dot.active {
        background: #ee4b3a;
        width: 24px;
        border-radius: 12px;
    }
    
    .jcp-reviews-footer {
        text-align: left;
        font-size: 13px;
        color: #6b7280;
        margin: 24px 0 8px 0;
        line-height: 1.4;
        display: flex;
        align-items: flex-start;
        justify-content: center;
        gap: 8px;
        flex-wrap: nowrap;
    }
    
    .jcp-camera-icon {
        width: 17px;
        height: 17px;
        color: #ff5036;
        stroke: #ff5036;
        flex-shrink: 0;
        margin-top: 2px;
    }
    
    .jcp-powered-by {
        text-align: center;
        font-size: 11px;
        color: #9ca3af;
        margin: 0;
    }
    
    .jcp-no-images {
        background: #f3f4f6;
        border-radius: 20px;
        padding: 60px 20px;
        text-align: center;
        color: #9ca3af;
    }
    
    .jcp-error {
        padding: 20px;
        background: #fef2f2;
        border: 1px solid #fecaca;
        border-radius: 12px;
        color: #991b1b;
        margin: 20px;
    }
    
    .rankings-header h1 {
        display: none;
    }
    .page-id-1381 .directory-header, .page-id-1381 .jcp-footer {
        display: none !important;
    }
    .page-id-1381 .jcp-top-banner {
        display: none;
    }
    .page-id-1381 .jcp-marketing > .jcp-section:first-of-type {
        padding-top: 0 !important;
    }
    </style>
    ';
    
    echo $css;
}

/**
 * Add JavaScript for share functionality only
 */
function jcp_add_share_scripts() {
    if (defined('JCP_REVIEW_SCRIPTS_ADDED')) {
        return;
    }
    define('JCP_REVIEW_SCRIPTS_ADDED', true);
    
    $ajax_url = admin_url('admin-ajax.php');
    
    $script = '
    <script>
    document.addEventListener("DOMContentLoaded", function() {
        // Helper function to share an image using Web Share API
        async function shareImage(url, filename) {
            const proxyUrl = "' . $ajax_url . '?action=jcp_proxy_image&image_url=" + encodeURIComponent(url);
            
            try {
                const response = await fetch(proxyUrl);
                const blob = await response.blob();
                const file = new File([blob], filename, { type: blob.type });
                
                // Check if Web Share API is supported and can share files
                if (navigator.share && navigator.canShare && navigator.canShare({ files: [file] })) {
                    await navigator.share({
                        title: "Job Photo",
                        text: "Check out this photo from my job!",
                        files: [file]
                    });
                    return true;
                } else {
                    // Fallback: Open image in new tab
                    window.open(url, "_blank");
                    alert("Share not fully supported. Image opened in new tab. You can save it from there.");
                    return false;
                }
            } catch (error) {
                console.error("Share failed:", error);
                // Fallback: Open image in new tab
                window.open(url, "_blank");
                return false;
            }
        }
        
        // Helper function to extract filename from URL
        function getFileNameFromUrl(url, index) {
            try {
                const urlObj = new URL(url);
                const pathname = urlObj.pathname;
                let filename = pathname.split("/").pop();
                if (filename && filename.includes(".")) {
                    filename = filename.split("?")[0];
                    if (!filename.match(/\.(jpg|jpeg|png|gif|webp)$/i)) {
                        filename = filename + ".jpg";
                    }
                    return filename;
                }
            } catch (e) {}
            return `job_photo_${index + 1}.jpg`;
        }
        
        // Share single image button handler
        document.querySelectorAll(".jcp-share-single-btn").forEach(btn => {
            btn.addEventListener("click", async function(e) {
                e.stopPropagation();
                e.preventDefault();
                
                const url = this.getAttribute("data-url");
                const index = parseInt(this.getAttribute("data-index")) || 0;
                
                if (url) {
                    const originalHtml = this.innerHTML;
                    this.innerHTML = "<span style=\"font-size:14px;\">⏳</span>";
                    this.disabled = true;
                    
                    const filename = getFileNameFromUrl(url, index);
                    await shareImage(url, filename);
                    
                    this.innerHTML = originalHtml;
                    this.disabled = false;
                }
            });
        });
    });
    </script>
    ';
    
    echo $script;
}

/**
 * AJAX handler for proxying images (bypasses CORS and 403 errors)
 */
function jcp_ajax_proxy_image() {
    $image_url = isset($_GET['image_url']) ? urldecode($_GET['image_url']) : '';
    
    if (empty($image_url)) {
        status_header(404);
        echo 'Image not found';
        exit;
    }
    
    // Fetch the image from the remote server
    $response = wp_remote_get($image_url, array(
        'timeout' => 30,
        'sslverify' => false,
        'headers' => array(
            'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
        )
    ));
    
    if (is_wp_error($response)) {
        status_header(404);
        echo 'Failed to fetch image';
        exit;
    }
    
    $http_code = wp_remote_retrieve_response_code($response);
    if ($http_code !== 200) {
        status_header($http_code);
        echo 'Image not available';
        exit;
    }
    
    $image_data = wp_remote_retrieve_body($response);
    $content_type = wp_remote_retrieve_header($response, 'content-type');
    
    // Set headers for image output
    header('Content-Type: ' . $content_type);
    header('Cache-Control: public, max-age=86400'); // Cache for 24 hours
    
    // Output the image data
    echo $image_data;
    exit;
}
add_action('wp_ajax_jcp_proxy_image', 'jcp_ajax_proxy_image');
add_action('wp_ajax_nopriv_jcp_proxy_image', 'jcp_ajax_proxy_image');

/**
 * Fetch data from API
 */
function jcp_fetch_review_data($type, $id, $access_token, $base_url) {
    $cache_key = 'jcp_review_' . $type . '_' . md5($id);
    
    $cached = get_transient($cache_key);
    if ($cached !== false) {
        return $cached;
    }
    
    $url = $base_url . $type . '/' . $id;
    
    $args = array(
        'headers' => array(
            'Authorization' => 'Bearer ' . $access_token,
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ),
        'timeout' => 30,
        'sslverify' => true,
    );
    
    $response = wp_remote_get($url, $args);
    
    if (is_wp_error($response)) {
        error_log('JCP API Error: ' . $response->get_error_message());
        return $response;
    }
    
    $response_code = wp_remote_retrieve_response_code($response);
    $body = wp_remote_retrieve_body($response);
    
    if ($response_code !== 200) {
        error_log('JCP API HTTP Error: ' . $response_code);
        return new WP_Error('api_error', 'API returned HTTP ' . $response_code);
    }
    
    $data = json_decode($body, true);
    
    if (!$data || json_last_error() !== JSON_ERROR_NONE) {
        error_log('JCP API JSON Error: ' . json_last_error_msg());
        return new WP_Error('json_error', 'Invalid JSON response');
    }
    
    // Cache successful response for 5 minutes
    set_transient($cache_key, $data, 5 * MINUTE_IN_SECONDS);
    
    return $data;
}

/**
 * Shortcode for displaying the review page
 */
function jcp_review_shortcode($atts) {
    return display_jcp_review_data();
}
add_shortcode('jcp_review', 'jcp_review_shortcode');
add_shortcode('jcp_perimeters', 'jcp_review_shortcode');